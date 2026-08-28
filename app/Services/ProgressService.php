<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\Student;
use Illuminate\Support\Carbon;

class ProgressService
{
    public const COMPLETION_THRESHOLD = 90; // watch % that auto-completes a video lesson

    public function recordWatch(
        Student $student,
        Lesson $lesson,
        int $watchedSeconds,
        int $lastPosition,
        ?Carbon $now = null,
    ): Progress {
        $now ??= now();

        $progress = Progress::query()->firstOrNew([
            'student_id' => $student->id,
            'lesson_id' => $lesson->id,
        ]);

        $duration = max(1, $lesson->duration_seconds);
        $percent = (int) round(($watchedSeconds / $duration) * 100);
        $percent = min(100, max($progress->watch_percent, $percent));

        $progress->watched_seconds = max($progress->watched_seconds, $watchedSeconds);
        $progress->last_position_seconds = $lastPosition;
        $progress->watch_percent = $percent;
        $progress->last_watched_at = $now;

        if ($percent >= self::COMPLETION_THRESHOLD && ! $progress->completed) {
            $progress->completed = true;
            $progress->completed_at = $now;
        }

        $progress->save();

        ActivityLog::query()->create([
            'student_id' => $student->id,
            'lesson_id' => $lesson->id,
            'action' => ActivityLog::ACTION_VIDEO_WATCHED,
            'seconds_spent' => $watchedSeconds,
            'occurred_at' => $now,
        ]);

        // Completion may finalise the parent course → certificate.
        $this->finaliseCourseIfComplete($student, $lesson->module->course, $now);

        return $progress;
    }

    public function markCompleted(Student $student, Lesson $lesson): Progress
    {
        $progress = Progress::query()->firstOrNew([
            'student_id' => $student->id,
            'lesson_id' => $lesson->id,
        ]);

        $progress->watch_percent = 100;
        $progress->watched_seconds = max($progress->watched_seconds, $lesson->duration_seconds);
        $progress->completed = true;
        $progress->completed_at ??= now();
        $progress->last_watched_at = now();
        $progress->save();

        $this->finaliseCourseIfComplete($student, $lesson->module->course, now());

        return $progress;
    }

    /** Percent (0-100) of published lessons completed in a course. */
    public function courseProgress(Student $student, Course $course): float
    {
        $lessons = $course->lessons()->where('lessons.is_published', true)->pluck('lessons.id');

        if ($lessons->isEmpty()) {
            return 0.0;
        }

        $completed = Progress::query()
            ->where('student_id', $student->id)
            ->whereIn('lesson_id', $lessons)
            ->where('completed', true)
            ->count();

        return round(($completed / $lessons->count()) * 100, 1);
    }

    public function isCourseComplete(Student $student, Course $course): bool
    {
        return $this->courseProgress($student, $course) >= 100.0;
    }

    /** Issue a certificate once a course is fully complete (idempotent). */
    public function finaliseCourseIfComplete(Student $student, Course $course, ?Carbon $now = null): ?Certificate
    {
        if ($this->isCourseComplete($student, $course) === false) {
            return null;
        }

        $existing = Certificate::query()
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $certificate = Certificate::query()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'issued_at' => $now ?? now(),
        ]);

        ActivityLog::query()->create([
            'student_id' => $student->id,
            'action' => ActivityLog::ACTION_CERTIFICATE_ISSUED,
            'occurred_at' => $certificate->issued_at,
        ]);

        return $certificate;
    }

    public function recordActivity(Student $student, string $action, ?Lesson $lesson = null, int $secondsSpent = 0): ActivityLog
    {
        return ActivityLog::query()->create([
            'student_id' => $student->id,
            'lesson_id' => $lesson?->id,
            'action' => $action,
            'seconds_spent' => $secondsSpent,
            'occurred_at' => now(),
        ]);
    }
}

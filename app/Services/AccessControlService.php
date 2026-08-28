<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Subscription;
use Illuminate\Support\Collection;

/**
 * Encodes the core business rule:
 *   User → Subscription → Course → Access
 *
 * Access is granted only while a subscription is active/trial and not expired.
 * Expired/cancelled subscriptions revoke access but historical data is never deleted.
 */
class AccessControlService
{
    /** Subject IDs the student may currently access. */
    public function accessibleSubjectIds(Student $student): Collection
    {
        $subscriptions = $student->activeSubscriptions()
            ->with('plan.subjects')
            ->get()
            ->filter(fn (Subscription $s) => $s->grantsAccess());

        return $subscriptions
            ->map(fn (Subscription $s) => $s->plan->subjects->pluck('id'))
            ->flatten()
            ->unique()
            ->values();
    }

    /**
     * Subjects the student currently has access to.
     *
     * @return Collection<int, Subject>
     */
    public function accessibleSubjects(Student $student): Collection
    {
        $ids = $this->accessibleSubjectIds($student);

        if ($ids->isEmpty()) {
            return collect();
        }

        return Subject::query()->whereIn('id', $ids)->get();
    }

    public function canAccessSubject(Student $student, Subject $subject): bool
    {
        if (! $subject->is_active) {
            return false;
        }

        return $this->accessibleSubjectIds($student)->contains($subject->id);
    }

    public function canAccessCourse(Student $student, Course $course): bool
    {
        if (! $course->is_published) {
            return false;
        }

        return $this->canAccessSubject($student, $course->subject);
    }

    /**
     * A lesson is accessible when its course subject is subscribed,
     * unless the lesson is explicitly free (preview lessons).
     */
    public function canAccessLesson(Student $student, Lesson $lesson): bool
    {
        if ($lesson->is_free && $lesson->is_published) {
            return true;
        }

        return $this->canAccessCourse($student, $lesson->module->course);
    }

    public function canAccessLiveClass(Student $student, Subject $subject): bool
    {
        return $this->canAccessSubject($student, $subject);
    }
}

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
    public const PAID_LESSONS_PER_SUBSCRIPTION_MONTH = 4;

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

        $lesson->loadMissing('module.course.subject');

        if (! $this->canAccessCourse($student, $lesson->module->course)) {
            return false;
        }

        return $this->isLessonReleasedForStudent($student, $lesson);
    }

    public function canAccessLiveClass(Student $student, Subject $subject): bool
    {
        return $this->canAccessSubject($student, $subject);
    }

    /**
     * Paid content is paced by subscription period. A student with three
     * months of access should receive three months of lessons, not the entire
     * class library on day one.
     */
    public function releasedPaidLessonLimit(Student $student, Course $course): int
    {
        $course->loadMissing('subject');

        $months = $student->activeSubscriptions()
            ->with('plan.subjects')
            ->get()
            ->filter(fn (Subscription $subscription) => $subscription->grantsAccess())
            ->filter(fn (Subscription $subscription) => $subscription->plan->subjects->contains('id', $course->subject_id))
            ->map(function (Subscription $subscription) {
                if ($subscription->started_at === null || $subscription->expires_at === null) {
                    return 1;
                }

                $days = max(1, $subscription->started_at->diffInDays($subscription->expires_at, false));

                return max(1, (int) ceil($days / 30));
            })
            ->max() ?? 0;

        return $months * self::PAID_LESSONS_PER_SUBSCRIPTION_MONTH;
    }

    public function isLessonReleasedForStudent(Student $student, Lesson $lesson): bool
    {
        if (! $lesson->is_published) {
            return false;
        }

        if ($lesson->is_free) {
            return true;
        }

        $lesson->loadMissing('module.course');
        $limit = $this->releasedPaidLessonLimit($student, $lesson->module->course);

        if ($limit < 1) {
            return false;
        }

        $releasedLessonIds = Lesson::query()
            ->select('lessons.id')
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->where('modules.course_id', $lesson->module->course_id)
            ->where('lessons.is_published', true)
            ->where('lessons.is_free', false)
            ->orderBy('modules.sort_order')
            ->orderBy('modules.id')
            ->orderBy('lessons.sort_order')
            ->orderBy('lessons.id')
            ->limit($limit)
            ->pluck('lessons.id');

        return $releasedLessonIds->contains($lesson->id);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\DiscussionThread;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\User;
use App\Services\AccessControlService;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LessonPortalController extends Controller
{
    public function show(Request $request, Lesson $lesson, AccessControlService $access)
    {
        abort_if(! $lesson->is_published, 404);

        $lesson->load([
            'module.course.subject.classYear',
            'worksheets',
            'quizzes.questions',
            'assignments',
            'discussions.user',
        ]);

        $user = $request->user();
        $student = null;
        $hasAccess = $lesson->is_free;
        $hasCourseAccess = false;

        if ($user && $request->filled('student')) {
            /** @var User $user */
            $student = $user->students()->whereKey($request->integer('student'))->first();

            if ($student) {
                $hasCourseAccess = $access->canAccessCourse($student, $lesson->module->course);
                $hasAccess = $access->canAccessLesson($student, $lesson);
            }
        }

        return Inertia::render('Lessons/Show', [
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'notes' => $lesson->notes,
                'duration_minutes' => (int) ceil($lesson->duration_seconds / 60),
                'video_provider' => $lesson->video_provider,
                'video_id' => $lesson->video_id,
                'video_thumbnail' => $lesson->video_thumbnail,
                'is_free' => $lesson->is_free,
                'course' => [
                    'title' => $lesson->module->course->title,
                    'slug' => $lesson->module->course->slug,
                    'subject' => $lesson->module->course->subject?->name,
                    'year' => $lesson->module->course->subject?->classYear?->name,
                ],
                'worksheets' => $lesson->worksheets->map(fn ($worksheet) => [
                    'id' => $worksheet->id,
                    'title' => $worksheet->title,
                    'file_url' => $worksheet->file_url,
                ]),
                'quizzes' => $lesson->quizzes->where('is_published', true)->map(fn ($quiz) => [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'type' => $quiz->type,
                    'time_limit_minutes' => $quiz->time_limit_minutes,
                    'question_count' => $quiz->questions->count(),
                ])->values(),
                'assignments' => $lesson->assignments->where('is_published', true)->map(fn ($assignment) => [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'description' => $assignment->description,
                    'deadline' => $assignment->deadline?->toDateString(),
                    'max_score' => (float) $assignment->max_score,
                ])->values(),
                'discussions' => $lesson->discussions->take(5)->map(fn ($thread) => [
                    'id' => $thread->id,
                    'message' => $thread->message,
                    'author' => $thread->user?->name ?? 'Student',
                    'created_at' => $thread->created_at?->diffForHumans(),
                ]),
            ],
            'student' => $student ? [
                'id' => $student->id,
                'name' => $student->name,
                'student_code' => $student->student_code,
            ] : null,
            'progress' => $student ? $student->progress()
                ->where('lesson_id', $lesson->id)
                ->first(['watch_percent', 'watched_seconds', 'last_position_seconds', 'completed']) : null,
            'hasAccess' => $hasAccess,
            'lockReason' => $hasAccess
                ? null
                : ($hasCourseAccess ? 'scheduled' : 'subscription_required'),
            'subscribeUrl' => $student ? route('subscriptions.show', ['student' => $student->id]) : null,
        ]);
    }

    public function progress(Request $request, Lesson $lesson, AccessControlService $access, ProgressService $progress)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'watched_seconds' => ['required', 'integer', 'min:0'],
            'last_position_seconds' => ['required', 'integer', 'min:0'],
        ]);

        $student = $this->ownedStudent($request, $validated['student_id']);
        abort_unless($access->canAccessLesson($student, $lesson), 403);

        $progress->recordWatch(
            $student,
            $lesson,
            $validated['watched_seconds'],
            $validated['last_position_seconds'],
        );

        return back()->with('success', 'Lesson progress saved.');
    }

    public function complete(Request $request, Lesson $lesson, AccessControlService $access, ProgressService $progress)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ]);

        $student = $this->ownedStudent($request, $validated['student_id']);
        abort_unless($access->canAccessLesson($student, $lesson), 403);

        $progress->markCompleted($student, $lesson);

        return back()->with('success', 'Lesson marked as complete.');
    }

    public function question(Request $request, Lesson $lesson, AccessControlService $access)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'message' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $student = $this->ownedStudent($request, $validated['student_id']);
        abort_unless($access->canAccessLesson($student, $lesson), 403);

        DiscussionThread::query()->create([
            'lesson_id' => $lesson->id,
            'student_id' => $student->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        return back()->with('success', 'Question posted for the tutor.');
    }

    private function ownedStudent(Request $request, int $studentId): Student
    {
        /** @var User $user */
        $user = $request->user();

        return $user->students()->whereKey($studentId)->firstOrFail();
    }
}

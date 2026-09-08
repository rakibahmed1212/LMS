<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\User;
use App\Services\AccessControlService;
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

        if ($user && $request->filled('student')) {
            /** @var User $user */
            $student = $user->students()->whereKey($request->integer('student'))->first();

            if ($student) {
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
            'hasAccess' => $hasAccess,
            'subscribeUrl' => $student ? route('subscriptions.show', ['student' => $student->id]) : null,
        ]);
    }
}

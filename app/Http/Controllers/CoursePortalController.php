<?php

namespace App\Http\Controllers;

use App\Models\ClassYear;
use App\Models\Course;
use App\Models\Student;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AccessControlService;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoursePortalController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'class_year_id' => ['nullable', 'integer', 'exists:class_years,id'],
        ]);

        $years = ClassYear::query()
            ->where('is_active', true)
            ->when($filters['class_year_id'] ?? null, fn ($query, $yearId) => $query->whereKey($yearId))
            ->with(['subjects' => fn ($query) => $query
                ->where('is_active', true)
                ->with(['courses' => fn ($query) => $query
                    ->where('is_published', true)
                    ->when($filters['q'] ?? null, function ($query, string $term) {
                        $like = '%'.strtolower($term).'%';

                        $query->where(function ($query) use ($like) {
                            $query->whereRaw('LOWER(courses.title) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(courses.description) LIKE ?', [$like])
                                ->orWhereHas('subject', fn ($query) => $query->whereRaw('LOWER(subjects.name) LIKE ?', [$like]))
                                ->orWhereHas('modules', function ($query) use ($like) {
                                    $query->whereRaw('LOWER(modules.title) LIKE ?', [$like])
                                        ->orWhereHas('lessons', fn ($query) => $query
                                            ->whereRaw('LOWER(lessons.title) LIKE ?', [$like])
                                            ->orWhereRaw('LOWER(lessons.notes) LIKE ?', [$like])
                                            ->orWhereHas('worksheets', fn ($query) => $query->whereRaw('LOWER(worksheets.title) LIKE ?', [$like])));
                                });
                        });
                    })
                    ->withCount(['lessons'])
                    ->orderBy('title')])
                ->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ClassYear $year) => [
                'id' => $year->id,
                'name' => $year->name,
                'subjects' => $year->subjects->map(fn ($subject) => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'color' => $subject->color,
                    'courses' => $subject->courses->map(fn (Course $course) => [
                        'id' => $course->id,
                        'slug' => $course->slug,
                        'title' => $course->title,
                        'description' => $course->description,
                        'thumbnail' => $course->thumbnail,
                        'lessons_count' => $course->lessons_count,
                    ]),
                ]),
            ]);

        return Inertia::render('Courses/Index', [
            'years' => $years,
            'allYears' => ClassYear::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
            'filters' => [
                'q' => $filters['q'] ?? '',
                'class_year_id' => $filters['class_year_id'] ?? '',
            ],
        ]);
    }

    public function show(Request $request, Course $course, AccessControlService $access, ProgressService $progress)
    {
        abort_if(! $course->is_published, 404);

        $course->load([
            'subject.classYear',
            'modules.lessons' => fn ($query) => $query->where('is_published', true),
        ]);

        $user = $request->user();

        $children = collect();
        $unlockedStudentIds = collect();
        if ($user) {
            /** @var User $user */
            $children = $user->students()
                ->with('classYears')
                ->get()
                ->map(function (Student $student) use ($access, $course, $progress) {
                    $hasAccess = $access->canAccessCourse($student, $course);

                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                        'student_code' => $student->student_code,
                        'years' => $student->classYears->pluck('name'),
                        'has_access' => $hasAccess,
                        'course_progress' => $hasAccess ? $progress->courseProgress($student, $course) : 0,
                        'subscribe_url' => route('subscriptions.show', ['student' => $student->id]),
                    ];
                });
            $unlockedStudentIds = $children
                ->where('has_access', true)
                ->pluck('id')
                ->values();
        }

        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->whereHas('subjects', fn ($query) => $query->whereKey($course->subject_id))
            ->orderBy('is_bundle')
            ->orderBy('price')
            ->get()
            ->map(fn (SubscriptionPlan $plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'billing_cycle' => $plan->billing_cycle,
                'price' => (float) $plan->price,
                'trial_days' => $plan->trial_days,
                'is_bundle' => $plan->is_bundle,
            ]);

        return Inertia::render('Courses/Show', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'thumbnail' => $course->thumbnail,
                'subject' => $course->subject?->name,
                'year' => $course->subject?->classYear?->name,
                'modules' => $course->modules->map(fn ($module) => [
                    'id' => $module->id,
                    'title' => $module->title,
                    'lessons' => $module->lessons->map(fn ($lesson) => [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'notes' => $lesson->notes,
                        'duration_minutes' => (int) ceil($lesson->duration_seconds / 60),
                        'is_free' => $lesson->is_free,
                        'open_url' => route('lessons.show', [
                            'lesson' => $lesson->id,
                            'student' => $unlockedStudentIds->first(),
                        ]),
                    ]),
                ]),
            ],
            'children' => $children,
            'plans' => $plans,
        ]);
    }
}

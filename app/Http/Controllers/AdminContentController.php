<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ClassYear;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminContentController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Content', [
            'years' => ClassYear::query()
                ->with(['subjects.courses.modules.lessons' => fn ($query) => $query->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get()
                ->map(fn (ClassYear $year) => [
                    'id' => $year->id,
                    'name' => $year->name,
                    'subjects' => $year->subjects->map(fn ($subject) => [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'courses' => $subject->courses->map(fn ($course) => [
                            'id' => $course->id,
                            'title' => $course->title,
                            'modules' => $course->modules->map(fn ($module) => [
                                'id' => $module->id,
                                'title' => $module->title,
                                'lessons' => $module->lessons->map(fn ($lesson) => [
                                    'id' => $lesson->id,
                                    'title' => $lesson->title,
                                    'sort_order' => $lesson->sort_order,
                                    'is_free' => $lesson->is_free,
                                    'is_published' => $lesson->is_published,
                                    'video_provider' => $lesson->video_provider,
                                    'video_id' => $lesson->video_id,
                                ]),
                            ]),
                        ]),
                    ]),
                ]),
            'modules' => Module::query()
                ->with('course.subject.classYear')
                ->orderBy('course_id')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Module $module) => [
                    'id' => $module->id,
                    'label' => $module->course?->subject?->classYear?->name.' · '.$module->course?->subject?->name.' · '.$module->course?->title.' · '.$module->title,
                ]),
        ]);
    }

    public function storeLesson(Request $request)
    {
        $validated = $request->validate([
            'module_id' => ['required', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'duration_seconds' => ['required', 'integer', 'min:60', 'max:7200'],
            'video_provider' => ['nullable', 'string', 'max:40'],
            'video_id' => ['nullable', 'string', 'max:255'],
            'video_thumbnail' => ['nullable', 'string', 'max:255'],
            'is_free' => ['required', 'boolean'],
            'is_published' => ['required', 'boolean'],
        ]);

        $sortOrder = (int) Lesson::query()
            ->where('module_id', $validated['module_id'])
            ->max('sort_order') + 1;

        $lesson = Lesson::query()->create([
            ...$validated,
            'sort_order' => $sortOrder,
            'video_provider' => $validated['video_provider'] ?: 'mux',
            'video_id' => $validated['video_id'] ?: Str::slug($validated['title']).'-demo',
        ]);

        AuditLog::record('lesson.created', $lesson, ['new' => $lesson->only([
            'module_id', 'title', 'video_provider', 'video_id', 'is_published',
        ])], $request->user());

        return back()->with('success', 'Lesson created.');
    }
}

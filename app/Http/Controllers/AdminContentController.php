<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ClassYear;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\Worksheet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminContentController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Content', [
            'years' => ClassYear::query()
                ->with([
                    'subjects' => fn ($query) => $query->orderBy('sort_order'),
                    'subjects.courses' => fn ($query) => $query->orderBy('title'),
                    'subjects.courses.modules' => fn ($query) => $query->orderBy('sort_order'),
                    'subjects.courses.modules.lessons' => fn ($query) => $query->orderBy('sort_order'),
                    'subjects.courses.modules.lessons.worksheets' => fn ($query) => $query->orderBy('sort_order'),
                    'subjects.courses.modules.lessons.quizzes.questions' => fn ($query) => $query->orderBy('sort_order'),
                ])
                ->orderBy('sort_order')
                ->get()
                ->map(fn (ClassYear $year) => [
                    'id' => $year->id,
                    'name' => $year->name,
                    'slug' => $year->slug,
                    'is_active' => $year->is_active,
                    'sort_order' => $year->sort_order,
                    'subjects' => $year->subjects->map(fn ($subject) => [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'slug' => $subject->slug,
                        'color' => $subject->color,
                        'is_active' => $subject->is_active,
                        'sort_order' => $subject->sort_order,
                        'courses' => $subject->courses->map(fn ($course) => [
                            'id' => $course->id,
                            'title' => $course->title,
                            'slug' => $course->slug,
                            'description' => $course->description,
                            'thumbnail' => $course->thumbnail,
                            'is_published' => $course->is_published,
                            'modules' => $course->modules->map(fn ($module) => [
                                'id' => $module->id,
                                'title' => $module->title,
                                'description' => $module->description,
                                'sort_order' => $module->sort_order,
                                'lessons' => $module->lessons->map(fn ($lesson) => [
                                    'id' => $lesson->id,
                                    'title' => $lesson->title,
                                    'notes' => $lesson->notes,
                                    'sort_order' => $lesson->sort_order,
                                    'is_free' => $lesson->is_free,
                                    'is_published' => $lesson->is_published,
                                    'video_provider' => $lesson->video_provider,
                                    'video_id' => $lesson->video_id,
                                    'video_thumbnail' => $lesson->video_thumbnail,
                                    'subtitle_url' => $lesson->subtitle_url,
                                    'duration_seconds' => $lesson->duration_seconds,
                                    'worksheets' => $lesson->worksheets->map(fn ($worksheet) => [
                                        'id' => $worksheet->id,
                                        'title' => $worksheet->title,
                                        'file_url' => $worksheet->file_url,
                                        'sort_order' => $worksheet->sort_order,
                                    ]),
                                    'quizzes' => $lesson->quizzes->map(fn ($quiz) => [
                                        'id' => $quiz->id,
                                        'title' => $quiz->title,
                                        'type' => $quiz->type,
                                        'time_limit_minutes' => $quiz->time_limit_minutes,
                                        'max_score' => $quiz->max_score,
                                        'shuffle_questions' => $quiz->shuffle_questions,
                                        'is_published' => $quiz->is_published,
                                        'questions' => $quiz->questions->map(fn ($question) => [
                                            'id' => $question->id,
                                            'type' => $question->type,
                                            'text' => $question->text,
                                            'options' => $question->options ?? [],
                                            'correct_answer' => $question->correct_answer,
                                            'points' => $question->points,
                                            'bank_tag' => $question->bank_tag,
                                            'sort_order' => $question->sort_order,
                                        ]),
                                    ]),
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
            'classYears' => ClassYear::query()
                ->orderBy('sort_order')
                ->get(['id', 'name']),
            'subjects' => Subject::query()
                ->with('classYear:id,name')
                ->orderBy('class_year_id')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Subject $subject) => [
                    'id' => $subject->id,
                    'label' => $subject->classYear?->name.' · '.$subject->name,
                ]),
            'courses' => Course::query()
                ->with('subject.classYear')
                ->orderBy('subject_id')
                ->orderBy('title')
                ->get()
                ->map(fn (Course $course) => [
                    'id' => $course->id,
                    'label' => $course->subject?->classYear?->name.' · '.$course->subject?->name.' · '.$course->title,
                ]),
            'lessons' => Lesson::query()
                ->with('module.course.subject.classYear')
                ->orderBy('module_id')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Lesson $lesson) => [
                    'id' => $lesson->id,
                    'label' => $lesson->module?->course?->subject?->classYear?->name.' · '.$lesson->module?->course?->subject?->name.' · '.$lesson->module?->course?->title.' · '.$lesson->title,
                ]),
            'quizzes' => Quiz::query()
                ->with('lesson.module.course.subject.classYear')
                ->orderBy('lesson_id')
                ->orderBy('title')
                ->get()
                ->map(fn (Quiz $quiz) => [
                    'id' => $quiz->id,
                    'label' => $quiz->lesson?->module?->course?->subject?->classYear?->name.' · '.$quiz->lesson?->title.' · '.$quiz->title,
                ]),
        ]);
    }

    public function storeClassYear(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('class_years', 'slug')],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $classYear = ClassYear::query()->create([
            ...$validated,
            'slug' => $validated['slug'] ?: Str::slug($validated['name']),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return $this->recordAndBack($request, 'class_year.created', $classYear, 'Class year created.');
    }

    public function updateClassYear(Request $request, ClassYear $classYear)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('class_years', 'slug')->ignore($classYear)],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        return $this->updateRecord($request, $classYear, [
            ...$validated,
            'slug' => $validated['slug'] ?: Str::slug($validated['name']),
            'sort_order' => $validated['sort_order'] ?? 0,
        ], 'class_year.updated', 'Class year updated.');
    }

    public function destroyClassYear(Request $request, ClassYear $classYear)
    {
        return $this->deleteRecord($request, $classYear, 'class_year.deleted', 'Class year deleted.');
    }

    public function storeSubject(Request $request)
    {
        $validated = $request->validate([
            'class_year_id' => ['required', 'exists:class_years,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('subjects', 'slug')],
            'color' => ['required', 'string', 'max:20'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $subject = Subject::query()->create([
            ...$validated,
            'slug' => $validated['slug'] ?: Str::slug($validated['name']),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return $this->recordAndBack($request, 'subject.created', $subject, 'Subject created.');
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'class_year_id' => ['required', 'exists:class_years,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('subjects', 'slug')->ignore($subject)],
            'color' => ['required', 'string', 'max:20'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        return $this->updateRecord($request, $subject, [
            ...$validated,
            'slug' => $validated['slug'] ?: Str::slug($validated['name']),
            'sort_order' => $validated['sort_order'] ?? 0,
        ], 'subject.updated', 'Subject updated.');
    }

    public function destroySubject(Request $request, Subject $subject)
    {
        return $this->deleteRecord($request, $subject, 'subject.deleted', 'Subject deleted.');
    }

    public function storeCourse(Request $request)
    {
        $validated = $this->validateCourse($request);
        $course = Course::query()->create($validated);

        return $this->recordAndBack($request, 'course.created', $course, 'Course created.');
    }

    public function updateCourse(Request $request, Course $course)
    {
        return $this->updateRecord($request, $course, $this->validateCourse($request, $course), 'course.updated', 'Course updated.');
    }

    public function destroyCourse(Request $request, Course $course)
    {
        return $this->deleteRecord($request, $course, 'course.deleted', 'Course deleted.');
    }

    public function storeModule(Request $request)
    {
        $validated = $this->validateModule($request);
        $module = Module::query()->create($validated);

        return $this->recordAndBack($request, 'module.created', $module, 'Module created.');
    }

    public function updateModule(Request $request, Module $module)
    {
        return $this->updateRecord($request, $module, $this->validateModule($request), 'module.updated', 'Module updated.');
    }

    public function destroyModule(Request $request, Module $module)
    {
        return $this->deleteRecord($request, $module, 'module.deleted', 'Module deleted.');
    }

    public function storeLesson(Request $request)
    {
        $validated = $this->validateLesson($request);

        $sortOrder = $validated['sort_order'] ?? (int) Lesson::query()
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

    public function updateLesson(Request $request, Lesson $lesson)
    {
        return $this->updateRecord($request, $lesson, $this->validateLesson($request), 'lesson.updated', 'Lesson updated.');
    }

    public function destroyLesson(Request $request, Lesson $lesson)
    {
        return $this->deleteRecord($request, $lesson, 'lesson.deleted', 'Lesson deleted.');
    }

    public function storeWorksheet(Request $request)
    {
        $validated = $this->validateWorksheet($request);
        $worksheet = Worksheet::query()->create($validated);

        return $this->recordAndBack($request, 'worksheet.created', $worksheet, 'Worksheet created.');
    }

    public function updateWorksheet(Request $request, Worksheet $worksheet)
    {
        return $this->updateRecord($request, $worksheet, $this->validateWorksheet($request), 'worksheet.updated', 'Worksheet updated.');
    }

    public function destroyWorksheet(Request $request, Worksheet $worksheet)
    {
        return $this->deleteRecord($request, $worksheet, 'worksheet.deleted', 'Worksheet deleted.');
    }

    public function storeQuiz(Request $request)
    {
        $validated = $this->validateQuiz($request);
        $quiz = Quiz::query()->create($validated);

        return $this->recordAndBack($request, 'quiz.created', $quiz, 'Quiz created.');
    }

    public function updateQuiz(Request $request, Quiz $quiz)
    {
        return $this->updateRecord($request, $quiz, $this->validateQuiz($request), 'quiz.updated', 'Quiz updated.');
    }

    public function destroyQuiz(Request $request, Quiz $quiz)
    {
        return $this->deleteRecord($request, $quiz, 'quiz.deleted', 'Quiz deleted.');
    }

    public function storeQuestion(Request $request)
    {
        $validated = $this->validateQuestion($request);
        $question = Question::query()->create($validated);

        return $this->recordAndBack($request, 'question.created', $question, 'Question created.');
    }

    public function updateQuestion(Request $request, Question $question)
    {
        return $this->updateRecord($request, $question, $this->validateQuestion($request), 'question.updated', 'Question updated.');
    }

    public function destroyQuestion(Request $request, Question $question)
    {
        return $this->deleteRecord($request, $question, 'question.deleted', 'Question deleted.');
    }

    private function validateCourse(Request $request, ?Course $course = null): array
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                $course
                    ? Rule::unique('courses', 'slug')->ignore($course)
                    : Rule::unique('courses', 'slug'),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'is_published' => ['required', 'boolean'],
        ]);

        return [
            ...$validated,
            'slug' => $validated['slug'] ?: Str::slug($validated['title']),
        ];
    }

    private function validateModule(Request $request): array
    {
        return $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function validateLesson(Request $request): array
    {
        return $request->validate([
            'module_id' => ['required', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'duration_seconds' => ['required', 'integer', 'min:60', 'max:7200'],
            'video_provider' => ['nullable', 'string', 'max:40'],
            'video_id' => ['nullable', 'string', 'max:255'],
            'video_thumbnail' => ['nullable', 'string', 'max:255'],
            'subtitle_url' => ['nullable', 'string', 'max:255'],
            'is_free' => ['required', 'boolean'],
            'is_published' => ['required', 'boolean'],
        ]);
    }

    private function validateWorksheet(Request $request): array
    {
        return $request->validate([
            'lesson_id' => ['required', 'exists:lessons,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'file_url' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function validateQuiz(Request $request): array
    {
        return $request->validate([
            'lesson_id' => ['required', 'exists:lessons,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in([
                Quiz::TYPE_PRACTICE,
                Quiz::TYPE_GRADED,
                Quiz::TYPE_END_OF_TOPIC,
                Quiz::TYPE_END_OF_YEAR,
            ])],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'max_score' => ['required', 'integer', 'min:1', 'max:10000'],
            'shuffle_questions' => ['required', 'boolean'],
            'is_published' => ['required', 'boolean'],
        ]);
    }

    private function validateQuestion(Request $request): array
    {
        $validated = $request->validate([
            'quiz_id' => ['required', 'exists:quizzes,id'],
            'type' => ['required', Rule::in([
                Question::TYPE_MCQ,
                Question::TYPE_TRUE_FALSE,
                Question::TYPE_SHORT_ANSWER,
            ])],
            'text' => ['required', 'string', 'max:5000'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string', 'max:500'],
            'correct_answer' => ['nullable', 'string', 'max:1000'],
            'points' => ['required', 'integer', 'min:1', 'max:1000'],
            'bank_tag' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['options'] = collect($validated['options'] ?? [])
            ->filter(fn ($option) => filled($option))
            ->values()
            ->all();

        return $validated;
    }

    private function updateRecord(Request $request, $record, array $data, string $action, string $message)
    {
        $old = $record->getOriginal();
        $record->update($data);

        AuditLog::record($action, $record, [
            'old' => $old,
            'new' => $record->fresh()?->toArray(),
        ], $request->user());

        return back()->with('success', $message);
    }

    private function deleteRecord(Request $request, $record, string $action, string $message)
    {
        $old = $record->toArray();

        AuditLog::record($action, $record, ['old' => $old], $request->user());
        $record->delete();

        return back()->with('success', $message);
    }

    private function recordAndBack(Request $request, string $action, $record, string $message)
    {
        AuditLog::record($action, $record, ['new' => $record->toArray()], $request->user());

        return back()->with('success', $message);
    }
}

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import InputError from '@/Components/InputError';
import TextInput from '@/Components/TextInput';
import { Head, router, useForm, usePage } from '@inertiajs/react';

const quizTypes = [
    ['practice', 'Practice'],
    ['graded', 'Graded'],
    ['end_of_topic', 'End of topic'],
    ['end_of_year', 'End of year'],
];

const questionTypes = [
    ['mcq', 'MCQ'],
    ['true_false', 'True/false'],
    ['short_answer', 'Short answer'],
];

function fieldValue(field, value) {
    if (field.type === 'checkbox') {
        return Boolean(value);
    }

    if (field.type === 'select') {
        return String(value ?? field.options[0]?.value ?? '');
    }

    if (field.type === 'options') {
        return Array.isArray(value) ? value : [];
    }

    return value ?? '';
}

function defaultData(fields, initial = {}) {
    return fields.reduce((values, field) => ({
        ...values,
        [field.name]: fieldValue(field, initial[field.name] ?? field.default),
    }), {});
}

function FormField({ field, data, setData, errors, prefix }) {
    const id = `${prefix}_${field.name}`;

    if (field.type === 'select') {
        return (
            <div className={field.wide ? 'md:col-span-2' : ''}>
                <label className="label" htmlFor={id}>{field.label}</label>
                <select
                    id={id}
                    value={data[field.name]}
                    onChange={(e) => setData(field.name, e.target.value)}
                    className="input mt-1"
                >
                    {field.options.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>
                <InputError message={errors[field.name]} className="mt-2" />
            </div>
        );
    }

    if (field.type === 'textarea' || field.type === 'options') {
        return (
            <div className="md:col-span-2">
                <label className="label" htmlFor={id}>{field.label}</label>
                <textarea
                    id={id}
                    value={field.type === 'options' ? data[field.name].join('\n') : data[field.name]}
                    onChange={(e) => setData(
                        field.name,
                        field.type === 'options'
                            ? e.target.value.split('\n')
                            : e.target.value,
                    )}
                    className="input mt-1 min-h-20"
                    placeholder={field.placeholder}
                />
                <InputError message={errors[field.name]} className="mt-2" />
            </div>
        );
    }

    if (field.type === 'checkbox') {
        return (
            <label className="mt-6 inline-flex items-center gap-2 text-sm text-slate-600">
                <input
                    type="checkbox"
                    checked={data[field.name]}
                    onChange={(e) => setData(field.name, e.target.checked)}
                    className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                />
                {field.label}
                <InputError message={errors[field.name]} />
            </label>
        );
    }

    return (
        <div className={field.wide ? 'md:col-span-2' : ''}>
            <label className="label" htmlFor={id}>{field.label}</label>
            <TextInput
                id={id}
                type={field.type === 'number' ? 'number' : field.type === 'color' ? 'color' : 'text'}
                min={field.min}
                value={data[field.name]}
                onChange={(e) => setData(field.name, e.target.value)}
                className="mt-1 block w-full"
                placeholder={field.placeholder}
            />
            <InputError message={errors[field.name]} className="mt-2" />
        </div>
    );
}

function EntityForm({ title, routeName, fields, initial = {}, button = 'Save', method = 'post', onSuccess }) {
    const form = useForm(defaultData(fields, initial));

    const submit = (e) => {
        e.preventDefault();
        form[method](route(routeName, initial.routeParams ?? {}), {
            preserveScroll: true,
            onSuccess: () => {
                if (method === 'post') {
                    form.reset();
                }
                onSuccess?.();
            },
        });
    };

    return (
        <form onSubmit={submit} className="rounded-lg border border-slate-200 bg-white p-4">
            <h3 className="font-semibold text-slate-900">{title}</h3>
            <div className="mt-4 grid gap-4 md:grid-cols-2">
                {fields.map((field) => (
                    <FormField
                        key={field.name}
                        field={field}
                        data={form.data}
                        setData={form.setData}
                        errors={form.errors}
                        prefix={`${routeName}_${initial.id ?? 'new'}`}
                    />
                ))}
            </div>
            <div className="mt-4 flex items-center justify-end gap-3">
                {form.recentlySuccessful && (
                    <span className="text-xs font-semibold text-emerald-600">Saved</span>
                )}
                <button type="submit" disabled={form.processing} className="btn-primary">
                    {form.processing ? 'Saving...' : button}
                </button>
            </div>
        </form>
    );
}

function DeleteButton({ routeName, routeParams, label }) {
    const destroy = () => {
        if (!window.confirm(`Delete ${label}? Child records may also be deleted.`)) {
            return;
        }

        router.delete(route(routeName, routeParams), {
            preserveScroll: true,
        });
    };

    return (
        <button type="button" onClick={destroy} className="btn-secondary border-rose-200 text-rose-700 hover:bg-rose-50">
            Delete
        </button>
    );
}

function pickOptions(items) {
    return items.map((item) => ({ value: String(item.id), label: item.label ?? item.name }));
}

function StructureItem({ title, meta, children, editForm, deleteButton }) {
    return (
        <div className="rounded-lg border border-slate-200 bg-white p-4">
            <div className="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <p className="font-semibold text-slate-900">{title}</p>
                    {meta && <p className="mt-1 text-xs text-slate-500">{meta}</p>}
                </div>
                {deleteButton}
            </div>
            <div className="mt-4">{editForm}</div>
            {children && <div className="mt-4 space-y-3">{children}</div>}
        </div>
    );
}

export default function Content({ years, classYears, subjects, courses, modules, lessons, quizzes }) {
    const { flash } = usePage().props;

    const classYearOptions = pickOptions(classYears);
    const subjectOptions = pickOptions(subjects);
    const courseOptions = pickOptions(courses);
    const moduleOptions = pickOptions(modules);
    const lessonOptions = pickOptions(lessons);
    const quizOptions = pickOptions(quizzes);

    const classYearFields = [
        { name: 'name', label: 'Name' },
        { name: 'slug', label: 'Slug', placeholder: 'Auto from name' },
        { name: 'sort_order', label: 'Sort order', type: 'number', default: 0, min: 0 },
        { name: 'is_active', label: 'Active', type: 'checkbox', default: true },
    ];
    const subjectFields = [
        { name: 'class_year_id', label: 'Class year', type: 'select', options: classYearOptions },
        { name: 'name', label: 'Name' },
        { name: 'slug', label: 'Slug', placeholder: 'Auto from name' },
        { name: 'color', label: 'Color', type: 'color', default: '#4f46e5' },
        { name: 'sort_order', label: 'Sort order', type: 'number', default: 0, min: 0 },
        { name: 'is_active', label: 'Active', type: 'checkbox', default: true },
    ];
    const courseFields = [
        { name: 'subject_id', label: 'Subject', type: 'select', options: subjectOptions, wide: true },
        { name: 'title', label: 'Title' },
        { name: 'slug', label: 'Slug', placeholder: 'Auto from title' },
        { name: 'thumbnail', label: 'Thumbnail URL' },
        { name: 'description', label: 'Description', type: 'textarea' },
        { name: 'is_published', label: 'Published', type: 'checkbox', default: false },
    ];
    const moduleFields = [
        { name: 'course_id', label: 'Course', type: 'select', options: courseOptions, wide: true },
        { name: 'title', label: 'Title' },
        { name: 'sort_order', label: 'Sort order', type: 'number', default: 0, min: 0 },
        { name: 'description', label: 'Description', type: 'textarea' },
    ];
    const lessonFields = [
        { name: 'module_id', label: 'Module', type: 'select', options: moduleOptions, wide: true },
        { name: 'title', label: 'Title' },
        { name: 'sort_order', label: 'Sort order', type: 'number', default: 0, min: 0 },
        { name: 'duration_seconds', label: 'Duration seconds', type: 'number', default: 600, min: 60 },
        { name: 'video_provider', label: 'Video provider', default: 'mux' },
        { name: 'video_id', label: 'Video ID' },
        { name: 'video_thumbnail', label: 'Thumbnail URL', default: '/images/tuition-hero.png' },
        { name: 'subtitle_url', label: 'Subtitle URL' },
        { name: 'notes', label: 'Notes', type: 'textarea' },
        { name: 'is_free', label: 'Free preview', type: 'checkbox', default: false },
        { name: 'is_published', label: 'Published', type: 'checkbox', default: true },
    ];
    const worksheetFields = [
        { name: 'lesson_id', label: 'Lesson', type: 'select', options: lessonOptions, wide: true },
        { name: 'title', label: 'Title' },
        { name: 'file_url', label: 'File URL' },
        { name: 'sort_order', label: 'Sort order', type: 'number', default: 0, min: 0 },
    ];
    const quizFields = [
        { name: 'lesson_id', label: 'Lesson', type: 'select', options: lessonOptions, wide: true },
        { name: 'title', label: 'Title' },
        { name: 'type', label: 'Type', type: 'select', options: quizTypes.map(([value, label]) => ({ value, label })) },
        { name: 'time_limit_minutes', label: 'Time limit minutes', type: 'number', min: 1 },
        { name: 'max_score', label: 'Max score', type: 'number', default: 100, min: 1 },
        { name: 'shuffle_questions', label: 'Shuffle questions', type: 'checkbox', default: true },
        { name: 'is_published', label: 'Published', type: 'checkbox', default: true },
    ];
    const questionFields = [
        { name: 'quiz_id', label: 'Quiz', type: 'select', options: quizOptions, wide: true },
        { name: 'type', label: 'Type', type: 'select', options: questionTypes.map(([value, label]) => ({ value, label })) },
        { name: 'points', label: 'Points', type: 'number', default: 1, min: 1 },
        { name: 'bank_tag', label: 'Bank tag' },
        { name: 'correct_answer', label: 'Correct answer' },
        { name: 'text', label: 'Question text', type: 'textarea' },
        { name: 'options', label: 'Options', type: 'options', placeholder: 'One option per line' },
    ];

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">Content Management</h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        Manage class, subject, course, module, lesson, worksheet, quiz and question records.
                    </p>
                </div>
            }
        >
            <Head title="Content Management" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    {flash.success && (
                        <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {flash.success}
                        </div>
                    )}

                    <div className="grid gap-4 xl:grid-cols-2">
                        <EntityForm title="Create class year" routeName="admin.content.class-years.store" fields={classYearFields} button="Create class" />
                        <EntityForm title="Create subject" routeName="admin.content.subjects.store" fields={subjectFields} button="Create subject" />
                        <EntityForm title="Create course" routeName="admin.content.courses.store" fields={courseFields} button="Create course" />
                        <EntityForm title="Create module" routeName="admin.content.modules.store" fields={moduleFields} button="Create module" />
                        <EntityForm title="Create lesson" routeName="admin.content.lessons.store" fields={lessonFields} button="Create lesson" />
                        <EntityForm title="Create worksheet" routeName="admin.content.worksheets.store" fields={worksheetFields} button="Create worksheet" />
                        <EntityForm title="Create quiz" routeName="admin.content.quizzes.store" fields={quizFields} button="Create quiz" />
                        <EntityForm title="Create question" routeName="admin.content.questions.store" fields={questionFields} button="Create question" />
                    </div>

                    <div className="space-y-4">
                        {years.map((year) => (
                            <StructureItem
                                key={year.id}
                                title={year.name}
                                meta={`Slug: ${year.slug} · Order: ${year.sort_order}`}
                                editForm={<EntityForm title="Edit class year" routeName="admin.content.class-years.update" method="patch" fields={classYearFields} initial={{ ...year, routeParams: { classYear: year.id } }} />}
                                deleteButton={<DeleteButton routeName="admin.content.class-years.destroy" routeParams={{ classYear: year.id }} label={year.name} />}
                            >
                                {year.subjects.map((subject) => (
                                    <StructureItem
                                        key={subject.id}
                                        title={subject.name}
                                        meta={`Slug: ${subject.slug} · Order: ${subject.sort_order}`}
                                        editForm={<EntityForm title="Edit subject" routeName="admin.content.subjects.update" method="patch" fields={subjectFields} initial={{ ...subject, class_year_id: String(year.id), routeParams: { subject: subject.id } }} />}
                                        deleteButton={<DeleteButton routeName="admin.content.subjects.destroy" routeParams={{ subject: subject.id }} label={subject.name} />}
                                    >
                                        {subject.courses.map((course) => (
                                            <StructureItem
                                                key={course.id}
                                                title={course.title}
                                                meta={course.is_published ? 'Published' : 'Draft'}
                                                editForm={<EntityForm title="Edit course" routeName="admin.content.courses.update" method="patch" fields={courseFields} initial={{ ...course, subject_id: String(subject.id), routeParams: { course: course.id } }} />}
                                                deleteButton={<DeleteButton routeName="admin.content.courses.destroy" routeParams={{ course: course.id }} label={course.title} />}
                                            >
                                                {course.modules.map((module) => (
                                                    <StructureItem
                                                        key={module.id}
                                                        title={module.title}
                                                        meta={`Order: ${module.sort_order}`}
                                                        editForm={<EntityForm title="Edit module" routeName="admin.content.modules.update" method="patch" fields={moduleFields} initial={{ ...module, course_id: String(course.id), routeParams: { module: module.id } }} />}
                                                        deleteButton={<DeleteButton routeName="admin.content.modules.destroy" routeParams={{ module: module.id }} label={module.title} />}
                                                    >
                                                        {module.lessons.map((lesson) => (
                                                            <StructureItem
                                                                key={lesson.id}
                                                                title={lesson.title}
                                                                meta={`Order: ${lesson.sort_order} · ${lesson.duration_seconds}s`}
                                                                editForm={<EntityForm title="Edit lesson" routeName="admin.content.lessons.update" method="patch" fields={lessonFields} initial={{ ...lesson, module_id: String(module.id), routeParams: { lesson: lesson.id } }} />}
                                                                deleteButton={<DeleteButton routeName="admin.content.lessons.destroy" routeParams={{ lesson: lesson.id }} label={lesson.title} />}
                                                            >
                                                                <div className="flex flex-wrap gap-2">
                                                                    {lesson.is_free && <Badge>Free</Badge>}
                                                                    <Badge status={lesson.is_published ? 'active' : 'pending'}>
                                                                        {lesson.is_published ? 'Published' : 'Draft'}
                                                                    </Badge>
                                                                </div>
                                                                {lesson.worksheets.map((worksheet) => (
                                                                    <StructureItem
                                                                        key={worksheet.id}
                                                                        title={worksheet.title || 'Worksheet'}
                                                                        meta={worksheet.file_url}
                                                                        editForm={<EntityForm title="Edit worksheet" routeName="admin.content.worksheets.update" method="patch" fields={worksheetFields} initial={{ ...worksheet, lesson_id: String(lesson.id), routeParams: { worksheet: worksheet.id } }} />}
                                                                        deleteButton={<DeleteButton routeName="admin.content.worksheets.destroy" routeParams={{ worksheet: worksheet.id }} label={worksheet.title || 'worksheet'} />}
                                                                    />
                                                                ))}
                                                                {lesson.quizzes.map((quiz) => (
                                                                    <StructureItem
                                                                        key={quiz.id}
                                                                        title={quiz.title}
                                                                        meta={`${quiz.type} · ${quiz.max_score} marks`}
                                                                        editForm={<EntityForm title="Edit quiz" routeName="admin.content.quizzes.update" method="patch" fields={quizFields} initial={{ ...quiz, lesson_id: String(lesson.id), routeParams: { quiz: quiz.id } }} />}
                                                                        deleteButton={<DeleteButton routeName="admin.content.quizzes.destroy" routeParams={{ quiz: quiz.id }} label={quiz.title} />}
                                                                    >
                                                                        {quiz.questions.map((question) => (
                                                                            <StructureItem
                                                                                key={question.id}
                                                                                title={question.text}
                                                                                meta={`${question.type} · ${question.points} points`}
                                                                                editForm={<EntityForm title="Edit question" routeName="admin.content.questions.update" method="patch" fields={questionFields} initial={{ ...question, quiz_id: String(quiz.id), routeParams: { question: question.id } }} />}
                                                                                deleteButton={<DeleteButton routeName="admin.content.questions.destroy" routeParams={{ question: question.id }} label="question" />}
                                                                            />
                                                                        ))}
                                                                    </StructureItem>
                                                                ))}
                                                            </StructureItem>
                                                        ))}
                                                    </StructureItem>
                                                ))}
                                            </StructureItem>
                                        ))}
                                    </StructureItem>
                                ))}
                            </StructureItem>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

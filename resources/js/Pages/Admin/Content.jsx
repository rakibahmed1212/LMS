import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import InputError from '@/Components/InputError';
import TextInput from '@/Components/TextInput';
import { Head, useForm, usePage } from '@inertiajs/react';

function CreateLessonForm({ modules }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        module_id: modules[0]?.id ? String(modules[0].id) : '',
        title: '',
        notes: '',
        duration_seconds: 600,
        video_provider: 'mux',
        video_id: '',
        video_thumbnail: '/images/tuition-hero.png',
        is_free: false,
        is_published: true,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('admin.content.lessons.store'), {
            preserveScroll: true,
            onSuccess: () => reset('title', 'notes', 'video_id'),
        });
    };

    return (
        <form onSubmit={submit} className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 className="font-semibold text-slate-900">Create lesson</h3>
            <div className="mt-4 grid gap-4 lg:grid-cols-2">
                <div className="lg:col-span-2">
                    <label className="label" htmlFor="module_id">Module</label>
                    <select
                        id="module_id"
                        value={data.module_id}
                        onChange={(e) => setData('module_id', e.target.value)}
                        className="input mt-1"
                    >
                        {modules.map((module) => (
                            <option key={module.id} value={module.id}>
                                {module.label}
                            </option>
                        ))}
                    </select>
                    <InputError message={errors.module_id} className="mt-2" />
                </div>
                <div>
                    <label className="label" htmlFor="title">Title</label>
                    <TextInput
                        id="title"
                        value={data.title}
                        onChange={(e) => setData('title', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.title} className="mt-2" />
                </div>
                <div>
                    <label className="label" htmlFor="duration">Duration seconds</label>
                    <TextInput
                        id="duration"
                        type="number"
                        min="60"
                        value={data.duration_seconds}
                        onChange={(e) => setData('duration_seconds', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.duration_seconds} className="mt-2" />
                </div>
                <div>
                    <label className="label" htmlFor="video_provider">Video provider</label>
                    <TextInput
                        id="video_provider"
                        value={data.video_provider}
                        onChange={(e) => setData('video_provider', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.video_provider} className="mt-2" />
                </div>
                <div>
                    <label className="label" htmlFor="video_id">Video ID</label>
                    <TextInput
                        id="video_id"
                        value={data.video_id}
                        onChange={(e) => setData('video_id', e.target.value)}
                        className="mt-1 block w-full"
                        placeholder="Provider video reference"
                    />
                    <InputError message={errors.video_id} className="mt-2" />
                </div>
                <div className="lg:col-span-2">
                    <label className="label" htmlFor="notes">Lesson notes</label>
                    <textarea
                        id="notes"
                        value={data.notes}
                        onChange={(e) => setData('notes', e.target.value)}
                        className="input mt-1 min-h-24"
                    />
                    <InputError message={errors.notes} className="mt-2" />
                </div>
            </div>
            <div className="mt-4 flex flex-wrap items-center justify-between gap-3">
                <div className="flex flex-wrap gap-4">
                    <label className="inline-flex items-center gap-2 text-sm text-slate-600">
                        <input
                            type="checkbox"
                            checked={data.is_free}
                            onChange={(e) => setData('is_free', e.target.checked)}
                            className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        Free preview
                    </label>
                    <label className="inline-flex items-center gap-2 text-sm text-slate-600">
                        <input
                            type="checkbox"
                            checked={data.is_published}
                            onChange={(e) => setData('is_published', e.target.checked)}
                            className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        Published
                    </label>
                </div>
                <button type="submit" disabled={processing || !data.title || !data.module_id} className="btn-primary">
                    {processing ? 'Creating...' : 'Create lesson'}
                </button>
            </div>
        </form>
    );
}

export default function Content({ years, modules }) {
    const { flash } = usePage().props;

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        Content Management
                    </h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        Manage class, subject, course, module and lesson content.
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
                    <CreateLessonForm modules={modules} />

                    <div className="space-y-4">
                        {years.map((year) => (
                            <section key={year.id} className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                                <h3 className="font-semibold text-slate-950">{year.name}</h3>
                                <div className="mt-4 grid gap-4 lg:grid-cols-2">
                                    {year.subjects.map((subject) => (
                                        <div key={subject.id} className="rounded-lg border border-slate-200 p-4">
                                            <h4 className="font-semibold text-slate-900">{subject.name}</h4>
                                            <div className="mt-3 space-y-3">
                                                {subject.courses.map((course) => (
                                                    <div key={course.id} className="rounded-lg bg-slate-50 p-3">
                                                        <p className="text-sm font-semibold text-slate-900">{course.title}</p>
                                                        <div className="mt-2 space-y-2">
                                                            {course.modules.map((module) => (
                                                                <div key={module.id}>
                                                                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">{module.title}</p>
                                                                    <div className="mt-1 space-y-1">
                                                                        {module.lessons.map((lesson) => (
                                                                            <div key={lesson.id} className="flex items-center justify-between gap-3 rounded bg-white px-3 py-2 text-sm">
                                                                                <span className="truncate text-slate-700">{lesson.sort_order}. {lesson.title}</span>
                                                                                <div className="flex gap-2">
                                                                                    {lesson.is_free && <Badge>Free</Badge>}
                                                                                    <Badge status={lesson.is_published ? 'active' : 'pending'}>
                                                                                        {lesson.is_published ? 'Published' : 'Draft'}
                                                                                    </Badge>
                                                                                </div>
                                                                            </div>
                                                                        ))}
                                                                    </div>
                                                                </div>
                                                            ))}
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </section>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

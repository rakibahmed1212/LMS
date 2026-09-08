import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import InputError from '@/Components/InputError';
import TextInput from '@/Components/TextInput';
import MarketingLayout from '@/Layouts/MarketingLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

function ResourceList({ title, empty, items, render }) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex items-center justify-between gap-3">
                <h3 className="font-semibold text-slate-900">{title}</h3>
                <span className="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-500">
                    {items.length}
                </span>
            </div>
            {items.length === 0 ? (
                <p className="mt-3 text-sm text-slate-500">{empty}</p>
            ) : (
                <div className="mt-4 space-y-3">{items.map(render)}</div>
            )}
        </section>
    );
}

function ProgressPanel({ lesson, student, progress }) {
    const initialWatched = progress?.watched_seconds ?? 0;
    const { data, setData, post, processing, errors } = useForm({
        student_id: student?.id ?? '',
        watched_seconds: initialWatched,
        last_position_seconds: progress?.last_position_seconds ?? initialWatched,
    });

    const save = (e) => {
        e.preventDefault();
        post(route('lessons.progress', { lesson: lesson.id }), {
            preserveScroll: true,
        });
    };

    const complete = () => {
        post(route('lessons.complete', { lesson: lesson.id }), {
            data: { student_id: student.id },
            preserveScroll: true,
        });
    };

    if (!student) {
        return null;
    }

    return (
        <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex items-center justify-between gap-3">
                <h3 className="font-semibold text-slate-900">Learning progress</h3>
                <span className="text-sm font-semibold text-cyan-700">
                    {progress?.watch_percent ?? 0}%
                </span>
            </div>
            <div className="mt-3 h-2 overflow-hidden rounded-full bg-slate-200">
                <div
                    className="h-full rounded-full bg-cyan-600"
                    style={{ width: `${progress?.watch_percent ?? 0}%` }}
                />
            </div>
            <form onSubmit={save} className="mt-4 space-y-3">
                <div>
                    <label className="label" htmlFor="watched_seconds">
                        Watched seconds
                    </label>
                    <TextInput
                        id="watched_seconds"
                        type="number"
                        min="0"
                        value={data.watched_seconds}
                        onChange={(e) => {
                            setData('watched_seconds', e.target.value);
                            setData('last_position_seconds', e.target.value);
                        }}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.watched_seconds} className="mt-2" />
                </div>
                <button type="submit" disabled={processing} className="btn-secondary w-full">
                    Save progress
                </button>
                <button
                    type="button"
                    disabled={processing || progress?.completed}
                    onClick={complete}
                    className="btn-primary w-full"
                >
                    {progress?.completed ? 'Completed' : 'Mark complete'}
                </button>
            </form>
        </section>
    );
}

function QuestionPanel({ lesson, student, discussions }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        student_id: student?.id ?? '',
        message: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('lessons.questions.store', { lesson: lesson.id }), {
            preserveScroll: true,
            onSuccess: () => reset('message'),
        });
    };

    return (
        <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 className="font-semibold text-slate-900">Q&A</h3>
            {student && (
                <form onSubmit={submit} className="mt-4 space-y-3">
                    <textarea
                        value={data.message}
                        onChange={(e) => setData('message', e.target.value)}
                        className="input min-h-24"
                        placeholder="Ask a question for the tutor"
                    />
                    <InputError message={errors.message} />
                    <button type="submit" disabled={processing || !data.message} className="btn-primary w-full">
                        Post question
                    </button>
                </form>
            )}
            <div className="mt-5 space-y-3">
                {discussions.length === 0 ? (
                    <p className="text-sm text-slate-500">No questions yet.</p>
                ) : (
                    discussions.map((item) => (
                        <div key={item.id} className="rounded-lg border border-slate-200 p-3">
                            <p className="text-sm text-slate-700">{item.message}</p>
                            <p className="mt-2 text-xs text-slate-400">
                                {item.author} · {item.created_at}
                            </p>
                        </div>
                    ))
                )}
            </div>
        </section>
    );
}

function VideoShell({ lesson, hasAccess, lockReason }) {
    const scheduled = lockReason === 'scheduled';

    return (
        <div className="relative flex aspect-video items-center justify-center overflow-hidden bg-slate-950 text-white">
            {lesson.video_thumbnail && (
                <img
                    src={lesson.video_thumbnail}
                    alt=""
                    className="absolute inset-0 h-full w-full object-cover opacity-35"
                />
            )}
            <div className="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/70 to-slate-900/30" />
            {hasAccess ? (
                <div className="relative w-full max-w-2xl px-6 text-center">
                    <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-slate-950 shadow-lg">
                        <span className="ml-1 block h-0 w-0 border-y-[11px] border-l-[17px] border-y-transparent border-l-current" />
                    </div>
                    <p className="mt-5 text-xs font-semibold uppercase tracking-wide text-cyan-200">
                        {lesson.video_provider || 'streaming'} lesson
                    </p>
                    <h1 className="mt-2 text-2xl font-semibold text-white">
                        {lesson.title}
                    </h1>
                    <p className="mt-2 font-mono text-xs text-slate-300">
                        Secure video ref: {lesson.video_id || 'pending-provider-id'}
                    </p>
                </div>
            ) : (
                <div className="relative max-w-md px-6 text-center">
                    <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white/10 ring-1 ring-white/20">
                        <span className="text-2xl font-semibold">!</span>
                    </div>
                    <h1 className="mt-4 text-2xl font-semibold text-white">
                        {scheduled ? 'Scheduled lesson' : 'Subscription required'}
                    </h1>
                    <p className="mt-3 text-sm leading-6 text-slate-300">
                        {scheduled
                            ? 'This lesson will unlock when the student reaches this stage of the subscription plan.'
                            : 'This lesson is locked. Subscribe a student to unlock the course.'}
                    </p>
                </div>
            )}
        </div>
    );
}

export default function LessonShow({ lesson, student, progress, hasAccess, lockReason, subscribeUrl }) {
    const { auth, flash } = usePage().props;
    const Layout = auth.user ? AuthenticatedLayout : MarketingLayout;

    const content = (
        <>
            <Head title={lesson.title} />
            <div className="py-8">
                <div className="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-8">
                    <main className="space-y-6">
                        {flash.success && (
                            <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                                {flash.success}
                            </div>
                        )}
                        <section className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                            <VideoShell
                                lesson={lesson}
                                hasAccess={hasAccess}
                                lockReason={lockReason}
                            />
                            <div className="p-5 sm:p-6">
                                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <Badge>{lesson.is_free ? 'Free preview' : 'Subscribers only'}</Badge>
                                        {student && <Badge status={hasAccess ? 'active' : 'blocked'}>{student.name}</Badge>}
                                    </div>
                                    <span className="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-600">
                                        {lesson.duration_minutes} min
                                    </span>
                                </div>
                                <p className="mt-4 leading-7 text-slate-600">
                                    {lesson.notes}
                                </p>
                            </div>
                        </section>

                        {hasAccess && (
                            <div className="grid gap-6 md:grid-cols-2">
                                <ResourceList
                                    title="Worksheets"
                                    empty="No worksheets attached."
                                    items={lesson.worksheets}
                                    render={(worksheet) => (
                                        <a
                                            key={worksheet.id}
                                            href={worksheet.file_url}
                                            className="block rounded-lg border border-slate-200 p-4 text-sm font-medium text-cyan-700 hover:border-cyan-200 hover:bg-cyan-50/40"
                                        >
                                            {worksheet.title || 'Worksheet'}
                                        </a>
                                    )}
                                />

                                <ResourceList
                                    title="Quizzes"
                                    empty="No quizzes published."
                                    items={lesson.quizzes}
                                    render={(quiz) => (
                                        <div key={quiz.id} className="rounded-lg border border-slate-200 p-4">
                                            <p className="text-sm font-medium text-slate-900">{quiz.title}</p>
                                            <p className="mt-1 text-xs capitalize text-slate-500">
                                                {quiz.type} · {quiz.question_count} questions · {quiz.time_limit_minutes} min
                                            </p>
                                        </div>
                                    )}
                                />
                            </div>
                        )}

                        {hasAccess && (
                            <QuestionPanel
                                lesson={lesson}
                                student={student}
                                discussions={lesson.discussions}
                            />
                        )}
                    </main>

                    <aside className="space-y-6">
                        <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 className="font-semibold text-slate-900">Course</h3>
                            <p className="mt-2 text-sm font-medium text-slate-800">
                                {lesson.course.title}
                            </p>
                            <p className="mt-1 text-xs text-slate-500">
                                {lesson.course.year} · {lesson.course.subject} · {lesson.duration_minutes} min
                            </p>
                            <Link
                                href={route('courses.show', { course: lesson.course.slug })}
                                className="btn-secondary mt-4 w-full"
                            >
                                Back to course
                            </Link>
                        </section>

                        {!hasAccess && (
                            <section className="rounded-lg border border-cyan-200 bg-cyan-50 p-5">
                                <h3 className="font-semibold text-cyan-950">
                                    {lockReason === 'scheduled'
                                        ? 'Unlocks by schedule'
                                        : 'Unlock this lesson'}
                                </h3>
                                <p className="mt-2 text-sm leading-6 text-cyan-900">
                                    {lockReason === 'scheduled'
                                        ? 'Paid lessons are paced across the subscription period, so students receive a steady amount of class content.'
                                        : 'Subscribe per child and subject to keep access active.'}
                                </p>
                                {lockReason === 'scheduled' ? null : auth.user && subscribeUrl ? (
                                    <Link href={subscribeUrl} className="btn-primary mt-4 w-full">
                                        Subscribe now
                                    </Link>
                                ) : (
                                    <Link href={route('register')} className="btn-primary mt-4 w-full">
                                        Create parent account
                                    </Link>
                                )}
                            </section>
                        )}

                        {hasAccess && (
                            <>
                                <ProgressPanel
                                    lesson={lesson}
                                    student={student}
                                    progress={progress}
                                />
                                <ResourceList
                                    title="Assignments"
                                    empty="No assignments published."
                                    items={lesson.assignments}
                                    render={(assignment) => (
                                        <div key={assignment.id} className="rounded-lg border border-slate-200 p-4">
                                            <p className="text-sm font-medium text-slate-900">{assignment.title}</p>
                                            <p className="mt-1 text-xs text-slate-500">
                                                Due {assignment.deadline || 'not set'} · {assignment.max_score} marks
                                            </p>
                                            <p className="mt-2 text-sm text-slate-500">{assignment.description}</p>
                                        </div>
                                    )}
                                />
                            </>
                        )}
                    </aside>
                </div>
            </div>
        </>
    );

    return auth.user ? (
        <Layout
            header={
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        {lesson.title}
                    </h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        {lesson.course.year} · {lesson.course.subject}
                    </p>
                </div>
            }
        >
            {content}
        </Layout>
    ) : (
        <Layout>{content}</Layout>
    );
}

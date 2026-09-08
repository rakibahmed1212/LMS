import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import MarketingLayout from '@/Layouts/MarketingLayout';
import { Head, Link, usePage } from '@inertiajs/react';

function ResourceList({ title, empty, items, render }) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 className="font-semibold text-slate-900">{title}</h3>
            {items.length === 0 ? (
                <p className="mt-3 text-sm text-slate-500">{empty}</p>
            ) : (
                <div className="mt-4 space-y-3">{items.map(render)}</div>
            )}
        </section>
    );
}

export default function LessonShow({ lesson, student, hasAccess, subscribeUrl }) {
    const auth = usePage().props.auth;
    const Layout = auth.user ? AuthenticatedLayout : MarketingLayout;

    const content = (
        <>
            <Head title={lesson.title} />
            <div className="py-8">
                <div className="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-8">
                    <main className="space-y-6">
                        <section className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                            <div className="flex aspect-video items-center justify-center bg-slate-950 text-white">
                                {hasAccess ? (
                                    <div className="px-6 text-center">
                                        <p className="text-sm uppercase tracking-wide text-cyan-200">
                                            {lesson.video_provider || 'video'} lesson
                                        </p>
                                        <h1 className="mt-2 text-2xl font-semibold text-white">
                                            {lesson.title}
                                        </h1>
                                        <p className="mt-2 font-mono text-sm text-slate-300">
                                            {lesson.video_id}
                                        </p>
                                    </div>
                                ) : (
                                    <div className="max-w-md px-6 text-center">
                                        <h1 className="text-2xl font-semibold text-white">
                                            Subscription required
                                        </h1>
                                        <p className="mt-3 text-sm leading-6 text-slate-300">
                                            This lesson is locked. Subscribe a student to unlock the full course.
                                        </p>
                                    </div>
                                )}
                            </div>
                            <div className="p-5 sm:p-6">
                                <div className="flex flex-wrap items-center gap-2">
                                    <Badge>{lesson.is_free ? 'Free preview' : 'Subscribers only'}</Badge>
                                    {student && <Badge status={hasAccess ? 'active' : 'blocked'}>{student.name}</Badge>}
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
                                <h3 className="font-semibold text-cyan-950">Unlock this lesson</h3>
                                <p className="mt-2 text-sm leading-6 text-cyan-900">
                                    Subscribe per child and subject to keep access active.
                                </p>
                                {auth.user && subscribeUrl ? (
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

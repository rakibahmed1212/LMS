import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import MarketingLayout from '@/Layouts/MarketingLayout';
import { Head, Link, usePage } from '@inertiajs/react';

function ProgressBar({ value }) {
    return (
        <div className="h-2 overflow-hidden rounded-full bg-slate-200">
            <div
                className="h-full rounded-full bg-cyan-600"
                style={{ width: `${Math.min(100, Math.max(0, value))}%` }}
            />
        </div>
    );
}

function CourseStat({ label, value }) {
    return (
        <div className="rounded-lg border border-slate-200 bg-white p-4">
            <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">
                {label}
            </p>
            <p className="mt-2 text-xl font-semibold text-slate-950">{value}</p>
        </div>
    );
}

export default function CourseShow({ course, children, plans }) {
    const auth = usePage().props.auth;
    const Layout = auth.user ? AuthenticatedLayout : MarketingLayout;
    const unlockedChildren = children.filter((child) => child.has_access);
    const lessonCount = course.modules.reduce(
        (total, module) => total + module.lessons.length,
        0,
    );
    const freePreviewCount = course.modules.reduce(
        (total, module) =>
            total + module.lessons.filter((lesson) => lesson.is_free).length,
        0,
    );

    const content = (
        <>
            <Head title={course.title} />
            <div className="bg-white py-8">
                <div className="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-8">
                    <div className="space-y-6">
                        <section className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                            <div className="relative min-h-[300px] bg-slate-950">
                                <img
                                    src={course.thumbnail || '/images/tuition-hero.png'}
                                    alt=""
                                    className="absolute inset-0 h-full w-full object-cover opacity-75"
                                />
                                <div className="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/55 to-slate-950/10" />
                                <div className="relative flex min-h-[300px] flex-col justify-end p-5 sm:p-8">
                                    <div className="flex flex-wrap gap-2">
                                        <Badge>{unlockedChildren.length > 0 ? 'Subscribed' : 'Subscription required'}</Badge>
                                        <span className="badge bg-white/90 text-slate-700 ring-1 ring-inset ring-white/80">
                                            {course.year} · {course.subject}
                                        </span>
                                    </div>
                                    <h1 className="mt-4 max-w-3xl text-3xl font-semibold text-white sm:text-4xl">
                                        {course.title}
                                    </h1>
                                    <p className="mt-4 max-w-3xl text-base leading-7 text-slate-100">
                                        {course.description}
                                    </p>
                                </div>
                            </div>
                            <div className="grid gap-3 border-t border-slate-200 bg-slate-50 p-4 sm:grid-cols-3">
                                <CourseStat label="Modules" value={course.modules.length} />
                                <CourseStat label="Lessons" value={lessonCount} />
                                <CourseStat label="Free previews" value={freePreviewCount} />
                            </div>
                        </section>

                        <section className="rounded-lg border border-slate-200 bg-white shadow-sm">
                            <div className="border-b border-slate-100 p-5 sm:p-6">
                                <div className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h3 className="font-semibold text-slate-900">
                                            Learning Path
                                        </h3>
                                        <p className="mt-1 text-sm text-slate-500">
                                            Year → subject → topic → lesson structure.
                                        </p>
                                    </div>
                                    <span className="text-sm font-semibold text-slate-500">
                                        {lessonCount} lessons
                                    </span>
                                </div>
                            </div>
                            <div className="divide-y divide-slate-100">
                                {course.modules.map((module, moduleIndex) => (
                                    <div key={module.id} className="p-5 sm:p-6">
                                        <div className="flex items-start gap-3">
                                            <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-cyan-50 text-sm font-semibold text-cyan-700">
                                                {moduleIndex + 1}
                                            </span>
                                            <div className="min-w-0 flex-1">
                                                <h4 className="font-semibold text-slate-900">
                                                    {module.title}
                                                </h4>
                                                <ul className="mt-4 space-y-2">
                                                    {module.lessons.map((lesson) => {
                                                        const canOpen = lesson.is_released;
                                                        const rowClass =
                                                            'flex items-center justify-between gap-4 rounded-lg border border-slate-200 p-3 transition';
                                                        const inner = (
                                                            <>
                                                                <div className="min-w-0">
                                                                    <p className="truncate text-sm font-medium text-slate-900">
                                                                        {lesson.title}
                                                                    </p>
                                                                    <p className="text-xs text-slate-500">
                                                                        {lesson.duration_minutes} min
                                                                        {lesson.is_free
                                                                            ? ' · free preview'
                                                                            : lesson.is_released
                                                                              ? ' · released'
                                                                              : ' · scheduled'}
                                                                    </p>
                                                                </div>
                                                                <span
                                                                    className={`badge ring-1 ring-inset ${
                                                                        canOpen
                                                                            ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                                                            : 'bg-slate-100 text-slate-500 ring-slate-200'
                                                                    }`}
                                                                >
                                                                    {canOpen ? 'Open' : 'Locked'}
                                                                </span>
                                                            </>
                                                        );

                                                        return (
                                                            <li key={lesson.id}>
                                                                {canOpen ? (
                                                                    <Link
                                                                        href={lesson.open_url}
                                                                        className={`${rowClass} hover:border-cyan-200 hover:bg-cyan-50/40`}
                                                                    >
                                                                        {inner}
                                                                    </Link>
                                                                ) : (
                                                                    <div
                                                                        className={`${rowClass} bg-slate-50`}
                                                                    >
                                                                        {inner}
                                                                    </div>
                                                                )}
                                                            </li>
                                                        );
                                                    })}
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </section>
                    </div>

                    <aside className="space-y-6 lg:sticky lg:top-24 lg:self-start">
                        <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 className="font-semibold text-slate-900">
                                Child access
                            </h3>
                            {!auth.user ? (
                                <div className="mt-4 rounded-lg border border-cyan-200 bg-cyan-50 p-4">
                                    <p className="text-sm leading-6 text-cyan-900">
                                        Create a parent account or log in to subscribe a child and unlock this course.
                                    </p>
                                    <div className="mt-4 flex flex-wrap gap-2">
                                        <Link href={route('register')} className="btn-primary">
                                            Get started
                                        </Link>
                                        <Link href={route('login')} className="btn-secondary">
                                            Log in
                                        </Link>
                                    </div>
                                </div>
                            ) : children.length === 0 ? (
                                <div className="mt-4 rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500">
                                    Add a student from your parent dashboard before subscribing.
                                </div>
                            ) : (
                                <div className="mt-4 space-y-3">
                                    {children.map((child) => (
                                        <div
                                            key={child.id}
                                            className="rounded-lg border border-slate-200 p-4"
                                        >
                                            <div className="flex items-start justify-between gap-3">
                                                <div>
                                                    <p className="font-medium text-slate-900">
                                                        {child.name}
                                                    </p>
                                                    <p className="text-xs text-slate-500">
                                                        {child.student_code}
                                                    </p>
                                                </div>
                                                <Badge status={child.has_access ? 'active' : 'blocked'} />
                                            </div>
                                            {child.has_access ? (
                                                <div className="mt-4">
                                                    <div className="mb-1 flex justify-between text-xs text-slate-500">
                                                        <span>Progress</span>
                                                        <span>{child.course_progress}%</span>
                                                    </div>
                                                    <ProgressBar value={child.course_progress} />
                                                </div>
                                            ) : (
                                                <Link
                                                    href={child.subscribe_url}
                                                    className="btn-primary mt-4 w-full"
                                                >
                                                    Subscribe for this child
                                                </Link>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            )}
                        </section>

                        <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 className="font-semibold text-slate-900">
                                Available plans
                            </h3>
                            <div className="mt-4 space-y-2">
                                {plans.map((plan) => (
                                    <div
                                        key={plan.id}
                                        className="rounded-lg border border-slate-200 p-3"
                                    >
                                        <div className="flex justify-between gap-3">
                                            <div>
                                                <p className="text-sm font-medium text-slate-900">
                                                    {plan.name}
                                                </p>
                                                <p className="text-xs capitalize text-slate-500">
                                                    {plan.billing_cycle}
                                                    {plan.trial_days ? ` · ${plan.trial_days}-day trial` : ''}
                                                </p>
                                            </div>
                                            <p className="font-semibold text-slate-900">
                                                £{plan.price}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </section>
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
                        {course.title}
                    </h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        {course.year} · {course.subject}
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

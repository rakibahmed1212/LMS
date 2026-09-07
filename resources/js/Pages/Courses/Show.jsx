import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import { Head, Link } from '@inertiajs/react';

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

export default function CourseShow({ course, children, plans }) {
    const unlockedChildren = children.filter((child) => child.has_access);

    return (
        <AuthenticatedLayout
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
            <Head title={course.title} />
            <div className="py-8">
                <div className="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-8">
                    <div className="space-y-6">
                        <section className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                            <div className="aspect-[21/9] bg-slate-100">
                                <img
                                    src={course.thumbnail || '/images/tuition-hero.png'}
                                    alt=""
                                    className="h-full w-full object-cover"
                                />
                            </div>
                            <div className="p-5 sm:p-6">
                                <Badge>{unlockedChildren.length > 0 ? 'Subscribed' : 'Subscription required'}</Badge>
                                <p className="mt-4 leading-7 text-slate-600">
                                    {course.description}
                                </p>
                            </div>
                        </section>

                        <section className="rounded-lg border border-slate-200 bg-white shadow-sm">
                            <div className="border-b border-slate-100 p-5 sm:p-6">
                                <h3 className="font-semibold text-slate-900">
                                    Curriculum
                                </h3>
                            </div>
                            <div className="divide-y divide-slate-100">
                                {course.modules.map((module) => (
                                    <div key={module.id} className="p-5 sm:p-6">
                                        <h4 className="font-semibold text-slate-900">
                                            {module.title}
                                        </h4>
                                        <ul className="mt-4 space-y-2">
                                            {module.lessons.map((lesson) => (
                                                <li
                                                    key={lesson.id}
                                                    className="flex items-center justify-between gap-4 rounded-lg border border-slate-200 p-3"
                                                >
                                                    <div>
                                                        <p className="text-sm font-medium text-slate-900">
                                                            {lesson.title}
                                                        </p>
                                                        <p className="text-xs text-slate-500">
                                                            {lesson.duration_minutes} min
                                                            {lesson.is_free ? ' · free preview' : ' · subscribers only'}
                                                        </p>
                                                    </div>
                                                    <span
                                                        className={`badge ${
                                                            lesson.is_free || unlockedChildren.length > 0
                                                                ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200'
                                                                : 'bg-slate-100 text-slate-500'
                                                        }`}
                                                    >
                                                        {lesson.is_free || unlockedChildren.length > 0 ? 'Open' : 'Locked'}
                                                    </span>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                ))}
                            </div>
                        </section>
                    </div>

                    <aside className="space-y-6">
                        <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 className="font-semibold text-slate-900">
                                Child access
                            </h3>
                            {children.length === 0 ? (
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
        </AuthenticatedLayout>
    );
}

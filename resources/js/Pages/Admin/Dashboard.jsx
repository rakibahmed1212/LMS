import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import StatCard from '@/Components/StatCard';
import { Head, Link } from '@inertiajs/react';

function IconPath({ name }) {
    const paths = {
        users: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2a5 5 0 00-10 0m0 0v2m8-13a3 3 0 11-6 0 3 3 0 016 0z',
        money: 'M12 6v12m-6-6h12m-9 6h6a3 3 0 000-6h-6a3 3 0 010-6h6',
        active: 'M5 13l4 4L19 7',
        alert: 'M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z',
        refresh: 'M4 4v6h6M20 20v-6h-6M5 19a9 9 0 0014-3M19 5A9 9 0 005 8',
        receipt: 'M9 7h6M9 11h6M9 15h3M5 3h14v18l-3-2-2 2-2-2-2 2-2-2-3 2V3z',
    };

    return <path strokeLinecap="round" strokeLinejoin="round" d={paths[name]} />;
}

function Icon({ name, className = 'h-5 w-5' }) {
    return (
        <svg
            className={className}
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            strokeWidth="1.8"
        >
            <IconPath name={name} />
        </svg>
    );
}

function Bar({ value, max, tone = 'bg-cyan-600' }) {
    const width = max > 0 ? Math.max(4, Math.round((value / max) * 100)) : 0;

    return (
        <div className="h-2 overflow-hidden rounded-full bg-slate-100">
            <div className={`h-full rounded-full ${tone}`} style={{ width: `${width}%` }} />
        </div>
    );
}

function Panel({ title, action, children }) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div className="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <h3 className="font-semibold text-slate-900">{title}</h3>
                {action}
            </div>
            <div className="p-5">{children}</div>
        </section>
    );
}

export default function Dashboard({
    overview,
    status_breakdown,
    renewals,
    revenue_by_plan,
    at_risk_students,
    course_activity,
    recent_subscriptions,
}) {
    const stats = [
        {
            label: 'Revenue paid',
            value: `£${overview.revenue}`,
            icon: (props) => <Icon name="money" {...props} />,
            accent: 'text-cyan-700',
            iconClass: 'bg-cyan-50 text-cyan-700',
        },
        {
            label: 'Active subscriptions',
            value: overview.active_subscriptions,
            icon: (props) => <Icon name="active" {...props} />,
            accent: 'text-emerald-700',
            iconClass: 'bg-emerald-50 text-emerald-700',
        },
        {
            label: 'Students',
            value: overview.total_students,
            icon: (props) => <Icon name="users" {...props} />,
            iconClass: 'bg-blue-50 text-blue-700',
        },
        {
            label: 'Past due',
            value: overview.past_due_subscriptions,
            icon: (props) => <Icon name="alert" {...props} />,
            accent: overview.past_due_subscriptions > 0 ? 'text-amber-700' : 'text-slate-900',
            iconClass: 'bg-amber-50 text-amber-700',
        },
    ];

    const maxStatus = Math.max(...status_breakdown.map((item) => item.total), 1);
    const maxRevenue = Math.max(...revenue_by_plan.map((item) => item.total), 1);

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="text-xl font-semibold text-slate-900">
                            Admin Dashboard
                        </h2>
                        <p className="mt-0.5 text-sm text-slate-500">
                            Operations view for subscriptions, revenue, students and course activity.
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Link href={route('admin.users.index')} className="btn-secondary">
                            Manage users
                        </Link>
                        <Link href={route('courses.index')} className="btn-primary">
                            Course portal
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title="Admin" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        {stats.map((stat) => (
                            <StatCard key={stat.label} {...stat} />
                        ))}
                    </div>

                    <div className="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                        <Panel title="Subscription Health">
                            <div className="space-y-4">
                                {status_breakdown.map((item) => (
                                    <div key={item.status}>
                                        <div className="mb-1 flex items-center justify-between text-sm">
                                            <Badge status={item.status} />
                                            <span className="font-semibold text-slate-700">{item.total}</span>
                                        </div>
                                        <Bar value={item.total} max={maxStatus} />
                                    </div>
                                ))}
                            </div>
                        </Panel>

                        <Panel title="Revenue By Plan">
                            {revenue_by_plan.length === 0 ? (
                                <p className="text-sm text-slate-500">No paid revenue yet.</p>
                            ) : (
                                <div className="space-y-4">
                                    {revenue_by_plan.map((item) => (
                                        <div key={item.plan}>
                                            <div className="mb-1 flex items-center justify-between gap-3 text-sm">
                                                <span className="truncate font-medium text-slate-800">{item.plan}</span>
                                                <span className="font-semibold text-slate-900">£{item.total}</span>
                                            </div>
                                            <Bar value={item.total} max={maxRevenue} tone="bg-emerald-600" />
                                        </div>
                                    ))}
                                </div>
                            )}
                        </Panel>
                    </div>

                    <div className="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                        <Panel
                            title="Renewals Next 14 Days"
                            action={
                                <span className="text-sm font-semibold text-slate-500">
                                    {overview.renewals_next_7_days} due this week
                                </span>
                            }
                        >
                            {renewals.length === 0 ? (
                                <p className="text-sm text-slate-500">No upcoming renewals.</p>
                            ) : (
                                <ul className="divide-y divide-slate-100">
                                    {renewals.map((sub) => (
                                        <li key={sub.id} className="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                                            <div>
                                                <p className="text-sm font-medium text-slate-900">{sub.student}</p>
                                                <p className="text-xs text-slate-500">{sub.parent} · {sub.plan}</p>
                                            </div>
                                            <div className="text-right">
                                                <Badge status={sub.status} />
                                                <p className="mt-1 text-xs text-slate-500">{sub.expires_at}</p>
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </Panel>

                        <Panel title="Students Needing Attention">
                            {at_risk_students.length === 0 ? (
                                <p className="text-sm text-slate-500">No low-score students in the current demo data.</p>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full text-sm">
                                        <thead className="text-left text-xs uppercase tracking-wide text-slate-400">
                                            <tr>
                                                <th className="pb-3 font-semibold">Student</th>
                                                <th className="pb-3 font-semibold">Subject</th>
                                                <th className="pb-3 font-semibold">Term</th>
                                                <th className="pb-3 text-right font-semibold">Score</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-100">
                                            {at_risk_students.map((student) => (
                                                <tr key={student.id}>
                                                    <td className="py-3">
                                                        <p className="font-medium text-slate-900">{student.student}</p>
                                                        <p className="text-xs text-slate-500">{student.parent}</p>
                                                    </td>
                                                    <td className="py-3 text-slate-600">{student.year} · {student.subject}</td>
                                                    <td className="py-3 text-slate-600">{student.term}</td>
                                                    <td className="py-3 text-right font-semibold text-amber-700">
                                                        {student.final_score}%
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </Panel>
                    </div>

                    <div className="grid gap-6 xl:grid-cols-[1fr_1fr]">
                        <Panel title="Course Activity">
                            <div className="grid gap-3 sm:grid-cols-2">
                                {course_activity.map((course) => (
                                    <div key={course.id} className="rounded-lg border border-slate-200 p-4">
                                        <p className="text-sm font-semibold text-slate-900">{course.title}</p>
                                        <p className="mt-1 text-xs text-slate-500">{course.year} · {course.subject}</p>
                                        <div className="mt-3 flex items-center gap-3 text-xs font-medium text-slate-500">
                                            <span>{course.lessons_count} lessons</span>
                                            <span>{course.certificates_count} certificates</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </Panel>

                        <Panel title="Recent Subscriptions">
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead className="text-left text-xs uppercase tracking-wide text-slate-400">
                                        <tr>
                                            <th className="pb-3 font-semibold">Student</th>
                                            <th className="pb-3 font-semibold">Plan</th>
                                            <th className="pb-3 font-semibold">Status</th>
                                            <th className="pb-3 font-semibold">Expires</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100">
                                        {recent_subscriptions.map((sub) => (
                                            <tr key={sub.id}>
                                                <td className="py-3">
                                                    <p className="font-medium text-slate-900">{sub.student}</p>
                                                    <p className="text-xs text-slate-500">{sub.parent ?? 'No parent'}</p>
                                                </td>
                                                <td className="py-3 text-slate-600">{sub.plan}</td>
                                                <td className="py-3"><Badge status={sub.status} /></td>
                                                <td className="py-3 text-slate-600">{sub.expires_at ?? '-'}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </Panel>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

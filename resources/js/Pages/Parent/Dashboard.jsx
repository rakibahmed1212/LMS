import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

const statusBadge = {
    active: 'bg-green-100 text-green-800',
    trial: 'bg-blue-100 text-blue-700',
    past_due: 'bg-amber-100 text-amber-800',
    expired: 'bg-gray-100 text-gray-600',
    cancelled: 'bg-red-100 text-red-700',
    pending: 'bg-gray-100 text-gray-600',
    paused: 'bg-yellow-100 text-yellow-800',
};

function SubscriptionRow({ sub }) {
    return (
        <li className="flex flex-col gap-1 rounded-lg border border-gray-200 p-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p className="text-sm font-medium text-gray-900">{sub.plan}</p>
                <p className="text-xs text-gray-500">
                    {sub.billing_cycle} · £{sub.price} · expires {sub.expires_at ?? '—'}
                </p>
            </div>
            <div className="flex items-center gap-2">
                {sub.renewal_due && (
                    <span className="rounded bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">
                        renewal due
                    </span>
                )}
                <span
                    className={`rounded-full px-2 py-0.5 text-xs font-semibold ${statusBadge[sub.status] ?? statusBadge.pending}`}
                >
                    {sub.status.replace('_', ' ')}
                </span>
            </div>
        </li>
    );
}

export default function Dashboard({ children, hasSubscriptionPlans }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    My Tuition Dashboard
                </h2>
            }
        >
            <Head title="Parent Dashboard" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="grid gap-6">
                        {children.length === 0 && (
                            <div className="rounded-lg bg-white p-8 text-center shadow-sm">
                                <p className="text-gray-600">
                                    No children added yet. Add a student to get started.
                                </p>
                            </div>
                        )}

                        {children.map((child) => (
                            <div
                                key={child.id}
                                className="overflow-hidden rounded-lg bg-white shadow-sm"
                            >
                                <div className="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 p-5">
                                    <div>
                                        <h3 className="text-lg font-semibold text-gray-900">
                                            {child.name}
                                        </h3>
                                        <p className="text-sm text-gray-500">
                                            {child.student_code}
                                            {child.school ? ` · ${child.school}` : ''}
                                            {child.years.length > 0
                                                ? ` · ${child.years.join(', ')}`
                                                : ''}
                                        </p>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <span className="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700">
                                            {child.accessible_subjects.length} subject
                                            {child.accessible_subjects.length === 1 ? '' : 's'}
                                        </span>
                                        {hasSubscriptionPlans && (
                                            <Link
                                                href={route('subscriptions.show', {
                                                    student: child.id,
                                                })}
                                                className="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500"
                                            >
                                                Add subscription
                                            </Link>
                                        )}
                                    </div>
                                </div>

                                <div className="grid gap-4 p-5 md:grid-cols-2">
                                    <div>
                                        <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                            Subscriptions
                                        </p>
                                        {child.subscriptions.length === 0 ? (
                                            <p className="text-sm text-gray-500">
                                                No active subscriptions.
                                            </p>
                                        ) : (
                                            <ul className="space-y-2">
                                                {child.subscriptions.map((sub) => (
                                                    <SubscriptionRow key={sub.id} sub={sub} />
                                                ))}
                                            </ul>
                                        )}
                                    </div>

                                    <div>
                                        <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                            Subjects in use: {child.accessible_subjects.join(', ') || '—'}
                                        </p>
                                        <p className="text-sm text-gray-500">
                                            Progress tracking, quizzes, and grades are linked to
                                            each subscribed subject. Historical data stays after a
                                            subscription expires.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
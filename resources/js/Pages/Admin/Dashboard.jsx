import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

function Stat({ label, value, accent = 'text-gray-900' }) {
    return (
        <div className="rounded-lg bg-white p-5 shadow-sm">
            <p className="text-sm font-medium text-gray-500">{label}</p>
            <p className={`mt-1 text-2xl font-bold ${accent}`}>{value}</p>
        </div>
    );
}

export default function Dashboard({ overview, recent_subscriptions }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Admin Overview
                </h2>
            }
        >
            <Head title="Admin" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <Stat label="Students" value={overview.total_students} />
                        <Stat
                            label="Active subscriptions"
                            value={overview.active_subscriptions}
                            accent="text-green-600"
                        />
                        <Stat
                            label="Past due"
                            value={overview.past_due_subscriptions}
                            accent={
                                overview.past_due_subscriptions > 0
                                    ? 'text-amber-600'
                                    : 'text-gray-900'
                            }
                        />
                        <Stat
                            label="Revenue (paid)"
                            value={`£${overview.revenue}`}
                            accent="text-indigo-600"
                        />
                        <Stat
                            label="Renewals next 7 days"
                            value={overview.renewals_next_7_days}
                        />
                        <Stat
                            label="Expired subscriptions"
                            value={overview.expired_subscriptions}
                        />
                        <Stat label="Paid payments" value={overview.paid_payments} />
                    </div>

                    <div className="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div className="border-b border-gray-100 p-5">
                            <h3 className="font-semibold text-gray-900">
                                Recent subscriptions
                            </h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200 text-sm">
                                <thead className="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                    <tr>
                                        <th className="px-5 py-3">Student</th>
                                        <th className="px-5 py-3">Parent</th>
                                        <th className="px-5 py-3">Plan</th>
                                        <th className="px-5 py-3">Subjects</th>
                                        <th className="px-5 py-3">Status</th>
                                        <th className="px-5 py-3">Expires</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {recent_subscriptions.map((sub) => (
                                        <tr key={sub.id}>
                                            <td className="px-5 py-3 font-medium text-gray-900">
                                                {sub.student}
                                            </td>
                                            <td className="px-5 py-3 text-gray-600">
                                                {sub.parent ?? '—'}
                                            </td>
                                            <td className="px-5 py-3 text-gray-600">
                                                {sub.plan}
                                            </td>
                                            <td className="px-5 py-3 text-gray-600">
                                                {sub.covered_subjects.join(', ')}
                                            </td>
                                            <td className="px-5 py-3">
                                                <span className="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">
                                                    {sub.status.replace('_', ' ')}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3 text-gray-600">
                                                {sub.expires_at ?? '—'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
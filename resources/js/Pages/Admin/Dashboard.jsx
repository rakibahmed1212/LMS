import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import StatCard from '@/Components/StatCard';
import Badge from '@/Components/Badge';
import { Head } from '@inertiajs/react';

function UsersIcon() {
    return (
        <path strokeLinecap="round" strokeLinejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
    );
}

function CheckIcon() {
    return (
        <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
    );
}

function AlertIcon() {
    return (
        <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    );
}

function BankIcon() {
    return (
        <path strokeLinecap="round" strokeLinejoin="round" d="M12 8c-1.657 0-3-.895-3-2s1.343-2 3-2 3 .895 3 2-1.343 2-3 2zm0 0c1.11 0 2.08.402 2.599 1M12 8v7m0 0a2 2 0 002 2h4a2 2 0 002-2v-3a2 2 0 00-2-2h-2m-2 3h-4a2 2 0 01-2-2V9a2 2 0 012-2m2 11v3m-6-3a2 2 0 01-2-2v-2m4 4v3" />
    );
}

function RefreshIcon() {
    return (
        <path strokeLinecap="round" strokeLinejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
    );
}

function ArchiveIcon() {
    return (
        <path strokeLinecap="round" strokeLinejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
    );
}

function ReceiptIcon() {
    return (
        <path strokeLinecap="round" strokeLinejoin="round" d="M9 14l6 0m-6-4l6 0m-4 8l-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v13a2 2 0 01-2 2h-2l-2-2zm0 0l4 4" />
    );
}

function MoneyIcon() {
    return (
        <path strokeLinecap="round" strokeLinejoin="round" d="M12 8c-1.657 0-3-.895-3-2s1.343-2 3-2 3 .895 3 2-1.343 2-3 2zm0 0v7m0 0a2 2 0 002 2h4a2 2 0 002-2v-3a2 2 0 00-2-2h-2m-2 3h-4a2 2 0 01-2-2V9a2 2 0 012-2m2 11v3m-6-3a2 2 0 01-2-2v-2m4 4v3" />
    );
}

function CurrencyIcon() {
    return (
        <path strokeLinecap="round" strokeLinejoin="round" d="M7 6h10a3 3 0 010 6H7a3 3 0 010-6zm0 0h13m-3 14h-7m3-14v9m-3-3h3" />
    );
}

const iconMap = (name) => ({
    users: UsersIcon,
    active: CheckIcon,
    alert: AlertIcon,
    bank: BankIcon,
    refresh: RefreshIcon,
    archive: ArchiveIcon,
    receipt: ReceiptIcon,
    money: MoneyIcon,
    currency: CurrencyIcon,
}[name]);

export default function Dashboard({ overview, recent_subscriptions }) {
    const stats = [
        {
            label: 'Total students',
            value: overview.total_students,
            icon: iconMap('users'),
            iconClass: 'bg-indigo-50 text-indigo-600',
        },
        {
            label: 'Active subscriptions',
            value: overview.active_subscriptions,
            icon: iconMap('active'),
            accent: 'text-emerald-600',
            iconClass: 'bg-emerald-50 text-emerald-600',
        },
        {
            label: 'Past due',
            value: overview.past_due_subscriptions,
            icon: iconMap('alert'),
            accent:
                overview.past_due_subscriptions > 0
                    ? 'text-amber-600'
                    : 'text-slate-900',
            iconClass: 'bg-amber-50 text-amber-600',
        },
        {
            label: 'Revenue (paid)',
            value: `£${overview.revenue}`,
            icon: iconMap('money'),
            accent: 'text-indigo-600',
            iconClass: 'bg-violet-50 text-violet-600',
        },
        {
            label: 'Renewals next 7 days',
            value: overview.renewals_next_7_days,
            icon: iconMap('refresh'),
        },
        {
            label: 'Expired subscriptions',
            value: overview.expired_subscriptions,
            icon: iconMap('archive'),
            iconClass: 'bg-slate-100 text-slate-500',
        },
        {
            label: 'Paid payments',
            value: overview.paid_payments,
            icon: iconMap('receipt'),
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        Admin Overview
                    </h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        Insights across students, subscriptions and revenue.
                    </p>
                </div>
            }
        >
            <Head title="Admin" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {stats.map((stat) => (
                            <StatCard key={stat.label} {...stat} />
                        ))}
                    </div>

                    <div className="card overflow-hidden">
                        <div className="border-b border-slate-100 p-5 sm:p-6">
                            <h3 className="font-semibold text-slate-900">
                                Recent subscriptions
                            </h3>
                            <p className="mt-0.5 text-sm text-slate-500">
                                The latest subscription activity across all
                                students.
                            </p>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200 text-sm">
                                <thead className="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th className="px-5 py-3 font-semibold">
                                            Student
                                        </th>
                                        <th className="px-5 py-3 font-semibold">
                                            Parent
                                        </th>
                                        <th className="px-5 py-3 font-semibold">
                                            Plan
                                        </th>
                                        <th className="px-5 py-3 font-semibold">
                                            Subjects
                                        </th>
                                        <th className="px-5 py-3 font-semibold">
                                            Status
                                        </th>
                                        <th className="px-5 py-3 font-semibold">
                                            Expires
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {recent_subscriptions.map((sub) => (
                                        <tr
                                            key={sub.id}
                                            className="hover:bg-slate-50/60"
                                        >
                                            <td className="px-5 py-3 font-medium text-slate-900">
                                                {sub.student}
                                            </td>
                                            <td className="px-5 py-3 text-slate-600">
                                                {sub.parent ?? '—'}
                                            </td>
                                            <td className="px-5 py-3 text-slate-600">
                                                {sub.plan}
                                            </td>
                                            <td className="px-5 py-3 text-slate-600">
                                                {sub.covered_subjects.join(
                                                    ', ',
                                                )}
                                            </td>
                                            <td className="px-5 py-3">
                                                <Badge status={sub.status} />
                                            </td>
                                            <td className="px-5 py-3 text-slate-600">
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

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

function Stat({ label, value }) {
    return (
        <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">
                {label}
            </p>
            <p className="mt-2 text-2xl font-semibold text-slate-950">
                {value}
            </p>
        </div>
    );
}

function StudentCard({ student }) {
    return (
        <article className="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div className="flex flex-col gap-4 border-b border-slate-100 p-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div className="flex flex-wrap items-center gap-2">
                        <h3 className="text-base font-semibold text-slate-950">
                            {student.name}
                        </h3>
                        <span className="rounded bg-slate-100 px-2 py-1 font-mono text-xs text-slate-600">
                            {student.student_code}
                        </span>
                        <Badge status={student.is_active ? 'active' : 'inactive'} />
                    </div>
                    <p className="mt-1 text-sm text-slate-500">
                        {student.school || 'No school added'}
                        {student.years.length > 0
                            ? ` · ${student.years.join(', ')}`
                            : ''}
                    </p>
                    <p className="mt-2 text-sm text-slate-600">
                        Parent: {student.parent.name || 'Unknown'} ·{' '}
                        {student.parent.email || 'No email'}
                        {student.parent.phone ? ` · ${student.parent.phone}` : ''}
                    </p>
                </div>
                <div className="grid grid-cols-3 gap-2 text-center sm:min-w-72">
                    <div className="rounded-lg bg-slate-50 p-3">
                        <p className="text-lg font-semibold text-slate-950">
                            {student.viewed_lessons_count}
                        </p>
                        <p className="text-xs text-slate-500">viewed</p>
                    </div>
                    <div className="rounded-lg bg-slate-50 p-3">
                        <p className="text-lg font-semibold text-slate-950">
                            {student.completed_lessons_count}
                        </p>
                        <p className="text-xs text-slate-500">complete</p>
                    </div>
                    <div className="rounded-lg bg-slate-50 p-3">
                        <p className="text-lg font-semibold text-slate-950">
                            {student.certificates_count}
                        </p>
                        <p className="text-xs text-slate-500">certs</p>
                    </div>
                </div>
            </div>

            <div className="p-5">
                <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Subscriptions and payments
                </p>
                {student.subscriptions.length === 0 ? (
                    <div className="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500">
                        No subscription yet.
                    </div>
                ) : (
                    <div className="grid gap-3 lg:grid-cols-2">
                        {student.subscriptions.map((subscription) => (
                            <div
                                key={subscription.id}
                                className="rounded-lg border border-slate-200 bg-slate-50/70 p-4"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="font-semibold text-slate-900">
                                            {subscription.plan}
                                        </p>
                                        <p className="mt-1 text-xs text-slate-500">
                                            {subscription.subjects.join(', ')}
                                        </p>
                                    </div>
                                    <Badge status={subscription.status} />
                                </div>
                                <div className="mt-3 grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                                    <p>
                                        {subscription.currency}{' '}
                                        {subscription.price.toFixed(2)} ·{' '}
                                        {subscription.billing_cycle}
                                    </p>
                                    <p>
                                        {subscription.expires_at
                                            ? `Expires ${subscription.expires_at}`
                                            : 'No expiry date'}
                                    </p>
                                    <p>
                                        {subscription.auto_renew
                                            ? 'Auto-renew on'
                                            : 'Auto-renew off'}
                                    </p>
                                    <p>
                                        {subscription.latest_payment
                                            ? `${subscription.latest_payment.invoice_no} · ${subscription.latest_payment.status}`
                                            : 'No payment recorded'}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </article>
    );
}

export default function Students({ students, filters, classYears, stats }) {
    const [values, setValues] = useState({
        q: filters.q || '',
        class_year_id: filters.class_year_id || '',
        subscription_status: filters.subscription_status || '',
    });

    useEffect(() => {
        const timeout = setTimeout(() => {
            router.get(route('admin.students.index'), values, {
                preserveState: true,
                replace: true,
            });
        }, 300);

        return () => clearTimeout(timeout);
    }, [values]);

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        Students
                    </h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        Search student IDs, parents, class years, subscriptions and learning status.
                    </p>
                </div>
            }
        >
            <Head title="Students" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <Stat label="Students" value={stats.students} />
                        <Stat label="Active profiles" value={stats.active} />
                        <Stat label="Subscribed" value={stats.subscribed} />
                        <Stat label="Past due" value={stats.past_due} />
                    </div>

                    <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div className="grid gap-4 lg:grid-cols-[1fr_220px_220px]">
                            <div>
                                <label className="label" htmlFor="student_q">
                                    Search
                                </label>
                                <input
                                    id="student_q"
                                    value={values.q}
                                    onChange={(e) =>
                                        setValues({ ...values, q: e.target.value })
                                    }
                                    className="input mt-1"
                                    placeholder="Student ID, name, parent, email, phone or school"
                                />
                            </div>
                            <div>
                                <label className="label" htmlFor="class_year">
                                    Class year
                                </label>
                                <select
                                    id="class_year"
                                    value={values.class_year_id}
                                    onChange={(e) =>
                                        setValues({
                                            ...values,
                                            class_year_id: e.target.value,
                                        })
                                    }
                                    className="input mt-1"
                                >
                                    <option value="">All years</option>
                                    {classYears.map((year) => (
                                        <option key={year.id} value={year.id}>
                                            {year.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="label" htmlFor="sub_status">
                                    Subscription
                                </label>
                                <select
                                    id="sub_status"
                                    value={values.subscription_status}
                                    onChange={(e) =>
                                        setValues({
                                            ...values,
                                            subscription_status: e.target.value,
                                        })
                                    }
                                    className="input mt-1"
                                >
                                    <option value="">All statuses</option>
                                    <option value="active">Active</option>
                                    <option value="trial">Trial</option>
                                    <option value="past_due">Past due</option>
                                    <option value="expired">Expired</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="none">No subscription</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {students.data.length === 0 ? (
                        <div className="rounded-lg border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
                            No students match this search.
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {students.data.map((student) => (
                                <StudentCard key={student.id} student={student} />
                            ))}
                        </div>
                    )}

                    {students.links.length > 3 && (
                        <div className="flex flex-wrap gap-2">
                            {students.links.map((link) => (
                                <Link
                                    key={link.label}
                                    href={link.url || '#'}
                                    preserveScroll
                                    className={`rounded-lg border px-3 py-2 text-sm ${
                                        link.active
                                            ? 'border-cyan-600 bg-cyan-600 text-white'
                                            : 'border-slate-200 bg-white text-slate-600'
                                    } ${!link.url ? 'pointer-events-none opacity-50' : ''}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

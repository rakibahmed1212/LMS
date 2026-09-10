import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import { Head, Link } from '@inertiajs/react';

function Row({ label, value }) {
    return (
        <div className="flex justify-between gap-4 border-b border-slate-100 py-3 text-sm">
            <span className="text-slate-500">{label}</span>
            <span className="text-right font-medium text-slate-900">
                {value || '-'}
            </span>
        </div>
    );
}

export default function Receipt({ receipt }) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        Payment Receipt
                    </h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        {receipt.invoice_no}
                    </p>
                </div>
            }
        >
            <Head title={`Receipt ${receipt.invoice_no}`} />
            <div className="py-8">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                        <div className="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p className="text-sm font-semibold uppercase tracking-wide text-slate-400">
                                    Invoice
                                </p>
                                <h1 className="mt-2 text-2xl font-semibold text-slate-950">
                                    {receipt.invoice_no}
                                </h1>
                                <p className="mt-1 text-sm text-slate-500">
                                    {receipt.student.name} · {receipt.student.student_code}
                                </p>
                            </div>
                            <Badge status={receipt.status} />
                        </div>

                        <div className="grid gap-6 p-6 md:grid-cols-2">
                            <section>
                                <h3 className="font-semibold text-slate-900">
                                    Parent
                                </h3>
                                <div className="mt-3">
                                    <Row label="Name" value={receipt.parent.name} />
                                    <Row label="Email" value={receipt.parent.email} />
                                    <Row label="Phone" value={receipt.parent.phone} />
                                </div>
                            </section>

                            <section>
                                <h3 className="font-semibold text-slate-900">
                                    Subscription
                                </h3>
                                <div className="mt-3">
                                    <Row label="Plan" value={receipt.subscription.plan} />
                                    <Row
                                        label="Subjects"
                                        value={receipt.subscription.subjects.join(', ')}
                                    />
                                    <Row
                                        label="Expires"
                                        value={receipt.subscription.expires_at}
                                    />
                                </div>
                            </section>
                        </div>

                        <div className="border-t border-slate-100 p-6">
                            <h3 className="font-semibold text-slate-900">
                                Payment
                            </h3>
                            <div className="mt-3">
                                <Row
                                    label="Amount"
                                    value={`${receipt.currency} ${receipt.amount.toFixed(2)}`}
                                />
                                <Row
                                    label="Discount"
                                    value={`${receipt.currency} ${receipt.discount_amount.toFixed(2)}`}
                                />
                                <Row
                                    label="Total"
                                    value={`${receipt.currency} ${receipt.total.toFixed(2)}`}
                                />
                                <Row label="Gateway" value={receipt.gateway} />
                                <Row
                                    label="Transaction"
                                    value={receipt.transaction_id}
                                />
                                <Row label="Paid at" value={receipt.paid_at} />
                            </div>
                        </div>

                        <div className="flex justify-end border-t border-slate-100 bg-slate-50 p-4">
                            <Link href={route('parent.dashboard')} className="btn-secondary">
                                Back to dashboard
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

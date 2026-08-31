import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

function PlanCard({ plan, subjectColor }) {
    return (
        <div className="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 transition hover:border-indigo-200 hover:shadow-sm">
            <div>
                <p className="flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-900">
                    {plan.name}
                    {plan.is_bundle && (
                        <span className="badge brand-gradient text-white">
                            bundle
                        </span>
                    )}
                    {plan.trial_days ? (
                        <span className="badge bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200">
                            {plan.trial_days}-day trial
                        </span>
                    ) : null}
                </p>
                <p className="mt-1 text-xs capitalize text-slate-500">
                    {plan.billing_cycle} billing
                    <span
                        className="mx-1.5 inline-block h-2 w-2 rounded-full align-middle"
                        style={{ backgroundColor: subjectColor }}
                    />
                </p>
            </div>
            <p className="whitespace-nowrap text-lg font-bold text-slate-900">
                £{plan.price}
                <span className="text-xs font-normal text-slate-400">
                    /{plan.billing_cycle === 'monthly' ? 'mo' : 'yr'}
                </span>
            </p>
        </div>
    );
}

export default function Plans({ years }) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        Courses &amp; Plans
                    </h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        Subscribe per subject for each child. Bundles save more.
                    </p>
                </div>
            }
        >
            <Head title="Course Plans" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-10 px-4 sm:px-6 lg:px-8">
                    {years.map((year) => (
                        <section key={year.id}>
                            <div className="mb-4 flex items-center gap-3">
                                <h3 className="text-lg font-semibold text-slate-900">
                                    {year.name}
                                </h3>
                                <span className="h-px flex-1 bg-slate-200" />
                            </div>
                            <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                                {year.subjects.map((subject) => (
                                    <div
                                        key={subject.id}
                                        className="card overflow-hidden"
                                    >
                                        <div className="flex items-center justify-between border-b border-slate-100 p-5">
                                            <h4 className="font-semibold text-slate-900">
                                                {subject.name}
                                            </h4>
                                            <span
                                                className="h-3.5 w-3.5 rounded-full ring-2 ring-white shadow"
                                                style={{
                                                    backgroundColor:
                                                        subject.color,
                                                }}
                                            />
                                        </div>
                                        <div className="space-y-2 p-5">
                                            {subject.plans.length === 0 ? (
                                                <p className="rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">
                                                    No plans yet.
                                                </p>
                                            ) : (
                                                subject.plans.map((plan) => (
                                                    <PlanCard
                                                        key={plan.id}
                                                        plan={plan}
                                                        subjectColor={
                                                            subject.color
                                                        }
                                                    />
                                                ))
                                            )}
                                        </div>
                                        <div className="border-t border-slate-100 px-5 py-3">
                                            <p className="text-xs text-slate-400">
                                                {subject.has_course
                                                    ? 'Course content available for subscribers.'
                                                    : 'Course content under production.'}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </section>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

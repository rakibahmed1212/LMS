import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

function PlanCard({ plan }) {
    return (
        <div className="flex items-center justify-between rounded-lg border border-indigo-100 bg-indigo-50/50 p-4">
            <div>
                <p className="text-sm font-semibold text-gray-900">
                    {plan.name}
                    {plan.is_bundle && (
                        <span className="ms-2 rounded bg-indigo-600 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-white">
                            bundle
                        </span>
                    )}
                </p>
                <p className="text-xs text-gray-500">
                    {plan.billing_cycle}
                    {plan.trial_days ? ` · ${plan.trial_days}-day free trial` : ''}
                </p>
            </div>
            <p className="text-lg font-bold text-gray-900">
                £{plan.price}
                <span className="text-xs font-normal text-gray-500">
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
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Available Courses &amp; Plans
                </h2>
            }
        >
            <Head title="Course Plans" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                    {years.map((year) => (
                        <section key={year.id}>
                            <h3 className="mb-3 text-lg font-semibold text-gray-900">
                                {year.name}
                            </h3>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                {year.subjects.map((subject) => (
                                    <div
                                        key={subject.id}
                                        className="rounded-lg bg-white p-5 shadow-sm"
                                    >
                                        <div className="mb-3 flex items-center justify-between">
                                            <h4 className="font-semibold text-gray-900">
                                                {subject.name}
                                            </h4>
                                            <span
                                                className="h-3 w-3 rounded-full"
                                                style={{ backgroundColor: subject.color }}
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            {subject.plans.length === 0 && (
                                                <p className="text-sm text-gray-500">
                                                    No plans yet.
                                                </p>
                                            )}
                                            {subject.plans.map((plan) => (
                                                <PlanCard key={plan.id} plan={plan} />
                                            ))}
                                        </div>
                                        <p className="mt-3 text-xs text-gray-500">
                                            {subject.has_course
                                                ? 'Course content available for subscribers.'
                                                : 'Course content under production.'}
                                        </p>
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
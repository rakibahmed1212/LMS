import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Purchase({ student, plans }) {
    const { data, setData, post, processing, errors } = useForm({
        plan_id: '',
        coupon_code: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('subscriptions.store', { student: student.id }));
    };

    const selected = plans.find(
        (plan) => String(plan.id) === data.plan_id,
    );

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        Subscribe {student.name}
                    </h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        {student.student_code} · choose a plan below
                    </p>
                </div>
            }
        >
            <Head title="Purchase Subscription" />
            <div className="py-8">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <div className="grid gap-6 lg:grid-cols-[1fr_320px]">
                        <div className="card">
                            <div className="border-b border-slate-100 p-5 sm:p-6">
                                <h3 className="font-semibold text-slate-900">
                                    Choose a plan
                                </h3>
                                <p className="mt-0.5 text-sm text-slate-500">
                                    Each subject is a separate subscription for
                                    this student.
                                </p>
                            </div>

                            <div className="grid gap-3 p-5 sm:grid-cols-2 sm:p-6">
                                {plans.map((plan) => {
                                    const active =
                                        data.plan_id === String(plan.id);
                                    return (
                                        <label
                                            key={plan.id}
                                            className={`relative flex cursor-pointer flex-col gap-1.5 rounded-xl border-2 p-4 transition ${
                                                active
                                                    ? 'border-indigo-500 bg-indigo-50/50 shadow-sm'
                                                    : 'border-slate-200 hover:border-indigo-200'
                                            }`}
                                        >
                                            <input
                                                type="radio"
                                                name="plan_id"
                                                value={plan.id}
                                                checked={active}
                                                onChange={(e) =>
                                                    setData(
                                                        'plan_id',
                                                        e.target.value,
                                                    )
                                                }
                                                className="sr-only"
                                            />
                                            <span className="flex items-center justify-between">
                                                <span className="text-sm font-semibold text-slate-900">
                                                    {plan.name}
                                                </span>
                                                <span
                                                    className={`flex h-5 w-5 items-center justify-center rounded-full border-2 ${
                                                        active
                                                            ? 'border-indigo-600 bg-indigo-600'
                                                            : 'border-slate-300'
                                                    }`}
                                                >
                                                    {active && (
                                                        <svg
                                                            className="h-3 w-3 text-white"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fillRule="evenodd"
                                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                clipRule="evenodd"
                                                            />
                                                        </svg>
                                                    )}
                                                </span>
                                            </span>
                                            <span className="text-xs text-slate-500">
                                                {plan.subject} ·{' '}
                                                <span className="capitalize">
                                                    {plan.billing_cycle}
                                                </span>
                                            </span>
                                            <span className="mt-1 flex items-end gap-1">
                                                <span className="text-2xl font-bold text-slate-900">
                                                    £{plan.price}
                                                </span>
                                                <span className="pb-0.5 text-xs text-slate-400">
                                                    /
                                                    {plan.billing_cycle ===
                                                    'monthly'
                                                        ? 'month'
                                                        : 'year'}
                                                </span>
                                            </span>
                                            {plan.trial_days && (
                                                <span className="badge mt-1 w-fit bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                                    {plan.trial_days}-day free
                                                    trial
                                                </span>
                                            )}
                                            {plan.subscribed && (
                                                <span className="badge mt-1 w-fit bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200">
                                                    currently active
                                                </span>
                                            )}
                                        </label>
                                    );
                                })}
                            </div>
                        </div>

                        <div className="card h-fit p-5 sm:p-6">
                            <h3 className="font-semibold text-slate-900">
                                Order summary
                            </h3>
                            <div className="mt-4 space-y-3 text-sm">
                                <div className="flex justify-between text-slate-600">
                                    <span>{student.name}</span>
                                    <span className="font-medium text-slate-900">
                                        {selected
                                            ? `${selected.name} · £${selected.price}/${
                                                  selected.billing_cycle ===
                                                  'monthly'
                                                      ? 'mo'
                                                      : 'yr'
                                              }`
                                            : 'No plan selected'}
                                    </span>
                                </div>
                            </div>

                            <form onSubmit={submit} className="mt-4 space-y-4">
                                <div>
                                    <label
                                        className="label"
                                        htmlFor="coupon_code"
                                    >
                                        Coupon code (optional)
                                    </label>
                                    <div className="mt-1">
                                        <TextInput
                                            id="coupon_code"
                                            value={data.coupon_code}
                                            onChange={(e) =>
                                                setData(
                                                    'coupon_code',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="e.g. WELCOME10"
                                            className="block w-full"
                                        />
                                        <InputError
                                            message={errors.coupon_code}
                                            className="mt-2"
                                        />
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing || !data.plan_id}
                                    className="btn-primary w-full"
                                >
                                    {processing
                                        ? 'Processing…'
                                        : 'Start subscription'}
                                </button>

                                <Link
                                    href={route('parent.dashboard')}
                                    className="btn-secondary w-full"
                                >
                                    Back to dashboard
                                </Link>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

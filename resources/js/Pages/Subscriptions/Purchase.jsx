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

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Subscribe {student.name} · {student.student_code}
                </h2>
            }
        >
            <Head title="Purchase Subscription" />
            <div className="py-8">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white shadow-sm">
                        <div className="border-b border-gray-100 p-5">
                            <h3 className="font-semibold text-gray-900">
                                Choose a plan
                            </h3>
                            <p className="text-sm text-gray-500">
                                Each subject is a separate subscription for this student.
                            </p>
                        </div>

                        <div className="grid gap-3 p-5 grid-cols-1 md:grid-cols-2">
                            {plans.map((plan) => (
                                <label
                                    key={plan.id}
                                    className={`flex cursor-pointer flex-col gap-1 rounded-lg border p-4 transition ${
                                        data.plan_id === String(plan.id)
                                            ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500'
                                            : 'border-gray-200 hover:border-indigo-200'
                                    }`}
                                >
                                    <input
                                        type="radio"
                                        name="plan_id"
                                        value={plan.id}
                                        checked={data.plan_id === String(plan.id)}
                                        onChange={(e) =>
                                            setData('plan_id', e.target.value)
                                        }
                                        className="sr-only"
                                    />
                                    <span className="flex items-center justify-between">
                                        <span className="text-sm font-semibold text-gray-900">
                                            {plan.name}
                                        </span>
                                        {plan.subscribed && (
                                            <span className="rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-700">
                                                active
                                            </span>
                                        )}
                                    </span>
                                    <span className="text-xs text-gray-500">
                                        {plan.subject} · {plan.billing_cycle}
                                        {plan.trial_days
                                            ? ` · ${plan.trial_days}-day trial`
                                            : ''}
                                    </span>
                                    <span className="mt-1 text-lg font-bold text-gray-900">
                                        £{plan.price}
                                        <span className="text-xs font-normal text-gray-500">
                                            /{plan.billing_cycle === 'monthly' ? 'mo' : 'yr'}
                                        </span>
                                    </span>
                                </label>
                            ))}
                        </div>

                        <form onSubmit={submit} className="space-y-4 border-t border-gray-100 p-5">
                            <div>
                                <label className="block text-sm font-medium text-gray-700">
                                    Coupon code (optional)
                                </label>
                                <div className="mt-1">
                                    <TextInput
                                        value={data.coupon_code}
                                        onChange={(e) =>
                                            setData('coupon_code', e.target.value)
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

                            <div className="flex items-center justify-end gap-3">
                                <Link
                                    href={route('parent.dashboard')}
                                    className="rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900"
                                >
                                    Back
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing || !data.plan_id}
                                    className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Start subscription
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
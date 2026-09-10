import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import InputError from '@/Components/InputError';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

function SubscriptionRow({ sub }) {
    return (
        <li className="rounded-lg border border-slate-200 bg-slate-50/50 p-3">
            <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p className="text-sm font-semibold text-slate-900">
                        {sub.plan}
                    </p>
                    <p className="text-xs text-slate-500">
                        {sub.billing_cycle} · £{sub.price} ·{' '}
                        <span className="capitalize">
                            {sub.billing_cycle === 'monthly'
                                ? 'per month'
                                : 'per year'}
                        </span>
                        {sub.expires_at ? ` · renews ${sub.expires_at}` : ''}
                    </p>
                </div>
                <div className="flex items-center gap-2">
                    {sub.renewal_due && (
                        <span className="badge bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200">
                            renewal due
                        </span>
                    )}
                    <Badge status={sub.status} />
                </div>
            </div>
            {sub.payments.length > 0 && (
                <div className="mt-3 border-t border-slate-200 pt-3">
                    <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Payment history
                    </p>
                    <div className="space-y-2">
                        {sub.payments.map((payment) => (
                            <a
                                key={payment.invoice_no}
                                href={payment.receipt_url}
                                className="flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2 text-xs text-slate-600 hover:text-cyan-700"
                            >
                                <span className="font-mono">
                                    {payment.invoice_no}
                                </span>
                                <span>
                                    £{payment.total} · {payment.status}
                                </span>
                            </a>
                        ))}
                    </div>
                </div>
            )}
        </li>
    );
}

function ProgressBar({ value }) {
    return (
        <div className="h-2 overflow-hidden rounded-full bg-slate-200">
            <div
                className="h-full rounded-full bg-cyan-600"
                style={{ width: `${Math.min(100, Math.max(0, value))}%` }}
            />
        </div>
    );
}

function AddStudentForm({ classYears }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        preferred_name: '',
        dob: '',
        school: '',
        gender: '',
        address_line1: '',
        address_line2: '',
        city: '',
        postcode: '',
        country: 'United Kingdom',
        emergency_contact_name: '',
        emergency_contact_phone: '',
        learning_needs: '',
        medical_notes: '',
        class_year_id: classYears[0]?.id ? String(classYears[0].id) : '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('parent.students.store'), {
            preserveScroll: true,
            onSuccess: () =>
                reset(
                    'name',
                    'preferred_name',
                    'dob',
                    'school',
                    'gender',
                    'address_line1',
                    'address_line2',
                    'city',
                    'postcode',
                    'emergency_contact_name',
                    'emergency_contact_phone',
                    'learning_needs',
                    'medical_notes',
                ),
        });
    };

    return (
        <form onSubmit={submit} className="card p-5 sm:p-6">
            <div className="border-b border-slate-100 pb-4">
                <h3 className="font-semibold text-slate-900">Add student</h3>
                <p className="mt-1 text-sm text-slate-500">
                    Capture academic, contact and safeguarding details in one profile.
                </p>
            </div>

            <div className="mt-5 space-y-6">
                <div>
                    <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Student details
                    </p>
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label className="label" htmlFor="name">
                            Full name
                        </label>
                        <TextInput
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className="mt-1 block w-full"
                            placeholder="Full name"
                        />
                        <InputError message={errors.name} className="mt-2" />
                    </div>
                    <div>
                        <label className="label" htmlFor="preferred_name">
                            Preferred name
                        </label>
                        <TextInput
                            id="preferred_name"
                            value={data.preferred_name}
                            onChange={(e) =>
                                setData('preferred_name', e.target.value)
                            }
                            className="mt-1 block w-full"
                            placeholder="Optional"
                        />
                        <InputError
                            message={errors.preferred_name}
                            className="mt-2"
                        />
                    </div>
                    <div>
                        <label className="label" htmlFor="class_year_id">
                            Class year
                        </label>
                        <select
                            id="class_year_id"
                            value={data.class_year_id}
                            onChange={(e) =>
                                setData('class_year_id', e.target.value)
                            }
                            className="input mt-1"
                        >
                            {classYears.map((year) => (
                                <option key={year.id} value={year.id}>
                                    {year.name}
                                </option>
                            ))}
                        </select>
                        <InputError
                            message={errors.class_year_id}
                            className="mt-2"
                        />
                    </div>
                    <div>
                        <label className="label" htmlFor="school">
                            School
                        </label>
                        <TextInput
                            id="school"
                            value={data.school}
                            onChange={(e) => setData('school', e.target.value)}
                            className="mt-1 block w-full"
                            placeholder="Optional"
                        />
                        <InputError message={errors.school} className="mt-2" />
                    </div>
                    <div>
                        <label className="label" htmlFor="dob">
                            Date of birth
                        </label>
                        <TextInput
                            id="dob"
                            type="date"
                            value={data.dob}
                            onChange={(e) => setData('dob', e.target.value)}
                            className="mt-1 block w-full"
                        />
                        <InputError message={errors.dob} className="mt-2" />
                    </div>
                    <div>
                        <label className="label" htmlFor="gender">
                            Gender
                        </label>
                        <select
                            id="gender"
                            value={data.gender}
                            onChange={(e) => setData('gender', e.target.value)}
                            className="input mt-1"
                        >
                            <option value="">Not specified</option>
                            <option value="female">Female</option>
                            <option value="male">Male</option>
                            <option value="other">Other</option>
                            <option value="prefer_not_to_say">
                                Prefer not to say
                            </option>
                        </select>
                        <InputError message={errors.gender} className="mt-2" />
                    </div>
                    </div>
                </div>

                <div>
                    <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Address
                    </p>
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div className="sm:col-span-2">
                            <label className="label" htmlFor="address_line1">
                                Address line 1
                            </label>
                            <TextInput
                                id="address_line1"
                                value={data.address_line1}
                                onChange={(e) =>
                                    setData('address_line1', e.target.value)
                                }
                                className="mt-1 block w-full"
                                placeholder="House, street"
                            />
                            <InputError
                                message={errors.address_line1}
                                className="mt-2"
                            />
                        </div>
                        <div className="sm:col-span-2">
                            <label className="label" htmlFor="address_line2">
                                Address line 2
                            </label>
                            <TextInput
                                id="address_line2"
                                value={data.address_line2}
                                onChange={(e) =>
                                    setData('address_line2', e.target.value)
                                }
                                className="mt-1 block w-full"
                                placeholder="Optional"
                            />
                            <InputError
                                message={errors.address_line2}
                                className="mt-2"
                            />
                        </div>
                        <div>
                            <label className="label" htmlFor="city">
                                City
                            </label>
                            <TextInput
                                id="city"
                                value={data.city}
                                onChange={(e) => setData('city', e.target.value)}
                                className="mt-1 block w-full"
                            />
                            <InputError message={errors.city} className="mt-2" />
                        </div>
                        <div>
                            <label className="label" htmlFor="postcode">
                                Postcode
                            </label>
                            <TextInput
                                id="postcode"
                                value={data.postcode}
                                onChange={(e) =>
                                    setData('postcode', e.target.value)
                                }
                                className="mt-1 block w-full"
                            />
                            <InputError
                                message={errors.postcode}
                                className="mt-2"
                            />
                        </div>
                        <div className="sm:col-span-2">
                            <label className="label" htmlFor="country">
                                Country
                            </label>
                            <TextInput
                                id="country"
                                value={data.country}
                                onChange={(e) =>
                                    setData('country', e.target.value)
                                }
                                className="mt-1 block w-full"
                            />
                            <InputError
                                message={errors.country}
                                className="mt-2"
                            />
                        </div>
                    </div>
                </div>

                <div>
                    <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Support information
                    </p>
                    <div className="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label
                                className="label"
                                htmlFor="emergency_contact_name"
                            >
                                Emergency contact name
                            </label>
                            <TextInput
                                id="emergency_contact_name"
                                value={data.emergency_contact_name}
                                onChange={(e) =>
                                    setData(
                                        'emergency_contact_name',
                                        e.target.value,
                                    )
                                }
                                className="mt-1 block w-full"
                            />
                            <InputError
                                message={errors.emergency_contact_name}
                                className="mt-2"
                            />
                        </div>
                        <div>
                            <label
                                className="label"
                                htmlFor="emergency_contact_phone"
                            >
                                Emergency contact phone
                            </label>
                            <TextInput
                                id="emergency_contact_phone"
                                value={data.emergency_contact_phone}
                                onChange={(e) =>
                                    setData(
                                        'emergency_contact_phone',
                                        e.target.value,
                                    )
                                }
                                className="mt-1 block w-full"
                            />
                            <InputError
                                message={errors.emergency_contact_phone}
                                className="mt-2"
                            />
                        </div>
                        <div>
                            <label className="label" htmlFor="learning_needs">
                                Learning needs
                            </label>
                            <textarea
                                id="learning_needs"
                                value={data.learning_needs}
                                onChange={(e) =>
                                    setData('learning_needs', e.target.value)
                                }
                                className="input mt-1 min-h-24"
                                placeholder="Exam goals, reading level, support needs"
                            />
                            <InputError
                                message={errors.learning_needs}
                                className="mt-2"
                            />
                        </div>
                        <div>
                            <label className="label" htmlFor="medical_notes">
                                Medical notes
                            </label>
                            <textarea
                                id="medical_notes"
                                value={data.medical_notes}
                                onChange={(e) =>
                                    setData('medical_notes', e.target.value)
                                }
                                className="input mt-1 min-h-24"
                                placeholder="Allergies or important notes"
                            />
                            <InputError
                                message={errors.medical_notes}
                                className="mt-2"
                            />
                        </div>
                    </div>
                </div>

                <div className="flex justify-end">
                    <button
                        type="submit"
                        disabled={
                            processing || !data.name || !data.class_year_id
                        }
                        className="btn-primary"
                    >
                        {processing ? 'Adding...' : 'Add student'}
                    </button>
                </div>
            </div>
        </form>
    );
}

export default function Dashboard({ children, hasSubscriptionPlans, classYears }) {
    const { flash } = usePage().props;

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        My Tuition Dashboard
                    </h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        Manage all your children and their subscriptions in one
                        place.
                    </p>
                </div>
            }
        >
            <Head title="Parent Dashboard" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    {flash.success && (
                        <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {flash.success}
                        </div>
                    )}

                    <AddStudentForm classYears={classYears} />

                    {children.length === 0 ? (
                        <div className="card p-10 text-center">
                            <h3 className="text-lg font-semibold">
                                No children added yet
                            </h3>
                            <p className="mt-2 text-slate-500">
                                Add a student to start managing their tuition
                                subscriptions.
                            </p>
                        </div>
                    ) : (
                        <div className="grid gap-6">
                            {children.map((child) => (
                                <div
                                    key={child.id}
                                    className="card overflow-hidden"
                                >
                                    <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-white to-indigo-50/40 p-5">
                                        <div className="flex items-center gap-4">
                                            <span className="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-lg font-bold text-indigo-700">
                                                {child.name.charAt(0)}
                                            </span>
                                            <div>
                                                <h3 className="text-lg font-semibold text-slate-900">
                                                    {child.name}
                                                </h3>
                                                <p className="text-sm text-slate-500">
                                                    {child.student_code}
                                                    {child.preferred_name
                                                        ? ` · known as ${child.preferred_name}`
                                                        : ''}
                                                    {child.school
                                                        ? ` · ${child.school}`
                                                        : ''}
                                                    {child.years.length > 0
                                                        ? ` · ${child.years.join(
                                                              ', ',
                                                          )}`
                                                        : ''}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-3">
                                            <Badge>
                                                {child.accessible_subjects.length}{' '}
                                                subject
                                                {child.accessible_subjects
                                                    .length === 1
                                                    ? ''
                                                    : 's'}
                                            </Badge>
                                            {hasSubscriptionPlans && (
                                                <Link
                                                    href={route(
                                                        'subscriptions.show',
                                                        {
                                                            student: child.id,
                                                        },
                                                    )}
                                                    className="btn-primary"
                                                >
                                                    Add subscription
                                                </Link>
                                            )}
                                        </div>
                                    </div>

                                    <div className="grid gap-6 p-5 lg:grid-cols-[1fr_1fr] sm:p-6">
                                        <div>
                                            <div className="mb-5 rounded-lg border border-slate-200 bg-white p-4">
                                                <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                                    Profile information
                                                </p>
                                                <div className="grid gap-3 text-sm text-slate-600 sm:grid-cols-2">
                                                    <p>
                                                        <span className="font-medium text-slate-800">
                                                            Location:
                                                        </span>{' '}
                                                        {[child.city, child.postcode, child.country]
                                                            .filter(Boolean)
                                                            .join(', ') ||
                                                            'Not added'}
                                                    </p>
                                                    <p>
                                                        <span className="font-medium text-slate-800">
                                                            Emergency:
                                                        </span>{' '}
                                                        {[
                                                            child.emergency_contact_name,
                                                            child.emergency_contact_phone,
                                                        ]
                                                            .filter(Boolean)
                                                            .join(' · ') ||
                                                            'Not added'}
                                                    </p>
                                                    <p className="sm:col-span-2">
                                                        <span className="font-medium text-slate-800">
                                                            Learning:
                                                        </span>{' '}
                                                        {child.learning_needs ||
                                                            'No notes'}
                                                    </p>
                                                    <p className="sm:col-span-2">
                                                        <span className="font-medium text-slate-800">
                                                            Medical:
                                                        </span>{' '}
                                                        {child.medical_notes ||
                                                            'No notes'}
                                                    </p>
                                                </div>
                                            </div>

                                            <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                                Subscriptions
                                            </p>
                                            {child.subscriptions.length === 0 ? (
                                                <p className="rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">
                                                    No active subscriptions.
                                                </p>
                                            ) : (
                                                <ul className="space-y-2">
                                                    {child.subscriptions.map(
                                                        (sub) => (
                                                            <SubscriptionRow
                                                                key={sub.id}
                                                                sub={sub}
                                                            />
                                                        ),
                                                    )}
                                                </ul>
                                            )}
                                        </div>

                                        <div className="space-y-4">
                                            <div className="rounded-lg border border-slate-200 bg-slate-50/60 p-4">
                                                <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                                    Course progress
                                                </p>
                                                {child.courses.length === 0 ? (
                                                    <p className="text-sm text-slate-500">
                                                        No active course access yet.
                                                    </p>
                                                ) : (
                                                    <div className="space-y-3">
                                                        {child.courses.map((course) => (
                                                            <div key={course.id}>
                                                                <div className="mb-1 flex items-center justify-between gap-3 text-sm">
                                                                    <span className="font-medium text-slate-800">
                                                                        {course.year} · {course.subject}
                                                                    </span>
                                                                    <span className="text-slate-500">
                                                                        {course.progress}%
                                                                    </span>
                                                                </div>
                                                                <ProgressBar value={course.progress} />
                                                            </div>
                                                        ))}
                                                    </div>
                                                )}
                                            </div>

                                            <div className="grid gap-4 md:grid-cols-2">
                                                <div className="rounded-lg border border-slate-200 bg-white p-4">
                                                    <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                                        Gradebook
                                                    </p>
                                                    {child.grades.length === 0 ? (
                                                        <p className="text-sm text-slate-500">No scores yet.</p>
                                                    ) : (
                                                        <ul className="space-y-2">
                                                            {child.grades.map((grade) => (
                                                                <li key={`${grade.subject}-${grade.term}`} className="text-sm">
                                                                    <div className="flex justify-between">
                                                                        <span className="font-medium text-slate-800">
                                                                            {grade.subject} · {grade.term}
                                                                        </span>
                                                                        <span className="text-slate-600">
                                                                            {grade.final_score ?? 0}%
                                                                        </span>
                                                                    </div>
                                                                    <p className="text-xs text-slate-400">
                                                                        Quiz {grade.quiz_avg ?? 0}% · Assignment {grade.assignment_avg ?? 0}%
                                                                    </p>
                                                                </li>
                                                            ))}
                                                        </ul>
                                                    )}
                                                </div>

                                                <div className="rounded-lg border border-slate-200 bg-white p-4">
                                                    <p className="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                                        Certificates
                                                    </p>
                                                    {child.certificates.length === 0 ? (
                                                        <p className="text-sm text-slate-500">No certificates issued yet.</p>
                                                    ) : (
                                                        <ul className="space-y-2">
                                                            {child.certificates.map((certificate) => (
                                                                <li key={certificate.code}>
                                                                    <a
                                                                        href={certificate.verify_url}
                                                                        className="text-sm font-medium text-cyan-700 hover:text-cyan-600"
                                                                    >
                                                                        {certificate.course}
                                                                    </a>
                                                                    <p className="font-mono text-xs text-slate-400">
                                                                        {certificate.code}
                                                                    </p>
                                                                </li>
                                                            ))}
                                                        </ul>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

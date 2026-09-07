import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import InputError from '@/Components/InputError';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

function SubscriptionRow({ sub }) {
    return (
        <li className="flex flex-col gap-2 rounded-xl border border-slate-200 bg-slate-50/50 p-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p className="text-sm font-semibold text-slate-900">
                    {sub.plan}
                </p>
                <p className="text-xs text-slate-500">
                    {sub.billing_cycle} · £{sub.price} ·{' '}
                    <span className="capitalize">
                        {sub.billing_cycle === 'monthly' ? 'per month' : 'per year'}
                    </span>
                    {sub.expires_at
                        ? ` · renews ${sub.expires_at}`
                        : ''}
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
        dob: '',
        school: '',
        gender: '',
        class_year_id: classYears[0]?.id ? String(classYears[0].id) : '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('parent.students.store'), {
            preserveScroll: true,
            onSuccess: () => reset('name', 'dob', 'school', 'gender'),
        });
    };

    return (
        <form onSubmit={submit} className="card p-5 sm:p-6">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-end">
                <div className="grid flex-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label className="label" htmlFor="name">
                            Student name
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
                </div>
                <button
                    type="submit"
                    disabled={processing || !data.name || !data.class_year_id}
                    className="btn-primary shrink-0"
                >
                    {processing ? 'Adding...' : 'Add student'}
                </button>
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

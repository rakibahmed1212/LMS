import Brand from '@/Components/Brand';
import { Head, Link } from '@inertiajs/react';

export default function Verify({ certificate, code }) {
    return (
        <div className="min-h-screen bg-slate-50">
            <Head title="Verify Certificate" />
            <main className="mx-auto flex min-h-screen max-w-3xl flex-col justify-center px-4 py-12">
                <div className="mb-8">
                    <Link href="/">
                        <Brand />
                    </Link>
                </div>

                <section className="card overflow-hidden">
                    <div className="border-b border-slate-100 bg-white p-6">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Certificate verification
                        </p>
                        <h1 className="mt-2 text-2xl font-semibold text-slate-900">
                            {certificate
                                ? 'Valid certificate'
                                : 'Certificate not found'}
                        </h1>
                    </div>

                    {certificate ? (
                        <div className="space-y-5 p-6">
                            <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                                This certificate was issued by the LMS platform.
                            </div>
                            <dl className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <dt className="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Student
                                    </dt>
                                    <dd className="mt-1 font-medium text-slate-900">
                                        {certificate.student.name}
                                    </dd>
                                    <dd className="text-sm text-slate-500">
                                        {certificate.student.student_code}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Course
                                    </dt>
                                    <dd className="mt-1 font-medium text-slate-900">
                                        {certificate.course.title}
                                    </dd>
                                    <dd className="text-sm text-slate-500">
                                        {certificate.course.year} ·{' '}
                                        {certificate.course.subject}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Certificate code
                                    </dt>
                                    <dd className="mt-1 font-mono text-sm text-slate-900">
                                        {certificate.code}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Issued
                                    </dt>
                                    <dd className="mt-1 text-slate-900">
                                        {certificate.issued_at}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    ) : (
                        <div className="p-6">
                            <p className="text-sm text-slate-600">
                                No certificate matches code{' '}
                                <span className="font-mono">{code}</span>.
                            </p>
                        </div>
                    )}
                </section>
            </main>
        </div>
    );
}

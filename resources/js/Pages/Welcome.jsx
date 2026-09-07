import Brand from '@/Components/Brand';
import { Head, Link } from '@inertiajs/react';

const metrics = [
    ['4+', 'subjects seeded'],
    ['16+', 'demo lessons'],
    ['40', 'passing tests'],
];

const features = [
    ['Family accounts', 'One parent account can manage multiple children, subscriptions and progress records.'],
    ['Subscription access', 'Course access follows active or trial subscriptions, with expired history preserved.'],
    ['Learning records', 'Video progress, quiz attempts, assignment scores and certificates are linked per student.'],
    ['Admin control', 'Super admins can manage users, roles, billing status and high-level analytics.'],
];

const demoAccounts = [
    ['Parent', 'parent@lms.test', 'password'],
    ['Super Admin', 'admin@lms.test', 'password'],
    ['Tutor', 'tutor@lms.test', 'password'],
];

export default function Welcome({ auth, canLogin, canRegister }) {
    return (
        <>
            <Head title="LearnSphere LMS" />
            <div className="min-h-screen bg-slate-50 text-slate-800">
                <header className="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur">
                    <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                        <Brand />
                        <nav className="flex items-center gap-2">
                            {auth.user ? (
                                <Link href={route('dashboard')} className="btn-primary">
                                    Dashboard
                                </Link>
                            ) : (
                                <>
                                    {canLogin && (
                                        <Link href={route('login')} className="btn-ghost hidden sm:inline-flex">
                                            Log in
                                        </Link>
                                    )}
                                    {canRegister && (
                                        <Link href={route('register')} className="btn-primary">
                                            Get started
                                        </Link>
                                    )}
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                <section className="relative min-h-[calc(100vh-4rem)] overflow-hidden bg-white">
                    <img
                        src="/images/tuition-hero.png"
                        alt=""
                        className="absolute inset-0 h-full w-full object-cover"
                    />
                    <div className="absolute inset-0 bg-gradient-to-r from-white via-white/90 to-white/20" />
                    <div className="relative mx-auto flex min-h-[calc(100vh-4rem)] max-w-7xl items-center px-4 py-14 sm:px-6 lg:px-8">
                        <div className="max-w-2xl">
                            <span className="badge bg-cyan-50 text-cyan-700 ring-1 ring-inset ring-cyan-200">
                                Subscription-based online tuition
                            </span>
                            <h1 className="mt-6 text-4xl font-semibold leading-tight text-slate-950 sm:text-6xl">
                                LearnSphere LMS
                            </h1>
                            <p className="mt-5 max-w-xl text-lg leading-8 text-slate-600">
                                A parent-first tuition platform with student profiles, subject subscriptions, progress tracking, assignments, certificates and admin controls ready for demo.
                            </p>
                            <div className="mt-8 flex flex-wrap gap-3">
                                <Link href={auth.user ? route('dashboard') : route('login')} className="btn-primary px-6">
                                    Open platform
                                </Link>
                                <Link href={route('courses.index')} className="btn-secondary px-6">
                                    Browse courses
                                </Link>
                            </div>
                            <dl className="mt-10 grid max-w-lg grid-cols-3 gap-3">
                                {metrics.map(([value, label]) => (
                                    <div key={label} className="rounded-lg border border-slate-200 bg-white/85 p-4 shadow-sm">
                                        <dt className="text-2xl font-semibold text-slate-950">{value}</dt>
                                        <dd className="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500">{label}</dd>
                                    </div>
                                ))}
                            </dl>
                        </div>
                    </div>
                </section>

                <section className="border-y border-slate-200 bg-white py-12">
                    <div className="mx-auto grid max-w-7xl gap-4 px-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
                        {features.map(([title, description]) => (
                            <article key={title} className="rounded-lg border border-slate-200 p-5">
                                <h2 className="text-base font-semibold text-slate-950">{title}</h2>
                                <p className="mt-2 text-sm leading-6 text-slate-500">{description}</p>
                            </article>
                        ))}
                    </div>
                </section>

                <section className="bg-slate-50 py-14">
                    <div className="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_420px] lg:px-8">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-wide text-cyan-700">
                                Demo ready
                            </p>
                            <h2 className="mt-2 text-3xl font-semibold text-slate-950">
                                Seeded with realistic parent, student and admin data
                            </h2>
                            <p className="mt-4 max-w-2xl leading-7 text-slate-600">
                                The database now includes active, trial, past-due and unsubscribed student scenarios, plus lessons, worksheets, quizzes, assignments, live classes, gradebook records and certificate verification data.
                            </p>
                        </div>

                        <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 className="font-semibold text-slate-950">Demo accounts</h3>
                            <div className="mt-4 space-y-3">
                                {demoAccounts.map(([role, email, password]) => (
                                    <div key={email} className="flex items-center justify-between gap-3 rounded-lg bg-slate-50 p-3 text-sm">
                                        <div>
                                            <p className="font-medium text-slate-900">{role}</p>
                                            <p className="font-mono text-xs text-slate-500">{email}</p>
                                        </div>
                                        <span className="font-mono text-xs text-slate-500">{password}</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </section>

                <footer className="border-t border-slate-200 bg-white">
                    <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 py-6 text-sm text-slate-500 sm:flex-row sm:px-6 lg:px-8">
                        <Brand className="opacity-80" />
                        <p>Subscription-based online tuition platform</p>
                    </div>
                </footer>
            </div>
        </>
    );
}

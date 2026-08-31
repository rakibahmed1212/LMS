import Brand from '@/Components/Brand';
import { Head, Link } from '@inertiajs/react';

const features = [
    {
        title: 'One account, all your children',
        desc: 'Manage every child from a single parent account and switch between them in one tap.',
        icon: (
            <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        ),
    },
    {
        title: 'Subject-by-subject subscriptions',
        desc: 'Subscribe your child to exactly the subjects they need — each billed separately, monthly or yearly.',
        icon: (
            <path d="M4 6h16M4 12h16M4 18h7" />
        ),
    },
    {
        title: 'Track real progress',
        desc: 'Watch percentages, quiz results, assignments and course completion — all in one dashboard.',
        icon: (
            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        ),
    },
    {
        title: 'Stay on top of renewals',
        desc: 'Automatic recurring billing with clear reminders, so tuition never lapses without you knowing.',
        icon: (
            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        ),
    },
];

const steps = [
    {
        step: '01',
        title: 'Create your parent account',
        desc: 'Sign up in seconds with just an email or phone number.',
    },
    {
        step: '02',
        title: 'Add your children',
        desc: 'Each child gets a unique ID and can be enrolled in multiple subjects.',
    },
    {
        step: '03',
        title: 'Pick a subscription',
        desc: 'Choose monthly or annual plans for each subject — with bundles that save more.',
    },
    {
        step: '04',
        title: 'Watch progress grow',
        desc: 'Follow lessons, quizzes and grades as your child learns at their own pace.',
    },
];

export default function Welcome({ auth, canLogin, canRegister }) {
    return (
        <>
            <Head title="Welcome" />
            <div className="flex min-h-screen flex-col bg-slate-50">
                <header className="sticky top-0 z-20 border-b border-white/10 bg-white/80 backdrop-blur">
                    <div className="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                        <Brand />

                        <nav className="flex items-center gap-2">
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="btn-primary"
                                >
                                    Go to Dashboard
                                </Link>
                            ) : (
                                <>
                                    {canLogin && (
                                        <Link
                                            href={route('login')}
                                            className="btn-ghost hidden sm:inline-flex"
                                        >
                                            Log in
                                        </Link>
                                    )}
                                    {canRegister && (
                                        <Link
                                            href={route('register')}
                                            className="btn-primary"
                                        >
                                            Get started
                                        </Link>
                                    )}
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                <section className="relative overflow-hidden">
                    <div
                        className="brand-gradient absolute inset-0"
                        aria-hidden="true"
                    />
                    <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.25),transparent_55%)]" />
                    <div className="relative mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-28">
                        <div className="mx-auto max-w-3xl text-center text-white">
                            <span className="badge bg-white/20 text-white ring-1 ring-inset ring-white/30">
                                Subscription-based online tuition
                            </span>
                            <h1 className="mt-6 text-4xl font-bold leading-tight tracking-tight sm:text-6xl">
                                One dashboard for every
                                <span className="block">child's learning</span>
                            </h1>
                            <p className="mx-auto mt-6 max-w-xl text-lg text-indigo-100">
                                Add your children, subscribe to the subjects they
                                need, and watch their progress grow — all from a
                                single parent account.
                            </p>
                            <div className="mt-10 flex flex-wrap items-center justify-center gap-3">
                                {auth.user ? (
                                    <Link
                                        href={route('dashboard')}
                                        className="btn bg-white text-indigo-700 shadow-lg hover:bg-indigo-50"
                                    >
                                        Open your dashboard
                                    </Link>
                                ) : (
                                    <>
                                        {canRegister && (
                                            <Link
                                                href={route('register')}
                                                className="btn bg-white px-6 text-indigo-700 shadow-lg hover:bg-indigo-50"
                                            >
                                                Create a free account
                                            </Link>
                                        )}
                                        {canLogin && (
                                            <Link
                                                href={route('login')}
                                                className="btn bg-white/10 text-white ring-1 ring-inset ring-white/40 hover:bg-white/20"
                                            >
                                                Log in
                                            </Link>
                                        )}
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </section>

                <section className="py-16 sm:py-20">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6">
                        <div className="mb-12 text-center">
                            <h2 className="text-3xl font-bold sm:text-4xl">
                                Everything a parent needs
                            </h2>
                            <p className="mx-auto mt-3 max-w-2xl text-slate-500">
                                A complete tuition experience designed around
                                families, not just individual students.
                            </p>
                        </div>

                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            {features.map((feature) => (
                                <div key={feature.title} className="card p-6">
                                    <span className="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                        <svg
                                            className="h-6 w-6"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            strokeWidth="1.7"
                                        >
                                            {feature.icon}
                                        </svg>
                                    </span>
                                    <h3 className="text-base font-semibold">
                                        {feature.title}
                                    </h3>
                                    <p className="mt-2 text-sm leading-relaxed text-slate-500">
                                        {feature.desc}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="brand-gradient-soft py-16 sm:py-20">
                    <div className="mx-auto max-w-6xl px-4 sm:px-6">
                        <div className="mb-12 text-center">
                            <h2 className="text-3xl font-bold sm:text-4xl">
                                How it works
                            </h2>
                            <p className="mx-auto mt-3 max-w-2xl text-slate-500">
                                Four simple steps between you and better tuition
                                management.
                            </p>
                        </div>

                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            {steps.map((item) => (
                                <div key={item.step} className="relative">
                                    <span className="text-4xl font-bold text-indigo-200">
                                        {item.step}
                                    </span>
                                    <h3 className="mt-2 text-base font-semibold">
                                        {item.title}
                                    </h3>
                                    <p className="mt-2 text-sm leading-relaxed text-slate-500">
                                        {item.desc}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="py-16 sm:py-20">
                    <div className="mx-auto max-w-4xl px-4 sm:px-6">
                        <div className="brand-gradient rounded-3xl p-10 text-center text-white shadow-xl sm:p-14">
                            <h2 className="text-3xl font-bold sm:text-4xl">
                                Ready for your children to get ahead?
                            </h2>
                            <p className="mx-auto mt-4 max-w-xl text-indigo-100">
                                Join today and manage all your children's tuition
                                subscriptions in one place.
                            </p>
                            <div className="mt-8">
                                {canRegister ? (
                                    <Link
                                        href={route('register')}
                                        className="btn bg-white px-8 text-indigo-700 shadow-lg hover:bg-indigo-50"
                                    >
                                        Get started free
                                    </Link>
                                ) : (
                                    <Link
                                        href={route('login')}
                                        className="btn bg-white px-8 text-indigo-700 shadow-lg hover:bg-indigo-50"
                                    >
                                        Log in to your account
                                    </Link>
                                )}
                            </div>
                        </div>
                    </div>
                </section>

                <footer className="border-t border-slate-200 bg-white">
                    <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 py-8 text-sm text-slate-500 sm:flex-row sm:px-6">
                        <Brand className="opacity-80" />
                        <p>
                            © {new Date().getFullYear()} LearnSphere. All rights
                            reserved.
                        </p>
                    </div>
                </footer>
            </div>
        </>
    );
}

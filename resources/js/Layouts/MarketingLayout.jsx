import Brand from '@/Components/Brand';
import { Link, usePage } from '@inertiajs/react';

const nav = [
    ['Courses', 'courses.index'],
    ['About', 'about'],
    ['FAQ', 'faq'],
    ['Contact', 'contact'],
];

export default function MarketingLayout({ children }) {
    const auth = usePage().props.auth;

    return (
        <div className="min-h-screen bg-slate-50 text-slate-800">
            <header className="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <Link href="/">
                        <Brand />
                    </Link>
                    <nav className="hidden items-center gap-1 md:flex">
                        {nav.map(([label, routeName]) => (
                            <Link
                                key={routeName}
                                href={route(routeName)}
                                className="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                            >
                                {label}
                            </Link>
                        ))}
                    </nav>
                    <div className="flex items-center gap-2">
                        {auth.user ? (
                            <Link href={route('dashboard')} className="btn-primary">
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link href={route('login')} className="btn-ghost hidden sm:inline-flex">
                                    Log in
                                </Link>
                                <Link href={route('register')} className="btn-primary">
                                    Get started
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </header>

            <main>{children}</main>

            <footer className="border-t border-slate-200 bg-white">
                <div className="mx-auto grid max-w-7xl gap-6 px-4 py-8 text-sm text-slate-500 sm:px-6 md:grid-cols-[1fr_auto] lg:px-8">
                    <div>
                        <Brand className="opacity-80" />
                        <p className="mt-3 max-w-lg">
                            Parent-first online tuition with subscription access, course progress, assessments and certificates.
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-4">
                        <Link href={route('privacy')} className="hover:text-slate-900">Privacy</Link>
                        <Link href={route('terms')} className="hover:text-slate-900">Terms</Link>
                        <Link href={route('contact')} className="hover:text-slate-900">Contact</Link>
                    </div>
                </div>
            </footer>
        </div>
    );
}

import Brand from '@/Components/Brand';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="relative flex min-h-screen flex-col bg-slate-50">
            <div
                className="brand-gradient-soft absolute inset-x-0 top-0 h-64"
                aria-hidden="true"
            />

            <header className="relative z-10">
                <div className="mx-auto flex max-w-md items-center px-4 py-8">
                    <Link href="/">
                        <Brand />
                    </Link>
                </div>
            </header>

            <div className="relative z-10 flex flex-1 items-center justify-center px-4 pb-16">
                <div className="w-full max-w-md">
                    <div className="card p-8 shadow-lg">
                        {children}
                    </div>
                    <p className="mt-6 text-center text-sm text-slate-400">
                        Subscription-based online tuition platform
                    </p>
                </div>
            </div>
        </div>
    );
}

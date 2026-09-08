import MarketingLayout from '@/Layouts/MarketingLayout';
import { Head, Link } from '@inertiajs/react';

export default function Contact() {
    return (
        <MarketingLayout>
            <Head title="Contact" />
            <section className="bg-white py-16 sm:py-20">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <p className="text-sm font-semibold uppercase tracking-wide text-cyan-700">
                        Contact
                    </p>
                    <h1 className="mt-3 text-4xl font-semibold text-slate-950">
                        Talk to the LearnSphere team
                    </h1>
                    <p className="mt-4 max-w-2xl leading-7 text-slate-600">
                        For admissions, billing, course support or platform setup, reach out through the channels below.
                    </p>

                    <div className="mt-8 grid gap-4 sm:grid-cols-3">
                        <div className="rounded-lg border border-slate-200 p-5">
                            <h2 className="font-semibold text-slate-900">Email</h2>
                            <p className="mt-2 text-sm text-slate-500">support@learnsphere.test</p>
                        </div>
                        <div className="rounded-lg border border-slate-200 p-5">
                            <h2 className="font-semibold text-slate-900">Phone</h2>
                            <p className="mt-2 text-sm text-slate-500">+880 1712 345678</p>
                        </div>
                        <div className="rounded-lg border border-slate-200 p-5">
                            <h2 className="font-semibold text-slate-900">Hours</h2>
                            <p className="mt-2 text-sm text-slate-500">Sun-Thu, 10:00-18:00</p>
                        </div>
                    </div>

                    <div className="mt-8 rounded-lg border border-cyan-200 bg-cyan-50 p-5">
                        <p className="text-sm font-medium text-cyan-900">
                            Parents can create an account, add children and subscribe directly from the course portal.
                        </p>
                        <Link href={route('register')} className="btn-primary mt-4">
                            Create account
                        </Link>
                    </div>
                </div>
            </section>
        </MarketingLayout>
    );
}

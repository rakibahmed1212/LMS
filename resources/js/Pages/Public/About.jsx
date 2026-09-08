import MarketingLayout from '@/Layouts/MarketingLayout';
import { Head, Link } from '@inertiajs/react';

const capabilities = [
    'Parent account with multiple student profiles',
    'Subject-by-subject subscriptions and bundle pricing',
    'Video lessons with progress and resume tracking',
    'Quizzes, assignments, gradebook and certificates',
    'Admin analytics, user roles and subscription health',
    'Tutor/content manager access scopes',
];

export default function About() {
    return (
        <MarketingLayout>
            <Head title="About LearnSphere" />
            <section className="bg-white py-16 sm:py-20">
                <div className="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[1fr_420px] lg:px-8">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-wide text-cyan-700">
                            About us
                        </p>
                        <h1 className="mt-3 text-4xl font-semibold leading-tight text-slate-950 sm:text-5xl">
                            Built for families, students and tuition teams
                        </h1>
                        <p className="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                            LearnSphere is a subscription-based online tuition LMS where parents manage children, students learn through structured courses, and admins keep subscriptions, learning outcomes and staff access under control.
                        </p>
                        <div className="mt-8 flex flex-wrap gap-3">
                            <Link href={route('courses.index')} className="btn-primary">
                                Browse courses
                            </Link>
                            <Link href={route('contact')} className="btn-secondary">
                                Contact us
                            </Link>
                        </div>
                    </div>
                    <div className="rounded-lg border border-slate-200 bg-slate-50 p-6">
                        <h2 className="font-semibold text-slate-900">
                            Platform coverage
                        </h2>
                        <ul className="mt-5 space-y-3">
                            {capabilities.map((item) => (
                                <li key={item} className="flex gap-3 text-sm text-slate-600">
                                    <span className="mt-1 h-2 w-2 rounded-full bg-cyan-600" />
                                    <span>{item}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </section>
        </MarketingLayout>
    );
}

import MarketingLayout from '@/Layouts/MarketingLayout';
import { Head } from '@inertiajs/react';

const sections = [
    ['Student data', 'We keep student profiles, learning progress, subscriptions and assessment records only for platform operations.'],
    ['Parent control', 'Parents manage child accounts, subscription choices and access to billing history.'],
    ['Payments', 'Raw card data should be handled by a PCI-compliant payment gateway, never stored by the LMS application.'],
    ['Retention', 'Progress and certificate history are preserved after cancellation unless lawful deletion is requested.'],
];

export default function Privacy() {
    return (
        <MarketingLayout>
            <Head title="Privacy Policy" />
            <section className="bg-white py-16">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    <h1 className="text-4xl font-semibold text-slate-950">Privacy Policy</h1>
                    <p className="mt-4 leading-7 text-slate-600">
                        This demo policy outlines how a child-focused tuition platform should treat family, student and learning data.
                    </p>
                    <div className="mt-8 space-y-5">
                        {sections.map(([title, body]) => (
                            <section key={title} className="rounded-lg border border-slate-200 p-5">
                                <h2 className="font-semibold text-slate-900">{title}</h2>
                                <p className="mt-2 text-sm leading-6 text-slate-600">{body}</p>
                            </section>
                        ))}
                    </div>
                </div>
            </section>
        </MarketingLayout>
    );
}

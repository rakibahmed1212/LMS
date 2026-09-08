import MarketingLayout from '@/Layouts/MarketingLayout';
import { Head, Link } from '@inertiajs/react';

const faqs = [
    ['Can one parent manage multiple students?', 'Yes. A parent account can add multiple children and subscribe each child to different subjects.'],
    ['Is course access public or private?', 'Neither. Access is controlled by active subscriptions per student and subject.'],
    ['What happens after a subscription expires?', 'Access is revoked, but progress, quiz scores, assignments and certificates remain stored.'],
    ['Can students try a course first?', 'Courses can include free preview lessons and subscription plans may include trial days.'],
    ['Does the platform support tutors?', 'Yes. Tutor and content manager roles are seeded, with scoped course and student assignment data.'],
    ['Is payment live?', 'The current demo simulates successful payment. Stripe or SSLCommerz webhook integration is the next production step.'],
];

export default function Faq() {
    return (
        <MarketingLayout>
            <Head title="FAQ" />
            <section className="bg-white py-16">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <p className="text-sm font-semibold uppercase tracking-wide text-cyan-700">
                        FAQ
                    </p>
                    <h1 className="mt-3 text-4xl font-semibold text-slate-950">
                        Common questions
                    </h1>
                    <div className="mt-8 divide-y divide-slate-100 rounded-lg border border-slate-200">
                        {faqs.map(([question, answer]) => (
                            <section key={question} className="p-5">
                                <h2 className="font-semibold text-slate-900">{question}</h2>
                                <p className="mt-2 text-sm leading-6 text-slate-600">{answer}</p>
                            </section>
                        ))}
                    </div>
                    <div className="mt-8 rounded-lg border border-cyan-200 bg-cyan-50 p-5">
                        <p className="text-sm font-medium text-cyan-950">
                            Browse the seeded course portal to see the subscription flow in action.
                        </p>
                        <Link href={route('courses.index')} className="btn-primary mt-4">
                            Browse courses
                        </Link>
                    </div>
                </div>
            </section>
        </MarketingLayout>
    );
}

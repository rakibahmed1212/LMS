import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import { Head } from '@inertiajs/react';

export default function Platform({ integrations }) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        Platform Readiness
                    </h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        Payment, video, notification, backup and security modules.
                    </p>
                </div>
            }
        >
            <Head title="Platform Readiness" />
            <div className="py-8">
                <div className="mx-auto grid max-w-5xl gap-4 px-4 sm:px-6 lg:px-8">
                    {integrations.map((integration) => (
                        <article
                            key={integration.name}
                            className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
                        >
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h3 className="font-semibold text-slate-950">
                                        {integration.name}
                                    </h3>
                                    <p className="mt-1 text-sm text-slate-500">
                                        Provider: {integration.provider}
                                    </p>
                                    <p className="mt-3 text-sm leading-6 text-slate-600">
                                        {integration.notes}
                                    </p>
                                </div>
                                <Badge status={integration.status}>
                                    {integration.status.replace('_', ' ')}
                                </Badge>
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

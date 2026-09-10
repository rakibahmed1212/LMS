import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function AuditLogs({ logs, filters }) {
    const [q, setQ] = useState(filters.q || '');

    useEffect(() => {
        const timeout = setTimeout(() => {
            router.get(route('admin.audit-logs.index'), { q }, {
                preserveState: true,
                replace: true,
            });
        }, 300);

        return () => clearTimeout(timeout);
    }, [q]);

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        Audit Logs
                    </h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        Admin, subscription and profile actions.
                    </p>
                </div>
            }
        >
            <Head title="Audit Logs" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <label className="label" htmlFor="audit_q">Search</label>
                        <input
                            id="audit_q"
                            value={q}
                            onChange={(e) => setQ(e.target.value)}
                            className="input mt-1"
                            placeholder="Action or entity"
                        />
                    </div>

                    <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200 text-sm">
                                <thead className="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-400">
                                    <tr>
                                        <th className="px-4 py-3 font-semibold">Action</th>
                                        <th className="px-4 py-3 font-semibold">Actor</th>
                                        <th className="px-4 py-3 font-semibold">Entity</th>
                                        <th className="px-4 py-3 font-semibold">IP</th>
                                        <th className="px-4 py-3 font-semibold">Time</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {logs.data.map((log) => (
                                        <tr key={log.id}>
                                            <td className="px-4 py-3 font-semibold text-slate-900">
                                                {log.action}
                                            </td>
                                            <td className="px-4 py-3 text-slate-600">
                                                {log.actor
                                                    ? `${log.actor.name} · ${log.actor.email}`
                                                    : 'System'}
                                            </td>
                                            <td className="px-4 py-3 text-slate-600">
                                                {log.entity_type || '-'} #{log.entity_id || '-'}
                                            </td>
                                            <td className="px-4 py-3 text-slate-600">
                                                {log.ip_address || '-'}
                                            </td>
                                            <td className="px-4 py-3 text-slate-600">
                                                {log.created_at}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {logs.links.length > 3 && (
                        <div className="flex flex-wrap gap-2">
                            {logs.links.map((link) => (
                                <Link
                                    key={link.label}
                                    href={link.url || '#'}
                                    preserveScroll
                                    className={`rounded-lg border px-3 py-2 text-sm ${
                                        link.active
                                            ? 'border-cyan-600 bg-cyan-600 text-white'
                                            : 'border-slate-200 bg-white text-slate-600'
                                    } ${!link.url ? 'pointer-events-none opacity-50' : ''}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

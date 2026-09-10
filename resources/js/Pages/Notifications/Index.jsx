import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function NotificationsIndex({ notifications }) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        Notifications
                    </h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        Subscription, quiz, assignment and certificate updates.
                    </p>
                </div>
            }
        >
            <Head title="Notifications" />
            <div className="py-8">
                <div className="mx-auto max-w-4xl space-y-4 px-4 sm:px-6 lg:px-8">
                    {notifications.data.length === 0 ? (
                        <div className="rounded-lg border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
                            No notifications yet.
                        </div>
                    ) : (
                        notifications.data.map((notification) => (
                            <div
                                key={notification.id}
                                className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
                            >
                                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p className="text-sm font-semibold text-slate-950">
                                            {notification.title}
                                        </p>
                                        <p className="mt-1 text-sm text-slate-500">
                                            {notification.body}
                                        </p>
                                        <p className="mt-2 text-xs capitalize text-slate-400">
                                            {notification.type.replace('_', ' ')} · {notification.channel} · {notification.created_at}
                                        </p>
                                    </div>
                                    {!notification.read_at && (
                                        <Link
                                            href={route('notifications.read', notification.id)}
                                            method="patch"
                                            preserveScroll
                                            className="btn-secondary shrink-0"
                                        >
                                            Mark read
                                        </Link>
                                    )}
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

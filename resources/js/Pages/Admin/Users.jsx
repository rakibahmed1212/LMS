import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import InputError from '@/Components/InputError';
import TextInput from '@/Components/TextInput';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

function CreateUserForm({ roles }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        phone: '',
        locale: 'en',
        password: '',
        password_confirmation: '',
        role_ids: roles
            .filter((role) => role.name === 'parent')
            .map((role) => role.id),
        is_active: true,
    });

    const toggleRole = (roleId) => {
        setData(
            'role_ids',
            data.role_ids.includes(roleId)
                ? data.role_ids.filter((id) => id !== roleId)
                : [...data.role_ids, roleId],
        );
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('admin.users.store'), {
            preserveScroll: true,
            onSuccess: () =>
                reset(
                    'name',
                    'email',
                    'phone',
                    'password',
                    'password_confirmation',
                ),
        });
    };

    return (
        <form onSubmit={submit} className="card p-5 sm:p-6">
            <div className="flex flex-col gap-1 border-b border-slate-100 pb-4">
                <h3 className="font-semibold text-slate-900">
                    Create user
                </h3>
                <p className="text-sm text-slate-500">
                    Add admin, tutor, content manager or parent accounts from the backend.
                </p>
            </div>

            <div className="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label className="label" htmlFor="create_name">
                        Name
                    </label>
                    <TextInput
                        id="create_name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.name} className="mt-2" />
                </div>
                <div>
                    <label className="label" htmlFor="create_email">
                        Email
                    </label>
                    <TextInput
                        id="create_email"
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.email} className="mt-2" />
                </div>
                <div>
                    <label className="label" htmlFor="create_phone">
                        Phone
                    </label>
                    <TextInput
                        id="create_phone"
                        value={data.phone}
                        onChange={(e) => setData('phone', e.target.value)}
                        className="mt-1 block w-full"
                        placeholder="Optional"
                    />
                    <InputError message={errors.phone} className="mt-2" />
                </div>
                <div>
                    <label className="label" htmlFor="create_locale">
                        Language
                    </label>
                    <select
                        id="create_locale"
                        value={data.locale}
                        onChange={(e) => setData('locale', e.target.value)}
                        className="input mt-1"
                    >
                        <option value="en">English</option>
                        <option value="bn">Bengali</option>
                    </select>
                    <InputError message={errors.locale} className="mt-2" />
                </div>
                <div>
                    <label className="label" htmlFor="create_password">
                        Password
                    </label>
                    <TextInput
                        id="create_password"
                        type="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>
                <div>
                    <label
                        className="label"
                        htmlFor="create_password_confirmation"
                    >
                        Confirm password
                    </label>
                    <TextInput
                        id="create_password_confirmation"
                        type="password"
                        value={data.password_confirmation}
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                        className="mt-1 block w-full"
                    />
                    <InputError
                        message={errors.password_confirmation}
                        className="mt-2"
                    />
                </div>
                <div className="md:col-span-2">
                    <p className="label">Roles</p>
                    <div className="mt-2 flex flex-wrap gap-2">
                        {roles.map((role) => (
                            <label
                                key={role.id}
                                className={`inline-flex cursor-pointer items-center gap-2 rounded-lg border px-2.5 py-1.5 text-xs font-semibold transition ${
                                    data.role_ids.includes(role.id)
                                        ? 'border-indigo-200 bg-indigo-50 text-indigo-700'
                                        : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'
                                }`}
                            >
                                <input
                                    type="checkbox"
                                    checked={data.role_ids.includes(role.id)}
                                    onChange={() => toggleRole(role.id)}
                                    className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                {role.label}
                            </label>
                        ))}
                    </div>
                    <InputError message={errors.role_ids} className="mt-2" />
                </div>
            </div>

            <div className="mt-5 flex flex-wrap items-center justify-between gap-3">
                <label className="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input
                        type="checkbox"
                        checked={data.is_active}
                        onChange={(e) => setData('is_active', e.target.checked)}
                        className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    Active account
                </label>
                <button
                    type="submit"
                    disabled={
                        processing ||
                        data.role_ids.length === 0 ||
                        !data.name ||
                        !data.email ||
                        !data.password
                    }
                    className="btn-primary"
                >
                    {processing ? 'Creating...' : 'Create user'}
                </button>
            </div>
        </form>
    );
}

function UserRow({ user, roles }) {
    const { data, setData, patch, processing, errors, recentlySuccessful } =
        useForm({
            role_ids: user.roles.map((role) => role.id),
            is_active: user.is_active,
        });

    const toggleRole = (roleId) => {
        setData(
            'role_ids',
            data.role_ids.includes(roleId)
                ? data.role_ids.filter((id) => id !== roleId)
                : [...data.role_ids, roleId],
        );
    };

    const submit = (e) => {
        e.preventDefault();
        patch(route('admin.users.update', { user: user.id }), {
            preserveScroll: true,
        });
    };

    return (
        <tr className="align-top hover:bg-slate-50/60">
            <td className="px-5 py-4">
                <p className="font-medium text-slate-900">{user.name}</p>
                <p className="text-xs text-slate-500">{user.email}</p>
                {user.phone && (
                    <p className="text-xs text-slate-400">{user.phone}</p>
                )}
            </td>
            <td className="px-5 py-4">
                <Badge status={user.is_active ? 'active' : 'blocked'} />
            </td>
            <td className="px-5 py-4">
                <form onSubmit={submit} className="space-y-3">
                    <div className="flex flex-wrap gap-2">
                        {roles.map((role) => (
                            <label
                                key={role.id}
                                className={`inline-flex cursor-pointer items-center gap-2 rounded-lg border px-2.5 py-1.5 text-xs font-semibold transition ${
                                    data.role_ids.includes(role.id)
                                        ? 'border-indigo-200 bg-indigo-50 text-indigo-700'
                                        : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'
                                }`}
                            >
                                <input
                                    type="checkbox"
                                    checked={data.role_ids.includes(role.id)}
                                    onChange={() => toggleRole(role.id)}
                                    className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                {role.label}
                            </label>
                        ))}
                    </div>
                    <InputError message={errors.role_ids} />
                    <InputError message={errors.is_active} />
                    <div className="flex flex-wrap items-center gap-3">
                        <label className="inline-flex items-center gap-2 text-sm text-slate-600">
                            <input
                                type="checkbox"
                                checked={data.is_active}
                                onChange={(e) =>
                                    setData('is_active', e.target.checked)
                                }
                                className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            Active account
                        </label>
                        <button
                            type="submit"
                            disabled={processing || data.role_ids.length === 0}
                            className="btn-secondary px-3 py-1.5 text-xs"
                        >
                            {processing ? 'Saving...' : 'Save'}
                        </button>
                        {recentlySuccessful && (
                            <span className="text-xs font-medium text-emerald-600">
                                Saved
                            </span>
                        )}
                    </div>
                </form>
            </td>
            <td className="px-5 py-4 text-sm text-slate-500">
                {user.created_at}
            </td>
        </tr>
    );
}

export default function Users({ users, roles, filters }) {
    const { flash } = usePage().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [role, setRole] = useState(filters.role ?? '');

    useEffect(() => {
        const timeout = window.setTimeout(() => {
            router.get(
                route('admin.users.index'),
                { search, role },
                { preserveState: true, replace: true },
            );
        }, 250);

        return () => window.clearTimeout(timeout);
    }, [search, role]);

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        User Management
                    </h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        Manage staff, parent and student account roles.
                    </p>
                </div>
            }
        >
            <Head title="Users" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    {flash.success && (
                        <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {flash.success}
                        </div>
                    )}

                    <CreateUserForm roles={roles} />

                    <div className="card p-5 sm:p-6">
                        <div className="grid gap-4 md:grid-cols-[1fr_240px]">
                            <div>
                                <label className="label" htmlFor="search">
                                    Search
                                </label>
                                <TextInput
                                    id="search"
                                    value={search}
                                    onChange={(e) =>
                                        setSearch(e.target.value)
                                    }
                                    className="mt-1 block w-full"
                                    placeholder="Name, email or phone"
                                />
                            </div>
                            <div>
                                <label className="label" htmlFor="role">
                                    Role
                                </label>
                                <select
                                    id="role"
                                    value={role}
                                    onChange={(e) => setRole(e.target.value)}
                                    className="input mt-1"
                                >
                                    <option value="">All roles</option>
                                    {roles.map((item) => (
                                        <option
                                            key={item.id}
                                            value={item.name}
                                        >
                                            {item.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div className="card overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200 text-sm">
                                <thead className="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th className="px-5 py-3 font-semibold">
                                            User
                                        </th>
                                        <th className="px-5 py-3 font-semibold">
                                            Status
                                        </th>
                                        <th className="px-5 py-3 font-semibold">
                                            Roles
                                        </th>
                                        <th className="px-5 py-3 font-semibold">
                                            Created
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {users.data.map((user) => (
                                        <UserRow
                                            key={user.id}
                                            user={user}
                                            roles={roles}
                                        />
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <div className="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4 text-sm text-slate-500">
                            <span>
                                Showing {users.from ?? 0}-{users.to ?? 0} of{' '}
                                {users.total}
                            </span>
                            <div className="flex flex-wrap gap-2">
                                {users.links.map((link) => (
                                    <Link
                                        key={link.label}
                                        href={link.url ?? '#'}
                                        preserveScroll
                                        className={`rounded-lg border px-3 py-1.5 text-sm ${
                                            link.active
                                                ? 'border-indigo-200 bg-indigo-50 text-indigo-700'
                                                : 'border-slate-200 text-slate-600'
                                        } ${
                                            link.url
                                                ? 'hover:bg-slate-50'
                                                : 'pointer-events-none opacity-50'
                                        }`}
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

import ApplicationLogo from '@/Components/ApplicationLogo';
import Brand from '@/Components/Brand';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function AuthenticatedLayout({ header, children }) {
    const user = usePage().props.auth.user;
    const roles = user?.roles ?? [];
    const isParent = roles.includes('parent');
    const isAdmin = roles.includes('super_admin');
    const isStaff = roles.includes('tutor') || roles.includes('content_manager');

    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    const links = [];
    if (isParent) {
        links.push({
            href: route('parent.dashboard'),
            active: route().current('parent.dashboard'),
            label: 'My Children',
        });
    }
    if (isAdmin || isStaff || isParent) {
        links.push({
            href: route('plans.index'),
            active: route().current('plans.index'),
            label: 'Plans',
        });
    }
    if (isAdmin) {
        links.push({
            href: route('admin.dashboard'),
            active: route().current('admin.dashboard'),
            label: 'Admin',
        });
    }

    return (
        <div className="flex min-h-screen flex-col bg-slate-50">
            <header className="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-8">
                        <Link href="/">
                            <Brand />
                        </Link>

                        <nav className="hidden items-center gap-1 sm:flex">
                            {links.map((link) => (
                                <NavLink
                                    key={link.href}
                                    href={link.href}
                                    active={link.active}
                                >
                                    {link.label}
                                </NavLink>
                            ))}
                        </nav>
                    </div>

                    <div className="flex items-center gap-3">
                        <div className="relative hidden sm:block">
                            <Dropdown>
                                <Dropdown.Trigger>
                                    <button
                                        type="button"
                                        className="flex items-center gap-2.5 rounded-full border border-slate-200 bg-white py-1.5 pl-1.5 pr-3 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
                                    >
                                        <span className="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">
                                            {user.name
                                                .charAt(0)
                                                .toUpperCase()}
                                        </span>
                                        {user.name.split(' ')[0]}
                                        <svg
                                            className="h-4 w-4 text-slate-400"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fillRule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clipRule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </Dropdown.Trigger>

                                <Dropdown.Content contentClasses="py-1 bg-white">
                                    <Dropdown.Link
                                        href={route('profile.edit')}
                                    >
                                        Profile
                                    </Dropdown.Link>
                                    <Dropdown.Link
                                        href={route('logout')}
                                        method="post"
                                        as="button"
                                    >
                                        Log Out
                                    </Dropdown.Link>
                                </Dropdown.Content>
                            </Dropdown>
                        </div>

                        <button
                            onClick={() =>
                                setShowingNavigationDropdown(
                                    (previousState) => !previousState,
                                )
                            }
                            className="inline-flex items-center justify-center rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 sm:hidden"
                        >
                            <svg
                                className="h-6 w-6"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    className={
                                        !showingNavigationDropdown
                                            ? 'inline-flex'
                                            : 'hidden'
                                    }
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    className={
                                        showingNavigationDropdown
                                            ? 'inline-flex'
                                            : 'hidden'
                                    }
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <div
                    className={
                        (showingNavigationDropdown ? 'block' : 'hidden') +
                        ' border-t border-slate-200 sm:hidden'
                    }
                >
                    <nav className="space-y-1 px-4 py-3">
                        {links.map((link) => (
                            <ResponsiveNavLink
                                key={link.href}
                                href={link.href}
                                active={link.active}
                            >
                                {link.label}
                            </ResponsiveNavLink>
                        ))}
                    </nav>
                    <div className="border-t border-slate-200 px-4 py-3">
                        <div className="flex items-center gap-3">
                            <span className="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">
                                {user.name.charAt(0).toUpperCase()}
                            </span>
                            <div>
                                <div className="text-sm font-medium text-slate-800">
                                    {user.name}
                                </div>
                                <div className="text-xs text-slate-500">
                                    {user.email}
                                </div>
                            </div>
                        </div>
                        <div className="mt-3 space-y-1">
                            <ResponsiveNavLink href={route('profile.edit')}>
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                method="post"
                                href={route('logout')}
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </header>

            {header && (
                <div className="bg-white shadow-sm">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </div>
            )}

            <main className="flex-1">{children}</main>

            <footer className="border-t border-slate-200 bg-white">
                <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 py-6 text-sm text-slate-500 sm:flex-row sm:px-6 lg:px-8">
                    <Brand className="opacity-80" />
                    <p>Subscription-based online tuition platform</p>
                </div>
            </footer>
        </div>
    );
}

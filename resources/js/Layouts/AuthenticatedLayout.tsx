import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import BottomNavigation from '@/Components/BottomNavigation';
import NotificationBell from '@/Components/NotificationBell';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState } from 'react';
import { Menu, X, History, LayoutDashboard, CalendarCheck, TrendingUp, Trophy, Scale } from 'lucide-react';
import { cn } from '@/lib/utils';

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const user = usePage().props.auth.user;

    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    const navLinks = [
        {
            name: 'Dashboard',
            href: route('dashboard'),
            icon: LayoutDashboard,
            isActive: route().current('dashboard'),
        },
        {
            name: 'Recent Activity',
            href: route('activity.index'),
            icon: History,
            isActive: route().current('activity.*'),
        },
        {
            name: 'Schedule',
            href: route('schedule.index'),
            icon: CalendarCheck,
            isActive: route().current('schedule.*'),
        },
        {
            name: 'Analytics',
            href: route('analytics.index'),
            icon: TrendingUp,
            isActive: route().current('analytics.*'),
        },
        {
            name: 'Journey',
            href: route('achievements.index'),
            icon: Trophy,
            isActive: route().current('achievements.*'),
        },
        {
            name: 'InBody',
            href: route('inbody.index'),
            icon: Scale,
            isActive: route().current('inbody.*'),
        },
    ];

    return (
        <div className="min-h-screen bg-background pb-16">
            <nav className="border-b border-border bg-card">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-16 justify-between items-center">
                        {/* Left: Logo & Desktop Navigation */}
                        <div className="flex items-center gap-8">
                            <a href="/dashboard" className="shrink-0 flex items-center">
                                <ApplicationLogo className="block h-9 w-auto fill-current text-primary" />
                            </a>

                            <div className="hidden lg:flex items-center gap-1">
                                {navLinks.map((link) => {
                                    const Icon = link.icon;
                                    return (
                                        <Link
                                            key={link.name}
                                            href={link.href}
                                            className={cn(
                                                'inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                                                link.isActive
                                                    ? 'bg-secondary text-primary font-semibold'
                                                    : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground'
                                            )}
                                        >
                                            <Icon className="h-4 w-4" />
                                            <span>{link.name}</span>
                                        </Link>
                                    );
                                })}
                            </div>

                            {/* Mid-sized screen compact links (Dashboard + Recent Activity) */}
                            <div className="hidden md:flex lg:hidden items-center gap-1">
                                <Link
                                    href={route('dashboard')}
                                    className={cn(
                                        'inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                                        route().current('dashboard')
                                            ? 'bg-secondary text-primary font-semibold'
                                            : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground'
                                    )}
                                >
                                    <LayoutDashboard className="h-4 w-4" />
                                    <span>Dashboard</span>
                                </Link>
                                <Link
                                    href={route('activity.index')}
                                    className={cn(
                                        'inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                                        route().current('activity.*')
                                            ? 'bg-secondary text-primary font-semibold'
                                            : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground'
                                    )}
                                >
                                    <History className="h-4 w-4" />
                                    <span>Recent Activity</span>
                                </Link>
                            </div>
                        </div>

                        {/* Right: Notifications & User */}
                        <div className="flex items-center gap-4">
                            {/* Notification Bell */}
                            <NotificationBell />

                            {/* User Dropdown */}
                            <div className="hidden sm:flex sm:items-center">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="inline-flex items-center gap-2 rounded-lg border border-border bg-secondary px-3 py-2 text-sm font-medium text-secondary-foreground transition duration-150 ease-in-out hover:bg-muted focus:outline-none"
                                            >
                                                <div className="flex items-center gap-2">
                                                    <div className="h-8 w-8 rounded-full bg-primary/20 flex items-center justify-center">
                                                        <span className="text-primary font-bold text-sm">
                                                            {user.name.charAt(0).toUpperCase()}
                                                        </span>
                                                    </div>
                                                    <span className="hidden md:block">{user.name}</span>
                                                </div>
                                                <svg
                                                    className="h-4 w-4"
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
                                        </span>
                                    </Dropdown.Trigger>

                                    <Dropdown.Content>
                                        <Dropdown.Link
                                            href={route('activity.index')}
                                        >
                                            Recent Activity
                                        </Dropdown.Link>
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

                            {/* Mobile menu button */}
                            <div className="flex items-center sm:hidden">
                                <button
                                    onClick={() =>
                                        setShowingNavigationDropdown(
                                            (previousState) => !previousState,
                                        )
                                    }
                                    className="inline-flex items-center justify-center rounded-md p-2 text-muted-foreground transition duration-150 ease-in-out hover:bg-muted hover:text-foreground focus:bg-muted focus:text-foreground focus:outline-none"
                                >
                                    {showingNavigationDropdown ? (
                                        <X className="h-6 w-6" />
                                    ) : (
                                        <Menu className="h-6 w-6" />
                                    )}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Mobile dropdown */}
                <div
                    className={
                        (showingNavigationDropdown ? 'block' : 'hidden') +
                        ' sm:hidden'
                    }
                >
                    <div className="border-t border-border pb-3 pt-4">
                        <div className="px-4 mb-3">
                            <div className="text-base font-semibold text-foreground">
                                {user.name}
                            </div>
                            <div className="text-sm font-medium text-muted-foreground">
                                {user.email}
                            </div>
                        </div>

                        <div className="space-y-1 px-4">
                            <Link
                                href={route('dashboard')}
                                className={cn(
                                    'block rounded-lg px-3 py-2 text-base font-medium transition-colors',
                                    route().current('dashboard')
                                        ? 'bg-secondary text-primary font-semibold'
                                        : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                )}
                            >
                                Dashboard
                            </Link>
                            <Link
                                href={route('activity.index')}
                                className={cn(
                                    'block rounded-lg px-3 py-2 text-base font-medium transition-colors',
                                    route().current('activity.*')
                                        ? 'bg-secondary text-primary font-semibold'
                                        : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                )}
                            >
                                Recent Activity
                            </Link>
                            <Link
                                href={route('schedule.index')}
                                className={cn(
                                    'block rounded-lg px-3 py-2 text-base font-medium transition-colors',
                                    route().current('schedule.*')
                                        ? 'bg-secondary text-primary font-semibold'
                                        : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                )}
                            >
                                Daily Schedule
                            </Link>
                            <Link
                                href={route('analytics.index')}
                                className={cn(
                                    'block rounded-lg px-3 py-2 text-base font-medium transition-colors',
                                    route().current('analytics.*')
                                        ? 'bg-secondary text-primary font-semibold'
                                        : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                )}
                            >
                                Analytics
                            </Link>
                            <Link
                                href={route('profile.edit')}
                                className="block rounded-lg px-3 py-2 text-base font-medium text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                            >
                                Profile
                            </Link>
                            <Link
                                method="post"
                                href={route('logout')}
                                as="button"
                                className="block w-full text-left rounded-lg px-3 py-2 text-base font-medium text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                            >
                                Log Out
                            </Link>
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="bg-card border-b border-border">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            <main>{children}</main>

            {/* Bottom Navigation */}
            <BottomNavigation />
        </div>
    );
}

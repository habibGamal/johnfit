import { Link } from '@inertiajs/react';
import { Home, Dumbbell, Utensils, User, Scale } from 'lucide-react';
import { cn } from '@/lib/utils';

interface NavItem {
    name: string;
    href: string;
    icon: React.ComponentType<{ className?: string }>;
    isActive: boolean;
}

export default function BottomNavigation() {
    const navItems: NavItem[] = [
        {
            name: 'Home',
            href: route('dashboard'),
            icon: Home,
            isActive: route().current('dashboard'),
        },
        {
            name: 'Workouts',
            href: route('workout-plans.index'),
            icon: Dumbbell,
            isActive: route().current('workout-plans.*'),
        },
        {
            name: 'Meals',
            href: route('meal-plans.index'),
            icon: Utensils,
            isActive: route().current('meal-plans.*'),
        },
        {
            name: 'InBody',
            href: route('inbody.index'),
            icon: Scale,
            isActive: route().current('inbody.*'),
        },
        {
            name: 'Profile',
            href: route('profile.edit'),
            icon: User,
            isActive: route().current('profile.*'),
        },
    ];

    return (
        <nav className="fixed bottom-0 left-0 right-0 z-50 bg-card border-t border-border">
            <div className="grid grid-cols-5 h-16 max-w-7xl mx-auto">
                {navItems.map((item) => {
                    const Icon = item.icon;
                    return (
                        <Link
                            key={item.name}
                            href={item.href}
                            className={cn(
                                'flex flex-col items-center justify-center gap-1 transition-colors duration-200',
                                'hover:bg-muted/50',
                                item.isActive
                                    ? 'text-primary'
                                    : 'text-muted-foreground'
                            )}
                            aria-label={item.name}
                            aria-current={item.isActive ? 'page' : undefined}
                        >
                            <Icon className="h-5 w-5" />
                            <span className="text-xs font-medium">{item.name}</span>
                            {item.isActive && (
                                <div className="absolute bottom-0 left-1/2 -translate-x-1/2 w-12 h-1 bg-primary rounded-t-full" />
                            )}
                        </Link>
                    );
                })}
            </div>
        </nav>
    );
}

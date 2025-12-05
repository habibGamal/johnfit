import { LucideIcon } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { Link } from '@inertiajs/react';

interface Action {
    label: string;
    icon?: React.ReactNode;
    href: string;
}

interface EmptyStateProps {
    icon: React.ReactNode;
    title: string;
    description: string;
    action?: Action;
}

export default function EmptyState({ icon, title, description, action }: EmptyStateProps) {
    return (
        <div className="flex flex-col items-center justify-center py-12 text-center animate-in fade-in zoom-in duration-500">
            <div className="mb-4 rounded-full bg-muted/50 p-6 shadow-inner ring-1 ring-border/50">
                {icon}
            </div>
            <h3 className="mb-2 text-xl font-bold tracking-tight text-foreground sm:text-2xl">
                {title}
            </h3>
            <p className="mb-8 max-w-sm text-muted-foreground">
                {description}
            </p>
            {action && (
                <Button asChild size="lg" className="rounded-full px-8 shadow-lg hover:shadow-xl transition-all">
                    <Link href={action.href}>
                        {action.icon && <span className="mr-2">{action.icon}</span>}
                        {action.label}
                    </Link>
                </Button>
            )}
        </div>
    );
}

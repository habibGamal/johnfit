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
        <div className="flex flex-col items-center justify-center py-16 text-center animate-in fade-in zoom-in duration-500 rounded-3xl border border-white/5 bg-zinc-900/20 backdrop-blur-sm">
            <div className="mb-6 rounded-full bg-yellow-500/10 p-8 shadow-[0_0_40px_-10px_rgba(234,179,8,0.2)] ring-1 ring-yellow-500/20">
                {icon}
            </div>
            <h3 className="mb-3 text-2xl font-black tracking-wide text-white uppercase sm:text-3xl">
                {title}
            </h3>
            <p className="mb-8 max-w-md text-muted-foreground text-lg leading-relaxed">
                {description}
            </p>
            {action && (
                <Button asChild size="lg" className="rounded-full px-8 py-6 text-base font-bold bg-yellow-500 text-black hover:bg-yellow-400 shadow-lg hover:shadow-yellow-500/20 transition-all uppercase tracking-wider">
                    <Link href={action.href}>
                        {action.icon && <span className="mr-2">{action.icon}</span>}
                        {action.label}
                    </Link>
                </Button>
            )}
        </div>
    );
}

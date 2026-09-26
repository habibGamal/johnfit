import { motion, useReducedMotion } from 'framer-motion';
import { Droplets, Dumbbell, Salad, CalendarCheck } from 'lucide-react';
import { cn } from '@/lib/utils';

interface StreakFlameProps {
    type: 'workout' | 'meal' | 'hydration' | 'overall';
    current: number;
    best: number;
}

const CONFIG: Record<
    StreakFlameProps['type'],
    { label: string; icon: React.ComponentType<{ className?: string }> }
> = {
    workout: { label: 'Workout', icon: Dumbbell },
    meal: { label: 'Meals', icon: Salad },
    hydration: { label: 'Hydration', icon: Droplets },
    overall: { label: 'Perfect Days', icon: CalendarCheck },
};

export default function StreakFlame({ type, current, best }: StreakFlameProps) {
    const reduceMotion = useReducedMotion();
    const { label, icon: Icon } = CONFIG[type];
    const active = current > 0;

    return (
        <div
            className={cn(
                'flex items-center gap-3 rounded-xl border p-4 transition-colors duration-300',
                active
                    ? 'border-amber-500/40 bg-amber-500/10'
                    : 'border-zinc-800 bg-card/60'
            )}
        >
            <div
                className={cn(
                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-lg transition-colors duration-300',
                    active ? 'bg-amber-500/20 text-amber-400' : 'bg-zinc-900 text-zinc-600'
                )}
            >
                {active && !reduceMotion ? (
                    <motion.span
                        animate={{ scale: [1, 1.08, 1] }}
                        transition={{ duration: 2, repeat: Infinity, ease: 'easeInOut' }}
                        className="flex"
                    >
                        <Icon className="h-5 w-5" />
                    </motion.span>
                ) : (
                    <Icon className="h-5 w-5" />
                )}
            </div>

            <div className="min-w-0 flex-1">
                <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                    {label}
                </p>
                <p className="text-lg font-bold leading-tight text-foreground">
                    <span className="tabular-nums">{current}</span>
                    <span className="ml-1 text-xs font-normal text-muted-foreground">
                        day{current === 1 ? '' : 's'}
                    </span>
                </p>
            </div>

            <div className="shrink-0 text-right">
                <p className="text-[10px] uppercase tracking-wide text-muted-foreground">Best</p>
                <p className="text-sm font-semibold tabular-nums text-foreground">{best}</p>
            </div>
        </div>
    );
}

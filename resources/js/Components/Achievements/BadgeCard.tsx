import { motion, useReducedMotion } from 'framer-motion';
import { Lock } from 'lucide-react';
import * as LucideIcons from 'lucide-react';
import { Progress } from '@/Components/ui/progress';
import { cn } from '@/lib/utils';
import { describeRequirement, formatValue, tierStyle } from '@/lib/badges';
import type { AchievementBadge } from '@/types/achievements';

interface BadgeCardProps {
    badge: AchievementBadge;
    index?: number;
}

type IconComponent = React.ComponentType<{ className?: string }>;

/**
 * Resolve a Lucide icon by name, falling back to Trophy for anything unknown
 * so a bad admin-entered name can never blank out the card.
 */
function resolveIcon(name: string): IconComponent {
    const icons = LucideIcons as unknown as Record<string, IconComponent | undefined>;
    return icons[name] ?? (icons.Trophy as IconComponent);
}

export default function BadgeCard({ badge, index = 0 }: BadgeCardProps) {
    const reduceMotion = useReducedMotion();
    const tier = tierStyle(badge.tier);
    const Icon = resolveIcon(badge.icon);
    const unlocked = badge.unlocked;

    return (
        <motion.div
            initial={reduceMotion ? false : { opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.3, delay: reduceMotion ? 0 : Math.min(index * 0.04, 0.4) }}
            className={cn(
                'group relative flex flex-col rounded-2xl border p-5 transition-all duration-300',
                unlocked
                    ? cn(tier.ring, tier.bg, tier.glow)
                    : 'border-zinc-800 bg-card/60 hover:border-zinc-700'
            )}
        >
            <div className="flex items-start gap-4">
                <div
                    className={cn(
                        'flex h-14 w-14 shrink-0 items-center justify-center rounded-xl border transition-colors duration-300',
                        unlocked ? cn(tier.ring, tier.bg, tier.text) : 'border-zinc-800 bg-zinc-900/60'
                    )}
                >
                    {unlocked ? (
                        <Icon className="h-7 w-7" />
                    ) : (
                        <Lock className="h-5 w-5 text-zinc-600" />
                    )}
                </div>

                <div className="min-w-0 flex-1">
                    <div className="flex items-start justify-between gap-2">
                        <h3
                            className={cn(
                                'text-base font-semibold leading-tight',
                                unlocked ? 'text-foreground' : 'text-muted-foreground'
                            )}
                        >
                            {badge.name}
                        </h3>
                        <span
                            className={cn(
                                'shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider',
                                unlocked ? cn(tier.ring, tier.text) : 'border-zinc-800 text-zinc-500'
                            )}
                        >
                            {tier.label}
                        </span>
                    </div>

                    {badge.description && (
                        <p className="mt-1 text-sm text-muted-foreground">{badge.description}</p>
                    )}
                </div>
            </div>

            <ul className="mt-4 space-y-1.5">
                {badge.requirements.map((requirement) => {
                    const actual = badge.per_metric[requirement.metric];
                    const met = actual !== null && actual !== undefined &&
                        (requirement.operator === 'lte'
                            ? actual <= requirement.threshold
                            : actual >= requirement.threshold);

                    return (
                        <li
                            key={requirement.metric}
                            className="flex items-center justify-between gap-2 text-xs"
                        >
                            <span className="truncate text-muted-foreground">
                                {describeRequirement(requirement)}
                            </span>
                            <span
                                className={cn(
                                    'shrink-0 font-medium tabular-nums',
                                    met ? tier.text : 'text-zinc-500'
                                )}
                            >
                                {formatValue(actual)}
                            </span>
                        </li>
                    );
                })}
            </ul>

            <div className="mt-4">
                <div className="mb-1.5 flex items-center justify-between text-xs">
                    <span className={unlocked ? tier.text : 'text-muted-foreground'}>
                        {unlocked ? 'Unlocked' : `${badge.progress}% complete`}
                    </span>
                    {badge.unlocked_at && (
                        <span className="text-muted-foreground">
                            {new Date(badge.unlocked_at).toLocaleDateString()}
                        </span>
                    )}
                </div>
                <Progress
                    value={unlocked ? 100 : badge.progress}
                    className="h-1.5"
                    aria-label={`${badge.name} progress`}
                />
            </div>
        </motion.div>
    );
}

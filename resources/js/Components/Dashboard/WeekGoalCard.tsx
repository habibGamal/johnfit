import { ReactNode } from 'react';
import { motion } from 'framer-motion';
import { Flame, TrendingDown, TrendingUp, Minus } from 'lucide-react';
import { WeeklyCompletionRate, ComparisonStats } from '@/types';
import ProgressRing from './ProgressRing';
import WeekStrip from './WeekStrip';

export interface GoalAccent {
    ringTrack: string;
    ringProgress: string;
    dotActive: string;
    todayRing: string;
    chip: string;
}

interface WeekGoalCardProps {
    title: string;
    icon: ReactNode;
    /** Singular unit, e.g. "workout" */
    unit: string;
    /** Plural unit, e.g. "workouts" */
    units: string;
    weeklyCompletionRate: WeeklyCompletionRate;
    activeDays?: Record<string, number>;
    comparisonStats?: ComparisonStats;
    currentStreak?: number;
    accent: GoalAccent;
    children?: ReactNode;
}

/**
 * One card that answers "how am I doing this week?" for a single goal type.
 *
 * Shared by workouts and meals so both read identically — previously the two
 * cards duplicated the same layout with inconsistent wording.
 */
export default function WeekGoalCard({
    title,
    icon,
    unit,
    units,
    weeklyCompletionRate,
    activeDays = {},
    comparisonStats,
    currentStreak = 0,
    accent,
    children,
}: WeekGoalCardProps) {
    const completed = weeklyCompletionRate?.completed ?? 0;
    const total = weeklyCompletionRate?.total ?? 0;
    const percentage = Math.max(0, Math.min(100, weeklyCompletionRate?.percentage ?? 0));
    const remaining = Math.max(0, total - completed);

    // Plain-language status, so the user never has to interpret a percentage.
    let status: string;
    let statusTone = 'text-muted-foreground';

    if (total === 0) {
        status = `No ${units} scheduled this week yet.`;
    } else if (percentage >= 100) {
        status = `All ${units} done. Goal smashed!`;
        statusTone = 'text-emerald-600 dark:text-emerald-400';
    } else if (completed === 0) {
        status = `Nothing logged yet. Your first ${unit} is waiting.`;
    } else {
        status = `${remaining} more to hit your weekly goal.`;
    }

    const change = comparisonStats?.percentage_change ?? 0;
    const ChangeIcon =
        comparisonStats?.trend === 'up' ? TrendingUp : comparisonStats?.trend === 'down' ? TrendingDown : Minus;
    const changeTone =
        comparisonStats?.trend === 'up'
            ? 'text-emerald-600 dark:text-emerald-400'
            : comparisonStats?.trend === 'down'
            ? 'text-rose-600 dark:text-rose-400'
            : 'text-muted-foreground';

    return (
        <motion.div
            initial={{ opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.45, ease: 'easeOut' }}
            className="rounded-2xl border border-border bg-card/60 p-4 shadow-sm sm:p-5"
        >
            {/* Header */}
            <div className="mb-4 flex items-center gap-2.5">
                <div className={`flex h-9 w-9 items-center justify-center rounded-xl ${accent.chip}`}>{icon}</div>
                <h3 className="text-base font-bold text-foreground sm:text-lg">{title}</h3>
            </div>

            {/* Headline number + ring */}
            <div className="flex items-center gap-4">
                <ProgressRing
                    value={percentage}
                    size={92}
                    strokeWidth={9}
                    trackClassName={accent.ringTrack}
                    progressClassName={accent.ringProgress}
                >
                    <span className="text-xl font-extrabold leading-none text-foreground">
                        {Math.round(percentage)}%
                    </span>
                    <span className="mt-1 text-[10px] font-medium uppercase tracking-wide text-muted-foreground">
                        done
                    </span>
                </ProgressRing>

                <div className="min-w-0 flex-1">
                    <p className="text-2xl font-extrabold leading-none text-foreground sm:text-3xl">
                        {completed}
                        <span className="text-base font-semibold text-muted-foreground"> / {total}</span>
                    </p>
                    <p className="mt-1 text-sm font-medium text-muted-foreground">
                        {completed === 1 ? unit : units} completed
                    </p>
                    <p className={`mt-2 text-xs font-semibold leading-snug ${statusTone}`}>{status}</p>
                </div>
            </div>

            {/* Week strip */}
            <div className="mt-5 border-t border-border pt-4">
                <div className="mb-3 flex items-center justify-between">
                    <span className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                        This week
                    </span>
                    {currentStreak > 0 ? (
                        <span className="inline-flex items-center gap-1 rounded-full bg-orange-500/10 px-2 py-0.5 text-[11px] font-bold text-orange-600 dark:text-orange-400">
                            <Flame className="h-3 w-3" />
                            {currentStreak} day streak
                        </span>
                    ) : null}
                </div>

                <WeekStrip
                    activeDays={activeDays}
                    activeClassName={accent.dotActive}
                    todayRingClassName={accent.todayRing}
                />
            </div>

            {/* Week over week */}
            {comparisonStats ? (
                <div className="mt-4 flex items-center gap-2 border-t border-border pt-4 text-xs">
                    <ChangeIcon className={`h-4 w-4 ${changeTone}`} />
                    <span className="font-semibold text-foreground">
                        {Math.abs(change)}% {change === 0 ? 'same as' : change > 0 ? 'better than' : 'lower than'} last week
                    </span>
                    <span className="ml-auto whitespace-nowrap text-muted-foreground">
                        {comparisonStats.last_week} &rarr; {comparisonStats.this_week}
                    </span>
                </div>
            ) : null}

            {children ? <div className="mt-4 border-t border-border pt-4">{children}</div> : null}
        </motion.div>
    );
}

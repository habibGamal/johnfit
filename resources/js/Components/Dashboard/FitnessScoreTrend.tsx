import { ReactNode, useMemo, useState } from 'react';
import { motion } from 'framer-motion';
import { Area, AreaChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import { Activity, ArrowDownRight, ArrowUpRight, Dumbbell, Minus, Utensils, Droplets } from 'lucide-react';
import type { PointsHistoryItem } from '@/types/fitness-score';

interface FitnessScoreTrendProps {
    history?: PointsHistoryItem[];
    weeks?: number;
    isLoading?: boolean;
}

const RANGES = [
    { label: '4W', weeks: 4 },
    { label: '12W', weeks: 12 },
];

/** Builds a short, human sentence about points progression. */
function buildSummary(currentPoints: number, previousPoints: number | null, rangeLabel: string): string {
    if (previousPoints === null) {
        return `You earned ${Math.round(currentPoints)} points this week. Keep logging activities to build your trend!`;
    }

    const delta = Math.round(currentPoints - previousPoints);

    if (delta === 0) {
        return `Consistent pace: ${Math.round(currentPoints)} points earned this week. Consistency beats spikes.`;
    }
    if (delta > 0) {
        return `Up +${delta} points compared to last week (${Math.round(currentPoints)} total). You're leveling up fast!`;
    }
    return `${Math.round(currentPoints)} points earned this week (${delta} vs previous). Keep going to gain momentum!`;
}

/** A single contributor row, e.g. "Workouts  18 pts". */
function ContributorRow({
    icon,
    label,
    points,
    delta,
    colorClass,
}: {
    icon: ReactNode;
    label: string;
    points: number | null;
    delta: number | null;
    colorClass: string;
}) {
    if (points === null || points === undefined) return null;

    const DeltaIcon = delta === null || delta === 0 ? Minus : delta > 0 ? ArrowUpRight : ArrowDownRight;
    const deltaTone =
        delta === null || delta === 0
            ? 'text-muted-foreground'
            : delta > 0
            ? 'text-emerald-500'
            : 'text-rose-500';

    return (
        <div className="flex items-center gap-3 py-2.5">
            <div className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ${colorClass}`}>{icon}</div>

            <span className="min-w-0 flex-1 truncate text-sm font-medium text-foreground">{label}</span>

            {delta !== null && delta !== 0 ? (
                <span className={`flex items-center gap-0.5 text-xs font-bold ${deltaTone}`}>
                    <DeltaIcon className="h-3.5 w-3.5" />
                    {delta > 0 ? `+${delta}` : delta}
                </span>
            ) : null}

            <span className="w-14 text-right text-sm font-extrabold text-foreground tabular-nums">
                {Math.round(points)} <span className="text-[10px] font-normal text-muted-foreground">pts</span>
            </span>
        </div>
    );
}

export default function FitnessScoreTrend({ history = [], weeks = 12, isLoading }: FitnessScoreTrendProps) {
    const [range, setRange] = useState(weeks);

    // The API returns entries, take the latest N weeks
    const pointsList = useMemo(() => history.slice(-range), [history, range]);

    const current = pointsList.length ? pointsList[pointsList.length - 1] : null;
    const previous = pointsList.length > 1 ? pointsList[pointsList.length - 2] : null;

    const currentWeekly = current?.points_earned ?? current?.total_score ?? 0;
    const previousWeekly = previous ? (previous.points_earned ?? previous.total_score ?? 0) : null;
    const delta = previousWeekly === null ? null : currentWeekly - previousWeekly;

    const DeltaIcon = delta === null || delta === 0 ? Minus : delta > 0 ? ArrowUpRight : ArrowDownRight;
    const deltaTone =
        delta === null || delta === 0
            ? 'bg-muted text-muted-foreground'
            : delta > 0
            ? 'bg-emerald-500/10 text-emerald-500'
            : 'bg-rose-500/10 text-rose-500';

    const rangeLabel = RANGES.find((r) => r.weeks === range)?.label ?? `${range}W`;

    if (isLoading) {
        return (
            <div className="animate-pulse space-y-4 rounded-2xl border border-border bg-card/60 p-5">
                <div className="h-6 w-40 rounded bg-muted" />
                <div className="h-40 w-full rounded bg-muted" />
            </div>
        );
    }

    if (!current) {
        return (
            <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-card/40 px-6 py-14 text-center">
                <div className="mb-3 rounded-full bg-muted p-3">
                    <Activity className="h-6 w-6 text-muted-foreground" />
                </div>
                <h3 className="text-base font-semibold text-foreground">No points history yet</h3>
                <p className="mt-1 max-w-xs text-sm text-muted-foreground">
                    Complete workouts, meals, and hydration — your points curve will appear here.
                </p>
            </div>
        );
    }

    return (
        <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
            className="rounded-2xl border border-border bg-card/60 p-4 shadow-sm sm:p-5 flex flex-col justify-between"
        >
            {/* Header + range switch */}
            <div className="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h3 className="text-base font-bold text-foreground sm:text-lg">Points Progression</h3>
                    <p className="text-xs text-muted-foreground">Weekly points earned across all activities</p>
                </div>

                <div className="flex shrink-0 gap-1 rounded-lg bg-muted p-1">
                    {RANGES.map((r) => (
                        <button
                            key={r.weeks}
                            type="button"
                            onClick={() => setRange(r.weeks)}
                            className={[
                                'rounded-md px-2.5 py-1 text-xs font-bold transition-colors',
                                range === r.weeks
                                    ? 'bg-card text-foreground shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground',
                            ].join(' ')}
                        >
                            {r.label}
                        </button>
                    ))}
                </div>
            </div>

            {/* Headline: points earned this week + delta */}
            <div className="mb-4 flex items-end gap-3">
                <span className="text-4xl font-extrabold leading-none text-foreground sm:text-5xl tabular-nums">
                    {Math.round(currentWeekly)}
                </span>
                <div className="flex flex-col gap-1 pb-1">
                    <span className={`inline-flex w-fit items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold ${deltaTone}`}>
                        <DeltaIcon className="h-3.5 w-3.5" />
                        {delta === null ? 'New' : `${delta > 0 ? '+' : ''}${Math.round(delta)} pts`}
                    </span>
                    <span className="text-[11px] font-medium text-muted-foreground">
                        Earned this week &middot; {rangeLabel} view
                    </span>
                </div>
            </div>

            {/* Area chart */}
            <div className="-mx-1">
                <ResponsiveContainer width="100%" height={190}>
                    <AreaChart data={pointsList} margin={{ top: 10, right: 8, left: -22, bottom: 0 }}>
                        <defs>
                            <linearGradient id="scoreFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stopColor="#F59E0B" stopOpacity={0.45} />
                                <stop offset="100%" stopColor="#F59E0B" stopOpacity={0.02} />
                            </linearGradient>
                        </defs>
                        <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" opacity={0.4} vertical={false} />
                        <XAxis
                            dataKey="date"
                            tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 11 }}
                            tickLine={false}
                            axisLine={false}
                            minTickGap={18}
                        />
                        <YAxis
                            domain={[0, 'auto']}
                            tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 11 }}
                            tickLine={false}
                            axisLine={false}
                            width={44}
                        />
                        <Tooltip
                            cursor={{ stroke: 'hsl(var(--border))', strokeWidth: 1 }}
                            content={({ active, payload }: any) => {
                                if (!active || !payload?.length) return null;
                                const p = payload[0].payload as PointsHistoryItem;
                                return (
                                    <div className="rounded-lg border border-border bg-card px-3 py-2 shadow-lg">
                                        <p className="text-xs font-bold text-foreground">{p.fullDate}</p>
                                        <p className="mt-0.5 text-sm font-extrabold text-amber-500">
                                            {p.points_earned ?? p.total_score ?? 0}{' '}
                                            <span className="text-[10px] font-medium text-muted-foreground">pts earned</span>
                                        </p>
                                        {p.total_points !== undefined && (
                                            <p className="text-[11px] text-muted-foreground">
                                                Cumulative: {p.total_points} pts (Level {p.level})
                                            </p>
                                        )}
                                    </div>
                                );
                            }}
                        />
                        <Area
                            type="monotone"
                            dataKey="points_earned"
                            stroke="#F59E0B"
                            strokeWidth={3}
                            fill="url(#scoreFill)"
                            dot={false}
                            activeDot={{ r: 5, fill: '#F59E0B', stroke: 'hsl(var(--card))', strokeWidth: 2 }}
                        />
                    </AreaChart>
                </ResponsiveContainer>
            </div>

            {/* Plain-English takeaway */}
            <p className="mt-3 rounded-xl bg-muted/50 p-3 text-xs font-medium leading-relaxed text-foreground">
                {buildSummary(currentWeekly, previousWeekly, rangeLabel)}
            </p>

            {/* What's moving your score */}
            <div className="mt-4 border-t border-border pt-3">
                <p className="mb-1 text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                    This Week's Point Breakdown
                </p>
                <div className="divide-y divide-border">
                    <ContributorRow
                        icon={<Dumbbell className="h-4 w-4" />}
                        label="Workouts"
                        points={current.workout_points ?? 0}
                        delta={previous ? (current.workout_points ?? 0) - (previous.workout_points ?? 0) : null}
                        colorClass="bg-blue-500/10 text-blue-500"
                    />
                    <ContributorRow
                        icon={<Utensils className="h-4 w-4" />}
                        label="Nutrition"
                        points={current.meal_points ?? 0}
                        delta={previous ? (current.meal_points ?? 0) - (previous.meal_points ?? 0) : null}
                        colorClass="bg-emerald-500/10 text-emerald-500"
                    />
                    <ContributorRow
                        icon={<Droplets className="h-4 w-4" />}
                        label="Hydration"
                        points={current.hydration_points ?? 0}
                        delta={previous ? (current.hydration_points ?? 0) - (previous.hydration_points ?? 0) : null}
                        colorClass="bg-cyan-500/10 text-cyan-500"
                    />
                </div>
            </div>
        </motion.div>
    );
}

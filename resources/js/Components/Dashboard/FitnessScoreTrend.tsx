import { ReactNode, useMemo, useState } from 'react';
import { motion } from 'framer-motion';
import { Area, AreaChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import { Activity, ArrowDownRight, ArrowUpRight, Dumbbell, Minus, Utensils, Droplets, Calendar, Sparkles } from 'lucide-react';
import type { PointsHistoryItem } from '@/types/fitness-score';

interface FitnessScoreTrendProps {
    history?: PointsHistoryItem[];
    weeks?: number; // Kept for interface backward-compatibility
    isLoading?: boolean;
}

type RangeOption = '7D' | '14D' | '30D';

const RANGES: { label: RangeOption; days: number; description: string }[] = [
    { label: '7D', days: 7, description: 'Last 7 Days' },
    { label: '14D', days: 14, description: 'Last 2 Weeks' },
    { label: '30D', days: 30, description: 'Last 30 Days' },
];

/** Builds a short, human sentence about daily points progression. */
function buildDailySummary(selected: PointsHistoryItem, previous: PointsHistoryItem | null, rangeTotal: number): string {
    const isToday = Boolean(selected.isToday);
    const dateLabel = isToday ? 'today' : `on ${selected.date}`;

    if (selected.points_earned > 0) {
        const parts: string[] = [];
        if (selected.workout_points > 0) parts.push(`${selected.workout_points} workout`);
        if (selected.meal_points > 0) parts.push(`${selected.meal_points} nutrition`);
        if (selected.hydration_points > 0) parts.push(`${selected.hydration_points} hydration`);

        const breakdown = parts.length ? ` (${parts.join(', ')} pts)` : '';
        return `You earned ${selected.points_earned} points ${dateLabel}${breakdown}. Total in this range: ${rangeTotal} pts.`;
    }

    if (previous && previous.points_earned > 0) {
        return `No points logged ${dateLabel}. Rest day or pending activities? Complete items to earn points!`;
    }

    return `No points logged ${dateLabel}. Check off your scheduled workouts, meals, or water goal to start earning!`;
}

/** A single contributor row, e.g. "Workouts  6 pts". */
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
    const currentPts = points ?? 0;
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

            <span className="w-16 text-right text-sm font-extrabold text-foreground tabular-nums">
                {currentPts} <span className="text-[10px] font-normal text-muted-foreground">pts</span>
            </span>
        </div>
    );
}

export default function FitnessScoreTrend({ history = [], isLoading }: FitnessScoreTrendProps) {
    const [range, setRange] = useState<RangeOption>('7D');
    const [selectedDate, setSelectedDate] = useState<string | null>(null);

    const activeRange = useMemo(() => {
        return RANGES.find((r) => r.label === range) ?? RANGES[0];
    }, [range]);

    // Slice history by chosen days count
    const pointsList = useMemo(() => {
        return history.slice(-activeRange.days);
    }, [history, activeRange.days]);

    // Sum of points in current viewed range
    const rangeTotal = useMemo(() => {
        return pointsList.reduce((acc, p) => acc + (p.points_earned ?? 0), 0);
    }, [pointsList]);

    // Active selected point (hovered/clicked day or fallback to latest/today)
    const activePoint = useMemo(() => {
        if (!pointsList.length) return null;
        if (selectedDate) {
            const found = pointsList.find((p) => p.fullDate === selectedDate);
            if (found) return found;
        }
        return pointsList[pointsList.length - 1];
    }, [pointsList, selectedDate]);

    // Previous point relative to active selected point
    const previousPoint = useMemo(() => {
        if (!activePoint || pointsList.length <= 1) return null;
        const index = pointsList.findIndex((p) => p.fullDate === activePoint.fullDate);
        return index > 0 ? pointsList[index - 1] : null;
    }, [pointsList, activePoint]);

    const activePointsEarned = activePoint?.points_earned ?? 0;
    const previousPointsEarned = previousPoint?.points_earned ?? 0;
    const delta = previousPoint ? activePointsEarned - previousPointsEarned : null;

    const DeltaIcon = delta === null || delta === 0 ? Minus : delta > 0 ? ArrowUpRight : ArrowDownRight;
    const deltaTone =
        delta === null || delta === 0
            ? 'bg-muted text-muted-foreground'
            : delta > 0
            ? 'bg-emerald-500/10 text-emerald-500'
            : 'bg-rose-500/10 text-rose-500';

    if (isLoading) {
        return (
            <div className="animate-pulse space-y-4 rounded-2xl border border-border bg-card/60 p-5">
                <div className="h-6 w-40 rounded bg-muted" />
                <div className="h-40 w-full rounded bg-muted" />
            </div>
        );
    }

    if (!pointsList.length) {
        return (
            <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-card/40 px-6 py-14 text-center">
                <div className="mb-3 rounded-full bg-muted p-3">
                    <Activity className="h-6 w-6 text-muted-foreground" />
                </div>
                <h3 className="text-base font-semibold text-foreground">No points history yet</h3>
                <p className="mt-1 max-w-xs text-sm text-muted-foreground">
                    Complete workouts, meals, and hydration — your daily points progress will appear here.
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
                    <div className="flex items-center gap-2">
                        <h3 className="text-base font-bold text-foreground sm:text-lg">Daily Points Progression</h3>
                        <span className="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-bold text-primary">
                            Daily Logs
                        </span>
                    </div>
                    <p className="text-xs text-muted-foreground">Points logged per day across workouts, nutrition &amp; water</p>
                </div>

                <div className="flex shrink-0 gap-1 rounded-lg bg-muted p-1">
                    {RANGES.map((r) => (
                        <button
                            key={r.label}
                            type="button"
                            onClick={() => {
                                setRange(r.label);
                                setSelectedDate(null);
                            }}
                            className={[
                                'rounded-md px-2.5 py-1 text-xs font-bold transition-colors',
                                range === r.label
                                    ? 'bg-card text-foreground shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground',
                            ].join(' ')}
                        >
                            {r.label}
                        </button>
                    ))}
                </div>
            </div>

            {/* Headline: Points earned on the active selected day */}
            <div className="mb-4 flex items-end gap-3">
                <span className="text-4xl font-extrabold leading-none text-foreground sm:text-5xl tabular-nums">
                    {activePointsEarned}
                </span>
                <div className="flex flex-col gap-1 pb-1">
                    <span className={`inline-flex w-fit items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold ${deltaTone}`}>
                        <DeltaIcon className="h-3.5 w-3.5" />
                        {delta === null ? 'Selected' : `${delta > 0 ? '+' : ''}${delta} pts vs prev day`}
                    </span>
                    <span className="text-[11px] font-medium text-muted-foreground flex items-center gap-1">
                        <Calendar className="w-3 h-3 text-muted-foreground" />
                        {activePoint?.isToday ? 'Today' : activePoint?.dayName} &middot; {activePoint?.date}
                    </span>
                </div>
            </div>

            {/* Daily Area Chart with interactive hover */}
            <div className="-mx-1">
                <ResponsiveContainer width="100%" height={190}>
                    <AreaChart
                        data={pointsList}
                        margin={{ top: 10, right: 8, left: -22, bottom: 0 }}
                        onMouseMove={(e: any) => {
                            if (e && e.activePayload && e.activePayload.length) {
                                const payload = e.activePayload[0].payload as PointsHistoryItem;
                                setSelectedDate(payload.fullDate);
                            }
                        }}
                        onMouseLeave={() => setSelectedDate(null)}
                    >
                        <defs>
                            <linearGradient id="pointsFill" x1="0" y1="0" x2="0" y2="1">
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
                            minTickGap={14}
                        />
                        <YAxis
                            domain={[0, 'auto']}
                            tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 11 }}
                            tickLine={false}
                            axisLine={false}
                            width={44}
                        />
                        <Tooltip
                            cursor={{ stroke: 'hsl(var(--primary))', strokeWidth: 1.5, strokeDasharray: '2 2' }}
                            content={({ active, payload }: any) => {
                                if (!active || !payload?.length) return null;
                                const p = payload[0].payload as PointsHistoryItem;
                                return (
                                    <div className="rounded-xl border border-border bg-card/95 backdrop-blur-md px-3.5 py-2.5 shadow-xl">
                                        <div className="flex items-center justify-between gap-3 border-b border-border/60 pb-1.5 mb-1.5">
                                            <p className="text-xs font-bold text-foreground">
                                                {p.dayName ? `${p.dayName}, ` : ''}{p.date}
                                                {p.isToday ? ' (Today)' : ''}
                                            </p>
                                            <span className="text-[10px] font-semibold text-amber-500 bg-amber-500/10 px-1.5 py-0.5 rounded">
                                                Level {p.level}
                                            </span>
                                        </div>
                                        <p className="text-sm font-extrabold text-foreground flex items-center justify-between gap-4">
                                            <span className="text-xs font-medium text-muted-foreground">Points Earned:</span>
                                            <span className="text-amber-500 tabular-nums">+{p.points_earned} pts</span>
                                        </p>
                                        <div className="mt-1 space-y-0.5 text-[11px] text-muted-foreground border-t border-border/40 pt-1">
                                            <div className="flex justify-between gap-3">
                                                <span>🏋️ Workouts:</span>
                                                <span className="font-semibold text-foreground tabular-nums">{p.workout_points} pts</span>
                                            </div>
                                            <div className="flex justify-between gap-3">
                                                <span>🥗 Nutrition:</span>
                                                <span className="font-semibold text-foreground tabular-nums">{p.meal_points} pts</span>
                                            </div>
                                            <div className="flex justify-between gap-3">
                                                <span>💧 Hydration:</span>
                                                <span className="font-semibold text-foreground tabular-nums">{p.hydration_points} pts</span>
                                            </div>
                                        </div>
                                        <p className="mt-1.5 text-[10px] text-muted-foreground/80 border-t border-border/40 pt-1 text-right">
                                            Cumulative: {p.total_points} pts
                                        </p>
                                    </div>
                                );
                            }}
                        />
                        <Area
                            type="monotone"
                            dataKey="points_earned"
                            stroke="#F59E0B"
                            strokeWidth={3}
                            fill="url(#pointsFill)"
                            dot={(props: any) => {
                                const { cx, cy, payload } = props;
                                if (!payload || payload.points_earned <= 0) return null;
                                return (
                                    <circle
                                        key={`dot-${payload.fullDate}`}
                                        cx={cx}
                                        cy={cy}
                                        r={3.5}
                                        fill="#F59E0B"
                                        stroke="hsl(var(--card))"
                                        strokeWidth={1.5}
                                    />
                                );
                            }}
                            activeDot={{ r: 6, fill: '#F59E0B', stroke: 'hsl(var(--card))', strokeWidth: 2 }}
                        />
                    </AreaChart>
                </ResponsiveContainer>
            </div>

            {/* Plain-English takeaway for selected day */}
            <p className="mt-3 rounded-xl bg-muted/50 p-3 text-xs font-medium leading-relaxed text-foreground">
                {activePoint ? buildDailySummary(activePoint, previousPoint, rangeTotal) : ''}
            </p>

            {/* Selected Day's Point Breakdown */}
            <div className="mt-4 border-t border-border pt-3">
                <div className="mb-1 flex items-center justify-between">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                        {activePoint?.isToday ? "Today's" : `${activePoint?.date}'s`} Point Breakdown
                    </p>
                    <span className="text-[10px] text-muted-foreground flex items-center gap-1">
                        <Sparkles className="w-3 h-3 text-amber-500" />
                        Hover chart to inspect any day
                    </span>
                </div>
                <div className="divide-y divide-border">
                    <ContributorRow
                        icon={<Dumbbell className="h-4 w-4" />}
                        label="Workouts"
                        points={activePoint?.workout_points ?? 0}
                        delta={previousPoint ? (activePoint?.workout_points ?? 0) - (previousPoint?.workout_points ?? 0) : null}
                        colorClass="bg-blue-500/10 text-blue-500"
                    />
                    <ContributorRow
                        icon={<Utensils className="h-4 w-4" />}
                        label="Nutrition"
                        points={activePoint?.meal_points ?? 0}
                        delta={previousPoint ? (activePoint?.meal_points ?? 0) - (previousPoint?.meal_points ?? 0) : null}
                        colorClass="bg-emerald-500/10 text-emerald-500"
                    />
                    <ContributorRow
                        icon={<Droplets className="h-4 w-4" />}
                        label="Hydration"
                        points={activePoint?.hydration_points ?? 0}
                        delta={previousPoint ? (activePoint?.hydration_points ?? 0) - (previousPoint?.hydration_points ?? 0) : null}
                        colorClass="bg-cyan-500/10 text-cyan-500"
                    />
                </div>
            </div>
        </motion.div>
    );
}

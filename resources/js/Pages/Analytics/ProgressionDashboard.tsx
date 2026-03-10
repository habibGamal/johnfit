import { useState, useMemo } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { Progress } from '@/Components/ui/progress';
import {
    LineChart,
    Line,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
    Legend,
    Area,
    ComposedChart,
    Bar,
} from 'recharts';
import { motion, AnimatePresence } from 'framer-motion';
import {
    TrendingUp,
    TrendingDown,
    Minus,
    Target,
    Flame,
    Trophy,
    Dumbbell,
    Activity,
    ArrowLeft,
    Zap,
    Scale,
    Award,
    BarChart3,
} from 'lucide-react';
import {
    ProgressionDashboardProps,
    MuscleHeatmapItem,
    WorkoutAnalytics,
    AnalyticsWorkout,
} from '@/types';
import EmptyState from '@/Components/EmptyState';

// Color palette aligned with app theme (gold accents)
const COLORS = {
    gains: '#22C55E',       // Green for positive trends
    regression: '#EF4444',  // Red for negative trends
    neutral: '#6B7280',     // Gray for stable
    volume: '#10B981',      // Emerald for volume metrics
    oneRm: '#F59E0B',       // Amber for strength/1RM
    bodyWeight: '#EAB308',  // Gold for body weight
    primary: '#EAB308',     // Gold primary (matches --primary)
};

// Animation variants
const fadeInUp = {
    initial: { opacity: 0, y: 20 },
    animate: { opacity: 1, y: 0 },
    exit: { opacity: 0, y: -20 },
};

const stagger = {
    animate: {
        transition: {
            staggerChildren: 0.1,
        },
    },
};

interface MetricCardProps {
    title: string;
    value: string | number;
    subtitle?: string;
    trend?: 'up' | 'down' | 'stable';
    icon: React.ReactNode;
    accentColor?: string;
}

function MetricCard({ title, value, subtitle, trend, icon, accentColor = COLORS.primary }: MetricCardProps) {
    const TrendIcon = trend === 'up' ? TrendingUp : trend === 'down' ? TrendingDown : Minus;
    const trendColor = trend === 'up' ? COLORS.gains : trend === 'down' ? COLORS.regression : COLORS.neutral;

    return (
        <motion.div
            variants={fadeInUp}
            whileHover={{ scale: 1.02, y: -2 }}
            transition={{ type: 'spring', stiffness: 300 }}
        >
            <Card className="relative overflow-hidden bg-card/40 border-border/50 backdrop-blur-md shadow-xl hover:shadow-2xl transition-shadow duration-300">
                <div
                    className="absolute inset-0 opacity-5"
                    style={{
                        background: `radial-gradient(circle at top right, ${accentColor}, transparent 60%)`,
                    }}
                />
                <CardHeader className="pb-2">
                    <div className="flex items-center justify-between">
                        <div
                            className="p-2.5 rounded-xl border"
                            style={{
                                backgroundColor: `${accentColor}15`,
                                borderColor: `${accentColor}30`,
                            }}
                        >
                            {icon}
                        </div>
                        {trend && (
                            <motion.div
                                className="flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium"
                                style={{
                                    color: trendColor,
                                    backgroundColor: `${trendColor}15`,
                                }}
                                initial={{ scale: 0 }}
                                animate={{ scale: 1 }}
                            >
                                <TrendIcon className="h-3.5 w-3.5" />
                                <span>{trend === 'up' ? 'Up' : trend === 'down' ? 'Down' : 'Stable'}</span>
                            </motion.div>
                        )}
                    </div>
                </CardHeader>
                <CardContent>
                    <p className="text-sm text-muted-foreground mb-1">{title}</p>
                    <p className="text-2xl font-bold text-foreground">{value}</p>
                    {subtitle && (
                        <p className="text-xs text-muted-foreground/70 mt-1">{subtitle}</p>
                    )}
                </CardContent>
            </Card>
        </motion.div>
    );
}

interface MuscleHeatmapProps {
    data: MuscleHeatmapItem[];
}

function MuscleHeatmap({ data }: MuscleHeatmapProps) {
    if (!data || data.length === 0) {
        return (
            <Card className="bg-card/40 border-border/50 backdrop-blur-md">
                <CardHeader>
                    <CardTitle className="text-foreground flex items-center gap-2">
                        <div className="p-2 bg-primary/10 rounded-xl border border-primary/20">
                            <BarChart3 className="h-5 w-5 text-primary" />
                        </div>
                        Muscle Group Volume
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="flex flex-col items-center justify-center py-8 text-center">
                        <Dumbbell className="h-10 w-10 text-muted-foreground/30 mb-3" />
                        <p className="text-muted-foreground">No volume data available</p>
                        <p className="text-xs text-muted-foreground/60 mt-1">Complete some workouts to see your muscle distribution</p>
                    </div>
                </CardContent>
            </Card>
        );
    }

    const maxVolume = Math.max(...data.map(d => d.volume));

    // Get color based on intensity: emerald (low) -> gold (high)
    const getBarColor = (intensity: number) => {
        if (intensity > 0.75) return { bg: '#EAB308', glow: 'rgba(234, 179, 8, 0.4)' }; // Gold
        if (intensity > 0.5) return { bg: '#F59E0B', glow: 'rgba(245, 158, 11, 0.3)' }; // Amber
        if (intensity > 0.25) return { bg: '#10B981', glow: 'rgba(16, 185, 129, 0.3)' }; // Emerald
        return { bg: '#6B7280', glow: 'rgba(107, 114, 128, 0.2)' }; // Gray
    };

    return (
        <motion.div variants={fadeInUp}>
            <Card className="bg-card/40 border-border/50 backdrop-blur-md shadow-xl">
                <CardHeader>
                    <CardTitle className="text-foreground flex items-center gap-2">
                        <div className="p-2 bg-primary/10 rounded-xl border border-primary/20">
                            <BarChart3 className="h-5 w-5 text-primary" />
                        </div>
                        Muscle Group Volume
                    </CardTitle>
                    <CardDescription className="text-muted-foreground">
                        Distribution of training volume across muscle groups (last 4 weeks)
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-3">
                    {data.slice(0, 8).map((muscle, index) => {
                        const intensity = muscle.volume / maxVolume;
                        const colors = getBarColor(intensity);

                        return (
                            <motion.div
                                key={muscle.muscle}
                                initial={{ opacity: 0, x: -20 }}
                                animate={{ opacity: 1, x: 0 }}
                                transition={{ delay: index * 0.05 }}
                                className="space-y-1.5"
                            >
                                <div className="flex justify-between items-center text-sm">
                                    <span className="text-foreground font-medium">{muscle.muscle}</span>
                                    <div className="flex items-center gap-2">
                                        <span className="text-muted-foreground text-xs">
                                            {Math.round(muscle.percentage)}%
                                        </span>
                                        <span className="text-muted-foreground/70">
                                            {muscle.volume.toLocaleString()} kg
                                        </span>
                                    </div>
                                </div>
                                <div className="h-2.5 bg-muted/50 rounded-full overflow-hidden">
                                    <motion.div
                                        className="h-full rounded-full"
                                        style={{
                                            backgroundColor: colors.bg,
                                            boxShadow: `0 0 8px ${colors.glow}`,
                                        }}
                                        initial={{ width: 0 }}
                                        animate={{ width: `${muscle.percentage}%` }}
                                        transition={{ duration: 0.8, ease: 'easeOut' }}
                                    />
                                </div>
                            </motion.div>
                        );
                    })}

                    {/* Color Legend */}
                    <div className="flex items-center justify-center gap-4 pt-3 mt-3 border-t border-border/50">
                        <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <div className="w-2.5 h-2.5 rounded-full bg-[#6B7280]" />
                            <span>Low</span>
                        </div>
                        <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <div className="w-2.5 h-2.5 rounded-full bg-[#10B981]" />
                            <span>Medium</span>
                        </div>
                        <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <div className="w-2.5 h-2.5 rounded-full bg-[#F59E0B]" />
                            <span>High</span>
                        </div>
                        <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <div className="w-2.5 h-2.5 rounded-full bg-[#EAB308]" />
                            <span>Peak</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </motion.div>
    );
}

interface ProgressPulseChartProps {
    data: any[];
    selectedWorkout: WorkoutAnalytics | null;
}

function ProgressPulseChart({ data, selectedWorkout }: ProgressPulseChartProps) {
    const chartData = useMemo(() => {
        if (!selectedWorkout) return data;

        // Combine volume and 1RM trends
        const volumeMap = new Map(
            selectedWorkout.volume_trend?.map(v => [v.session_date, v.total_volume]) || []
        );
        const oneRmMap = new Map(
            selectedWorkout.one_rm_trend?.map(o => [o.date, o.estimated_1rm]) || []
        );
        const relStrengthMap = new Map(
            selectedWorkout.relative_strength_trend?.map(r => [r.date, { bw: r.body_weight, rs: r.relative_strength }]) || []
        );

        const allDates = new Set([
            ...volumeMap.keys(),
            ...oneRmMap.keys(),
            ...relStrengthMap.keys(),
        ]);

        return Array.from(allDates)
            .sort()
            .map(date => ({
                date,
                volume: volumeMap.get(date) || null,
                estimated_1rm: oneRmMap.get(date) || null,
                body_weight: relStrengthMap.get(date)?.bw || null,
                relative_strength: relStrengthMap.get(date)?.rs || null,
            }));
    }, [data, selectedWorkout]);

    if (!chartData || chartData.length === 0) {
        return (
            <Card className="bg-card/40 border-border/50 backdrop-blur-md">
                <CardHeader>
                    <CardTitle className="text-foreground flex items-center gap-2">
                        <div className="p-2 bg-primary/10 rounded-xl border border-primary/20">
                            <Activity className="h-5 w-5 text-primary" />
                        </div>
                        Progress Pulse
                    </CardTitle>
                </CardHeader>
                <CardContent className="h-80 flex flex-col items-center justify-center">
                    <Activity className="h-12 w-12 text-muted-foreground/30 mb-3" />
                    <p className="text-muted-foreground">No progression data available yet</p>
                    <p className="text-xs text-muted-foreground/60 mt-1">Complete workouts to track your progress</p>
                </CardContent>
            </Card>
        );
    }

    const CustomTooltip = ({ active, payload, label }: any) => {
        if (active && payload && payload.length) {
            return (
                <div className="bg-card/95 border border-border rounded-lg p-3 shadow-xl backdrop-blur-md">
                    <p className="text-muted-foreground text-sm mb-2 font-medium">{label}</p>
                    {payload.map((entry: any, index: number) => (
                        <div key={index} className="flex items-center gap-2 text-sm py-0.5">
                            <div
                                className="w-2.5 h-2.5 rounded-full"
                                style={{ backgroundColor: entry.color }}
                            />
                            <span className="text-muted-foreground">{entry.name}:</span>
                            <span className="font-semibold text-foreground">
                                {typeof entry.value === 'number' ? entry.value.toFixed(1) : 'N/A'}
                                {entry.name === 'Volume' ? ' kg' : entry.name === 'Body Weight' ? ' kg' : ''}
                            </span>
                        </div>
                    ))}
                </div>
            );
        }
        return null;
    };

    return (
        <motion.div variants={fadeInUp}>
            <Card className="bg-card/40 border-border/50 backdrop-blur-md shadow-xl">
                <CardHeader>
                    <CardTitle className="text-foreground flex items-center gap-2">
                        <div className="p-2 bg-primary/10 rounded-xl border border-primary/20">
                            <Activity className="h-5 w-5 text-primary" />
                        </div>
                        Progress Pulse
                        {selectedWorkout && (
                            <Badge variant="outline" className="ml-2 border-primary/50 text-primary">
                                {selectedWorkout.workout_name}
                            </Badge>
                        )}
                    </CardTitle>
                    <CardDescription className="text-muted-foreground">
                        Multi-dimensional view of your training progression
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <ResponsiveContainer width="100%" height={350}>
                        <ComposedChart data={chartData}>
                            <defs>
                                <linearGradient id="volumeGradient" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="5%" stopColor={COLORS.volume} stopOpacity={0.3} />
                                    <stop offset="95%" stopColor={COLORS.volume} stopOpacity={0} />
                                </linearGradient>
                            </defs>
                            <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" opacity={0.3} />
                            <XAxis
                                dataKey="date"
                                stroke="hsl(var(--muted-foreground))"
                                tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 11 }}
                                tickFormatter={(value) => {
                                    const date = new Date(value);
                                    return `${date.getMonth() + 1}/${date.getDate()}`;
                                }}
                            />
                            <YAxis
                                yAxisId="left"
                                stroke={COLORS.volume}
                                tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 11 }}
                                tickFormatter={(value) => `${(value / 1000).toFixed(0)}k`}
                            />
                            <YAxis
                                yAxisId="right"
                                orientation="right"
                                stroke={COLORS.oneRm}
                                tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 11 }}
                            />
                            <Tooltip content={<CustomTooltip />} />
                            <Legend
                                wrapperStyle={{ paddingTop: '20px' }}
                                formatter={(value) => (
                                    <span className="text-muted-foreground text-sm">{value}</span>
                                )}
                            />
                            <Area
                                yAxisId="left"
                                type="monotone"
                                dataKey="volume"
                                name="Volume"
                                stroke={COLORS.volume}
                                fill="url(#volumeGradient)"
                                strokeWidth={2}
                                connectNulls
                            />
                            <Line
                                yAxisId="right"
                                type="monotone"
                                dataKey="estimated_1rm"
                                name="Est. 1RM"
                                stroke={COLORS.oneRm}
                                strokeWidth={2}
                                dot={{ fill: COLORS.oneRm, strokeWidth: 0, r: 4 }}
                                connectNulls
                            />
                            <Line
                                yAxisId="right"
                                type="monotone"
                                dataKey="body_weight"
                                name="Body Weight"
                                stroke={COLORS.bodyWeight}
                                strokeWidth={2}
                                strokeDasharray="5 5"
                                dot={false}
                                connectNulls
                            />
                        </ComposedChart>
                    </ResponsiveContainer>
                </CardContent>
            </Card>
        </motion.div>
    );
}

interface WorkoutSelectorProps {
    workouts: AnalyticsWorkout[];
    selectedId: number | null;
    onSelect: (id: number | null) => void;
}

function WorkoutSelector({ workouts, selectedId, onSelect }: WorkoutSelectorProps) {
    return (
        <div className="flex flex-wrap gap-2">
            <Button
                variant={selectedId === null ? 'default' : 'outline'}
                size="sm"
                onClick={() => onSelect(null)}
                className={`transition-all ${selectedId === null
                    ? 'bg-gradient-to-r from-primary to-amber-500 text-primary-foreground border-0'
                    : 'border-border/50 text-muted-foreground hover:text-foreground hover:border-primary'
                    }`}
            >
                All Workouts
            </Button>
            {workouts.map((workout) => (
                <Button
                    key={workout.id}
                    variant={selectedId === workout.id ? 'default' : 'outline'}
                    size="sm"
                    onClick={() => onSelect(workout.id)}
                    className={`transition-all ${selectedId === workout.id
                        ? 'bg-gradient-to-r from-primary to-amber-500 text-primary-foreground border-0'
                        : 'border-border/50 text-muted-foreground hover:text-foreground hover:border-primary'
                        }`}
                >
                    {workout.name}
                </Button>
            ))}
        </div>
    );
}

interface PersonalBestCardProps {
    workout: WorkoutAnalytics;
}

function PersonalBestCard({ workout }: PersonalBestCardProps) {
    const { personal_bests: pbs } = workout;

    if (!pbs.has_volume_pb && !pbs.has_strength_pb) {
        return null;
    }

    return (
        <motion.div
            initial={{ scale: 0.9, opacity: 0 }}
            animate={{ scale: 1, opacity: 1 }}
            transition={{ type: 'spring', stiffness: 200 }}
            whileHover={{ scale: 1.02 }}
        >
            <Card className="relative bg-gradient-to-br from-primary/20 to-amber-900/20 border-primary/50 overflow-hidden shadow-xl">
                {/* Shimmer effect */}
                <motion.div
                    className="absolute inset-0 bg-gradient-to-r from-transparent via-primary/10 to-transparent"
                    initial={{ x: '-100%' }}
                    animate={{ x: '200%' }}
                    transition={{ duration: 2, repeat: Infinity, repeatDelay: 3 }}
                />
                <div className="absolute top-0 right-0 w-32 h-32 bg-primary/10 rounded-full -translate-y-16 translate-x-16" />
                <CardHeader className="pb-2">
                    <CardTitle className="text-primary flex items-center gap-2">
                        <motion.div
                            animate={{ rotate: [0, -10, 10, -10, 0] }}
                            transition={{ duration: 0.5, delay: 0.3 }}
                        >
                            <Trophy className="h-5 w-5" />
                        </motion.div>
                        Personal Best Today!
                    </CardTitle>
                    <CardDescription className="text-muted-foreground">
                        {workout.workout_name}
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-3">
                    {pbs.has_strength_pb && pbs.strength_pb_details && (
                        <motion.div
                            className="flex items-center justify-between p-3 bg-background/30 rounded-lg border border-border/30"
                            initial={{ x: -20, opacity: 0 }}
                            animate={{ x: 0, opacity: 1 }}
                            transition={{ delay: 0.2 }}
                        >
                            <div className="flex items-center gap-2">
                                <Zap className="h-4 w-4 text-amber-400" />
                                <span className="text-foreground/80">New 1RM</span>
                            </div>
                            <div className="text-right">
                                <p className="text-lg font-bold" style={{ color: COLORS.gains }}>
                                    {pbs.strength_pb_details.new_record} kg
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    +{pbs.strength_pb_details.improvement_percentage}% improvement
                                </p>
                            </div>
                        </motion.div>
                    )}
                    {pbs.has_volume_pb && pbs.volume_pb_details && (
                        <motion.div
                            className="flex items-center justify-between p-3 bg-background/30 rounded-lg border border-border/30"
                            initial={{ x: -20, opacity: 0 }}
                            animate={{ x: 0, opacity: 1 }}
                            transition={{ delay: 0.3 }}
                        >
                            <div className="flex items-center gap-2">
                                <Dumbbell className="h-4 w-4 text-emerald-400" />
                                <span className="text-foreground/80">Volume PB</span>
                            </div>
                            <div className="text-right">
                                <p className="text-lg font-bold" style={{ color: COLORS.gains }}>
                                    {pbs.volume_pb_details.new_record.toLocaleString()} kg
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    +{pbs.volume_pb_details.improvement_percentage}% improvement
                                </p>
                            </div>
                        </motion.div>
                    )}
                </CardContent>
            </Card>
        </motion.div>
    );
}

export default function ProgressionDashboard({
    workouts,
    selectedWorkoutId,
    chartData,
    muscleHeatmap,
    intensityDelta,
    consistencyScore,
    workoutAnalytics,
    inbodyTrend,
}: ProgressionDashboardProps) {
    const [selectedWorkout, setSelectedWorkout] = useState<number | null>(selectedWorkoutId);

    const handleWorkoutSelect = (workoutId: number | null) => {
        setSelectedWorkout(workoutId);
        router.get(
            route('analytics.index'),
            workoutId ? { workout_id: workoutId } : {},
            { preserveState: true, preserveScroll: true }
        );
    };

    const currentWorkoutAnalytics = useMemo(() => {
        if (selectedWorkout === null) {
            return workoutAnalytics[0] || null;
        }
        return workoutAnalytics.find(w => w.workout_id === selectedWorkout) || null;
    }, [selectedWorkout, workoutAnalytics]);

    const hasData = workouts.length > 0 || chartData.length > 0;

    // Find any PBs from today
    const todaysPBs = workoutAnalytics.filter(
        w => w.personal_bests.has_volume_pb || w.personal_bests.has_strength_pb
    );

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button
                            variant="ghost"
                            size="icon"
                            asChild
                            className="rounded-full hover:bg-muted"
                        >
                            <Link href={route('dashboard')}>
                                <ArrowLeft className="h-5 w-5" />
                            </Link>
                        </Button>
                        <div>
                            <h2 className="text-xl font-bold leading-tight text-foreground">
                                Progression Analytics
                            </h2>
                            <p className="text-xs text-muted-foreground">Track your strength journey</p>
                        </div>
                    </div>
                </div>
            }
        >
            <Head title="Progression Analytics" />

            <div className="min-h-screen py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <AnimatePresence mode="wait">
                        {!hasData ? (
                            <motion.div
                                key="empty"
                                initial={{ opacity: 0 }}
                                animate={{ opacity: 1 }}
                                exit={{ opacity: 0 }}
                            >
                                <EmptyState
                                    title="No workout data yet"
                                    description="Complete some workout sessions to start tracking your progression"
                                    icon={<Dumbbell className="h-12 w-12 text-gray-500" />}
                                    action={{ label: "Go to Workout Plans", href: route('workout-plans.index') }}
                                />
                            </motion.div>
                        ) : (
                            <motion.div
                                key="content"
                                variants={stagger}
                                initial="initial"
                                animate="animate"
                                className="space-y-8"
                            >
                                {/* Today's Personal Bests */}
                                {todaysPBs.length > 0 && (
                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        {todaysPBs.map((workout) => (
                                            <PersonalBestCard key={workout.workout_id} workout={workout} />
                                        ))}
                                    </div>
                                )}

                                {/* Metric Cards */}
                                <motion.div
                                    variants={stagger}
                                    className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
                                >
                                    <MetricCard
                                        title="Intensity Delta"
                                        value={`${intensityDelta.delta_percentage > 0 ? '+' : ''}${intensityDelta.delta_percentage}%`}
                                        subtitle="vs last 4 weeks"
                                        trend={intensityDelta.trend}
                                        icon={<TrendingUp className="h-5 w-5" style={{ color: COLORS.gains }} />}
                                        accentColor={intensityDelta.trend === 'up' ? COLORS.gains : COLORS.regression}
                                    />
                                    <MetricCard
                                        title="Consistency Score"
                                        value={`${consistencyScore.percentage}%`}
                                        subtitle={`${consistencyScore.completed_sessions}/${consistencyScore.target_sessions} sessions`}
                                        icon={<Target className="h-5 w-5 text-emerald-400" />}
                                        accentColor={COLORS.volume}
                                    />
                                    <MetricCard
                                        title="Total Volume"
                                        value={`${(intensityDelta.current_volume / 1000).toFixed(1)}k kg`}
                                        subtitle="Last 4 weeks"
                                        icon={<Dumbbell className="h-5 w-5 text-amber-400" />}
                                        accentColor={COLORS.oneRm}
                                    />
                                    <MetricCard
                                        title="Workouts Tracked"
                                        value={workouts.length}
                                        subtitle="Unique exercises"
                                        icon={<Activity className="h-5 w-5 text-yellow-400" />}
                                        accentColor={COLORS.bodyWeight}
                                    />
                                </motion.div>

                                {/* Workout Selector */}
                                <motion.div variants={fadeInUp}>
                                    <Card className="bg-card/40 border-border/50 backdrop-blur-md">
                                        <CardContent className="p-4">
                                            <div className="flex flex-col sm:flex-row sm:items-center gap-4">
                                                <span className="text-muted-foreground text-sm whitespace-nowrap">
                                                    Filter by workout:
                                                </span>
                                                <WorkoutSelector
                                                    workouts={workouts}
                                                    selectedId={selectedWorkout}
                                                    onSelect={handleWorkoutSelect}
                                                />
                                            </div>
                                        </CardContent>
                                    </Card>
                                </motion.div>

                                {/* Main Chart and Heatmap */}
                                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <div className="lg:col-span-2">
                                        <ProgressPulseChart
                                            data={chartData}
                                            selectedWorkout={currentWorkoutAnalytics}
                                        />
                                    </div>
                                    <div className="lg:col-span-1">
                                        <MuscleHeatmap data={muscleHeatmap} />
                                    </div>
                                </div>

                                {/* Workout Details Tabs */}
                                {currentWorkoutAnalytics && (
                                    <motion.div variants={fadeInUp}>
                                        <Card className="bg-card/40 border-border/50 backdrop-blur-md shadow-xl">
                                            <CardHeader>
                                                <CardTitle className="text-foreground flex items-center gap-2">
                                                    <div className="p-2 bg-primary/10 rounded-xl border border-primary/20">
                                                        <Award className="h-5 w-5 text-primary" />
                                                    </div>
                                                    Workout Details: {currentWorkoutAnalytics.workout_name}
                                                </CardTitle>
                                            </CardHeader>
                                            <CardContent>
                                                <Tabs defaultValue="volume" className="w-full">
                                                    <TabsList className="bg-muted/50 border-border/50">
                                                        <TabsTrigger
                                                            value="volume"
                                                            className="data-[state=active]:bg-emerald-600 data-[state=active]:text-white"
                                                        >
                                                            Volume Trend
                                                        </TabsTrigger>
                                                        <TabsTrigger
                                                            value="strength"
                                                            className="data-[state=active]:bg-amber-600 data-[state=active]:text-white"
                                                        >
                                                            Strength Trend
                                                        </TabsTrigger>
                                                        <TabsTrigger
                                                            value="relative"
                                                            className="data-[state=active]:bg-primary data-[state=active]:text-primary-foreground"
                                                        >
                                                            Relative Strength
                                                        </TabsTrigger>
                                                    </TabsList>

                                                    <TabsContent value="volume" className="mt-4">
                                                        <ResponsiveContainer width="100%" height={250}>
                                                            <ComposedChart data={currentWorkoutAnalytics.volume_trend}>
                                                                <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" opacity={0.3} />
                                                                <XAxis
                                                                    dataKey="session_date"
                                                                    stroke="hsl(var(--muted-foreground))"
                                                                    tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 11 }}
                                                                    tickFormatter={(value) => {
                                                                        const date = new Date(value);
                                                                        return `${date.getMonth() + 1}/${date.getDate()}`;
                                                                    }}
                                                                />
                                                                <YAxis stroke="hsl(var(--muted-foreground))" tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 11 }} />
                                                                <Tooltip
                                                                    contentStyle={{
                                                                        backgroundColor: 'hsl(var(--card))',
                                                                        border: '1px solid hsl(var(--border))',
                                                                        borderRadius: '8px',
                                                                    }}
                                                                    labelStyle={{ color: 'hsl(var(--muted-foreground))' }}
                                                                />
                                                                <Bar
                                                                    dataKey="total_volume"
                                                                    name="Total Volume"
                                                                    fill={COLORS.volume}
                                                                    radius={[4, 4, 0, 0]}
                                                                />
                                                            </ComposedChart>
                                                        </ResponsiveContainer>
                                                    </TabsContent>

                                                    <TabsContent value="strength" className="mt-4">
                                                        <ResponsiveContainer width="100%" height={250}>
                                                            <LineChart data={currentWorkoutAnalytics.one_rm_trend}>
                                                                <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" opacity={0.3} />
                                                                <XAxis
                                                                    dataKey="date"
                                                                    stroke="hsl(var(--muted-foreground))"
                                                                    tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 11 }}
                                                                    tickFormatter={(value) => {
                                                                        const date = new Date(value);
                                                                        return `${date.getMonth() + 1}/${date.getDate()}`;
                                                                    }}
                                                                />
                                                                <YAxis stroke="hsl(var(--muted-foreground))" tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 11 }} />
                                                                <Tooltip
                                                                    contentStyle={{
                                                                        backgroundColor: 'hsl(var(--card))',
                                                                        border: '1px solid hsl(var(--border))',
                                                                        borderRadius: '8px',
                                                                    }}
                                                                    labelStyle={{ color: 'hsl(var(--muted-foreground))' }}
                                                                />
                                                                <Line
                                                                    type="monotone"
                                                                    dataKey="estimated_1rm"
                                                                    name="Estimated 1RM"
                                                                    stroke={COLORS.oneRm}
                                                                    strokeWidth={3}
                                                                    dot={{ fill: COLORS.oneRm, strokeWidth: 0, r: 5 }}
                                                                />
                                                            </LineChart>
                                                        </ResponsiveContainer>
                                                    </TabsContent>

                                                    <TabsContent value="relative" className="mt-4">
                                                        {currentWorkoutAnalytics.relative_strength_trend.some(r => r.relative_strength !== null) ? (
                                                            <ResponsiveContainer width="100%" height={250}>
                                                                <LineChart data={currentWorkoutAnalytics.relative_strength_trend}>
                                                                    <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" opacity={0.3} />
                                                                    <XAxis
                                                                        dataKey="date"
                                                                        stroke="hsl(var(--muted-foreground))"
                                                                        tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 11 }}
                                                                        tickFormatter={(value) => {
                                                                            const date = new Date(value);
                                                                            return `${date.getMonth() + 1}/${date.getDate()}`;
                                                                        }}
                                                                    />
                                                                    <YAxis stroke="hsl(var(--muted-foreground))" tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 11 }} />
                                                                    <Tooltip
                                                                        contentStyle={{
                                                                            backgroundColor: 'hsl(var(--card))',
                                                                            border: '1px solid hsl(var(--border))',
                                                                            borderRadius: '8px',
                                                                        }}
                                                                        labelStyle={{ color: 'hsl(var(--muted-foreground))' }}
                                                                        formatter={(value: number | undefined) => [`${typeof value === 'number' ? value.toFixed(2) : 'N/A'}x BW`, 'Relative Strength']}
                                                                    />
                                                                    <Line
                                                                        type="monotone"
                                                                        dataKey="relative_strength"
                                                                        name="Relative Strength"
                                                                        stroke={COLORS.bodyWeight}
                                                                        strokeWidth={3}
                                                                        dot={{ fill: COLORS.bodyWeight, strokeWidth: 0, r: 5 }}
                                                                        connectNulls
                                                                    />
                                                                </LineChart>
                                                            </ResponsiveContainer>
                                                        ) : (
                                                            <div className="h-64 flex items-center justify-center text-muted-foreground">
                                                                <div className="text-center">
                                                                    <Scale className="h-12 w-12 mx-auto mb-2 opacity-50" />
                                                                    <p>Add InBody measurements to see relative strength</p>
                                                                    <Button
                                                                        variant="outline"
                                                                        size="sm"
                                                                        className="mt-3 border-border/50 hover:border-primary"
                                                                        asChild
                                                                    >
                                                                        <Link href={route('inbody.index')}>
                                                                            Log InBody Data
                                                                        </Link>
                                                                    </Button>
                                                                </div>
                                                            </div>
                                                        )}
                                                    </TabsContent>
                                                </Tabs>
                                            </CardContent>
                                        </Card>
                                    </motion.div>
                                )}

                                {/* InBody Correlation Section */}
                                {inbodyTrend && inbodyTrend.length > 0 && (
                                    <motion.div variants={fadeInUp}>
                                        <Card className="bg-card/40 border-border/50 backdrop-blur-md shadow-xl">
                                            <CardHeader>
                                                <CardTitle className="text-foreground flex items-center gap-2">
                                                    <div className="p-2 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                                                        <Scale className="h-5 w-5 text-emerald-500" />
                                                    </div>
                                                    Body Composition Trend
                                                </CardTitle>
                                                <CardDescription className="text-muted-foreground">
                                                    Track how your body composition changes with training
                                                </CardDescription>
                                            </CardHeader>
                                            <CardContent>
                                                <ResponsiveContainer width="100%" height={250}>
                                                    <LineChart data={inbodyTrend}>
                                                        <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" opacity={0.3} />
                                                        <XAxis
                                                            dataKey="date"
                                                            stroke="hsl(var(--muted-foreground))"
                                                            tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 11 }}
                                                            tickFormatter={(value) => {
                                                                const date = new Date(value);
                                                                return `${date.getMonth() + 1}/${date.getDate()}`;
                                                            }}
                                                        />
                                                        <YAxis yAxisId="weight" stroke="hsl(var(--muted-foreground))" tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 11 }} />
                                                        <YAxis yAxisId="percentage" orientation="right" stroke="hsl(var(--muted-foreground))" tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 11 }} />
                                                        <Tooltip
                                                            contentStyle={{
                                                                backgroundColor: 'hsl(var(--card))',
                                                                border: '1px solid hsl(var(--border))',
                                                                borderRadius: '8px',
                                                            }}
                                                            labelStyle={{ color: 'hsl(var(--muted-foreground))' }}
                                                        />
                                                        <Legend />
                                                        <Line
                                                            yAxisId="weight"
                                                            type="monotone"
                                                            dataKey="weight"
                                                            name="Weight (kg)"
                                                            stroke={COLORS.bodyWeight}
                                                            strokeWidth={2}
                                                        />
                                                        <Line
                                                            yAxisId="weight"
                                                            type="monotone"
                                                            dataKey="smm"
                                                            name="SMM (kg)"
                                                            stroke={COLORS.gains}
                                                            strokeWidth={2}
                                                        />
                                                        <Line
                                                            yAxisId="percentage"
                                                            type="monotone"
                                                            dataKey="pbf"
                                                            name="Body Fat %"
                                                            stroke={COLORS.regression}
                                                            strokeWidth={2}
                                                        />
                                                    </LineChart>
                                                </ResponsiveContainer>
                                            </CardContent>
                                        </Card>
                                    </motion.div>
                                )}
                            </motion.div>
                        )}
                    </AnimatePresence>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

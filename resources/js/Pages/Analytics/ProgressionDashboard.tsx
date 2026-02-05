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

// Color palette with neon accents
const COLORS = {
    gains: '#00FF41',      // Neon green for positive
    regression: '#FF3131', // Neon red for negative
    neutral: '#888888',    // Gray for stable
    volume: '#00D9FF',     // Cyan for volume
    oneRm: '#FF00FF',      // Magenta for 1RM
    bodyWeight: '#FFD700', // Gold for body weight
    primary: '#6366F1',    // Indigo primary
    background: '#0F0F23', // Dark background
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
        <motion.div variants={fadeInUp}>
            <Card className="relative overflow-hidden bg-gray-900/80 border-gray-800 backdrop-blur-xl">
                <div
                    className="absolute inset-0 opacity-10"
                    style={{
                        background: `radial-gradient(circle at top right, ${accentColor}, transparent 70%)`,
                    }}
                />
                <CardHeader className="pb-2">
                    <div className="flex items-center justify-between">
                        <div
                            className="p-2 rounded-lg"
                            style={{ backgroundColor: `${accentColor}20` }}
                        >
                            {icon}
                        </div>
                        {trend && (
                            <div className="flex items-center gap-1" style={{ color: trendColor }}>
                                <TrendIcon className="h-4 w-4" />
                            </div>
                        )}
                    </div>
                </CardHeader>
                <CardContent>
                    <p className="text-sm text-gray-400 mb-1">{title}</p>
                    <p className="text-2xl font-bold text-white">{value}</p>
                    {subtitle && (
                        <p className="text-xs text-gray-500 mt-1">{subtitle}</p>
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
            <Card className="bg-gray-900/80 border-gray-800">
                <CardHeader>
                    <CardTitle className="text-white flex items-center gap-2">
                        <BarChart3 className="h-5 w-5 text-cyan-400" />
                        Muscle Group Volume
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p className="text-gray-500 text-center py-8">No volume data available</p>
                </CardContent>
            </Card>
        );
    }

    const maxVolume = Math.max(...data.map(d => d.volume));

    return (
        <motion.div variants={fadeInUp}>
            <Card className="bg-gray-900/80 border-gray-800">
                <CardHeader>
                    <CardTitle className="text-white flex items-center gap-2">
                        <BarChart3 className="h-5 w-5 text-cyan-400" />
                        Muscle Group Volume
                    </CardTitle>
                    <CardDescription className="text-gray-400">
                        Distribution of training volume across muscle groups (last 4 weeks)
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-3">
                    {data.slice(0, 8).map((muscle, index) => {
                        const intensity = muscle.volume / maxVolume;
                        const hue = 180 - (intensity * 120); // Cyan to red gradient

                        return (
                            <motion.div
                                key={muscle.muscle}
                                initial={{ opacity: 0, x: -20 }}
                                animate={{ opacity: 1, x: 0 }}
                                transition={{ delay: index * 0.05 }}
                                className="space-y-1"
                            >
                                <div className="flex justify-between items-center text-sm">
                                    <span className="text-gray-300 font-medium">{muscle.muscle}</span>
                                    <span className="text-gray-500">
                                        {muscle.volume.toLocaleString()} kg
                                    </span>
                                </div>
                                <div className="h-2 bg-gray-800 rounded-full overflow-hidden">
                                    <motion.div
                                        className="h-full rounded-full"
                                        style={{
                                            backgroundColor: `hsl(${hue}, 100%, 50%)`,
                                            boxShadow: `0 0 10px hsla(${hue}, 100%, 50%, 0.5)`,
                                        }}
                                        initial={{ width: 0 }}
                                        animate={{ width: `${muscle.percentage}%` }}
                                        transition={{ duration: 0.8, ease: 'easeOut' }}
                                    />
                                </div>
                            </motion.div>
                        );
                    })}
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
            <Card className="bg-gray-900/80 border-gray-800">
                <CardHeader>
                    <CardTitle className="text-white flex items-center gap-2">
                        <Activity className="h-5 w-5" style={{ color: COLORS.volume }} />
                        Progress Pulse
                    </CardTitle>
                </CardHeader>
                <CardContent className="h-80 flex items-center justify-center">
                    <p className="text-gray-500">No progression data available yet</p>
                </CardContent>
            </Card>
        );
    }

    const CustomTooltip = ({ active, payload, label }: any) => {
        if (active && payload && payload.length) {
            return (
                <div className="bg-gray-900/95 border border-gray-700 rounded-lg p-3 shadow-xl backdrop-blur-sm">
                    <p className="text-gray-400 text-sm mb-2">{label}</p>
                    {payload.map((entry: any, index: number) => (
                        <div key={index} className="flex items-center gap-2 text-sm">
                            <div
                                className="w-2 h-2 rounded-full"
                                style={{ backgroundColor: entry.color }}
                            />
                            <span className="text-gray-300">{entry.name}:</span>
                            <span className="font-semibold text-white">
                                {entry.value?.toFixed(1) ?? 'N/A'}
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
            <Card className="bg-gray-900/80 border-gray-800">
                <CardHeader>
                    <CardTitle className="text-white flex items-center gap-2">
                        <Activity className="h-5 w-5" style={{ color: COLORS.volume }} />
                        Progress Pulse
                        {selectedWorkout && (
                            <Badge variant="outline" className="ml-2 border-cyan-500 text-cyan-400">
                                {selectedWorkout.workout_name}
                            </Badge>
                        )}
                    </CardTitle>
                    <CardDescription className="text-gray-400">
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
                            <CartesianGrid strokeDasharray="3 3" stroke="#333" />
                            <XAxis
                                dataKey="date"
                                stroke="#666"
                                tick={{ fill: '#888', fontSize: 11 }}
                                tickFormatter={(value) => {
                                    const date = new Date(value);
                                    return `${date.getMonth() + 1}/${date.getDate()}`;
                                }}
                            />
                            <YAxis
                                yAxisId="left"
                                stroke={COLORS.volume}
                                tick={{ fill: '#888', fontSize: 11 }}
                                tickFormatter={(value) => `${(value / 1000).toFixed(0)}k`}
                            />
                            <YAxis
                                yAxisId="right"
                                orientation="right"
                                stroke={COLORS.oneRm}
                                tick={{ fill: '#888', fontSize: 11 }}
                            />
                            <Tooltip content={<CustomTooltip />} />
                            <Legend
                                wrapperStyle={{ paddingTop: '20px' }}
                                formatter={(value) => (
                                    <span className="text-gray-300 text-sm">{value}</span>
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
                className={`transition-all ${
                    selectedId === null
                        ? 'bg-gradient-to-r from-cyan-500 to-blue-500 text-white border-0'
                        : 'border-gray-700 text-gray-400 hover:text-white hover:border-cyan-500'
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
                    className={`transition-all ${
                        selectedId === workout.id
                            ? 'bg-gradient-to-r from-cyan-500 to-blue-500 text-white border-0'
                            : 'border-gray-700 text-gray-400 hover:text-white hover:border-cyan-500'
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
        >
            <Card className="bg-gradient-to-br from-yellow-900/30 to-orange-900/30 border-yellow-600/50 overflow-hidden">
                <div className="absolute top-0 right-0 w-32 h-32 bg-yellow-500/10 rounded-full -translate-y-16 translate-x-16" />
                <CardHeader className="pb-2">
                    <CardTitle className="text-yellow-400 flex items-center gap-2">
                        <Trophy className="h-5 w-5" />
                        Personal Best Today!
                    </CardTitle>
                    <CardDescription className="text-yellow-200/70">
                        {workout.workout_name}
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-3">
                    {pbs.has_strength_pb && pbs.strength_pb_details && (
                        <div className="flex items-center justify-between p-3 bg-black/30 rounded-lg">
                            <div className="flex items-center gap-2">
                                <Zap className="h-4 w-4 text-purple-400" />
                                <span className="text-gray-300">New 1RM</span>
                            </div>
                            <div className="text-right">
                                <p className="text-lg font-bold" style={{ color: COLORS.gains }}>
                                    {pbs.strength_pb_details.new_record} kg
                                </p>
                                <p className="text-xs text-gray-500">
                                    +{pbs.strength_pb_details.improvement_percentage}% improvement
                                </p>
                            </div>
                        </div>
                    )}
                    {pbs.has_volume_pb && pbs.volume_pb_details && (
                        <div className="flex items-center justify-between p-3 bg-black/30 rounded-lg">
                            <div className="flex items-center gap-2">
                                <Dumbbell className="h-4 w-4 text-cyan-400" />
                                <span className="text-gray-300">Volume PB</span>
                            </div>
                            <div className="text-right">
                                <p className="text-lg font-bold" style={{ color: COLORS.gains }}>
                                    {pbs.volume_pb_details.new_record.toLocaleString()} kg
                                </p>
                                <p className="text-xs text-gray-500">
                                    +{pbs.volume_pb_details.improvement_percentage}% improvement
                                </p>
                            </div>
                        </div>
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
                            className="rounded-full hover:bg-gray-800"
                        >
                            <Link href={route('dashboard')}>
                                <ArrowLeft className="h-5 w-5" />
                            </Link>
                        </Button>
                        <div>
                            <h2 className="text-xl font-bold leading-tight text-white">
                                Progression Analytics
                            </h2>
                            <p className="text-xs text-gray-400">Track your strength journey</p>
                        </div>
                    </div>
                </div>
            }
        >
            <Head title="Progression Analytics" />

            <div className="min-h-screen bg-gradient-to-br from-gray-950 via-gray-900 to-gray-950 py-8">
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
                                    actionLabel="Go to Workout Plans"
                                    actionRoute={route('workout-plans.index')}
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
                                        icon={<Target className="h-5 w-5 text-cyan-400" />}
                                        accentColor={COLORS.volume}
                                    />
                                    <MetricCard
                                        title="Total Volume"
                                        value={`${(intensityDelta.current_volume / 1000).toFixed(1)}k kg`}
                                        subtitle="Last 4 weeks"
                                        icon={<Dumbbell className="h-5 w-5 text-purple-400" />}
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
                                    <Card className="bg-gray-900/50 border-gray-800">
                                        <CardContent className="p-4">
                                            <div className="flex flex-col sm:flex-row sm:items-center gap-4">
                                                <span className="text-gray-400 text-sm whitespace-nowrap">
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
                                        <Card className="bg-gray-900/80 border-gray-800">
                                            <CardHeader>
                                                <CardTitle className="text-white flex items-center gap-2">
                                                    <Award className="h-5 w-5 text-yellow-400" />
                                                    Workout Details: {currentWorkoutAnalytics.workout_name}
                                                </CardTitle>
                                            </CardHeader>
                                            <CardContent>
                                                <Tabs defaultValue="volume" className="w-full">
                                                    <TabsList className="bg-gray-800 border-gray-700">
                                                        <TabsTrigger
                                                            value="volume"
                                                            className="data-[state=active]:bg-cyan-600 data-[state=active]:text-white"
                                                        >
                                                            Volume Trend
                                                        </TabsTrigger>
                                                        <TabsTrigger
                                                            value="strength"
                                                            className="data-[state=active]:bg-purple-600 data-[state=active]:text-white"
                                                        >
                                                            Strength Trend
                                                        </TabsTrigger>
                                                        <TabsTrigger
                                                            value="relative"
                                                            className="data-[state=active]:bg-yellow-600 data-[state=active]:text-white"
                                                        >
                                                            Relative Strength
                                                        </TabsTrigger>
                                                    </TabsList>

                                                    <TabsContent value="volume" className="mt-4">
                                                        <ResponsiveContainer width="100%" height={250}>
                                                            <ComposedChart data={currentWorkoutAnalytics.volume_trend}>
                                                                <CartesianGrid strokeDasharray="3 3" stroke="#333" />
                                                                <XAxis
                                                                    dataKey="session_date"
                                                                    stroke="#666"
                                                                    tick={{ fill: '#888', fontSize: 11 }}
                                                                    tickFormatter={(value) => {
                                                                        const date = new Date(value);
                                                                        return `${date.getMonth() + 1}/${date.getDate()}`;
                                                                    }}
                                                                />
                                                                <YAxis stroke="#666" tick={{ fill: '#888', fontSize: 11 }} />
                                                                <Tooltip
                                                                    contentStyle={{
                                                                        backgroundColor: '#1a1a2e',
                                                                        border: '1px solid #333',
                                                                        borderRadius: '8px',
                                                                    }}
                                                                    labelStyle={{ color: '#888' }}
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
                                                                <CartesianGrid strokeDasharray="3 3" stroke="#333" />
                                                                <XAxis
                                                                    dataKey="date"
                                                                    stroke="#666"
                                                                    tick={{ fill: '#888', fontSize: 11 }}
                                                                    tickFormatter={(value) => {
                                                                        const date = new Date(value);
                                                                        return `${date.getMonth() + 1}/${date.getDate()}`;
                                                                    }}
                                                                />
                                                                <YAxis stroke="#666" tick={{ fill: '#888', fontSize: 11 }} />
                                                                <Tooltip
                                                                    contentStyle={{
                                                                        backgroundColor: '#1a1a2e',
                                                                        border: '1px solid #333',
                                                                        borderRadius: '8px',
                                                                    }}
                                                                    labelStyle={{ color: '#888' }}
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
                                                                    <CartesianGrid strokeDasharray="3 3" stroke="#333" />
                                                                    <XAxis
                                                                        dataKey="date"
                                                                        stroke="#666"
                                                                        tick={{ fill: '#888', fontSize: 11 }}
                                                                        tickFormatter={(value) => {
                                                                            const date = new Date(value);
                                                                            return `${date.getMonth() + 1}/${date.getDate()}`;
                                                                        }}
                                                                    />
                                                                    <YAxis stroke="#666" tick={{ fill: '#888', fontSize: 11 }} />
                                                                    <Tooltip
                                                                        contentStyle={{
                                                                            backgroundColor: '#1a1a2e',
                                                                            border: '1px solid #333',
                                                                            borderRadius: '8px',
                                                                        }}
                                                                        labelStyle={{ color: '#888' }}
                                                                        formatter={(value: number) => [`${value?.toFixed(2)}x BW`, 'Relative Strength']}
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
                                                            <div className="h-64 flex items-center justify-center text-gray-500">
                                                                <div className="text-center">
                                                                    <Scale className="h-12 w-12 mx-auto mb-2 opacity-50" />
                                                                    <p>Add InBody measurements to see relative strength</p>
                                                                    <Button
                                                                        variant="outline"
                                                                        size="sm"
                                                                        className="mt-3 border-gray-700"
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
                                        <Card className="bg-gray-900/80 border-gray-800">
                                            <CardHeader>
                                                <CardTitle className="text-white flex items-center gap-2">
                                                    <Scale className="h-5 w-5 text-emerald-400" />
                                                    Body Composition Trend
                                                </CardTitle>
                                                <CardDescription className="text-gray-400">
                                                    Track how your body composition changes with training
                                                </CardDescription>
                                            </CardHeader>
                                            <CardContent>
                                                <ResponsiveContainer width="100%" height={250}>
                                                    <LineChart data={inbodyTrend}>
                                                        <CartesianGrid strokeDasharray="3 3" stroke="#333" />
                                                        <XAxis
                                                            dataKey="date"
                                                            stroke="#666"
                                                            tick={{ fill: '#888', fontSize: 11 }}
                                                            tickFormatter={(value) => {
                                                                const date = new Date(value);
                                                                return `${date.getMonth() + 1}/${date.getDate()}`;
                                                            }}
                                                        />
                                                        <YAxis yAxisId="weight" stroke="#666" tick={{ fill: '#888', fontSize: 11 }} />
                                                        <YAxis yAxisId="percentage" orientation="right" stroke="#666" tick={{ fill: '#888', fontSize: 11 }} />
                                                        <Tooltip
                                                            contentStyle={{
                                                                backgroundColor: '#1a1a2e',
                                                                border: '1px solid #333',
                                                                borderRadius: '8px',
                                                            }}
                                                            labelStyle={{ color: '#888' }}
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

import { motion } from 'framer-motion';
import { Dumbbell, Utensils, Droplets, Trophy, ChevronRight, Sparkles } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { Progress } from '@/Components/ui/progress';
import type { PointsSummaryData } from '@/types/fitness-score';

interface FitnessScoreWidgetProps {
    data?: PointsSummaryData;
    isLoading?: boolean;
}

export default function FitnessScoreWidget({ data, isLoading }: FitnessScoreWidgetProps) {
    if (isLoading) {
        return (
            <div className="relative overflow-hidden rounded-2xl border border-border bg-card/50 backdrop-blur-sm p-6">
                <div className="animate-pulse space-y-4">
                    <div className="h-8 w-32 bg-muted rounded" />
                    <div className="h-32 w-32 mx-auto bg-muted rounded-full" />
                    <div className="space-y-2">
                        <div className="h-4 bg-muted rounded" />
                        <div className="h-4 bg-muted rounded" />
                        <div className="h-4 bg-muted rounded" />
                    </div>
                </div>
            </div>
        );
    }

    if (!data) {
        return null;
    }

    const {
        level = 1,
        title = 'Novice',
        total_points = 0,
        points_in_level = 0,
        points_needed_in_level = 20,
        points_to_next_level = 20,
        progress_percent = 0,
        components,
    } = data;

    // Get level avatar image based on level
    const getLevelAvatar = (lvl: number): string => {
        if (lvl < 2) return '/images/levels/avatar_unfit.webp';
        if (lvl < 5) return '/images/levels/avatar_beginner.webp';
        if (lvl < 10) return '/images/levels/avatar_average.webp';
        if (lvl < 20) return '/images/levels/avatar_fit.webp';
        if (lvl < 40) return '/images/levels/avatar_athletic.webp';
        return '/images/levels/avatar_peak.webp';
    };

    // Calculate circular ring progress
    const radius = 64;
    const circumference = 2 * Math.PI * radius;
    const strokeDashoffset = circumference - (Math.min(100, Math.max(0, progress_percent)) / 100) * circumference;

    const workoutPoints = components?.workout?.points ?? 0;
    const mealPoints = components?.meal?.points ?? 0;
    const hydrationPoints = components?.hydration?.points ?? 0;

    return (
        <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
            className="relative flex flex-col justify-between overflow-hidden rounded-2xl border border-border bg-card/60 backdrop-blur-sm p-6 hover:border-primary/40 transition-all duration-300 group shadow-sm"
        >
            <div className="relative z-10">
                {/* Header */}
                <div className="flex items-center justify-between mb-5">
                    <div className="flex items-center gap-2.5">
                        <div className="p-2 rounded-xl bg-amber-500/10 text-amber-500">
                            <Trophy className="w-5 h-5" />
                        </div>
                        <div>
                            <h3 className="text-base font-bold text-foreground">Your Progression</h3>
                            <p className="text-xs text-muted-foreground">Points &amp; Infinite Levels</p>
                        </div>
                    </div>
                    <span className="inline-flex items-center gap-1.5 rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-500">
                        <Sparkles className="w-3 h-3" />
                        Level {level}
                    </span>
                </div>

                {/* Circular XP Progress */}
                <div className="flex flex-col items-center mb-5">
                    <div className="relative">
                        <svg className="transform -rotate-90" width="156" height="156">
                            {/* Track circle */}
                            <circle
                                cx="78"
                                cy="78"
                                r={radius}
                                stroke="currentColor"
                                strokeWidth="7"
                                fill="none"
                                className="text-muted/20"
                            />
                            {/* XP Progress Arc */}
                            <motion.circle
                                cx="78"
                                cy="78"
                                r={radius}
                                stroke="#F59E0B"
                                strokeWidth="7"
                                fill="none"
                                strokeLinecap="round"
                                strokeDasharray={circumference}
                                initial={{ strokeDashoffset: circumference }}
                                animate={{ strokeDashoffset }}
                                transition={{ duration: 1.2, ease: 'easeOut' }}
                            />
                        </svg>

                        {/* Avatar Image & Level Badge */}
                        <div className="absolute inset-0 flex items-center justify-center">
                            <motion.div
                                initial={{ scale: 0.8, opacity: 0 }}
                                animate={{ scale: 1, opacity: 1 }}
                                transition={{ delay: 0.2, duration: 0.4, type: 'spring' }}
                                className="relative flex items-center justify-center"
                            >
                                <img
                                    src={getLevelAvatar(level)}
                                    alt={`Level ${level} avatar`}
                                    className="w-24 h-24 rounded-full object-cover border-2 border-amber-500/40 shadow-inner"
                                />
                                {/* Total Points Pill */}
                                <div className="absolute -bottom-2.5 left-1/2 transform -translate-x-1/2 bg-card/95 border border-border shadow-md rounded-full px-3 py-0.5 whitespace-nowrap">
                                    <span className="text-xs font-extrabold text-foreground tabular-nums">
                                        {total_points}
                                    </span>
                                    <span className="text-[10px] text-muted-foreground ml-1">pts</span>
                                </div>
                            </motion.div>
                        </div>
                    </div>

                    {/* Level title & Progress text */}
                    <div className="text-center mt-4 w-full px-2">
                        <div className="text-sm font-bold text-foreground">
                            Level {level} &middot; <span className="text-amber-500 font-semibold">{title}</span>
                        </div>
                        <div className="mt-1 text-xs text-muted-foreground flex justify-between items-center">
                            <span>{points_in_level} / {points_needed_in_level} XP</span>
                            <span className="font-medium text-foreground">{progress_percent}%</span>
                        </div>
                        <Progress value={progress_percent} className="h-1.5 mt-1.5" />
                        <p className="mt-1.5 text-[11px] text-muted-foreground text-center">
                            {points_to_next_level} pts to Level {level + 1}
                        </p>
                    </div>
                </div>

                {/* Points Breakdown Categories */}
                <div className="grid grid-cols-3 gap-2 pt-2 border-t border-border/60">
                    {/* Workouts */}
                    <div className="flex flex-col items-center p-2.5 rounded-xl bg-muted/20 border border-border/40">
                        <div className="p-1 rounded-md bg-blue-500/10 text-blue-500 mb-1">
                            <Dumbbell className="w-3.5 h-3.5" />
                        </div>
                        <span className="text-[10px] font-medium text-muted-foreground">Workouts</span>
                        <span className="text-sm font-bold text-foreground tabular-nums mt-0.5">
                            {workoutPoints}
                        </span>
                        <span className="text-[9px] text-muted-foreground">pts</span>
                    </div>

                    {/* Meals */}
                    <div className="flex flex-col items-center p-2.5 rounded-xl bg-muted/20 border border-border/40">
                        <div className="p-1 rounded-md bg-emerald-500/10 text-emerald-500 mb-1">
                            <Utensils className="w-3.5 h-3.5" />
                        </div>
                        <span className="text-[10px] font-medium text-muted-foreground">Nutrition</span>
                        <span className="text-sm font-bold text-foreground tabular-nums mt-0.5">
                            {mealPoints}
                        </span>
                        <span className="text-[9px] text-muted-foreground">pts</span>
                    </div>

                    {/* Hydration */}
                    <div className="flex flex-col items-center p-2.5 rounded-xl bg-muted/20 border border-border/40">
                        <div className="p-1 rounded-md bg-cyan-500/10 text-cyan-500 mb-1">
                            <Droplets className="w-3.5 h-3.5" />
                        </div>
                        <span className="text-[10px] font-medium text-muted-foreground">Hydration</span>
                        <span className="text-sm font-bold text-foreground tabular-nums mt-0.5">
                            {hydrationPoints}
                        </span>
                        <span className="text-[9px] text-muted-foreground">pts</span>
                    </div>
                </div>
            </div>

            {/* Link to Achievements Journey */}
            <Link
                href="/achievements"
                className="mt-4 flex items-center justify-center gap-1.5 py-2 text-xs font-semibold text-primary hover:text-primary/80 transition-colors border-t border-border/40 pt-3"
            >
                View Achievements Journey <ChevronRight className="w-3.5 h-3.5" />
            </Link>
        </motion.div>
    );
}

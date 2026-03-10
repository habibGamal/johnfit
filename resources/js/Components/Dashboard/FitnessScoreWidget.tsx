import { motion } from 'framer-motion';
import { TrendingUp, TrendingDown, Minus, Activity, Dumbbell, Utensils, Scale } from 'lucide-react';
import { FitnessScoreData } from '@/types/fitness-score';

interface FitnessScoreWidgetProps {
    data?: FitnessScoreData;
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

    const { total_score, level, trend, period, components } = data;

    // Get level avatar image based on score
    const getLevelAvatar = (score: number): string => {
        if (score < 30) return '/images/levels/avatar_unfit.webp';
        if (score < 45) return '/images/levels/avatar_beginner.webp';
        if (score < 60) return '/images/levels/avatar_average.webp';
        if (score < 75) return '/images/levels/avatar_fit.webp';
        if (score < 90) return '/images/levels/avatar_athletic.webp';
        return '/images/levels/avatar_peak.webp';
    };

    // Calculate circle progress
    const circumference = 2 * Math.PI * 70; // radius = 70
    const strokeDashoffset = circumference - (total_score / 100) * circumference;

    // Get level color
    const getLevelColor = (level: string) => {
        switch (level) {
            case 'Elite':
                return 'text-primary';
            case 'Excellent':
                return 'text-emerald-500';
            case 'Great':
                return 'text-blue-500';
            case 'Good':
                return 'text-cyan-500';
            case 'Average':
                return 'text-yellow-500';
            case 'Fair':
                return 'text-orange-500';
            default:
                return 'text-red-500';
        }
    };

    // Get trend icon
    const getTrendIcon = () => {
        if (trend === 'up') return <TrendingUp className="w-4 h-4 text-emerald-500" />;
        if (trend === 'down') return <TrendingDown className="w-4 h-4 text-red-500" />;
        return <Minus className="w-4 h-4 text-muted-foreground" />;
    };

    return (
        <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
            className="relative overflow-hidden rounded-2xl border border-border bg-card/50 backdrop-blur-sm p-6 cursor-pointer hover:border-primary/50 transition-all duration-300 group"
        >
            {/* Gradient overlay */}
            <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

            <div className="relative z-10">
                {/* Header */}
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center gap-2">
                        <div className="p-2 rounded-lg bg-primary/10">
                            <Activity className="w-5 h-5 text-primary" />
                        </div>
                        <h3 className="text-lg font-semibold text-foreground">Fitness Score</h3>
                    </div>
                    {trend && (
                        <div className="flex items-center gap-1">
                            {getTrendIcon()}
                        </div>
                    )}
                </div>

                {/* Circular Progress */}
                <div className="flex flex-col items-center mb-6">
                    <div className="relative">
                        <svg className="transform -rotate-90" width="160" height="160">
                            {/* Background circle */}
                            <circle
                                cx="80"
                                cy="80"
                                r="70"
                                stroke="currentColor"
                                strokeWidth="8"
                                fill="none"
                                className="text-muted/20"
                            />
                            {/* Progress circle */}
                            <motion.circle
                                cx="80"
                                cy="80"
                                r="70"
                                stroke="url(#scoreGradient)"
                                strokeWidth="8"
                                fill="none"
                                strokeLinecap="round"
                                strokeDasharray={circumference}
                                initial={{ strokeDashoffset: circumference }}
                                animate={{ strokeDashoffset }}
                                transition={{ duration: 1.5, ease: "easeOut" }}
                            />
                            <defs>
                                <linearGradient id="scoreGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stopColor="#FCD34D" />
                                    <stop offset="100%" stopColor="#F59E0B" />
                                </linearGradient>
                            </defs>
                        </svg>

                        {/* Avatar Image */}
                        <div className="absolute inset-0 flex items-center justify-center">
                            <motion.div
                                initial={{ scale: 0, opacity: 0 }}
                                animate={{ scale: 1, opacity: 1 }}
                                transition={{ delay: 0.3, duration: 0.5, type: "spring" }}
                                className="relative"
                            >
                                <img
                                    src={getLevelAvatar(total_score)}
                                    alt={`${level} level avatar`}
                                    className="w-24 h-24 rounded-full object-cover"
                                />
                                {/* Score overlay */}
                                <motion.div
                                    initial={{ scale: 0 }}
                                    animate={{ scale: 1 }}
                                    transition={{ delay: 0.6, duration: 0.4, type: "spring" }}
                                    className="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-card/95 backdrop-blur-sm border border-border rounded-full px-3 py-1 shadow-lg"
                                >
                                    <div className="text-lg font-bold text-foreground">
                                        {Math.round(total_score)}
                                    </div>
                                </motion.div>
                            </motion.div>
                        </div>
                    </div>

                    {/* Level and Period */}
                    <div className="text-center mt-3">
                        <div className={`text-sm font-semibold ${getLevelColor(level)} mb-1`}>
                            {level}
                        </div>
                        <div className="text-xs text-muted-foreground">
                            {period.start} - {period.end}
                        </div>
                    </div>
                </div>

                {/* Component Breakdown */}
                <div className="space-y-3">
                    {/* Workout Score */}
                    <motion.div
                        initial={{ opacity: 0, x: -20 }}
                        animate={{ opacity: 1, x: 0 }}
                        transition={{ delay: 0.2 }}
                        className="flex items-center justify-between p-3 rounded-lg bg-muted/20 hover:bg-muted/30 transition-colors"
                    >
                        <div className="flex items-center gap-3">
                            <div className="p-1.5 rounded-md bg-blue-500/10">
                                <Dumbbell className="w-4 h-4 text-blue-500" />
                            </div>
                            <div>
                                <div className="text-sm font-medium text-foreground">Workout</div>
                                <div className="text-xs text-muted-foreground">
                                    {components.workout.metrics.completion_rate.toFixed(0)}% complete
                                </div>
                            </div>
                        </div>
                        <div className="text-right">
                            <div className="text-lg font-bold text-foreground">
                                {Math.round(components.workout.score)}
                            </div>
                            <div className="text-xs text-muted-foreground">
                                {components.workout.weight.toFixed(0)}% weight
                            </div>
                        </div>
                    </motion.div>

                    {/* Meal Score */}
                    <motion.div
                        initial={{ opacity: 0, x: -20 }}
                        animate={{ opacity: 1, x: 0 }}
                        transition={{ delay: 0.3 }}
                        className="flex items-center justify-between p-3 rounded-lg bg-muted/20 hover:bg-muted/30 transition-colors"
                    >
                        <div className="flex items-center gap-3">
                            <div className="p-1.5 rounded-md bg-emerald-500/10">
                                <Utensils className="w-4 h-4 text-emerald-500" />
                            </div>
                            <div>
                                <div className="text-sm font-medium text-foreground">Nutrition</div>
                                <div className="text-xs text-muted-foreground">
                                    {components.meal.metrics.completion_rate.toFixed(0)}% complete
                                </div>
                            </div>
                        </div>
                        <div className="text-right">
                            <div className="text-lg font-bold text-foreground">
                                {Math.round(components.meal.score)}
                            </div>
                            <div className="text-xs text-muted-foreground">
                                {components.meal.weight.toFixed(0)}% weight
                            </div>
                        </div>
                    </motion.div>

                    {/* InBody Score (if available) */}
                    {components.inbody && (
                        <motion.div
                            initial={{ opacity: 0, x: -20 }}
                            animate={{ opacity: 1, x: 0 }}
                            transition={{ delay: 0.4 }}
                            className="flex items-center justify-between p-3 rounded-lg bg-muted/20 hover:bg-muted/30 transition-colors"
                        >
                            <div className="flex items-center gap-3">
                                <div className="p-1.5 rounded-md bg-primary/10">
                                    <Scale className="w-4 h-4 text-primary" />
                                </div>
                                <div>
                                    <div className="text-sm font-medium text-foreground">Body Comp</div>
                                    <div className="text-xs text-muted-foreground capitalize">
                                        {components.inbody.metrics.classification.replace('_', ' ')}
                                    </div>
                                </div>
                            </div>
                            <div className="text-right">
                                <div className="text-lg font-bold text-foreground">
                                    {Math.round(components.inbody.score)}
                                </div>
                                <div className="text-xs text-muted-foreground">
                                    {components.inbody.weight.toFixed(0)}% weight
                                </div>
                            </div>
                        </motion.div>
                    )}
                </div>

                {/* View Details Link */}
                <motion.button
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ delay: 0.5 }}
                    className="w-full mt-4 py-2 text-sm font-medium text-primary hover:text-primary/80 transition-colors"
                >
                    View Detailed Breakdown →
                </motion.button>
            </div>
        </motion.div>
    );
}

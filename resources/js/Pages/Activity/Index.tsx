import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { Card, CardContent } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import {
    Dumbbell,
    Utensils,
    CalendarCheck,
    History,
    Flame,
    ArrowRight,
    CalendarDays,
    CheckCircle2,
    Sparkles,
    Filter,
} from 'lucide-react';
import { RecentActivity, MealRecentActivity, WeeklyCompletionRate } from '@/types';

interface ActivityIndexProps {
    recentWorkouts: RecentActivity[];
    recentMeals: MealRecentActivity[];
    workoutRate?: WeeklyCompletionRate;
    mealRate?: WeeklyCompletionRate;
    workoutStreak?: number;
    mealStreak?: number;
}

type ActivityItem = {
    id: string;
    type: 'workout' | 'meal';
    title: string;
    plan: string;
    day: string;
    completedAt: string;
};

export default function ActivityIndex({
    recentWorkouts = [],
    recentMeals = [],
    workoutRate,
    mealRate,
    workoutStreak = 0,
    mealStreak = 0,
}: ActivityIndexProps) {
    const [filter, setFilter] = useState<'all' | 'workouts' | 'meals'>('all');

    // Combine and sort activities for the unified "All" timeline
    const combinedActivities: ActivityItem[] = [
        ...recentWorkouts.map((w, index) => ({
            id: `workout-${index}`,
            type: 'workout' as const,
            title: w.workout || 'Workout Session',
            plan: w.plan_name || 'Workout Plan',
            day: w.day,
            completedAt: w.completed_at,
        })),
        ...recentMeals.map((m, index) => ({
            id: `meal-${index}`,
            type: 'meal' as const,
            title: m.meal || 'Meal Log',
            plan: m.plan_name || 'Nutrition Plan',
            day: m.day,
            completedAt: m.completed_at,
        })),
    ];

    const displayedActivities =
        filter === 'workouts'
            ? combinedActivities.filter((a) => a.type === 'workout')
            : filter === 'meals'
            ? combinedActivities.filter((a) => a.type === 'meal')
            : combinedActivities;

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="flex items-center gap-2.5 text-xl font-bold leading-tight text-foreground sm:text-2xl">
                            <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <History className="h-5 w-5" />
                            </span>
                            Recent Activity
                        </h2>
                        <p className="mt-1 text-xs text-muted-foreground sm:text-sm">
                            Your full chronological log of completed workouts and tracked meals.
                        </p>
                    </div>

                    <div className="mt-2 sm:mt-0">
                        <Button asChild size="sm" className="gap-2 bg-primary font-semibold text-black hover:bg-primary/90">
                            <Link href={route('schedule.index')}>
                                <CalendarCheck className="h-4 w-4" />
                                Daily Schedule
                            </Link>
                        </Button>
                    </div>
                </div>
            }
        >
            <Head title="Recent Activity" />

            <div className="min-h-screen bg-background py-6 sm:py-8">
                <div className="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
                    {/* Top Stat Summary Cards */}
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {/* Workouts Stat */}
                        <div className="rounded-2xl border border-border bg-card/60 p-4 shadow-sm sm:p-5">
                            <div className="flex items-center justify-between">
                                <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    <Dumbbell className="h-5 w-5" />
                                </div>
                                {workoutStreak > 0 ? (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-orange-500/10 px-2.5 py-0.5 text-xs font-bold text-orange-400 border border-orange-500/20">
                                        <Flame className="h-3 w-3" />
                                        {workoutStreak}d Streak
                                    </span>
                                ) : null}
                            </div>
                            <div className="mt-3">
                                <p className="text-2xl font-extrabold text-foreground sm:text-3xl">
                                    {recentWorkouts.length}
                                </p>
                                <p className="text-xs font-medium text-muted-foreground mt-0.5">
                                    Recent Workouts Completed
                                </p>
                            </div>
                            {workoutRate ? (
                                <div className="mt-3 pt-3 border-t border-border flex items-center justify-between text-xs text-muted-foreground">
                                    <span>This week's progress:</span>
                                    <span className="font-semibold text-foreground">
                                        {workoutRate.completed} of {workoutRate.total} ({workoutRate.percentage}%)
                                    </span>
                                </div>
                            ) : null}
                        </div>

                        {/* Meals Stat */}
                        <div className="rounded-2xl border border-border bg-card/60 p-4 shadow-sm sm:p-5">
                            <div className="flex items-center justify-between">
                                <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                                    <Utensils className="h-5 w-5" />
                                </div>
                                {mealStreak > 0 ? (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-orange-500/10 px-2.5 py-0.5 text-xs font-bold text-orange-400 border border-orange-500/20">
                                        <Flame className="h-3 w-3" />
                                        {mealStreak}d Streak
                                    </span>
                                ) : null}
                            </div>
                            <div className="mt-3">
                                <p className="text-2xl font-extrabold text-foreground sm:text-3xl">
                                    {recentMeals.length}
                                </p>
                                <p className="text-xs font-medium text-muted-foreground mt-0.5">
                                    Recent Meals Tracked
                                </p>
                            </div>
                            {mealRate ? (
                                <div className="mt-3 pt-3 border-t border-border flex items-center justify-between text-xs text-muted-foreground">
                                    <span>This week's progress:</span>
                                    <span className="font-semibold text-foreground">
                                        {mealRate.completed} of {mealRate.total} ({mealRate.percentage}%)
                                    </span>
                                </div>
                            ) : null}
                        </div>

                        {/* Overall Tracking Hub Shortcut */}
                        <div className="flex flex-col justify-between rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/10 via-card/60 to-card/60 p-4 shadow-sm sm:col-span-2 lg:col-span-1 sm:p-5">
                            <div>
                                <div className="flex items-center gap-2 text-primary font-bold text-sm">
                                    <Sparkles className="h-4 w-4" />
                                    <span>Daily Logging</span>
                                </div>
                                <h3 className="mt-2 text-base font-bold text-foreground">
                                    Log Today's Progress
                                </h3>
                                <p className="mt-1 text-xs text-muted-foreground leading-relaxed">
                                    Mark workouts complete, log weights &amp; reps, and check off meals to earn points.
                                </p>
                            </div>
                            <div className="mt-4 pt-3 border-t border-border/50">
                                <Link
                                    href={route('schedule.index')}
                                    className="inline-flex items-center gap-1.5 text-xs font-bold text-primary hover:underline"
                                >
                                    Open Daily Schedule <ArrowRight className="h-3.5 w-3.5" />
                                </Link>
                            </div>
                        </div>
                    </div>

                    {/* Filter Tabs */}
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                        <Tabs
                            value={filter}
                            onValueChange={(val) => setFilter(val as 'all' | 'workouts' | 'meals')}
                            className="w-full sm:w-auto"
                        >
                            <TabsList className="grid h-11 w-full grid-cols-3 rounded-xl border border-border bg-muted/60 p-1 sm:w-80">
                                <TabsTrigger
                                    value="all"
                                    className="rounded-lg text-xs font-semibold data-[state=active]:bg-card data-[state=active]:shadow-sm"
                                >
                                    All ({combinedActivities.length})
                                </TabsTrigger>
                                <TabsTrigger
                                    value="workouts"
                                    className="gap-1.5 rounded-lg text-xs font-semibold data-[state=active]:bg-card data-[state=active]:shadow-sm"
                                >
                                    <Dumbbell className="h-3.5 w-3.5 text-primary" />
                                    Workouts ({recentWorkouts.length})
                                </TabsTrigger>
                                <TabsTrigger
                                    value="meals"
                                    className="gap-1.5 rounded-lg text-xs font-semibold data-[state=active]:bg-card data-[state=active]:shadow-sm"
                                >
                                    <Utensils className="h-3.5 w-3.5 text-emerald-400" />
                                    Meals ({recentMeals.length})
                                </TabsTrigger>
                            </TabsList>
                        </Tabs>

                        <div className="text-xs text-muted-foreground">
                            Showing {displayedActivities.length} recent {displayedActivities.length === 1 ? 'entry' : 'entries'}
                        </div>
                    </div>

                    {/* Activities Feed */}
                    {displayedActivities.length > 0 ? (
                        <div className="space-y-3">
                            {displayedActivities.map((item, index) => {
                                const isWorkout = item.type === 'workout';
                                return (
                                    <motion.div
                                        key={item.id}
                                        initial={{ opacity: 0, y: 8 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        transition={{ duration: 0.25, delay: Math.min(index * 0.03, 0.3) }}
                                        className="flex items-start gap-4 rounded-2xl border border-border bg-card/60 p-4 transition-all duration-200 hover:bg-card/90 hover:border-border/80 hover:shadow-sm"
                                    >
                                        <div
                                            className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ${
                                                isWorkout
                                                    ? 'bg-primary/10 text-primary'
                                                    : 'bg-emerald-500/10 text-emerald-400'
                                            }`}
                                        >
                                            {isWorkout ? (
                                                <Dumbbell className="h-5 w-5" />
                                            ) : (
                                                <Utensils className="h-5 w-5" />
                                            )}
                                        </div>

                                        <div className="min-w-0 flex-1">
                                            <div className="flex flex-wrap items-center justify-between gap-2">
                                                <h4 className="text-sm font-bold text-foreground sm:text-base">
                                                    {item.title}
                                                </h4>
                                                <span className="rounded-md bg-secondary/80 px-2 py-0.5 text-[11px] font-medium text-muted-foreground">
                                                    {item.completedAt}
                                                </span>
                                            </div>

                                            <p className="mt-0.5 text-xs text-muted-foreground truncate">
                                                {item.plan}
                                            </p>

                                            <div className="mt-2.5 flex items-center gap-2">
                                                <span className="inline-flex items-center rounded-md bg-secondary/50 px-2 py-0.5 text-[11px] font-medium text-secondary-foreground border border-border">
                                                    <CalendarDays className="mr-1 h-3 w-3 text-muted-foreground" />
                                                    {item.day}
                                                </span>

                                                <span
                                                    className={`inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold border ${
                                                        isWorkout
                                                            ? 'bg-primary/10 text-primary border-primary/20'
                                                            : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                                                    }`}
                                                >
                                                    <CheckCircle2 className="h-3 w-3" />
                                                    {isWorkout ? 'Workout Completed' : 'Meal Logged'}
                                                </span>
                                            </div>
                                        </div>
                                    </motion.div>
                                );
                            })}
                        </div>
                    ) : (
                        <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-card/30 py-16 px-4 text-center">
                            <div className="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-muted">
                                <History className="h-7 w-7 text-muted-foreground" />
                            </div>
                            <h3 className="text-base font-bold text-foreground sm:text-lg">
                                No activity recorded yet
                            </h3>
                            <p className="mt-1 max-w-sm text-xs text-muted-foreground sm:text-sm">
                                {filter === 'workouts'
                                    ? 'Complete your first workout to start building your workout history.'
                                    : filter === 'meals'
                                    ? 'Track your meals in the daily schedule to see your nutrition activity.'
                                    : 'Log workouts and meals in your daily schedule to see your activity timeline here.'}
                            </p>
                            <Button asChild size="sm" className="mt-6 gap-2 bg-primary font-semibold text-black hover:bg-primary/90">
                                <Link href={route('schedule.index')}>
                                    <CalendarCheck className="h-4 w-4" />
                                    Open Daily Schedule
                                </Link>
                            </Button>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

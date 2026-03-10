import { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import {
    CheckCircle,
    Flame,
    Calendar,
    Calendar as CalendarIcon,
    Utensils,
    Apple,
    TrendingUp,
    Crown,
    ArrowRight
} from 'lucide-react';
import { WorkoutStats, MealStats, Subscription } from '@/types';
import { FitnessScoreData, FitnessScoreHistory } from '@/types/fitness-score';

// Import our modular components
import StatBadge from '@/Components/Dashboard/StatBadge';
import WelcomeSection from '@/Components/Dashboard/WelcomeSection';
import WeeklyProgressCard from '@/Components/Dashboard/WeeklyProgressCard';
import RecentActivityCard from '@/Components/Dashboard/RecentActivityCard';
import NutritionStatsCard from '@/Components/Dashboard/NutritionStatsCard';
import MealProgressCard from '@/Components/Dashboard/MealProgressCard';
import MealActivityCard from '@/Components/Dashboard/MealActivityCard';
import ActivityHeatmap from '@/Components/Dashboard/ActivityHeatmap';
import QuickActionCard from '@/Components/Dashboard/QuickActionCard';
import AchievementStat from '@/Components/Dashboard/AchievementStat';
import FitnessScoreWidget from '@/Components/Dashboard/FitnessScoreWidget';
import FitnessScoreTrend from '@/Components/Dashboard/FitnessScoreTrend';
import {
    Trophy,
    Flag,
    Dumbbell,
    Award,
    Target,
    Zap
} from 'lucide-react';
import { Badge } from '@/Components/ui/badge';

const IconMap: Record<string, any> = {
    Award,
    Trophy,
    Flame,
    Target,
    Zap,
    Dumbbell
};

interface DashboardProps {
    auth: {
        user: {
            name: string;
            email: string;
        };
    };
    workoutStats: WorkoutStats;
    mealStats: MealStats;
    fitnessScore: FitnessScoreData;
    fitnessScoreHistory: FitnessScoreHistory[];
    hasActiveSubscription: boolean;
    activeSubscription: Subscription | null;
}

export default function Dashboard({ auth, workoutStats, mealStats, fitnessScore, fitnessScoreHistory, hasActiveSubscription, activeSubscription }: DashboardProps) {
    // Get user's first name for personalized greeting
    const firstName = auth?.user?.name?.split(' ')[0] || 'Athlete';

    // Process workout most active days data
    const workoutActiveDaysArray = workoutStats ?
        Object.entries(workoutStats.mostActiveDays || {}).map(([day, count]) => ({ day, count })) : [];

    // Determine max count for scaling the workout bars
    const maxWorkoutDayCount = workoutActiveDaysArray.length > 0 ?
        Math.max(...workoutActiveDaysArray.map(item => item.count)) : 1;

    // Convert to the format expected by the WeeklyProgressCard component
    const workoutActiveDaysData = workoutActiveDaysArray.map(item => ({
        day: item.day,
        count: item.count,
        percentage: (item.count / maxWorkoutDayCount) * 100
    }));

    // Process meal most active days data
    const mealActiveDaysArray = mealStats ?
        Object.entries(mealStats.mostActiveDays || {}).map(([day, count]) => ({ day, count })) : [];

    // Determine max count for scaling the meal bars
    const maxMealDayCount = mealActiveDaysArray.length > 0 ?
        Math.max(...mealActiveDaysArray.map(item => item.count)) : 1;

    // Convert to the format expected by the MealProgressCard component
    const mealActiveDaysData = mealActiveDaysArray.map(item => ({
        day: item.day,
        count: item.count,
        percentage: (item.count / maxMealDayCount) * 100
    }));

    // Get current time to deliver appropriate greeting
    const currentHour = new Date().getHours();
    let timeGreeting = "Welcome";
    if (currentHour < 12) timeGreeting = "Good morning";
    else if (currentHour < 18) timeGreeting = "Good afternoon";
    else timeGreeting = "Good evening";

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-foreground">
                    Dashboard  {activeSubscription ?
                        <Badge variant="success" className="mr-auto">
                            Active: {activeSubscription.plan?.name}
                        </Badge>

                        : 'No Active Subscription'}
                </h2>
            }
        >
            <Head title="Dashboard" />

            <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ duration: 0.6, ease: "easeOut" }}
                className="min-h-screen bg-background py-8"
            >
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                    {/* Subscription Banner */}
                    {!hasActiveSubscription && (
                        <motion.div
                            initial={{ opacity: 0, y: -10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.4 }}
                            className="mb-8 rounded-2xl border border-primary/30 bg-primary/5 p-5 flex flex-col sm:flex-row items-start sm:items-center gap-4"
                        >
                            <div className="flex items-center gap-4 flex-1">
                                <div className="p-3 bg-primary/15 rounded-xl flex-shrink-0">
                                    <Crown className="w-6 h-6 text-primary" />
                                </div>
                                <div>
                                    <p className="font-bold text-foreground text-base">
                                        Unlock your full fitness journey
                                    </p>
                                    <p className="text-sm text-muted-foreground mt-0.5">
                                        Subscribe to get access to personalized workout plans, meal plans, and expert coaching.
                                    </p>
                                </div>
                            </div>
                            <Link href={route('packages.index')} className="flex-shrink-0">
                                <button className="flex items-center gap-2 bg-primary hover:bg-primary/90 text-black font-bold text-sm px-5 py-2.5 rounded-xl transition-colors whitespace-nowrap shadow-[0_0_20px_-5px_rgba(252,211,77,0.5)]">
                                    View Plans <ArrowRight className="w-4 h-4" />
                                </button>
                            </Link>
                        </motion.div>
                    )}

                    {/* Fitness Score Section */}
                    <div className="mb-10">
                        <motion.h3
                            initial={{ opacity: 0, x: -20 }}
                            whileInView={{ opacity: 1, x: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.5 }}
                            className="text-2xl font-bold text-white flex items-center gap-3 mb-6"
                        >
                            <span className="bg-primary p-1.5 rounded-lg shadow-[0_0_15px_-5px_rgba(252,211,77,0.5)]">
                                <TrendingUp className="w-5 h-5 text-black" />
                            </span>
                            <h2 className="text-3xl font-bold text-foreground tracking-tight">
                                {timeGreeting}, <span className="text-primary">{firstName}</span>
                            </h2>
                        </motion.h3>

                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            {/* Fitness Score Widget */}
                            <FitnessScoreWidget data={fitnessScore} />

                            {/* Fitness Score Trend Chart */}
                            <div className="lg:col-span-2">
                                <FitnessScoreTrend history={fitnessScoreHistory} weeks={12} />
                            </div>
                        </div>
                    </div>

                    {/* Main Statistics Section */}
                    <div className="mb-10">
                        <Tabs defaultValue="workouts" className="w-full">
                            <div className="flex items-center justify-between mb-6">
                                <TabsList className="grid w-full max-w-md grid-cols-2 bg-card/50 p-1 backdrop-blur-sm rounded-xl border border-border">
                                    <TabsTrigger
                                        value="workouts"
                                        className="data-[state=active]:bg-card data-[state=active]:shadow-sm rounded-lg transition-all duration-200"
                                    >
                                        Workout Progress
                                    </TabsTrigger>
                                    <TabsTrigger
                                        value="nutrition"
                                        className="data-[state=active]:bg-card data-[state=active]:shadow-sm rounded-lg transition-all duration-200"
                                    >
                                        Nutrition Progress
                                    </TabsTrigger>
                                </TabsList>
                            </div>

                            {/* Workout Stats Tab */}
                            <TabsContent value="workouts" className="space-y-6 animate-in fade-in-50 slide-in-from-bottom-2 duration-500">
                                <Tabs defaultValue="overview" className="w-full">
                                    <TabsList className="w-auto inline-flex h-9 items-center justify-center rounded-lg bg-muted p-1 text-muted-foreground mb-6">
                                        <TabsTrigger value="overview" className="rounded-md px-3 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow">Performance</TabsTrigger>
                                        <TabsTrigger value="activity" className="rounded-md px-3 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow">Recent Activity</TabsTrigger>
                                    </TabsList>

                                    <TabsContent value="overview" className="mt-0">
                                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                            {/* Weekly Progress */}
                                            <WeeklyProgressCard
                                                weeklyCompletionRate={workoutStats?.weeklyCompletionRate || { completed: 0, total: 0, percentage: 0 }}
                                                activeDaysData={workoutActiveDaysData}
                                                comparisonStats={workoutStats?.comparisonStats}
                                            />

                                            {/* Achievement Stats */}

                                        </div>
                                    </TabsContent>

                                    <TabsContent value="activity" className="mt-0 space-y-6">
                                        {workoutStats?.progressOverTime && (
                                            <ActivityHeatmap
                                                data={workoutStats.progressOverTime}
                                                title="Workout Intensity (Last 30 Days)"
                                            />
                                        )}
                                        <RecentActivityCard activities={workoutStats?.recentActivity || []} />
                                    </TabsContent>
                                </Tabs>
                            </TabsContent>

                            {/* Nutrition Stats Tab */}
                            <TabsContent value="nutrition" className="space-y-6 animate-in fade-in-50 slide-in-from-bottom-2 duration-500">
                                <Tabs defaultValue="overview" className="w-full">
                                    <TabsList className="w-auto inline-flex h-9 items-center justify-center rounded-lg bg-muted p-1 text-muted-foreground mb-6">
                                        <TabsTrigger value="overview" className="rounded-md px-3 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow">Performance</TabsTrigger>
                                        <TabsTrigger value="activity" className="rounded-md px-3 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow">Recent Activity</TabsTrigger>
                                    </TabsList>

                                    <TabsContent value="overview" className="mt-0">
                                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                            {/* Meal Progress */}
                                            <MealProgressCard
                                                weeklyCompletionRate={mealStats?.weeklyCompletionRate || { completed: 0, total: 0, percentage: 0 }}
                                                activeDaysData={mealActiveDaysData}
                                                comparisonStats={mealStats?.comparisonStats}
                                            />

                                            {/* Nutrition Stats */}
                                            <NutritionStatsCard
                                                nutritionAverages={mealStats?.nutritionAverages || {
                                                    calories: 0, protein: 0, carbs: 0, fat: 0
                                                }}
                                                macroDistribution={mealStats?.macroDistribution}
                                            />
                                        </div>
                                    </TabsContent>

                                    <TabsContent value="activity" className="mt-0 space-y-6">
                                        {mealStats?.progressOverTime && (
                                            <ActivityHeatmap
                                                data={mealStats.progressOverTime}
                                                title="Meal Tracking Frequency"
                                            />
                                        )}
                                        <MealActivityCard activities={mealStats?.recentActivity || []} />
                                    </TabsContent>
                                </Tabs>
                            </TabsContent>
                        </Tabs>
                    </div>

                    {/* Quick Actions */}
                    <div className="mb-6 mt-12">
                        <motion.h3
                            initial={{ opacity: 0, x: -20 }}
                            whileInView={{ opacity: 1, x: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.5 }}
                            className="text-2xl font-bold text-white flex items-center gap-3 mb-6"
                        >
                            <span className="bg-primary p-1.5 rounded-lg shadow-[0_0_15px_-5px_rgba(252,211,77,0.5)]">
                                <Flame className="w-5 h-5 text-black" />
                            </span>
                            Quick Actions
                        </motion.h3>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {/* Workout Plans */}
                            <QuickActionCard
                                title="Workout Plans"
                                description="Track your assigned plans and progress."
                                actionLabel="View"
                                actionRoute={route('workout-plans.index')}
                                // Using a gym-themed placeholder since generation failed
                                bgImage="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1470&auto=format&fit=crop"
                            />

                            {/* Meal Plans */}
                            <QuickActionCard
                                title="Meal Plans"
                                description="Monitor nutrition and healthy habits."
                                actionLabel="View"
                                actionRoute={route('meal-plans.index')}
                                // Food/Meal placeholder
                                bgImage="https://images.unsplash.com/photo-1490645935967-10de6ba17061?q=80&w=1453&auto=format&fit=crop"
                            />

                            {/* Analytics */}
                            <QuickActionCard
                                title="Analytics"
                                description="Analyze trends and body composition."
                                actionLabel="View"
                                actionRoute={route('analytics.index')}
                                // Analytics/Tech placeholder
                                bgImage="https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=1470&auto=format&fit=crop"
                            />
                        </div>
                    </div>

                    {/* Achievements Section */}
                    <div className="mt-12">
                        <div className="flex items-center justify-between mb-6">
                            <motion.h3
                                initial={{ opacity: 0, x: -20 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                viewport={{ once: true }}
                                transition={{ duration: 0.5 }}
                                className="text-2xl font-bold text-white flex items-center gap-3"
                            >
                                <span className="text-primary drop-shadow-[0_0_10px_rgba(252,211,77,0.5)]">
                                    <Trophy className="w-6 h-6" />
                                </span>
                                Achievements
                            </motion.h3>
                            <a href="#" className="text-primary text-sm font-semibold hover:underline transition-colors">
                                View All
                            </a>
                        </div>

                        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            {/* Dynamically Map Achievements if available, else show placeholders matching design */}
                            {workoutStats?.achievements && workoutStats.achievements.length > 0 ? (
                                workoutStats.achievements.slice(0, 4).map((achievement) => (
                                    <AchievementStat
                                        key={achievement.id}
                                        title={achievement.title}
                                        subtitle={`${Math.round(achievement.progress)}% Complete`}
                                        progress={achievement.progress}
                                        icon={IconMap[achievement.icon] || Trophy}
                                    />
                                ))
                            ) : (
                                <>
                                    <AchievementStat
                                        title="First Step"
                                        subtitle="1/5 Workouts"
                                        progress={20}
                                        icon={Flag}
                                    />
                                    <AchievementStat
                                        title="Gym Rat"
                                        subtitle="12/20 Sessions"
                                        progress={60}
                                        icon={Dumbbell}
                                    />
                                    <AchievementStat
                                        title="Nutritionist"
                                        subtitle="5 Day Streak"
                                        progress={100}
                                        icon={Utensils}
                                    />
                                    {/* Empty/Locked state example if needed, or just filler */}
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </motion.div>
        </AuthenticatedLayout>
    );
}

import { ReactNode, useState } from 'react';
import { motion } from 'framer-motion';
import { Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { Badge } from '@/Components/ui/badge';
import {
    Apple,
    ArrowRight,
    Award,
    Bolt,
    CalendarCheck,
    Crown,
    Droplets,
    Dumbbell,
    Flag,
    Flame,
    History,
    Medal,
    Salad,
    Shield,
    Target,
    Trophy,
    Utensils,
    Zap,
} from 'lucide-react';
import { WorkoutStats, MealStats, Subscription } from '@/types';
import { FitnessScoreData, FitnessScoreHistory } from '@/types/fitness-score';
import { WaterData } from '@/types/water';
import { AchievementJourney, AchievementBadge } from '@/types/achievements';

import WeekAtGlance, { GlanceTile } from '@/Components/Dashboard/WeekAtGlance';
import WeekGoalCard, { GoalAccent } from '@/Components/Dashboard/WeekGoalCard';
import NutritionStatsCard from '@/Components/Dashboard/NutritionStatsCard';
import QuickActionCard from '@/Components/Dashboard/QuickActionCard';
import AchievementStat from '@/Components/Dashboard/AchievementStat';
import FitnessScoreWidget from '@/Components/Dashboard/FitnessScoreWidget';
import FitnessScoreTrend from '@/Components/Dashboard/FitnessScoreTrend';
import AutoPlanGeneratorCard from '@/Components/Dashboard/AutoPlanGeneratorCard';
import WaterIntakeWidget from '@/Components/Water/WaterIntakeWidget';

const IconMap: Record<string, any> = { Award, Trophy, Flame, Target, Zap, Dumbbell, Apple, Utensils, Salad, Crown, Shield, Droplets, CalendarCheck, Bolt, Medal };

const WORKOUT_ACCENT: GoalAccent = {
    ringTrack: 'text-primary/20',
    ringProgress: 'text-primary',
    dotActive: 'bg-primary text-primary-foreground border-primary',
    todayRing: 'ring-2 ring-primary ring-offset-2 ring-offset-background',
    chip: 'bg-primary/10 text-primary',
};

const MEAL_ACCENT: GoalAccent = {
    ringTrack: 'text-emerald-500/20',
    ringProgress: 'text-emerald-500',
    dotActive: 'bg-emerald-500 text-white border-emerald-500',
    todayRing: 'ring-2 ring-emerald-500 ring-offset-2 ring-offset-background',
    chip: 'bg-emerald-500/10 text-emerald-500',
};

const EMPTY_RATE = { completed: 0, total: 0, percentage: 0 };
const EMPTY_NUTRITION = { calories: 0, protein: 0, carbs: 0, fat: 0 };

interface DashboardProps {
    auth: { user: { name: string; email: string } };
    workoutStats: WorkoutStats;
    mealStats: MealStats;
    fitnessScore: FitnessScoreData;
    fitnessScoreHistory: FitnessScoreHistory[];
    hasActiveSubscription: boolean;
    activeSubscription: Subscription | null;
    waterData?: WaterData;
    achievementJourney?: AchievementJourney;
}

function SectionHeading({ icon, title, description }: { icon: ReactNode; title: string; description?: string }) {
    return (
        <div className="mb-4">
            <h2 className="flex items-center gap-2 text-lg font-bold tracking-tight text-foreground sm:text-xl">
                <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    {icon}
                </span>
                {title}
            </h2>
            {description ? <p className="mt-1 text-sm text-muted-foreground">{description}</p> : null}
        </div>
    );
}


export default function Dashboard({
    auth,
    workoutStats,
    mealStats,
    fitnessScore,
    fitnessScoreHistory,
    hasActiveSubscription,
    activeSubscription,
    waterData,
    achievementJourney,
}: DashboardProps) {
    const [section, setSection] = useState<'workouts' | 'nutrition'>('workouts');

    const firstName = auth?.user?.name?.split(' ')[0] || 'Athlete';

    const currentHour = new Date().getHours();
    const timeGreeting = currentHour < 12 ? 'Good morning' : currentHour < 18 ? 'Good afternoon' : 'Good evening';

    const workoutRate = workoutStats?.weeklyCompletionRate ?? EMPTY_RATE;
    const mealRate = mealStats?.weeklyCompletionRate ?? EMPTY_RATE;
    const waterLog = waterData?.log;

    // Show what has been earned first, then the badges closest to completion.
    const allBadges: AchievementBadge[] = achievementJourney?.badges ?? [];
    const featuredBadges = [
        ...allBadges.filter((b) => b.unlocked),
        ...allBadges.filter((b) => !b.unlocked).sort((a, b) => b.progress - a.progress),
    ].slice(0, 4);

    // Answer-first tiles: the three numbers people open a dashboard to check.
    const glanceTiles: GlanceTile[] = [
        {
            id: 'workouts',
            label: 'Workouts',
            value: `${workoutRate.completed} of ${workoutRate.total}`,
            percent: workoutRate.percentage,
            caption:
                workoutRate.total === 0
                    ? 'Nothing scheduled'
                    : workoutRate.percentage >= 100
                    ? 'Week complete'
                    : `${Math.max(0, workoutRate.total - workoutRate.completed)} to go`,
            icon: <Dumbbell className="h-4 w-4" />,
            iconClassName: 'bg-primary/10 text-primary',
            barClassName: 'bg-primary',
        },
        {
            id: 'meals',
            label: 'Meals',
            value: `${mealRate.completed} of ${mealRate.total}`,
            percent: mealRate.percentage,
            caption:
                mealRate.total === 0
                    ? 'Nothing scheduled'
                    : mealRate.percentage >= 100
                    ? 'Week complete'
                    : `${Math.max(0, mealRate.total - mealRate.completed)} to go`,
            icon: <Apple className="h-4 w-4" />,
            iconClassName: 'bg-emerald-500/10 text-emerald-500',
            barClassName: 'bg-emerald-500',
        },
        {
            id: 'water',
            label: 'Water',
            value: waterLog ? `${Math.round(waterLog.percentage)}%` : '—',
            percent: waterLog?.percentage ?? 0,
            caption: waterLog
                ? `${waterLog.consumed_ml.toLocaleString()} / ${waterLog.effective_target_ml.toLocaleString()} ml`
                : 'No water goal set',
            icon: <Droplets className="h-4 w-4" />,
            iconClassName: 'bg-sky-500/10 text-sky-500',
            barClassName: 'bg-sky-500',
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-foreground">
                    Dashboard{' '}
                    {activeSubscription ? (
                        <Badge variant="success" className="mr-auto">
                            Active: {activeSubscription.plan?.name}
                        </Badge>
                    ) : (
                        'No Active Subscription'
                    )}
                </h2>
            }
        >
            <Head title="Dashboard" />

            <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ duration: 0.5, ease: 'easeOut' }}
                className="min-h-screen bg-background py-6 sm:py-8"
            >
                <div className="mx-auto max-w-5xl space-y-8 px-4 sm:px-6 lg:px-8">
                    {/* Subscription Banner */}
                    {!hasActiveSubscription ? (
                        <div className="flex items-center gap-3 rounded-xl border border-primary/30 bg-primary/5 px-4 py-3">
                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/15">
                                <Crown className="h-4 w-4 text-primary" />
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="text-sm font-semibold leading-tight text-foreground">
                                    Unlock your full fitness journey
                                </p>
                                <p className="truncate text-xs text-muted-foreground">
                                    Get personalized workout &amp; meal plans plus expert coaching.
                                </p>
                            </div>
                            <Link
                                href={route('packages.index')}
                                className="flex shrink-0 items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-black transition-colors hover:bg-primary/90"
                            >
                                View Plans <ArrowRight className="h-4 w-4" />
                            </Link>
                        </div>
                    ) : null}

                    {/* Greeting */}
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            {timeGreeting}, <span className="text-primary">{firstName}</span>
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">Here's how your week is going.</p>
                    </div>

                    <AutoPlanGeneratorCard />

                    {/* Answer-first summary */}
                    <WeekAtGlance
                        title="This week"
                        subtitle="Your workouts, meals and water at a glance"
                        tiles={glanceTiles}
                    />

                    {/* Level & Points progression + trend */}
                    <section>
                        <SectionHeading
                            icon={<Target className="h-4 w-4" />}
                            title="Your Progression"
                            description="Infinite levels powered by workout, nutrition, and hydration points"
                        />
                        <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">
                            <FitnessScoreWidget data={fitnessScore} />
                            <div className="lg:col-span-2">
                                <FitnessScoreTrend history={fitnessScoreHistory} weeks={12} />
                            </div>
                        </div>
                    </section>

                    {/* Detailed progress — ONE level of tabs, no nesting */}
                    <section>
                        <SectionHeading
                            icon={<Flame className="h-4 w-4" />}
                            title="Progress"
                            description="Drill into workouts or food &amp; water"
                        />

                        <Tabs value={section} onValueChange={(v) => setSection(v as 'workouts' | 'nutrition')}>
                            <TabsList className="mb-4 grid h-12 w-full grid-cols-2 rounded-xl border border-border bg-muted/60 p-1">
                                <TabsTrigger
                                    value="workouts"
                                    className="gap-2 rounded-lg text-sm font-semibold data-[state=active]:bg-card data-[state=active]:shadow-sm"
                                >
                                    <Dumbbell className="h-4 w-4" />
                                    Workouts
                                </TabsTrigger>
                                <TabsTrigger
                                    value="nutrition"
                                    className="gap-2 rounded-lg text-sm font-semibold data-[state=active]:bg-card data-[state=active]:shadow-sm"
                                >
                                    <Utensils className="h-4 w-4" />
                                    Food &amp; Water
                                </TabsTrigger>
                            </TabsList>

                            <TabsContent value="workouts" className="mt-0 space-y-4">
                                <WeekGoalCard
                                    title="Weekly Workouts"
                                    icon={<Dumbbell className="h-5 w-5" />}
                                    unit="workout"
                                    units="workouts"
                                    weeklyCompletionRate={workoutRate}
                                    activeDays={workoutStats?.mostActiveDays}
                                    comparisonStats={workoutStats?.comparisonStats}
                                    currentStreak={workoutStats?.currentStreak}
                                    accent={WORKOUT_ACCENT}
                                />
                                <Link
                                    href={route('activity.index')}
                                    className="flex items-center justify-between rounded-2xl border border-border bg-card/60 p-4 transition-all hover:bg-card hover:border-primary/40 group"
                                >
                                    <div className="flex items-center gap-3">
                                        <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                            <History className="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p className="text-sm font-bold text-foreground">Recent Workout Activity</p>
                                            <p className="text-xs text-muted-foreground">View your full log of completed workouts</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-1 text-xs font-semibold text-primary group-hover:translate-x-0.5 transition-transform">
                                        View All <ArrowRight className="h-4 w-4" />
                                    </div>
                                </Link>
                            </TabsContent>

                            <TabsContent value="nutrition" className="mt-0 space-y-4">
                                <WeekGoalCard
                                    title="Weekly Meals"
                                    icon={<Apple className="h-5 w-5" />}
                                    unit="meal"
                                    units="meals"
                                    weeklyCompletionRate={mealRate}
                                    activeDays={mealStats?.mostActiveDays}
                                    comparisonStats={mealStats?.comparisonStats}
                                    currentStreak={mealStats?.currentStreak}
                                    accent={MEAL_ACCENT}
                                />
                                <NutritionStatsCard
                                    nutritionAverages={mealStats?.nutritionAverages ?? EMPTY_NUTRITION}
                                />
                                {waterData ? (
                                    <WaterIntakeWidget
                                        initialLog={waterData.log}
                                        calculation={waterData.calculation}
                                        weeklyStats={waterData.weekly_stats}
                                    />
                                ) : null}
                                <Link
                                    href={route('activity.index')}
                                    className="flex items-center justify-between rounded-2xl border border-border bg-card/60 p-4 transition-all hover:bg-card hover:border-emerald-500/40 group"
                                >
                                    <div className="flex items-center gap-3">
                                        <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                                            <History className="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p className="text-sm font-bold text-foreground">Recent Meal Activity</p>
                                            <p className="text-xs text-muted-foreground">View your tracked meals and nutrition logs</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-1 text-xs font-semibold text-emerald-400 group-hover:translate-x-0.5 transition-transform">
                                        View All <ArrowRight className="h-4 w-4" />
                                    </div>
                                </Link>
                            </TabsContent>
                        </Tabs>
                    </section>

                    {/* Quick Actions */}
                    <section>
                        <SectionHeading icon={<Zap className="h-4 w-4" />} title="Quick Actions" />
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <QuickActionCard
                                title="Daily Schedule"
                                description="Track your workouts, meals, and daily points."
                                actionLabel="View"
                                actionRoute={route('schedule.index')}
                                bgImage="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1470&auto=format&fit=crop"
                            />
                            <QuickActionCard
                                title="InBody Tracking"
                                description="Monitor body composition, weight, and muscle trends."
                                actionLabel="View"
                                actionRoute={route('inbody.index')}
                                bgImage="https://images.unsplash.com/photo-1490645935967-10de6ba17061?q=80&w=1453&auto=format&fit=crop"
                            />
                            <QuickActionCard
                                title="Analytics"
                                description="Analyze strength progression and consistency."
                                actionLabel="View"
                                actionRoute={route('analytics.index')}
                                bgImage="https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=1470&auto=format&fit=crop"
                            />
                        </div>
                    </section>

                    {/* Achievements */}
                    <section>
                        <SectionHeading
                            icon={<Trophy className="h-4 w-4" />}
                            title="Achievements"
                            description={`${achievementJourney?.unlocked_count ?? 0} of ${
                                achievementJourney?.total_count ?? 0
                            } badges earned`}
                        />
                        <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                            {featuredBadges.length > 0 ? (
                                featuredBadges.map((badge) => (
                                    <Link
                                        key={badge.badge_id}
                                        href={route('achievements.index')}
                                        className="block cursor-pointer"
                                    >
                                        <AchievementStat
                                            title={badge.name}
                                            subtitle={
                                                badge.unlocked
                                                    ? 'Unlocked'
                                                    : `${Math.round(badge.progress)}% Complete`
                                            }
                                            progress={badge.unlocked ? 100 : badge.progress}
                                            icon={IconMap[badge.icon] || Trophy}
                                        />
                                    </Link>
                                ))
                            ) : (
                                <p className="col-span-full rounded-2xl border border-dashed border-border p-8 text-center text-sm text-muted-foreground">
                                    No badges yet — keep training to start your journey.
                                </p>
                            )}
                        </div>
                    </section>
                </div>
            </motion.div>
        </AuthenticatedLayout>
    );
}

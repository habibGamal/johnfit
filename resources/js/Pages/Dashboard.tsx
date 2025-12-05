import { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import {
  CheckCircle,
  Flame,
  Calendar,
  Calendar as CalendarIcon,
  Utensils,
  Apple
} from 'lucide-react';
import { WorkoutStats, MealStats } from '@/types';

// Import our modular components
import StatBadge from '@/Components/Dashboard/StatBadge';
import WelcomeSection from '@/Components/Dashboard/WelcomeSection';
import WeeklyProgressCard from '@/Components/Dashboard/WeeklyProgressCard';
import AchievementsCard from '@/Components/Dashboard/AchievementsCard';
import RecentActivityCard from '@/Components/Dashboard/RecentActivityCard';
import ActionCard from '@/Components/Dashboard/ActionCard';
import NutritionStatsCard from '@/Components/Dashboard/NutritionStatsCard';
import MealProgressCard from '@/Components/Dashboard/MealProgressCard';
import MealActivityCard from '@/Components/Dashboard/MealActivityCard';
import ActivityHeatmap from '@/Components/Dashboard/ActivityHeatmap';

interface DashboardProps {
  auth: {
    user: {
      name: string;
      email: string;
    };
  };
  workoutStats: WorkoutStats;
  mealStats: MealStats;
}

export default function Dashboard({ auth, workoutStats, mealStats }: DashboardProps) {
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
        <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          Dashboard
        </h2>
      }
    >
      <Head title="Dashboard" />

      <div className="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 py-8">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          {/* Welcome Section with Quick Stats */}
          <WelcomeSection
            firstName={firstName}
            greeting={timeGreeting}
            statBadges={
              <>
                <StatBadge
                  icon={<CheckCircle className="h-5 w-5" />}
                  label="Weekly Completion"
                  value={`${workoutStats?.weeklyCompletionRate?.percentage || 0}%`}
                  className="bg-emerald-500/10 border-emerald-200 dark:border-emerald-800"
                  iconClassName="text-emerald-600 dark:text-emerald-400"
                  valueClassName="text-emerald-700 dark:text-emerald-300"
                />
                <StatBadge
                  icon={<Flame className="h-5 w-5" />}
                  label="Current Streak"
                  value={`${workoutStats?.currentStreak || 0} days`}
                  className="bg-amber-500/10 border-amber-200 dark:border-amber-800"
                  iconClassName="text-amber-600 dark:text-amber-400"
                  valueClassName="text-amber-700 dark:text-amber-300"
                />
                <StatBadge
                  icon={<Calendar className="h-5 w-5" />}
                  label="Active Plans"
                  value={workoutStats?.aggregateStats?.active_plans || 0}
                  className="bg-indigo-500/10 border-indigo-200 dark:border-indigo-800"
                  iconClassName="text-indigo-600 dark:text-indigo-400"
                  valueClassName="text-indigo-700 dark:text-indigo-300"
                />
              </>
            }
          />

          {/* Main Statistics Section */}
          <div className="mb-10">
            <Tabs defaultValue="workouts" className="w-full">
              <div className="flex items-center justify-between mb-6">
                <TabsList className="grid w-full max-w-md grid-cols-2 bg-white/50 dark:bg-gray-800/50 p-1 backdrop-blur-sm rounded-xl border border-gray-200 dark:border-gray-700">
                  <TabsTrigger
                    value="workouts"
                    className="data-[state=active]:bg-white dark:data-[state=active]:bg-gray-700 data-[state=active]:shadow-sm rounded-lg transition-all duration-200"
                  >
                    Workout Progress
                  </TabsTrigger>
                  <TabsTrigger
                    value="nutrition"
                    className="data-[state=active]:bg-white dark:data-[state=active]:bg-gray-700 data-[state=active]:shadow-sm rounded-lg transition-all duration-200"
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
                      <AchievementsCard
                        achievements={workoutStats?.achievements || []}
                      />
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
          <div className="mb-6">
            <h3 className="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
              <span className="bg-primary/10 p-1.5 rounded-lg">
                <Flame className="w-5 h-5 text-primary" />
              </span>
              Quick Actions
            </h3>
            <p className="text-gray-500 dark:text-gray-400 text-sm mt-1 ml-10">Manage your fitness journey</p>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {/* Workout Plans Card */}
            <ActionCard
              title="Workout Plans"
              description="View and track your assigned workout plans"
              icon={<CalendarIcon className="h-5 w-5 text-primary" />}
              actionLabel="View Plans"
              actionRoute={route('workout-plans.index')}
            >
              <p>Follow your workout plans, mark exercises as complete, and track your progress over time.</p>
            </ActionCard>

            {/* Meal Plans Card */}
            <ActionCard
              title="Meal Plans"
              description="View and track your assigned meal plans"
              icon={<Apple className="h-5 w-5 text-emerald-600" />}
              actionLabel="View Plans"
              actionRoute={route('meal-plans.index')}
            >
              <p>Follow your meal plans, track your nutrition intake, and maintain healthy eating habits.</p>
            </ActionCard>

            {/* Additional action cards can be added here as needed */}
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}

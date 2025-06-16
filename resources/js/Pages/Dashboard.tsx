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
        <h2 className="text-xl font-semibold leading-tight text-gray-800">
          Dashboard
        </h2>
      }
    >
      <Head title="Dashboard" />

      <div className="py-12">
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
                  className="bg-primary/10"
                  iconClassName="text-primary"
                />
                <StatBadge
                  icon={<Flame className="h-5 w-5" />}
                  label="Current Streak"
                  value={`${workoutStats?.currentStreak || 0} days`}
                  className="bg-amber-500/10"
                  iconClassName="text-amber-500"
                />
                <StatBadge
                  icon={<Calendar className="h-5 w-5" />}
                  label="Active Plans"
                  value={workoutStats?.aggregateStats?.active_plans || 0}
                  className="bg-indigo-500/10"
                  iconClassName="text-indigo-500"
                />
              </>
            }
          />

          {/* Main Statistics Section */}
          <div className="mb-8">
            <Tabs defaultValue="workouts" className="w-full">
              <TabsList className="grid w-full grid-cols-2 mb-6">
                <TabsTrigger value="workouts">Workout Progress</TabsTrigger>
                <TabsTrigger value="nutrition">Nutrition Progress</TabsTrigger>
              </TabsList>

              {/* Workout Stats Tab */}
              <TabsContent value="workouts">
                <Tabs defaultValue="overview" className="w-full">
                  <TabsList className="w-full mb-4">
                    <TabsTrigger value="overview">Performance</TabsTrigger>
                    <TabsTrigger value="activity">Recent Activity</TabsTrigger>
                  </TabsList>

                  <TabsContent value="overview">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                      {/* Weekly Progress */}
                      <WeeklyProgressCard
                        weeklyCompletionRate={workoutStats?.weeklyCompletionRate || { completed: 0, total: 0, percentage: 0 }}
                        activeDaysData={workoutActiveDaysData}
                      />

                      {/* Achievement Stats */}
                      <AchievementsCard
                        aggregateStats={workoutStats?.aggregateStats || {
                          total_completions: 0,
                          active_plans: 0,
                          recent_completions: 0
                        }}
                        currentStreak={workoutStats?.currentStreak || 0}
                      />
                    </div>
                  </TabsContent>

                  <TabsContent value="activity">
                    <RecentActivityCard activities={workoutStats?.recentActivity || []} />
                  </TabsContent>
                </Tabs>
              </TabsContent>

              {/* Nutrition Stats Tab */}
              <TabsContent value="nutrition">
                <Tabs defaultValue="overview" className="w-full">
                  <TabsList className="w-full mb-4">
                    <TabsTrigger value="overview">Performance</TabsTrigger>
                    <TabsTrigger value="activity">Recent Activity</TabsTrigger>
                  </TabsList>

                  <TabsContent value="overview">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                      {/* Meal Progress */}
                      <MealProgressCard
                        weeklyCompletionRate={mealStats?.weeklyCompletionRate || { completed: 0, total: 0, percentage: 0 }}
                        activeDaysData={mealActiveDaysData}
                      />

                      {/* Nutrition Stats */}
                      <NutritionStatsCard
                        nutritionAverages={mealStats?.nutritionAverages || {
                          calories: 0, protein: 0, carbs: 0, fat: 0
                        }}
                      />
                    </div>
                  </TabsContent>

                  <TabsContent value="activity">
                    <MealActivityCard activities={mealStats?.recentActivity || []} />
                  </TabsContent>
                </Tabs>
              </TabsContent>
            </Tabs>
          </div>

          {/* Quick Actions */}
          <div className="mb-2">
            <h3 className="text-lg font-medium mb-4">Quick Actions</h3>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {/* Workout Plans Card */}
            <ActionCard
              title="Workout Plans"
              description="View and track your assigned workout plans"
              icon={<CalendarIcon className="h-4 w-4 text-primary" />}
              actionLabel="View Plans"
              actionRoute={route('workout-plans.index')}
            >
              <p>Follow your workout plans, mark exercises as complete, and track your progress over time.</p>
            </ActionCard>

            {/* Meal Plans Card */}
            <ActionCard
              title="Meal Plans"
              description="View and track your assigned meal plans"
              icon={<Apple className="h-4 w-4 text-green-600" />}
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

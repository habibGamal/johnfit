import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { MealPlan } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { ArrowLeft, Utensils, Calendar, Target, Flame, ChevronRight } from 'lucide-react';
import MealDay from '@/Components/MealPlans/MealDay';
import { Badge } from '@/Components/ui/badge';
import { Progress } from '@/Components/ui/progress';

interface ShowProps {
  mealPlan: MealPlan;
}

export default function Show({ mealPlan }: ShowProps) {
  // Calculate completion statistics
  const allMeals = mealPlan.plan.flatMap(day => [
    ...day.meals,
    ...day.timeSlots.flatMap(timeSlot => timeSlot.meals)
  ]);

  const totalMeals = allMeals.length;
  const completedMeals = allMeals.filter(meal => meal.completed).length;
  const completionPercentage = totalMeals > 0
    ? Math.round((completedMeals / totalMeals) * 100)
    : 0;

  // Calculate nutrition totals
  const totalNutrition = allMeals.reduce((acc, meal) => {
    const multiplier = Number(meal.quantity); // Assuming quantity is in grams and nutrition values are per 100g
    return {
      calories: acc.calories + (meal.calories * multiplier),
      protein: acc.protein + (meal.protein * multiplier),
      carbs: acc.carbs + (meal.carbs * multiplier),
      fat: acc.fat + (meal.fat * multiplier),
    };
  }, { calories: 0, protein: 0, carbs: 0, fat: 0 });

  // Calculate daily averages based on number of unique days in the plan
  const uniqueDays = new Set(mealPlan.plan.map(day => day.day)).size;
  const dailyAverage = {
    calories: uniqueDays > 0 ? Math.round(totalNutrition.calories / uniqueDays) : 0,
    protein: uniqueDays > 0 ? Math.round(totalNutrition.protein / uniqueDays) : 0,
    carbs: uniqueDays > 0 ? Math.round(totalNutrition.carbs / uniqueDays) : 0,
    fat: uniqueDays > 0 ? Math.round(totalNutrition.fat / uniqueDays) : 0,
  };

  // Get today's day name
  const today = new Date().toLocaleDateString('en-US', { weekday: 'long' });

  // Find today's meal plan if it exists
  const todayPlan = mealPlan.plan.find(day => day.day === today);

  return (
    <AuthenticatedLayout
      header={
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Button variant="ghost" size="icon" asChild className="rounded-full hover:bg-gray-100 dark:hover:bg-gray-800">
              <Link href={route('meal-plans.index')}>
                <ArrowLeft className="h-5 w-5" />
              </Link>
            </Button>
            <div>
              <h2 className="text-xl font-bold leading-tight text-gray-900 dark:text-gray-100">
                {mealPlan.name}
              </h2>
              <p className="text-xs text-muted-foreground">Your nutrition roadmap</p>
            </div>
          </div>
        </div>
      }
    >
      <Head title={mealPlan.name} />

      <div className="py-8">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 gap-8 md:grid-cols-12">

            {/* Sidebar Stats */}
            <div className="md:col-span-4 space-y-6">
              {/* Progress Card */}
              <Card className="border-none shadow-lg bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-950">
                <CardHeader className="pb-2">
                  <CardTitle className="text-lg flex items-center gap-2">
                    <Target className="h-5 w-5 text-primary" />
                    Overall Progress
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    <div className="flex items-end justify-between">
                      <span className="text-4xl font-bold text-primary">{completionPercentage}%</span>
                      <span className="text-sm text-muted-foreground mb-1">{completedMeals}/{totalMeals} meals</span>
                    </div>
                    <Progress value={completionPercentage} className="h-3" />
                  </div>
                </CardContent>
              </Card>

              {/* Nutrition Goals Card */}
              <Card className="border-none shadow-lg">
                <CardHeader>
                  <CardTitle className="flex items-center gap-2 text-lg">
                    <Flame className="h-5 w-5 text-orange-500" />
                    Daily Targets
                  </CardTitle>
                  <CardDescription>Average nutrition intake</CardDescription>
                </CardHeader>
                <CardContent className="grid gap-4">
                  <div className="flex items-center justify-between p-3 rounded-lg bg-orange-50 dark:bg-orange-950/20">
                    <span className="text-sm font-medium">Calories</span>
                    <span className="text-lg font-bold text-orange-600 dark:text-orange-400">{dailyAverage.calories.toLocaleString()}</span>
                  </div>
                  <div className="grid grid-cols-3 gap-2">
                    <div className="p-3 rounded-lg bg-blue-50 dark:bg-blue-950/20 text-center">
                      <span className="block text-xs text-muted-foreground">Protein</span>
                      <span className="block text-lg font-bold text-blue-600 dark:text-blue-400">{dailyAverage.protein}g</span>
                    </div>
                    <div className="p-3 rounded-lg bg-green-50 dark:bg-green-950/20 text-center">
                      <span className="block text-xs text-muted-foreground">Carbs</span>
                      <span className="block text-lg font-bold text-green-600 dark:text-green-400">{dailyAverage.carbs}g</span>
                    </div>
                    <div className="p-3 rounded-lg bg-yellow-50 dark:bg-yellow-950/20 text-center">
                      <span className="block text-xs text-muted-foreground">Fat</span>
                      <span className="block text-lg font-bold text-yellow-600 dark:text-yellow-400">{dailyAverage.fat}g</span>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Main Content */}
            <div className="md:col-span-8 space-y-8">
              {/* Today's Plan */}
              {todayPlan && (
                <div className="space-y-4">
                  <div className="flex items-center justify-between">
                    <h2 className="text-xl font-bold flex items-center gap-2">
                      <Calendar className="h-5 w-5 text-primary" />
                      Today's Focus
                    </h2>
                    <Badge variant="outline" className="px-3 py-1 bg-primary/10 text-primary border-primary/20">
                      {today}
                    </Badge>
                  </div>

                  <div className="relative z-10">
                    <MealDay
                      day={todayPlan}
                      mealPlanId={mealPlan.id}
                      defaultOpen={true}
                    />
                  </div>
                </div>
              )}

              {/* Full Week Plan */}
              <div className="space-y-4">
                <h2 className="text-lg font-semibold text-muted-foreground">Full Schedule</h2>
                <div className="space-y-4">
                  {mealPlan.plan.filter(d => d.day !== today).map((day) => (
                    <MealDay
                      key={day.day}
                      day={day}
                      mealPlanId={mealPlan.id}
                    />
                  ))}
                  {/* Re-render today if needed in the list or keep it separate. 
                      Common pattern is to show today at top and others below, usually duplicating isn't needed unless the list is strict.
                      If todayPlan exists, we showed it above. 
                      If we want to show ALL days in order, we might just map all of them. 
                      Let's stick to showing remaining days here or just all days if we want a complete list.
                      For a dashboard feel, "Today" projected at top is good. 
                  */}
                  {todayPlan && (
                    <div className="opacity-50 pointer-events-none grayscale hover:grayscale-0 transition-all">
                      {/* Optional: Show today again in the list as disabled/done or just skip it. 
                               The filter above skips it. Perfect.
                           */}
                    </div>
                  )}
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}

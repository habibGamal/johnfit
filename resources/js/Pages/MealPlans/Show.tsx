import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { MealPlan } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { ArrowLeft, Utensils, Calendar, BarChart4 } from 'lucide-react';
import MealDay from '@/Components/MealPlans/MealDay';
import { Badge } from '@/Components/ui/badge';

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
          <h2 className="text-xl font-semibold leading-tight text-gray-800">
            {mealPlan.name}
          </h2>
          <Button variant="outline" size="sm" asChild>
            <Link href={route('meal-plans.index')}>
              <ArrowLeft className="mr-2 h-4 w-4" />
              Back to Meal Plans
            </Link>
          </Button>
        </div>
      }
    >
      <Head title={mealPlan.name} />

      <div className="py-12">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
            {/* Sidebar */}
            <div className="space-y-6">
              {/* Summary Card */}
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Utensils className="h-5 w-5 text-primary" />
                    Summary
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    <div>
                      <div className="flex items-center justify-between text-sm mb-1">
                        <span>Total Progress</span>
                        <span className="font-medium">{completionPercentage}%</span>
                      </div>
                      <div className="h-2 w-full overflow-hidden rounded-full bg-secondary">
                        <div
                          className="h-full bg-primary"
                          style={{ width: `${completionPercentage}%` }}
                        />
                      </div>
                      <p className="mt-1 text-xs text-muted-foreground">
                        {completedMeals} of {totalMeals} meals consumed
                      </p>
                    </div>

                    <div className="pt-2">
                      <p className="text-sm font-medium mb-2">Meal Plan Days</p>
                      <div className="flex flex-wrap gap-2">
                        {mealPlan.plan.map(day => (
                          <Badge
                            key={day.day}
                            variant="outline"
                            className="flex items-center gap-1"
                          >
                            <Calendar className="h-3 w-3" /> {day.day}
                          </Badge>
                        ))}
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>

              {/* Nutrition Stats Card (Placeholder) */}
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <BarChart4 className="h-5 w-5 text-primary" />
                    Nutrition Goals
                  </CardTitle>
                  <CardDescription>Daily average targets</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="space-y-2">
                    <div className="flex justify-between">
                      <span className="text-sm">Calories</span>
                      <span className="text-sm font-medium">{dailyAverage.calories.toLocaleString()} kcal</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-sm">Protein</span>
                      <span className="text-sm font-medium">{dailyAverage.protein}g</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-sm">Carbs</span>
                      <span className="text-sm font-medium">{dailyAverage.carbs}g</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-sm">Fat</span>
                      <span className="text-sm font-medium">{dailyAverage.fat}g</span>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Main Content */}
            <div className="md:col-span-2">
              {/* Today's Plan (if available) */}
              {todayPlan && (
                <div className="mb-6">
                  <h2 className="text-lg font-semibold mb-4">Today's Meals</h2>
                  <MealDay
                    day={todayPlan}
                    mealPlanId={mealPlan.id}
                    defaultOpen={true}
                  />
                </div>
              )}

              {/* Full Week Plan */}
              <h2 className="text-lg font-semibold mb-4">Full Meal Plan</h2>
              <div className="space-y-4">
                {mealPlan.plan.map((day) => (
                  <MealDay
                    key={day.day}
                    day={day}
                    mealPlanId={mealPlan.id}
                  />
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}

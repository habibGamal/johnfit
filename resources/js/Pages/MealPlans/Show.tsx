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
  // All groups (each OR slot counts as one slot)
  const allGroups = mealPlan.plan.flatMap(day => [
    ...day.meals,
    ...day.timeSlots.flatMap(timeSlot => timeSlot.meals)
  ]);

  const totalMeals = allGroups.length;
  const completedMeals = allGroups.filter(group => group.completed).length;
  const completionPercentage = totalMeals > 0
    ? Math.round((completedMeals / totalMeals) * 100)
    : 0;

  // Calculate nutrition totals — for OR groups, average nutrition across their options
  const totalNutrition = allGroups.reduce((acc, group) => {
    const optionCount = group.options.length;
    if (optionCount === 0) { return acc; }

    const groupNutrition = group.options.reduce((oAcc, option) => {
      const multiplier = Number(option.quantity);
      return {
        calories: oAcc.calories + (option.calories * multiplier),
        protein: oAcc.protein + (option.protein * multiplier),
        carbs: oAcc.carbs + (option.carbs * multiplier),
        fat: oAcc.fat + (option.fat * multiplier),
      };
    }, { calories: 0, protein: 0, carbs: 0, fat: 0 });

    return {
      calories: acc.calories + groupNutrition.calories / optionCount,
      protein: acc.protein + groupNutrition.protein / optionCount,
      carbs: acc.carbs + groupNutrition.carbs / optionCount,
      fat: acc.fat + groupNutrition.fat / optionCount,
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
            <Button variant="ghost" size="icon" asChild className="rounded-full hover:bg-yellow-500/20 hover:text-yellow-500 transition-colors">
              <Link href={route('meal-plans.index')}>
                <ArrowLeft className="h-5 w-5" />
              </Link>
            </Button>
            <div>
              <h2 className="text-xl font-black uppercase tracking-wider text-foreground">
                {mealPlan.name}
              </h2>
              <p className="text-xs font-medium text-yellow-500 uppercase tracking-widest">Nutrition Plan</p>
            </div>
          </div>
        </div>
      }
    >
      <Head title={mealPlan.name} />

      <div className="py-8 relative">
        {/* Background Glow */}
        <div className="fixed top-20 right-0 w-[300px] h-[300px] bg-yellow-500/5 blur-[100px] pointer-events-none rounded-full" />

        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
          <div className="grid grid-cols-1 gap-8 md:grid-cols-12 items-start">

            {/* Sidebar Stats */}
            <div className="md:col-span-4 space-y-6 sticky top-8">
              {/* Progress Card */}
              <Card className="border border-white/10 bg-zinc-900/60 backdrop-blur-xl shadow-xl overflow-hidden relative">
                <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-yellow-500 to-transparent opacity-50" />
                <CardHeader className="pb-2">
                  <div className="flex items-center justify-between">
                    <CardTitle className="text-sm uppercase font-bold tracking-wider text-muted-foreground">
                      Plan Progress
                    </CardTitle>
                    <Target className="h-5 w-5 text-yellow-500" />
                  </div>
                </CardHeader>
                <CardContent className="pt-4">
                  <div className="relative flex items-center justify-center py-6">
                    {/* Radial Progress */}
                    <div className="relative h-40 w-40">
                      <svg className="h-full w-full -rotate-90 transform" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="40" fill="transparent" stroke="currentColor" strokeWidth="8" className="text-zinc-800" />
                        <circle
                          cx="50" cy="50" r="40"
                          fill="transparent"
                          stroke="currentColor"
                          strokeWidth="8"
                          strokeDasharray={`${251.2 * (completionPercentage / 100)} 251.2`}
                          strokeLinecap="round"
                          className="text-yellow-500 transition-all duration-1000 ease-out"
                        />
                      </svg>
                      <div className="absolute inset-0 flex flex-col items-center justify-center">
                        <span className="text-4xl font-black text-white">{completionPercentage}%</span>
                      </div>
                    </div>
                  </div>

                  <div className="mt-4 grid grid-cols-2 gap-4">
                    <div className="bg-zinc-950/50 rounded-lg p-3 text-center border border-white/5">
                      <div className="text-xs text-muted-foreground uppercase tracking-wider mb-1">Completed</div>
                      <div className="text-xl font-bold text-white">{completedMeals}</div>
                    </div>
                    <div className="bg-zinc-950/50 rounded-lg p-3 text-center border border-white/5">
                      <div className="text-xs text-muted-foreground uppercase tracking-wider mb-1">Total</div>
                      <div className="text-xl font-bold text-white">{totalMeals}</div>
                    </div>
                  </div>
                </CardContent>
              </Card>

              {/* Nutrition Goals Card */}
              <Card className="border border-white/5 bg-zinc-900/40 backdrop-blur-md">
                <CardHeader>
                  <CardTitle className="text-sm uppercase font-bold tracking-wider text-muted-foreground flex items-center gap-2">
                    <Flame className="h-4 w-4" />
                    Daily Targets
                  </CardTitle>
                  <CardDescription>Average nutrition intake</CardDescription>
                </CardHeader>
                <CardContent className="grid gap-4">
                  <div className="flex items-center justify-between p-3 rounded-lg bg-zinc-950/50 border border-white/5">
                    <span className="text-sm font-medium text-white">Calories</span>
                    <span className="text-lg font-bold text-yellow-500">{dailyAverage.calories.toLocaleString()}</span>
                  </div>
                  <div className="grid grid-cols-3 gap-2">
                    <div className="p-3 rounded-lg bg-zinc-950/50 border border-white/5 text-center">
                      <span className="block text-xs text-muted-foreground">Protein</span>
                      <span className="block text-lg font-bold text-blue-400">{dailyAverage.protein}g</span>
                    </div>
                    <div className="p-3 rounded-lg bg-zinc-950/50 border border-white/5 text-center">
                      <span className="block text-xs text-muted-foreground">Carbs</span>
                      <span className="block text-lg font-bold text-green-400">{dailyAverage.carbs}g</span>
                    </div>
                    <div className="p-3 rounded-lg bg-zinc-950/50 border border-white/5 text-center">
                      <span className="block text-xs text-muted-foreground">Fat</span>
                      <span className="block text-lg font-bold text-yellow-400">{dailyAverage.fat}g</span>
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
                    <h2 className="text-2xl font-black uppercase tracking-wide text-white flex items-center gap-2">
                      <Calendar className="h-5 w-5 text-yellow-500" />
                      Today's Focus
                    </h2>
                    <Badge variant="outline" className="px-3 py-1 bg-yellow-500/10 text-yellow-500 border-yellow-500/20">
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
                <h2 className="text-lg font-bold uppercase tracking-wider text-muted-foreground">Full Schedule</h2>
                <div className="space-y-4">
                  {mealPlan.plan.filter(d => d.day !== today).map((day) => (
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
      </div>
    </AuthenticatedLayout>
  );
}

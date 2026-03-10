import { MealPlan } from '@/types';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Utensils, CalendarDays, ChevronRight, Activity } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { Badge } from '@/Components/ui/badge';

interface MealPlanCardProps {
  mealPlan: MealPlan;
}

export default function MealPlanCard({ mealPlan }: MealPlanCardProps) {
  // Calculate completion statistics — each group counts as one slot
  const allGroups = mealPlan.plan.flatMap(day => [
    ...day.meals,
    ...day.timeSlots.flatMap(timeSlot => timeSlot.meals)
  ]);

  const totalMeals = allGroups.length;
  const completedMeals = allGroups.filter(group => group.completed).length;
  const completionPercentage = totalMeals > 0
    ? Math.round((completedMeals / totalMeals) * 100)
    : 0;

  return (
    <Card className="group h-full overflow-hidden border border-white/5 bg-zinc-900/40 backdrop-blur-md transition-all hover:border-yellow-500/30 hover:ring-1 hover:ring-yellow-500/10 hover:shadow-xl">
      <div className="absolute inset-0 bg-gradient-to-br from-transparent to-yellow-500/5 opacity-0 transition-opacity group-hover:opacity-100" />

      <CardHeader className="pb-3 relative">
        <div className="flex justify-between items-start">
          <div className="space-y-1">
            <CardTitle className="text-xl font-bold text-white">{mealPlan.name}</CardTitle>
            <CardDescription className="flex items-center gap-1.5 text-muted-foreground">
              <CalendarDays className="h-3.5 w-3.5" />
              {mealPlan.plan.length} {mealPlan.plan.length === 1 ? 'day' : 'days'} plan
            </CardDescription>
          </div>
          <div className="rounded-full bg-yellow-500/10 p-2 text-yellow-500">
            <Utensils className="h-5 w-5" />
          </div>
        </div>
      </CardHeader>

      <CardContent className="pb-2 relative">
        <div className="flex flex-wrap gap-2 mb-4">
          {mealPlan.plan.slice(0, 4).map((day) => (
            <Badge key={day.day} variant="outline" className="flex items-center gap-1 bg-zinc-950/30 border-white/10">
              <div className="h-1.5 w-1.5 rounded-full bg-yellow-500" />
              {day.day}
            </Badge>
          ))}
          {mealPlan.plan.length > 4 && (
            <Badge variant="outline" className="text-muted-foreground border-white/10 bg-zinc-950/30">
              +{mealPlan.plan.length - 4} more
            </Badge>
          )}
        </div>

        <div className="space-y-1.5">
          <div className="flex items-center justify-between text-xs">
            <span className="text-muted-foreground">Progress</span>
            <span className="font-medium text-white">{completionPercentage}%</span>
          </div>
          <div className="h-2 w-full overflow-hidden rounded-full bg-zinc-800">
            <div
              className="h-full bg-yellow-500 transition-all duration-300"
              style={{ width: `${completionPercentage}%` }}
            />
          </div>
          <p className="text-[10px] text-muted-foreground text-right">
            {completedMeals}/{totalMeals} meals
          </p>
        </div>
      </CardContent>

      <CardFooter className="relative pt-4">
        <Button
          className="w-full gap-2 bg-yellow-500 hover:bg-yellow-400 text-black font-bold shadow-sm transition-all group-hover:shadow-md hover:scale-[1.01]"
          asChild
        >
          <Link href={route('meal-plans.show', mealPlan.id)}>
            View Meal Plan
            <ChevronRight className="h-4 w-4" />
          </Link>
        </Button>
      </CardFooter>
    </Card>
  );
}

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

  return (
    <Card className="group h-full overflow-hidden border-muted/60 bg-card transition-all hover:border-primary/50 hover:shadow-lg">
      <div className="absolute inset-0 bg-gradient-to-br from-transparent to-muted/20 opacity-0 transition-opacity group-hover:opacity-100" />

      <CardHeader className="pb-3 relative">
        <div className="flex justify-between items-start">
          <div className="space-y-1">
            <CardTitle className="text-xl font-bold">{mealPlan.name}</CardTitle>
            <CardDescription className="flex items-center gap-1.5">
              <CalendarDays className="h-3.5 w-3.5" />
              {mealPlan.plan.length} {mealPlan.plan.length === 1 ? 'day' : 'days'} plan
            </CardDescription>
          </div>
          <div className="rounded-full bg-primary/10 p-2 text-primary">
            <Utensils className="h-5 w-5" />
          </div>
        </div>
      </CardHeader>

      <CardContent className="pb-2 relative">
        <div className="flex flex-wrap gap-2 mb-4">
          {mealPlan.plan.slice(0, 4).map((day) => (
            <Badge key={day.day} variant="outline" className="flex items-center gap-1 bg-background/50">
              <div className="h-1.5 w-1.5 rounded-full bg-primary" />
              {day.day}
            </Badge>
          ))}
          {mealPlan.plan.length > 4 && (
            <Badge variant="outline" className="text-muted-foreground">
              +{mealPlan.plan.length - 4} more
            </Badge>
          )}
        </div>

        <div className="space-y-1.5">
          <div className="flex items-center justify-between text-xs">
            <span className="text-muted-foreground">Progress</span>
            <span className="font-medium">{completionPercentage}%</span>
          </div>
          <div className="h-2 w-full overflow-hidden rounded-full bg-secondary/50">
            <div
              className="h-full bg-primary transition-all duration-300"
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
          className="w-full gap-2 shadow-sm transition-all group-hover:shadow-md"
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

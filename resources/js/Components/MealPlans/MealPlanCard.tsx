import { MealPlan } from '@/types';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Utensils, CalendarDays, ChevronRight } from 'lucide-react';
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
    <Card className="overflow-hidden">
      <CardHeader className="pb-2">
        <CardTitle className="text-xl">{mealPlan.name}</CardTitle>
        <CardDescription className="flex items-center gap-1">
          <CalendarDays className="h-4 w-4" />
          {mealPlan.plan.length} {mealPlan.plan.length === 1 ? 'day' : 'days'} plan
        </CardDescription>
      </CardHeader>

      <CardContent className="pb-2">
        <div className="flex flex-wrap gap-2">
          {mealPlan.plan.map((day) => (
            <Badge key={day.day} variant="outline" className="flex items-center gap-1">
              <Utensils className="h-3 w-3" /> {day.day}
            </Badge>
          ))}
        </div>

        <div className="mt-4">
          <div className="mb-1 flex items-center justify-between text-sm">
            <span>Completion</span>
            <span className="font-medium">{completionPercentage}%</span>
          </div>
          <div className="h-2 w-full overflow-hidden rounded-full bg-secondary">
            <div
              className="h-full bg-primary"
              style={{ width: `${completionPercentage}%` }}
            />
          </div>
          <p className="mt-1 text-xs text-muted-foreground text-right">
            {completedMeals} of {totalMeals} meals consumed
          </p>
        </div>
      </CardContent>

      <CardFooter>
        <Button
          variant="default"
          className="w-full"
          asChild
        >
          <Link href={route('meal-plans.show', mealPlan.id)}>
            View Meal Plan
            <ChevronRight className="ml-2 h-4 w-4" />
          </Link>
        </Button>
      </CardFooter>
    </Card>
  );
}

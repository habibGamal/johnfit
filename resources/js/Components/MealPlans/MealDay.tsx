import { MealDay as MealDayType } from '@/types';
import MealItem from './MealItem';
import MealTimeSlot from './MealTimeSlot';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/Components/ui/accordion';
import { CalendarDays, CheckCircle2 } from 'lucide-react';
import { Badge } from '@/Components/ui/badge';
import { cn } from '@/lib/utils';
import { Progress } from '@/Components/ui/progress';

interface MealDayProps {
  day: MealDayType;
  mealPlanId: number;
  defaultOpen?: boolean;
}

export default function MealDay({ day, mealPlanId, defaultOpen = false }: MealDayProps) {
  // Calculate total number of meals for the day
  const totalMeals =
    day.meals.length +
    day.timeSlots.reduce((acc, timeSlot) => acc + timeSlot.meals.length, 0);

  // Calculate completion status
  const completedMeals =
    day.meals.filter(meal => meal.completed).length +
    day.timeSlots.reduce((acc, timeSlot) =>
      acc + timeSlot.meals.filter(meal => meal.completed).length, 0);

  const isFullyCompleted = completedMeals === totalMeals && totalMeals > 0;
  const completionPercentage = totalMeals > 0
    ? Math.round((completedMeals / totalMeals) * 100)
    : 0;

  return (
    <Accordion type="single" collapsible defaultValue={defaultOpen ? day.day : undefined}>
      <AccordionItem value={day.day} className="border-none shadow-md bg-white dark:bg-card rounded-xl mb-4 overflow-hidden">
        <AccordionTrigger className={cn(
          "px-5 py-4 hover:no-underline transition-colors group",
          isFullyCompleted ? "bg-primary/5" : ""
        )}>
          <div className="flex items-center justify-between w-full pr-4">
            <div className="flex items-center gap-3">
              <div className={cn(
                "h-10 w-10 rounded-full flex items-center justify-center transition-colors",
                isFullyCompleted ? "bg-green-100 text-green-600 dark:bg-green-900/30" : "bg-primary/10 text-primary"
              )}>
                {isFullyCompleted ? <CheckCircle2 className="h-5 w-5" /> : <CalendarDays className="h-5 w-5" />}
              </div>
              <div className="text-left">
                <span className="block font-semibold text-lg leading-tight">{day.day}</span>
                <span className="text-xs text-muted-foreground font-normal">
                  {totalMeals} {totalMeals === 1 ? 'meal' : 'meals'} total
                </span>
              </div>
            </div>

            <div className="flex items-center gap-4">
              {/* Mini Progress Bar for desktop */}
              <div className="hidden sm:block w-24">
                <Progress value={completionPercentage} className="h-2" />
              </div>
              <Badge
                variant={isFullyCompleted ? "default" : "secondary"}
                className={cn(
                  "ml-auto",
                  isFullyCompleted && "bg-green-600 hover:bg-green-700"
                )}
              >
                {completedMeals}/{totalMeals} done
              </Badge>
            </div>
          </div>
        </AccordionTrigger>
        <AccordionContent>
          <div className="px-5 pt-2 pb-6 bg-muted/5">
            {/* Regular meals (not in time slots) */}
            {day.meals.length > 0 && (
              <div className="mb-6">
                <h3 className="text-sm font-semibold mb-3 px-1 text-muted-foreground uppercase tracking-wider text-[11px]">Unscheduled Meals</h3>
                <div className="space-y-3">
                  {day.meals.map((meal) => (
                    <MealItem
                      key={meal.id}
                      meal={meal}
                      mealPlanId={mealPlanId}
                      day={day.day}
                    />
                  ))}
                </div>
              </div>
            )}

            {/* Meals organized by time slots */}
            {day.timeSlots.map((timeSlot) => (
              <MealTimeSlot
                key={timeSlot.time}
                timeSlot={timeSlot}
                mealPlanId={mealPlanId}
                day={day.day}
              />
            ))}
          </div>
        </AccordionContent>
      </AccordionItem>
    </Accordion>
  );
}

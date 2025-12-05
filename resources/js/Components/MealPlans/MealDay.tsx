import { MealDay as MealDayType } from '@/types';
import MealItem from './MealItem';
import MealTimeSlot from './MealTimeSlot';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/Components/ui/accordion';
import { CalendarDays } from 'lucide-react';
import { Badge } from '@/Components/ui/badge';

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
      <AccordionItem value={day.day} className="border rounded-lg mb-4 overflow-hidden">
        <AccordionTrigger className="px-4 py-3 hover:no-underline">
          <div className="flex items-center justify-between w-full">
            <div className="flex items-center gap-2">
              <CalendarDays className="h-5 w-5 text-primary" />
              <span className="font-medium">{day.day}</span>
            </div>
            <div className="flex items-center gap-2">
              <Badge
                variant={isFullyCompleted ? "success" : "outline"}
              >
                {completedMeals}/{totalMeals} meals
              </Badge>
            </div>
          </div>
        </AccordionTrigger>
        <AccordionContent>
          <div className="px-4 pt-2 pb-4">
            {/* Regular meals (not in time slots) */}
            {day.meals.length > 0 && (
              <div className="mb-6">
                <h3 className="text-sm font-medium mb-3">General</h3>
                <div className="space-y-4">
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

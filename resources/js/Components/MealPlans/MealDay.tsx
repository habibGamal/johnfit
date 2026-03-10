import { MealDay as MealDayType } from '@/types';
import MealGroup from './MealGroup';
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
  // Each group (including OR groups) counts as one "meal slot"
  const totalMeals =
    day.meals.length +
    day.timeSlots.reduce((acc, timeSlot) => acc + timeSlot.meals.length, 0);

  // A group is completed when any one of its options is completed
  const completedMeals =
    day.meals.filter(group => group.completed).length +
    day.timeSlots.reduce((acc, timeSlot) =>
      acc + timeSlot.meals.filter(group => group.completed).length, 0);

  const isFullyCompleted = completedMeals === totalMeals && totalMeals > 0;
  const completionPercentage = totalMeals > 0
    ? Math.round((completedMeals / totalMeals) * 100)
    : 0;

  return (
    <Accordion type="single" collapsible defaultValue={defaultOpen ? day.day : undefined}>
      <AccordionItem value={day.day} className={cn(
        "border border-white/5 rounded-2xl bg-zinc-900/40 backdrop-blur-md overflow-hidden transition-all duration-300 mb-4",
        defaultOpen && "border-yellow-500/30 ring-1 ring-yellow-500/10"
      )}>
        <AccordionTrigger className={cn(
          "px-5 py-4 hover:no-underline transition-colors group",
          isFullyCompleted ? "bg-yellow-500/5" : ""
        )}>
          <div className="flex items-center justify-between w-full pr-4">
            <div className="flex items-center gap-3">
              <div className={cn(
                "h-10 w-10 rounded-full flex items-center justify-center border-2 transition-colors font-bold",
                isFullyCompleted ? "border-yellow-500 bg-yellow-500/10 text-yellow-500" : "border-zinc-700 bg-zinc-800 text-muted-foreground group-hover:border-zinc-600"
              )}>
                {isFullyCompleted ? <CheckCircle2 className="h-5 w-5" /> : <CalendarDays className="h-5 w-5" />}
              </div>
              <div className="text-left">
                <span className="block font-bold text-lg leading-tight uppercase tracking-wide text-zinc-300">{day.day}</span>
                <span className="text-xs text-muted-foreground font-medium">
                  {totalMeals} {totalMeals === 1 ? 'meal slot' : 'meal slots'} total
                </span>
              </div>
            </div>

            <div className="flex items-center gap-4">
              {/* Mini Progress Bar for desktop */}
              <div className="hidden sm:block w-24">
                <Progress value={completionPercentage} className="h-2 bg-zinc-800">
                  <div className="h-full bg-yellow-500 transition-all" style={{ width: `${completionPercentage}%` }} />
                </Progress>
              </div>
              <Badge
                variant={isFullyCompleted ? "default" : "secondary"}
                className={cn(
                  "ml-auto border-white/10",
                  isFullyCompleted && "bg-yellow-500 hover:bg-yellow-400 text-black border-yellow-500/20"
                )}
              >
                {completedMeals}/{totalMeals} done
              </Badge>
            </div>
          </div>
        </AccordionTrigger>
        <AccordionContent>
          <div className="px-5 pt-2 pb-6 bg-zinc-950/20">
            {/* Regular meal groups (not in time slots) */}
            {day.meals.length > 0 && (
              <div className="mb-6">
                <h3 className="text-sm font-bold mb-3 px-1 text-muted-foreground uppercase tracking-wider text-[11px]">Unscheduled Meals</h3>
                <div className="space-y-3">
                  {day.meals.map((group, index) => (
                    <MealGroup
                      key={`${group.group_meal_ids.join('-')}-${index}`}
                      group={group}
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

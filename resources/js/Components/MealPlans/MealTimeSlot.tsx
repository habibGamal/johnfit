import { MealTimeSlot as MealTimeSlotType, Meal } from '@/types';
import MealItem from './MealItem';
import { Clock } from 'lucide-react';

interface MealTimeSlotProps {
  timeSlot: MealTimeSlotType;
  mealPlanId: number;
  day: string;
}

export default function MealTimeSlot({ timeSlot, mealPlanId, day }: MealTimeSlotProps) {
  return (
    <div className="space-y-3 mb-6 last:mb-0">
      <div className="flex items-center gap-2 pb-1 border-b border-border/50">
        <Clock className="h-4 w-4 text-primary" />
        <h4 className="text-sm font-semibold text-foreground tracking-wide">{timeSlot.time}</h4>
      </div>
      <div className="space-y-3 pl-2 sm:pl-0">
        {timeSlot.meals.map((meal) => (
          <MealItem
            key={`${meal.id}-${timeSlot.time}`}
            meal={meal}
            mealPlanId={mealPlanId}
            day={day}
            mealTime={timeSlot.time}
          />
        ))}
      </div>
    </div>
  );
}

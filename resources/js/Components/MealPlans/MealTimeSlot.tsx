import { MealTimeSlot as MealTimeSlotType } from '@/types';
import MealGroup from './MealGroup';
import { Clock } from 'lucide-react';

interface MealTimeSlotProps {
  timeSlot: MealTimeSlotType;
  mealPlanId: number;
  day: string;
}

export default function MealTimeSlot({ timeSlot, mealPlanId, day }: MealTimeSlotProps) {
  return (
    <div className="space-y-3 mb-6 last:mb-0">
      <div className="flex items-center gap-2 pb-2 border-b border-white/10 bg-zinc-950/20 px-3 py-2 rounded-t-lg">
        <Clock className="h-4 w-4 text-yellow-500" />
        <h4 className="text-sm font-bold text-white uppercase tracking-wider">{timeSlot.time}</h4>
      </div>
      <div className="space-y-3 pl-2 sm:pl-0">
        {timeSlot.meals.map((group, index) => (
          <MealGroup
            key={`${group.group_meal_ids.join('-')}-${index}`}
            group={group}
            mealPlanId={mealPlanId}
            day={day}
            mealTime={timeSlot.time}
          />
        ))}
      </div>
    </div>
  );
}

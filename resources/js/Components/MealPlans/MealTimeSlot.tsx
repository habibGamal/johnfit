import { MealTimeSlot as MealTimeSlotType, Meal } from '@/types';
import MealItem from './MealItem';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Clock } from 'lucide-react';

interface MealTimeSlotProps {
  timeSlot: MealTimeSlotType;
  mealPlanId: number;
  day: string;
}

export default function MealTimeSlot({ timeSlot, mealPlanId, day }: MealTimeSlotProps) {
  return (
    <Card className="mb-6">
      <CardHeader className="pb-3">
        <CardTitle className="text-base flex items-center gap-2">
          <Clock className="h-4 w-4 text-primary" />
          {timeSlot.time}
        </CardTitle>
      </CardHeader>
      <CardContent className="pt-0">
        <div className="space-y-4">
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
      </CardContent>
    </Card>
  );
}

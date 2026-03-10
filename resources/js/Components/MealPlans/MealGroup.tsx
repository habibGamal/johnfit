import { MealGroup as MealGroupType } from '@/types';
import MealItem from './MealItem';
import { cn } from '@/lib/utils';

interface MealGroupProps {
  group: MealGroupType;
  mealPlanId: number;
  day: string;
  mealTime?: string;
}

export default function MealGroup({ group, mealPlanId, day, mealTime }: MealGroupProps) {
  const isOrGroup = group.options.length > 1;

  if (!isOrGroup) {
    return (
      <MealItem
        meal={group.options[0]}
        mealPlanId={mealPlanId}
        day={day}
        mealTime={mealTime}
        groupMealIds={group.group_meal_ids}
      />
    );
  }

  return (
    <div className={cn(
      "relative rounded-xl border p-3 pt-5 space-y-0",
      group.completed
        ? "border-yellow-500/30 bg-yellow-500/5"
        : "border-white/10 bg-zinc-950/30"
    )}>
      {/* "Choose One" badge */}
      <div className="absolute -top-3 left-4">
        <span className={cn(
          "text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full border",
          group.completed
            ? "bg-yellow-500 text-black border-yellow-500/20"
            : "bg-zinc-800 text-yellow-500 border-yellow-500/30"
        )}>
          Choose One
        </span>
      </div>

      <div className="space-y-1">
        {group.options.map((meal, index) => (
          <div key={meal.id}>
            {index > 0 && (
              <div className="flex items-center gap-2 my-2 px-1">
                <div className="flex-1 h-px bg-white/10" />
                <span className="text-[10px] font-bold text-yellow-500 bg-zinc-900 px-2.5 py-0.5 rounded-full border border-yellow-500/30 uppercase tracking-widest">
                  OR
                </span>
                <div className="flex-1 h-px bg-white/10" />
              </div>
            )}
            <MealItem
              meal={meal}
              mealPlanId={mealPlanId}
              day={day}
              mealTime={mealTime}
              groupMealIds={group.group_meal_ids}
            />
          </div>
        ))}
      </div>
    </div>
  );
}

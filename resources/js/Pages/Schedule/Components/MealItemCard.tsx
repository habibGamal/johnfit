import { router } from '@inertiajs/react';
import { UserDailyItem } from '@/types';
import { Card, CardContent } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Utensils, CheckCircle2, ChevronRight, Flame } from 'lucide-react';
import { cn } from '@/lib/utils';

interface MealItemCardProps {
    item: UserDailyItem;
    isLocked: boolean;
    onOpenMealModal: (item: UserDailyItem) => void;
}

export default function MealItemCard({
    item,
    isLocked,
    onOpenMealModal,
}: MealItemCardProps) {
    const handleToggle = (e: React.MouseEvent) => {
        e.stopPropagation();
        if (isLocked) return;

        router.post(
            route('schedule.items.toggle', item.id),
            { completed: !item.is_completed },
            { preserveScroll: true, preserveState: true }
        );
    };

    const targetDetails = item.target_details || {};
    const options = (targetDetails.options || []) as any[];
    const hasOptions = options.length > 1;

    const consumedOptionId = item.execution_payload?.consumed_option_id || item.reference_id;
    const activeOption: any = options.find((opt: any) => opt.meal_id === consumedOptionId) || targetDetails.primary_option || options[0] || {};

    const timeSlot = targetDetails.time_slot || 'Meal';
    const consumedQty = item.execution_payload?.consumed_quantity || activeOption.quantity || 100;
    const baseQty = activeOption.quantity || 100;
    const ratio = baseQty > 0 ? consumedQty / baseQty : 1;

    const calories = Math.round((activeOption.calories || 0) * (item.is_completed ? ratio : 1));
    const protein = Math.round((activeOption.protein || 0) * (item.is_completed ? ratio : 1) * 10) / 10;
    const carbs = Math.round((activeOption.carbs || 0) * (item.is_completed ? ratio : 1) * 10) / 10;
    const fat = Math.round((activeOption.fat || 0) * (item.is_completed ? ratio : 1) * 10) / 10;

    return (
        <Card
            onClick={() => onOpenMealModal(item)}
            className={cn(
                'group relative cursor-pointer border transition-all duration-200 hover:shadow-md overflow-hidden',
                item.is_completed
                    ? 'bg-emerald-500/[0.03] border-emerald-500/40 hover:border-emerald-500/60'
                    : 'bg-card border-border hover:border-emerald-500/40'
            )}
        >
            <CardContent className="p-4 flex items-center justify-between gap-3">
                {/* Left: Checkbox + Meal Info */}
                <div className="flex items-center gap-3.5 min-w-0">
                    <button
                        type="button"
                        disabled={isLocked}
                        onClick={handleToggle}
                        className={cn(
                            'h-7 w-7 rounded-lg flex items-center justify-center transition-colors shrink-0',
                            item.is_completed
                                ? 'bg-emerald-600 text-white shadow-sm'
                                : 'border-2 border-border hover:border-emerald-500/60 bg-secondary/50 text-transparent'
                        )}
                        aria-label={item.is_completed ? 'Mark incomplete' : 'Mark complete'}
                    >
                        <CheckCircle2 className="h-4 w-4 fill-current stroke-background" />
                    </button>

                    <div className="space-y-1 min-w-0">
                        <div className="flex items-center gap-2 flex-wrap">
                            <span className="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
                                {timeSlot}
                            </span>
                            <Badge variant="outline" className="text-[10px] px-1.5 py-0 font-medium text-muted-foreground">
                                +{item.points} pts
                            </Badge>
                            {hasOptions && (
                                <Badge variant="secondary" className="text-[10px] px-1.5 py-0 font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20">
                                    ⚡ {options.length} Options (OR)
                                </Badge>
                            )}
                        </div>

                        <h4
                            className={cn(
                                'text-sm sm:text-base font-bold truncate text-foreground',
                                item.is_completed && 'line-through text-muted-foreground font-semibold'
                            )}
                        >
                            {item.item_name}
                        </h4>

                        {/* Nutrition pill badges */}
                        <div className="flex items-center gap-2 text-xs text-muted-foreground flex-wrap">
                            {calories > 0 && (
                                <span className="flex items-center gap-1 text-orange-500 font-semibold">
                                    <Flame className="h-3 w-3 fill-orange-500" /> {calories} kcal
                                </span>
                            )}
                            {protein > 0 && (
                                <span className="text-blue-500 font-medium">{protein}g P</span>
                            )}
                            {carbs > 0 && (
                                <span className="text-emerald-500 font-medium">{carbs}g C</span>
                            )}
                            {fat > 0 && (
                                <span className="text-amber-500 font-medium">{fat}g F</span>
                            )}
                        </div>
                    </div>
                </div>

                {/* Right: Action / Open meal details */}
                <div className="flex items-center gap-2 shrink-0">
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-8 text-xs font-semibold gap-1 text-muted-foreground group-hover:text-emerald-600 group-hover:bg-emerald-500/10"
                    >
                        <span className="hidden sm:inline">Log Portions</span>
                        <ChevronRight className="h-4 w-4" />
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
}

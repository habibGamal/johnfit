import { useState } from "react";
import { Card, CardContent } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Checkbox } from "@/Components/ui/checkbox";
import { Badge } from "@/Components/ui/badge";
import { Utensils, Flame, CheckCircle2, Circle } from "lucide-react";
import { Meal } from "@/types";
import { router } from "@inertiajs/react";
import { cn } from "@/lib/utils";

interface MealItemProps {
    meal: Meal;
    mealPlanId: number;
    day: string;
    mealTime?: string;
    groupMealIds?: number[];
}

export default function MealItem({
    meal,
    mealPlanId,
    day,
    mealTime,
    groupMealIds = [],
}: MealItemProps) {
    const [isLoading, setIsLoading] = useState<boolean>(false);

    const toggleCompletion = () => {
        router.post(
            route("meal-plans.toggle-completion"),
            {
                meal_plan_id: mealPlanId,
                day: day,
                meal_id: meal.id,
                meal_time: mealTime || null,
                quantity: meal.quantity,
                group_meal_ids: groupMealIds.length > 0 ? groupMealIds : undefined,
            },
            {
                onStart: () => setIsLoading(true),
                onFinish: () => setIsLoading(false),
                preserveScroll: true,
            }
        );
    };

    return (
        <div className={cn(
            "group relative overflow-hidden rounded-xl border border-white/5 bg-zinc-900/50 backdrop-blur-sm text-card-foreground shadow-sm transition-all hover:shadow-md hover:border-white/10",
            meal.completed && "bg-zinc-900/70 border-yellow-500/20"
        )}>
            {/* Completion Overlay Effect */}
            <div className={cn(
                "absolute inset-0 bg-yellow-500/5 pointer-events-none transition-opacity duration-300",
                meal.completed ? "opacity-100" : "opacity-0"
            )} />

            <div className="p-4 flex items-start gap-4 relative z-10">
                {/* Icon/Image Placeholder */}
                <div className="flex-shrink-0">
                    <div className={cn(
                        "h-14 w-14 rounded-full flex items-center justify-center transition-colors",
                        meal.completed ? "bg-yellow-500/20 text-yellow-500" : "bg-zinc-800 text-yellow-500"
                    )}>
                        <Utensils className="h-6 w-6" />
                    </div>
                </div>

                <div className="flex-1 min-w-0">
                    <div className="flex items-start justify-between gap-2">
                        <div>
                            <div className="flex items-center gap-2 mb-1">
                                <h4 className={cn(
                                    "font-semibold text-base leading-tight transition-colors",
                                    meal.completed ? "text-muted-foreground line-through decoration-yellow-500/50 decoration-2" : "text-white"
                                )}>
                                    {meal.name}
                                </h4>
                                {mealTime && (
                                    <Badge variant="outline" className="text-[10px] h-5 px-1.5 bg-zinc-950/50 border-white/10">
                                        {mealTime}
                                    </Badge>
                                )}
                            </div>
                            <p className="text-sm text-muted-foreground">
                                {Number(meal.quantity)}g serving
                            </p>
                        </div>

                        <button
                            onClick={toggleCompletion}
                            disabled={isLoading}
                            className={cn(
                                "flex-shrink-0 rounded-full transition-all focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 focus:ring-offset-zinc-900",
                                isLoading && "opacity-50 cursor-not-allowed"
                            )}
                        >
                            {meal.completed ? (
                                <CheckCircle2 className="h-6 w-6 text-yellow-500 fill-yellow-500/10" />
                            ) : (
                                <Circle className="h-6 w-6 text-muted-foreground hover:text-yellow-500 transition-colors" />
                            )}
                        </button>
                    </div>

                    {/* Nutrition Stats Grid */}
                    <div className="mt-3 grid grid-cols-4 gap-2 text-xs">
                        <div className="flex flex-col items-center justify-center p-1.5 rounded bg-zinc-950/50 border border-white/5 text-orange-400">
                            <span className="font-bold">{(meal.calories * Number(meal.quantity)).toFixed(0)}</span>
                            <span className="opacity-70 text-[10px] uppercase">kcal</span>
                        </div>
                        <div className="flex flex-col items-center justify-center p-1.5 rounded bg-zinc-950/50 border border-white/5 text-blue-400">
                            <span className="font-bold">{(meal.protein * Number(meal.quantity)).toFixed(1)}g</span>
                            <span className="opacity-70 text-[10px] uppercase">Pro</span>
                        </div>
                        <div className="flex flex-col items-center justify-center p-1.5 rounded bg-zinc-950/50 border border-white/5 text-green-400">
                            <span className="font-bold">{(meal.carbs * Number(meal.quantity)).toFixed(1)}g</span>
                            <span className="opacity-70 text-[10px] uppercase">Carb</span>
                        </div>
                        <div className="flex flex-col items-center justify-center p-1.5 rounded bg-zinc-950/50 border border-white/5 text-yellow-400">
                            <span className="font-bold">{(meal.fat * Number(meal.quantity)).toFixed(1)}g</span>
                            <span className="opacity-70 text-[10px] uppercase">Fat</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

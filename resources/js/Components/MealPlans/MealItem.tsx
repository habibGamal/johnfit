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
}

export default function MealItem({
    meal,
    mealPlanId,
    day,
    mealTime,
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
            "group relative overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm transition-all hover:shadow-md",
            meal.completed && "bg-muted/30 border-primary/20"
        )}>
            {/* Completion Overlay Effect */}
            <div className={cn(
                "absolute inset-0 bg-primary/5 pointer-events-none transition-opacity duration-300",
                meal.completed ? "opacity-100" : "opacity-0"
            )} />

            <div className="p-4 flex items-start gap-4 relative z-10">
                {/* Icon/Image Placeholder */}
                <div className="flex-shrink-0">
                    <div className={cn(
                        "h-14 w-14 rounded-full flex items-center justify-center transition-colors",
                        meal.completed ? "bg-primary/20 text-primary" : "bg-primary/10 text-primary"
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
                                    meal.completed ? "text-muted-foreground line-through decoration-primary/50 decoration-2" : "text-foreground"
                                )}>
                                    {meal.name}
                                </h4>
                                {mealTime && (
                                    <Badge variant="outline" className="text-[10px] h-5 px-1.5 bg-background/50">
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
                                "flex-shrink-0 rounded-full transition-all focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2",
                                isLoading && "opacity-50 cursor-not-allowed"
                            )}
                        >
                            {meal.completed ? (
                                <CheckCircle2 className="h-6 w-6 text-primary fill-primary/10" />
                            ) : (
                                <Circle className="h-6 w-6 text-muted-foreground hover:text-primary transition-colors" />
                            )}
                        </button>
                    </div>

                    {/* Nutrition Stats Grid */}
                    <div className="mt-3 grid grid-cols-4 gap-2 text-xs">
                        <div className="flex flex-col items-center justify-center p-1.5 rounded bg-orange-50 dark:bg-orange-950/20 text-orange-700 dark:text-orange-400">
                            <span className="font-bold">{(meal.calories * Number(meal.quantity)).toFixed(0)}</span>
                            <span className="opacity-70 text-[10px] uppercase">kcal</span>
                        </div>
                        <div className="flex flex-col items-center justify-center p-1.5 rounded bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400">
                            <span className="font-bold">{(meal.protein * Number(meal.quantity)).toFixed(1)}g</span>
                            <span className="opacity-70 text-[10px] uppercase">Pro</span>
                        </div>
                        <div className="flex flex-col items-center justify-center p-1.5 rounded bg-green-50 dark:bg-green-950/20 text-green-700 dark:text-green-400">
                            <span className="font-bold">{(meal.carbs * Number(meal.quantity)).toFixed(1)}g</span>
                            <span className="opacity-70 text-[10px] uppercase">Carb</span>
                        </div>
                        <div className="flex flex-col items-center justify-center p-1.5 rounded bg-yellow-50 dark:bg-yellow-950/20 text-yellow-700 dark:text-yellow-400">
                            <span className="font-bold">{(meal.fat * Number(meal.quantity)).toFixed(1)}g</span>
                            <span className="opacity-70 text-[10px] uppercase">Fat</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

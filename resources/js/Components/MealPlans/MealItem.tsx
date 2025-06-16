import { useState } from "react";
import { Card, CardContent } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Checkbox } from "@/Components/ui/checkbox";
import { Badge } from "@/Components/ui/badge";
import { Utensils } from "lucide-react";
import { Meal } from "@/types";
import { router } from "@inertiajs/react";

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
    console.log("Meal Item Props:", {
        meal,
        mealPlanId,
        day,
        mealTime,
    });
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
            }
        );
    };

    return (
        <Card className="overflow-hidden border">
            <CardContent className="p-4 flex flex-col sm:flex-row sm:items-start gap-4">
                <div className="flex-shrink-0 mb-3 sm:mb-0">
                    <div className="h-16 w-16 sm:h-20 sm:w-20 overflow-hidden rounded-md bg-muted">
                        <div className="flex h-full w-full items-center justify-center bg-muted-foreground/10 text-muted-foreground/50">
                            <Utensils className="h-8 w-8" />
                        </div>
                    </div>
                </div>

                <div className="flex-1">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                        <h4 className="font-medium text-foreground">
                            {meal.name}
                        </h4>
                        <div className="flex items-center space-x-3">
                            {mealTime && (
                                <Badge variant="outline" className="text-xs">
                                    {mealTime}
                                </Badge>
                            )}
                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id={`meal-${meal.id}-${
                                        mealTime || "default"
                                    }`}
                                    checked={meal.completed}
                                    disabled={isLoading}
                                    onCheckedChange={toggleCompletion}
                                />
                                <label
                                    htmlFor={`meal-${meal.id}-${
                                        mealTime || "default"
                                    }`}
                                    className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                >
                                    {meal.completed
                                        ? "Consumed"
                                        : "Mark as consumed"}
                                </label>
                            </div>
                        </div>
                    </div>{" "}
                    <div className="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-1 text-sm">
                        <div>
                            <span className="font-medium">Quantity:</span>{" "}
                            {meal.quantity} g
                        </div>
                        <div>
                            <span className="font-medium">Calories:</span>{" "}
                            {(meal.calories * Number(meal.quantity)).toFixed(2)} kcal
                        </div>
                        <div>
                            <span className="font-medium">Protein:</span>{" "}
                            {(meal.protein * Number(meal.quantity)).toFixed(2)} g
                        </div>
                        <div>
                            <span className="font-medium">Carbs:</span>{" "}
                            {(meal.carbs * Number(meal.quantity)).toFixed(2)} g
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}

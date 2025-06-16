<?php

namespace App\Services;

use App\Models\Meal;
use App\Models\MealCompletion;
use App\Models\MealPlan;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MealTrackingService
{
    /**
     * Format meal plans for display to a user.
     *
     * @param Collection $mealPlans
     * @param User $user
     * @return Collection
     */
    public function formatMealPlansForUser(Collection $mealPlans, User $user): Collection
    {
        return $mealPlans->map(function ($plan) use ($user) {
            $planData = json_decode(Storage::get($plan->file_path), true);

            $mealIds = collect($planData)->flatMap(function ($dayData) {
                // Handle both structures: directly in 'meals' or organized by 'time'
                $mealsCollection = collect();
                if (isset($dayData['meals'])) {
                    $mealsCollection = collect($dayData['meals'])->pluck('meal_id');
                }
                if (isset($dayData['time'])) {
                    foreach ($dayData['time'] as $timeData) {
                        if (isset($timeData['meals'])) {
                            $mealsCollection = $mealsCollection->concat(collect($timeData['meals'])->pluck('meal_id'));
                        }
                    }
                }
                return $mealsCollection;
            })->unique()->toArray();

            $meals = Meal::whereIn('id', $mealIds)->get()->keyBy('id');

            // Get user completion status for this plan
            $completions = MealCompletion::where('user_id', $user->id)
                ->where('meal_plan_id', $plan->id)
                ->get()
                ->keyBy(function ($item) {
                    return $item->day . '_' . $item->meal_id . '_' . $item->meal_time;
                });

            $formattedPlan = [];
            foreach ($planData as $dayData) {
                $day = $dayData['day'];
                $dayMeals = [];
                $timeSlots = [];

                // Handle meals directly assigned to the day (without time)
                if (isset($dayData['meals'])) {
                    foreach ($dayData['meals'] as $mealData) {
                        $meal_id = $mealData['meal_id'];
                        $meal = $meals[$meal_id] ?? null;

                        if ($meal) {
                            $completed = $completions->get($day . '_' . $meal_id . '_')->completed ?? false;

                            $dayMeals[] = [
                                'id' => $meal->id,
                                'name' => $meal->name,
                                'quantity' => $mealData['quantity'],
                                'calories' => $meal->calories,
                                'protein' => $meal->protein,
                                'carbs' => $meal->carbs,
                                'fat' => $meal->fat,
                                'completed' => $completed,
                            ];
                        }
                    }
                }

                // Handle meals organized by time
                if (isset($dayData['time'])) {
                    foreach ($dayData['time'] as $timeData) {
                        $mealTime = $timeData['meal_time'];
                        $timeMeals = [];

                        foreach ($timeData['meals'] as $mealData) {
                            $meal_id = $mealData['meal_id'];
                            $meal = $meals[$meal_id] ?? null;

                            if ($meal) {
                                $completed = $completions->get($day . '_' . $meal_id . '_' . $mealTime)->completed ?? false;

                                $timeMeals[] = [
                                    'id' => $meal->id,
                                    'name' => $meal->name,
                                    'quantity' => $mealData['quantity'],
                                    'calories' => $meal->calories,
                                    'protein' => $meal->protein,
                                    'carbs' => $meal->carbs,
                                    'fat' => $meal->fat,
                                    'completed' => $completed,
                                ];
                            }
                        }

                        if (!empty($timeMeals)) {
                            $timeSlots[] = [
                                'time' => $mealTime,
                                'meals' => $timeMeals,
                            ];
                        }
                    }
                }

                $formattedPlan[] = [
                    'day' => $day,
                    'meals' => $dayMeals,
                    'timeSlots' => $timeSlots,
                ];
            }

            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'plan' => $formattedPlan,
            ];
        });
    }

    /**
     * Format a specific meal plan for display to a user.
     *
     * @param MealPlan $mealPlan
     * @param User $user
     * @return array
     */
    public function formatMealPlanForUser(MealPlan $mealPlan, User $user): array
    {
        $planData = json_decode(Storage::get($mealPlan->file_path), true);

        $mealIds = collect($planData)->flatMap(function ($dayData) {
            // Handle both structures: directly in 'meals' or organized by 'time'
            $mealsCollection = collect();
            if (isset($dayData['meals'])) {
                $mealsCollection = collect($dayData['meals'])->pluck('meal_id');
            }
            if (isset($dayData['time'])) {
                foreach ($dayData['time'] as $timeData) {
                    if (isset($timeData['meals'])) {
                        $mealsCollection = $mealsCollection->concat(collect($timeData['meals'])->pluck('meal_id'));
                    }
                }
            }
            return $mealsCollection;
        })->unique()->toArray();

        $meals = Meal::whereIn('id', $mealIds)->get()->keyBy('id');

        // Get user completion status for this plan
        $completions = MealCompletion::where('user_id', $user->id)
            ->where('meal_plan_id', $mealPlan->id)
            ->get()
            ->keyBy(function ($item) {
                return $item->day . '_' . $item->meal_id . '_' . $item->meal_time;
            });

        $formattedPlan = [];
        foreach ($planData as $dayData) {
            $day = $dayData['day'];
            $dayMeals = [];
            $timeSlots = [];

            // Handle meals directly assigned to the day (without time)
            if (isset($dayData['meals'])) {
                foreach ($dayData['meals'] as $mealData) {
                    $meal_id = $mealData['meal_id'];
                    $meal = $meals[$meal_id] ?? null;

                    if ($meal) {
                        $completed = $completions->get($day . '_' . $meal_id . '_')->completed ?? false;

                        $dayMeals[] = [
                            'id' => $meal->id,
                            'name' => $meal->name,
                            'quantity' => $mealData['quantity'],
                            'calories' => $meal->calories,
                            'protein' => $meal->protein,
                            'carbs' => $meal->carbs,
                            'fat' => $meal->fat,
                            'completed' => $completed,
                        ];
                    }
                }
            }

            // Handle meals organized by time
            if (isset($dayData['time'])) {
                foreach ($dayData['time'] as $timeData) {
                    $mealTime = $timeData['meal_time'];
                    $timeMeals = [];

                    foreach ($timeData['meals'] as $mealData) {
                        $meal_id = $mealData['meal_id'];
                        $meal = $meals[$meal_id] ?? null;

                        if ($meal) {
                            $completed = $completions->get($day . '_' . $meal_id . '_' . $mealTime)->completed ?? false;

                            $timeMeals[] = [
                                'id' => $meal->id,
                                'name' => $meal->name,
                                'quantity' => $mealData['quantity'],
                                'calories' => $meal->calories,
                                'protein' => $meal->protein,
                                'carbs' => $meal->carbs,
                                'fat' => $meal->fat,
                                'completed' => $completed,
                            ];
                        }
                    }

                    if (!empty($timeMeals)) {
                        $timeSlots[] = [
                            'time' => $mealTime,
                            'meals' => $timeMeals,
                        ];
                    }
                }
            }

            $formattedPlan[] = [
                'day' => $day,
                'meals' => $dayMeals,
                'timeSlots' => $timeSlots,
            ];
        }

        return [
            'id' => $mealPlan->id,
            'name' => $mealPlan->name,
            'plan' => $formattedPlan,
        ];
    }

    /**
     * Toggle meal consumption tracking status for a user.
     *
     * @param User $user
     * @param int $mealPlanId
     * @param string $day
     * @param int $mealId
     * @param string|null $mealTime
     * @param float|null $quantity
     * @return bool The new completion status
     */
    public function toggleMealCompletion(User $user, int $mealPlanId, string $day, int $mealId, ?string $mealTime = null, ?float $quantity = null): bool
    {
        $completion = MealCompletion::firstOrNew([
            'user_id' => $user->id,
            'meal_plan_id' => $mealPlanId,
            'day' => $day,
            'meal_id' => $mealId,
            'meal_time' => $mealTime,
        ]);

        $completion->completed = !$completion->completed;

        // Update quantity if provided, otherwise keep existing or default to 1.0
        if ($quantity !== null) {
            $completion->quantity = $quantity;
        } elseif (!$completion->exists) {
            $completion->quantity = 1.0;
        }

        $completion->save();

        return $completion->completed;
    }

    /**
     * Check if a user can access a meal plan.
     *
     * @param User $user
     * @param MealPlan $mealPlan
     * @return bool
     */
    public function canUserAccessMealPlan(User $user, MealPlan $mealPlan): bool
    {
        return $user->mealPlans->contains($mealPlan->id);
    }
}

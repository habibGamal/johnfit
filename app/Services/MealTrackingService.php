<?php

namespace App\Services;

use App\Models\Meal;
use App\Models\MealCompletion;
use App\Models\MealPlan;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class MealTrackingService
{
    /**
     * Format meal plans for display to a user.
     */
    public function formatMealPlansForUser(Collection $mealPlans, User $user): Collection
    {
        return $mealPlans->map(function ($plan) use ($user) {
            $planData = json_decode(Storage::get($plan->file_path), true);

            $mealIds = $this->extractMealIds($planData);
            $meals = Meal::whereIn('id', $mealIds)->get()->keyBy('id');

            $completions = MealCompletion::where('user_id', $user->id)
                ->where('meal_plan_id', $plan->id)
                ->get()
                ->keyBy(function ($item) {
                    return $item->day.'_'.$item->meal_id.'_'.$item->meal_time;
                });

            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'plan' => $this->buildFormattedPlan($planData, $meals, $completions),
            ];
        });
    }

    /**
     * Format a specific meal plan for display to a user.
     */
    public function formatMealPlanForUser(MealPlan $mealPlan, User $user): array
    {
        $planData = json_decode(Storage::get($mealPlan->file_path), true);

        $mealIds = $this->extractMealIds($planData);
        $meals = Meal::whereIn('id', $mealIds)->get()->keyBy('id');

        $completions = MealCompletion::where('user_id', $user->id)
            ->where('meal_plan_id', $mealPlan->id)
            ->get()
            ->keyBy(function ($item) {
                return $item->day.'_'.$item->meal_id.'_'.$item->meal_time;
            });

        return [
            'id' => $mealPlan->id,
            'name' => $mealPlan->name,
            'plan' => $this->buildFormattedPlan($planData, $meals, $completions),
        ];
    }

    /**
     * Extract all unique meal IDs from plan data, handling the OR-options structure.
     */
    private function extractMealIds(array $planData): array
    {
        return collect($planData)->flatMap(function ($dayData) {
            $ids = collect();
            if (isset($dayData['meals'])) {
                $ids = $ids->concat(
                    collect($dayData['meals'])->flatMap(fn ($slot) => collect($slot['options'] ?? [])->pluck('meal_id'))
                );
            }
            if (isset($dayData['time'])) {
                foreach ($dayData['time'] as $timeData) {
                    if (isset($timeData['meals'])) {
                        $ids = $ids->concat(
                            collect($timeData['meals'])->flatMap(fn ($slot) => collect($slot['options'] ?? [])->pluck('meal_id'))
                        );
                    }
                }
            }

            return $ids;
        })->unique()->toArray();
    }

    /**
     * Build the formatted plan array with MealGroup entries for OR support.
     */
    private function buildFormattedPlan(array $planData, $meals, $completions): array
    {
        $formattedPlan = [];

        foreach ($planData as $dayData) {
            $day = $dayData['day'];
            $dayMeals = [];
            $timeSlots = [];

            if (isset($dayData['meals'])) {
                foreach ($dayData['meals'] as $mealSlot) {
                    $group = $this->buildMealGroup($mealSlot, $meals, $completions, $day, null);
                    if ($group !== null) {
                        $dayMeals[] = $group;
                    }
                }
            }

            if (isset($dayData['time'])) {
                foreach ($dayData['time'] as $timeData) {
                    $mealTime = $timeData['meal_time'];
                    $timeGroups = [];

                    foreach ($timeData['meals'] as $mealSlot) {
                        $group = $this->buildMealGroup($mealSlot, $meals, $completions, $day, $mealTime);
                        if ($group !== null) {
                            $timeGroups[] = $group;
                        }
                    }

                    if (! empty($timeGroups)) {
                        $timeSlots[] = [
                            'time' => $mealTime,
                            'meals' => $timeGroups,
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

        return $formattedPlan;
    }

    /**
     * Build a single MealGroup from a meal slot (which may have one or multiple OR options).
     */
    private function buildMealGroup(array $mealSlot, $meals, $completions, string $day, ?string $mealTime): ?array
    {
        $options = [];
        $groupCompleted = false;
        $groupMealIds = [];

        foreach ($mealSlot['options'] ?? [] as $optionData) {
            $meal_id = $optionData['meal_id'];
            $meal = $meals[$meal_id] ?? null;
            $groupMealIds[] = (int) $meal_id;

            if ($meal) {
                $completionKey = $day.'_'.$meal_id.'_'.($mealTime ?? '');
                $completed = $completions->get($completionKey)?->completed ?? false;

                if ($completed) {
                    $groupCompleted = true;
                }

                $options[] = [
                    'id' => $meal->id,
                    'name' => $meal->name,
                    'quantity' => $optionData['quantity'],
                    'calories' => $meal->calories,
                    'protein' => $meal->protein,
                    'carbs' => $meal->carbs,
                    'fat' => $meal->fat,
                    'completed' => $completed,
                ];
            }
        }

        if (empty($options)) {
            return null;
        }

        return [
            'options' => $options,
            'completed' => $groupCompleted,
            'group_meal_ids' => $groupMealIds,
        ];
    }

    /**
     * Toggle meal consumption tracking status for a user.
     * When completing a meal that belongs to an OR group, the other options in the
     * group are automatically uncompleted so only one can be active at a time.
     *
     * @param  array<int>  $groupMealIds  All meal IDs in the OR group (including $mealId)
     * @return bool The new completion status
     */
    public function toggleMealCompletion(User $user, int $mealPlanId, string $day, int $mealId, ?string $mealTime = null, ?float $quantity = null, array $groupMealIds = []): bool
    {
        $completion = MealCompletion::firstOrNew([
            'user_id' => $user->id,
            'meal_plan_id' => $mealPlanId,
            'day' => $day,
            'meal_id' => $mealId,
            'meal_time' => $mealTime,
        ]);

        $newStatus = ! $completion->completed;
        $completion->completed = $newStatus;

        if ($quantity !== null) {
            $completion->quantity = $quantity;
        } elseif (! $completion->exists) {
            $completion->quantity = 1.0;
        }

        $completion->save();

        // When marking one option as done, uncomplete any other completed option in the same OR group
        if ($newStatus && count($groupMealIds) > 1) {
            $otherMealIds = array_filter($groupMealIds, fn ($id) => (int) $id !== $mealId);
            if (! empty($otherMealIds)) {
                MealCompletion::where('user_id', $user->id)
                    ->where('meal_plan_id', $mealPlanId)
                    ->where('day', $day)
                    ->where('meal_time', $mealTime)
                    ->whereIn('meal_id', array_values($otherMealIds))
                    ->update(['completed' => false]);
            }
        }

        return $completion->completed;
    }

    /**
     * Check if a user can access a meal plan.
     */
    public function canUserAccessMealPlan(User $user, MealPlan $mealPlan): bool
    {
        return $user->mealPlans->contains($mealPlan->id);
    }
}

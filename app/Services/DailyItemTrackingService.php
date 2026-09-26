<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDailyItem;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class DailyItemTrackingService
{
    public function __construct(
        protected ProgressionService $progressionService,
        protected PointsService $pointsService
    ) {}

    /**
     * Toggle or set an item's completion status.
     */
    public function toggleItemCompletion(User $user, int $itemId, ?bool $status = null): UserDailyItem
    {
        $item = UserDailyItem::with('schedule')->findOrFail($itemId);

        if ($item->schedule->user_id !== $user->id) {
            throw ValidationException::withMessages(['item' => 'Unauthorized access to this daily item.']);
        }

        if ($item->schedule->is_locked) {
            throw ValidationException::withMessages(['schedule' => 'This daily schedule is locked and cannot be edited.']);
        }

        $wasCompleted = (bool) $item->is_completed;
        $newStatus = is_null($status) ? ! $item->is_completed : $status;

        $item->is_completed = $newStatus;
        $item->completed_at = $newStatus ? Carbon::now() : null;

        // If it's a workout item and being marked complete, ensure sets inside payload are marked complete if not customized
        if ($item->type === 'workout' && $item->execution_payload && isset($item->execution_payload['sets'])) {
            $payload = $item->execution_payload;
            if ($newStatus) {
                // If user simply clicks toggle on the workout card, mark all sets completed
                $payload['sets'] = collect($payload['sets'])->map(function ($set) {
                    $set['completed'] = true;
                    return $set;
                })->all();
            } else {
                $payload['sets'] = collect($payload['sets'])->map(function ($set) {
                    $set['completed'] = false;
                    return $set;
                })->all();
            }
            $item->execution_payload = $payload;
        }

        $item->save();

        $item->schedule->recalculateScores();

        // Award or deduct points based on completion transition
        if ($newStatus && ! $wasCompleted) {
            if ($item->type === 'workout') {
                $this->pointsService->addWorkoutPoints($user, (int) $item->points);
            } elseif ($item->type === 'meal') {
                $this->pointsService->addMealPoints($user, (int) $item->points);
            }
        } elseif (! $newStatus && $wasCompleted) {
            if ($item->type === 'workout') {
                $this->pointsService->deductWorkoutPoints($user, (int) $item->points);
            } elseif ($item->type === 'meal') {
                $this->pointsService->deductMealPoints($user, (int) $item->points);
            }
        }

        if ($item->type === 'workout') {
            $this->progressionService->clearUserCache($user);
        }

        return $item->fresh(['schedule']);
    }

    /**
     * Save workout sets execution payload and update completion state.
     */
    public function saveWorkoutSets(User $user, int $itemId, array $sets, ?int $selectedWorkoutId = null): UserDailyItem
    {
        $item = UserDailyItem::with('schedule')->findOrFail($itemId);

        if ($item->schedule->user_id !== $user->id) {
            throw ValidationException::withMessages(['item' => 'Unauthorized access to this daily item.']);
        }

        if ($item->schedule->is_locked) {
            throw ValidationException::withMessages(['schedule' => 'This daily schedule is locked and cannot be edited.']);
        }

        if ($item->type !== 'workout') {
            throw ValidationException::withMessages(['item' => 'This item is not a workout.']);
        }

        $payload = $item->execution_payload ?? [];
        $payload['sets'] = $sets;

        if ($selectedWorkoutId) {
            $payload['selected_workout_id'] = $selectedWorkoutId;

            // If options exist in target_details, update active item_name and reference_id to match chosen option
            $options = $item->target_details['options'] ?? [];
            $matchedOption = collect($options)->firstWhere('workout_id', $selectedWorkoutId);
            if ($matchedOption) {
                $item->item_name = $matchedOption['name'];
                $item->reference_id = $selectedWorkoutId;
            }
        }

        $item->execution_payload = $payload;

        $wasCompleted = (bool) $item->is_completed;

        // Check if all sets are completed
        $allCompleted = count($sets) > 0 && collect($sets)->every(function ($s) {
            return ! empty($s['completed']);
        });

        $item->is_completed = $allCompleted;
        $item->completed_at = $allCompleted ? ($item->completed_at ?? Carbon::now()) : null;
        $item->save();

        $item->schedule->recalculateScores();

        // Award or deduct points
        if ($allCompleted && ! $wasCompleted) {
            $this->pointsService->addWorkoutPoints($user, (int) $item->points);
        } elseif (! $allCompleted && $wasCompleted) {
            $this->pointsService->deductWorkoutPoints($user, (int) $item->points);
        }

        $this->progressionService->clearUserCache($user);

        return $item->fresh(['schedule']);
    }

    /**
     * Save meal consumption execution payload and mark complete.
     */
    public function saveMealConsumption(User $user, int $itemId, int $optionId, float $quantity = 1.0): UserDailyItem
    {
        $item = UserDailyItem::with('schedule')->findOrFail($itemId);

        if ($item->schedule->user_id !== $user->id) {
            throw ValidationException::withMessages(['item' => 'Unauthorized access to this daily item.']);
        }

        if ($item->schedule->is_locked) {
            throw ValidationException::withMessages(['schedule' => 'This daily schedule is locked and cannot be edited.']);
        }

        if ($item->type !== 'meal') {
            throw ValidationException::withMessages(['item' => 'This item is not a meal.']);
        }

        $wasCompleted = (bool) $item->is_completed;

        $payload = $item->execution_payload ?? [];
        $payload['consumed_option_id'] = $optionId;
        $payload['consumed_quantity'] = $quantity;

        // If options exist in target_details, update active item_name and reference_id to match chosen option
        $options = $item->target_details['options'] ?? [];
        $matchedOption = collect($options)->firstWhere('meal_id', $optionId);
        if ($matchedOption) {
            $item->item_name = $matchedOption['name'];
            $item->reference_id = $optionId;
        }

        $item->execution_payload = $payload;

        $item->is_completed = true;
        $item->completed_at = Carbon::now();
        $item->save();

        $item->schedule->recalculateScores();

        if (! $wasCompleted) {
            $this->pointsService->addMealPoints($user, (int) $item->points);
        }

        return $item->fresh(['schedule']);
    }
}

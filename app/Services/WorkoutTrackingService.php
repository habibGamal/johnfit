<?php

namespace App\Services;

use App\Models\Workout;
use App\Models\WorkoutCompletion;
use App\Models\WorkoutPlan;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class WorkoutTrackingService
{
    /**
     * Format workout plans for display to a user.
     *
     * @param Collection $workoutPlans
     * @param User $user
     * @return Collection
     */
    public function formatWorkoutPlansForUser(Collection $workoutPlans, User $user): Collection
    {
        return $workoutPlans->map(function ($plan) use ($user) {
            $planData = json_decode(Storage::get($plan->file_path), true);

            $workoutIds = collect($planData)->flatMap(function ($day) {
                return collect($day['workouts'])->pluck('workout_id');
            })->unique()->toArray();

            $workouts = Workout::whereIn('id', $workoutIds)->get()->keyBy('id');

            // Get user completion status for this plan
            $completions = WorkoutCompletion::where('user_id', $user->id)
                ->where('workout_plan_id', $plan->id)
                ->get()
                ->keyBy(function ($item) {
                    return $item->day . '_' . $item->workout_id;
                });

            $formattedPlan = [];
            foreach ($planData as $dayData) {
                $day = $dayData['day'];
                $dayWorkouts = [];

                foreach ($dayData['workouts'] as $workoutData) {
                    $workout_id = $workoutData['workout_id'];
                    $workout = $workouts[$workout_id] ?? null;

                    if ($workout) {
                        $completed = $completions->get($day . '_' . $workout_id)?->completed ?? false;

                        $dayWorkouts[] = [
                            'id' => $workout->id,
                            'name' => $workout->name,
                            'reps' => $workoutData['reps'],
                            'muscles' => $workout->muscles,
                            'tools' => $workout->tools,
                            'thumb' => $workout->thumb,
                            'video_url' => $workout->video_url,
                            'completed' => $completed,
                        ];
                    }
                }

                $formattedPlan[] = [
                    'day' => $day,
                    'workouts' => $dayWorkouts,
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
     * Format a specific workout plan for display to a user.
     *
     * @param WorkoutPlan $workoutPlan
     * @param User $user
     * @return array
     */
    public function formatWorkoutPlanForUser(WorkoutPlan $workoutPlan, User $user): array
    {
        $planData = json_decode(Storage::get($workoutPlan->file_path), true);

        $workoutIds = collect($planData)->flatMap(function ($day) {
            return collect($day['workouts'])->pluck('workout_id');
        })->unique()->toArray();

        $workouts = Workout::whereIn('id', $workoutIds)->get()->keyBy('id');

        // Get user completion status for this plan
        $completions = WorkoutCompletion::where('user_id', $user->id)
            ->where('workout_plan_id', $workoutPlan->id)
            ->get()
            ->keyBy(function ($item) {
                return $item->day . '_' . $item->workout_id;
            });

        $formattedPlan = [];
        foreach ($planData as $dayData) {
            $day = $dayData['day'];
            $dayWorkouts = [];

            foreach ($dayData['workouts'] as $workoutData) {
                $workout_id = $workoutData['workout_id'];
                $workout = $workouts[$workout_id] ?? null;

                if ($workout) {
                    $completed = $completions->get($day . '_' . $workout_id)?->completed ?? false;

                    $dayWorkouts[] = [
                        'id' => $workout->id,
                        'name' => $workout->name,
                        'reps' => isset($workoutData['reps']) && !empty($workoutData['reps'])
                            ? \App\Models\RepsPreset::find($workoutData['reps'])->value
                            : $workoutData['reps'],
                        'muscles' => $workout->muscles,
                        'tools' => $workout->tools,
                        'thumb' => $workout->thumb,
                        'video_url' => $workout->video_url,
                        'completed' => $completed,
                    ];
                }
            }

            $formattedPlan[] = [
                'day' => $day,
                'workouts' => $dayWorkouts,
            ];
        }

        return [
            'id' => $workoutPlan->id,
            'name' => $workoutPlan->name,
            'plan' => $formattedPlan,
        ];
    }

    /**
     * Toggle workout completion status for a user.
     *
     * @param User $user
     * @param int $workoutPlanId
     * @param string $day
     * @param int $workoutId
     * @return bool The new completion status
     */
    public function toggleWorkoutCompletion(User $user, int $workoutPlanId, string $day, int $workoutId): bool
    {
        $completion = WorkoutCompletion::firstOrNew([
            'user_id' => $user->id,
            'workout_plan_id' => $workoutPlanId,
            'day' => $day,
            'workout_id' => $workoutId,
        ]);

        $completion->completed = !$completion->completed;
        $completion->save();

        return $completion->completed;
    }

    /**
     * Check if a user can access a workout plan.
     *
     * @param User $user
     * @param WorkoutPlan $workoutPlan
     * @return bool
     */
    public function canUserAccessWorkoutPlan(User $user, WorkoutPlan $workoutPlan): bool
    {
        return $user->workoutPlans->contains($workoutPlan->id);
    }
}

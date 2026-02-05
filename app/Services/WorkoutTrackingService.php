<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutCompletion;
use App\Models\WorkoutPlan;
use App\Models\WorkoutSetCompletion;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class WorkoutTrackingService
{
    /**
     * Format workout plans for display to a user.
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
                    return $item->day.'_'.$item->workout_id;
                });

            $formattedPlan = [];
            foreach ($planData as $dayData) {
                $day = trim($dayData['day']);
                $dayWorkouts = [];

                foreach ($dayData['workouts'] as $workoutData) {
                    $workout_id = $workoutData['workout_id'];
                    $workout = $workouts[$workout_id] ?? null;

                    if ($workout) {
                        $completed = $completions->get($day.'_'.$workout_id)?->completed ?? false;

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
     */
    public function formatWorkoutPlanForUser(WorkoutPlan $workoutPlan, User $user): array
    {
        $planData = json_decode(Storage::get($workoutPlan->file_path), true);
        $today = Carbon::today()->toDateString();

        $workoutIds = collect($planData)->flatMap(function ($day) {
            return collect($day['workouts'])->pluck('workout_id');
        })->unique()->toArray();

        $workouts = Workout::whereIn('id', $workoutIds)->get()->keyBy('id');

        // Get user completion status for this plan
        $completions = WorkoutCompletion::where('user_id', $user->id)
            ->where('workout_plan_id', $workoutPlan->id)
            ->get()
            ->keyBy(function ($item) {
                return $item->day.'_'.$item->workout_id;
            });

        // Get today's set completions for each workout
        $todaySets = WorkoutSetCompletion::where('user_id', $user->id)
            ->where('workout_plan_id', $workoutPlan->id)
            ->where('session_date', $today)
            ->get()
            ->groupBy(function ($item) {
                return $item->day.'_'.$item->workout_id;
            });

        // Get previous session sets for suggestions
        $previousSets = [];
        foreach ($workoutIds as $workoutId) {
            $prevSets = WorkoutSetCompletion::getPreviousSessionSets($user->id, $workoutId, $today);
            if ($prevSets->isNotEmpty()) {
                $previousSets[$workoutId] = $prevSets->map(function ($set) {
                    return [
                        'set_number' => $set->set_number,
                        'weight' => $set->weight,
                        'reps' => $set->reps,
                    ];
                })->toArray();
            }
        }

        $formattedPlan = [];
        foreach ($planData as $dayData) {
            $day = trim($dayData['day']);
            $dayWorkouts = [];

            foreach ($dayData['workouts'] as $workoutData) {
                $workout_id = $workoutData['workout_id'];
                $workout = $workouts[$workout_id] ?? null;

                if ($workout) {
                    $completed = $completions->get($day.'_'.$workout_id)?->completed ?? false;

                    // Get reps preset value
                    $repsPreset = null;
                    if (isset($workoutData['reps']) && ! empty($workoutData['reps'])) {
                        $repsPresetModel = \App\Models\RepsPreset::find($workoutData['reps']);
                        $repsPreset = $repsPresetModel ? $repsPresetModel->reps : null;
                    }

                    // Get today's sets for this workout
                    $workoutSets = $todaySets->get($day.'_'.$workout_id, collect())->map(function ($set) {
                        return [
                            'id' => $set->id,
                            'set_number' => $set->set_number,
                            'weight' => $set->weight,
                            'reps' => $set->reps,
                            'completed' => $set->completed,
                        ];
                    })->values()->toArray();

                    // Determine if this workout requires weight (based on tools)
                    $toolsArray = $workout->tools;
                    $requiresWeight = $this->workoutRequiresWeight($toolsArray);

                    $dayWorkouts[] = [
                        'id' => $workout->id,
                        'name' => $workout->name,
                        'reps' => $repsPresetModel->value ?? $workoutData['reps'],
                        'reps_preset' => $repsPreset,
                        'muscles' => $workout->muscles,
                        'tools' => $workout->tools,
                        'thumb' => $workout->thumb,
                        'video_url' => $workout->video_url,
                        'completed' => $completed,
                        'requires_weight' => $requiresWeight,
                        'sets' => $workoutSets,
                        'previous_sets' => $previousSets[$workout_id] ?? [],
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
     * Determine if a workout requires weight tracking based on tools.
     */
    protected function workoutRequiresWeight(array $tools): bool
    {
        $bodyweightTools = ['Bodyweight'];
        foreach ($tools as $tool) {
            if (in_array(trim($tool), $bodyweightTools)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Toggle workout completion status for a user.
     *
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

        $completion->completed = ! $completion->completed;
        $completion->save();

        return $completion->completed;
    }

    /**
     * Save or update a workout set.
     */
    public function saveWorkoutSet(
        User $user,
        int $workoutPlanId,
        string $day,
        int $workoutId,
        int $setNumber,
        ?float $weight,
        int $reps,
        bool $completed = true
    ): WorkoutSetCompletion {
        $today = Carbon::today()->toDateString();

        $set = WorkoutSetCompletion::updateOrCreate(
            [
                'user_id' => $user->id,
                'workout_plan_id' => $workoutPlanId,
                'day' => $day,
                'workout_id' => $workoutId,
                'set_number' => $setNumber,
                'session_date' => $today,
            ],
            [
                'weight' => $weight,
                'reps' => $reps,
                'completed' => $completed,
            ]
        );

        // Update workout overall completion based on sets
        $this->updateWorkoutCompletionFromSets($user, $workoutPlanId, $day, $workoutId);

        return $set;
    }

    /**
     * Toggle a set's completion status.
     */
    public function toggleSetCompletion(
        User $user,
        int $workoutPlanId,
        string $day,
        int $workoutId,
        int $setNumber
    ): ?WorkoutSetCompletion {
        $today = Carbon::today()->toDateString();

        $set = WorkoutSetCompletion::where([
            'user_id' => $user->id,
            'workout_plan_id' => $workoutPlanId,
            'day' => $day,
            'workout_id' => $workoutId,
            'set_number' => $setNumber,
            'session_date' => $today,
        ])->first();

        if ($set) {
            $set->completed = ! $set->completed;
            $set->save();

            $this->updateWorkoutCompletionFromSets($user, $workoutPlanId, $day, $workoutId);
        }

        return $set;
    }

    /**
     * Delete a specific set.
     */
    public function deleteWorkoutSet(
        User $user,
        int $workoutPlanId,
        string $day,
        int $workoutId,
        int $setNumber
    ): bool {
        $today = Carbon::today()->toDateString();

        $deleted = WorkoutSetCompletion::where([
            'user_id' => $user->id,
            'workout_plan_id' => $workoutPlanId,
            'day' => $day,
            'workout_id' => $workoutId,
            'set_number' => $setNumber,
            'session_date' => $today,
        ])->delete();

        // Re-number remaining sets
        $this->renumberSets($user, $workoutPlanId, $day, $workoutId, $today);

        $this->updateWorkoutCompletionFromSets($user, $workoutPlanId, $day, $workoutId);

        return $deleted > 0;
    }

    /**
     * Re-number sets after deletion to maintain sequential order.
     */
    protected function renumberSets(User $user, int $workoutPlanId, string $day, int $workoutId, string $sessionDate): void
    {
        $sets = WorkoutSetCompletion::where([
            'user_id' => $user->id,
            'workout_plan_id' => $workoutPlanId,
            'day' => $day,
            'workout_id' => $workoutId,
            'session_date' => $sessionDate,
        ])->orderBy('set_number')->get();

        $newNumber = 1;
        foreach ($sets as $set) {
            if ($set->set_number !== $newNumber) {
                $set->set_number = $newNumber;
                $set->save();
            }
            $newNumber++;
        }
    }

    /**
     * Update overall workout completion based on set completions.
     */
    protected function updateWorkoutCompletionFromSets(User $user, int $workoutPlanId, string $day, int $workoutId): void
    {
        $today = Carbon::today()->toDateString();

        $sets = WorkoutSetCompletion::where([
            'user_id' => $user->id,
            'workout_plan_id' => $workoutPlanId,
            'day' => $day,
            'workout_id' => $workoutId,
            'session_date' => $today,
        ])->get();

        $hasCompletedSets = $sets->where('completed', true)->count() > 0;

        $completion = WorkoutCompletion::firstOrNew([
            'user_id' => $user->id,
            'workout_plan_id' => $workoutPlanId,
            'day' => $day,
            'workout_id' => $workoutId,
        ]);

        $completion->completed = $hasCompletedSets;
        $completion->save();
    }

    /**
     * Finish a workout day, handling incomplete sets.
     *
     * @param  string  $action  'complete' or 'discard'
     */
    public function finishWorkoutDay(
        User $user,
        int $workoutPlanId,
        string $day,
        string $action = 'discard'
    ): array {
        $today = Carbon::today()->toDateString();

        $sets = WorkoutSetCompletion::where([
            'user_id' => $user->id,
            'workout_plan_id' => $workoutPlanId,
            'day' => $day,
            'session_date' => $today,
        ])->get();

        $completedCount = 0;
        $discardedCount = 0;

        foreach ($sets as $set) {
            if (! $set->completed) {
                if ($action === 'complete' && $set->reps > 0) {
                    $set->completed = true;
                    $set->save();
                    $completedCount++;
                } else {
                    // Discard incomplete or invalid sets
                    $set->delete();
                    $discardedCount++;
                }
            }
        }

        return [
            'completed' => $completedCount,
            'discarded' => $discardedCount,
        ];
    }

    /**
     * Get today's workout session summary.
     */
    public function getTodaySessionSummary(User $user, int $workoutPlanId, string $day): array
    {
        $today = Carbon::today()->toDateString();

        $sets = WorkoutSetCompletion::where([
            'user_id' => $user->id,
            'workout_plan_id' => $workoutPlanId,
            'day' => $day,
            'session_date' => $today,
        ])->get();

        $completedSets = $sets->where('completed', true)->count();
        $incompleteSets = $sets->where('completed', false)->count();
        $totalSets = $sets->count();

        return [
            'completed_sets' => $completedSets,
            'incomplete_sets' => $incompleteSets,
            'total_sets' => $totalSets,
            'has_incomplete' => $incompleteSets > 0,
        ];
    }

    /**
     * Check if a user can access a workout plan.
     */
    public function canUserAccessWorkoutPlan(User $user, WorkoutPlan $workoutPlan): bool
    {
        return $user->workoutPlans->contains($workoutPlan->id);
    }
}

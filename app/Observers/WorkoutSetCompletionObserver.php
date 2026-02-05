<?php

namespace App\Observers;

use App\Models\WorkoutSetCompletion;
use App\Services\ProgressionService;

class WorkoutSetCompletionObserver
{
    public function __construct(
        protected ProgressionService $progressionService
    ) {}

    /**
     * Handle the WorkoutSetCompletion "created" event.
     */
    public function created(WorkoutSetCompletion $workoutSetCompletion): void
    {
        $this->clearUserCache($workoutSetCompletion);
    }

    /**
     * Handle the WorkoutSetCompletion "updated" event.
     */
    public function updated(WorkoutSetCompletion $workoutSetCompletion): void
    {
        $this->clearUserCache($workoutSetCompletion);
    }

    /**
     * Handle the WorkoutSetCompletion "deleted" event.
     */
    public function deleted(WorkoutSetCompletion $workoutSetCompletion): void
    {
        $this->clearUserCache($workoutSetCompletion);
    }

    /**
     * Clear the progression analytics cache for the user.
     */
    protected function clearUserCache(WorkoutSetCompletion $workoutSetCompletion): void
    {
        if ($workoutSetCompletion->user) {
            $this->progressionService->clearUserCache($workoutSetCompletion->user);
        }
    }
}

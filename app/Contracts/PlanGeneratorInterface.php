<?php

namespace App\Contracts;

use App\Models\MealPlan;
use App\Models\User;
use App\Models\WorkoutPlan;

interface PlanGeneratorInterface
{
    /**
     * Generate both Workout Plan and Meal Plan for the given user.
     *
     * @return array{workout_plan: WorkoutPlan, meal_plan: MealPlan, summary: array}
     */
    public function generateForUser(User $user): array;
}

<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkoutPlan>
 */
class WorkoutPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $planName = $this->faker->words(3, true).' Plan';
        $fileName = 'workout_plans/test_'.uniqid().'.json';

        // Create a minimal test plan JSON
        $planData = [
            [
                'day' => 'Day 1',
                'workouts' => [],
            ],
        ];

        Storage::put($fileName, json_encode($planData));

        return [
            'name' => $planName,
            'file_path' => $fileName,
        ];
    }

    /**
     * Attach users to the plan after creation.
     */
    public function forUser(User $user): static
    {
        return $this->afterCreating(function ($plan) use ($user) {
            $plan->users()->attach($user->id);
        });
    }
}

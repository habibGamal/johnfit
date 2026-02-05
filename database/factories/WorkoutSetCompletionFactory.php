<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkoutSetCompletion>
 */
class WorkoutSetCompletionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'workout_plan_id' => WorkoutPlan::factory(),
            'day' => $this->faker->randomElement(['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5']),
            'workout_id' => Workout::factory(),
            'set_number' => $this->faker->numberBetween(1, 5),
            'weight' => $this->faker->randomFloat(2, 10, 200),
            'reps' => $this->faker->numberBetween(1, 20),
            'completed' => true,
            'session_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ];
    }

    /**
     * Create a completed set.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => true,
        ]);
    }

    /**
     * Create an incomplete set.
     */
    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => false,
        ]);
    }

    /**
     * Create a bodyweight set (no weight).
     */
    public function bodyweight(): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => null,
            'reps' => $this->faker->numberBetween(10, 30),
        ]);
    }

    /**
     * Create a heavy lifting set.
     */
    public function heavy(): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => $this->faker->randomFloat(2, 100, 250),
            'reps' => $this->faker->numberBetween(1, 5),
        ]);
    }

    /**
     * Create a hypertrophy-focused set.
     */
    public function hypertrophy(): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => $this->faker->randomFloat(2, 40, 100),
            'reps' => $this->faker->numberBetween(8, 12),
        ]);
    }

    /**
     * Create a set for today's session.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'session_date' => now()->toDateString(),
        ]);
    }

    /**
     * Create a set for a specific date.
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'session_date' => $date,
        ]);
    }

    /**
     * Create a set with specific weight and reps (for 1RM testing).
     */
    public function withPerformance(float $weight, int $reps): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => $weight,
            'reps' => $reps,
            'completed' => true,
        ]);
    }
}

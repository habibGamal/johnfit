<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workout>
 */
class WorkoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $muscles = $this->faker->randomElements([
            'Chest', 'Shoulder', 'Triceps', 'Biceps', 'Lat', 'Lower Back',
            'Quadriceps', 'Hamstrings', 'Glutes', 'Calf', 'Abdominals', 'Core',
        ], $this->faker->numberBetween(1, 3));

        $tools = $this->faker->randomElements([
            'Dumbbell', 'Barbell', 'Machine', 'Cable', 'Bodyweight', 'Kettlebell',
        ], $this->faker->numberBetween(1, 2));

        return [
            'name' => $this->faker->words(3, true).' Exercise',
            'muscles' => implode(',', $muscles),
            'tools' => implode(',', $tools),
            'thumb' => null,
            'video_url' => null,
        ];
    }

    /**
     * Create a chest exercise.
     */
    public function chest(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Bench Press',
            'muscles' => 'Chest,Triceps,Shoulder',
            'tools' => 'Barbell',
        ]);
    }

    /**
     * Create a back exercise.
     */
    public function back(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Deadlift',
            'muscles' => 'Lower Back,Hamstrings,Glutes',
            'tools' => 'Barbell',
        ]);
    }

    /**
     * Create a leg exercise.
     */
    public function legs(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Squat',
            'muscles' => 'Quadriceps,Glutes,Hamstrings',
            'tools' => 'Barbell',
        ]);
    }

    /**
     * Create a bodyweight exercise.
     */
    public function bodyweight(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Push Ups',
            'muscles' => 'Chest,Triceps,Shoulder',
            'tools' => 'Bodyweight',
        ]);
    }
}

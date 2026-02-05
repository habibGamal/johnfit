<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InBodyLog>
 */
class InBodyLogFactory extends Factory
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
            'weight' => $this->faker->randomFloat(2, 50, 150),
            'smm' => $this->faker->randomFloat(2, 20, 50),
            'pbf' => $this->faker->randomFloat(2, 5, 45),
            'bmi' => $this->faker->randomFloat(1, 15, 40),
            'bmr' => $this->faker->randomFloat(1, 1200, 2500),
            'body_water' => $this->faker->randomFloat(2, 30, 50),
            'lean_body_mass' => $this->faker->randomFloat(2, 35, 80),
            'visceral_fat' => $this->faker->randomFloat(1, 1, 20),
            'waist_hip_ratio' => $this->faker->randomFloat(3, 0.7, 1.0),
            'measured_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Create a log with healthy metrics.
     */
    public function healthy(): static
    {
        return $this->state(fn (array $attributes) => [
            'pbf' => $this->faker->randomFloat(2, 10, 20),
            'bmi' => $this->faker->randomFloat(1, 18.5, 24.9),
            'visceral_fat' => $this->faker->randomFloat(1, 1, 9),
        ]);
    }

    /**
     * Create a log indicating muscle gain progress.
     */
    public function muscleGain(): static
    {
        return $this->state(fn (array $attributes) => [
            'smm' => $this->faker->randomFloat(2, 35, 50),
            'pbf' => $this->faker->randomFloat(2, 12, 18),
        ]);
    }
}

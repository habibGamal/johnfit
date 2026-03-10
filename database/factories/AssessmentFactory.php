<?php

namespace Database\Factories;

use App\Enums\AssessmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assessment>
 */
class AssessmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question' => $this->faker->sentence(6),
            'image' => null,
            'type' => AssessmentType::Text,
            'options' => null,
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }
}

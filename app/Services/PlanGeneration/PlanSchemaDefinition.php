<?php

namespace App\Services\PlanGeneration;

use Prism\Prism\Contracts\Schema;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

class PlanSchemaDefinition
{
    /**
     * Get the Prism ObjectSchema defining the complete AI-generated meal and workout plan.
     */
    public static function create(): Schema
    {
        // 1. Meal Option Schema
        $mealOptionSchema = new ObjectSchema(
            name: 'meal_option',
            description: 'Individual meal selection with portion size in grams',
            properties: [
                new NumberSchema('meal_id', 'The exact integer ID of the meal from the available meals catalog'),
                new NumberSchema('quantity', 'Optimal portion size in grams (e.g. 50 to 350 grams)'),
            ],
            requiredFields: ['meal_id', 'quantity']
        );

        // 2. Meal Slot Item Schema
        $mealSlotItemSchema = new ObjectSchema(
            name: 'meal_item',
            description: 'Meal slot item container with option variants',
            properties: [
                new ArraySchema(
                    name: 'options',
                    description: 'Array of meal options (usually 1 primary option, optionally alternative options)',
                    items: $mealOptionSchema
                ),
            ],
            requiredFields: ['options']
        );

        // 3. Meal Time Slot Schema
        $mealTimeSlotSchema = new ObjectSchema(
            name: 'time_slot',
            description: 'A meal time slot such as Breakfast, Lunch, Snack, or Dinner',
            properties: [
                new StringSchema('meal_time', 'Name of meal slot: Breakfast, Lunch, Snack, or Dinner'),
                new ArraySchema(
                    name: 'meals',
                    description: 'List of meals scheduled for this time slot',
                    items: $mealSlotItemSchema
                ),
            ],
            requiredFields: ['meal_time', 'meals']
        );

        // 4. Meal Day Schema
        $mealDaySchema = new ObjectSchema(
            name: 'meal_day',
            description: 'Daily meal schedule for a single day',
            properties: [
                new StringSchema('day', 'Day label, strictly: Day 1, Day 2, Day 3, Day 4, Day 5, Day 6, or Day 7'),
                new ArraySchema(
                    name: 'time',
                    description: 'Scheduled meal time slots for this day',
                    items: $mealTimeSlotSchema
                ),
            ],
            requiredFields: ['day', 'time']
        );

        // 5. Meal Targets Schema
        $mealTargetsSchema = new ObjectSchema(
            name: 'targets',
            description: 'Daily macronutrient and caloric targets',
            properties: [
                new NumberSchema('calories', 'Daily target calories in kcal'),
                new NumberSchema('proteins', 'Daily target protein in grams'),
                new NumberSchema('carbs', 'Daily target carbohydrates in grams'),
                new NumberSchema('fats', 'Daily target healthy fats in grams'),
            ],
            requiredFields: ['calories', 'proteins', 'carbs', 'fats']
        );

        // 6. Complete Meal Plan Schema
        $mealPlanSchema = new ObjectSchema(
            name: 'meal_plan',
            description: 'Complete 7-day personalized meal plan',
            properties: [
                $mealTargetsSchema,
                new ArraySchema(
                    name: 'days',
                    description: '7-day meal plan array for Day 1 through Day 7',
                    items: $mealDaySchema
                ),
            ],
            requiredFields: ['targets', 'days']
        );

        // 7. Workout Item Schema
        $workoutItemSchema = new ObjectSchema(
            name: 'workout_item',
            description: 'Individual exercise assignment',
            properties: [
                new NumberSchema('workout_id', 'The exact integer ID of the workout exercise from the catalog'),
                new NumberSchema('reps_preset_id', 'The exact integer ID of the reps preset (e.g. 3x10 or 4x12)'),
            ],
            requiredFields: ['workout_id', 'reps_preset_id']
        );

        // 8. Workout Day Schema
        $workoutDaySchema = new ObjectSchema(
            name: 'workout_day',
            description: 'Daily workout schedule for a single day (empty array for rest days)',
            properties: [
                new StringSchema('day', 'Day label, strictly: Day 1, Day 2, Day 3, Day 4, Day 5, Day 6, or Day 7'),
                new ArraySchema(
                    name: 'workouts',
                    description: 'List of exercises for active training day, or empty array [] for rest day',
                    items: $workoutItemSchema
                ),
            ],
            requiredFields: ['day', 'workouts']
        );

        // 9. Complete Workout Plan Schema
        $workoutPlanSchema = new ObjectSchema(
            name: 'workout_plan',
            description: 'Complete 7-day workout routine',
            properties: [
                new ArraySchema(
                    name: 'days',
                    description: '7-day workout plan array for Day 1 through Day 7',
                    items: $workoutDaySchema
                ),
            ],
            requiredFields: ['days']
        );

        // 10. Summary Schema
        $summarySchema = new ObjectSchema(
            name: 'summary',
            description: 'High-level summary of the generated fitness plan',
            properties: [
                new StringSchema('goal', 'User fitness goal'),
                new NumberSchema('target_calories', 'Calculated target daily calories'),
                new StringSchema('workout_days', 'Number of active workout days per week'),
                new StringSchema('reasoning', 'Brief professional rationale for dietary and training split choices'),
            ],
            requiredFields: ['goal', 'target_calories', 'workout_days', 'reasoning']
        );

        // Root Structured Output Schema
        return new ObjectSchema(
            name: 'generated_fitness_plan',
            description: 'Root object containing tailored 7-day meal plan, 7-day workout plan, and summary',
            properties: [
                $summarySchema,
                $mealPlanSchema,
                $workoutPlanSchema,
            ],
            requiredFields: ['summary', 'meal_plan', 'workout_plan']
        );
    }
}

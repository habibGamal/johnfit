<?php

namespace App\Services\PlanGeneration;

class PlanPromptBuilder
{
    /**
     * Build system instructions for the AI fitness planning agent.
     */
    public static function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an elite, certified Sports Nutritionist and Strength & Conditioning Specialist AI Agent.
Your mission is to formulate an evidence-based, highly customized 7-day Meal Plan and 7-day Workout Plan for the user based strictly on their assessment data, biometric logs (InBody if available), and the catalog of available meals and exercises.

### CORE OPERATIONAL DIRECTIVES:

1. MEAL PLAN SPECIFICATIONS:
- Days must be exactly: "Day 1", "Day 2", "Day 3", "Day 4", "Day 5", "Day 6", "Day 7".
- Daily slots should match the user's meal frequency (typically "Breakfast", "Lunch", "Snack", "Dinner").
- EVERY meal item MUST use a valid `meal_id` present in the provided Available Meals Catalog. NEVER invent or hallucinate IDs.
- Quantities must be specified in grams (e.g., 50g to 350g) and reflect realistic dietary servings according to the food's calorie density.
- Honor user dietary restrictions (e.g., lactose-free, gluten-free) and actively favor user's preferred proteins, carbs, and healthy fats.
- Maintain variety across days: avoid repeating the identical meal in the same slot on consecutive days.
- Ensure daily macronutrient totals match the calculated targets (Protein, Carbs, Fats, Calories).

2. WORKOUT PLAN SPECIFICATIONS:
- Days must be exactly: "Day 1", "Day 2", "Day 3", "Day 4", "Day 5", "Day 6", "Day 7".
- The number of active training days must strictly match the user's committed weekly workout frequency (e.g., 2, 3, 4, or 5 days).
- On rest days, provide an empty workouts array: `[]`.
- On active days, schedule 4 to 6 exercises per session targeting an intelligent muscle split (e.g., Push/Pull/Legs or Upper/Lower) that suits the user's training location and equipment.
- EVERY workout item MUST use a valid `workout_id` present in the provided Available Workouts Catalog.
- EVERY workout item MUST use a valid `reps_preset_id` present in the provided Reps Presets list.

3. INTEGRITY & ZERO-HALLUCINATION GUARANTEE:
- You must strictly output the structured JSON according to the schema.
- All references (`meal_id`, `workout_id`, `reps_preset_id`) MUST resolve to valid items provided in the prompt catalogs.
PROMPT;
    }

    /**
     * Build structured user prompt with all biometric, assessment, and catalog context.
     */
    public static function buildUserPrompt(PlanGenerationContext $context): string
    {
        $estimatedTargets = $context->getEstimatedTargets();
        $inBody = $context->inBody;

        $inBodyText = $inBody
            ? sprintf(
                "Weight: %s kg, Height: %s cm, BMR: %s kcal, Body Fat: %s%%, SMM (Skeletal Muscle Mass): %s kg",
                $inBody->weight ?? 'N/A',
                $inBody->height ?? 'N/A',
                $inBody->bmr ?? 'N/A',
                $inBody->body_fat_percent ?? 'N/A',
                $inBody->muscle_mass ?? 'N/A'
            )
            : sprintf(
                "No recent InBody scan available. Baseline estimates -> Weight: %.1f kg, Height: %.1f cm, Estimated BMR: %.0f kcal",
                $context->getWeight(),
                $context->getHeight(),
                $context->getBmr()
            );

        $assessmentSummary = [
            'Goal' => $context->getGoal(),
            'Activity Level' => $context->getActivityText(),
            'Meal Frequency' => $context->getMealsPerDayText(),
            'Dietary Restrictions' => $context->getDietaryRestrictions() ?: 'None',
            'Preferred Proteins' => implode(', ', $context->getProteinPreferences()) ?: 'Any',
            'Preferred Carbohydrates' => implode(', ', $context->getCarbPreferences()) ?: 'Any',
            'Preferred Healthy Fats' => implode(', ', $context->getFatPreferences()) ?: 'Any',
            'Committed Workout Days' => $context->getWorkoutDaysText(),
            'Workout Duration per Session' => $context->getWorkoutDuration(),
            'Workout Location & Equipment' => $context->getWorkoutLocation(),
        ];

        $condensedMeals = $context->getCondensedMeals();
        $condensedWorkouts = $context->getCondensedWorkouts();
        $condensedPresets = $context->getCondensedPresets();

        $mealsJson = json_encode($condensedMeals, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $workoutsJson = json_encode($condensedWorkouts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $presetsJson = json_encode($condensedPresets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $prompt = "### USER PROFILE:\n";
        $prompt .= "- Name: " . $context->user->name . "\n";
        $prompt .= "- Email: " . $context->user->email . "\n";
        $prompt .= "- Biometrics / InBody: " . $inBodyText . "\n\n";

        $prompt .= "### ASSESSMENT ANSWERS:\n";
        foreach ($assessmentSummary as $key => $value) {
            $prompt .= "- {$key}: {$value}\n";
        }

        $prompt .= "\n### RECOMMENDED BASELINE TARGETS:\n";
        $prompt .= sprintf(
            "- Daily Calories: %s kcal\n- Protein: %s g\n- Carbs: %s g\n- Healthy Fats: %s g\n- Active Workout Days Count: %d days\n\n",
            $estimatedTargets['calories'],
            $estimatedTargets['proteins'],
            $estimatedTargets['carbs'],
            $estimatedTargets['fats'],
            $context->getWorkoutDaysCount()
        );

        $prompt .= "### AVAILABLE REPS PRESETS (Choose valid 'id' for 'reps_preset_id'):\n";
        $prompt .= $presetsJson . "\n\n";

        $prompt .= "### AVAILABLE MEALS CATALOG (Use valid 'id' for 'meal_id'):\n";
        $prompt .= $mealsJson . "\n\n";

        $prompt .= "### AVAILABLE WORKOUTS CATALOG (Use valid 'id' for 'workout_id'):\n";
        $prompt .= $workoutsJson . "\n\n";

        $prompt .= "Generate the complete, tailored 7-day Meal Plan and 7-day Workout Plan adhering to the instructions and JSON schema.";

        return $prompt;
    }
}

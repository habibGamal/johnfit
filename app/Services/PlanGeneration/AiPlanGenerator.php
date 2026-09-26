<?php

namespace App\Services\PlanGeneration;

use App\Contracts\PlanGeneratorInterface;
use App\Models\Meal;
use App\Models\MealPlan;
use App\Models\RepsPreset;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutPlan;
use App\Services\PlanAssignmentService;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use RuntimeException;

class AiPlanGenerator implements PlanGeneratorInterface
{
    public function __construct(
        protected PlanAssignmentService $assignmentService
    ) {
    }

    /**
     * Generate both Workout Plan and Meal Plan for the given user using AI Agent.
     *
     * @return array{workout_plan: WorkoutPlan, meal_plan: MealPlan, summary: array}
     */
    public function generateForUser(User $user): array
    {
        $context = PlanGenerationContext::createForUser($user);

        if ($context->meals->isEmpty()) {
            throw new RuntimeException('No meals found in database to generate plan.');
        }

        if ($context->workouts->isEmpty()) {
            throw new RuntimeException('No workouts found in database to generate plan.');
        }

        $apiKey = config('prism.providers.openai.api_key') ?: env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            throw new RuntimeException('OpenAI API key is missing. Please set OPENAI_API_KEY in .env.');
        }

        $providerName = config('plan_generation.ai.provider', 'openai');
        $model = config('plan_generation.ai.model', 'gpt-4o-mini');
        $providerEnum = Provider::tryFrom($providerName) ?? Provider::OpenAI;

        Log::info('Initiating AI Plan Generation for user', [
            'user_id' => $user->id,
            'provider' => $providerEnum->value,
            'model' => $model,
        ]);

        $schema = PlanSchemaDefinition::create();
        $systemPrompt = PlanPromptBuilder::buildSystemPrompt();
        $userPrompt = PlanPromptBuilder::buildUserPrompt($context);

        $response = Prism::structured()
            ->using($providerEnum, $model)
            ->withSchema($schema)
            ->withSystemPrompt($systemPrompt)
            ->withPrompt($userPrompt)
            ->withClientOptions([
                'timeout' => (int) config('plan_generation.ai.timeout', 60),
            ])
            ->asStructured();

        $data = $response->structured;

        if (empty($data) || ! isset($data['meal_plan']) || ! isset($data['workout_plan'])) {
            throw new RuntimeException('AI agent returned invalid or empty plan structure.');
        }

        // 1. Process and persist Meal Plan
        $mealPlan = $this->createMealPlan($user, $data['meal_plan'], $context);

        // 2. Process and persist Workout Plan
        $workoutPlan = $this->createWorkoutPlan($user, $data['workout_plan'], $context);

        $summaryData = $data['summary'] ?? [];

        return [
            'workout_plan' => $workoutPlan,
            'meal_plan' => $mealPlan,
            'summary' => [
                'goal' => $summaryData['goal'] ?? $context->getGoal(),
                'weight' => $context->getWeight(),
                'target_calories' => $mealPlan->targets['calories'] ?? 2000,
                'workout_days' => $summaryData['workout_days'] ?? $context->getWorkoutDaysText(),
                'reasoning' => $summaryData['reasoning'] ?? null,
                'engine' => 'ai_agent',
            ],
        ];
    }

    /**
     * Parse, sanitize, and persist the AI-generated Meal Plan.
     */
    protected function createMealPlan(User $user, array $mealPlanData, PlanGenerationContext $context): MealPlan
    {
        $validMealIds = $context->meals->pluck('id')->all();
        $fallbackMealId = $validMealIds[0] ?? null;

        $rawDays = $mealPlanData['days'] ?? [];
        $expectedDays = ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'];
        $formattedDays = [];

        // Index raw days by day name
        $daysByName = collect($rawDays)->keyBy(fn ($d) => trim($d['day'] ?? ''));

        foreach ($expectedDays as $dayName) {
            $dayData = $daysByName->get($dayName) ?? ['time' => []];
            $timeSlots = [];

            foreach ($dayData['time'] ?? [] as $slot) {
                $slotName = $slot['meal_time'] ?? 'Meal';
                $mealsList = [];

                foreach ($slot['meals'] ?? [] as $mealGroup) {
                    $options = [];
                    foreach ($mealGroup['options'] ?? [] as $option) {
                        $mealId = (int) ($option['meal_id'] ?? 0);
                        // Validate ID existence against catalog to prevent hallucinated IDs
                        if (! in_array($mealId, $validMealIds)) {
                            $mealId = $fallbackMealId;
                        }

                        $quantity = max(10, min(500, (int) ($option['quantity'] ?? 150)));

                        $options[] = [
                            'meal_id' => $mealId,
                            'quantity' => $quantity,
                        ];
                    }

                    if (! empty($options)) {
                        $mealsList[] = ['options' => $options];
                    }
                }

                if (! empty($mealsList)) {
                    $timeSlots[] = [
                        'meal_time' => $slotName,
                        'meals' => $mealsList,
                    ];
                }
            }

            $formattedDays[] = [
                'day' => $dayName,
                'time' => $timeSlots,
            ];
        }

        $targets = [
            'calories' => round((float) ($mealPlanData['targets']['calories'] ?? 2000), 1),
            'proteins' => round((float) ($mealPlanData['targets']['proteins'] ?? 150), 1),
            'carbs' => round((float) ($mealPlanData['targets']['carbs'] ?? 200), 1),
            'fats' => round((float) ($mealPlanData['targets']['fats'] ?? 60), 1),
        ];

        $mealPlan = new MealPlan();
        $mealPlan->name = 'AI Plan - ' . $user->name . ' (' . now()->format('d M Y - H.i.s') . ')';
        $mealPlan->targets = $targets;
        $mealPlan->days = $formattedDays;
        $mealPlan->save();

        $this->assignmentService->assignPlan($user, $mealPlan->id, 'meal');

        return $mealPlan;
    }

    /**
     * Parse, sanitize, and persist the AI-generated Workout Plan.
     */
    protected function createWorkoutPlan(User $user, array $workoutPlanData, PlanGenerationContext $context): WorkoutPlan
    {
        $validWorkoutIds = $context->workouts->pluck('id')->all();
        $fallbackWorkoutId = $validWorkoutIds[0] ?? null;

        $validPresetIds = $context->repsPresets->pluck('id')->all();
        $fallbackPresetId = $validPresetIds[0] ?? 1;

        $rawDays = $workoutPlanData['days'] ?? [];
        $expectedDays = ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'];
        $formattedDays = [];

        $daysByName = collect($rawDays)->keyBy(fn ($d) => trim($d['day'] ?? ''));

        foreach ($expectedDays as $dayName) {
            $dayData = $daysByName->get($dayName) ?? ['workouts' => []];
            $workoutsList = [];

            foreach ($dayData['workouts'] ?? [] as $workoutItem) {
                $workoutId = (int) ($workoutItem['workout_id'] ?? 0);
                if (! in_array($workoutId, $validWorkoutIds)) {
                    $workoutId = $fallbackWorkoutId;
                }

                $presetId = (int) ($workoutItem['reps_preset_id'] ?? $fallbackPresetId);
                if (! in_array($presetId, $validPresetIds)) {
                    $presetId = $fallbackPresetId;
                }

                $workoutsList[] = [
                    'workout_id' => $workoutId,
                    'reps' => $presetId,
                ];
            }

            $formattedDays[] = [
                'day' => $dayName,
                'workouts' => $workoutsList,
            ];
        }

        $workoutPlan = new WorkoutPlan();
        $workoutPlan->name = 'AI Workout Plan - ' . $user->name . ' (' . now()->format('d M Y - H.i.s') . ')';
        $workoutPlan->days = $formattedDays;
        $workoutPlan->save();

        $this->assignmentService->assignPlan($user, $workoutPlan->id, 'workout');

        return $workoutPlan;
    }
}

<?php

namespace App\Services;

use App\Contracts\PlanGeneratorInterface;
use App\Models\User;
use App\Services\PlanGeneration\AiPlanGenerator;
use App\Services\PlanGeneration\RuleBasedPlanGenerator;
use Illuminate\Support\Facades\Log;
use Throwable;

class PlanGenerationService implements PlanGeneratorInterface
{
    public function __construct(
        protected RuleBasedPlanGenerator $ruleBasedGenerator,
        protected AiPlanGenerator $aiGenerator,
    ) {
    }

    /**
     * Generate both Workout Plan and Meal Plan for the given user,
     * switching between AI Agent and Rule-based algorithm based on configuration.
     *
     * @return array{workout_plan: \App\Models\WorkoutPlan, meal_plan: \App\Models\MealPlan, summary: array}
     */
    public function generateForUser(User $user): array
    {
        $driver = config('plan_generation.driver', 'ai');

        if ($driver === 'ai') {
            try {
                return $this->aiGenerator->generateForUser($user);
            } catch (Throwable $e) {
                Log::warning('AI Plan Generation encountered an error: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'exception' => get_class($e),
                ]);

                if (config('plan_generation.fallback_to_rule_based', true)) {
                    Log::info('Falling back automatically to Rule-Based Plan Generator for user', [
                        'user_id' => $user->id,
                    ]);

                    $result = $this->ruleBasedGenerator->generateForUser($user);
                    $result['summary']['engine'] = 'rule_based_fallback';
                    $result['summary']['fallback_reason'] = $e->getMessage();

                    return $result;
                }

                throw $e;
            }
        }

        $result = $this->ruleBasedGenerator->generateForUser($user);
        $result['summary']['engine'] = 'rule_based';

        return $result;
    }

    /**
     * Directly invoke the rule-based algorithm (for manual or legacy invocations).
     */
    public function generateRuleBased(User $user): array
    {
        return $this->ruleBasedGenerator->generateForUser($user);
    }

    /**
     * Directly invoke the AI Agent generator.
     */
    public function generateAi(User $user): array
    {
        return $this->aiGenerator->generateForUser($user);
    }
}

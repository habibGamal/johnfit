<?php

namespace App\Services;

use App\Contracts\PlanGenerationEligibilityStrategyInterface;
use App\Contracts\PlanGeneratorInterface;
use App\Exceptions\PlanGenerationNotAllowedException;
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
        protected PlanGenerationEligibilityStrategyInterface $eligibilityStrategy,
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
            // Enforce Strategy Pattern: Validate eligibility before AI generation
            $this->eligibilityStrategy->validate($user);

            try {
                return $this->aiGenerator->generateForUser($user);
            } catch (PlanGenerationNotAllowedException $e) {
                // Policy limit reached: never fallback to rule-based on quota/eligibility restriction
                throw $e;
            } catch (Throwable $e) {
                Log::warning('AI Plan Generation encountered an error: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
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
     * Check if the user is eligible for AI plan generation under the active strategy.
     */
    public function isUserEligibleForAiPlan(User $user): bool
    {
        return $this->eligibilityStrategy->isEligible($user);
    }

    /**
     * Get detailed AI plan eligibility info for the user.
     *
     * @return array{can_generate: bool, remaining: ?int, reason: ?string}
     */
    public function getAiPlanEligibility(User $user): array
    {
        return [
            'can_generate' => $this->eligibilityStrategy->isEligible($user),
            'remaining' => $this->eligibilityStrategy->getRemainingGenerations($user),
            'reason' => $this->eligibilityStrategy->getIneligibilityReason($user),
        ];
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

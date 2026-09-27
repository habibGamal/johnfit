<?php

namespace App\Services\PlanGeneration\Strategies;

use App\Contracts\PlanGenerationEligibilityStrategyInterface;
use App\Exceptions\PlanGenerationNotAllowedException;
use App\Models\AiPlanGeneration;
use App\Models\User;

class OncePerUserStrategy implements PlanGenerationEligibilityStrategyInterface
{
    /**
     * Maximum allowed AI plan generations per user under this strategy.
     */
    protected const MAX_GENERATIONS = 1;

    /**
     * Determine whether the user is eligible to generate an AI plan.
     */
    public function isEligible(User $user): bool
    {
        return $this->getUsageCount($user) < self::MAX_GENERATIONS;
    }

    /**
     * Validate whether the user can generate an AI plan.
     * Throws an exception if not eligible.
     *
     * @throws PlanGenerationNotAllowedException
     */
    public function validate(User $user): void
    {
        if (!$this->isEligible($user)) {
            throw new PlanGenerationNotAllowedException($this->getIneligibilityReason($user));
        }
    }

    /**
     * Record usage after a successful AI plan generation.
     *
     * @param array<string, mixed> $context
     */
    public function recordUsage(User $user, array $context = []): void
    {
        AiPlanGeneration::create([
            'user_id' => $user->id,
            'workout_plan_id' => $context['workout_plan_id'] ?? null,
            'meal_plan_id' => $context['meal_plan_id'] ?? null,
            'driver' => $context['driver'] ?? 'ai',
            'metadata' => [
                'summary' => $context['summary'] ?? null,
                'model' => $context['model'] ?? null,
                'provider' => $context['provider'] ?? null,
                'strategy' => 'once_per_user',
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get the remaining number of generations allowed for the user.
     */
    public function getRemainingGenerations(User $user): ?int
    {
        return max(0, self::MAX_GENERATIONS - $this->getUsageCount($user));
    }

    /**
     * Get a human-readable explanation if the user is ineligible.
     */
    public function getIneligibilityReason(User $user): ?string
    {
        if ($this->isEligible($user)) {
            return null;
        }

        return 'The AI Plan Generator can only be used once per account. You have already generated your personalized plan.';
    }

    /**
     * Get the count of AI plan generations performed by the user.
     */
    protected function getUsageCount(User $user): int
    {
        return $user->aiPlanGenerations()->count();
    }
}

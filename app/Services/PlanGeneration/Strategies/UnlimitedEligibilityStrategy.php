<?php

namespace App\Services\PlanGeneration\Strategies;

use App\Contracts\PlanGenerationEligibilityStrategyInterface;
use App\Models\AiPlanGeneration;
use App\Models\User;

class UnlimitedEligibilityStrategy implements PlanGenerationEligibilityStrategyInterface
{
    /**
     * Determine whether the user is eligible to generate an AI plan.
     */
    public function isEligible(User $user): bool
    {
        return true;
    }

    /**
     * Validate whether the user can generate an AI plan.
     */
    public function validate(User $user): void
    {
        // Always allowed
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
                'strategy' => 'unlimited',
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get the remaining number of generations allowed for the user.
     * Returns null indicating unlimited access.
     */
    public function getRemainingGenerations(User $user): ?int
    {
        return null;
    }

    /**
     * Get a human-readable explanation if the user is ineligible.
     */
    public function getIneligibilityReason(User $user): ?string
    {
        return null;
    }
}

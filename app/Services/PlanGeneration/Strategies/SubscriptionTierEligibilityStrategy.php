<?php

namespace App\Services\PlanGeneration\Strategies;

use App\Contracts\PlanGenerationEligibilityStrategyInterface;
use App\Exceptions\PlanGenerationNotAllowedException;
use App\Models\AiPlanGeneration;
use App\Models\User;

class SubscriptionTierEligibilityStrategy implements PlanGenerationEligibilityStrategyInterface
{
    /**
     * Determine whether the user is eligible to generate an AI plan based on subscription tier.
     */
    public function isEligible(User $user): bool
    {
        $allowed = $this->getAllowedGenerationsForUser($user);

        if ($allowed === null) {
            return true; // Unlimited
        }

        return $this->getCurrentPeriodUsageCount($user) < $allowed;
    }

    /**
     * Validate whether the user can generate an AI plan.
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
                'strategy' => 'subscription_tier',
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get the remaining number of generations allowed for the user.
     */
    public function getRemainingGenerations(User $user): ?int
    {
        $allowed = $this->getAllowedGenerationsForUser($user);

        if ($allowed === null) {
            return null; // Unlimited
        }

        return max(0, $allowed - $this->getCurrentPeriodUsageCount($user));
    }

    /**
     * Get a human-readable explanation if the user is ineligible.
     */
    public function getIneligibilityReason(User $user): ?string
    {
        if ($this->isEligible($user)) {
            return null;
        }

        $subscription = $user->activeSubscription();
        if (!$subscription) {
            return 'You have used your free AI plan generation. Please subscribe to an active plan to generate more.';
        }

        return 'You have reached the AI Plan generation limit for your current subscription period.';
    }

    /**
     * Determine how many generations the user's tier permits.
     * null = unlimited.
     */
    protected function getAllowedGenerationsForUser(User $user): ?int
    {
        $subscription = $user->activeSubscription();

        if (!$subscription) {
            return 1; // Free users get 1 generation
        }

        // Future extension: inspect subscription plan slug/tier
        // e.g. 'pro' => 3, 'vip' => null (unlimited)
        return 3;
    }

    /**
     * Get the count of AI plan generations in the current active period.
     */
    protected function getCurrentPeriodUsageCount(User $user): int
    {
        $subscription = $user->activeSubscription();

        if (!$subscription) {
            return $user->aiPlanGenerations()->count();
        }

        return $user->aiPlanGenerations()
            ->where('created_at', '>=', $subscription->created_at)
            ->count();
    }
}

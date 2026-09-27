<?php

namespace App\Contracts;

use App\Models\User;

interface PlanGenerationEligibilityStrategyInterface
{
    /**
     * Determine whether the user is eligible to generate an AI plan.
     */
    public function isEligible(User $user): bool;

    /**
     * Validate whether the user can generate an AI plan.
     * Throws an exception if not eligible.
     *
     * @throws \App\Exceptions\PlanGenerationNotAllowedException
     */
    public function validate(User $user): void;

    /**
     * Record usage after a successful AI plan generation.
     *
     * @param array<string, mixed> $context
     */
    public function recordUsage(User $user, array $context = []): void;

    /**
     * Get the remaining number of generations allowed for the user.
     * Returns null if unlimited.
     */
    public function getRemainingGenerations(User $user): ?int;

    /**
     * Get a human-readable explanation if the user is ineligible.
     */
    public function getIneligibilityReason(User $user): ?string;
}

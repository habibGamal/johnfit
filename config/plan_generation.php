<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Plan Generation Driver
    |--------------------------------------------------------------------------
    |
    | Supported: 'ai', 'rule_based'
    | Default: 'ai' (with automatic fallback to 'rule_based' if AI fails)
    |
    */
    'driver' => env('PLAN_GENERATION_DRIVER', 'ai'),

    /*
    |--------------------------------------------------------------------------
    | AI Plan Generator Settings
    |--------------------------------------------------------------------------
    */
    'ai' => [
        'provider' => env('AI_PLAN_PROVIDER', 'openai'),
        'model' => env('AI_PLAN_MODEL', 'gpt-4o-mini'),
        'temperature' => (float) env('AI_PLAN_TEMPERATURE', 0.4),
        'max_tokens' => (int) env('AI_PLAN_MAX_TOKENS', 4000),
        'timeout' => (int) env('AI_PLAN_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback on AI Failure
    |--------------------------------------------------------------------------
    |
    | When true, if the AI generator throws an exception or fails schema
    | validation, the system will seamlessly fall back to the deterministic
    | rule-based generator and log a warning.
    |
    */
    'fallback_to_rule_based' => env('PLAN_GENERATION_FALLBACK_ON_ERROR', true),

    /*
    |--------------------------------------------------------------------------
    | AI Plan Generation Eligibility Strategy
    |--------------------------------------------------------------------------
    |
    | Controls which strategy governs access / quotas for AI plan generation.
    | - 'once_per_user': User can only generate an AI plan once.
    | - 'unlimited': Unlimited generations (useful for local dev/admin).
    | - 'subscription_tier': Tier-based limits based on active subscriptions.
    |
    */
    'eligibility_strategy' => env('AI_PLAN_ELIGIBILITY_STRATEGY', 'once_per_user'),

    'eligibility_strategies' => [
        'once_per_user' => \App\Services\PlanGeneration\Strategies\OncePerUserStrategy::class,
        'unlimited' => \App\Services\PlanGeneration\Strategies\UnlimitedEligibilityStrategy::class,
        'subscription_tier' => \App\Services\PlanGeneration\Strategies\SubscriptionTierEligibilityStrategy::class,
    ],
];

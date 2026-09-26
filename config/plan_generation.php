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
];

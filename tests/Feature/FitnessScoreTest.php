<?php

use App\Models\User;
use App\Services\FitnessScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('fitness score service calculates score summary successfully', function () {
    $user = User::factory()->create(['assessment_completed_at' => now()]);
    $service = app(FitnessScoreService::class);

    $summary = $service->getScoreSummary($user);

    expect($summary)->toBeArray()
        ->toHaveKeys(['total_score', 'level', 'trend', 'period', 'components', 'updated_at'])
        ->and($summary['components'])->toHaveKeys(['workout', 'meal', 'inbody']);
});

test('fitness score history endpoint returns success response', function () {
    $user = User::factory()->create(['assessment_completed_at' => now()]);
    $service = app(FitnessScoreService::class);
    $service->calculateScore($user);

    $response = $this->actingAs($user)->getJson('/fitness-score/history');

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);
});

test('dashboard loads successfully with fitness score summary and history', function () {
    $user = User::factory()->create([
        'assessment_completed_at' => now(),
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
});

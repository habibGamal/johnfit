<?php

use App\Models\InBodyLog;
use App\Models\User;
use App\Services\InBodyAnalysisService;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('InBody Log CRUD', function () {
    it('displays the inbody index page for authenticated users', function () {
        $response = $this->actingAs($this->user)
            ->get(route('inbody.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('InBody/Index'));
    });

    it('redirects unauthenticated users to login', function () {
        $response = $this->get(route('inbody.index'));

        $response->assertRedirect(route('login'));
    });

    it('can store a new inbody log with valid data', function () {
        $data = [
            'weight' => 75.5,
            'smm' => 35.2,
            'pbf' => 18.5,
            'bmi' => 23.4,
            'bmr' => 1750,
            'measured_at' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
            ->post(route('inbody.store'), $data);

        $response->assertRedirect(route('inbody.index'));

        $this->assertDatabaseHas('inbody_logs', [
            'user_id' => $this->user->id,
            'weight' => 75.5,
            'smm' => 35.2,
            'pbf' => 18.5,
        ]);
    });

    it('validates required fields when storing', function () {
        $response = $this->actingAs($this->user)
            ->post(route('inbody.store'), []);

        $response->assertSessionHasErrors([
            'weight',
            'smm',
            'pbf',
            'bmi',
            'bmr',
            'measured_at',
        ]);
    });

    it('rejects unrealistic weight values', function () {
        $data = [
            'weight' => 500, // Unrealistic
            'smm' => 35.2,
            'pbf' => 18.5,
            'bmi' => 23.4,
            'bmr' => 1750,
            'measured_at' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
            ->post(route('inbody.store'), $data);

        $response->assertSessionHasErrors('weight');
    });

    it('rejects future dates', function () {
        $data = [
            'weight' => 75.5,
            'smm' => 35.2,
            'pbf' => 18.5,
            'bmi' => 23.4,
            'bmr' => 1750,
            'measured_at' => now()->addDays(5)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
            ->post(route('inbody.store'), $data);

        $response->assertSessionHasErrors('measured_at');
    });

    it('can delete own inbody log', function () {
        $log = InBodyLog::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('inbody.destroy', $log));

        $response->assertRedirect(route('inbody.index'));
        $this->assertDatabaseMissing('inbody_logs', ['id' => $log->id]);
    });

    it('cannot delete another users inbody log', function () {
        $otherUser = User::factory()->create();
        $log = InBodyLog::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('inbody.destroy', $log));

        $response->assertForbidden();
    });
});

describe('InBody Analysis Service', function () {
    it('returns empty analysis for users with no logs', function () {
        $service = new InBodyAnalysisService;
        $analysis = $service->getAnalysis($this->user);

        expect($analysis['latest'])->toBeNull();
        expect($analysis['delta'])->toBeNull();
        expect($analysis['history'])->toBeEmpty();
    });

    it('calculates delta between two logs', function () {
        $previousLog = InBodyLog::factory()->create([
            'user_id' => $this->user->id,
            'weight' => 80.0,
            'smm' => 30.0,
            'pbf' => 25.0,
            'bmi' => 25.0,
            'bmr' => 1700,
            'measured_at' => now()->subDays(30),
        ]);

        $currentLog = InBodyLog::factory()->create([
            'user_id' => $this->user->id,
            'weight' => 78.0,
            'smm' => 32.0,
            'pbf' => 22.0,
            'bmi' => 24.5,
            'bmr' => 1750,
            'measured_at' => now(),
        ]);

        $service = new InBodyAnalysisService;
        $delta = $service->calculateDelta($currentLog, $previousLog);

        expect($delta['weight']['absolute'])->toBe(-2.0);
        expect($delta['smm']['absolute'])->toBe(2.0);
        expect($delta['pbf']['absolute'])->toBe(-3.0);
    });

    it('classifies recomposition correctly', function () {
        $previousLog = InBodyLog::factory()->create([
            'user_id' => $this->user->id,
            'weight' => 80.0,
            'smm' => 30.0,
            'pbf' => 25.0,
            'bmi' => 25.0,
            'bmr' => 1700,
            'measured_at' => now()->subDays(30),
        ]);

        $currentLog = InBodyLog::factory()->create([
            'user_id' => $this->user->id,
            'weight' => 79.0,
            'smm' => 31.0, // Gained 1kg muscle
            'pbf' => 22.0, // Lost 3% body fat
            'bmi' => 24.8,
            'bmr' => 1720,
            'measured_at' => now(),
        ]);

        $service = new InBodyAnalysisService;
        $analysis = $service->analyzeBodyComposition($currentLog, $previousLog);

        expect($analysis['classification'])->toBe('recomposition');
        expect($analysis['status'])->toBe('excellent');
        expect($analysis['indicators']['muscle_trend'])->toBe('gaining');
        expect($analysis['indicators']['fat_trend'])->toBe('losing');
    });

    it('classifies cutting correctly', function () {
        $previousLog = InBodyLog::factory()->create([
            'user_id' => $this->user->id,
            'weight' => 85.0,
            'smm' => 35.0,
            'pbf' => 22.0,
            'bmi' => 26.0,
            'bmr' => 1800,
            'measured_at' => now()->subDays(30),
        ]);

        $currentLog = InBodyLog::factory()->create([
            'user_id' => $this->user->id,
            'weight' => 82.0,
            'smm' => 35.0, // Maintained muscle
            'pbf' => 19.0, // Lost 3% body fat
            'bmi' => 25.0,
            'bmr' => 1780,
            'measured_at' => now(),
        ]);

        $service = new InBodyAnalysisService;
        $analysis = $service->analyzeBodyComposition($currentLog, $previousLog);

        expect($analysis['classification'])->toBe('cutting');
        expect($analysis['status'])->toBe('positive');
    });

    it('provides recommendations based on classification', function () {
        $previousLog = InBodyLog::factory()->create([
            'user_id' => $this->user->id,
            'weight' => 80.0,
            'smm' => 30.0,
            'pbf' => 20.0,
            'bmi' => 25.0,
            'bmr' => 1700,
            'measured_at' => now()->subDays(30),
        ]);

        $currentLog = InBodyLog::factory()->create([
            'user_id' => $this->user->id,
            'weight' => 82.0,
            'smm' => 29.0, // Lost muscle
            'pbf' => 22.0, // Gained fat
            'bmi' => 25.5,
            'bmr' => 1680,
            'measured_at' => now(),
        ]);

        $service = new InBodyAnalysisService;
        $analysis = $service->analyzeBodyComposition($currentLog, $previousLog);

        expect($analysis['recommendations'])->toBeArray();
        expect($analysis['recommendations'])->not->toBeEmpty();
    });

    it('calculates statistics from log history', function () {
        // Create multiple logs
        InBodyLog::factory()->create([
            'user_id' => $this->user->id,
            'weight' => 75.0,
            'smm' => 30.0,
            'pbf' => 20.0,
            'bmi' => 24.0,
            'bmr' => 1650,
            'measured_at' => now()->subDays(60),
        ]);

        InBodyLog::factory()->create([
            'user_id' => $this->user->id,
            'weight' => 77.0,
            'smm' => 31.0,
            'pbf' => 19.0,
            'bmi' => 24.5,
            'bmr' => 1680,
            'measured_at' => now()->subDays(30),
        ]);

        InBodyLog::factory()->create([
            'user_id' => $this->user->id,
            'weight' => 76.0,
            'smm' => 32.0,
            'pbf' => 18.0,
            'bmi' => 24.2,
            'bmr' => 1700,
            'measured_at' => now(),
        ]);

        $service = new InBodyAnalysisService;
        $analysis = $service->getAnalysis($this->user);

        expect($analysis['statistics'])->not->toBeNull();
        expect($analysis['statistics']['weight']['min'])->toBe(75.0);
        expect($analysis['statistics']['weight']['max'])->toBe(77.0);
        expect($analysis['history'])->toHaveCount(3);
    });
});

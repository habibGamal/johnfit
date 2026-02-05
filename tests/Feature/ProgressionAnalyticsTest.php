<?php

use App\Models\InBodyLog;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutPlan;
use App\Models\WorkoutSetCompletion;
use App\Services\ProgressionService;
use Carbon\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = new ProgressionService;
});

describe('One Rep Max Calculations', function () {
    it('calculates 1RM correctly using Epley formula', function () {
        // Epley Formula: Weight × (1 + Reps/30)

        // Test case 1: 100kg × 10 reps
        // Expected: 100 × (1 + 10/30) = 100 × 1.333... = 133.33
        $result = $this->service->calculateOneRepMax(100, 10);
        expect($result)->toBe(133.33);

        // Test case 2: 80kg × 5 reps
        // Expected: 80 × (1 + 5/30) = 80 × 1.166... = 93.33
        $result = $this->service->calculateOneRepMax(80, 5);
        expect($result)->toBe(93.33);

        // Test case 3: 60kg × 12 reps
        // Expected: 60 × (1 + 12/30) = 60 × 1.4 = 84.00
        $result = $this->service->calculateOneRepMax(60, 12);
        expect($result)->toBe(84.0);

        // Test case 4: 150kg × 1 rep (single rep equals actual weight)
        $result = $this->service->calculateOneRepMax(150, 1);
        expect($result)->toBe(150.0);
    });

    it('returns zero for invalid inputs', function () {
        expect($this->service->calculateOneRepMax(0, 10))->toBe(0.0);
        expect($this->service->calculateOneRepMax(100, 0))->toBe(0.0);
        expect($this->service->calculateOneRepMax(-50, 10))->toBe(0.0);
        expect($this->service->calculateOneRepMax(100, -5))->toBe(0.0);
    });

    it('handles edge cases correctly', function () {
        // Very heavy weight, low reps
        $result = $this->service->calculateOneRepMax(200, 3);
        expect($result)->toBe(220.0); // 200 × (1 + 3/30) = 200 × 1.1 = 220

        // Light weight, high reps
        $result = $this->service->calculateOneRepMax(30, 20);
        expect($result)->toBe(50.0); // 30 × (1 + 20/30) = 30 × 1.666... = 50
    });
});

describe('Volume Calculations', function () {
    it('calculates volume correctly', function () {
        // Volume = Weight × Reps
        expect($this->service->calculateVolume(100, 10))->toBe(1000.0);
        expect($this->service->calculateVolume(75.5, 8))->toBe(604.0);
        expect($this->service->calculateVolume(0, 10))->toBe(0.0);
    });
});

describe('Personal Best Detection', function () {
    beforeEach(function () {
        $this->workout = Workout::factory()->chest()->create();
        $this->plan = WorkoutPlan::factory()->create();
    });

    it('detects a strength personal best', function () {
        $yesterday = Carbon::yesterday()->toDateString();
        $today = Carbon::today()->toDateString();

        // Previous session: 100kg × 5 = 116.67 1RM
        WorkoutSetCompletion::factory()
            ->for($this->user)
            ->for($this->workout)
            ->withPerformance(100, 5)
            ->onDate($yesterday)
            ->create(['workout_plan_id' => $this->plan->id, 'day' => 'Day 1']);

        // Today's session: 110kg × 5 = 128.33 1RM (new PB)
        WorkoutSetCompletion::factory()
            ->for($this->user)
            ->for($this->workout)
            ->withPerformance(110, 5)
            ->onDate($today)
            ->create(['workout_plan_id' => $this->plan->id, 'day' => 'Day 1']);

        $pbs = $this->service->detectPersonalBests($this->user, $this->workout->id);

        expect($pbs['has_strength_pb'])->toBeTrue();
        expect($pbs['strength_pb_details'])->not->toBeNull();
        expect($pbs['strength_pb_details']['new_record'])->toBeGreaterThan(
            $pbs['strength_pb_details']['previous_record']
        );
    });

    it('detects a volume personal best', function () {
        $yesterday = Carbon::yesterday()->toDateString();
        $today = Carbon::today()->toDateString();

        // Previous session: 3 sets of 100kg × 10 = 3000kg total volume
        for ($i = 1; $i <= 3; $i++) {
            WorkoutSetCompletion::factory()
                ->for($this->user)
                ->for($this->workout)
                ->withPerformance(100, 10)
                ->onDate($yesterday)
                ->create([
                    'workout_plan_id' => $this->plan->id,
                    'day' => 'Day 1',
                    'set_number' => $i,
                ]);
        }

        // Today's session: 4 sets of 100kg × 10 = 4000kg total volume (new PB)
        for ($i = 1; $i <= 4; $i++) {
            WorkoutSetCompletion::factory()
                ->for($this->user)
                ->for($this->workout)
                ->withPerformance(100, 10)
                ->onDate($today)
                ->create([
                    'workout_plan_id' => $this->plan->id,
                    'day' => 'Day 1',
                    'set_number' => $i,
                ]);
        }

        $pbs = $this->service->detectPersonalBests($this->user, $this->workout->id);

        expect($pbs['has_volume_pb'])->toBeTrue();
        expect($pbs['volume_pb_details'])->not->toBeNull();
        expect($pbs['volume_pb_details']['new_record'])->toBe(4000.0);
        expect($pbs['volume_pb_details']['previous_record'])->toBe(3000.0);
    });

    it('does not flag PB when performance is lower', function () {
        $yesterday = Carbon::yesterday()->toDateString();
        $today = Carbon::today()->toDateString();

        // Previous session: Better performance
        WorkoutSetCompletion::factory()
            ->for($this->user)
            ->for($this->workout)
            ->withPerformance(120, 8)
            ->onDate($yesterday)
            ->create(['workout_plan_id' => $this->plan->id, 'day' => 'Day 1']);

        // Today's session: Worse performance
        WorkoutSetCompletion::factory()
            ->for($this->user)
            ->for($this->workout)
            ->withPerformance(100, 6)
            ->onDate($today)
            ->create(['workout_plan_id' => $this->plan->id, 'day' => 'Day 1']);

        $pbs = $this->service->detectPersonalBests($this->user, $this->workout->id);

        expect($pbs['has_strength_pb'])->toBeFalse();
        expect($pbs['has_volume_pb'])->toBeFalse();
    });

    it('returns no PBs when there is no previous data', function () {
        $today = Carbon::today()->toDateString();

        // Only today's session exists
        WorkoutSetCompletion::factory()
            ->for($this->user)
            ->for($this->workout)
            ->withPerformance(100, 10)
            ->onDate($today)
            ->create(['workout_plan_id' => $this->plan->id, 'day' => 'Day 1']);

        $pbs = $this->service->detectPersonalBests($this->user, $this->workout->id);

        // First session can't be a PB (no baseline to compare)
        expect($pbs['has_strength_pb'])->toBeFalse();
        expect($pbs['has_volume_pb'])->toBeFalse();
    });
});

describe('Volume Per Workout Tracking', function () {
    beforeEach(function () {
        $this->workout = Workout::factory()->create();
        $this->plan = WorkoutPlan::factory()->create();
    });

    it('aggregates volume by session date', function () {
        $date1 = Carbon::today()->subDays(2)->toDateString();
        $date2 = Carbon::today()->subDays(1)->toDateString();

        // Session 1: 2 sets
        WorkoutSetCompletion::factory()
            ->for($this->user)
            ->for($this->workout)
            ->withPerformance(100, 10) // 1000kg
            ->onDate($date1)
            ->create(['workout_plan_id' => $this->plan->id, 'day' => 'Day 1', 'set_number' => 1]);

        WorkoutSetCompletion::factory()
            ->for($this->user)
            ->for($this->workout)
            ->withPerformance(100, 8) // 800kg
            ->onDate($date1)
            ->create(['workout_plan_id' => $this->plan->id, 'day' => 'Day 1', 'set_number' => 2]);

        // Session 2: 1 set
        WorkoutSetCompletion::factory()
            ->for($this->user)
            ->for($this->workout)
            ->withPerformance(120, 5) // 600kg
            ->onDate($date2)
            ->create(['workout_plan_id' => $this->plan->id, 'day' => 'Day 1', 'set_number' => 1]);

        $volumeData = $this->service->getVolumePerWorkout($this->user, $this->workout->id);

        expect($volumeData)->toHaveCount(2);
        expect((float) $volumeData[0]['total_volume'])->toBe(1800.0); // 1000 + 800
        expect((float) $volumeData[1]['total_volume'])->toBe(600.0);
    });
});

describe('1RM Trend Tracking', function () {
    beforeEach(function () {
        $this->workout = Workout::factory()->create();
        $this->plan = WorkoutPlan::factory()->create();
    });

    it('tracks estimated 1RM over time', function () {
        $dates = [
            Carbon::today()->subDays(3)->toDateString(),
            Carbon::today()->subDays(2)->toDateString(),
            Carbon::today()->subDays(1)->toDateString(),
        ];

        // Progressive overload pattern
        $performances = [
            ['weight' => 80, 'reps' => 10],  // 1RM: 106.67
            ['weight' => 85, 'reps' => 8],   // 1RM: 107.67
            ['weight' => 90, 'reps' => 6],   // 1RM: 108.00
        ];

        foreach ($dates as $i => $date) {
            WorkoutSetCompletion::factory()
                ->for($this->user)
                ->for($this->workout)
                ->withPerformance($performances[$i]['weight'], $performances[$i]['reps'])
                ->onDate($date)
                ->create(['workout_plan_id' => $this->plan->id, 'day' => 'Day 1', 'set_number' => 1]);
        }

        $trend = $this->service->getOneRepMaxTrend($this->user, $this->workout->id);

        expect($trend)->toHaveCount(3);

        // Verify progression
        $firstSession = $trend->first();
        $lastSession = $trend->last();

        expect($lastSession['estimated_1rm'])->toBeGreaterThan($firstSession['estimated_1rm']);
    });
});

describe('Muscle Group Volume Distribution', function () {
    it('calculates volume per muscle group', function () {
        $chestWorkout = Workout::factory()->create([
            'muscles' => 'Chest,Triceps',
        ]);
        $backWorkout = Workout::factory()->create([
            'muscles' => 'Lat,Biceps',
        ]);
        $plan = WorkoutPlan::factory()->create();
        $today = Carbon::today()->toDateString();

        // Chest workout: 100kg × 10 × 3 sets = 3000kg (split between Chest and Triceps)
        for ($i = 1; $i <= 3; $i++) {
            WorkoutSetCompletion::factory()
                ->for($this->user)
                ->for($chestWorkout)
                ->withPerformance(100, 10)
                ->onDate($today)
                ->create(['workout_plan_id' => $plan->id, 'day' => 'Day 1', 'set_number' => $i]);
        }

        // Back workout: 80kg × 8 × 2 sets = 1280kg
        for ($i = 1; $i <= 2; $i++) {
            WorkoutSetCompletion::factory()
                ->for($this->user)
                ->for($backWorkout)
                ->withPerformance(80, 8)
                ->onDate($today)
                ->create(['workout_plan_id' => $plan->id, 'day' => 'Day 2', 'set_number' => $i]);
        }

        $distribution = $this->service->getVolumePerMuscleGroup($this->user);

        expect($distribution)->not->toBeEmpty();

        // Both Chest and Triceps should have 3000kg each (shared from same workout)
        $chestVolume = $distribution->firstWhere('muscle', 'Chest');
        expect($chestVolume)->not->toBeNull();
        expect($chestVolume['volume'])->toBe(3000.0);
    });
});

describe('Relative Strength Tracking', function () {
    beforeEach(function () {
        $this->workout = Workout::factory()->create();
        $this->plan = WorkoutPlan::factory()->create();
    });

    it('correlates strength with body weight', function () {
        $date1 = Carbon::today()->subDays(30)->toDateString();
        $date2 = Carbon::today()->toDateString();

        // InBody logs
        InBodyLog::factory()->create([
            'user_id' => $this->user->id,
            'weight' => 80,
            'measured_at' => Carbon::today()->subDays(30),
        ]);

        InBodyLog::factory()->create([
            'user_id' => $this->user->id,
            'weight' => 78,
            'measured_at' => Carbon::today(),
        ]);

        // Workout sessions
        WorkoutSetCompletion::factory()
            ->for($this->user)
            ->for($this->workout)
            ->withPerformance(100, 5) // 1RM: 116.67
            ->onDate($date1)
            ->create(['workout_plan_id' => $this->plan->id, 'day' => 'Day 1', 'set_number' => 1]);

        WorkoutSetCompletion::factory()
            ->for($this->user)
            ->for($this->workout)
            ->withPerformance(105, 5) // 1RM: 122.50
            ->onDate($date2)
            ->create(['workout_plan_id' => $this->plan->id, 'day' => 'Day 1', 'set_number' => 1]);

        $trend = $this->service->getRelativeStrengthTrend($this->user, $this->workout->id);

        expect($trend)->toHaveCount(2);

        // Check relative strength calculation
        $latestSession = $trend->last();
        expect($latestSession['body_weight'])->toBe(78.0);
        expect($latestSession['relative_strength'])->not->toBeNull();

        // Relative strength should be roughly 1RM / body_weight
        // 122.50 / 78 ≈ 1.57
        expect($latestSession['relative_strength'])->toBeGreaterThan(1.5);
    });
});

describe('Analytics Controller', function () {
    it('displays the analytics dashboard', function () {
        $response = $this->actingAs($this->user)
            ->get(route('analytics.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Analytics/ProgressionDashboard'));
    });

    it('requires authentication', function () {
        $response = $this->get(route('analytics.index'));

        $response->assertRedirect(route('login'));
    });

    it('returns workout analytics data', function () {
        $workout = Workout::factory()->create();
        $plan = WorkoutPlan::factory()->create();

        // Create some workout data
        WorkoutSetCompletion::factory()
            ->for($this->user)
            ->for($workout)
            ->withPerformance(100, 10)
            ->today()
            ->create(['workout_plan_id' => $plan->id, 'day' => 'Day 1']);

        $response = $this->actingAs($this->user)
            ->get(route('analytics.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('workouts')
            ->has('muscleHeatmap')
            ->has('intensityDelta')
            ->has('consistencyScore')
        );
    });

    it('filters by workout when specified', function () {
        $workout = Workout::factory()->create();
        $plan = WorkoutPlan::factory()->create();

        WorkoutSetCompletion::factory()
            ->for($this->user)
            ->for($workout)
            ->withPerformance(100, 10)
            ->today()
            ->create(['workout_plan_id' => $plan->id, 'day' => 'Day 1']);

        $response = $this->actingAs($this->user)
            ->get(route('analytics.index', ['workout_id' => $workout->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('selectedWorkoutId', $workout->id)
        );
    });
});

describe('Intensity Delta Calculation', function () {
    beforeEach(function () {
        $this->workout = Workout::factory()->create();
        $this->plan = WorkoutPlan::factory()->create();
    });

    it('calculates positive intensity delta', function () {
        // Previous 4 weeks: Lower volume
        $prevDate = Carbon::today()->subWeeks(6)->toDateString();
        WorkoutSetCompletion::factory()
            ->for($this->user)
            ->for($this->workout)
            ->withPerformance(100, 10) // 1000kg
            ->onDate($prevDate)
            ->create(['workout_plan_id' => $this->plan->id, 'day' => 'Day 1', 'set_number' => 1]);

        // Current 4 weeks: Higher volume
        $currentDate = Carbon::today()->subDays(1)->toDateString();
        WorkoutSetCompletion::factory()
            ->for($this->user)
            ->for($this->workout)
            ->withPerformance(120, 10) // 1200kg
            ->onDate($currentDate)
            ->create(['workout_plan_id' => $this->plan->id, 'day' => 'Day 1', 'set_number' => 1]);

        $analytics = $this->service->getProgressionAnalytics($this->user);

        expect($analytics['intensity_delta']['trend'])->toBe('up');
        expect($analytics['intensity_delta']['delta_percentage'])->toBeGreaterThan(0);
    });
});

describe('Consistency Score', function () {
    beforeEach(function () {
        $this->workout = Workout::factory()->create();
        $this->plan = WorkoutPlan::factory()->create();
    });

    it('calculates consistency based on session count', function () {
        // Create 8 sessions over 4 weeks (2 per week = 50% of target 4/week)
        for ($i = 0; $i < 8; $i++) {
            $date = Carbon::today()->subDays($i * 3)->toDateString();
            WorkoutSetCompletion::factory()
                ->for($this->user)
                ->for($this->workout)
                ->withPerformance(100, 10)
                ->onDate($date)
                ->create(['workout_plan_id' => $this->plan->id, 'day' => 'Day 1', 'set_number' => 1]);
        }

        $analytics = $this->service->getProgressionAnalytics($this->user);

        expect($analytics['consistency_score']['completed_sessions'])->toBe(8);
        expect($analytics['consistency_score']['percentage'])->toBeGreaterThan(0);
    });
});

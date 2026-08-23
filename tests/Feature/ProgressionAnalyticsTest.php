<?php

use App\Models\InBodyLog;
use App\Models\User;
use App\Models\UserDailyItem;
use App\Models\UserDailySchedule;
use App\Models\Workout;
use App\Services\ProgressionService;
use Carbon\Carbon;

function createWorkoutItemForTest($user, $workout, $date, array $sets)
{
    $schedule = UserDailySchedule::firstOrCreate(
        ['user_id' => $user->id, 'date' => $date],
        ['target_score' => 3, 'earned_score' => 3, 'is_locked' => false]
    );

    return UserDailyItem::create([
        'daily_schedule_id' => $schedule->id,
        'type' => 'workout',
        'item_name' => $workout->name,
        'reference_id' => $workout->id,
        'target_details' => ['muscles' => $workout->muscles, 'sets_count' => count($sets)],
        'points' => 3,
        'is_completed' => true,
        'execution_payload' => ['sets' => $sets],
    ]);
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = new ProgressionService();
});

describe('One Rep Max Calculations', function () {
    it('calculates 1RM correctly using Epley formula', function () {
        $result = $this->service->calculateOneRepMax(100, 10);
        expect($result)->toBe(133.33);

        $result = $this->service->calculateOneRepMax(80, 5);
        expect($result)->toBe(93.33);

        $result = $this->service->calculateOneRepMax(60, 12);
        expect($result)->toBe(84.0);

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
        $result = $this->service->calculateOneRepMax(200, 3);
        expect($result)->toBe(220.0);

        $result = $this->service->calculateOneRepMax(30, 20);
        expect($result)->toBe(50.0);
    });
});

describe('Volume Calculations', function () {
    it('calculates volume correctly', function () {
        expect($this->service->calculateVolume(100, 10))->toBe(1000.0);
        expect($this->service->calculateVolume(75.5, 8))->toBe(604.0);
        expect($this->service->calculateVolume(0, 10))->toBe(0.0);
    });
});

describe('Personal Best Detection', function () {
    beforeEach(function () {
        $this->workout = Workout::factory()->chest()->create();
    });

    it('detects a strength personal best', function () {
        $yesterday = Carbon::yesterday()->toDateString();
        $today = Carbon::today()->toDateString();

        // Previous session: 100kg × 5 = 116.67 1RM
        createWorkoutItemForTest($this->user, $this->workout, $yesterday, [
            ['set_number' => 1, 'reps' => 5, 'weight' => 100, 'completed' => true],
        ]);

        // Today's session: 110kg × 5 = 128.33 1RM (new PB)
        createWorkoutItemForTest($this->user, $this->workout, $today, [
            ['set_number' => 1, 'reps' => 5, 'weight' => 110, 'completed' => true],
        ]);

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
        createWorkoutItemForTest($this->user, $this->workout, $yesterday, [
            ['set_number' => 1, 'reps' => 10, 'weight' => 100, 'completed' => true],
            ['set_number' => 2, 'reps' => 10, 'weight' => 100, 'completed' => true],
            ['set_number' => 3, 'reps' => 10, 'weight' => 100, 'completed' => true],
        ]);

        // Today's session: 4 sets of 100kg × 10 = 4000kg total volume (new PB)
        createWorkoutItemForTest($this->user, $this->workout, $today, [
            ['set_number' => 1, 'reps' => 10, 'weight' => 100, 'completed' => true],
            ['set_number' => 2, 'reps' => 10, 'weight' => 100, 'completed' => true],
            ['set_number' => 3, 'reps' => 10, 'weight' => 100, 'completed' => true],
            ['set_number' => 4, 'reps' => 10, 'weight' => 100, 'completed' => true],
        ]);

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
        createWorkoutItemForTest($this->user, $this->workout, $yesterday, [
            ['set_number' => 1, 'reps' => 8, 'weight' => 120, 'completed' => true],
        ]);

        // Today's session: Worse performance
        createWorkoutItemForTest($this->user, $this->workout, $today, [
            ['set_number' => 1, 'reps' => 6, 'weight' => 100, 'completed' => true],
        ]);

        $pbs = $this->service->detectPersonalBests($this->user, $this->workout->id);

        expect($pbs['has_volume_pb'])->toBeFalse();
        expect($pbs['has_strength_pb'])->toBeFalse();
    });

    it('returns empty result when no sessions exist today', function () {
        $yesterday = Carbon::yesterday()->toDateString();

        createWorkoutItemForTest($this->user, $this->workout, $yesterday, [
            ['set_number' => 1, 'reps' => 10, 'weight' => 100, 'completed' => true],
        ]);

        $pbs = $this->service->detectPersonalBests($this->user, $this->workout->id);

        expect($pbs['has_volume_pb'])->toBeFalse();
        expect($pbs['has_strength_pb'])->toBeFalse();
        expect($pbs['volume_pb_details'])->toBeNull();
        expect($pbs['strength_pb_details'])->toBeNull();
    });
});

describe('Volume Per Workout Over Time', function () {
    beforeEach(function () {
        $this->workout = Workout::factory()->create();
    });

    it('aggregates volume correctly per session date', function () {
        $day1 = Carbon::today()->subDays(5)->toDateString();
        $day2 = Carbon::today()->subDays(2)->toDateString();

        // Day 1: 2 sets = 100*10 + 100*10 = 2000kg
        createWorkoutItemForTest($this->user, $this->workout, $day1, [
            ['set_number' => 1, 'reps' => 10, 'weight' => 100, 'completed' => true],
            ['set_number' => 2, 'reps' => 10, 'weight' => 100, 'completed' => true],
        ]);

        // Day 2: 3 sets = 110*10 + 110*10 + 110*10 = 3300kg
        createWorkoutItemForTest($this->user, $this->workout, $day2, [
            ['set_number' => 1, 'reps' => 10, 'weight' => 110, 'completed' => true],
            ['set_number' => 2, 'reps' => 10, 'weight' => 110, 'completed' => true],
            ['set_number' => 3, 'reps' => 10, 'weight' => 110, 'completed' => true],
        ]);

        $volumeTrend = $this->service->getVolumePerWorkout($this->user, $this->workout->id);

        expect($volumeTrend)->toHaveCount(2);
        expect($volumeTrend[0]->session_date)->toBe($day1);
        expect($volumeTrend[0]->total_volume)->toBe(2000.0);
        expect($volumeTrend[0]->total_sets)->toBe(2);
        expect($volumeTrend[0]->total_reps)->toBe(20);

        expect($volumeTrend[1]->session_date)->toBe($day2);
        expect($volumeTrend[1]->total_volume)->toBe(3300.0);
        expect($volumeTrend[1]->total_sets)->toBe(3);
        expect($volumeTrend[1]->total_reps)->toBe(30);
    });

    it('respects date filters', function () {
        $day1 = Carbon::today()->subDays(30)->toDateString();
        $day2 = Carbon::today()->subDays(5)->toDateString();

        createWorkoutItemForTest($this->user, $this->workout, $day1, [
            ['set_number' => 1, 'reps' => 10, 'weight' => 100, 'completed' => true],
        ]);

        createWorkoutItemForTest($this->user, $this->workout, $day2, [
            ['set_number' => 1, 'reps' => 10, 'weight' => 100, 'completed' => true],
        ]);

        $volumeTrend = $this->service->getVolumePerWorkout(
            $this->user,
            $this->workout->id,
            Carbon::today()->subDays(10),
            Carbon::today()
        );

        expect($volumeTrend)->toHaveCount(1);
        expect($volumeTrend[0]->session_date)->toBe($day2);
    });
});

describe('Volume Per Muscle Group', function () {
    it('aggregates volume by muscle group', function () {
        $chestWorkout = Workout::factory()->chest()->create();
        $legWorkout = Workout::factory()->legs()->create();
        $today = Carbon::today()->toDateString();

        createWorkoutItemForTest($this->user, $chestWorkout, $today, [
            ['set_number' => 1, 'reps' => 10, 'weight' => 100, 'completed' => true],
        ]);

        createWorkoutItemForTest($this->user, $legWorkout, $today, [
            ['set_number' => 1, 'reps' => 10, 'weight' => 150, 'completed' => true],
        ]);

        $muscleVolume = $this->service->getVolumePerMuscleGroup($this->user);

        expect($muscleVolume)->toBeCollection();
        expect($muscleVolume->count())->toBeGreaterThanOrEqual(2);

        $muscles = $muscleVolume->pluck('muscle')->toArray();
        expect($muscles)->toContain('Chest');
    });
});

describe('1RM Trend', function () {
    beforeEach(function () {
        $this->workout = Workout::factory()->create();
    });

    it('calculates highest 1RM per session', function () {
        $day1 = Carbon::today()->subDays(3)->toDateString();

        createWorkoutItemForTest($this->user, $this->workout, $day1, [
            ['set_number' => 1, 'reps' => 10, 'weight' => 80, 'completed' => true],  // 1RM: 106.67
            ['set_number' => 2, 'reps' => 5, 'weight' => 100, 'completed' => true],  // 1RM: 116.67 (highest)
            ['set_number' => 3, 'reps' => 8, 'weight' => 85, 'completed' => true],   // 1RM: 107.67
        ]);

        $trend = $this->service->getOneRepMaxTrend($this->user, $this->workout->id);

        expect($trend)->toHaveCount(1);
        expect($trend[0]['date'])->toBe($day1);
        expect($trend[0]['estimated_1rm'])->toBe(116.67);
        expect($trend[0]['best_weight'])->toBe(100.0);
        expect($trend[0]['best_reps'])->toBe(5);
    });
});

describe('Relative Strength (Strength-to-Weight Ratio)', function () {
    beforeEach(function () {
        $this->workout = Workout::factory()->create();
    });

    it('correlates 1RM with body weight from InBody logs', function () {
        $day1 = Carbon::today()->subDays(3)->toDateString();

        InBodyLog::factory()
            ->for($this->user)
            ->create([
                'weight' => 80.0,
                'measured_at' => $day1,
            ]);

        // 1RM: 120kg (100kg × 6 reps = 120.0)
        createWorkoutItemForTest($this->user, $this->workout, $day1, [
            ['set_number' => 1, 'reps' => 6, 'weight' => 100, 'completed' => true],
        ]);

        $trend = $this->service->getRelativeStrengthTrend($this->user, $this->workout->id);

        expect($trend)->toHaveCount(1);
        expect((float) $trend[0]['body_weight'])->toBe(80.0);
        expect((float) $trend[0]['relative_strength'])->toBe(1.5); // 120 / 80 = 1.5
    });
});

describe('Intensity Delta Calculation', function () {
    it('calculates positive volume delta correctly', function () {
        $workout = Workout::factory()->create();

        // Previous period (8 weeks ago to 4 weeks ago)
        $prevDate = Carbon::today()->subWeeks(6)->toDateString();
        createWorkoutItemForTest($this->user, $workout, $prevDate, [
            ['set_number' => 1, 'reps' => 10, 'weight' => 100, 'completed' => true], // 1000kg
        ]);

        // Current period (last 4 weeks)
        $currentDate = Carbon::today()->subWeeks(2)->toDateString();
        createWorkoutItemForTest($this->user, $workout, $currentDate, [
            ['set_number' => 1, 'reps' => 15, 'weight' => 100, 'completed' => true], // 1500kg
        ]);

        $analytics = $this->service->getProgressionAnalytics($this->user, $workout->id);

        expect($analytics['intensity_delta']['previous_volume'])->toBe(1000.0);
        expect($analytics['intensity_delta']['current_volume'])->toBe(1500.0);
        expect($analytics['intensity_delta']['delta_percentage'])->toBe(50.0);
        expect($analytics['intensity_delta']['trend'])->toBe('up');
    });
});

describe('Consistency Score', function () {
    it('calculates consistency based on session days in 4-week window', function () {
        $workout = Workout::factory()->create();

        // 8 sessions over last 4 weeks (target: 16 sessions = 4/week * 4 weeks)
        for ($i = 1; $i <= 8; $i++) {
            $date = Carbon::today()->subDays($i * 3)->toDateString();
            createWorkoutItemForTest($this->user, $workout, $date, [
                ['set_number' => 1, 'reps' => 10, 'weight' => 100, 'completed' => true],
            ]);
        }

        $analytics = $this->service->getProgressionAnalytics($this->user, $workout->id);

        expect($analytics['consistency_score']['completed_sessions'])->toBe(8);
        expect((int) $analytics['consistency_score']['target_sessions'])->toBe(16);
        expect((float) $analytics['consistency_score']['percentage'])->toBe(50.0);
    });
});

<?php

use App\Models\User;
use App\Models\UserDailyItem;
use App\Models\UserDailySchedule;
use App\Models\UserPlanAssignment;
use App\Services\DailyItemTrackingService;
use App\Services\DailyScheduleService;
use App\Services\PlanAssignmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->scheduleService = app(DailyScheduleService::class);
    $this->itemTrackingService = app(DailyItemTrackingService::class);
    $this->assignmentService = app(PlanAssignmentService::class);
});

describe('Daily Schedule Progress Architecture', function () {
    it('creates daily schedules and items with target points', function () {
        $schedule = UserDailySchedule::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'target_score' => 0,
            'earned_score' => 0,
            'is_locked' => false,
        ]);

        $item1 = UserDailyItem::create([
            'daily_schedule_id' => $schedule->id,
            'type' => 'workout',
            'item_name' => 'Bench Press',
            'target_details' => ['sets_count' => 3, 'target_reps' => [10, 10, 10]],
            'points' => 3,
            'is_completed' => false,
            'execution_payload' => [
                'sets' => [
                    ['set_number' => 1, 'reps' => 10, 'weight' => 60, 'completed' => false],
                    ['set_number' => 2, 'reps' => 10, 'weight' => 60, 'completed' => false],
                    ['set_number' => 3, 'reps' => 10, 'weight' => 60, 'completed' => false],
                ],
            ],
        ]);

        $item2 = UserDailyItem::create([
            'daily_schedule_id' => $schedule->id,
            'type' => 'meal',
            'item_name' => 'Oatmeal & Whey',
            'target_details' => ['time_slot' => 'Breakfast'],
            'points' => 5,
            'is_completed' => false,
        ]);

        $schedule->recalculateScores();

        expect($schedule->target_score)->toBe(8);
        expect($schedule->earned_score)->toBe(0);
        expect($schedule->adherence_percentage)->toBe(0.0);
        expect($schedule->is_completed)->toBeFalse();
    });

    it('toggles item completion and dynamically updates earned points', function () {
        $schedule = UserDailySchedule::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'target_score' => 8,
            'earned_score' => 0,
            'is_locked' => false,
        ]);

        $item = UserDailyItem::create([
            'daily_schedule_id' => $schedule->id,
            'type' => 'workout',
            'item_name' => 'Squats',
            'target_details' => ['sets_count' => 3],
            'points' => 3,
            'is_completed' => false,
        ]);

        $schedule->recalculateScores();

        // Complete item
        $updated = $this->itemTrackingService->toggleItemCompletion($this->user, $item->id, true);
        expect($updated->is_completed)->toBeTrue();
        expect($updated->completed_at)->not->toBeNull();

        $schedule->refresh();
        expect($schedule->earned_score)->toBe(3);
        expect($schedule->adherence_percentage)->toBe(100.0);

        // Toggle back to incomplete
        $updatedAgain = $this->itemTrackingService->toggleItemCompletion($this->user, $item->id, false);
        expect($updatedAgain->is_completed)->toBeFalse();

        $schedule->refresh();
        expect($schedule->earned_score)->toBe(0);
        expect($schedule->adherence_percentage)->toBe(0.0);
    });

    it('saves workout sets payload and marks completed when all sets are done', function () {
        $schedule = UserDailySchedule::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'target_score' => 0,
            'earned_score' => 0,
            'is_locked' => false,
        ]);

        $item = UserDailyItem::create([
            'daily_schedule_id' => $schedule->id,
            'type' => 'workout',
            'item_name' => 'Deadlift',
            'target_details' => ['sets_count' => 2],
            'points' => 3,
            'is_completed' => false,
        ]);

        $schedule->recalculateScores();

        $sets = [
            ['set_number' => 1, 'reps' => 5, 'weight' => 100, 'completed' => true],
            ['set_number' => 2, 'reps' => 5, 'weight' => 110, 'completed' => true],
        ];

        $updated = $this->itemTrackingService->saveWorkoutSets($this->user, $item->id, $sets);

        expect($updated->is_completed)->toBeTrue();
        expect($updated->execution_payload['sets'])->toHaveCount(2);

        $schedule->refresh();
        expect($schedule->earned_score)->toBe(3);
    });

    it('saves meal consumption payload and awards points', function () {
        $schedule = UserDailySchedule::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'target_score' => 0,
            'earned_score' => 0,
            'is_locked' => false,
        ]);

        $item = UserDailyItem::create([
            'daily_schedule_id' => $schedule->id,
            'type' => 'meal',
            'item_name' => 'Chicken Salad',
            'target_details' => ['time_slot' => 'Lunch'],
            'points' => 5,
            'is_completed' => false,
        ]);

        $schedule->recalculateScores();

        $updated = $this->itemTrackingService->saveMealConsumption($this->user, $item->id, 42, 250);

        expect($updated->is_completed)->toBeTrue();
        expect($updated->execution_payload['consumed_option_id'])->toBe(42);
        expect((float) $updated->execution_payload['consumed_quantity'])->toBe(250.0);

        $schedule->refresh();
        expect($schedule->earned_score)->toBe(5);
    });

    it('prevents modifying locked past schedules', function () {
        $schedule = UserDailySchedule::create([
            'user_id' => $this->user->id,
            'date' => Carbon::yesterday()->toDateString(),
            'target_score' => 3,
            'earned_score' => 0,
            'is_locked' => true,
        ]);

        $item = UserDailyItem::create([
            'daily_schedule_id' => $schedule->id,
            'type' => 'workout',
            'item_name' => 'Pushups',
            'target_details' => [],
            'points' => 3,
            'is_completed' => false,
        ]);

        expect(fn () => $this->itemTrackingService->toggleItemCompletion($this->user, $item->id, true))
            ->toThrow(\Illuminate\Validation\ValidationException::class);
    });

    it('locks past schedules via artisan command', function () {
        $pastSchedule = UserDailySchedule::create([
            'user_id' => $this->user->id,
            'date' => Carbon::yesterday()->toDateString(),
            'target_score' => 3,
            'earned_score' => 3,
            'is_locked' => false,
        ]);

        $todaySchedule = UserDailySchedule::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'target_score' => 3,
            'earned_score' => 0,
            'is_locked' => false,
        ]);

        $this->artisan('schedules:lock-past')->assertSuccessful();

        expect($pastSchedule->fresh()->is_locked)->toBeTrue();
        expect($todaySchedule->fresh()->is_locked)->toBeFalse();
    });

    it('handles optional exercise paths (ex1 OR ex2) and updates item details when alternative is chosen', function () {
        $schedule = UserDailySchedule::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'target_score' => 0,
            'earned_score' => 0,
            'is_locked' => false,
        ]);

        $item = UserDailyItem::create([
            'daily_schedule_id' => $schedule->id,
            'type' => 'workout',
            'item_name' => 'Barbell Bench Press',
            'reference_id' => 101,
            'target_details' => [
                'options' => [
                    [
                        'workout_id' => 101,
                        'name' => 'Barbell Bench Press',
                        'muscles' => ['Chest', 'Triceps'],
                        'sets_count' => 4,
                        'target_reps' => [10, 10, 10, 10],
                    ],
                    [
                        'workout_id' => 102,
                        'name' => 'Dumbbell Chest Press',
                        'muscles' => ['Chest'],
                        'sets_count' => 3,
                        'target_reps' => [12, 12, 12],
                    ],
                ],
                'primary_option' => [
                    'workout_id' => 101,
                    'name' => 'Barbell Bench Press',
                ],
            ],
            'points' => 3,
            'is_completed' => false,
            'execution_payload' => [
                'selected_workout_id' => 101,
                'sets' => [],
            ],
        ]);

        $schedule->recalculateScores();
        expect($item->target_details['options'])->toHaveCount(2);

        // User decides to perform the alternative (Dumbbell Chest Press - workout_id 102)
        $sets = [
            ['set_number' => 1, 'reps' => 12, 'weight' => 24, 'completed' => true],
            ['set_number' => 2, 'reps' => 12, 'weight' => 24, 'completed' => true],
            ['set_number' => 3, 'reps' => 12, 'weight' => 24, 'completed' => true],
        ];

        $updated = $this->itemTrackingService->saveWorkoutSets($this->user, $item->id, $sets, 102);

        expect($updated->is_completed)->toBeTrue();
        expect($updated->item_name)->toBe('Dumbbell Chest Press');
        expect($updated->reference_id)->toBe(102);
        expect($updated->execution_payload['selected_workout_id'])->toBe(102);
        expect($updated->execution_payload['sets'])->toHaveCount(3);

        $schedule->refresh();
        expect($schedule->earned_score)->toBe(3);
    });

    it('handles optional meal paths (meal1 OR meal2) and updates item details when alternative is chosen', function () {
        $schedule = UserDailySchedule::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'target_score' => 0,
            'earned_score' => 0,
            'is_locked' => false,
        ]);

        $item = UserDailyItem::create([
            'daily_schedule_id' => $schedule->id,
            'type' => 'meal',
            'item_name' => 'Eggs & Toast',
            'reference_id' => 201,
            'target_details' => [
                'time_slot' => 'Breakfast',
                'options' => [
                    [
                        'meal_id' => 201,
                        'name' => 'Eggs & Toast',
                        'quantity' => 150,
                        'calories' => 350,
                        'protein' => 20,
                    ],
                    [
                        'meal_id' => 202,
                        'name' => 'Greek Yogurt & Berries',
                        'quantity' => 200,
                        'calories' => 280,
                        'protein' => 25,
                    ],
                ],
                'primary_option' => [
                    'meal_id' => 201,
                    'name' => 'Eggs & Toast',
                ],
            ],
            'points' => 5,
            'is_completed' => false,
            'execution_payload' => [
                'consumed_option_id' => 201,
                'consumed_quantity' => 150,
            ],
        ]);

        $schedule->recalculateScores();

        // User logs the alternative meal (Greek Yogurt - meal_id 202)
        $updated = $this->itemTrackingService->saveMealConsumption($this->user, $item->id, 202, 220);

        expect($updated->is_completed)->toBeTrue();
        expect($updated->item_name)->toBe('Greek Yogurt & Berries');
        expect($updated->reference_id)->toBe(202);
        expect($updated->execution_payload['consumed_option_id'])->toBe(202);
        expect((float) $updated->execution_payload['consumed_quantity'])->toBe(220.0);

        $schedule->refresh();
        expect($schedule->earned_score)->toBe(5);
    });

    it('resolves Day 1, Day 2 plan days sequentially based on assignment start date', function () {
        $days = [
            ['day' => 'Day 1', 'workouts' => []],
            ['day' => 'Day 2', 'workouts' => []],
            ['day' => 'Day 3', 'workouts' => []],
        ];

        $assignment = UserPlanAssignment::create([
            'user_id' => $this->user->id,
            'plan_id' => 1,
            'plan_type' => 'workout',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-29',
            'status' => 'active',
        ]);

        // Start date -> Day 1
        $match0 = $this->scheduleService->resolveMatchingPlanDay($days, Carbon::parse('2026-08-01'), $assignment);
        expect($match0['day'])->toBe('Day 1');

        // Day after -> Day 2
        $match1 = $this->scheduleService->resolveMatchingPlanDay($days, Carbon::parse('2026-08-02'), $assignment);
        expect($match1['day'])->toBe('Day 2');

        // 2 days after -> Day 3
        $match2 = $this->scheduleService->resolveMatchingPlanDay($days, Carbon::parse('2026-08-03'), $assignment);
        expect($match2['day'])->toBe('Day 3');

        // 3 days after -> cycles back to Day 1
        $match3 = $this->scheduleService->resolveMatchingPlanDay($days, Carbon::parse('2026-08-04'), $assignment);
        expect($match3['day'])->toBe('Day 1');
    });

    it('automatically defaults assignment end date to 28 days (4 weeks)', function () {
        $startDate = Carbon::parse('2026-08-10');
        $assignment = $this->assignmentService->assignPlan(
            $this->user,
            1,
            'workout',
            $startDate,
            null,
            0 // Don't pre-materialize in this unit check
        );

        expect($assignment->start_date->toDateString())->toBe('2026-08-10');
        expect($assignment->end_date->toDateString())->toBe('2026-09-07'); // 28 days after
    });

    it('syncs plan updates to active users using future_only strategy', function () {
        $assignment = UserPlanAssignment::create([
            'user_id' => $this->user->id,
            'plan_id' => 99,
            'plan_type' => 'workout',
            'start_date' => Carbon::today()->subDays(5)->toDateString(),
            'end_date' => Carbon::today()->addDays(23)->toDateString(),
            'status' => 'active',
        ]);

        // Create today's schedule (should remain untouched)
        $todaySchedule = UserDailySchedule::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'target_score' => 10,
            'earned_score' => 5,
            'is_locked' => false,
        ]);

        // Create tomorrow's schedule (should be dropped & re-materialized)
        $tomorrowSchedule = UserDailySchedule::create([
            'user_id' => $this->user->id,
            'date' => Carbon::tomorrow()->toDateString(),
            'target_score' => 8,
            'earned_score' => 0,
            'is_locked' => false,
        ]);

        $synced = $this->scheduleService->syncPlanUpdateToAssignedUsers(99, 'workout', 'future_only');
        expect($synced)->toBe(1);

        // Today's schedule was untouched
        expect($todaySchedule->fresh()->target_score)->toBe(10);
        expect($todaySchedule->fresh()->earned_score)->toBe(5);
    });

    it('successfully assigns a plan and materializes schedules for the initial window', function () {
        // Create mock workout plan file in storage
        $planJson = json_encode([
            [
                'day' => 'Day 1',
                'workouts' => [
                    [
                        'options' => [
                            ['workout_id' => 1, 'reps' => 1],
                        ],
                    ],
                ],
            ],
        ]);

        \Illuminate\Support\Facades\Storage::disk('local')->put('workout-plans/test-plan.json', $planJson);

        $workoutPlan = \App\Models\WorkoutPlan::create([
            'name' => 'Test Plan',
            'file_path' => 'workout-plans/test-plan.json',
        ]);

        $assignment = $this->assignmentService->assignPlan(
            $this->user,
            $workoutPlan->id,
            'workout',
            Carbon::today(),
            Carbon::today()->addDays(28),
            7 // Pre-materialize 7 days
        );

        expect($assignment)->not->toBeNull();
        expect(UserDailySchedule::where('user_id', $this->user->id)->count())->toBe(7);
    });
});


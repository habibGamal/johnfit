<?php

namespace App\Services;

use App\Models\Meal;
use App\Models\MealPlan;
use App\Models\RepsPreset;
use App\Models\User;
use App\Models\UserDailyItem;
use App\Models\UserDailySchedule;
use App\Models\UserPlanAssignment;
use App\Models\Workout;
use App\Models\WorkoutPlan;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DailyScheduleService
{
    /**
     * Get or dynamically materialize a user's daily schedule for a specific date.
     */
    public function getScheduleForDate(User $user, Carbon|string $date): ?UserDailySchedule
    {
        $targetDate = is_string($date) ? Carbon::parse($date)->toDateString() : $date->toDateString();

        // Check if schedule already exists
        $schedule = UserDailySchedule::with([
            'items' => function ($q) {
                $q->active()->orderBy('order_index');
            }
        ])
            ->where('user_id', $user->id)
            ->whereDate('date', $targetDate)
            ->first();

        if ($schedule) {
            return $schedule;
        }

        // Try to materialize from active plan assignments
        return $this->materializeDateForUser($user, Carbon::parse($targetDate));
    }

    /**
     * Materialize a schedule for a single date based on user's active assignments.
     */
    public function materializeDateForUser(User $user, Carbon $date): ?UserDailySchedule
    {
        $dateStr = $date->toDateString();
        $dayName = $date->format('l'); // e.g. 'Monday'

        $activeAssignments = UserPlanAssignment::where('user_id', $user->id)
            ->active()
            ->forDate($dateStr)
            ->get();

        if ($activeAssignments->isEmpty()) {
            return null;
        }

        return DB::transaction(function () use ($user, $date, $dateStr, $dayName, $activeAssignments) {
            $workoutAssignment = $activeAssignments->firstWhere('plan_type', 'workout')
                ?? $activeAssignments->firstWhere('plan_type', 'unified');
            $mealAssignment = $activeAssignments->firstWhere('plan_type', 'meal')
                ?? $activeAssignments->firstWhere('plan_type', 'unified');

            // Find or create schedule
            $schedule = UserDailySchedule::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $dateStr,
                ],
                [
                    'workout_assignment_id' => $workoutAssignment?->id,
                    'meal_assignment_id' => $mealAssignment?->id,
                    'target_score' => 0,
                    'earned_score' => 0,
                    'is_locked' => Carbon::parse($dateStr)->isPast() && !Carbon::parse($dateStr)->isToday(),
                ]
            );

            // If schedule already existed, update foreign keys if newly assigned
            $dirty = false;
            if ($workoutAssignment && $schedule->workout_assignment_id !== $workoutAssignment->id) {
                $schedule->workout_assignment_id = $workoutAssignment->id;
                $dirty = true;
            }
            if ($mealAssignment && $schedule->meal_assignment_id !== $mealAssignment->id) {
                $schedule->meal_assignment_id = $mealAssignment->id;
                $dirty = true;
            }
            if ($dirty) {
                $schedule->save();
            }

            $orderIndex = 0;

            foreach ($activeAssignments as $assignment) {
                if ($assignment->plan_type === 'workout' || $assignment->plan_type === 'unified') {
                    $workoutPlan = WorkoutPlan::find($assignment->plan_id);
                    if ($workoutPlan && $workoutPlan->file_path && Storage::disk('local')->exists($workoutPlan->file_path)) {
                        $orderIndex = $this->materializeWorkoutItems($schedule, $workoutPlan, $date, $assignment, $orderIndex);
                    }
                }

                if ($assignment->plan_type === 'meal' || $assignment->plan_type === 'unified') {
                    $mealPlan = MealPlan::find($assignment->plan_id);
                    if ($mealPlan && $mealPlan->file_path && Storage::disk('local')->exists($mealPlan->file_path)) {
                        $orderIndex = $this->materializeMealItems($schedule, $mealPlan, $date, $assignment, $orderIndex);
                    }
                }
            }

            $schedule->recalculateScores();

            return $schedule->fresh([
                'items' => function ($q) {
                    $q->active()->orderBy('order_index');
                }
            ]);
        });
    }

    /**
     * Resolve the matching day template from a plan using Day 1, Day 2 cycle progression.
     */
    public function resolveMatchingPlanDay(array $days, Carbon $date, UserPlanAssignment $assignment): ?array
    {
        if (empty($days)) {
            return null;
        }

        $startDate = Carbon::parse($assignment->start_date);
        $dayOffset = $startDate->diffInDays($date);
        $totalDays = count($days);
        $cycleDayIndex = $totalDays > 0 ? ($dayOffset % $totalDays) + 1 : 1;
        $targetDayName = "Day {$cycleDayIndex}";
        $calendarDayName = $date->format('l');

        // 1. Check exact match for "Day 1", "Day 2", etc.
        $match = collect($days)->first(function ($d) use ($targetDayName) {
            return strcasecmp(trim($d['day'] ?? ''), $targetDayName) === 0;
        });

        if ($match) {
            return $match;
        }

        // 2. Check index-based Day matching
        if (isset($days[$cycleDayIndex - 1])) {
            $candidate = $days[$cycleDayIndex - 1];
            $candidateDay = trim($candidate['day'] ?? '');
            if (preg_match('/^Day\s*\d+$/i', $candidateDay) || is_numeric($candidateDay)) {
                return $candidate;
            }
        }

        // 3. Fallback: match by weekday (e.g. "Monday") for backward compatibility
        $matchByWeekday = collect($days)->first(function ($d) use ($calendarDayName) {
            return strcasecmp(trim($d['day'] ?? ''), $calendarDayName) === 0;
        });

        if ($matchByWeekday) {
            return $matchByWeekday;
        }

        // 4. Fallback to modulo cycle index
        return $days[($cycleDayIndex - 1) % $totalDays] ?? $days[0];
    }

    /**
     * Materialize workout items from a WorkoutPlan template day.
     */
    public function materializeWorkoutItems(
        UserDailySchedule $schedule,
        WorkoutPlan $workoutPlan,
        Carbon $date,
        UserPlanAssignment $assignment,
        int $startOrder = 0
    ): int {
        $json = Storage::disk('local')->get($workoutPlan->file_path);
        $days = json_decode($json, true) ?? [];

        $matchingDay = $this->resolveMatchingPlanDay($days, $date, $assignment);

        if (!$matchingDay || empty($matchingDay['workouts'])) {
            return $startOrder;
        }

        // Collect all workout IDs and reps IDs (from options or flat)
        $workoutIds = [];
        $repsIds = [];
        foreach ($matchingDay['workouts'] as $slot) {
            if (isset($slot['options']) && is_array($slot['options'])) {
                foreach ($slot['options'] as $opt) {
                    if (!empty($opt['workout_id'])) {
                        $workoutIds[] = $opt['workout_id'];
                    }
                    if (!empty($opt['reps'])) {
                        $repsIds[] = $opt['reps'];
                    }
                }
            } elseif (isset($slot['workout_id'])) {
                $workoutIds[] = $slot['workout_id'];
                if (!empty($slot['reps'])) {
                    $repsIds[] = $slot['reps'];
                }
            }
        }

        $workouts = Workout::findMany(array_unique($workoutIds))->keyBy('id');
        $repsPresets = RepsPreset::findMany(array_unique($repsIds))->keyBy('id');

        foreach ($matchingDay['workouts'] as $slot) {
            $rawOptions = [];
            if (isset($slot['options']) && is_array($slot['options'])) {
                $rawOptions = $slot['options'];
            } elseif (isset($slot['workout_id'])) {
                $rawOptions = [$slot];
            }

            if (empty($rawOptions)) {
                continue;
            }

            $options = collect($rawOptions)->map(function ($opt) use ($workouts, $repsPresets) {
                $workoutId = $opt['workout_id'] ?? null;
                $workout = $workouts->get($workoutId);
                $repsPreset = $repsPresets->get($opt['reps'] ?? null);

                $setsCount = 3;
                $repsList = [];

                if ($repsPreset && is_array($repsPreset->reps)) {
                    $setsCount = count($repsPreset->reps);
                    $repsList = collect($repsPreset->reps)->pluck('count')->all();
                }

                return [
                    'workout_id' => $workoutId,
                    'name' => $workout ? $workout->name : 'Workout Exercise',
                    'muscles' => $workout ? $workout->muscles : [],
                    'tools' => $workout ? $workout->tools : [],
                    'thumb' => $workout?->thumb,
                    'video_url' => $workout?->video_url,
                    'sets_count' => $setsCount,
                    'reps_preset_name' => $repsPreset?->short_name,
                    'target_reps' => $repsList,
                ];
            })->all();

            $primaryOption = $options[0] ?? null;
            if (!$primaryOption) {
                continue;
            }

            $setsCount = $primaryOption['sets_count'] ?? 3;
            $repsList = $primaryOption['target_reps'] ?? [];

            $initialSets = [];
            for ($s = 1; $s <= $setsCount; $s++) {
                $initialSets[] = [
                    'set_number' => $s,
                    'target_reps' => $repsList[$s - 1] ?? 10,
                    'reps' => $repsList[$s - 1] ?? 10,
                    'weight' => null,
                    'completed' => false,
                ];
            }

            UserDailyItem::create([
                'daily_schedule_id' => $schedule->id,
                'type' => 'workout',
                'item_name' => $primaryOption['name'],
                'reference_id' => $primaryOption['workout_id'],
                'target_details' => [
                    'options' => $options,
                    'primary_option' => $primaryOption,
                    'workout_id' => $primaryOption['workout_id'],
                    'muscles' => $primaryOption['muscles'],
                    'tools' => $primaryOption['tools'],
                    'thumb' => $primaryOption['thumb'],
                    'video_url' => $primaryOption['video_url'],
                    'sets_count' => $setsCount,
                    'reps_preset_name' => $primaryOption['reps_preset_name'],
                    'target_reps' => $repsList,
                ],
                'points' => 3,
                'is_completed' => false,
                'execution_payload' => [
                    'selected_workout_id' => $primaryOption['workout_id'],
                    'sets' => $initialSets,
                ],
                'status' => 'active',
                'order_index' => $startOrder++,
            ]);
        }

        return $startOrder;
    }

    /**
     * Materialize meal items from a MealPlan template day.
     */
    public function materializeMealItems(
        UserDailySchedule $schedule,
        MealPlan $mealPlan,
        Carbon $date,
        UserPlanAssignment $assignment,
        int $startOrder = 0
    ): int {
        $json = Storage::disk('local')->get($mealPlan->file_path);
        $days = json_decode($json, true) ?? [];

        $matchingDay = $this->resolveMatchingPlanDay($days, $date, $assignment);

        if (!$matchingDay || empty($matchingDay['time'])) {
            return $startOrder;
        }

        // Collect meal IDs
        $mealIds = collect($matchingDay['time'])->flatMap(function ($timeSlot) {
            return collect($timeSlot['meals'] ?? [])->flatMap(function ($group) {
                return collect($group['options'] ?? [])->pluck('meal_id');
            });
        })->filter()->unique()->all();

        $meals = Meal::findMany($mealIds)->keyBy('id');

        foreach ($matchingDay['time'] as $timeSlot) {
            $timeSlotName = $timeSlot['time'] ?? 'Meal Time';

            foreach ($timeSlot['meals'] ?? [] as $group) {
                $options = collect($group['options'] ?? [])->map(function ($opt) use ($meals) {
                    $meal = $meals->get($opt['meal_id'] ?? null);
                    return [
                        'meal_id' => $opt['meal_id'] ?? null,
                        'name' => $meal?->name ?? 'Meal Option',
                        'quantity' => $opt['quantity'] ?? 1,
                        'calories' => $meal?->calories ?? 0,
                        'protein' => $meal?->protein ?? 0,
                        'carbs' => $meal?->carbs ?? 0,
                        'fat' => $meal?->fat ?? 0,
                    ];
                })->all();

                $primaryOption = $options[0] ?? null;
                $itemName = $primaryOption ? $primaryOption['name'] : $timeSlotName;

                UserDailyItem::create([
                    'daily_schedule_id' => $schedule->id,
                    'type' => 'meal',
                    'item_name' => $itemName,
                    'reference_id' => $primaryOption['meal_id'] ?? null,
                    'target_details' => [
                        'time_slot' => $timeSlotName,
                        'options' => $options,
                        'primary_option' => $primaryOption,
                    ],
                    'points' => 5,
                    'is_completed' => false,
                    'execution_payload' => [
                        'consumed_option_id' => $primaryOption['meal_id'] ?? null,
                        'consumed_quantity' => $primaryOption['quantity'] ?? 1,
                    ],
                    'status' => 'active',
                    'order_index' => $startOrder++,
                ]);
            }
        }

        return $startOrder;
    }

    /**
     * Get weekly adherence statistics for a 7-day window.
     */
    public function getWeeklyAdherence(User $user, ?Carbon $startDate = null): array
    {
        $start = $startDate ? $startDate->copy()->startOfWeek() : Carbon::now()->startOfWeek();
        $end = $start->copy()->endOfWeek();

        $schedules = UserDailySchedule::where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(function ($s) {
                return Carbon::parse($s->date)->format('Y-m-d');
            });

        $days = [];
        $totalTarget = 0;
        $totalEarned = 0;

        $period = CarbonPeriod::create($start, $end);
        foreach ($period as $dt) {
            $dateKey = $dt->toDateString();
            $schedule = $schedules->get($dateKey);

            $target = $schedule ? $schedule->target_score : 0;
            $earned = $schedule ? $schedule->earned_score : 0;
            $percentage = $target > 0 ? round(($earned / $target) * 100, 1) : 0;

            $totalTarget += $target;
            $totalEarned += $earned;

            $days[] = [
                'date' => $dateKey,
                'day_name' => $dt->format('D'),
                'day_full' => $dt->format('l'),
                'target_score' => $target,
                'earned_score' => $earned,
                'percentage' => $percentage,
                'is_completed' => $schedule ? $schedule->is_completed : false,
                'is_today' => $dt->isToday(),
                'is_past' => $dt->isPast() && !$dt->isToday(),
                'is_locked' => $schedule ? $schedule->is_locked : false,
            ];
        }

        $overallPercentage = $totalTarget > 0 ? round(($totalEarned / $totalTarget) * 100, 1) : 0;

        return [
            'days' => $days,
            'total_target' => $totalTarget,
            'total_earned' => $totalEarned,
            'overall_percentage' => $overallPercentage,
        ];
    }

    /**
     * Compute current adherence streak.
     */
    public function getCurrentStreak(User $user): int
    {
        $schedules = UserDailySchedule::where('user_id', $user->id)
            ->where('date', '<=', Carbon::today()->toDateString())
            ->orderByDesc('date')
            ->take(60)
            ->get();

        $streak = 0;
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        // If today is completed, start from today; else start from yesterday
        $todaySchedule = $schedules->firstWhere('date', $today);
        $startDate = ($todaySchedule && $todaySchedule->is_completed) ? Carbon::today() : Carbon::yesterday();

        $expectedDate = $startDate;

        foreach ($schedules as $sched) {
            $schedDate = Carbon::parse($sched->date)->toDateString();

            if ($schedDate > $expectedDate->toDateString()) {
                continue;
            }

            if ($schedDate === $expectedDate->toDateString()) {
                if ($sched->is_completed) {
                    $streak++;
                    $expectedDate->subDay();
                } else {
                    break;
                }
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Synchronize plan template updates to all active assigned users based on chosen strategy.
     */
    public function syncPlanUpdateToAssignedUsers(
        int $planId,
        string $planType = 'workout',
        string $strategy = 'future_only'
    ): int {
        if ($strategy === 'none') {
            return 0;
        }

        $activeAssignments = UserPlanAssignment::where('plan_id', $planId)
            ->where('plan_type', $planType)
            ->active()
            ->with('user')
            ->get();

        $syncedUsersCount = 0;

        foreach ($activeAssignments as $assignment) {
            $user = $assignment->user;
            if (!$user) {
                continue;
            }

            $startDate = ($strategy === 'today_and_future')
                ? Carbon::today()
                : Carbon::tomorrow();

            $endDate = $assignment->end_date
                ? Carbon::parse($assignment->end_date)
                : $startDate->copy()->addDays(28);

            if ($startDate->gt($endDate)) {
                continue;
            }

            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                $dateStr = $date->toDateString();
                $schedule = UserDailySchedule::where('user_id', $user->id)
                    ->whereDate('date', $dateStr)
                    ->first();

                // If it's a locked schedule, don't modify
                if ($schedule && $schedule->is_locked) {
                    continue;
                }

                if ($date->isFuture()) {
                    // Drop existing schedule/items and re-materialize from updated plan
                    if ($schedule) {
                        $schedule->items()->delete();
                        $schedule->delete();
                    }
                    $this->materializeDateForUser($user, $date);
                } elseif ($date->isToday()) {
                    // If today and today_and_future:
                    // If schedule exists: delete uncompleted items for this plan type, and re-materialize
                    if ($schedule) {
                        // Remove uncompleted items of this plan type
                        $schedule->items()
                            ->where('type', $planType)
                            ->where('is_completed', false)
                            ->delete();

                        // Re-materialize items for this plan
                        $orderIndex = $schedule->items()->count();
                        if ($planType === 'workout') {
                            $workoutPlan = WorkoutPlan::find($planId);
                            if ($workoutPlan && $workoutPlan->file_path && Storage::disk('local')->exists($workoutPlan->file_path)) {
                                $this->materializeWorkoutItems($schedule, $workoutPlan, $date, $assignment, $orderIndex);
                            }
                        } elseif ($planType === 'meal') {
                            $mealPlan = MealPlan::find($planId);
                            if ($mealPlan && $mealPlan->file_path && Storage::disk('local')->exists($mealPlan->file_path)) {
                                $this->materializeMealItems($schedule, $mealPlan, $date, $assignment, $orderIndex);
                            }
                        }
                        $schedule->recalculateScores();
                    } else {
                        $this->materializeDateForUser($user, $date);
                    }
                }
            }

            $syncedUsersCount++;
        }

        return $syncedUsersCount;
    }
}


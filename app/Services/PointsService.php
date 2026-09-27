<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDailyItem;
use App\Models\UserDailyPoint;
use App\Models\UserDailyWaterLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PointsService
{
    public const HYDRATION_GOAL_POINTS = 5;

    public function __construct(
        protected ?BadgeService $badgeService = null
    ) {}

    /**
     * Compute cumulative points threshold needed to complete level L and reach L+1.
     *
     * Formula: P(L) = 5*L^2 + 15*L
     */
    public static function pointsThresholdForLevel(int $level): int
    {
        if ($level <= 0) {
            return 0;
        }

        return (5 * ($level ** 2)) + (15 * $level);
    }

    /**
     * Calculate level from total points using the inverse quadratic equation:
     *
     * 5k^2 + 15k <= P
     * k = floor((-15 + sqrt(225 + 20P)) / 10)
     * Current Level L = k + 1
     */
    public static function levelFromPoints(int $points): int
    {
        if ($points <= 0) {
            return 1;
        }

        $discriminant = 225 + (20 * $points);
        $k = (int) floor((-15 + sqrt($discriminant)) / 10);

        return max(1, $k + 1);
    }

    /**
     * Get comprehensive level progression details for given total points.
     *
     * @return array{
     *     level: int,
     *     title: string,
     *     total_points: int,
     *     level_min_points: int,
     *     level_max_points: int,
     *     points_in_level: int,
     *     points_needed_in_level: int,
     *     points_to_next_level: int,
     *     progress_percent: float
     * }
     */
    public static function getLevelDetails(int $totalPoints): array
    {
        $points = max(0, $totalPoints);
        $level = self::levelFromPoints($points);

        $levelMin = $level > 1 ? self::pointsThresholdForLevel($level - 1) : 0;
        $levelMax = self::pointsThresholdForLevel($level);

        $pointsInLevel = max(0, $points - $levelMin);
        $pointsNeeded = max(1, $levelMax - $levelMin);
        $pointsToNext = max(0, $levelMax - $points);
        $progress = round(min(100.0, ($pointsInLevel / $pointsNeeded) * 100), 1);

        return [
            'level' => $level,
            'title' => self::levelTitle($level),
            'total_points' => $points,
            'level_min_points' => $levelMin,
            'level_max_points' => $levelMax,
            'points_in_level' => $pointsInLevel,
            'points_needed_in_level' => $pointsNeeded,
            'points_to_next_level' => $pointsToNext,
            'progress_percent' => $progress,
        ];
    }

    /**
     * Human-readable title for infinite levels.
     */
    public static function levelTitle(int $level): string
    {
        return match (true) {
            $level >= 100 => 'Legend ' . ($level - 99),
            $level >= 75 => 'Mythic',
            $level >= 50 => 'Grandmaster',
            $level >= 40 => 'Master',
            $level >= 30 => 'Champion',
            $level >= 20 => 'Elite',
            $level >= 15 => 'Veteran',
            $level >= 10 => 'Warrior',
            $level >= 5 => 'Challenger',
            $level >= 2 => 'Apprentice',
            default => 'Novice',
        };
    }

    /**
     * Record workout points for a specific date and update user totals.
     */
    public function recordWorkoutPoints(User $user, int $points, ?Carbon $date = null): void
    {
        if ($points <= 0) {
            return;
        }

        $dateStr = ($date ?? Carbon::today())->toDateString();

        $daily = UserDailyPoint::firstOrCreate(
            ['user_id' => $user->id, 'date' => $dateStr],
            ['workout_points' => 0, 'meal_points' => 0, 'hydration_points' => 0, 'total_points' => 0]
        );

        $daily->workout_points += $points;
        $daily->recalculateTotal();

        $user->workout_points += $points;
        $this->syncUserTotals($user);
    }

    /**
     * Deduct workout points for a specific date and update user totals.
     */
    public function deductWorkoutPoints(User $user, int $points, ?Carbon $date = null): void
    {
        if ($points <= 0) {
            return;
        }

        $dateStr = ($date ?? Carbon::today())->toDateString();

        $daily = UserDailyPoint::where('user_id', $user->id)
            ->whereDate('date', $dateStr)
            ->first();

        if ($daily) {
            $daily->workout_points = max(0, $daily->workout_points - $points);
            $daily->recalculateTotal();
        }

        $user->workout_points = max(0, $user->workout_points - $points);
        $this->syncUserTotals($user);
    }

    /**
     * Record meal points for a specific date and update user totals.
     */
    public function recordMealPoints(User $user, int $points, ?Carbon $date = null): void
    {
        if ($points <= 0) {
            return;
        }

        $dateStr = ($date ?? Carbon::today())->toDateString();

        $daily = UserDailyPoint::firstOrCreate(
            ['user_id' => $user->id, 'date' => $dateStr],
            ['workout_points' => 0, 'meal_points' => 0, 'hydration_points' => 0, 'total_points' => 0]
        );

        $daily->meal_points += $points;
        $daily->recalculateTotal();

        $user->meal_points += $points;
        $this->syncUserTotals($user);
    }

    /**
     * Deduct meal points for a specific date and update user totals.
     */
    public function deductMealPoints(User $user, int $points, ?Carbon $date = null): void
    {
        if ($points <= 0) {
            return;
        }

        $dateStr = ($date ?? Carbon::today())->toDateString();

        $daily = UserDailyPoint::where('user_id', $user->id)
            ->whereDate('date', $dateStr)
            ->first();

        if ($daily) {
            $daily->meal_points = max(0, $daily->meal_points - $points);
            $daily->recalculateTotal();
        }

        $user->meal_points = max(0, $user->meal_points - $points);
        $this->syncUserTotals($user);
    }

    /**
     * Record hydration points for a specific date and update user totals.
     */
    public function recordHydrationPoints(User $user, int $points = self::HYDRATION_GOAL_POINTS, ?Carbon $date = null): void
    {
        if ($points <= 0) {
            return;
        }

        $dateStr = ($date ?? Carbon::today())->toDateString();

        $daily = UserDailyPoint::firstOrCreate(
            ['user_id' => $user->id, 'date' => $dateStr],
            ['workout_points' => 0, 'meal_points' => 0, 'hydration_points' => 0, 'total_points' => 0]
        );

        $daily->hydration_points += $points;
        $daily->recalculateTotal();

        $user->hydration_points += $points;
        $this->syncUserTotals($user);
    }

    /**
     * Deduct hydration points for a specific date and update user totals.
     */
    public function deductHydrationPoints(User $user, int $points = self::HYDRATION_GOAL_POINTS, ?Carbon $date = null): void
    {
        if ($points <= 0) {
            return;
        }

        $dateStr = ($date ?? Carbon::today())->toDateString();

        $daily = UserDailyPoint::where('user_id', $user->id)
            ->whereDate('date', $dateStr)
            ->first();

        if ($daily) {
            $daily->hydration_points = max(0, $daily->hydration_points - $points);
            $daily->recalculateTotal();
        }

        $user->hydration_points = max(0, $user->hydration_points - $points);
        $this->syncUserTotals($user);
    }

    // Aliases for compatibility
    public function addWorkoutPoints(User $user, int $points, ?Carbon $date = null): void
    {
        $this->recordWorkoutPoints($user, $points, $date);
    }

    public function addMealPoints(User $user, int $points, ?Carbon $date = null): void
    {
        $this->recordMealPoints($user, $points, $date);
    }

    public function addHydrationPoints(User $user, int $points = self::HYDRATION_GOAL_POINTS, ?Carbon $date = null): void
    {
        $this->recordHydrationPoints($user, $points, $date);
    }

    /**
     * Sync total_points, calculate new level, save user, and check badge unlocks.
     */
    protected function syncUserTotals(User $user): void
    {
        $user->total_points = $user->workout_points + $user->meal_points + $user->hydration_points;
        $user->level = self::levelFromPoints($user->total_points);
        $user->save();

        $this->syncBadges($user);
    }

    /**
     * Recalculate all user points and populate user_daily_points from completed schedule items and water logs.
     */
    public function recalculateUserPoints(User $user): array
    {
        // 1. Group completed workouts by date
        $workoutByDate = UserDailyItem::query()
            ->join('user_daily_schedules', 'user_daily_items.daily_schedule_id', '=', 'user_daily_schedules.id')
            ->where('user_daily_schedules.user_id', $user->id)
            ->where('user_daily_items.type', 'workout')
            ->where('user_daily_items.is_completed', true)
            ->where('user_daily_items.status', '!=', 'voided')
            ->groupBy('user_daily_schedules.date')
            ->selectRaw('user_daily_schedules.date, SUM(user_daily_items.points) as total_pts')
            ->pluck('total_pts', 'date');

        // 2. Group completed meals by date
        $mealsByDate = UserDailyItem::query()
            ->join('user_daily_schedules', 'user_daily_items.daily_schedule_id', '=', 'user_daily_schedules.id')
            ->where('user_daily_schedules.user_id', $user->id)
            ->where('user_daily_items.type', 'meal')
            ->where('user_daily_items.is_completed', true)
            ->where('user_daily_items.status', '!=', 'voided')
            ->groupBy('user_daily_schedules.date')
            ->selectRaw('user_daily_schedules.date, SUM(user_daily_items.points) as total_pts')
            ->pluck('total_pts', 'date');

        // 3. Completed water logs by date
        $waterByDate = UserDailyWaterLog::query()
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->pluck('date')
            ->mapWithKeys(fn ($d) => [Carbon::parse($d)->toDateString() => self::HYDRATION_GOAL_POINTS]);

        // Merge all dates where user made progress
        $allDates = collect()
            ->merge($workoutByDate->keys())
            ->merge($mealsByDate->keys())
            ->merge($waterByDate->keys())
            ->unique();

        // Clear existing daily points for clean recalculation
        UserDailyPoint::where('user_id', $user->id)->delete();

        $totalWorkout = 0;
        $totalMeal = 0;
        $totalHydration = 0;

        foreach ($allDates as $date) {
            $dateStr = Carbon::parse($date)->toDateString();
            $wPts = (int) ($workoutByDate[$dateStr] ?? 0);
            $mPts = (int) ($mealsByDate[$dateStr] ?? 0);
            $hPts = (int) ($waterByDate[$dateStr] ?? 0);
            $tPts = $wPts + $mPts + $hPts;

            if ($tPts > 0) {
                UserDailyPoint::create([
                    'user_id' => $user->id,
                    'date' => $dateStr,
                    'workout_points' => $wPts,
                    'meal_points' => $mPts,
                    'hydration_points' => $hPts,
                    'total_points' => $tPts,
                ]);

                $totalWorkout += $wPts;
                $totalMeal += $mPts;
                $totalHydration += $hPts;
            }
        }

        $user->workout_points = $totalWorkout;
        $user->meal_points = $totalMeal;
        $user->hydration_points = $totalHydration;
        $user->total_points = $totalWorkout + $totalMeal + $totalHydration;
        $user->level = self::levelFromPoints($user->total_points);
        $user->save();

        $this->syncBadges($user);

        return $this->getSummary($user);
    }

    /**
     * Get complete points and level summary for the user.
     */
    public function getSummary(User $user): array
    {
        $levelDetails = self::getLevelDetails($user->total_points ?? 0);

        return [
            ...$levelDetails,
            'components' => [
                'workout' => [
                    'points' => (int) ($user->workout_points ?? 0),
                    'label' => 'Workout',
                    'icon' => 'Dumbbell',
                ],
                'meal' => [
                    'points' => (int) ($user->meal_points ?? 0),
                    'label' => 'Nutrition',
                    'icon' => 'Utensils',
                ],
                'hydration' => [
                    'points' => (int) ($user->hydration_points ?? 0),
                    'label' => 'Hydration',
                    'icon' => 'Droplets',
                ],
            ],
            'updated_at' => now()->toISOString(),
        ];
    }

    /**
     * Get points progression history reading directly from the user_daily_points table.
     *
     * Returns a day-by-day array of logged points for the given number of days (default 30).
     */
    public function getPointsHistory(User $user, int $days = 30): array
    {
        $days = max(7, min(180, $days));
        $startDate = Carbon::today()->subDays($days - 1);
        $endDate = Carbon::today();

        // 1. Fetch all user_daily_points records for user in this range
        $dailyRecords = UserDailyPoint::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn ($r) => Carbon::parse($r->date)->toDateString());

        // 2. Fetch cumulative points prior to startDate to establish the correct running baseline
        $priorPoints = (int) UserDailyPoint::query()
            ->where('user_id', $user->id)
            ->where('date', '<', $startDate->toDateString())
            ->sum('total_points');

        $runningTotal = $priorPoints;
        $history = [];
        $cursor = $startDate->copy();

        while ($cursor <= $endDate) {
            $dateStr = $cursor->toDateString();
            $record = $dailyRecords->get($dateStr);

            $wPts = (int) ($record?->workout_points ?? 0);
            $mPts = (int) ($record?->meal_points ?? 0);
            $hPts = (int) ($record?->hydration_points ?? 0);
            $earned = $wPts + $mPts + $hPts;

            $runningTotal += $earned;

            $history[] = [
                'date' => $cursor->format('M d'),
                'dayName' => $cursor->format('D'),
                'fullDate' => $dateStr,
                'isToday' => $cursor->isToday(),
                'workout_points' => $wPts,
                'meal_points' => $mPts,
                'hydration_points' => $hPts,
                'points_earned' => $earned,
                'total_points' => $runningTotal,
                'total_score' => $earned,
                'level' => self::levelFromPoints($runningTotal),
            ];

            $cursor->addDay();
        }

        return $history;
    }

    /**
     * Trigger badge unlock check.
     */
    protected function syncBadges(User $user): void
    {
        try {
            $badgeService = $this->badgeService ?? app(BadgeService::class);
            $badgeService->syncUnlocks($user);
        } catch (\Throwable $e) {
            // Silently capture any badge sync issues
        }
    }
}

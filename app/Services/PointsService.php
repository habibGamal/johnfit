<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDailyItem;
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
     *
     * (At P=0 -> k=0 -> Level 1. Reaching P=20 -> k=1 -> Level 2.)
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
     * Add workout points and update user totals.
     */
    public function addWorkoutPoints(User $user, int $points): void
    {
        if ($points <= 0) {
            return;
        }

        $user->workout_points += $points;
        $this->syncUserTotals($user);
    }

    /**
     * Deduct workout points (when unchecked/undone).
     */
    public function deductWorkoutPoints(User $user, int $points): void
    {
        if ($points <= 0) {
            return;
        }

        $user->workout_points = max(0, $user->workout_points - $points);
        $this->syncUserTotals($user);
    }

    /**
     * Add meal points and update user totals.
     */
    public function addMealPoints(User $user, int $points): void
    {
        if ($points <= 0) {
            return;
        }

        $user->meal_points += $points;
        $this->syncUserTotals($user);
    }

    /**
     * Deduct meal points (when unchecked/undone).
     */
    public function deductMealPoints(User $user, int $points): void
    {
        if ($points <= 0) {
            return;
        }

        $user->meal_points = max(0, $user->meal_points - $points);
        $this->syncUserTotals($user);
    }

    /**
     * Add hydration points and update user totals.
     */
    public function addHydrationPoints(User $user, int $points = self::HYDRATION_GOAL_POINTS): void
    {
        if ($points <= 0) {
            return;
        }

        $user->hydration_points += $points;
        $this->syncUserTotals($user);
    }

    /**
     * Deduct hydration points (when water goal becomes uncompleted).
     */
    public function deductHydrationPoints(User $user, int $points = self::HYDRATION_GOAL_POINTS): void
    {
        if ($points <= 0) {
            return;
        }

        $user->hydration_points = max(0, $user->hydration_points - $points);
        $this->syncUserTotals($user);
    }

    /**
     * Sync total_points, calculate new level, save user, and check badge unlocks.
     */
    protected function syncUserTotals(User $user): void
    {
        $user->total_points = $user->workout_points + $user->meal_points + $user->hydration_points;
        $user->level = self::levelFromPoints($user->total_points);
        $user->save();

        // Refresh badge unlocks if BadgeService is available
        $this->syncBadges($user);
    }

    /**
     * Recalculate all user points from scratch based on actual completed daily items and water logs.
     */
    public function recalculateUserPoints(User $user): array
    {
        $workoutPoints = (int) UserDailyItem::query()
            ->whereHas('schedule', fn ($q) => $q->where('user_id', $user->id))
            ->where('type', 'workout')
            ->where('is_completed', true)
            ->where('status', '!=', 'voided')
            ->sum('points');

        $mealPoints = (int) UserDailyItem::query()
            ->whereHas('schedule', fn ($q) => $q->where('user_id', $user->id))
            ->where('type', 'meal')
            ->where('is_completed', true)
            ->where('status', '!=', 'voided')
            ->sum('points');

        $hydrationDaysCompleted = (int) UserDailyWaterLog::query()
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->count();

        $hydrationPoints = $hydrationDaysCompleted * self::HYDRATION_GOAL_POINTS;

        $user->workout_points = $workoutPoints;
        $user->meal_points = $mealPoints;
        $user->hydration_points = $hydrationPoints;
        $user->total_points = $workoutPoints + $mealPoints + $hydrationPoints;
        $user->level = self::levelFromPoints($user->total_points);
        $user->save();

        $this->syncBadges($user);

        return $this->getSummary($user);
    }

    /**
     * Get complete points and level summary for the authenticated user.
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
     * Get points history aggregated by week for trend chart.
     */
    public function getPointsHistory(User $user, int $weeks = 12): array
    {
        $startDate = Carbon::today()->subWeeks($weeks)->startOfWeek();
        $endDate = Carbon::today()->endOfWeek();

        // 1. Fetch completed items with date
        $workoutItems = UserDailyItem::query()
            ->join('user_daily_schedules', 'user_daily_items.daily_schedule_id', '=', 'user_daily_schedules.id')
            ->where('user_daily_schedules.user_id', $user->id)
            ->where('user_daily_items.type', 'workout')
            ->where('user_daily_items.is_completed', true)
            ->where('user_daily_items.status', '!=', 'voided')
            ->whereBetween('user_daily_schedules.date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select('user_daily_schedules.date', 'user_daily_items.points')
            ->get();

        $mealItems = UserDailyItem::query()
            ->join('user_daily_schedules', 'user_daily_items.daily_schedule_id', '=', 'user_daily_schedules.id')
            ->where('user_daily_schedules.user_id', $user->id)
            ->where('user_daily_items.type', 'meal')
            ->where('user_daily_items.is_completed', true)
            ->where('user_daily_items.status', '!=', 'voided')
            ->whereBetween('user_daily_schedules.date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select('user_daily_schedules.date', 'user_daily_items.points')
            ->get();

        $waterLogs = UserDailyWaterLog::query()
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get(['date']);

        $weeklyMap = [];
        $currentCursor = $startDate->copy();

        while ($currentCursor <= $endDate) {
            $weekKey = $currentCursor->format('Y-W');
            $weekEnd = $currentCursor->copy()->endOfWeek();

            $weeklyMap[$weekKey] = [
                'date' => $weekEnd->format('M d'),
                'fullDate' => $weekEnd->format('Y-m-d'),
                'workout_points' => 0,
                'meal_points' => 0,
                'hydration_points' => 0,
                'points_earned' => 0,
                'total_score' => 0, // for backwards-friendly chart axis if needed
            ];

            $currentCursor->addWeek();
        }

        foreach ($workoutItems as $item) {
            $key = Carbon::parse($item->date)->format('Y-W');
            if (isset($weeklyMap[$key])) {
                $weeklyMap[$key]['workout_points'] += (int) $item->points;
                $weeklyMap[$key]['points_earned'] += (int) $item->points;
            }
        }

        foreach ($mealItems as $item) {
            $key = Carbon::parse($item->date)->format('Y-W');
            if (isset($weeklyMap[$key])) {
                $weeklyMap[$key]['meal_points'] += (int) $item->points;
                $weeklyMap[$key]['points_earned'] += (int) $item->points;
            }
        }

        foreach ($waterLogs as $log) {
            $key = Carbon::parse($log->date)->format('Y-W');
            if (isset($weeklyMap[$key])) {
                $weeklyMap[$key]['hydration_points'] += self::HYDRATION_GOAL_POINTS;
                $weeklyMap[$key]['points_earned'] += self::HYDRATION_GOAL_POINTS;
            }
        }

        // Calculate cumulative points curve over time
        $runningTotal = 0;
        $history = [];

        foreach ($weeklyMap as $entry) {
            $runningTotal += $entry['points_earned'];
            $entry['total_points'] = $runningTotal;
            $entry['total_score'] = $entry['points_earned']; // primary metric plotted on chart
            $entry['level'] = self::levelFromPoints($runningTotal);
            $history[] = $entry;
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
            // Silently capture any badge sync issues so core item tracking does not fail
        }
    }
}

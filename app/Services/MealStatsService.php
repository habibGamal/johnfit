<?php

namespace App\Services;

use App\Models\Meal;
use App\Models\User;
use App\Models\UserDailyItem;
use Carbon\Carbon;

class MealStatsService
{
    /**
     * Get all meal plan statistics for a user.
     */
    public function getMealStats(User $user): array
    {
        return [
            'weeklyCompletionRate' => $this->getWeeklyCompletionRate($user),
            'currentStreak' => $this->getCurrentStreak($user),
            'mostActiveDays' => $this->getMostActiveDays($user),
            'recentActivity' => $this->getRecentActivity($user),
            'progressOverTime' => $this->getProgressOverTime($user),
            'aggregateStats' => $this->getAggregateStats($user),
            'nutritionAverages' => $this->getNutritionAverages($user),
            'comparisonStats' => $this->getComparisonStats($user),
            'macroDistribution' => $this->getMacroDistribution($user),
        ];
    }

    /**
     * Get the weekly meal completion rate for a user.
     */
    private function getWeeklyCompletionRate(User $user): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $items = UserDailyItem::whereHas('schedule', function ($q) use ($user, $startOfWeek, $endOfWeek) {
            $q->where('user_id', $user->id)
                ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()]);
        })
            ->where('type', 'meal')
            ->where('status', '!=', 'voided')
            ->get();

        $completed = $items->where('is_completed', true)->count();
        $total = $items->count();
        $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;

        return [
            'completed' => $completed,
            'total' => $total,
            'percentage' => $percentage,
        ];
    }

    /**
     * Get current streak of consecutive days with meal tracking
     */
    private function getCurrentStreak(User $user): int
    {
        $streak = 0;
        $date = Carbon::now();

        while (true) {
            $dateStr = $date->toDateString();
            $hasMeal = UserDailyItem::whereHas('schedule', function ($q) use ($user, $dateStr) {
                $q->where('user_id', $user->id)->where('date', $dateStr);
            })
                ->where('type', 'meal')
                ->where('is_completed', true)
                ->exists();

            if (! $hasMeal) {
                break;
            }

            $streak++;
            $date->subDay();
        }

        return $streak;
    }

    /**
     * Get most active meal tracking days of the week.
     */
    private function getMostActiveDays(User $user): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $items = UserDailyItem::with('schedule')
            ->whereHas('schedule', function ($q) use ($user, $startOfWeek, $endOfWeek) {
                $q->where('user_id', $user->id)
                    ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()]);
            })
            ->where('type', 'meal')
            ->where('is_completed', true)
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->schedule->date)->format('l');
            });

        $dayStats = [];
        foreach ($items as $day => $group) {
            $dayStats[$day] = $group->count();
        }

        return $dayStats;
    }

    /**
     * Get recent meal activity summary
     */
    public function getRecentActivity(User $user, int $limit = 5): array
    {
        return UserDailyItem::with('schedule')
            ->whereHas('schedule', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('type', 'meal')
            ->where('is_completed', true)
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'day' => Carbon::parse($item->schedule->date)->format('l'),
                    'meal' => $item->item_name,
                    'plan_name' => 'Daily Meal Plan',
                    'completed_at' => $item->completed_at ? $item->completed_at->diffForHumans() : 'Recently',
                ];
            })
            ->toArray();
    }

    /**
     * Get progress over time (last 4 weeks)
     */
    private function getProgressOverTime(User $user): array
    {
        $startDate = Carbon::now()->subWeeks(4)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $items = UserDailyItem::with('schedule')
            ->whereHas('schedule', function ($q) use ($user, $startDate, $endDate) {
                $q->where('user_id', $user->id)
                    ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->where('type', 'meal')
            ->where('is_completed', true)
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->schedule->date)->format('Y-m-d');
            });

        $progressData = [];
        for ($i = 28; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $progressData[] = [
                'date' => $date,
                'count' => isset($items[$date]) ? $items[$date]->count() : 0,
            ];
        }

        return $progressData;
    }

    /**
     * Get aggregate statistics for meals
     */
    private function getAggregateStats(User $user): array
    {
        $totalCompletions = UserDailyItem::whereHas('schedule', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->where('type', 'meal')
            ->where('is_completed', true)
            ->count();

        $recentCompletions = UserDailyItem::whereHas('schedule', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('date', '>=', now()->subDays(7)->toDateString());
        })
            ->where('type', 'meal')
            ->where('is_completed', true)
            ->count();

        return [
            'total_completions' => $totalCompletions,
            'active_plans' => 1,
            'recent_completions' => $recentCompletions,
        ];
    }

    /**
     * Get average daily nutrition metrics
     */
    private function getNutritionAverages(User $user): array
    {
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();

        $items = UserDailyItem::whereHas('schedule', function ($q) use ($user, $startDate, $endDate) {
            $q->where('user_id', $user->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
        })
            ->where('type', 'meal')
            ->where('is_completed', true)
            ->get();

        $totalCalories = 0;
        $totalProtein = 0;
        $totalCarbs = 0;
        $totalFat = 0;

        foreach ($items as $item) {
            $details = $item->target_details['primary_option'] ?? [];
            $baseQty = (float) ($details['quantity'] ?? 100);
            $consumedQty = (float) ($item->execution_payload['consumed_quantity'] ?? $baseQty);
            $ratio = $baseQty > 0 ? $consumedQty / $baseQty : 1;

            $totalCalories += ($details['calories'] ?? 0) * $ratio;
            $totalProtein += ($details['protein'] ?? 0) * $ratio;
            $totalCarbs += ($details['carbs'] ?? 0) * $ratio;
            $totalFat += ($details['fat'] ?? 0) * $ratio;
        }

        $days = 7;

        return [
            'calories' => round($totalCalories / $days),
            'protein' => round($totalProtein / $days),
            'carbs' => round($totalCarbs / $days),
            'fat' => round($totalFat / $days),
        ];
    }

    /**
     * Get comparison stats (this week vs last week)
     */
    private function getComparisonStats(User $user): array
    {
        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();

        $thisWeekCount = UserDailyItem::whereHas('schedule', function ($q) use ($user, $thisWeekStart, $thisWeekEnd) {
            $q->where('user_id', $user->id)->whereBetween('date', [$thisWeekStart->toDateString(), $thisWeekEnd->toDateString()]);
        })
            ->where('type', 'meal')
            ->where('is_completed', true)
            ->count();

        $lastWeekCount = UserDailyItem::whereHas('schedule', function ($q) use ($user, $lastWeekStart, $lastWeekEnd) {
            $q->where('user_id', $user->id)->whereBetween('date', [$lastWeekStart->toDateString(), $lastWeekEnd->toDateString()]);
        })
            ->where('type', 'meal')
            ->where('is_completed', true)
            ->count();

        $percentageChange = 0;
        $trend = 'neutral';

        if ($lastWeekCount > 0) {
            $percentageChange = round((($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100);
            $trend = $percentageChange > 0 ? 'up' : ($percentageChange < 0 ? 'down' : 'neutral');
        } elseif ($thisWeekCount > 0) {
            $percentageChange = 100;
            $trend = 'up';
        }

        return [
            'this_week' => $thisWeekCount,
            'last_week' => $lastWeekCount,
            'percentage_change' => abs($percentageChange),
            'trend' => $trend,
        ];
    }

    /**
     * Get macro distribution breakdown
     */
    private function getMacroDistribution(User $user): array
    {
        $averages = $this->getNutritionAverages($user);
        $total = ($averages['protein'] * 4) + ($averages['carbs'] * 4) + ($averages['fat'] * 9);

        if ($total <= 0) {
            return [
                ['name' => 'Protein', 'value' => 30, 'color' => '#3B82F6'],
                ['name' => 'Carbs', 'value' => 45, 'color' => '#10B981'],
                ['name' => 'Fat', 'value' => 25, 'color' => '#F59E0B'],
            ];
        }

        return [
            [
                'name' => 'Protein',
                'value' => round((($averages['protein'] * 4) / $total) * 100),
                'color' => '#3B82F6',
            ],
            [
                'name' => 'Carbs',
                'value' => round((($averages['carbs'] * 4) / $total) * 100),
                'color' => '#10B981',
            ],
            [
                'name' => 'Fat',
                'value' => round((($averages['fat'] * 9) / $total) * 100),
                'color' => '#F59E0B',
            ],
        ];
    }
}

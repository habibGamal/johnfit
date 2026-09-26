<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDailyItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WorkoutStatsService
{
    public function __construct(
        protected StreakService $streakService
    ) {}

    /**
     * Get all workout statistics for a user.
     */
    public function getWorkoutStats(User $user): array
    {
        return [
            'weeklyCompletionRate' => $this->getWeeklyCompletionRate($user),
            'currentStreak' => $this->getCurrentStreak($user),
            'mostActiveDays' => $this->getMostActiveDays($user),
            'recentActivity' => $this->getRecentActivity($user),
            'progressOverTime' => $this->getProgressOverTime($user),
            'aggregateStats' => $this->getAggregateStats($user),
            'comparisonStats' => $this->getComparisonStats($user),
        ];
    }

    /**
     * Get the weekly completion rate for a user
     */
    public function getWeeklyCompletionRate(User $user): array
    {
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        $items = UserDailyItem::whereHas('schedule', function ($q) use ($user, $startDate, $endDate) {
            $q->where('user_id', $user->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
        })
            ->where('type', 'workout')
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
     * Get the current streak of consecutive workout days.
     *
     * Delegates to the persisted streak record so the value stays consistent
     * between the dashboard, the schedule page and badge evaluation.
     */
    public function getCurrentStreak(User $user): int
    {
        return (int) $this->streakService->sync($user, 'workout')->current_streak;
    }

    /**
     * Get most active workout days of the week
     */
    public function getMostActiveDays(User $user): array
    {
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        $items = UserDailyItem::with('schedule')
            ->whereHas('schedule', function ($q) use ($user, $startDate, $endDate) {
                $q->where('user_id', $user->id)
                    ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->where('type', 'workout')
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
     * Get recent activity summary
     */
    public function getRecentActivity(User $user, int $limit = 5): Collection
    {
        return UserDailyItem::with('schedule')
            ->whereHas('schedule', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('type', 'workout')
            ->where('is_completed', true)
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'day' => Carbon::parse($item->schedule->date)->format('l'),
                    'workout' => $item->item_name,
                    'plan_name' => 'Daily Workout',
                    'completed_at' => $item->completed_at ? $item->completed_at->diffForHumans() : 'Recently',
                ];
            });
    }

    /**
     * Get progress over time (last 4 weeks)
     */
    public function getProgressOverTime(User $user): array
    {
        $startDate = now()->subWeeks(4)->startOfDay();
        $endDate = now()->endOfDay();

        $items = UserDailyItem::with('schedule')
            ->whereHas('schedule', function ($q) use ($user, $startDate, $endDate) {
                $q->where('user_id', $user->id)
                    ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->where('type', 'workout')
            ->where('is_completed', true)
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->schedule->date)->format('Y-m-d');
            });

        $progressData = [];
        for ($i = 28; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $progressData[] = [
                'date' => $date,
                'count' => isset($items[$date]) ? $items[$date]->count() : 0,
            ];
        }

        return $progressData;
    }

    /**
     * Get aggregate statistics
     */
    public function getAggregateStats(User $user): array
    {
        $totalCompletions = UserDailyItem::whereHas('schedule', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->where('type', 'workout')
            ->where('is_completed', true)
            ->count();

        $recentCompletions = UserDailyItem::whereHas('schedule', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('date', '>=', now()->subDays(7)->toDateString());
        })
            ->where('type', 'workout')
            ->where('is_completed', true)
            ->count();

        return [
            'total_completions' => $totalCompletions,
            'active_plans' => 1,
            'recent_completions' => $recentCompletions,
        ];
    }

    /**
     * Get comparison stats (this week vs last week)
     */
    public function getComparisonStats(User $user): array
    {
        $thisWeekStart = now()->startOfWeek();
        $thisWeekEnd = now()->endOfWeek();
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        $thisWeekCount = UserDailyItem::whereHas('schedule', function ($q) use ($user, $thisWeekStart, $thisWeekEnd) {
            $q->where('user_id', $user->id)->whereBetween('date', [$thisWeekStart->toDateString(), $thisWeekEnd->toDateString()]);
        })
            ->where('type', 'workout')
            ->where('is_completed', true)
            ->count();

        $lastWeekCount = UserDailyItem::whereHas('schedule', function ($q) use ($user, $lastWeekStart, $lastWeekEnd) {
            $q->where('user_id', $user->id)->whereBetween('date', [$lastWeekStart->toDateString(), $lastWeekEnd->toDateString()]);
        })
            ->where('type', 'workout')
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
}

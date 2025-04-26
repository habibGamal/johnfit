<?php

namespace App\Services;

use App\Models\WorkoutCompletion;
use App\Models\WorkoutPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WorkoutStatsService
{
    /**
     * Get all workout statistics for a user.
     *
     * @param User $user
     * @return array
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
        ];
    }

    /**
     * Get the weekly completion rate for a user
     *
     * @param User $user
     * @param int $daysBack
     * @return array
     */
    public function getWeeklyCompletionRate(User $user, int $daysBack = 7): array
    {
        $startDate = now()->subDays($daysBack)->startOfDay();
        $endDate = now()->endOfDay();

        // Get all workout completions for the user in the date range
        $completions = WorkoutCompletion::where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->get();

        $completed = $completions->where('completed', true)->count();
        $total = $completions->count();

        $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;

        return [
            'completed' => $completed,
            'total' => $total,
            'percentage' => $percentage,
        ];
    }

    /**
     * Get the current streak of consecutive workout days
     *
     * @param User $user
     * @return int
     */
    public function getCurrentStreak(User $user): int
    {
        $streak = 0;
        $date = now();

        while (true) {
            $hasWorkoutOnDate = WorkoutCompletion::where('user_id', $user->id)
                ->where('completed', true)
                ->whereDate('updated_at', $date->format('Y-m-d'))
                ->exists();

            if (!$hasWorkoutOnDate) {
                break;
            }

            $streak++;
            $date = $date->subDay();
        }

        return $streak;
    }

    /**
     * Get most active workout days of the week
     *
     * @param User $user
     * @return array
     */
    public function getMostActiveDays(User $user): array
    {
        $completions = WorkoutCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->get()
            ->groupBy(function ($completion) {
                return Carbon::parse($completion->updated_at)->format('l'); // Day name
            });

        $dayStats = [];

        foreach ($completions as $day => $items) {
            $dayStats[$day] = $items->count();
        }

        arsort($dayStats); // Sort by count in descending order

        return $dayStats;
    }

    /**
     * Get recent activity summary
     *
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getRecentActivity(User $user, int $limit = 5): Collection
    {
        return WorkoutCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->with(['workoutPlan'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($completion) {
                // Get the workout data if possible
                $workoutName = DB::table('workouts')
                    ->where('id', $completion->workout_id)
                    ->value('name') ?? 'Unknown Workout';

                return [
                    'day' => $completion->day,
                    'workout' => $workoutName,
                    'plan_name' => $completion->workoutPlan->name ?? 'Unknown Plan',
                    'completed_at' => $completion->updated_at->diffForHumans(),
                ];
            });
    }

    /**
     * Get progress over time (last 4 weeks)
     *
     * @param User $user
     * @return array
     */
    public function getProgressOverTime(User $user): array
    {
        $startDate = now()->subWeeks(4)->startOfDay();
        $endDate = now()->endOfDay();

        $completions = WorkoutCompletion::where('user_id', $user->id)
            ->where('updated_at', '>=', $startDate)
            ->where('updated_at', '<=', $endDate)
            ->where('completed', true)
            ->get()
            ->groupBy(function ($completion) {
                return Carbon::parse($completion->updated_at)->format('Y-m-d');
            });

        $progressData = [];

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            $formattedDate = $currentDate->format('M d');

            $progressData[] = [
                'date' => $formattedDate,
                'count' => $completions->get($dateString) ? count($completions->get($dateString)) : 0
            ];

            $currentDate->addDay();
        }

        return $progressData;
    }

    /**
     * Get aggregate stats for a user
     *
     * @param User $user
     * @return array
     */
    public function getAggregateStats(User $user): array
    {
        // Total workouts completed ever
        $totalCompletions = WorkoutCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->count();

        // Total active plans
        $activePlans = $user->workoutPlans()->count();

        // Recently completed workouts (last 7 days)
        $recentCompletions = WorkoutCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->where('updated_at', '>=', now()->subDays(7))
            ->count();

        return [
            'total_completions' => $totalCompletions,
            'active_plans' => $activePlans,
            'recent_completions' => $recentCompletions,
        ];
    }
}

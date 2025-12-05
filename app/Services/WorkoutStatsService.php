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
            'comparisonStats' => $this->getComparisonStats($user),
            'achievements' => $this->getAchievements($user),
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

    /**
     * Get comparison stats (this week vs last week)
     *
     * @param User $user
     * @return array
     */
    public function getComparisonStats(User $user): array
    {
        // This Week
        $startOfThisWeek = now()->startOfWeek();
        $endOfThisWeek = now()->endOfWeek();

        $thisWeekCount = WorkoutCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->where('updated_at', '>=', $startOfThisWeek)
            ->where('updated_at', '<=', $endOfThisWeek)
            ->count();

        // Last Week
        $startOfLastWeek = now()->subWeek()->startOfWeek();
        $endOfLastWeek = now()->subWeek()->endOfWeek();

        $lastWeekCount = WorkoutCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->where('updated_at', '>=', $startOfLastWeek)
            ->where('updated_at', '<=', $endOfLastWeek)
            ->count();

        // Avoid division by zero
        if ($lastWeekCount == 0) {
            $percentageChange = $thisWeekCount > 0 ? 100 : 0;
        } else {
            $percentageChange = round((($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100);
        }

        return [
            'this_week' => $thisWeekCount,
            'last_week' => $lastWeekCount,
            'percentage_change' => $percentageChange,
            'trend' => $percentageChange > 0 ? 'up' : ($percentageChange < 0 ? 'down' : 'neutral')
        ];
    }

    /**
     * Get user achievements based on stats
     *
     * @param User $user
     * @return array
     */
    public function getAchievements(User $user): array
    {
        $achievements = [];
        $totalCompletions = WorkoutCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->count();

        $streak = $this->getCurrentStreak($user);

        // Define potential achievements
        $definitions = [
            [
                'id' => 'first_step',
                'title' => 'First Step',
                'description' => 'Completed your first workout',
                'icon' => 'Target', // Lucide icon name
                'condition' => $totalCompletions >= 1,
                'progress' => min($totalCompletions, 1) / 1 * 100,
                'tier' => 'bronze'
            ],
            [
                'id' => 'getting_serious',
                'title' => 'Getting Serious',
                'description' => 'Completed 10 workouts',
                'icon' => 'Dumbbell',
                'condition' => $totalCompletions >= 10,
                'progress' => min($totalCompletions, 10) / 10 * 100,
                'tier' => 'silver'
            ],
            [
                'id' => 'workout_warrior',
                'title' => 'Workout Warrior',
                'description' => 'Completed 50 workouts',
                'icon' => 'Trophy',
                'condition' => $totalCompletions >= 50,
                'progress' => min($totalCompletions, 50) / 50 * 100,
                'tier' => 'gold'
            ],
            [
                'id' => 'week_streak',
                'title' => 'On Fire',
                'description' => '7 day workout streak',
                'icon' => 'Flame',
                'condition' => $streak >= 7,
                'progress' => min($streak, 7) / 7 * 100,
                'tier' => 'gold'
            ],
            [
                'id' => 'consistent',
                'title' => 'Consistency',
                'description' => '3 day workout streak',
                'icon' => 'Zap',
                'condition' => $streak >= 3,
                'progress' => min($streak, 3) / 3 * 100,
                'tier' => 'bronze'
            ]
        ];

        foreach ($definitions as $def) {
            if ($def['condition']) {
                $achievements[] = array_merge($def, ['unlocked' => true]);
            } else {
                $achievements[] = array_merge($def, ['unlocked' => false]);
            }
        }

        // Sort: Unlocked first, then by progress
        usort($achievements, function ($a, $b) {
            if ($a['unlocked'] === $b['unlocked']) {
                return $b['progress'] <=> $a['progress'];
            }
            return $b['unlocked'] <=> $a['unlocked'];
        });

        return $achievements;
    }
}

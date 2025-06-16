<?php

namespace App\Services;

use App\Models\Meal;
use App\Models\MealCompletion;
use App\Models\MealPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MealStatsService
{
    /**
     * Get all meal plan statistics for a user.
     *
     * @param User $user
     * @return array
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
        ];
    }

    /**
     * Get the weekly meal completion rate for a user.
     *
     * @param User $user
     * @return array
     */
    private function getWeeklyCompletionRate(User $user): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Get all meals completed this week
        $completedMeals = MealCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        // Get count of active meal plans
        $activeMealPlans = $user->mealPlans()->count();

        // Estimate total meals for the week (assume avg 3 meals per day per plan)
        $daysInWeek = 7;
        $avgMealsPerDay = 3;
        $totalPossibleMeals = $activeMealPlans * $daysInWeek * $avgMealsPerDay;

        // Safety check to avoid division by zero
        $totalPossibleMeals = max($totalPossibleMeals, 1);

        $percentage = round(($completedMeals / $totalPossibleMeals) * 100);

        return [
            'completed' => $completedMeals,
            'total' => $totalPossibleMeals,
            'percentage' => $percentage,
        ];
    }

    /**
     * Get the current streak of consecutive days with meal completions.
     *
     * @param User $user
     * @return int
     */
    private function getCurrentStreak(User $user): int
    {
        $streak = 0;
        $today = Carbon::today();
        $currentDate = $today->copy();
        $hasCompletionToday = false;

        // Check if there's at least one completion today
        $hasCompletionToday = MealCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->whereDate('created_at', $today)
            ->exists();

        // If no completion today, start checking from yesterday
        if (!$hasCompletionToday) {
            $currentDate->subDay();
        }

        // Count consecutive days with at least one completed meal
        while (true) {
            $hasCompletion = MealCompletion::where('user_id', $user->id)
                ->where('completed', true)
                ->whereDate('created_at', $currentDate)
                ->exists();

            if (!$hasCompletion) {
                break;
            }

            $streak++;
            $currentDate->subDay();
        }

        return $streak;
    }

    /**
     * Get the most active days for meal consumption.
     *
     * @param User $user
     * @return array
     */
    private function getMostActiveDays(User $user): array
    {
        $completions = MealCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->whereNotNull('created_at')
            ->get();

        $dayCount = [
            'Monday' => 0,
            'Tuesday' => 0,
            'Wednesday' => 0,
            'Thursday' => 0,
            'Friday' => 0,
            'Saturday' => 0,
            'Sunday' => 0,
        ];

        foreach ($completions as $completion) {
            $dayName = Carbon::parse($completion->created_at)->format('l');
            if (isset($dayCount[$dayName])) {
                $dayCount[$dayName]++;
            }
        }

        return $dayCount;
    }

    /**
     * Get recent meal activity for a user.
     *
     * @param User $user
     * @return array
     */
    private function getRecentActivity(User $user): array
    {
        $recentCompletions = MealCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $activities = [];

        foreach ($recentCompletions as $completion) {
            $mealPlan = MealPlan::find($completion->meal_plan_id);
            $meal = DB::table('meals')->where('id', $completion->meal_id)->first();

            if ($mealPlan && $meal) {
                $activities[] = [
                    'day' => $completion->day,
                    'meal' => $meal->name,
                    'plan_name' => $mealPlan->name,
                    'completed_at' => Carbon::parse($completion->created_at)->format('M d, Y · h:i A'),
                ];
            }
        }

        return $activities;
    }

    /**
     * Get meal completion progress over time.
     *
     * @param User $user
     * @return array
     */
    private function getProgressOverTime(User $user): array
    {
        $progress = [];
        $startDate = Carbon::now()->subDays(30);

        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            $count = MealCompletion::where('user_id', $user->id)
                ->where('completed', true)
                ->whereDate('created_at', $date)
                ->count();

            $progress[] = [
                'date' => $date->format('Y-m-d'),
                'count' => $count,
            ];
        }

        return $progress;
    }

    /**
     * Get aggregate meal stats for a user.
     *
     * @param User $user
     * @return array
     */
    private function getAggregateStats(User $user): array
    {
        $totalCompletions = MealCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->count();

        $activePlans = $user->mealPlans()->count();

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $recentCompletions = MealCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        return [
            'total_completions' => $totalCompletions,
            'active_plans' => $activePlans,
            'recent_completions' => $recentCompletions,
        ];
    }

    /**
     * Get average nutritional intake for a user.
     *
     * @param User $user
     * @return array
     */
    private function getNutritionAverages(User $user): array
    {
        // Get completed meals from the past 7 days
        $startDate = Carbon::now()->subDays(7);
        $completions = MealCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->where('created_at', '>=', $startDate)
            ->get();

        $dailyTotals = [];
        $nutritionSum = [
            'calories' => 0,
            'protein' => 0,
            'carbs' => 0,
            'fat' => 0
        ];
        foreach ($completions as $completion) {
            $day = Carbon::parse($completion->created_at)->format('Y-m-d');
            $meal = Meal::where('id', $completion->meal_id)->first();

            if ($meal) {
                $multiplier = $completion->quantity ?? 1; // Use the actual quantity from completion

                if (!isset($dailyTotals[$day])) {
                    $dailyTotals[$day] = [
                        'calories' => 0,
                        'protein' => 0,
                        'carbs' => 0,
                        'fat' => 0
                    ];
                }

                // Get macros from the meal
                $calories = $meal->calories ?? 0;
                $protein = $meal->protein ?? 0;
                $carbs = $meal->carbs ?? 0;
                $fat = $meal->fat ?? 0;

                $dailyTotals[$day]['calories'] += $calories * $multiplier;
                $dailyTotals[$day]['protein'] += $protein * $multiplier;
                $dailyTotals[$day]['carbs'] += $carbs * $multiplier;
                $dailyTotals[$day]['fat'] += $fat * $multiplier;
            }
        }

        $daysWithData = count($dailyTotals);

        if ($daysWithData > 0) {
            foreach ($dailyTotals as $dayTotal) {
                $nutritionSum['calories'] += $dayTotal['calories'];
                $nutritionSum['protein'] += $dayTotal['protein'];
                $nutritionSum['carbs'] += $dayTotal['carbs'];
                $nutritionSum['fat'] += $dayTotal['fat'];
            }

            $averages = [
                'calories' => round($nutritionSum['calories'] / $daysWithData),
                'protein' => round($nutritionSum['protein'] / $daysWithData, 1),
                'carbs' => round($nutritionSum['carbs'] / $daysWithData, 1),
                'fat' => round($nutritionSum['fat'] / $daysWithData, 1)
            ];
        } else {
            $averages = [
                'calories' => 0,
                'protein' => 0,
                'carbs' => 0,
                'fat' => 0
            ];
        }

        return $averages;
    }
}

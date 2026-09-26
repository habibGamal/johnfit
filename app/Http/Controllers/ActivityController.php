<?php

namespace App\Http\Controllers;

use App\Services\MealStatsService;
use App\Services\WorkoutStatsService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ActivityController extends Controller
{
    protected WorkoutStatsService $workoutStatsService;

    protected MealStatsService $mealStatsService;

    public function __construct(
        WorkoutStatsService $workoutStatsService,
        MealStatsService $mealStatsService
    ) {
        $this->workoutStatsService = $workoutStatsService;
        $this->mealStatsService = $mealStatsService;
    }

    /**
     * Display the separated recent activity page.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();

        // Retrieve extended recent activities for workouts and meals
        $recentWorkouts = $this->workoutStatsService->getRecentActivity($user, 40);
        $recentMeals = $this->mealStatsService->getRecentActivity($user, 40);

        // Completion rates & streaks for contextual summary
        $workoutRate = $this->workoutStatsService->getWeeklyCompletionRate($user);
        $mealRate = $this->mealStatsService->getWeeklyCompletionRate($user);
        $workoutStreak = $this->workoutStatsService->getCurrentStreak($user);
        $mealStreak = $this->mealStatsService->getCurrentStreak($user);

        return Inertia::render('Activity/Index', [
            'recentWorkouts' => $recentWorkouts,
            'recentMeals' => $recentMeals,
            'workoutRate' => $workoutRate,
            'mealRate' => $mealRate,
            'workoutStreak' => $workoutStreak,
            'mealStreak' => $mealStreak,
        ]);
    }
}

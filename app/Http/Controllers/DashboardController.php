<?php

namespace App\Http\Controllers;

use App\Services\WorkoutStatsService;
use App\Services\MealStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
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
     * Display the user dashboard with performance statistics.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();

        // Get workout statistics
        $workoutStats = $this->workoutStatsService->getWorkoutStats($user);

        // Get meal statistics
        $mealStats = $this->mealStatsService->getMealStats($user);

        return Inertia::render('Dashboard', [
            'workoutStats' => $workoutStats,
            'mealStats' => $mealStats
        ]);
    }
}

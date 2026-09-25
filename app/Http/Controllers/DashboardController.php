<?php

namespace App\Http\Controllers;

use App\Services\FitnessScoreService;
use App\Services\MealStatsService;
use App\Services\WaterIntakeService;
use App\Services\WorkoutStatsService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected WorkoutStatsService $workoutStatsService;

    protected MealStatsService $mealStatsService;

    protected FitnessScoreService $fitnessScoreService;

    protected WaterIntakeService $waterIntakeService;

    public function __construct(
        WorkoutStatsService $workoutStatsService,
        MealStatsService $mealStatsService,
        FitnessScoreService $fitnessScoreService,
        WaterIntakeService $waterIntakeService
    ) {
        $this->workoutStatsService = $workoutStatsService;
        $this->mealStatsService = $mealStatsService;
        $this->fitnessScoreService = $fitnessScoreService;
        $this->waterIntakeService = $waterIntakeService;
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

        // Get fitness score
        $fitnessScore = $this->fitnessScoreService->getScoreSummary($user);

        // Get fitness score history
        $fitnessScoreHistory = $this->fitnessScoreService->getScoreHistory($user, 12);

        // Get water intake data
        $waterLog = $this->waterIntakeService->getOrCreateDailyLog($user);
        $waterCalculation = $this->waterIntakeService->calculateDailyTarget($user);
        $waterWeeklyStats = $this->waterIntakeService->getWeeklyStats($user);

        return Inertia::render('Dashboard', [
            'workoutStats' => $workoutStats,
            'mealStats' => $mealStats,
            'fitnessScore' => $fitnessScore,
            'fitnessScoreHistory' => $fitnessScoreHistory,
            'hasActiveSubscription' => $user->hasActiveSubscription(),
            'activeSubscription' => $user->activeSubscription()?->load('plan'),
            'waterData' => [
                'log' => $waterLog,
                'calculation' => $waterCalculation,
                'weekly_stats' => $waterWeeklyStats,
            ],
        ]);
    }
}

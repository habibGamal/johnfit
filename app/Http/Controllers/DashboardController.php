<?php

namespace App\Http\Controllers;

use App\Services\BadgeService;
use App\Services\MealStatsService;
use App\Services\PointsService;
use App\Services\WaterIntakeService;
use App\Services\WorkoutStatsService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected WorkoutStatsService $workoutStatsService;

    protected MealStatsService $mealStatsService;

    protected WaterIntakeService $waterIntakeService;

    protected BadgeService $badgeService;

    protected PointsService $pointsService;

    protected \App\Services\PlanGenerationService $planGenerationService;

    public function __construct(
        WorkoutStatsService $workoutStatsService,
        MealStatsService $mealStatsService,
        WaterIntakeService $waterIntakeService,
        BadgeService $badgeService,
        PointsService $pointsService,
        \App\Services\PlanGenerationService $planGenerationService
    ) {
        $this->workoutStatsService = $workoutStatsService;
        $this->mealStatsService = $mealStatsService;
        $this->waterIntakeService = $waterIntakeService;
        $this->badgeService = $badgeService;
        $this->pointsService = $pointsService;
        $this->planGenerationService = $planGenerationService;
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

        // Get points summary & progression details
        $pointsSummary = $this->pointsService->getSummary($user);

        // Get daily points history for progression trend chart (last 30 days)
        $pointsHistory = $this->pointsService->getPointsHistory($user, 30);

        // Get water intake data
        $waterLog = $this->waterIntakeService->getOrCreateDailyLog($user);
        $waterCalculation = $this->waterIntakeService->calculateDailyTarget($user);
        $waterWeeklyStats = $this->waterIntakeService->getWeeklyStats($user);

        // Advance streaks and award any newly earned badges
        $this->badgeService->refresh($user);

        return Inertia::render('Dashboard', [
            'workoutStats' => $workoutStats,
            'mealStats' => $mealStats,
            'fitnessScore' => $pointsSummary,
            'fitnessScoreHistory' => $pointsHistory,
            'pointsSummary' => $pointsSummary,
            'pointsHistory' => $pointsHistory,
            'hasActiveSubscription' => $user->hasActiveSubscription(),
            'activeSubscription' => $user->activeSubscription()?->load(['plan', 'tier']),
            'achievementJourney' => $this->badgeService->getJourney($user),
            'waterData' => [
                'log' => $waterLog,
                'calculation' => $waterCalculation,
                'weekly_stats' => $waterWeeklyStats,
            ],
            'aiPlanEligibility' => $this->planGenerationService->getAiPlanEligibility($user),
        ]);
    }
}

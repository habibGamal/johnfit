<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Models\WorkoutSetCompletion;
use App\Services\ProgressionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function __construct(
        protected ProgressionService $progressionService
    ) {}

    /**
     * Display the progression analytics dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get user's workouts that have tracking data
        $trackedWorkoutIds = WorkoutSetCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->distinct()
            ->pluck('workout_id');

        $workouts = Workout::whereIn('id', $trackedWorkoutIds)
            ->select('id', 'name', 'muscles')
            ->get()
            ->map(fn ($w) => [
                'id' => $w->id,
                'name' => $w->name,
                'muscles' => $w->muscles,
            ]);

        // Get selected workout from request or use first available
        $selectedWorkoutId = $request->input('workout_id');
        if ($selectedWorkoutId) {
            $selectedWorkoutId = (int) $selectedWorkoutId;
        }

        // Get progression analytics data
        $progressData = $this->progressionService->getProgressPulseData($user, $selectedWorkoutId);

        return Inertia::render('Analytics/ProgressionDashboard', [
            'workouts' => $workouts,
            'selectedWorkoutId' => $selectedWorkoutId,
            'chartData' => $progressData['chart_data'],
            'muscleHeatmap' => $progressData['muscle_heatmap'],
            'intensityDelta' => $progressData['intensity_delta'],
            'consistencyScore' => $progressData['consistency_score'],
            'workoutAnalytics' => $progressData['workout_analytics'],
            'inbodyTrend' => $progressData['inbody_trend'],
        ]);
    }

    /**
     * Get analytics data for a specific workout (API endpoint).
     */
    public function workoutAnalytics(Request $request, Workout $workout)
    {
        $user = Auth::user();

        // Verify user has data for this workout
        $hasData = WorkoutSetCompletion::where('user_id', $user->id)
            ->where('workout_id', $workout->id)
            ->where('completed', true)
            ->exists();

        if (! $hasData) {
            return response()->json([
                'error' => 'No tracking data found for this workout.',
            ], 404);
        }

        $progressData = $this->progressionService->getProgressPulseData($user, $workout->id);

        return response()->json([
            'success' => true,
            'workout' => [
                'id' => $workout->id,
                'name' => $workout->name,
                'muscles' => $workout->muscles,
            ],
            ...$progressData,
        ]);
    }

    /**
     * Get personal bests for a workout.
     */
    public function personalBests(Request $request, Workout $workout)
    {
        $user = Auth::user();

        $pbs = $this->progressionService->detectPersonalBests($user, $workout->id);

        return response()->json([
            'success' => true,
            'workout_id' => $workout->id,
            ...$pbs,
        ]);
    }

    /**
     * Get muscle group volume distribution.
     */
    public function muscleDistribution(Request $request)
    {
        $user = Auth::user();

        $muscleHeatmap = $this->progressionService->getVolumePerMuscleGroup($user);

        return response()->json([
            'success' => true,
            'muscle_distribution' => $muscleHeatmap,
        ]);
    }
}

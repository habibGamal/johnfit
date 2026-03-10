<?php

namespace App\Http\Controllers;

use App\Services\FitnessScoreService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FitnessScoreController extends Controller
{
    public function __construct(
        private FitnessScoreService $scoreService
    ) {}

    /**
     * Get current fitness score for authenticated user.
     */
    public function current(Request $request): JsonResponse
    {
        $user = $request->user();
        $summary = $this->scoreService->getScoreSummary($user);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get fitness score history for trend charts.
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $weeks = $request->integer('weeks', 12);

        $history = $this->scoreService->getScoreHistory($user, $weeks);

        return response()->json([
            'success' => true,
            'data' => $history->map(fn ($score) => [
                'date' => $score->period_end->format('M d'),
                'fullDate' => $score->period_end->format('Y-m-d'),
                'total_score' => $score->total_score,
                'workout_score' => $score->workout_score,
                'meal_score' => $score->meal_score,
                'inbody_score' => $score->inbody_score,
                'level' => $score->level,
            ])->values(),
        ]);
    }

    /**
     * Recalculate fitness score for current period.
     */
    public function recalculate(Request $request): JsonResponse
    {
        $user = $request->user();
        $periodDays = $request->integer('period_days', 7);

        $score = $this->scoreService->calculateScore($user, Carbon::today(), $periodDays);

        return response()->json([
            'success' => true,
            'message' => 'Score recalculated successfully',
            'data' => $this->scoreService->getScoreSummary($user),
        ]);
    }
}

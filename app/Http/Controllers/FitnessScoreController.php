<?php

namespace App\Http\Controllers;

use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FitnessScoreController extends Controller
{
    public function __construct(
        private PointsService $pointsService
    ) {}

    /**
     * Get current points and level summary for authenticated user.
     */
    public function current(Request $request): JsonResponse
    {
        $user = $request->user();
        $summary = $this->pointsService->getSummary($user);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get points history for trend charts.
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $weeks = $request->integer('weeks', 12);

        $history = $this->pointsService->getPointsHistory($user, $weeks);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Recalculate user points and level from completion state.
     */
    public function recalculate(Request $request): JsonResponse
    {
        $user = $request->user();
        $summary = $this->pointsService->recalculateUserPoints($user);

        return response()->json([
            'success' => true,
            'message' => 'Points and levels recalculated successfully',
            'data' => $summary,
        ]);
    }
}

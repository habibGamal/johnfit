<?php

namespace App\Http\Controllers;

use App\Services\WaterIntakeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaterIntakeController extends Controller
{
    public function __construct(
        protected WaterIntakeService $waterService
    ) {}

    /**
     * Get current water data for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $date = $request->query('date') ? Carbon::parse($request->query('date')) : Carbon::today();

        $log = $this->waterService->getOrCreateDailyLog($user, $date);
        $targetBreakdown = $this->waterService->calculateDailyTarget($user, $date);
        $weeklyStats = $this->waterService->getWeeklyStats($user, $date);

        return response()->json([
            'log' => $log,
            'calculation' => $targetBreakdown,
            'weekly_stats' => $weeklyStats,
        ]);
    }

    /**
     * Log water intake.
     */
    public function log(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'amount_ml' => 'required|integer|min:50|max:3000',
            'container_type' => 'nullable|string|in:cup,bottle,shaker,custom',
            'date' => 'nullable|date',
        ]);

        $user = Auth::user();
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        $log = $this->waterService->logIntake(
            $user,
            (int) $request->input('amount_ml'),
            $request->input('container_type', 'cup'),
            $date
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'log' => $log,
            ]);
        }

        return redirect()->back();
    }

    /**
     * Delete an intake entry (Undo).
     */
    public function deleteEntry(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $log = $this->waterService->deleteEntry($user, $id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'log' => $log,
            ]);
        }

        return redirect()->back();
    }

    /**
     * Set or reset custom target.
     */
    public function setTarget(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'custom_target_ml' => 'nullable|integer|min:1000|max:8000',
            'date' => 'nullable|date',
        ]);

        $user = Auth::user();
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();
        $target = $request->has('custom_target_ml') && $request->input('custom_target_ml') !== null
            ? (int) $request->input('custom_target_ml')
            : null;

        $log = $this->waterService->setCustomTarget($user, $target, $date);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'log' => $log,
            ]);
        }

        return redirect()->back();
    }
}

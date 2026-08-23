<?php

namespace App\Http\Controllers;

use App\Services\DailyItemTrackingService;
use App\Services\DailyScheduleService;
use App\Services\PlanAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    public function __construct(
        protected DailyScheduleService $scheduleService,
        protected DailyItemTrackingService $itemTrackingService,
        protected PlanAssignmentService $assignmentService
    ) {}

    /**
     * Display the daily schedule and tracking hub for the given date.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $dateParam = $request->query('date', Carbon::today()->toDateString());

        try {
            $currentDate = Carbon::parse($dateParam);
        } catch (\Exception $e) {
            $currentDate = Carbon::today();
        }

        $schedule = $this->scheduleService->getScheduleForDate($user, $currentDate);
        $weeklyAdherence = $this->scheduleService->getWeeklyAdherence($user, $currentDate);
        $currentStreak = $this->scheduleService->getCurrentStreak($user);

        return Inertia::render('Schedule/Index', [
            'selectedDate' => $currentDate->toDateString(),
            'schedule' => $schedule ? [
                'id' => $schedule->id,
                'date' => $schedule->date->format('Y-m-d'),
                'target_score' => $schedule->target_score,
                'earned_score' => $schedule->earned_score,
                'adherence_percentage' => $schedule->adherence_percentage,
                'is_completed' => $schedule->is_completed,
                'is_locked' => $schedule->is_locked,
                'notes' => $schedule->notes,
                'items' => $schedule->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => $item->type,
                        'item_name' => $item->item_name,
                        'reference_id' => $item->reference_id,
                        'target_details' => $item->target_details,
                        'points' => $item->points,
                        'is_completed' => $item->is_completed,
                        'completed_at' => $item->completed_at?->toISOString(),
                        'execution_payload' => $item->execution_payload,
                        'status' => $item->status,
                        'order_index' => $item->order_index,
                    ];
                })->values(),
            ] : null,
            'weeklyAdherence' => $weeklyAdherence,
            'currentStreak' => $currentStreak,
        ]);
    }

    /**
     * Toggle item completion.
     */
    public function toggleItem(Request $request, int $item): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $status = $request->has('completed') ? $request->boolean('completed') : null;

        $updatedItem = $this->itemTrackingService->toggleItemCompletion($user, $item, $status);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => $updatedItem,
                'schedule' => [
                    'target_score' => $updatedItem->schedule->target_score,
                    'earned_score' => $updatedItem->schedule->earned_score,
                    'adherence_percentage' => $updatedItem->schedule->adherence_percentage,
                    'is_completed' => $updatedItem->schedule->is_completed,
                ],
            ]);
        }

        return redirect()->back();
    }

    /**
     * Save workout sets execution payload.
     */
    public function saveWorkoutSets(Request $request, int $item): JsonResponse|RedirectResponse
    {
        $request->validate([
            'sets' => 'required|array',
            'sets.*.set_number' => 'required|integer|min:1',
            'sets.*.reps' => 'nullable|integer|min:0',
            'sets.*.weight' => 'nullable|numeric|min:0',
            'sets.*.completed' => 'nullable|boolean',
            'selected_workout_id' => 'nullable|integer',
        ]);

        $user = Auth::user();
        $updatedItem = $this->itemTrackingService->saveWorkoutSets(
            $user,
            $item,
            $request->input('sets'),
            $request->input('selected_workout_id')
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => $updatedItem,
                'schedule' => [
                    'target_score' => $updatedItem->schedule->target_score,
                    'earned_score' => $updatedItem->schedule->earned_score,
                    'adherence_percentage' => $updatedItem->schedule->adherence_percentage,
                    'is_completed' => $updatedItem->schedule->is_completed,
                ],
            ]);
        }

        return redirect()->back();
    }

    /**
     * Save meal consumption execution payload.
     */
    public function saveMealConsumption(Request $request, int $item): JsonResponse|RedirectResponse
    {
        $request->validate([
            'option_id' => 'required|integer',
            'quantity' => 'nullable|numeric|min:0.1',
        ]);

        $user = Auth::user();
        $updatedItem = $this->itemTrackingService->saveMealConsumption(
            $user,
            $item,
            $request->input('option_id'),
            (float) $request->input('quantity', 1.0)
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => $updatedItem,
                'schedule' => [
                    'target_score' => $updatedItem->schedule->target_score,
                    'earned_score' => $updatedItem->schedule->earned_score,
                    'adherence_percentage' => $updatedItem->schedule->adherence_percentage,
                    'is_completed' => $updatedItem->schedule->is_completed,
                ],
            ]);
        }

        return redirect()->back();
    }
}

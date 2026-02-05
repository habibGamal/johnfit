<?php

namespace App\Http\Controllers;

use App\Models\WorkoutPlan;
use App\Services\WorkoutTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WorkoutPlanController extends Controller
{
    protected WorkoutTrackingService $trackingService;

    public function __construct(WorkoutTrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Display the user's assigned workout plans.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();
        $workoutPlans = $user->workoutPlans()->get();

        $formattedWorkoutPlans = $this->trackingService->formatWorkoutPlansForUser($workoutPlans, $user);

        return Inertia::render('WorkoutPlans/Index', [
            'workoutPlans' => $formattedWorkoutPlans,
        ]);
    }

    /**
     * Toggle workout completion status.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleCompletion(Request $request)
    {
        $request->validate([
            'workout_plan_id' => 'required|exists:workout_plans,id',
            'day' => 'required|string',
            'workout_id' => 'required|exists:workouts,id',
        ]);

        $user = Auth::user();
        $workoutPlanId = $request->workout_plan_id;

        // Toggle completion status
        $this->trackingService->toggleWorkoutCompletion(
            $user,
            $workoutPlanId,
            $request->day,
            $request->workout_id
        );

        return redirect()->back();
    }

    /**
     * Save or update a workout set.
     */
    public function saveSet(Request $request)
    {
        $request->validate([
            'workout_plan_id' => 'required|exists:workout_plans,id',
            'day' => 'required|string',
            'workout_id' => 'required|exists:workouts,id',
            'set_number' => 'required|integer|min:1',
            'weight' => 'nullable|numeric|min:0',
            'reps' => 'required|integer|min:0',
            'completed' => 'boolean',
        ]);

        $user = Auth::user();

        $set = $this->trackingService->saveWorkoutSet(
            $user,
            $request->workout_plan_id,
            $request->day,
            $request->workout_id,
            $request->set_number,
            $request->weight,
            $request->reps,
            $request->boolean('completed', true)
        );

        return response()->json([
            'success' => true,
            'set' => [
                'id' => $set->id,
                'set_number' => $set->set_number,
                'weight' => $set->weight,
                'reps' => $set->reps,
                'completed' => $set->completed,
            ],
        ]);
    }

    /**
     * Toggle a set's completion status.
     */
    public function toggleSet(Request $request)
    {
        $request->validate([
            'workout_plan_id' => 'required|exists:workout_plans,id',
            'day' => 'required|string',
            'workout_id' => 'required|exists:workouts,id',
            'set_number' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        $set = $this->trackingService->toggleSetCompletion(
            $user,
            $request->workout_plan_id,
            $request->day,
            $request->workout_id,
            $request->set_number
        );

        if (! $set) {
            return response()->json(['success' => false, 'message' => 'Set not found'], 404);
        }

        return response()->json([
            'success' => true,
            'completed' => $set->completed,
        ]);
    }

    /**
     * Delete a specific set.
     */
    public function deleteSet(Request $request)
    {
        $request->validate([
            'workout_plan_id' => 'required|exists:workout_plans,id',
            'day' => 'required|string',
            'workout_id' => 'required|exists:workouts,id',
            'set_number' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        $deleted = $this->trackingService->deleteWorkoutSet(
            $user,
            $request->workout_plan_id,
            $request->day,
            $request->workout_id,
            $request->set_number
        );

        return response()->json(['success' => $deleted]);
    }

    /**
     * Finish a workout day, handling incomplete sets.
     */
    public function finishDay(Request $request)
    {
        $request->validate([
            'workout_plan_id' => 'required|exists:workout_plans,id',
            'day' => 'required|string',
            'action' => 'required|in:complete,discard',
        ]);

        $user = Auth::user();

        $result = $this->trackingService->finishWorkoutDay(
            $user,
            $request->workout_plan_id,
            $request->day,
            $request->action
        );

        return response()->json([
            'success' => true,
            ...$result,
        ]);
    }

    /**
     * Get session summary for a workout day.
     */
    public function sessionSummary(WorkoutPlan $workoutPlan, string $day)
    {
        $user = Auth::user();

        if (! $this->trackingService->canUserAccessWorkoutPlan($user, $workoutPlan)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $summary = $this->trackingService->getTodaySessionSummary($user, $workoutPlan->id, $day);

        return response()->json($summary);
    }

    /**
     * Display the specified workout plan.
     *
     * @return \Inertia\Response
     */
    public function show(WorkoutPlan $workoutPlan)
    {
        $user = Auth::user();

        // Check if the user is assigned to this workout plan
        if (! $this->trackingService->canUserAccessWorkoutPlan($user, $workoutPlan)) {
            abort(403, 'You do not have access to this workout plan.');
        }

        $workoutPlanData = $this->trackingService->formatWorkoutPlanForUser($workoutPlan, $user);

        return Inertia::render('WorkoutPlans/Show', [
            'workoutPlan' => $workoutPlanData,
        ]);
    }
}

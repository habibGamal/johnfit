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
            'workoutPlans' => $formattedWorkoutPlans
        ]);
    }

    /**
     * Toggle workout completion status.
     *
     * @param Request $request
     * @return \Inertia\Response
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
     * Display the specified workout plan.
     *
     * @param WorkoutPlan $workoutPlan
     * @return \Inertia\Response
     */
    public function show(WorkoutPlan $workoutPlan)
    {
        $user = Auth::user();

        // Check if the user is assigned to this workout plan
        if (!$this->trackingService->canUserAccessWorkoutPlan($user, $workoutPlan)) {
            abort(403, 'You do not have access to this workout plan.');
        }

        $workoutPlanData = $this->trackingService->formatWorkoutPlanForUser($workoutPlan, $user);

        return Inertia::render('WorkoutPlans/Show', [
            'workoutPlan' => $workoutPlanData
        ]);
    }
}

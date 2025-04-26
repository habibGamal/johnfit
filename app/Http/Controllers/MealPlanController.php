<?php

namespace App\Http\Controllers;

use App\Models\MealPlan;
use App\Services\MealTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MealPlanController extends Controller
{
    protected MealTrackingService $trackingService;

    public function __construct(MealTrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Display the user's assigned meal plans.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();
        $mealPlans = $user->mealPlans()->get();

        $formattedMealPlans = $this->trackingService->formatMealPlansForUser($mealPlans, $user);

        return Inertia::render('MealPlans/Index', [
            'mealPlans' => $formattedMealPlans
        ]);
    }

    /**
     * Toggle meal consumption status.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleCompletion(Request $request)
    {
        $request->validate([
            'meal_plan_id' => 'required|exists:meal_plans,id',
            'day' => 'required|string',
            'meal_id' => 'required|exists:meals,id',
            'meal_time' => 'nullable|string',
        ]);

        $user = Auth::user();
        $mealPlanId = $request->meal_plan_id;

        // Toggle completion status
        $this->trackingService->toggleMealCompletion(
            $user,
            $mealPlanId,
            $request->day,
            $request->meal_id,
            $request->meal_time
        );

        return redirect()->back();
    }

    /**
     * Display the specified meal plan.
     *
     * @param MealPlan $mealPlan
     * @return \Inertia\Response
     */
    public function show(MealPlan $mealPlan)
    {
        $user = Auth::user();

        // Check if the user is assigned to this meal plan
        if (!$this->trackingService->canUserAccessMealPlan($user, $mealPlan)) {
            abort(403, 'You do not have access to this meal plan.');
        }

        $mealPlanData = $this->trackingService->formatMealPlanForUser($mealPlan, $user);

        return Inertia::render('MealPlans/Show', [
            'mealPlan' => $mealPlanData
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Exceptions\PlanGenerationNotAllowedException;
use App\Services\PlanGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanGenerationController extends Controller
{
    protected PlanGenerationService $planGenerationService;

    public function __construct(PlanGenerationService $planGenerationService)
    {
        $this->planGenerationService = $planGenerationService;
    }

    /**
     * Check if the authenticated user is eligible to generate an AI plan.
     */
    public function eligibility(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $eligibility = $this->planGenerationService->getAiPlanEligibility($user);

        return response()->json([
            'success' => true,
            'eligibility' => $eligibility,
        ]);
    }

    /**
     * Auto-generate tailored workout and meal plans for the authenticated user.
     */
    public function generate(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        try {
            $result = $this->planGenerationService->generateForUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Custom Workout and Meal Plans successfully generated!',
                'workout_plan_id' => $result['workout_plan']->id,
                'meal_plan_id' => $result['meal_plan']->id,
                'summary' => $result['summary'],
            ]);
        } catch (PlanGenerationNotAllowedException $e) {
            return response()->json([
                'success' => false,
                'code' => 'AI_PLAN_LIMIT_REACHED',
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate plan: '.$e->getMessage(),
            ], 500);
        }
    }
}

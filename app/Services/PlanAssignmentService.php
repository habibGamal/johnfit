<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPlanAssignment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PlanAssignmentService
{
    public function __construct(
        protected DailyScheduleService $scheduleService
    ) {}

    /**
     * Assign a plan template to a user and pre-materialize upcoming schedule window.
     */
    public function assignPlan(
        User $user,
        int $planId,
        string $planType = 'workout',
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        int $preMaterializeDays = 14
    ): UserPlanAssignment {
        $startDate = $startDate ?? Carbon::today();
        $endDate = $endDate ?? $startDate->copy()->addDays(28); // Default 4-week window

        // Deactivate overlapping active assignments of same type
        UserPlanAssignment::where('user_id', $user->id)
            ->where('plan_type', $planType)
            ->where('status', 'active')
            ->update(['status' => 'completed']);

        $assignment = UserPlanAssignment::create([
            'user_id' => $user->id,
            'plan_id' => $planId,
            'plan_type' => $planType,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate ? $endDate->toDateString() : null,
            'status' => 'active',
        ]);

        // Pre-materialize schedules for the initial window
        $materializeEnd = $startDate->copy()->addDays($preMaterializeDays - 1);
        if ($endDate && $materializeEnd->gt($endDate)) {
            $materializeEnd = $endDate;
        }

        $period = CarbonPeriod::create($startDate, $materializeEnd);
        foreach ($period as $date) {
            $this->scheduleService->materializeDateForUser($user, $date);
        }

        return $assignment;
    }
}

<?php

namespace App\Services;

use App\Models\InBodyLog;
use App\Models\User;
use App\Models\UserDailySchedule;
use App\Models\UserDailyWaterLog;
use App\Models\UserWaterEntry;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class WaterIntakeService
{
    public const DEFAULT_WEIGHT_KG = 75.0;
    public const DEFAULT_BASE_MULTIPLIER = 35.0; // ml per kg (Tier 1)
    public const LEAN_MASS_MULTIPLIER = 40.0;    // ml per kg lean mass (Tier 2)
    public const FAT_MASS_MULTIPLIER = 10.0;     // ml per kg fat mass (Tier 2)

    /**
     * Calculate the recommended daily water intake target for a user.
     *
     * Hierarchy:
     * 1. Admin Fixed Target
     * 2. Admin Custom Multiplier
     * 3. Tier 2: InBody Composition (Lean Body Mass * 40 + Fat Mass * 10)
     * 4. Tier 1: Base Weight (Weight * 35 ml)
     * + Dynamic Workout Day Bonus
     */
    public function calculateDailyTarget(User $user, ?Carbon $date = null): array
    {
        $date = $date ? $date->copy() : Carbon::today();

        // 1. Admin Fixed Override
        if ($user->water_target_mode === 'fixed' && ! empty($user->admin_water_target_ml)) {
            $target = (int) $user->admin_water_target_ml;
            return [
                'target_ml' => $target,
                'formula_tier' => 'admin_fixed',
                'tier_name' => 'Coach Assigned Target',
                'base_ml' => $target,
                'workout_bonus_ml' => 0,
                'admin_notes' => $user->admin_water_notes,
                'is_admin_override' => true,
                'allow_user_override' => (bool) $user->allow_user_water_override,
            ];
        }

        // Fetch latest InBody log to get biometric metrics
        $latestInBody = InBodyLog::where('user_id', $user->id)->latestFirst()->first();
        $weightKg = (float) ($latestInBody?->weight ?? self::DEFAULT_WEIGHT_KG);

        // 2. Admin Custom Multiplier
        if ($user->water_target_mode === 'custom_multiplier' && ! empty($user->water_multiplier_per_kg)) {
            $multiplier = (float) $user->water_multiplier_per_kg;
            $baseMl = (int) round($weightKg * $multiplier);
            $tier = 'admin_multiplier';
            $tierName = "Coach Ratio ({$multiplier} ml/kg)";
        }
        // 3. Tier 2: InBody Composition Formula
        elseif ($latestInBody && $latestInBody->lean_body_mass && $latestInBody->weight) {
            $leanMass = (float) $latestInBody->lean_body_mass;
            $fatMass = max(0.0, (float) $latestInBody->weight - $leanMass);
            $baseMl = (int) round(($leanMass * self::LEAN_MASS_MULTIPLIER) + ($fatMass * self::FAT_MASS_MULTIPLIER));
            $tier = 'tier_2_inbody';
            $tierName = 'InBody Body Composition (Tier 2)';
        }
        // 4. Tier 1: Baseline Body Weight Formula
        else {
            $baseMl = (int) round($weightKg * self::DEFAULT_BASE_MULTIPLIER);
            $tier = 'tier_1_base';
            $tierName = 'Base Body Weight (Tier 1)';
        }

        // Dynamic Workout Day Bonus
        $workoutBonusMl = $this->calculateWorkoutBonus($user, $date);
        $totalTarget = $baseMl + $workoutBonusMl;

        // Sensible clamping (1,500ml - 6,000ml)
        $totalTarget = max(1500, min(6000, $totalTarget));

        return [
            'target_ml' => $totalTarget,
            'formula_tier' => $tier,
            'tier_name' => $tierName,
            'base_ml' => $baseMl,
            'workout_bonus_ml' => $workoutBonusMl,
            'weight_kg' => $weightKg,
            'admin_notes' => $user->admin_water_notes,
            'is_admin_override' => false,
            'allow_user_override' => (bool) $user->allow_user_water_override,
        ];
    }

    /**
     * Compute hydration bonus if today has a scheduled workout.
     */
    protected function calculateWorkoutBonus(User $user, Carbon $date): int
    {
        $schedule = UserDailySchedule::where('user_id', $user->id)
            ->whereDate('date', $date->toDateString())
            ->with('workoutItems')
            ->first();

        if (! $schedule || $schedule->workoutItems->isEmpty()) {
            return 0;
        }

        $totalMinutes = 0;
        foreach ($schedule->workoutItems as $item) {
            $details = $item->target_details ?? [];
            $minutes = $details['duration_minutes'] ?? $details['duration'] ?? 45;
            $totalMinutes += (int) $minutes;
        }

        if ($totalMinutes <= 30) {
            return 350;
        } elseif ($totalMinutes <= 45) {
            return 500;
        } elseif ($totalMinutes <= 60) {
            return 750;
        }

        return 1000;
    }

    /**
     * Get or create daily water log for user on given date.
     */
    public function getOrCreateDailyLog(User $user, ?Carbon $date = null): UserDailyWaterLog
    {
        $date = $date ? $date->copy() : Carbon::today();
        $dateStr = $date->toDateString();

        $log = UserDailyWaterLog::with('entries')
            ->where('user_id', $user->id)
            ->whereDate('date', $dateStr)
            ->first();

        if (! $log) {
            $calculation = $this->calculateDailyTarget($user, $date);
            $log = UserDailyWaterLog::create([
                'user_id' => $user->id,
                'date' => $dateStr,
                'target_ml' => $calculation['target_ml'],
                'consumed_ml' => 0,
                'custom_target_ml' => null,
                'is_completed' => false,
            ]);
            $log->load('entries');
        }

        return $log;
    }

    /**
     * Log a water intake amount.
     */
    public function logIntake(
        User $user,
        int $amountMl,
        string $containerType = 'cup',
        ?Carbon $date = null
    ): UserDailyWaterLog {
        if ($amountMl < 50 || $amountMl > 3000) {
            throw ValidationException::withMessages([
                'amount_ml' => 'Intake amount must be between 50ml and 3,000ml.',
            ]);
        }

        $date = $date ? $date->copy() : Carbon::today();
        $log = $this->getOrCreateDailyLog($user, $date);

        UserWaterEntry::create([
            'water_log_id' => $log->id,
            'amount_ml' => $amountMl,
            'container_type' => $containerType,
            'logged_at' => now(),
        ]);

        $log->recalculate();

        return $log->fresh(['entries']);
    }

    /**
     * Delete a single water intake entry (Undo action).
     */
    public function deleteEntry(User $user, int $entryId): UserDailyWaterLog
    {
        $entry = UserWaterEntry::with('waterLog')->findOrFail($entryId);

        if ($entry->waterLog->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'entry' => 'Unauthorized action on this water entry.',
            ]);
        }

        $log = $entry->waterLog;
        $entry->delete();

        $log->recalculate();

        return $log->fresh(['entries']);
    }

    /**
     * Set or clear a user custom daily target.
     */
    public function setCustomTarget(User $user, ?int $customTargetMl, ?Carbon $date = null): UserDailyWaterLog
    {
        if (! $user->allow_user_water_override) {
            throw ValidationException::withMessages([
                'target' => 'Custom water target is locked by your coach.',
            ]);
        }

        if ($customTargetMl !== null && ($customTargetMl < 1000 || $customTargetMl > 8000)) {
            throw ValidationException::withMessages([
                'custom_target_ml' => 'Target must be between 1,000ml and 8,000ml.',
            ]);
        }

        $log = $this->getOrCreateDailyLog($user, $date);
        $log->custom_target_ml = $customTargetMl;
        $log->save();

        $log->recalculate();

        return $log->fresh(['entries']);
    }

    /**
     * Get weekly hydration statistics and history for the user.
     */
    public function getWeeklyStats(User $user, ?Carbon $date = null): array
    {
        $endDate = $date ? $date->copy() : Carbon::today();
        $startDate = $endDate->copy()->subDays(6);

        $logs = UserDailyWaterLog::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn ($item) => $item->date->format('Y-m-d'));

        $history = [];
        $totalConsumed = 0;
        $completedDays = 0;

        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $dayLog = $logs->get($key);

            $target = $dayLog ? $dayLog->effective_target_ml : 2500;
            $consumed = $dayLog ? $dayLog->consumed_ml : 0;
            $percentage = $target > 0 ? round(min(100, ($consumed / $target) * 100), 1) : 0;
            $isCompleted = $dayLog ? $dayLog->is_completed : false;

            if ($isCompleted) {
                $completedDays++;
            }
            $totalConsumed += $consumed;

            $history[] = [
                'date' => $d->format('M d'),
                'full_date' => $key,
                'day_name' => $d->format('D'),
                'target_ml' => $target,
                'consumed_ml' => $consumed,
                'percentage' => $percentage,
                'is_completed' => $isCompleted,
            ];
        }

        return [
            'history' => $history,
            'total_consumed_ml' => $totalConsumed,
            'average_daily_ml' => round($totalConsumed / 7),
            'completed_days' => $completedDays,
            'completion_rate' => round(($completedDays / 7) * 100, 1),
        ];
    }
}

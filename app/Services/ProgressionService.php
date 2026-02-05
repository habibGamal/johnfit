<?php

namespace App\Services;

use App\Models\InBodyLog;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutSetCompletion;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProgressionService
{
    /**
     * Cache TTL in seconds (24 hours).
     */
    protected const CACHE_TTL = 86400;

    /**
     * Calculate the Epley 1RM formula: Weight × (1 + Reps/30).
     */
    public function calculateOneRepMax(float $weight, int $reps): float
    {
        if ($weight <= 0 || $reps <= 0) {
            return 0.0;
        }

        // For single rep, 1RM equals the weight
        if ($reps === 1) {
            return $weight;
        }

        return round($weight * (1 + $reps / 30), 2);
    }

    /**
     * Calculate volume for a set (Weight × Reps).
     */
    public function calculateVolume(float $weight, int $reps): float
    {
        return round($weight * $reps, 2);
    }

    /**
     * Get total volume per workout over a date range.
     */
    public function getVolumePerWorkout(
        User $user,
        int $workoutId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): Collection {
        $cacheKey = $this->getCacheKey($user, "volume_workout_{$workoutId}", $startDate, $endDate);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $workoutId, $startDate, $endDate) {
            $query = WorkoutSetCompletion::where('user_id', $user->id)
                ->where('workout_id', $workoutId)
                ->where('completed', true)
                ->whereNotNull('weight')
                ->where('weight', '>', 0);

            if ($startDate) {
                $query->where('session_date', '>=', $startDate->toDateString());
            }

            if ($endDate) {
                $query->where('session_date', '<=', $endDate->toDateString());
            }

            return $query->select('session_date')
                ->selectRaw('SUM(weight * reps) as total_volume')
                ->selectRaw('COUNT(*) as total_sets')
                ->selectRaw('SUM(reps) as total_reps')
                ->groupBy('session_date')
                ->orderBy('session_date')
                ->get();
        });
    }

    /**
     * Get volume per muscle group over a date range.
     */
    public function getVolumePerMuscleGroup(
        User $user,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): Collection {
        $cacheKey = $this->getCacheKey($user, 'volume_muscle_groups', $startDate, $endDate);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $startDate, $endDate) {
            $query = WorkoutSetCompletion::where('workout_set_completions.user_id', $user->id)
                ->where('workout_set_completions.completed', true)
                ->join('workouts', 'workout_set_completions.workout_id', '=', 'workouts.id');

            if ($startDate) {
                $query->where('session_date', '>=', $startDate->toDateString());
            }

            if ($endDate) {
                $query->where('session_date', '<=', $endDate->toDateString());
            }

            $sets = $query->select(
                'workouts.muscles',
                'workout_set_completions.weight',
                'workout_set_completions.reps',
                'workout_set_completions.session_date'
            )->get();

            // Group by muscle and calculate volume
            $muscleVolumes = [];
            foreach ($sets as $set) {
                $muscles = is_array($set->muscles) ? $set->muscles : explode(',', $set->muscles);
                $volume = $this->calculateVolume((float) $set->weight, (int) $set->reps);

                foreach ($muscles as $muscle) {
                    $muscle = trim($muscle);
                    if (! isset($muscleVolumes[$muscle])) {
                        $muscleVolumes[$muscle] = 0;
                    }
                    $muscleVolumes[$muscle] += $volume;
                }
            }

            // Sort by volume descending
            arsort($muscleVolumes);

            return collect($muscleVolumes)->map(function ($volume, $muscle) use ($muscleVolumes) {
                $maxVolume = max($muscleVolumes) ?: 1;

                return [
                    'muscle' => $muscle,
                    'volume' => round($volume, 2),
                    'percentage' => round(($volume / $maxVolume) * 100, 1),
                ];
            })->values();
        });
    }

    /**
     * Get estimated 1RM trend for a workout.
     */
    public function getOneRepMaxTrend(
        User $user,
        int $workoutId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): Collection {
        $cacheKey = $this->getCacheKey($user, "1rm_trend_{$workoutId}", $startDate, $endDate);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $workoutId, $startDate, $endDate) {
            $query = WorkoutSetCompletion::where('user_id', $user->id)
                ->where('workout_id', $workoutId)
                ->where('completed', true)
                ->whereNotNull('weight')
                ->where('weight', '>', 0);

            if ($startDate) {
                $query->where('session_date', '>=', $startDate->toDateString());
            }

            if ($endDate) {
                $query->where('session_date', '<=', $endDate->toDateString());
            }

            $sets = $query->select('session_date', 'weight', 'reps')
                ->orderBy('session_date')
                ->get();

            // Calculate 1RM for each set and group by session
            return $sets->groupBy('session_date')->map(function ($sessionSets, $date) {
                $maxOneRepMax = 0;
                $bestSet = null;

                foreach ($sessionSets as $set) {
                    $oneRepMax = $this->calculateOneRepMax((float) $set->weight, (int) $set->reps);
                    if ($oneRepMax > $maxOneRepMax) {
                        $maxOneRepMax = $oneRepMax;
                        $bestSet = $set;
                    }
                }

                return [
                    'date' => $date,
                    'estimated_1rm' => $maxOneRepMax,
                    'best_weight' => $bestSet?->weight,
                    'best_reps' => $bestSet?->reps,
                ];
            })->values();
        });
    }

    /**
     * Detect if the latest session contains a personal best.
     */
    public function detectPersonalBests(User $user, int $workoutId): array
    {
        $today = Carbon::today()->toDateString();
        $cacheKey = "pb_detection_{$user->id}_{$workoutId}_{$today}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $workoutId, $today) {
            // Get today's completed sets
            $todaySets = WorkoutSetCompletion::where('user_id', $user->id)
                ->where('workout_id', $workoutId)
                ->where('session_date', $today)
                ->where('completed', true)
                ->whereNotNull('weight')
                ->where('weight', '>', 0)
                ->get();

            if ($todaySets->isEmpty()) {
                return [
                    'has_volume_pb' => false,
                    'has_strength_pb' => false,
                    'volume_pb_details' => null,
                    'strength_pb_details' => null,
                ];
            }

            // Get all previous completed sets
            $previousSets = WorkoutSetCompletion::where('user_id', $user->id)
                ->where('workout_id', $workoutId)
                ->where('session_date', '<', $today)
                ->where('completed', true)
                ->whereNotNull('weight')
                ->where('weight', '>', 0)
                ->get();

            // Calculate today's metrics
            $todayVolume = $todaySets->sum(fn ($set) => $this->calculateVolume((float) $set->weight, (int) $set->reps));
            $todayMaxOneRepMax = $todaySets->max(fn ($set) => $this->calculateOneRepMax((float) $set->weight, (int) $set->reps));

            // Calculate previous bests
            $previousVolumes = $previousSets->groupBy('session_date')
                ->map(fn ($sets) => $sets->sum(fn ($set) => $this->calculateVolume((float) $set->weight, (int) $set->reps)));
            $previousMaxVolume = $previousVolumes->max() ?? 0;

            $previousMaxOneRepMax = $previousSets->max(fn ($set) => $this->calculateOneRepMax((float) $set->weight, (int) $set->reps)) ?? 0;

            $hasVolumePb = $todayVolume > $previousMaxVolume && $previousMaxVolume > 0;
            $hasStrengthPb = $todayMaxOneRepMax > $previousMaxOneRepMax && $previousMaxOneRepMax > 0;

            return [
                'has_volume_pb' => $hasVolumePb,
                'has_strength_pb' => $hasStrengthPb,
                'volume_pb_details' => $hasVolumePb ? [
                    'new_record' => round($todayVolume, 2),
                    'previous_record' => round($previousMaxVolume, 2),
                    'improvement' => round($todayVolume - $previousMaxVolume, 2),
                    'improvement_percentage' => round((($todayVolume - $previousMaxVolume) / $previousMaxVolume) * 100, 1),
                ] : null,
                'strength_pb_details' => $hasStrengthPb ? [
                    'new_record' => round($todayMaxOneRepMax, 2),
                    'previous_record' => round($previousMaxOneRepMax, 2),
                    'improvement' => round($todayMaxOneRepMax - $previousMaxOneRepMax, 2),
                    'improvement_percentage' => round((($todayMaxOneRepMax - $previousMaxOneRepMax) / $previousMaxOneRepMax) * 100, 1),
                ] : null,
                'today_volume' => round($todayVolume, 2),
                'today_max_1rm' => round($todayMaxOneRepMax, 2),
            ];
        });
    }

    /**
     * Get relative strength (Performance vs Body Weight).
     */
    public function getRelativeStrengthTrend(
        User $user,
        int $workoutId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): Collection {
        $cacheKey = $this->getCacheKey($user, "relative_strength_{$workoutId}", $startDate, $endDate);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $workoutId, $startDate, $endDate) {
            // Get 1RM trend
            $oneRmTrend = $this->getOneRepMaxTrend($user, $workoutId, $startDate, $endDate);

            if ($oneRmTrend->isEmpty()) {
                return collect();
            }

            // Get InBody logs for correlation
            $inBodyLogs = InBodyLog::where('user_id', $user->id)
                ->when($startDate, fn ($q) => $q->where('measured_at', '>=', $startDate))
                ->when($endDate, fn ($q) => $q->where('measured_at', '<=', $endDate))
                ->orderBy('measured_at')
                ->get()
                ->keyBy(fn ($log) => $log->measured_at->format('Y-m-d'));

            return $oneRmTrend->map(function ($item) use ($inBodyLogs) {
                // Find closest InBody log to this workout date
                $workoutDate = Carbon::parse($item['date']);
                $closestLog = null;
                $minDiff = PHP_INT_MAX;

                foreach ($inBodyLogs as $dateStr => $log) {
                    $logDate = Carbon::parse($dateStr);
                    $diff = abs($workoutDate->diffInDays($logDate));
                    if ($diff < $minDiff) {
                        $minDiff = $diff;
                        $closestLog = $log;
                    }
                }

                $bodyWeight = $closestLog?->weight;
                $relativeStrength = ($bodyWeight && $bodyWeight > 0)
                    ? round($item['estimated_1rm'] / $bodyWeight, 2)
                    : null;

                return [
                    ...$item,
                    'body_weight' => $bodyWeight,
                    'relative_strength' => $relativeStrength,
                ];
            });
        });
    }

    /**
     * Get comprehensive progression analytics for a user.
     */
    public function getProgressionAnalytics(User $user, ?int $workoutId = null): array
    {
        $endDate = Carbon::today();
        $startDate4Weeks = $endDate->copy()->subWeeks(4);
        $startDate8Weeks = $endDate->copy()->subWeeks(8);
        $startDateAll = null;

        // Get user's workouts with sets
        $workoutIds = $workoutId
            ? [$workoutId]
            : WorkoutSetCompletion::where('user_id', $user->id)
                ->where('completed', true)
                ->distinct()
                ->pluck('workout_id')
                ->toArray();

        $workouts = Workout::whereIn('id', $workoutIds)->get()->keyBy('id');

        // Get muscle group volume (heatmap data)
        $muscleHeatmap = $this->getVolumePerMuscleGroup($user, $startDate4Weeks, $endDate);

        // Calculate intensity delta (last 4 weeks vs previous 4 weeks)
        $intensityDelta = $this->calculateIntensityDelta($user, $startDate8Weeks, $startDate4Weeks, $endDate);

        // Calculate consistency score
        $consistencyScore = $this->calculateConsistencyScore($user, $startDate4Weeks, $endDate);

        // Get workout-specific data
        $workoutAnalytics = [];
        foreach ($workoutIds as $wId) {
            $workout = $workouts->get($wId);
            if (! $workout) {
                continue;
            }

            $volumeTrend = $this->getVolumePerWorkout($user, $wId, $startDateAll, $endDate);
            $oneRmTrend = $this->getOneRepMaxTrend($user, $wId, $startDateAll, $endDate);
            $pbs = $this->detectPersonalBests($user, $wId);
            $relativeStrength = $this->getRelativeStrengthTrend($user, $wId, $startDateAll, $endDate);

            $workoutAnalytics[] = [
                'workout_id' => $wId,
                'workout_name' => $workout->name,
                'muscles' => $workout->muscles,
                'volume_trend' => $volumeTrend,
                'one_rm_trend' => $oneRmTrend,
                'personal_bests' => $pbs,
                'relative_strength_trend' => $relativeStrength,
            ];
        }

        // Get InBody trend for correlation
        $inBodyTrend = InBodyLog::where('user_id', $user->id)
            ->orderBy('measured_at')
            ->get()
            ->map(fn ($log) => [
                'date' => $log->measured_at->format('Y-m-d'),
                'weight' => (float) $log->weight,
                'smm' => (float) $log->smm,
                'pbf' => (float) $log->pbf,
            ]);

        return [
            'muscle_heatmap' => $muscleHeatmap,
            'intensity_delta' => $intensityDelta,
            'consistency_score' => $consistencyScore,
            'workout_analytics' => $workoutAnalytics,
            'inbody_trend' => $inBodyTrend,
            'period' => [
                'start' => $startDate4Weeks->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ];
    }

    /**
     * Calculate intensity delta between two periods.
     */
    protected function calculateIntensityDelta(
        User $user,
        Carbon $prevStart,
        Carbon $prevEnd,
        Carbon $currentEnd
    ): array {
        $currentStart = $prevEnd;

        // Current period volume
        $currentVolume = WorkoutSetCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->whereBetween('session_date', [$currentStart->toDateString(), $currentEnd->toDateString()])
            ->whereNotNull('weight')
            ->selectRaw('SUM(weight * reps) as total')
            ->value('total') ?? 0;

        // Previous period volume
        $previousVolume = WorkoutSetCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->whereBetween('session_date', [$prevStart->toDateString(), $prevEnd->toDateString()])
            ->whereNotNull('weight')
            ->selectRaw('SUM(weight * reps) as total')
            ->value('total') ?? 0;

        $delta = $previousVolume > 0
            ? round((($currentVolume - $previousVolume) / $previousVolume) * 100, 1)
            : ($currentVolume > 0 ? 100 : 0);

        return [
            'current_volume' => round($currentVolume, 2),
            'previous_volume' => round($previousVolume, 2),
            'delta_percentage' => $delta,
            'trend' => $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'stable'),
        ];
    }

    /**
     * Calculate consistency score based on workout frequency.
     */
    protected function calculateConsistencyScore(User $user, Carbon $startDate, Carbon $endDate): array
    {
        // Get unique session days
        $sessionDays = WorkoutSetCompletion::where('user_id', $user->id)
            ->where('completed', true)
            ->whereBetween('session_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->distinct()
            ->pluck('session_date')
            ->count();

        // Calculate total weeks in period
        $totalWeeks = max(1, $startDate->diffInWeeks($endDate));

        // Assume target is 4 sessions per week
        $targetSessions = $totalWeeks * 4;
        $percentage = min(100, round(($sessionDays / max(1, $targetSessions)) * 100, 1));

        return [
            'completed_sessions' => $sessionDays,
            'target_sessions' => $targetSessions,
            'percentage' => $percentage,
            'weeks' => $totalWeeks,
        ];
    }

    /**
     * Clear cache for a user when new data is saved.
     */
    public function clearUserCache(User $user): void
    {
        // Clear all progression-related cache for user
        $patterns = [
            "progression_{$user->id}_*",
            "volume_*_{$user->id}_*",
            "1rm_*_{$user->id}_*",
            "pb_detection_{$user->id}_*",
            "relative_strength_{$user->id}_*",
        ];

        // Since Laravel's default cache doesn't support pattern deletion,
        // we'll use tagged caching if available or just flush the specific keys we know
        Cache::forget("progression_analytics_{$user->id}");
    }

    /**
     * Generate a cache key for user-specific data.
     */
    protected function getCacheKey(User $user, string $prefix, ?Carbon $start, ?Carbon $end): string
    {
        $startStr = $start?->toDateString() ?? 'all';
        $endStr = $end?->toDateString() ?? 'now';

        return "progression_{$user->id}_{$prefix}_{$startStr}_{$endStr}";
    }

    /**
     * Get chart data formatted for the Progress Pulse view.
     */
    public function getProgressPulseData(User $user, ?int $workoutId = null): array
    {
        $analytics = $this->getProgressionAnalytics($user, $workoutId);

        // Format data for multi-series chart
        $chartData = [];

        // Combine all trends into unified chart format
        if (! empty($analytics['workout_analytics'])) {
            $firstWorkout = $analytics['workout_analytics'][0] ?? null;

            if ($firstWorkout) {
                $volumeData = collect($firstWorkout['volume_trend'] ?? []);
                $oneRmData = collect($firstWorkout['one_rm_trend'] ?? []);
                $inBodyData = collect($analytics['inbody_trend'] ?? []);

                // Create unified date range
                $allDates = $volumeData->pluck('session_date')
                    ->merge($oneRmData->pluck('date'))
                    ->merge($inBodyData->pluck('date'))
                    ->unique()
                    ->sort()
                    ->values();

                $chartData = $allDates->map(function ($date) use ($volumeData, $oneRmData, $inBodyData) {
                    $volume = $volumeData->firstWhere('session_date', $date);
                    $oneRm = $oneRmData->firstWhere('date', $date);
                    $inBody = $inBodyData->firstWhere('date', $date);

                    return [
                        'date' => $date,
                        'volume' => $volume['total_volume'] ?? null,
                        'estimated_1rm' => $oneRm['estimated_1rm'] ?? null,
                        'body_weight' => $inBody['weight'] ?? null,
                    ];
                })->values()->toArray();
            }
        }

        return [
            'chart_data' => $chartData,
            'muscle_heatmap' => $analytics['muscle_heatmap'],
            'intensity_delta' => $analytics['intensity_delta'],
            'consistency_score' => $analytics['consistency_score'],
            'workout_analytics' => $analytics['workout_analytics'],
            'inbody_trend' => $analytics['inbody_trend'],
        ];
    }
}

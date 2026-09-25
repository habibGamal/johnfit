<?php

namespace App\Services;

use App\Models\InBodyLog;
use App\Models\User;
use App\Models\UserDailyItem;
use App\Models\Workout;
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
     * Get IDs of workouts that have tracking data for a user.
     */
    public function getTrackedWorkoutIds(User $user): Collection
    {
        return $this->getCompletedSets($user)
            ->pluck('workout_id')
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Check if a user has tracking data for a specific workout.
     */
    public function hasTrackingData(User $user, int $workoutId): bool
    {
        return $this->getCompletedSets($user, $workoutId)->isNotEmpty();
    }

    /**
     * Helper to extract flat list of completed sets from materialized UserDailyItems.
     */
    public function getCompletedSets(
        User $user,
        ?int $workoutId = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): Collection {
        $query = UserDailyItem::with(['schedule', 'workout'])
            ->whereHas('schedule', function ($q) use ($user, $startDate, $endDate) {
                $q->where('user_id', $user->id);
                if ($startDate) {
                    $q->where('date', '>=', $startDate->toDateString());
                }
                if ($endDate) {
                    $q->where('date', '<=', $endDate->toDateString());
                }
            })
            ->where('type', 'workout')
            ->where('status', '!=', 'voided');

        if ($workoutId) {
            $query->where('reference_id', $workoutId);
        }

        $items = $query->get();
        $flattenedSets = collect();

        foreach ($items as $item) {
            $date = Carbon::parse($item->schedule->date)->toDateString();
            $payload = $item->execution_payload ?? [];
            $sets = $payload['sets'] ?? [];
            $hasCompletedSetsInPayload = false;

            if (! empty($sets) && is_array($sets)) {
                foreach ($sets as $set) {
                    if (! empty($set['completed'])) {
                        $hasCompletedSetsInPayload = true;
                        $flattenedSets->push((object) [
                            'session_date' => $date,
                            'workout_id' => $item->reference_id,
                            'weight' => (float) ($set['weight'] ?? 0.0),
                            'reps' => (int) ($set['reps'] ?? 0),
                            'muscles' => $item->target_details['muscles'] ?? $item->workout?->muscles ?? [],
                        ]);
                    }
                }
            }

            if (! $hasCompletedSetsInPayload && $item->is_completed) {
                // Single logged item without individual set payload
                $flattenedSets->push((object) [
                    'session_date' => $date,
                    'workout_id' => $item->reference_id,
                    'weight' => 0.0,
                    'reps' => 10,
                    'muscles' => $item->target_details['muscles'] ?? $item->workout?->muscles ?? [],
                ]);
            }
        }

        return $flattenedSets;
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
            $sets = $this->getCompletedSets($user, $workoutId, $startDate, $endDate);

            return $sets->groupBy('session_date')->map(function ($sessionSets, $date) {
                return (object) [
                    'session_date' => $date,
                    'total_volume' => (float) $sessionSets->sum(fn ($s) => $s->weight * $s->reps),
                    'total_sets' => $sessionSets->count(),
                    'total_reps' => (int) $sessionSets->sum('reps'),
                ];
            })->sortBy('session_date')->values();
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
            $sets = $this->getCompletedSets($user, null, $startDate, $endDate);

            $muscleVolumes = [];
            foreach ($sets as $set) {
                $muscles = is_array($set->muscles) ? $set->muscles : explode(',', (string) $set->muscles);
                $volume = $this->calculateVolume((float) $set->weight, (int) $set->reps);

                foreach ($muscles as $muscle) {
                    $muscle = trim($muscle);
                    if (! empty($muscle)) {
                        $muscleVolumes[$muscle] = ($muscleVolumes[$muscle] ?? 0) + $volume;
                    }
                }
            }

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
            $sets = $this->getCompletedSets($user, $workoutId, $startDate, $endDate);

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
                    'best_weight' => $bestSet?->weight ?? ($sessionSets->first()?->weight ?? 0.0),
                    'best_reps' => $bestSet?->reps ?? ($sessionSets->first()?->reps ?? 0),
                ];
            })->sortBy('date')->values();
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
            $allSets = $this->getCompletedSets($user, $workoutId)->filter(fn ($s) => $s->weight > 0);

            $todaySets = $allSets->where('session_date', $today);
            if ($todaySets->isEmpty()) {
                return [
                    'has_volume_pb' => false,
                    'has_strength_pb' => false,
                    'volume_pb_details' => null,
                    'strength_pb_details' => null,
                ];
            }

            $previousSets = $allSets->where('session_date', '<', $today);

            $todayVolume = $todaySets->sum(fn ($set) => $this->calculateVolume((float) $set->weight, (int) $set->reps));
            $todayMaxOneRepMax = $todaySets->max(fn ($set) => $this->calculateOneRepMax((float) $set->weight, (int) $set->reps)) ?? 0;

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
            $oneRmTrend = $this->getOneRepMaxTrend($user, $workoutId, $startDate, $endDate);

            if ($oneRmTrend->isEmpty()) {
                return collect();
            }

            $inBodyLogs = InBodyLog::where('user_id', $user->id)
                ->when($startDate, fn ($q) => $q->where('measured_at', '>=', $startDate))
                ->when($endDate, fn ($q) => $q->where('measured_at', '<=', $endDate))
                ->orderBy('measured_at')
                ->get()
                ->keyBy(fn ($log) => $log->measured_at->format('Y-m-d'));

            return $oneRmTrend->map(function ($item) use ($inBodyLogs) {
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

        $completedSets = $this->getCompletedSets($user);
        $workoutIds = $workoutId
            ? [$workoutId]
            : $completedSets->pluck('workout_id')->filter()->unique()->values()->toArray();

        $workouts = Workout::whereIn('id', $workoutIds)->get()->keyBy('id');

        $muscleHeatmap = $this->getVolumePerMuscleGroup($user, $startDate4Weeks, $endDate);
        $intensityDelta = $this->calculateIntensityDelta($user, $startDate8Weeks, $startDate4Weeks, $endDate);
        $consistencyScore = $this->calculateConsistencyScore($user, $startDate4Weeks, $endDate);

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

        $currentSets = $this->getCompletedSets($user, null, $currentStart, $currentEnd);
        $currentVolume = $currentSets->sum(fn ($s) => $this->calculateVolume($s->weight, $s->reps));

        $prevSets = $this->getCompletedSets($user, null, $prevStart, $prevEnd);
        $previousVolume = $prevSets->sum(fn ($s) => $this->calculateVolume($s->weight, $s->reps));

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
        $sessionDays = $this->getCompletedSets($user, null, $startDate, $endDate)
            ->pluck('session_date')
            ->unique()
            ->count();

        $totalWeeks = max(1, $startDate->diffInWeeks($endDate));
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
        Cache::forget("progression_analytics_{$user->id}");

        try {
            DB::table('cache')
                ->where('key', 'like', "%progression_{$user->id}_%")
                ->orWhere('key', 'like', "%pb_detection_{$user->id}_%")
                ->delete();
        } catch (\Throwable) {
            // Fail silently if cache store is not a DB table
        }
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

        $chartData = [];

        if (! empty($analytics['workout_analytics'])) {
            $firstWorkout = $analytics['workout_analytics'][0] ?? null;

            if ($firstWorkout) {
                $volumeData = collect($firstWorkout['volume_trend'] ?? []);
                $oneRmData = collect($firstWorkout['one_rm_trend'] ?? []);
                $inBodyData = collect($analytics['inbody_trend'] ?? []);

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
                        'volume' => $volume ? $volume->total_volume : null,
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

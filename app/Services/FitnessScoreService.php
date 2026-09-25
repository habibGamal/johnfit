<?php

namespace App\Services;

use App\Models\FitnessScore;
use App\Models\User;
use App\Models\UserDailyItem;
use Carbon\Carbon;

/**
 * Points-based fitness scoring.
 *
 * Everything is expressed in points over a rolling period (default 7 days):
 * - Workout score   = earned workout points   / target workout points   * 100
 * - Meal score      = earned meal points      / target meal points      * 100
 * - InBody score    = 50 + (muscle gained kg * 10) - (fat gained % * 5), clamped 0-100
 * - Total           = average of the available component scores
 */
class FitnessScoreService
{
    /**
     * Simple InBody-to-points equation constants.
     */
    private const INBODY_BASE_POINTS = 50;

    private const POINTS_PER_KG_MUSCLE_GAINED = 10;

    private const POINTS_PER_PERCENT_FAT_LOST = 5;

    public function __construct(
        private InBodyAnalysisService $inBodyService
    ) {}

    /**
     * Calculate and store fitness score for a user for a given period.
     */
    public function calculateScore(
        User $user,
        ?Carbon $periodEnd = null,
        int $periodDays = 7
    ): FitnessScore {
        $periodEnd = $periodEnd ?? Carbon::today();
        $periodStart = $periodEnd->copy()->subDays($periodDays - 1);

        $workoutPoints = $this->itemPoints($user, 'workout', $periodStart, $periodEnd);
        $mealPoints = $this->itemPoints($user, 'meal', $periodStart, $periodEnd);
        $inBodyPoints = $this->inBodyPoints($user);

        $workoutScore = $this->percentage($workoutPoints);
        $mealScore = $this->percentage($mealPoints);
        $totalScore = $this->averageOfAvailable([$workoutScore, $mealScore, $inBodyPoints]);

        return FitnessScore::updateOrCreate(
            [
                'user_id' => $user->id,
                'period_end' => $periodEnd->toDateString(),
            ],
            [
                'period_start' => $periodStart->toDateString(),
                'period_days' => $periodDays,
                'total_score' => $totalScore,
                'workout_score' => $workoutScore,
                'meal_score' => $mealScore,
                'inbody_score' => $inBodyPoints,
                'workout_metrics' => $workoutPoints,
                'meal_metrics' => $mealPoints,
                'inbody_metrics' => $inBodyPoints === null ? null : ['earned_points' => $inBodyPoints],
            ]
        );
    }

    /**
     * Get the latest fitness score for a user.
     */
    public function getLatestScore(User $user): ?FitnessScore
    {
        return FitnessScore::latestForUser($user->id)->first();
    }

    /**
     * Get score history for trend visualization.
     */
    public function getScoreHistory(User $user, int $weeks = 12): array
    {
        return FitnessScore::where('user_id', $user->id)
            ->where('period_end', '>=', Carbon::today()->subWeeks($weeks))
            ->orderBy('period_end')
            ->get()
            ->map(fn ($score) => [
                'date' => $score->period_end->format('M d'),
                'fullDate' => $score->period_end->format('Y-m-d'),
                'total_score' => (float) $score->total_score,
                'workout_score' => (float) $score->workout_score,
                'meal_score' => (float) $score->meal_score,
                'inbody_score' => $score->inbody_score !== null ? (float) $score->inbody_score : null,
                'level' => $score->level,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Get score summary with breakdowns for display.
     */
    public function getScoreSummary(User $user): array
    {
        $score = $this->calculateScore($user);

        $hasInBody = $score->inbody_score !== null;
        $weight = round(100 / ($hasInBody ? 3 : 2), 1);

        return [
            'total_score' => (float) $score->total_score,
            'level' => $score->level,
            'trend' => $score->trend,
            'period' => [
                'start' => $score->period_start->format('M d'),
                'end' => $score->period_end->format('M d'),
                'days' => $score->period_days,
            ],
            'components' => [
                'workout' => [
                    'score' => (float) $score->workout_score,
                    'weight' => $weight,
                    'metrics' => $score->workout_metrics,
                ],
                'meal' => [
                    'score' => (float) $score->meal_score,
                    'weight' => $weight,
                    'metrics' => $score->meal_metrics,
                ],
                'inbody' => $hasInBody ? [
                    'score' => (float) $score->inbody_score,
                    'weight' => $weight,
                    'metrics' => $score->inbody_metrics,
                ] : null,
            ],
            'updated_at' => $score->updated_at->format('M d, Y H:i'),
        ];
    }

    /**
     * Sum target and earned points of completed daily items for a type.
     *
     * @return array{target: int, earned: int}
     */
    private function itemPoints(User $user, string $type, Carbon $start, Carbon $end): array
    {
        $items = UserDailyItem::query()
            ->whereHas('schedule', function ($q) use ($user, $start, $end) {
                $q->where('user_id', $user->id)
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            })
            ->where('type', $type)
            ->where('status', '!=', 'voided')
            ->get(['points', 'is_completed']);

        return [
            'target' => (int) $items->sum('points'),
            'earned' => (int) $items->where('is_completed', true)->sum('points'),
        ];
    }

    /**
     * Convert InBody composition change into points (0-100).
     *
     * Equation: 50 base + 10 pts per kg muscle gained - 5 pts per % fat lost penalty.
     * Returns null when there is nothing to compare against yet.
     */
    private function inBodyPoints(User $user): ?float
    {
        $analysis = $this->inBodyService->getAnalysis($user);

        if (! $analysis['bodyCompositionAnalysis']) {
            return null;
        }

        $indicators = $analysis['bodyCompositionAnalysis']['indicators'] ?? [];
        $smmChange = (float) ($indicators['smm_change_kg'] ?? 0);
        $pbfChange = (float) ($indicators['pbf_change_pct'] ?? 0);

        $points = self::INBODY_BASE_POINTS
            + ($smmChange * self::POINTS_PER_KG_MUSCLE_GAINED)
            - ($pbfChange * self::POINTS_PER_PERCENT_FAT_LOST);

        return round(max(0, min(100, $points)), 2);
    }

    /**
     * Earned / target as a 0-100 percentage.
     */
    private function percentage(array $points): float
    {
        if ($points['target'] <= 0) {
            return 0.0;
        }

        return round(($points['earned'] / $points['target']) * 100, 2);
    }

    /**
     * Average of non-null component scores.
     */
    private function averageOfAvailable(array $scores): float
    {
        $available = array_filter($scores, fn ($s) => $s !== null);

        if (empty($available)) {
            return 0.0;
        }

        return round(array_sum($available) / count($available), 2);
    }
}

<?php

namespace App\Services;

use App\Models\FitnessScore;
use App\Models\MealCompletion;
use App\Models\User;
use App\Models\WorkoutSetCompletion;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service for calculating and managing fitness scores.
 *
 * Combines workout performance, meal tracking adherence, and InBody composition
 * analysis into a unified 0-100 fitness score.
 */
class FitnessScoreService
{
    /**
     * Weight distribution for score components.
     */
    private const WORKOUT_WEIGHT = 0.40;

    private const MEAL_WEIGHT = 0.30;

    private const INBODY_WEIGHT = 0.30;

    /**
     * Adjusted weights when InBody data is unavailable.
     */
    private const WORKOUT_WEIGHT_NO_INBODY = 0.57;

    private const MEAL_WEIGHT_NO_INBODY = 0.43;

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

        // Calculate component scores
        $workoutResult = $this->calculateWorkoutScore($user, $periodStart, $periodEnd);
        $mealResult = $this->calculateMealScore($user, $periodStart, $periodEnd);
        $inBodyResult = $this->calculateInBodyScore($user, $periodEnd);

        // Calculate total score with appropriate weights
        $totalScore = $this->calculateTotalScore(
            $workoutResult['score'],
            $mealResult['score'],
            $inBodyResult?->get('score')
        );

        // Store or update the score
        return FitnessScore::updateOrCreate(
            [
                'user_id' => $user->id,
                'period_end' => $periodEnd->toDateString(),
            ],
            [
                'period_start' => $periodStart->toDateString(),
                'period_days' => $periodDays,
                'total_score' => $totalScore,
                'workout_score' => $workoutResult['score'],
                'meal_score' => $mealResult['score'],
                'inbody_score' => $inBodyResult?->get('score'),
                'workout_metrics' => $workoutResult['metrics'],
                'meal_metrics' => $mealResult['metrics'],
                'inbody_metrics' => $inBodyResult?->get('metrics'),
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
        $scores = FitnessScore::where('user_id', $user->id)
            ->where('period_end', '>=', Carbon::today()->subWeeks($weeks))
            ->orderBy('period_end')
            ->get();

        return $scores->map(function ($score) {
            return [
                'date' => $score->period_end->format('M d'),
                'fullDate' => $score->period_end->format('Y-m-d'),
                'total_score' => (float) $score->total_score,
                'workout_score' => (float) $score->workout_score,
                'meal_score' => (float) $score->meal_score,
                'inbody_score' => $score->inbody_score ? (float) $score->inbody_score : null,
                'level' => $score->level,
            ];
        })->values()->toArray();
    }

    /**
     * Calculate the weighted total score.
     */
    private function calculateTotalScore(
        float $workoutScore,
        float $mealScore,
        ?float $inBodyScore
    ): float {
        if ($inBodyScore !== null) {
            return round(
                ($workoutScore * self::WORKOUT_WEIGHT) +
                ($mealScore * self::MEAL_WEIGHT) +
                ($inBodyScore * self::INBODY_WEIGHT),
                2
            );
        }

        return round(
            ($workoutScore * self::WORKOUT_WEIGHT_NO_INBODY) +
            ($mealScore * self::MEAL_WEIGHT_NO_INBODY),
            2
        );
    }

    /**
     * Calculate workout performance score (0-100).
     *
     * Components:
     * - Completion Rate (0-60 pts): Completed sets / Planned sets
     * - Volume Progression (0-30 pts): Total volume vs previous period
     * - Streak Bonus (0-10 pts): Consecutive workout days
     */
    private function calculateWorkoutScore(User $user, Carbon $start, Carbon $end): array
    {
        $sets = WorkoutSetCompletion::where('user_id', $user->id)
            ->whereBetween('session_date', [$start, $end])
            ->get();

        if ($sets->isEmpty()) {
            return [
                'score' => 0,
                'metrics' => [
                    'completion_rate' => 0,
                    'volume_progression' => 0,
                    'streak_days' => 0,
                    'total_sets' => 0,
                    'completed_sets' => 0,
                ],
            ];
        }

        // Completion rate (0-60 pts)
        $totalSets = $sets->count();
        $completedSets = $sets->where('completed', true)->count();
        $completionRate = $totalSets > 0 ? ($completedSets / $totalSets) : 0;
        $completionScore = $completionRate * 60;

        // Volume progression (0-30 pts)
        $currentVolume = $this->calculateTotalVolume($sets->where('completed', true));
        $previousPeriodStart = $start->copy()->subDays($end->diffInDays($start) + 1);
        $previousSets = WorkoutSetCompletion::where('user_id', $user->id)
            ->whereBetween('session_date', [$previousPeriodStart, $start->copy()->subDay()])
            ->where('completed', true)
            ->get();
        $previousVolume = $this->calculateTotalVolume($previousSets);
        $volumeProgression = $previousVolume > 0
            ? (($currentVolume - $previousVolume) / $previousVolume) * 100
            : 0;
        $volumeScore = $this->getVolumeProgressionScore($volumeProgression);

        // Streak bonus (0-10 pts)
        $workoutDays = $sets->where('completed', true)
            ->pluck('session_date')
            ->unique()
            ->count();
        $streakScore = $this->getStreakScore($workoutDays);

        $totalScore = min(100, $completionScore + $volumeScore + $streakScore);

        return [
            'score' => round($totalScore, 2),
            'metrics' => [
                'completion_rate' => round($completionRate * 100, 1),
                'volume_progression' => round($volumeProgression, 1),
                'streak_days' => $workoutDays,
                'total_sets' => $totalSets,
                'completed_sets' => $completedSets,
                'current_volume' => round($currentVolume, 0),
                'previous_volume' => round($previousVolume, 0),
            ],
        ];
    }

    /**
     * Calculate total volume (weight × reps) from sets.
     */
    private function calculateTotalVolume(Collection $sets): float
    {
        return $sets->sum(fn ($set) => (float) $set->weight * (int) $set->reps);
    }

    /**
     * Get volume progression score based on percentage change.
     */
    private function getVolumeProgressionScore(float $progression): float
    {
        return match (true) {
            $progression >= 10 => 30,
            $progression >= 5 => 20,
            $progression >= -5 => 15,
            $progression >= -10 => 10,
            default => 5,
        };
    }

    /**
     * Get streak bonus score based on workout days.
     */
    private function getStreakScore(int $days): float
    {
        return match (true) {
            $days >= 7 => 10,
            $days >= 5 => 7,
            $days >= 3 => 5,
            $days >= 1 => 3,
            default => 0,
        };
    }

    /**
     * Calculate meal tracking score (0-100).
     *
     * Components:
     * - Completion Rate (0-70 pts): Completed meals / Planned meals
     * - Consistency (0-20 pts): Days with all meals / Total days
     * - Quantity Accuracy (0-10 pts): Deviation from planned quantities
     */
    private function calculateMealScore(User $user, Carbon $start, Carbon $end): array
    {
        $meals = MealCompletion::where('user_id', $user->id)
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->get();

        if ($meals->isEmpty()) {
            return [
                'score' => 0,
                'metrics' => [
                    'completion_rate' => 0,
                    'consistency' => 0,
                    'quantity_accuracy' => 0,
                    'total_meals' => 0,
                    'completed_meals' => 0,
                    'perfect_days' => 0,
                ],
            ];
        }

        // Completion rate (0-70 pts)
        $totalMeals = $meals->count();
        $completedMeals = $meals->where('completed', true)->count();
        $completionRate = $totalMeals > 0 ? ($completedMeals / $totalMeals) : 0;
        $completionScore = $completionRate * 70;

        // Consistency (0-20 pts) - days with all meals completed
        $totalDays = $end->diffInDays($start) + 1;
        $mealsByDay = $meals->groupBy(fn ($m) => $m->created_at->toDateString());
        $perfectDays = $mealsByDay->filter(function ($dayMeals) {
            return $dayMeals->every(fn ($m) => $m->completed);
        })->count();
        $consistency = $totalDays > 0 ? ($perfectDays / $totalDays) : 0;
        $consistencyScore = $consistency * 20;

        // Quantity accuracy (0-10 pts)
        // For now, if completed = true, we consider it accurate
        // In the future, we could compare actual vs planned quantity
        $quantityAccuracy = $completedMeals > 0 ? 1.0 : 0;
        $quantityScore = $quantityAccuracy * 10;

        $totalScore = min(100, $completionScore + $consistencyScore + $quantityScore);

        return [
            'score' => round($totalScore, 2),
            'metrics' => [
                'completion_rate' => round($completionRate * 100, 1),
                'consistency' => round($consistency * 100, 1),
                'quantity_accuracy' => round($quantityAccuracy * 100, 1),
                'total_meals' => $totalMeals,
                'completed_meals' => $completedMeals,
                'perfect_days' => $perfectDays,
                'total_days' => $totalDays,
            ],
        ];
    }

    /**
     * Calculate InBody composition score (0-100).
     *
     * Uses the existing InBodyAnalysisService to get body composition analysis
     * and normalizes the progress score (-100 to +100) to (0 to 100).
     */
    private function calculateInBodyScore(User $user, Carbon $periodEnd): ?Collection
    {
        $analysis = $this->inBodyService->getAnalysis($user);

        // Need at least 2 logs to calculate progression
        if (! $analysis['bodyCompositionAnalysis']) {
            return null;
        }

        $bodyAnalysis = $analysis['bodyCompositionAnalysis'];
        $progressScore = $bodyAnalysis['score'] ?? 0;

        // Normalize from (-100 to +100) to (0 to 100)
        $normalizedScore = ($progressScore + 100) / 2;

        return collect([
            'score' => round($normalizedScore, 2),
            'metrics' => [
                'smm_change' => $bodyAnalysis['indicators']['smm_change_kg'] ?? 0,
                'pbf_change' => $bodyAnalysis['indicators']['pbf_change_pct'] ?? 0,
                'classification' => $bodyAnalysis['classification'] ?? 'unknown',
                'muscle_trend' => $bodyAnalysis['indicators']['muscle_trend'] ?? 'unknown',
                'fat_trend' => $bodyAnalysis['indicators']['fat_trend'] ?? 'unknown',
                'status' => $bodyAnalysis['status'] ?? 'neutral',
                'raw_progress_score' => $progressScore,
            ],
        ]);
    }

    /**
     * Get score summary with breakdowns for display.
     */
    public function getScoreSummary(User $user): array
    {
        // $score = $this->getLatestScore($user);
        // if (!$score) {
        //     // Calculate current score if none exists
        // }
        $score = $this->calculateScore($user);

        return [
            'total_score' => $score->total_score,
            'level' => $score->level,
            'trend' => $score->trend,
            'period' => [
                'start' => $score->period_start->format('M d'),
                'end' => $score->period_end->format('M d'),
                'days' => $score->period_days,
            ],
            'components' => [
                'workout' => [
                    'score' => $score->workout_score,
                    'weight' => $score->inbody_score !== null
                        ? self::WORKOUT_WEIGHT * 100
                        : self::WORKOUT_WEIGHT_NO_INBODY * 100,
                    'metrics' => $score->workout_metrics,
                ],
                'meal' => [
                    'score' => $score->meal_score,
                    'weight' => $score->inbody_score !== null
                        ? self::MEAL_WEIGHT * 100
                        : self::MEAL_WEIGHT_NO_INBODY * 100,
                    'metrics' => $score->meal_metrics,
                ],
                'inbody' => $score->inbody_score !== null ? [
                    'score' => $score->inbody_score,
                    'weight' => self::INBODY_WEIGHT * 100,
                    'metrics' => $score->inbody_metrics,
                ] : null,
            ],
            'updated_at' => $score->updated_at->format('M d, Y H:i'),
        ];
    }
}

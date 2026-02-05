<?php

namespace App\Services;

use App\Models\InBodyLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InBodyAnalysisService
{
    /**
     * Body composition change thresholds (in percentage points or kg).
     */
    private const SIGNIFICANT_SMM_CHANGE = 0.5;  // kg

    private const SIGNIFICANT_PBF_CHANGE = 1.0;  // percentage points

    private const SIGNIFICANT_WEIGHT_CHANGE = 1.0; // kg

    /**
     * Get comprehensive InBody analysis for a user.
     */
    public function getAnalysis(User $user, ?int $tenantId = null): array
    {
        $logs = $this->getUserLogs($user, $tenantId);

        if ($logs->isEmpty()) {
            return $this->emptyAnalysis();
        }

        $latestLog = $logs->first();
        $previousLog = $logs->skip(1)->first();

        return [
            'latest' => $this->formatLog($latestLog),
            'previous' => $previousLog ? $this->formatLog($previousLog) : null,
            'delta' => $previousLog ? $this->calculateDelta($latestLog, $previousLog) : null,
            'bodyCompositionAnalysis' => $previousLog ? $this->analyzeBodyComposition($latestLog, $previousLog) : null,
            'trends' => $this->calculateTrends($logs),
            'statistics' => $this->calculateStatistics($logs),
            'history' => $this->formatHistory($logs),
        ];
    }

    /**
     * Calculate delta between current and previous log entries.
     */
    public function calculateDelta(InBodyLog $current, InBodyLog $previous): array
    {
        $daysDiff = Carbon::parse($current->measured_at)->diffInDays(Carbon::parse($previous->measured_at));

        return [
            'weight' => $this->calculateChange((float) $current->weight, (float) $previous->weight),
            'smm' => $this->calculateChange((float) $current->smm, (float) $previous->smm),
            'pbf' => $this->calculateChange((float) $current->pbf, (float) $previous->pbf),
            'bmi' => $this->calculateChange((float) $current->bmi, (float) $previous->bmi),
            'bmr' => $this->calculateChange((float) $current->bmr, (float) $previous->bmr),
            'body_water' => $current->body_water && $previous->body_water
                ? $this->calculateChange((float) $current->body_water, (float) $previous->body_water)
                : null,
            'lean_body_mass' => $current->lean_body_mass && $previous->lean_body_mass
                ? $this->calculateChange((float) $current->lean_body_mass, (float) $previous->lean_body_mass)
                : null,
            'visceral_fat' => $current->visceral_fat && $previous->visceral_fat
                ? $this->calculateChange((float) $current->visceral_fat, (float) $previous->visceral_fat)
                : null,
            'days_between' => $daysDiff,
            'weekly_rate' => $daysDiff > 0 ? [
                'weight' => round(((float) $current->weight - (float) $previous->weight) / $daysDiff * 7, 2),
                'smm' => round(((float) $current->smm - (float) $previous->smm) / $daysDiff * 7, 2),
                'pbf' => round(((float) $current->pbf - (float) $previous->pbf) / $daysDiff * 7, 2),
            ] : null,
        ];
    }

    /**
     * Analyze body composition changes and return classification.
     *
     * @return array{
     *     status: string,
     *     classification: string,
     *     description: string,
     *     indicators: array,
     *     recommendations: array
     * }
     */
    public function analyzeBodyComposition(InBodyLog $current, InBodyLog $previous): array
    {
        $smmChange = $current->smm - $previous->smm;
        $pbfChange = $current->pbf - $previous->pbf;
        $weightChange = $current->weight - $previous->weight;

        // Determine body composition change pattern
        $gainingMuscle = $smmChange >= self::SIGNIFICANT_SMM_CHANGE;
        $losingMuscle = $smmChange <= -self::SIGNIFICANT_SMM_CHANGE;
        $losingFat = $pbfChange <= -self::SIGNIFICANT_PBF_CHANGE;
        $gainingFat = $pbfChange >= self::SIGNIFICANT_PBF_CHANGE;

        $classification = $this->classifyChange($gainingMuscle, $losingMuscle, $losingFat, $gainingFat);
        $status = $this->determineStatus($classification);

        return [
            'status' => $status,
            'classification' => $classification,
            'description' => $this->getClassificationDescription($classification),
            'indicators' => [
                'muscle_trend' => $gainingMuscle ? 'gaining' : ($losingMuscle ? 'losing' : 'maintaining'),
                'fat_trend' => $losingFat ? 'losing' : ($gainingFat ? 'gaining' : 'maintaining'),
                'weight_trend' => $weightChange > self::SIGNIFICANT_WEIGHT_CHANGE ? 'gaining'
                    : ($weightChange < -self::SIGNIFICANT_WEIGHT_CHANGE ? 'losing' : 'stable'),
                'smm_change_kg' => round($smmChange, 2),
                'pbf_change_pct' => round($pbfChange, 2),
                'weight_change_kg' => round($weightChange, 2),
            ],
            'score' => $this->calculateProgressScore($smmChange, $pbfChange),
            'recommendations' => $this->getRecommendations($classification, $smmChange, $pbfChange),
        ];
    }

    /**
     * Classify the body composition change.
     */
    private function classifyChange(bool $gainingMuscle, bool $losingMuscle, bool $losingFat, bool $gainingFat): string
    {
        if ($gainingMuscle && $losingFat) {
            return 'recomposition';
        }

        if ($gainingMuscle && ! $gainingFat) {
            return 'lean_bulk';
        }

        if ($gainingMuscle && $gainingFat) {
            return 'bulk';
        }

        if ($losingFat && ! $losingMuscle) {
            return 'cutting';
        }

        if ($losingFat && $losingMuscle) {
            return 'aggressive_cut';
        }

        if ($gainingFat && ! $gainingMuscle) {
            return 'fat_gain';
        }

        if ($losingMuscle && ! $losingFat) {
            return 'muscle_loss';
        }

        return 'maintenance';
    }

    /**
     * Determine the overall status based on classification.
     */
    private function determineStatus(string $classification): string
    {
        return match ($classification) {
            'recomposition' => 'excellent',
            'lean_bulk', 'cutting' => 'positive',
            'bulk', 'maintenance' => 'neutral',
            'aggressive_cut', 'fat_gain', 'muscle_loss' => 'negative',
            default => 'neutral',
        };
    }

    /**
     * Get a human-readable description for the classification.
     */
    private function getClassificationDescription(string $classification): string
    {
        return match ($classification) {
            'recomposition' => 'Outstanding! You\'re simultaneously building muscle and losing fat - the holy grail of fitness.',
            'lean_bulk' => 'Great progress! You\'re adding muscle mass with minimal fat gain.',
            'bulk' => 'You\'re in a building phase, gaining both muscle and some fat.',
            'cutting' => 'Excellent cut! You\'re losing fat while preserving your hard-earned muscle.',
            'aggressive_cut' => 'You\'re losing weight quickly, but some muscle is being lost too. Consider slowing down.',
            'fat_gain' => 'Body fat is increasing without muscle gains. Review your nutrition and training.',
            'muscle_loss' => 'Muscle mass is decreasing. Ensure adequate protein intake and resistance training.',
            'maintenance' => 'Your body composition is stable. Great for maintaining, consider new goals if desired.',
            default => 'Continue tracking to identify trends.',
        };
    }

    /**
     * Calculate a progress score (-100 to +100).
     */
    private function calculateProgressScore(float $smmChange, float $pbfChange): int
    {
        // Muscle gain is positive, fat loss is positive (inverted)
        $muscleScore = min(max($smmChange * 20, -50), 50);
        $fatScore = min(max(-$pbfChange * 10, -50), 50);

        return (int) round($muscleScore + $fatScore);
    }

    /**
     * Get personalized recommendations based on the analysis.
     */
    private function getRecommendations(string $classification, float $smmChange, float $pbfChange): array
    {
        $recommendations = [];

        switch ($classification) {
            case 'recomposition':
                $recommendations[] = 'Keep doing what you\'re doing - your program is working perfectly.';
                $recommendations[] = 'Consider tracking macros to optimize further.';
                break;

            case 'lean_bulk':
                $recommendations[] = 'Excellent muscle building! Maintain your current caloric surplus.';
                if ($smmChange < 1.0) {
                    $recommendations[] = 'Consider slightly increasing protein intake for faster gains.';
                }
                break;

            case 'cutting':
                $recommendations[] = 'Great fat loss while maintaining muscle!';
                $recommendations[] = 'Ensure you\'re getting at least 1.6g protein per kg bodyweight.';
                break;

            case 'aggressive_cut':
                $recommendations[] = 'Consider reducing your caloric deficit to preserve muscle.';
                $recommendations[] = 'Prioritize protein intake and resistance training.';
                break;

            case 'fat_gain':
                $recommendations[] = 'Review your caloric intake - you may be in too large a surplus.';
                $recommendations[] = 'Increase training intensity or frequency.';
                break;

            case 'muscle_loss':
                $recommendations[] = 'Increase resistance training volume or intensity.';
                $recommendations[] = 'Ensure adequate protein (1.6-2.2g per kg bodyweight).';
                $recommendations[] = 'Check that you\'re not in too aggressive a caloric deficit.';
                break;

            case 'maintenance':
                $recommendations[] = 'Your body composition is stable.';
                $recommendations[] = 'If you want to progress, consider defining specific goals.';
                break;

            default:
                $recommendations[] = 'Continue tracking to establish trends.';
        }

        return $recommendations;
    }

    /**
     * Calculate trends over the log history.
     */
    private function calculateTrends(Collection $logs): array
    {
        if ($logs->count() < 2) {
            return ['insufficient_data' => true];
        }

        $oldest = $logs->last();
        $newest = $logs->first();
        $totalDays = Carbon::parse($oldest->measured_at)->diffInDays(Carbon::parse($newest->measured_at));

        return [
            'period_days' => $totalDays,
            'total_entries' => $logs->count(),
            'overall' => [
                'weight' => $this->calculateChange($newest->weight, $oldest->weight),
                'smm' => $this->calculateChange($newest->smm, $oldest->smm),
                'pbf' => $this->calculateChange($newest->pbf, $oldest->pbf),
                'bmi' => $this->calculateChange($newest->bmi, $oldest->bmi),
            ],
            'average_per_week' => $totalDays > 0 ? [
                'weight' => round(($newest->weight - $oldest->weight) / $totalDays * 7, 2),
                'smm' => round(($newest->smm - $oldest->smm) / $totalDays * 7, 2),
                'pbf' => round(($newest->pbf - $oldest->pbf) / $totalDays * 7, 3),
            ] : null,
        ];
    }

    /**
     * Calculate statistics from the log history.
     */
    private function calculateStatistics(Collection $logs): array
    {
        return [
            'weight' => [
                'min' => round($logs->min('weight'), 2),
                'max' => round($logs->max('weight'), 2),
                'avg' => round($logs->avg('weight'), 2),
                'current' => round($logs->first()->weight, 2),
            ],
            'smm' => [
                'min' => round($logs->min('smm'), 2),
                'max' => round($logs->max('smm'), 2),
                'avg' => round($logs->avg('smm'), 2),
                'current' => round($logs->first()->smm, 2),
            ],
            'pbf' => [
                'min' => round($logs->min('pbf'), 2),
                'max' => round($logs->max('pbf'), 2),
                'avg' => round($logs->avg('pbf'), 2),
                'current' => round($logs->first()->pbf, 2),
            ],
            'bmi' => [
                'min' => round($logs->min('bmi'), 1),
                'max' => round($logs->max('bmi'), 1),
                'avg' => round($logs->avg('bmi'), 1),
                'current' => round($logs->first()->bmi, 1),
            ],
            'bmr' => [
                'min' => round($logs->min('bmr'), 0),
                'max' => round($logs->max('bmr'), 0),
                'avg' => round($logs->avg('bmr'), 0),
                'current' => round($logs->first()->bmr, 0),
            ],
        ];
    }

    /**
     * Get user logs ordered by date descending.
     */
    private function getUserLogs(User $user, ?int $tenantId = null): Collection
    {
        return InBodyLog::where('user_id', $user->id)
            ->forTenant($tenantId)
            ->latestFirst()
            ->get();
    }

    /**
     * Calculate change between two values.
     */
    private function calculateChange(float $current, float $previous): array
    {
        $absoluteChange = $current - $previous;
        $percentageChange = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;

        return [
            'current' => round($current, 2),
            'previous' => round($previous, 2),
            'absolute' => round($absoluteChange, 2),
            'percentage' => round($percentageChange, 2),
            'trend' => $absoluteChange > 0 ? 'up' : ($absoluteChange < 0 ? 'down' : 'stable'),
        ];
    }

    /**
     * Format a single log entry for API response.
     */
    private function formatLog(InBodyLog $log): array
    {
        return [
            'id' => $log->id,
            'weight' => round((float) $log->weight, 2),
            'smm' => round((float) $log->smm, 2),
            'pbf' => round((float) $log->pbf, 2),
            'bmi' => round((float) $log->bmi, 1),
            'bmr' => round((float) $log->bmr, 0),
            'body_water' => $log->body_water ? round((float) $log->body_water, 2) : null,
            'lean_body_mass' => $log->lean_body_mass ? round((float) $log->lean_body_mass, 2) : null,
            'visceral_fat' => $log->visceral_fat ? round((float) $log->visceral_fat, 1) : null,
            'waist_hip_ratio' => $log->waist_hip_ratio ? round((float) $log->waist_hip_ratio, 3) : null,
            'measured_at' => $log->measured_at->format('Y-m-d'),
            'measured_at_formatted' => $log->measured_at->format('M d, Y'),
            'notes' => $log->notes,
        ];
    }

    /**
     * Format history for chart display.
     */
    private function formatHistory(Collection $logs): array
    {
        return $logs->reverse()->map(fn (InBodyLog $log) => [
            'date' => $log->measured_at->format('M d'),
            'fullDate' => $log->measured_at->format('Y-m-d'),
            'weight' => round((float) $log->weight, 2),
            'smm' => round((float) $log->smm, 2),
            'pbf' => round((float) $log->pbf, 2),
            'bmi' => round((float) $log->bmi, 1),
            'bmr' => round((float) $log->bmr, 0),
        ])->values()->toArray();
    }

    /**
     * Return empty analysis structure for users with no logs.
     */
    private function emptyAnalysis(): array
    {
        return [
            'latest' => null,
            'previous' => null,
            'delta' => null,
            'bodyCompositionAnalysis' => null,
            'trends' => ['insufficient_data' => true],
            'statistics' => null,
            'history' => [],
        ];
    }
}

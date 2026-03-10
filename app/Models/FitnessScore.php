<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FitnessScore extends Model
{
    protected $fillable = [
        'user_id',
        'total_score',
        'workout_score',
        'meal_score',
        'inbody_score',
        'workout_metrics',
        'meal_metrics',
        'inbody_metrics',
        'period_start',
        'period_end',
        'period_days',
    ];

    protected function casts(): array
    {
        return [
            'total_score' => 'decimal:2',
            'workout_score' => 'decimal:2',
            'meal_score' => 'decimal:2',
            'inbody_score' => 'decimal:2',
            'workout_metrics' => 'array',
            'meal_metrics' => 'array',
            'inbody_metrics' => 'array',
            'period_start' => 'date',
            'period_end' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by date period.
     */
    public function scopeForPeriod(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->where('period_start', '>=', $start)
            ->where('period_end', '<=', $end);
    }

    /**
     * Scope to get the latest score for a user.
     */
    public function scopeLatestForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)
            ->orderByDesc('period_end');
    }

    /**
     * Get score level label based on total score.
     */
    public function getLevelAttribute(): string
    {
        return match (true) {
            $this->total_score >= 90 => 'Elite',
            $this->total_score >= 80 => 'Excellent',
            $this->total_score >= 70 => 'Great',
            $this->total_score >= 60 => 'Good',
            $this->total_score >= 50 => 'Average',
            $this->total_score >= 40 => 'Fair',
            default => 'Needs Improvement',
        };
    }

    /**
     * Get trend compared to previous score.
     */
    public function getTrendAttribute(): ?string
    {
        $previousScore = static::where('user_id', $this->user_id)
            ->where('period_end', '<', $this->period_end)
            ->orderByDesc('period_end')
            ->value('total_score');

        if ($previousScore === null) {
            return null;
        }

        $diff = $this->total_score - $previousScore;

        return match (true) {
            $diff >= 5 => 'up',
            $diff <= -5 => 'down',
            default => 'stable',
        };
    }
}

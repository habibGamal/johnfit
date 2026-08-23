<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserDailySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workout_assignment_id',
        'meal_assignment_id',
        'date',
        'target_score',
        'earned_score',
        'is_locked',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'target_score' => 'integer',
            'earned_score' => 'integer',
            'is_locked' => 'boolean',
        ];
    }

    protected $appends = [
        'adherence_percentage',
        'is_completed',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workoutAssignment(): BelongsTo
    {
        return $this->belongsTo(UserPlanAssignment::class, 'workout_assignment_id');
    }

    public function mealAssignment(): BelongsTo
    {
        return $this->belongsTo(UserPlanAssignment::class, 'meal_assignment_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(UserDailyItem::class, 'daily_schedule_id')->orderBy('order_index');
    }

    public function workoutItems(): HasMany
    {
        return $this->items()->where('type', 'workout')->where('status', '!=', 'voided');
    }

    public function mealItems(): HasMany
    {
        return $this->items()->where('type', 'meal')->where('status', '!=', 'voided');
    }

    public function getAdherencePercentageAttribute(): float
    {
        if ($this->target_score <= 0) {
            return 0.0;
        }

        return round(($this->earned_score / $this->target_score) * 100, 1);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->target_score > 0 && $this->earned_score >= $this->target_score;
    }

    /**
     * Recalculate target and earned scores from active items.
     */
    public function recalculateScores(): self
    {
        $activeItems = $this->items()->where('status', '!=', 'voided')->get();

        $this->target_score = $activeItems->sum('points');
        $this->earned_score = $activeItems->where('is_completed', true)->sum('points');
        $this->save();

        return $this;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserDailyWaterLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'target_ml',
        'consumed_ml',
        'custom_target_ml',
        'is_completed',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'target_ml' => 'integer',
            'consumed_ml' => 'integer',
            'custom_target_ml' => 'integer',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    protected $appends = [
        'effective_target_ml',
        'percentage',
        'remaining_ml',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(UserWaterEntry::class, 'water_log_id')->orderBy('logged_at', 'desc');
    }

    public function getEffectiveTargetMlAttribute(): int
    {
        return $this->custom_target_ml ?? $this->target_ml;
    }

    public function getPercentageAttribute(): float
    {
        $target = $this->effective_target_ml;
        if ($target <= 0) {
            return 0.0;
        }

        return round(min(100.0, ($this->consumed_ml / $target) * 100), 1);
    }

    public function getRemainingMlAttribute(): int
    {
        return max(0, $this->effective_target_ml - $this->consumed_ml);
    }

    /**
     * Recalculate consumed ml and completion status from entries.
     */
    public function recalculate(): self
    {
        $this->consumed_ml = (int) $this->entries()->sum('amount_ml');
        $target = $this->effective_target_ml;

        $wasCompleted = $this->is_completed;
        $isNowCompleted = $target > 0 && $this->consumed_ml >= $target;

        $this->is_completed = $isNowCompleted;

        if ($isNowCompleted && ! $wasCompleted) {
            $this->completed_at = now();
        } elseif (! $isNowCompleted) {
            $this->completed_at = null;
        }

        $this->save();

        return $this;
    }
}

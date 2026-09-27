<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPlanTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_plan_id',
        'months',
        'duration_days',
        'price',
        'tag',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'months' => 'integer',
            'duration_days' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Get effective duration in days, defaulting to months * 30 if null.
     */
    public function getEffectiveDaysAttribute(): int
    {
        return $this->duration_days ?: ($this->months * 30);
    }
}

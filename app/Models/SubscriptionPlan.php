<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriptionPlanFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'tag',
        'features',
        'is_active',
        'duration_days',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'duration_days' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(SubscriptionPlanTier::class, 'subscription_plan_id')
            ->orderBy('order')
            ->orderBy('months');
    }

    public function activeTiers(): HasMany
    {
        return $this->hasMany(SubscriptionPlanTier::class, 'subscription_plan_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('months');
    }

    public function getTierForMonths(int $months): ?SubscriptionPlanTier
    {
        return $this->activeTiers()->where('months', $months)->first();
    }

    public function getPriceForMonths(int $months): float
    {
        $tier = $this->getTierForMonths($months);

        return $tier ? (float) $tier->price : (float) $this->price;
    }

    public function getDaysForMonths(int $months): int
    {
        $tier = $this->getTierForMonths($months);

        return $tier ? $tier->effective_days : ($months * 30);
    }
}

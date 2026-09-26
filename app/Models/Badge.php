<?php

namespace App\Models;

use App\Enums\BadgeTier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'tier',
        'category',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Every requirement for this badge. All of them must pass to unlock it.
     */
    public function requirements(): HasMany
    {
        return $this->hasMany(BadgeRequirement::class);
    }

    /**
     * Users who have unlocked this badge.
     */
    public function unlockers(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }
}

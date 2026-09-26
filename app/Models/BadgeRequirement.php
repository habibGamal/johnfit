<?php

namespace App\Models;

use App\Enums\BadgeMetric;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadgeRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'badge_id',
        'metric',
        'operator',
        'threshold',
    ];

    protected function casts(): array
    {
        return [
            'metric' => BadgeMetric::class,
            'threshold' => 'float',
        ];
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * Does the given actual value satisfy this requirement?
     */
    public function isSatisfiedBy(?float $actual): bool
    {
        if ($actual === null) {
            return false;
        }

        return $this->operator === 'lte'
            ? $actual <= $this->threshold
            : $actual >= $this->threshold;
    }

    /**
     * How close the actual value is to the threshold, clamped to 0-1.
     */
    public function ratioFor(?float $actual): float
    {
        if ($actual === null) {
            return 0.0;
        }

        if ($this->isSatisfiedBy($actual)) {
            return 1.0;
        }

        if ($this->threshold <= 0) {
            return 1.0;
        }

        $ratio = $actual / $this->threshold;

        return $this->operator === 'lte'
            ? max(0.0, 1.0 - $ratio)
            : min(1.0, max(0.0, $ratio));
    }
}

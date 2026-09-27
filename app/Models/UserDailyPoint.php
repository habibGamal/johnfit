<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDailyPoint extends Model
{
    use HasFactory;

    protected $table = 'user_daily_points';

    protected $fillable = [
        'user_id',
        'date',
        'workout_points',
        'meal_points',
        'hydration_points',
        'total_points',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'workout_points' => 'integer',
            'meal_points' => 'integer',
            'hydration_points' => 'integer',
            'total_points' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Recalculate and persist total_points from component points.
     */
    public function recalculateTotal(): self
    {
        $this->total_points = $this->workout_points + $this->meal_points + $this->hydration_points;
        $this->save();

        return $this;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutCompletion extends Model
{
    protected $fillable = [
        'user_id',
        'workout_plan_id',
        'day',
        'workout_id',
        'completed'
    ];

    protected $casts = [
        'completed' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workoutPlan(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlan::class);
    }
}

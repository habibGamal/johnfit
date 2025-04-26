<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'meal_plan_id',
        'day',
        'meal_id',
        'meal_time',
        'completed'
    ];

    protected $casts = [
        'completed' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mealPlan(): BelongsTo
    {
        return $this->belongsTo(MealPlan::class);
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }
}

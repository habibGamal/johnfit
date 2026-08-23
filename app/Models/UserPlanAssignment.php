<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPlanAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'plan_type',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workoutPlan(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlan::class, 'plan_id');
    }

    public function mealPlan(): BelongsTo
    {
        return $this->belongsTo(MealPlan::class, 'plan_id');
    }

    public function getPlanNameAttribute(): string
    {
        if ($this->plan_type === 'workout') {
            return $this->workoutPlan?->name ?? 'Workout Plan #' . $this->plan_id;
        } elseif ($this->plan_type === 'meal') {
            return $this->mealPlan?->name ?? 'Meal Plan #' . $this->plan_id;
        }

        return 'Plan #' . $this->plan_id;
    }

    public function dailySchedules(): HasMany
    {
        $foreignKey = $this->plan_type === 'meal' ? 'meal_assignment_id' : 'workout_assignment_id';

        return $this->hasMany(UserDailySchedule::class, $foreignKey);
    }

    public function workoutDailySchedules(): HasMany
    {
        return $this->hasMany(UserDailySchedule::class, 'workout_assignment_id');
    }

    public function mealDailySchedules(): HasMany
    {
        return $this->hasMany(UserDailySchedule::class, 'meal_assignment_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('start_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDailyItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_schedule_id',
        'type',
        'item_name',
        'reference_id',
        'target_details',
        'points',
        'is_completed',
        'completed_at',
        'execution_payload',
        'status',
        'order_index',
    ];

    protected function casts(): array
    {
        return [
            'target_details' => 'array',
            'execution_payload' => 'array',
            'points' => 'integer',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
            'order_index' => 'integer',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(UserDailySchedule::class, 'daily_schedule_id');
    }

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class, 'reference_id');
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class, 'reference_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'voided');
    }

    public function scopeWorkouts($query)
    {
        return $query->where('type', 'workout');
    }

    public function scopeMeals($query)
    {
        return $query->where('type', 'meal');
    }
}

<?php

namespace App\Models;

use App\Services\WorkoutPlanServices;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WorkoutPlan extends Model
{
    /** @use HasFactory<\Database\Factories\WorkoutPlanFactory> */
    use HasFactory;

    protected static function booted()
    {
        static::saving(function ($modelData) {
            app(WorkoutPlanServices::class)->savePlanAsJson($modelData);
        });
    }


    /**
     * The assignments for this workout plan.
     */
    public function assignments()
    {
        return $this->hasMany(UserPlanAssignment::class, 'plan_id')->where('plan_type', 'workout');
    }

    /**
     * The users assigned to this workout plan.
     */
    public function users()
    {
        return $this->hasManyThrough(User::class, UserPlanAssignment::class, 'plan_id', 'id', 'id', 'user_id')
            ->where('user_plan_assignments.plan_type', 'workout');
    }
}

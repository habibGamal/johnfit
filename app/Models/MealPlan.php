<?php

namespace App\Models;

use App\Services\MealPlanServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MealPlan extends Model
{

    protected $casts = [
        'targets' => 'array',
    ];

    public function getDaysFromJsonAttribute()
    {
        $plan_file = Storage::disk('local')->get($this->file_path);
        return json_decode($plan_file, true);
    }

    protected static function booted()
    {
        static::saving(function ($modelData) {
            app(MealPlanServices::class)->savePlanAsJson($modelData);
        });
    }

    /**
     * The assignments for this meal plan.
     */
    public function assignments()
    {
        return $this->hasMany(UserPlanAssignment::class, 'plan_id')->where('plan_type', 'meal');
    }

    /**
     * The users assigned to this meal plan.
     */
    public function users()
    {
        return $this->hasManyThrough(User::class, UserPlanAssignment::class, 'plan_id', 'id', 'id', 'user_id')
            ->where('user_plan_assignments.plan_type', 'meal');
    }
}

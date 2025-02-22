<?php

namespace App\Models;

use App\Services\WorkoutPlanServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WorkoutPlan extends Model
{

    protected static function booted()
    {
        static::saving(function ($modelData) {
            app(WorkoutPlanServices::class)->savePlanAsJson($modelData);
        });
    }


    /**
     * The users that belong to the workout.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}

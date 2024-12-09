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
}

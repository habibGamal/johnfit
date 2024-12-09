<?php

namespace App\Services;

use App\Models\MealPlan;
use App\Models\RepsPreset;
use App\Models\Workout;
use App\Models\WorkoutPlan;
use Illuminate\Support\Facades\Storage;

class MealRenderService
{
    public function __construct(
        protected MealPlanServices $mealPlanServices
    ) {}

    public function render(MealPlan $mealPlan, $print = false)
    {
        $data = $this->mealPlanServices->loadDataFromJsonFile($mealPlan->file_path);
        $model = $mealPlan;
        $name = $mealPlan->name;
        return view('pdf_templates.meal-plan', compact('data', 'model','name', 'print'));
    }
}

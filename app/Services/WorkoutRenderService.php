<?php

namespace App\Services;

use App\Models\RepsPreset;
use App\Models\Workout;
use App\Models\WorkoutPlan;
use Illuminate\Support\Facades\Storage;

class WorkoutRenderService
{
    public function __construct(
        protected WorkoutPlanServices $workoutPlanServices
    ) {}

    public function render(WorkoutPlan $workoutPlan, $print = false)
    {
        $data = $this->workoutPlanServices->loadDataFromJsonFile($workoutPlan->file_path);
        $name = $workoutPlan->name;
        return view('pdf_templates.workout-plan', compact('data', 'name', 'print'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\WorkoutPlan;
use App\Services\PdfGeneratorService;
use App\Services\WorkoutRenderService;

class WorkoutController extends Controller
{

    public function show(WorkoutPlan $workoutPlan, WorkoutRenderService $service)
    {
        return $service->render($workoutPlan);
    }

    public function download(WorkoutPlan $workoutPlan, WorkoutRenderService $service)
    {
        return $service->render($workoutPlan,true);
    }
}

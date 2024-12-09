<?php

namespace App\Http\Controllers;

use App\Models\MealPlan;
use App\Services\MealRenderService;
use Illuminate\Http\Request;

class MealController extends Controller
{

    public function show(MealPlan $mealPlan, MealRenderService $service)
    {
        return $service->render($mealPlan);
    }

    public function download(MealPlan $mealPlan, MealRenderService $service)
    {
        return $service->render($mealPlan,true);
    }
}

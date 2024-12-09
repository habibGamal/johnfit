<?php

namespace App\Services;

use App\Models\Meal;
use App\Models\MealPlan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MealPlanServices
{
    const PLANS_DIR = 'meal-plans/';

    public function savePlanAsJson(MealPlan $modelData)
    {
        $plan = $modelData->days;
        $jsonPlan = json_encode($plan, JSON_PRETTY_PRINT);
        $filePath = static::PLANS_DIR  . $modelData->name . '.json';
        Storage::disk('local')->put($filePath, $jsonPlan);
        $modelData->file_path = $filePath;
        unset($modelData->days);
    }

    public function loadDataFromJsonFile($fileName)
    {
        // Read the JSON file from storage
        $json = Storage::disk('local')->get($fileName);
        $data = collect(json_decode($json, true));
        $meals = Meal::findMany($data->pluck('time.*.meals.*.meal_id')->flatten(2)->toArray());
        $data->transform(function ($day) use ($meals) {
            foreach ($day['time'] as $index => $time) {
                $day['time'][$index]['meals'] = collect($time['meals'])
                    ->map(function ($meal) use ($meals) {
                        $mealFound = $meals->where('id', $meal['meal_id'])->first();
                        $meal['data'] = $mealFound;
                        return $meal;
                    });
            }
            return $day;
        });
        return $data->toArray();
    }
}

<?php

namespace App\Services;

use App\Models\Meal;
use App\Models\MealPlan;
use Illuminate\Support\Facades\Storage;

class MealPlanServices
{
    const PLANS_DIR = 'meal-plans/';

    public function savePlanAsJson(MealPlan $modelData)
    {
        $plan = $modelData->days;
        $jsonPlan = json_encode($plan, JSON_PRETTY_PRINT);
        $filePath = static::PLANS_DIR.$modelData->name.'.json';
        Storage::disk('local')->put($filePath, $jsonPlan);
        $modelData->file_path = $filePath;
        unset($modelData->days);
    }

    public function loadDataFromJsonFile($fileName)
    {
        // Read the JSON file from storage
        $json = Storage::disk('local')->get($fileName);
        $data = collect(json_decode($json, true));

        // Extract all meal IDs from the nested options structure
        $mealIds = $data->flatMap(function ($day) {
            return collect($day['time'] ?? [])->flatMap(function ($time) {
                return collect($time['meals'] ?? [])->flatMap(function ($slot) {
                    return collect($slot['options'] ?? [])->pluck('meal_id');
                });
            });
        })->unique()->toArray();

        $meals = Meal::findMany($mealIds);

        $data->transform(function ($day) use ($meals) {
            foreach ($day['time'] as $index => $time) {
                $day['time'][$index]['meals'] = collect($time['meals'])
                    ->map(function ($slot) use ($meals) {
                        $slot['options'] = collect($slot['options'] ?? [])
                            ->map(function ($option) use ($meals) {
                                $mealFound = $meals->where('id', $option['meal_id'])->first();
                                $option['data'] = $mealFound;

                                return $option;
                            });

                        return $slot;
                    });
            }

            return $day;
        });

        return $data->toArray();
    }
}

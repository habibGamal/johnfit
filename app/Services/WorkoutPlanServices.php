<?php

namespace App\Services;

use App\Models\RepsPreset;
use App\Models\Workout;
use App\Models\WorkoutPlan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WorkoutPlanServices
{
    const PLANS_DIR = 'workout-plans/';

    public function savePlanAsJson(WorkoutPlan $modelData)
    {
        $plan = $modelData->days;
        $jsonPlan = json_encode($plan, JSON_PRETTY_PRINT);
        $filePath = static::PLANS_DIR  . $modelData->name . '.json';
        Storage::disk('local')->put($filePath, $jsonPlan);
        $modelData->file_path = $filePath;
        unset($modelData->days);
        unset($modelData->update_strategy);
    }

    public function loadDataFromJsonFile($fileName)
    {
        // Read the JSON file from storage
        $json = Storage::disk('local')->get($fileName);
        $data = collect(json_decode($json, true) ?? []);

        // Extract all workout and reps preset IDs (supporting both nested options and flat legacy formats)
        $workoutIds = [];
        $repsIds = [];

        foreach ($data as $day) {
            foreach ($day['workouts'] ?? [] as $slot) {
                if (isset($slot['options']) && is_array($slot['options'])) {
                    foreach ($slot['options'] as $opt) {
                        if (! empty($opt['workout_id'])) {
                            $workoutIds[] = $opt['workout_id'];
                        }
                        if (! empty($opt['reps'])) {
                            $repsIds[] = $opt['reps'];
                        }
                    }
                } elseif (isset($slot['workout_id'])) {
                    $workoutIds[] = $slot['workout_id'];
                    if (! empty($slot['reps'])) {
                        $repsIds[] = $slot['reps'];
                    }
                }
            }
        }

        $workouts = Workout::findMany(array_unique($workoutIds))->keyBy('id');
        $reps = RepsPreset::findMany(array_unique($repsIds))->keyBy('id');

        $data->transform(function ($day) use ($workouts, $reps) {
            $day['workouts'] = collect($day['workouts'] ?? [])->map(function ($slot) use ($workouts, $reps) {
                if (isset($slot['options']) && is_array($slot['options'])) {
                    $slot['options'] = collect($slot['options'])->map(function ($opt) use ($workouts, $reps) {
                        $opt['data'] = $workouts->get($opt['workout_id'] ?? null);
                        $opt['reps_data'] = $reps->get($opt['reps'] ?? null);
                        return $opt;
                    })->all();
                } else {
                    // Normalize flat legacy format to have options
                    $slot = [
                        'options' => [
                            [
                                'workout_id' => $slot['workout_id'] ?? null,
                                'reps' => $slot['reps'] ?? null,
                                'data' => $workouts->get($slot['workout_id'] ?? null),
                                'reps_data' => $reps->get($slot['reps'] ?? null),
                            ],
                        ],
                    ];
                }
                return $slot;
            })->all();

            return $day;
        });

        return $data->toArray();
    }
}

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
    }

    public function loadDataFromJsonFile($fileName)
    {
        // Read the JSON file from storage
        $json = Storage::disk('local')->get($fileName);
        $data = collect(json_decode($json, true));
        $workouts = Workout::findMany($data->pluck('workouts.*.workout_id')->flatten()->toArray());
        $reps = RepsPreset::findMany($data->pluck('workouts.*.reps')->flatten()->toArray());
        $data->transform(function ($day) use ($workouts, $reps) {
            $day['workouts'] = collect($day['workouts'])->map(function ($workout) use ($workouts, $reps) {
                $workoutFound = $workouts->where('id', $workout['workout_id'])->first();
                $repsPreset = $reps->where('id', $workout['reps'])->first();
                $workout['data'] = $workoutFound;
                $workout['reps'] = $repsPreset;
                return $workout;
            });
            return $day;
        });
        return $data->toArray();
    }
}

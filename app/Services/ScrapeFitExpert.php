<?php

namespace App\Services;

use App\Models\Meal;
use App\Models\Workout;
use Illuminate\Support\Facades\File;


class ScrapeFitExpert
{
    public function scrapeMeals(): void
    {
        $filePath = public_path('data/meals.json');
        if (!File::exists($filePath)) {
            throw new \Exception('File not found.');
        }

        $data = json_decode(File::get($filePath), true);
        // dd($data);
        foreach ($data as $page) {
            foreach ($page['data'] as $mealData) {
                $meal = new Meal();
                $meal->name = $mealData['name'];
                $meal->type = $mealData['meals'];
                $meal->meal_macros = $mealData['macros'];
                $meal->vitamins = $mealData['vitamins'];
                $meal->save();
            }
        }
    }

    public function scrapeWorkouts(): void
    {

        $filePath = public_path('data/workouts.json');
        if (!File::exists($filePath)) {
            throw new \Exception('File not found.');
        }

        $data = json_decode(File::get($filePath), true);
        foreach ($data as $page) {
            foreach ($page['data'] as $workoutData) {
                $meal = new Workout();
                $meal->name = $workoutData['name']['en'];
                $meal->thumb =  $workoutData['thumbnail'];
                $meal->video_url = $workoutData['preview_video'] ?? '';
                $meal->muscles = array_map(
                    fn($muscle) => $muscle['name']['en'],
                    $workoutData['muscles']
                );
                $meal->tools = array_map(
                    fn($tool) => $tool['name']['en'],
                    $workoutData['tools']
                );
                $meal->save();
            }
        }
    }
}

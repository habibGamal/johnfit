<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$today = \Carbon\Carbon::today()->toDateString();
echo "Today: $today\n";

$sets = \App\Models\WorkoutSetCompletion::where('user_id', 16)
    ->where('workout_plan_id', 1)
    ->where('session_date', $today)
    ->get();

echo "Found: " . $sets->count() . " sets\n";

foreach ($sets as $set) {
    echo "Set {$set->set_number}: weight={$set->weight}, reps={$set->reps}, completed={$set->completed}, session_date={$set->session_date}\n";
}

// Also test with workout_id=1 and day=D1
echo "\n\nWith day and workout filter:\n";
$setsFiltered = \App\Models\WorkoutSetCompletion::where('user_id', 16)
    ->where('workout_plan_id', 1)
    ->where('session_date', $today)
    ->where('day', 'D1')
    ->where('workout_id', 1)
    ->get();

echo "Found filtered: " . $setsFiltered->count() . " sets\n";

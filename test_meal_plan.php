<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PlanGenerationService;
use App\Models\User;

$user = User::first();
if (!$user) {
    echo "No user found!" . PHP_EOL;
    exit(1);
}

echo "Generating plan for: {$user->name} (id: {$user->id})" . PHP_EOL;

$service = new PlanGenerationService();

try {
    $result = $service->generateForUser($user);
    $mealPlan = $result['meal_plan'];

    echo PHP_EOL . "=== MEAL PLAN: {$mealPlan->name} ===" . PHP_EOL;
    echo "Targets: " . json_encode($mealPlan->targets, JSON_PRETTY_PRINT) . PHP_EOL;

    $days = $mealPlan->days;
    $allUsedMealIds = [];
    $dayDuplicates = 0;

    foreach ($days as $day) {
        echo PHP_EOL . "--- {$day['day']} ---" . PHP_EOL;
        $dayMealIds = [];

        foreach ($day['time'] as $slot) {
            $mealTime = $slot['meal_time'];
            foreach ($slot['meals'] as $mealGroup) {
                foreach ($mealGroup['options'] as $option) {
                    $meal = \App\Models\Meal::find($option['meal_id']);
                    $mealName = $meal ? $meal->name : 'UNKNOWN';
                    $mealType = $meal ? $meal->getRawOriginal('type') : 'UNKNOWN';
                    $qty = $option['quantity'];

                    // Check for same-day duplicate
                    $isDuplicate = in_array($option['meal_id'], $dayMealIds);
                    if ($isDuplicate) $dayDuplicates++;

                    $dupTag = $isDuplicate ? ' ⚠️ DUPLICATE' : '';
                    echo "  [{$mealTime}] {$mealName} ({$qty}g) [type: {$mealType}]{$dupTag}" . PHP_EOL;

                    $dayMealIds[] = $option['meal_id'];
                    $allUsedMealIds[] = $option['meal_id'];
                }
            }
        }
    }

    echo PHP_EOL . "=== STATISTICS ===" . PHP_EOL;
    echo "Total meal slots: " . count($allUsedMealIds) . PHP_EOL;
    echo "Unique meals used: " . count(array_unique($allUsedMealIds)) . PHP_EOL;
    echo "Same-day duplicates: {$dayDuplicates}" . PHP_EOL;

    // Check cross-day variety
    $counts = array_count_values($allUsedMealIds);
    arsort($counts);
    echo PHP_EOL . "Meal usage frequency:" . PHP_EOL;
    foreach (array_slice($counts, 0, 10, true) as $mealId => $count) {
        $meal = \App\Models\Meal::find($mealId);
        echo "  [{$mealId}] {$meal->name}: used {$count} times" . PHP_EOL;
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

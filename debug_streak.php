<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\UserDailyItem;
use App\Models\UserDailySchedule;
use App\Services\StreakService;

$u = User::where('email', 'user@user.com')->first();

UserDailyItem::query()->delete();
UserDailySchedule::query()->delete();

foreach ([2, 1, 0] as $d) {
    $date = now()->subDays($d)->startOfDay();
    $s = UserDailySchedule::create([
        'user_id' => $u->id,
        'date' => $date->toDateString(),
        'target_score' => 10,
        'earned_score' => 0,
        'is_locked' => false,
    ]);
    UserDailyItem::create([
        'daily_schedule_id' => $s->id,
        'type' => 'workout',
        'item_name' => 'X',
        'target_details' => [],
        'points' => 10,
        'is_completed' => true,
        'completed_at' => $date->copy()->setTime(18, 0),
        'status' => 'active',
        'order_index' => 0,
    ]);
}

$rows = UserDailyItem::query()
    ->whereHas('schedule', fn ($q) => $q->where('user_id', $u->id))
    ->where('type', 'workout')
    ->where('status', '!=', 'voided')
    ->where('is_completed', true)
    ->join('user_daily_schedules', 'user_daily_schedules.id', '=', 'user_daily_items.daily_schedule_id')
    ->distinct()
    ->orderBy('user_daily_schedules.date')
    ->pluck('user_daily_schedules.date');

echo 'DATES: ' . json_encode($rows) . PHP_EOL;

$streak = app(StreakService::class)->sync($u, 'workout');
echo 'STREAK cur=' . $streak->current_streak . ' best=' . $streak->longest_streak . PHP_EOL;

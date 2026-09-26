<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('quotes:send-morning')->dailyAt('09:00');
Schedule::command('schedules:lock-past')->dailyAt('23:59');
Schedule::command('streaks:sync-all')->dailyAt('00:10');

<?php

namespace App\Console\Commands;

use App\Models\UserDailySchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LockPastSchedulesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedules:lock-past';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lock past daily schedules at midnight to preserve historical data integrity';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today()->toDateString();

        $updatedCount = UserDailySchedule::where('date', '<', $today)
            ->where('is_locked', false)
            ->update(['is_locked' => true]);

        $this->info("Locked {$updatedCount} past daily schedules.");

        return Command::SUCCESS;
    }
}

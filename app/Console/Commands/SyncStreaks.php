<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Console\Command;

class SyncStreaks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'streaks:sync-all {--no-notify : Persist streaks only, skip badge unlock notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recompute persisted streaks for every user and award any newly earned badges.';

    public function __construct(
        protected BadgeService $badgeService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Syncing streaks and badges for all users...');

        $count = 0;
        $notify = ! $this->option('no-notify');

        User::query()
            ->where(function ($q) {
                $q->whereHas('dailySchedules')->orWhereHas('dailyWaterLogs');
            })
            ->chunkById(100, function ($users) use (&$count, $notify) {
                foreach ($users as $user) {
                    $newlyUnlocked = $this->badgeService->refresh($user, $notify);

                    $count++;

                    if ($newlyUnlocked->isNotEmpty()) {
                        $names = $newlyUnlocked->pluck('badge.name')->implode(', ');
                        $this->line("  {$user->email}: +{$newlyUnlocked->count()} ({$names})");
                    }
                }
            });

        $this->info("Done. Processed {$count} user(s).");

        return self::SUCCESS;
    }
}

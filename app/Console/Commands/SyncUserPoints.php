<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PointsService;
use Illuminate\Console\Command;

class SyncUserPoints extends Command
{
    protected $signature = 'points:sync {--user= : Optional user ID}';

    protected $description = 'Recalculate workout, meal, and hydration points and levels for users based on completed items';

    public function handle(PointsService $pointsService): int
    {
        $userId = $this->option('user');

        $query = User::query();
        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->get();
        $this->info("Recalculating points and levels for {$users->count()} user(s)...");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $summary = $pointsService->recalculateUserPoints($user);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Points and levels synchronized successfully.');

        return self::SUCCESS;
    }
}

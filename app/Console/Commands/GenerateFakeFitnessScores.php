<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserDailyPoint;
use App\Services\PointsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateFakeFitnessScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fitness:generate-fake-scores {user_id} {--weeks=12}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate fake daily progress points for a user for the previous N weeks';

    /**
     * Execute the console command.
     */
    public function handle(PointsService $pointsService): int
    {
        $userId = $this->argument('user_id');
        $weeks = (int) $this->option('weeks');

        $user = User::find($userId);

        if (! $user) {
            $this->error("User with ID {$userId} not found.");

            return 1;
        }

        $this->info("Generating {$weeks} weeks of fake daily progress points for {$user->name}...");

        $startDate = Carbon::today()->subWeeks($weeks);
        UserDailyPoint::where('user_id', $userId)
            ->where('date', '>=', $startDate->toDateString())
            ->delete();

        $days = $weeks * 7;
        $progressBar = $this->output->createProgressBar($days);
        $progressBar->start();

        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            // Active on ~70% of days
            if (rand(1, 10) <= 7) {
                $workoutPts = rand(0, 3) * 3; // 0, 3, 6, 9 pts
                $mealPts = rand(1, 4) * 5;    // 5, 10, 15, 20 pts
                $hydrationPts = rand(0, 1) === 1 ? PointsService::HYDRATION_GOAL_POINTS : 0;
                $totalPts = $workoutPts + $mealPts + $hydrationPts;

                if ($totalPts > 0) {
                    UserDailyPoint::create([
                        'user_id' => $userId,
                        'date' => $date->toDateString(),
                        'workout_points' => $workoutPts,
                        'meal_points' => $mealPts,
                        'hydration_points' => $hydrationPts,
                        'total_points' => $totalPts,
                    ]);
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $pointsService->recalculateUserPoints($user);
        $this->info("Successfully generated daily progress points. {$user->name} is now Level {$user->level} with {$user->total_points} total points.");

        return 0;
    }
}

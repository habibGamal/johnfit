<?php

namespace App\Console\Commands;

use App\Models\FitnessScore;
use App\Models\User;
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
    protected $description = 'Generate fake fitness scores for a user for the previous N weeks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $weeks = $this->option('weeks');

        $user = User::find($userId);

        if (! $user) {
            $this->error("User with ID {$userId} not found.");

            return 1;
        }

        $this->info("Generating {$weeks} weeks of fake fitness scores for {$user->name}...");

        // Delete existing scores for this user to avoid duplicates
        FitnessScore::where('user_id', $userId)->delete();
        $this->warn('Deleted existing fitness scores for this user.');

        $progressBar = $this->output->createProgressBar($weeks);
        $progressBar->start();

        // Generate scores for each week, going backwards from current week
        for ($i = 0; $i < $weeks; $i++) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();

            // Generate realistic trending scores (improve over time)
            $baseScore = 40 + ($weeks - $i) * 3; // Scores improve as we go back in time
            $variation = rand(-5, 5); // Add some randomness
            $totalScore = min(100, max(0, $baseScore + $variation));

            // Calculate component scores
            $workoutScore = $this->generateComponentScore($totalScore, 0.4);
            $mealScore = $this->generateComponentScore($totalScore, 0.3);
            $inbodyScore = $this->generateComponentScore($totalScore, 0.3);

            // Generate realistic metrics
            $workoutMetrics = [
                'completion_rate' => min(100, $workoutScore + rand(-10, 10)),
                'volume_progression' => rand(-5, 15),
                'streak_days' => rand(0, 7),
                'total_sets' => rand(20, 50),
                'completed_sets' => rand(15, 45),
                'current_volume' => rand(5000, 15000),
                'previous_volume' => rand(4000, 14000),
            ];

            $mealMetrics = [
                'completion_rate' => min(100, $mealScore + rand(-10, 10)),
                'consistency' => rand(50, 100),
                'quantity_accuracy' => rand(70, 100),
                'total_meals' => rand(15, 21),
                'completed_meals' => rand(10, 20),
                'perfect_days' => rand(0, 5),
                'total_days' => 7,
            ];

            $inbodyMetrics = [
                'smm_change' => round(rand(-10, 20) / 10, 2),
                'pbf_change' => round(rand(-30, 10) / 10, 2),
                'classification' => $this->getRandomClassification(),
                'muscle_trend' => $this->getRandomTrend(),
                'fat_trend' => $this->getRandomTrend(),
                'status' => $this->getRandomStatus(),
                'raw_progress_score' => rand(-50, 50),
            ];

            FitnessScore::create([
                'user_id' => $userId,
                'period_start' => $weekStart,
                'period_end' => $weekEnd,
                'total_score' => round($totalScore, 2),
                'workout_score' => round($workoutScore, 2),
                'meal_score' => round($mealScore, 2),
                'inbody_score' => round($inbodyScore, 2),
                'workout_metrics' => $workoutMetrics,
                'meal_metrics' => $mealMetrics,
                'inbody_metrics' => $inbodyMetrics,
                'created_at' => $weekEnd,
                'updated_at' => $weekEnd,
            ]);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("✅ Successfully generated {$weeks} weeks of fitness scores!");
        $this->info("User: {$user->name} (ID: {$userId})");

        return 0;
    }

    /**
     * Generate a component score based on total score and weight
     */
    private function generateComponentScore(float $totalScore, float $weight): float
    {
        // Component scores should be roughly aligned with total score
        $baseComponent = $totalScore + rand(-15, 15);

        return min(100, max(0, $baseComponent));
    }

    /**
     * Get random InBody classification
     */
    private function getRandomClassification(): string
    {
        $classifications = [
            'recomposition',
            'lean_bulk',
            'bulk',
            'cutting',
            'maintenance',
        ];

        return $classifications[array_rand($classifications)];
    }

    /**
     * Get random trend
     */
    private function getRandomTrend(): string
    {
        $trends = ['gaining', 'losing', 'maintaining'];

        return $trends[array_rand($trends)];
    }

    /**
     * Get random status
     */
    private function getRandomStatus(): string
    {
        $statuses = ['excellent', 'positive', 'neutral', 'negative'];

        return $statuses[array_rand($statuses)];
    }
}

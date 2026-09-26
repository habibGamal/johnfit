<?php

namespace Database\Seeders;

use App\Enums\BadgeMetric;
use App\Enums\BadgeTier;
use App\Models\Badge;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * The starter badge catalog based on points, levels, and streaks.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function catalog(): array
    {
        return [
            // --- Workout points ---
            [
                'slug' => 'first-rep',
                'name' => 'First Rep',
                'description' => 'Earn 30 workout points by completing your training sessions.',
                'icon' => 'Dumbbell',
                'tier' => BadgeTier::Bronze,
                'category' => 'workout',
                'sort_order' => 1,
                'requirements' => [['workout_points', 30]],
            ],
            [
                'slug' => 'getting-strong',
                'name' => 'Getting Strong',
                'description' => 'Earn 100 workout points.',
                'icon' => 'Dumbbell',
                'tier' => BadgeTier::Bronze,
                'category' => 'workout',
                'sort_order' => 2,
                'requirements' => [['workout_points', 100]],
            ],
            [
                'slug' => 'dedicated-trainer',
                'name' => 'Dedicated Trainer',
                'description' => 'Earn 300 workout points.',
                'icon' => 'Target',
                'tier' => BadgeTier::Silver,
                'category' => 'workout',
                'sort_order' => 3,
                'requirements' => [['workout_points', 300]],
            ],
            [
                'slug' => 'iron-will',
                'name' => 'Iron Will',
                'description' => 'Earn 750 workout points.',
                'icon' => 'Zap',
                'tier' => BadgeTier::Gold,
                'category' => 'workout',
                'sort_order' => 4,
                'requirements' => [['workout_points', 750]],
            ],
            [
                'slug' => 'elite-athlete',
                'name' => 'Elite Athlete',
                'description' => 'Earn 1,500 workout points.',
                'icon' => 'Crown',
                'tier' => BadgeTier::Diamond,
                'category' => 'workout',
                'sort_order' => 5,
                'requirements' => [['workout_points', 1500]],
            ],

            // --- Meal points ---
            [
                'slug' => 'nourished',
                'name' => 'Nourished',
                'description' => 'Earn 50 meal points by staying disciplined with your nutrition.',
                'icon' => 'Apple',
                'tier' => BadgeTier::Bronze,
                'category' => 'meal',
                'sort_order' => 6,
                'requirements' => [['meal_points', 50]],
            ],
            [
                'slug' => 'balanced-plate',
                'name' => 'Balanced Plate',
                'description' => 'Earn 150 meal points.',
                'icon' => 'Utensils',
                'tier' => BadgeTier::Bronze,
                'category' => 'meal',
                'sort_order' => 7,
                'requirements' => [['meal_points', 150]],
            ],
            [
                'slug' => 'clean-eater',
                'name' => 'Clean Eater',
                'description' => 'Earn 500 meal points.',
                'icon' => 'Salad',
                'tier' => BadgeTier::Silver,
                'category' => 'meal',
                'sort_order' => 8,
                'requirements' => [['meal_points', 500]],
            ],
            [
                'slug' => 'nutrition-master',
                'name' => 'Nutrition Master',
                'description' => 'Earn 1,200 meal points.',
                'icon' => 'ChefHat',
                'tier' => BadgeTier::Gold,
                'category' => 'meal',
                'sort_order' => 9,
                'requirements' => [['meal_points', 1200]],
            ],

            // --- Hydration points ---
            [
                'slug' => 'hydro-starter',
                'name' => 'Hydro Starter',
                'description' => 'Earn 25 hydration points by meeting your daily water target 5 times.',
                'icon' => 'Droplets',
                'tier' => BadgeTier::Bronze,
                'category' => 'hydration',
                'sort_order' => 10,
                'requirements' => [['hydration_points', 25]],
            ],
            [
                'slug' => 'deep-hydration',
                'name' => 'Deep Hydration',
                'description' => 'Earn 100 hydration points by consistently hitting your water target.',
                'icon' => 'Droplet',
                'tier' => BadgeTier::Silver,
                'category' => 'hydration',
                'sort_order' => 11,
                'requirements' => [['hydration_points', 100]],
            ],
            [
                'slug' => 'aqua-master',
                'name' => 'Aqua Master',
                'description' => 'Earn 250 hydration points from daily hydration excellence.',
                'icon' => 'Waves',
                'tier' => BadgeTier::Gold,
                'category' => 'hydration',
                'sort_order' => 12,
                'requirements' => [['hydration_points', 250]],
            ],

            // --- Level progression ---
            [
                'slug' => 'level-climber',
                'name' => 'Level Climber',
                'description' => 'Reach Level 3 by accumulating points across workouts, meals, and hydration.',
                'icon' => 'TrendingUp',
                'tier' => BadgeTier::Bronze,
                'category' => 'level',
                'sort_order' => 13,
                'requirements' => [['level', 3]],
            ],
            [
                'slug' => 'rising-force',
                'name' => 'Rising Force',
                'description' => 'Reach Level 5.',
                'icon' => 'ShieldAlert',
                'tier' => BadgeTier::Silver,
                'category' => 'level',
                'sort_order' => 14,
                'requirements' => [['level', 5]],
            ],
            [
                'slug' => 'warrior-rank',
                'name' => 'Warrior Rank',
                'description' => 'Reach Level 10.',
                'icon' => 'Shield',
                'tier' => BadgeTier::Gold,
                'category' => 'level',
                'sort_order' => 15,
                'requirements' => [['level', 10]],
            ],
            [
                'slug' => 'champion-rank',
                'name' => 'Champion Rank',
                'description' => 'Reach Level 20.',
                'icon' => 'Award',
                'tier' => BadgeTier::Platinum,
                'category' => 'level',
                'sort_order' => 16,
                'requirements' => [['level', 20]],
            ],
            [
                'slug' => 'living-legend',
                'name' => 'Living Legend',
                'description' => 'Reach Level 50 in your fitness journey.',
                'icon' => 'Crown',
                'tier' => BadgeTier::Diamond,
                'category' => 'level',
                'sort_order' => 17,
                'requirements' => [['level', 50]],
            ],

            // --- Total points milestones ---
            [
                'slug' => 'century-club',
                'name' => 'Century Club',
                'description' => 'Accumulate 100 total points.',
                'icon' => 'Sparkles',
                'tier' => BadgeTier::Bronze,
                'category' => 'total',
                'sort_order' => 18,
                'requirements' => [['total_points', 100]],
            ],
            [
                'slug' => 'millennium-mark',
                'name' => 'Millennium Mark',
                'description' => 'Accumulate 1,000 total points.',
                'icon' => 'Flame',
                'tier' => BadgeTier::Gold,
                'category' => 'total',
                'sort_order' => 19,
                'requirements' => [['total_points', 1000]],
            ],
            [
                'slug' => 'titan',
                'name' => 'Titan of Fitness',
                'description' => 'Accumulate 5,000 total points.',
                'icon' => 'Trophy',
                'tier' => BadgeTier::Diamond,
                'category' => 'total',
                'sort_order' => 20,
                'requirements' => [['total_points', 5000]],
            ],

            // --- Streaks ---
            [
                'slug' => 'consistency-king',
                'name' => 'Consistency King',
                'description' => 'Complete at least one workout 7 days in a row.',
                'icon' => 'Flame',
                'tier' => BadgeTier::Silver,
                'category' => 'streak',
                'sort_order' => 21,
                'requirements' => [['streak_workout', 7]],
            ],
            [
                'slug' => 'unbreakable',
                'name' => 'Unbreakable',
                'description' => 'Once complete 30 workout days in a row.',
                'icon' => 'Shield',
                'tier' => BadgeTier::Gold,
                'category' => 'streak',
                'sort_order' => 22,
                'requirements' => [['streak_workout_best', 30]],
            ],
            [
                'slug' => 'fuel-the-machine',
                'name' => 'Fuel the Machine',
                'description' => 'Hit your water target 7 days in a row.',
                'icon' => 'Droplets',
                'tier' => BadgeTier::Silver,
                'category' => 'streak',
                'sort_order' => 23,
                'requirements' => [['streak_hydration_best', 7]],
            ],
            [
                'slug' => 'perfect-week',
                'name' => 'Perfect Week',
                'description' => 'Complete every item on your schedule 7 days in a row.',
                'icon' => 'CalendarCheck',
                'tier' => BadgeTier::Gold,
                'category' => 'streak',
                'sort_order' => 24,
                'requirements' => [['streak_overall', 7]],
            ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::catalog() as $definition) {
            $badge = Badge::updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'icon' => $definition['icon'],
                    'tier' => $definition['tier']->value,
                    'category' => $definition['category'],
                    'sort_order' => $definition['sort_order'],
                    'is_active' => true,
                ]
            );

            // Requirements are replaced wholesale so catalog edits apply.
            $badge->requirements()->delete();

            foreach ($definition['requirements'] as [$metric, $threshold]) {
                $badge->requirements()->create([
                    'metric' => BadgeMetric::from($metric),
                    'operator' => 'gte',
                    'threshold' => $threshold,
                ]);
            }
        }
    }
}

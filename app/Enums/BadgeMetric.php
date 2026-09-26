<?php

namespace App\Enums;

/**
 * Metrics a badge requirement can be evaluated against.
 *
 * Points metrics read from the user's cumulative points (workout_points, meal_points,
 * hydration_points, total_points, level). Streak metrics read from `user_streaks`.
 */
enum BadgeMetric: string
{
    case WorkoutPoints = 'workout_points';
    case MealPoints = 'meal_points';
    case HydrationPoints = 'hydration_points';
    case TotalPoints = 'total_points';
    case Level = 'level';

    case StreakWorkout = 'streak_workout';
    case StreakWorkoutBest = 'streak_workout_best';
    case StreakMeal = 'streak_meal';
    case StreakMealBest = 'streak_meal_best';
    case StreakHydration = 'streak_hydration';
    case StreakHydrationBest = 'streak_hydration_best';
    case StreakOverall = 'streak_overall';
    case StreakOverallBest = 'streak_overall_best';

    /**
     * Points and level metrics read from the user record.
     */
    public function isPointMetric(): bool
    {
        return in_array($this, [
            self::WorkoutPoints,
            self::MealPoints,
            self::HydrationPoints,
            self::TotalPoints,
            self::Level,
        ], true);
    }

    /**
     * Legacy helper method alias for compatibility with existing check.
     */
    public function isScoreMetric(): bool
    {
        return $this->isPointMetric();
    }

    /**
     * The `user_streaks.streak_type` this metric reads, if any.
     */
    public function streakType(): ?string
    {
        return match ($this) {
            self::StreakWorkout, self::StreakWorkoutBest => 'workout',
            self::StreakMeal, self::StreakMealBest => 'meal',
            self::StreakHydration, self::StreakHydrationBest => 'hydration',
            self::StreakOverall, self::StreakOverallBest => 'overall',
            default => null,
        };
    }

    /**
     * Whether this metric reads the all-time record rather than the live streak.
     */
    public function isBestStreak(): bool
    {
        return in_array($this, [
            self::StreakWorkoutBest,
            self::StreakMealBest,
            self::StreakHydrationBest,
            self::StreakOverallBest,
        ], true);
    }

    /**
     * Filament select options.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = self::label($case);
        }

        return $options;
    }

    public static function label(self $metric): string
    {
        return match ($metric) {
            self::WorkoutPoints => 'Workout Points',
            self::MealPoints => 'Meal Points',
            self::HydrationPoints => 'Hydration Points',
            self::TotalPoints => 'Total Points',
            self::Level => 'Level',
            self::StreakWorkout => 'Workout Streak (current days)',
            self::StreakWorkoutBest => 'Workout Streak (best ever)',
            self::StreakMeal => 'Meal Streak (current days)',
            self::StreakMealBest => 'Meal Streak (best ever)',
            self::StreakHydration => 'Hydration Streak (current days)',
            self::StreakHydrationBest => 'Hydration Streak (best ever)',
            self::StreakOverall => 'Full Day Streak (current days)',
            self::StreakOverallBest => 'Full Day Streak (best ever)',
        };
    }
}

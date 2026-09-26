<?php

namespace App\Services;

use App\Enums\BadgeMetric;
use App\Models\Badge;
use App\Models\BadgeRequirement;
use App\Models\FitnessScore;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserStreak;
use App\Notifications\ExpoNotification;
use Illuminate\Support\Collection;

/**
 * Evaluates badges against a user's latest fitness score and streak records.
 *
 * Score metrics read the most recent `fitness_scores` row, which is a rolling
 * 7-day window — so a badge threshold is sustained performance, not a
 * one-day spike. Streak metrics read `user_streaks`.
 */
class BadgeService
{
    public function __construct(
        protected StreakService $streakService
    ) {}

    /**
     * All active badges with their current evaluation for a user.
     *
     * @return array{badges: Collection, unlocked_count: int, total_count: int, scores: array}
     */
    public function getJourney(User $user): array
    {
        $points = $this->currentPoints($user);
        $streaks = $this->streakValues($user);
        $unlocked = UserBadge::where('user_id', $user->id)
            ->get(['badge_id', 'unlocked_at'])
            ->mapWithKeys(fn ($row) => [$row->badge_id => $row->unlocked_at?->toIso8601String()]);

        $badges = Badge::with('requirements')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Badge $badge) use ($points, $streaks, $unlocked) {
                return array_merge(
                    $this->evaluateBadge($badge, $points, $streaks),
                    [
                        'slug' => $badge->slug,
                        'name' => $badge->name,
                        'description' => $badge->description,
                        'icon' => $badge->icon,
                        'tier' => $badge->tier,
                        'category' => $badge->category,
                        'sort_order' => $badge->sort_order,
                        'unlocked' => $unlocked->has($badge->id),
                        'unlocked_at' => $unlocked->get($badge->id),
                    ]
                );
            })
            ->sortBy([
                fn ($a, $b) => $b['unlocked'] <=> $a['unlocked'],
                fn ($a, $b) => $a['sort_order'] <=> $b['sort_order'],
            ])
            ->values();

        return [
            'badges' => $badges,
            'unlocked_count' => $unlocked->count(),
            'total_count' => $badges->count(),
            'points' => $points,
            'scores' => $points, // Compatibility alias for frontend components
            'streaks' => $streaks,
        ];
    }

    /**
     * Evaluate a single badge without persisting anything.
     *
     * @param  array<string, float|null>  $scores
     * @param  array<string, int>  $streaks
     * @return array<string, mixed>
     */
    public function evaluateBadge(Badge $badge, array $scores, array $streaks): array
    {
        $requirements = $badge->requirements;
        $perMetric = [];
        $allSatisfied = true;
        $ratios = [];

        foreach ($requirements as $requirement) {
            $value = $this->valueFor($requirement->metric, $scores, $streaks);
            $satisfied = $requirement->isSatisfiedBy($value);

            $perMetric[$requirement->metric->value] = $value;
            $ratios[] = $requirement->ratioFor($value);
            $allSatisfied = $allSatisfied && $satisfied;
        }

        // A badge with no requirements can never be earned.
        if ($requirements->isEmpty()) {
            $allSatisfied = false;
        }

        return [
            'badge_id' => $badge->id,
            'earned' => $allSatisfied,
            'progress' => $ratios ? round(array_sum($ratios) / count($ratios) * 100, 1) : 0.0,
            'per_metric' => $perMetric,
            'requirements' => $requirements->map(fn (BadgeRequirement $r) => [
                'metric' => $r->metric->value,
                'metric_label' => BadgeMetric::label($r->metric),
                'operator' => $r->operator,
                'threshold' => (float) $r->threshold,
            ])->values(),
        ];
    }

    /**
     * Persist any newly earned badges and notify the user about them.
     *
     * Idempotent: the unique (user_id, badge_id) index means re-running this
     * never creates a duplicate.
     *
     * @return Collection<int, UserBadge>
     */
    public function syncUnlocks(User $user, bool $notify = true): Collection
    {
        $journey = $this->getJourney($user);
        $periodEnd = $this->periodEnd($user);
        $newlyUnlocked = collect();

        foreach ($journey['badges'] as $evaluation) {
            if (! $evaluation['earned'] || $evaluation['unlocked']) {
                continue;
            }

            $userBadge = UserBadge::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'badge_id' => $evaluation['badge_id'],
                ],
                [
                    'unlocked_at' => now(),
                    'period_end' => $periodEnd,
                    'score_snapshot' => [
                        'scores' => $journey['scores'],
                        'streaks' => $journey['streaks'],
                    ],
                ]
            );

            if ($userBadge->wasRecentlyCreated) {
                $newlyUnlocked->push($userBadge->load('badge'));
            }
        }

        if ($notify) {
            $this->notifyUnlocks($user, $newlyUnlocked);
        }

        return $newlyUnlocked;
    }

    /**
     * Sync streaks then badges — the single entry point used by the dashboard.
     *
     * @return Collection<int, UserBadge>
     */
    public function refresh(User $user, bool $notify = true): Collection
    {
        $this->streakService->syncAll($user);

        return $this->syncUnlocks($user, $notify);
    }

    /**
     * Live points and level metrics from the user record.
     *
     * @return array<string, float|int|null>
     */
    protected function currentPoints(User $user): array
    {
        $workout = (int) ($user->workout_points ?? 0);
        $meal = (int) ($user->meal_points ?? 0);
        $hydration = (int) ($user->hydration_points ?? 0);
        $total = (int) ($user->total_points ?? 0);
        $level = (int) ($user->level ?? 1);

        return [
            'workout_points' => $workout,
            'meal_points' => $meal,
            'hydration_points' => $hydration,
            'total_points' => $total,
            'level' => $level,
            // Short aliases
            'workout' => $workout,
            'meal' => $meal,
            'hydration' => $hydration,
            'total' => $total,
        ];
    }

    /**
     * Streak values keyed by the metric name they satisfy.
     *
     * @return array<string, int>
     */
    protected function streakValues(User $user): array
    {
        $streaks = UserStreak::where('user_id', $user->id)->get()->keyBy('streak_type');
        $values = [];

        foreach (BadgeMetric::cases() as $metric) {
            if ($metric->streakType() === null) {
                continue;
            }

            $streak = $streaks->get($metric->streakType());
            $values[$metric->value] = $streak ? $streak->valueFor($metric->value) : 0;
        }

        return $values;
    }

    /**
     * @param  array<string, float|int|null>  $points
     * @param  array<string, int>  $streaks
     */
    protected function valueFor(BadgeMetric $metric, array $points, array $streaks): ?float
    {
        if ($metric->isPointMetric()) {
            return (float) ($points[$metric->value] ?? 0);
        }

        return (float) ($streaks[$metric->value] ?? 0);
    }

    protected function periodEnd(User $user): ?string
    {
        return now()->toDateString();
    }

    /**
     * @param  Collection<int, UserBadge>  $unlocks
     */
    protected function notifyUnlocks(User $user, Collection $unlocks): void
    {
        foreach ($unlocks as $unlock) {
            $user->notify(new ExpoNotification(
                title: "Achievement Unlocked: {$unlock->badge->name}",
                body: $unlock->badge->description ?? 'You earned a new badge.',
                data: [
                    'type' => 'achievement',
                    'badge_slug' => $unlock->badge->slug,
                    'badge_name' => $unlock->badge->name,
                    'tier' => $unlock->badge->tier,
                ],
            ));
        }
    }
}

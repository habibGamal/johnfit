<?php

use App\Enums\BadgeMetric;
use App\Models\Badge;
use App\Models\FitnessScore;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserDailyItem;
use App\Models\UserDailySchedule;
use App\Services\BadgeService;
use App\Services\StreakService;
use Carbon\Carbon;

/**
 * Create a badge with the given rules.
 *
 * @param  array<int, array{0: string, 1: float}>  $requirements
 */
function makeBadge(string $slug, array $requirements, array $overrides = []): Badge
{
    $badge = Badge::create(array_merge([
        'slug' => $slug,
        'name' => ucfirst(str_replace('-', ' ', $slug)),
        'description' => 'Test badge',
        'icon' => 'Trophy',
        'tier' => 'bronze',
        'category' => 'workout',
        'is_active' => true,
        'sort_order' => 1,
    ], $overrides));

    foreach ($requirements as [$metric, $threshold]) {
        $badge->requirements()->create([
            'metric' => BadgeMetric::from($metric),
            'operator' => 'gte',
            'threshold' => $threshold,
        ]);
    }

    return $badge->load('requirements');
}

function makeUser(): User
{
    return User::factory()->create(['assessment_completed_at' => now()]);
}

function giveScore(User $user, float $workout, ?float $meal = null, ?float $total = null): void
{
    FitnessScore::updateOrCreate(
        ['user_id' => $user->id, 'period_end' => Carbon::today()->toDateString()],
        [
            'period_start' => Carbon::today()->subDays(6)->toDateString(),
            'period_days' => 7,
            'workout_score' => $workout,
            'meal_score' => $meal ?? 0,
            'inbody_score' => null,
            'total_score' => $total ?? $workout,
        ]
    );
}

describe('Badge evaluation', function () {
    it('unlocks a single-rule badge once the score reaches the threshold', function () {
        $user = makeUser();
        makeBadge('strong-enough', [['workout', 50]]);

        giveScore($user, 60);

        $unlocked = app(BadgeService::class)->syncUnlocks($user, notify: false);

        expect($unlocked)->toHaveCount(1);
        expect(UserBadge::where('user_id', $user->id)->count())->toBe(1);
    });

    it('does not unlock a single-rule badge below the threshold', function () {
        $user = makeUser();
        makeBadge('strong-enough', [['workout', 50]]);

        giveScore($user, 49.9);

        expect(app(BadgeService::class)->syncUnlocks($user, notify: false))->toHaveCount(0);
    });

    it('requires every rule to pass, not just one', function () {
        $user = makeUser();
        // Hero: workout >= 90 AND meal >= 100
        makeBadge('hero', [['workout', 90], ['meal', 100]], ['tier' => 'platinum']);

        // Workout passes, meal does not.
        giveScore($user, 92, 50);

        expect(app(BadgeService::class)->syncUnlocks($user, notify: false))->toHaveCount(0);
        expect(UserBadge::where('user_id', $user->id)->count())->toBe(0);
    });

    it('unlocks a multi-rule badge when all rules pass', function () {
        $user = makeUser();
        makeBadge('hero', [['workout', 90], ['meal', 100]], ['tier' => 'platinum']);

        giveScore($user, 90, 100);

        expect(app(BadgeService::class)->syncUnlocks($user, notify: false))->toHaveCount(1);
    });

    it('reports partial progress while a multi-rule badge is incomplete', function () {
        $user = makeUser();
        $badge = makeBadge('hero', [['workout', 90], ['meal', 100]]);

        giveScore($user, 90, 50);

        $journey = app(BadgeService::class)->getJourney($user);
        $hero = $journey['badges']->firstWhere('slug', 'hero');

        expect($hero['earned'])->toBeFalse();
        // 100% on workout, 50% on meal -> 75% overall.
        expect($hero['progress'])->toBe(75.0);
        expect($hero['per_metric'])->toBe(['workout' => 90.0, 'meal' => 50.0]);
    });

    it('is idempotent when run repeatedly', function () {
        $user = makeUser();
        makeBadge('strong-enough', [['workout', 50]]);

        giveScore($user, 80);

        $service = app(BadgeService::class);

        expect($service->syncUnlocks($user, notify: false))->toHaveCount(1);
        expect($service->syncUnlocks($user, notify: false))->toHaveCount(0);
        expect($service->syncUnlocks($user, notify: false))->toHaveCount(0);
        expect(UserBadge::where('user_id', $user->id)->count())->toBe(1);
    });

    it('ignores inactive badges', function () {
        $user = makeUser();
        makeBadge('retired', [['workout', 10]], ['is_active' => false]);

        giveScore($user, 100);

        $journey = app(BadgeService::class)->getJourney($user);

        expect($journey['badges'])->toHaveCount(0);
        expect($journey['total_count'])->toBe(0);
    });

    it('never unlocks a badge that has no requirements', function () {
        $user = makeUser();
        makeBadge('empty', []);

        giveScore($user, 100);

        expect(app(BadgeService::class)->syncUnlocks($user, notify: false))->toHaveCount(0);
    });

    it('evaluates the lte operator as an upper bound', function () {
        $user = makeUser();
        $badge = makeBadge('under-weight', [['workout', 60]]);
        $badge->requirements()->update(['operator' => 'lte']);

        giveScore($user, 50);

        expect(app(BadgeService::class)->syncUnlocks($user, notify: false))->toHaveCount(1);
    });
});


describe('Streak tracking', function () {
    /**
     * Log a completed workout item on the given dates.
     *
     * @param  array<int, Carbon>  $dates
     */
    function logWorkouts(User $user, array $dates): void
    {
        foreach ($dates as $date) {
            $schedule = UserDailySchedule::create([
                'user_id' => $user->id,
                'date' => $date->toDateString(),
                'target_score' => 10,
                'earned_score' => 0,
                'is_locked' => false,
            ]);

            UserDailyItem::create([
                'daily_schedule_id' => $schedule->id,
                'type' => 'workout',
                'item_name' => 'Bench Press',
                'target_details' => [],
                'points' => 10,
                'is_completed' => true,
                'completed_at' => $date->copy()->setTime(18, 0),
                'status' => 'active',
                'order_index' => 0,
            ]);
        }
    }

    it('counts consecutive workout days as a current streak', function () {
        $user = makeUser();

        logWorkouts($user, [
            Carbon::today()->subDays(2),
            Carbon::today()->subDay(),
            Carbon::today(),
        ]);

        $streak = app(StreakService::class)->sync($user, 'workout');

        expect($streak->current_streak)->toBe(3);
        expect($streak->longest_streak)->toBe(3);
    });

    it('keeps the streak alive when today has not been logged yet', function () {
        $user = makeUser();

        logWorkouts($user, [
            Carbon::today()->subDay(),
            Carbon::today()->subDays(2),
        ]);

        $streak = app(StreakService::class)->sync($user, 'workout');

        // The day is not over, so yesterday's run still counts.
        expect($streak->current_streak)->toBe(2);
    });

    it('breaks the current streak after a missed day but keeps the record', function () {
        $user = makeUser();
        $service = app(StreakService::class);

        // A 5-day run ending two days ago.
        logWorkouts($user, [
            Carbon::today()->subDays(6),
            Carbon::today()->subDays(5),
            Carbon::today()->subDays(4),
            Carbon::today()->subDays(3),
            Carbon::today()->subDays(2),
        ]);

        $service->sync($user, 'workout');
        expect($service->sync($user, 'workout')->longest_streak)->toBe(5);

        // A single day today does not restore the earlier run.
        logWorkouts($user, [Carbon::today()]);
        $after = $service->sync($user, 'workout');

        expect($after->current_streak)->toBe(1);
        expect($after->longest_streak)->toBe(5);
    });

    it('resets both counters when the user has no activity', function () {
        $user = makeUser();

        $streak = app(StreakService::class)->sync($user, 'workout');

        expect($streak->current_streak)->toBe(0);
        expect($streak->longest_streak)->toBe(0);
    });

    it('unlocks a streak badge once the current streak reaches the threshold', function () {
        $user = makeUser();
        makeBadge('consistency-king', [['streak_workout', 3]], ['category' => 'streak']);

        logWorkouts($user, [Carbon::today()->subDay(), Carbon::today()]);

        expect(app(BadgeService::class)->syncUnlocks($user, notify: false))->toHaveCount(1);
    });

    it('awards a best-streak badge from the all-time record after the streak breaks', function () {
        $user = makeUser();
        makeBadge('unbreakable', [['streak_workout_best', 3]], ['category' => 'streak']);

        logWorkouts($user, [
            Carbon::today()->subDays(5),
            Carbon::today()->subDays(4),
            Carbon::today()->subDays(3),
        ]);

        app(StreakService::class)->sync($user, 'workout');

        // Nothing recent — only the persisted record qualifies.
        expect(app(BadgeService::class)->syncUnlocks($user, notify: false))->toHaveCount(1);
    });
});

describe('Achievements page', function () {
    it('renders the journey for an authenticated user', function () {
        $user = makeUser();
        makeBadge('strong-enough', [['workout', 50]]);
        giveScore($user, 60);

        $response = $this->actingAs($user)->get(route('achievements.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Achievements/Index')
            ->has('journey.badges')
            ->has('journey.scores')
            ->has('journey.streaks')
        );
    });

    it('redirects unauthenticated users to login', function () {
        $this->get(route('achievements.index'))->assertRedirect(route('login'));
    });

    it('awards earned badges when the page is visited', function () {
        $user = makeUser();
        makeBadge('strong-enough', [['workout', 50]]);
        giveScore($user, 60);

        $this->actingAs($user)->get(route('achievements.index'))->assertOk();

        expect(UserBadge::where('user_id', $user->id)->count())->toBe(1);
    });
});

<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDailyItem;
use App\Models\UserDailySchedule;
use App\Models\UserDailyWaterLog;
use App\Models\UserStreak;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Persisted streak tracking.
 *
 * Streaks used to be recomputed live on every dashboard load, which only ever
 * answered "how long is the current streak". Persisting them lets us keep an
 * all-time `longest_streak` record, which is what makes streak badges like
 * "30 days without a break" achievable.
 */
class StreakService
{
    /**
     * Every streak type we track.
     *
     * @return array<int, string>
     */
    public function types(): array
    {
        return ['workout', 'meal', 'hydration', 'overall'];
    }

    /**
     * Recompute and persist the streak for a user and type.
     *
     * Short-circuits when the streak was already evaluated for today, because
     * the answer can only change once a day has rolled over or the user has
     * logged new activity.
     */
    public function sync(User $user, string $streakType): UserStreak
    {
        $streak = UserStreak::firstOrNew([
            'user_id' => $user->id,
            'streak_type' => $streakType,
        ]);

        if ($streak->exists
            && $streak->updated_at !== null
            && $streak->updated_at->isToday()
            && ! $this->hasNewActivitySince($user, $streakType, $streak->updated_at)) {
            return $streak;
        }

        $activeDates = $this->activeDates($user, $streakType);

        $streak->current_streak = $this->currentStreak($activeDates);
        $streak->longest_streak = max(
            (int) $streak->longest_streak,
            $this->longestStreak($activeDates)
        );
        $streak->last_activity_date = $activeDates->last();
        $streak->save();

        return $streak;
    }

    /**
     * Sync every streak type for a user.
     *
     * @return Collection<int, UserStreak>
     */
    public function syncAll(User $user): Collection
    {
        return collect($this->types())
            ->map(fn (string $type) => $this->sync($user, $type))
            ->values();
    }

    /**
     * Sync all streak types for every user that has tracking activity.
     */
    public function syncAllUsers(): int
    {
        $count = 0;

        User::query()
            ->where(function ($q) {
                $q->whereHas('dailySchedules')->orWhereHas('dailyWaterLogs');
            })
            ->chunkById(100, function ($users) use (&$count) {
                foreach ($users as $user) {
                    $this->syncAll($user);
                    $count++;
                }
            });

        return $count;
    }

    /**
     * The dates on which this streak type counted as activity, ascending.
     *
     * @return Collection<int, string>
     */
    protected function activeDates(User $user, string $streakType): Collection
    {
        return match ($streakType) {
            'workout', 'meal' => $this->itemActivityDates($user, $streakType),
            'hydration' => $this->hydrationActivityDates($user),
            'overall' => $this->fullDayActivityDates($user),
            default => collect(),
        };
    }


    /**
     * A day counts for the workout/meal streak when at least one item of that
     * type was completed.
     *
     * @return Collection<int, string>
     */
    protected function itemActivityDates(User $user, string $type): Collection
    {
        return UserDailyItem::query()
            ->whereHas('schedule', fn ($q) => $q->where('user_id', $user->id))
            ->where('type', $type)
            ->where('status', '!=', 'voided')
            ->where('is_completed', true)
            ->join('user_daily_schedules', 'user_daily_schedules.id', '=', 'user_daily_items.daily_schedule_id')
            ->distinct()
            ->orderBy('user_daily_schedules.date')
            ->pluck('user_daily_schedules.date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    protected function hydrationActivityDates(User $user): Collection
    {
        return UserDailyWaterLog::where('user_id', $user->id)
            ->where('is_completed', true)
            ->orderBy('date')
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->values();
    }

    /**
     * A full day means every active item on the schedule was completed, which
     * is the same rule the schedule itself uses to set `is_completed`.
     *
     * @return Collection<int, string>
     */
    protected function fullDayActivityDates(User $user): Collection
    {
        return UserDailySchedule::where('user_id', $user->id)
            ->where('is_locked', false)
            ->where('target_score', '>', 0)
            ->whereColumn('earned_score', '>=', 'target_score')
            ->orderBy('date')
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->values();
    }

    /**
     * Walk the active dates backwards from today to get the live streak.
     *
     * Today not being active yet does not break the streak — the day simply
     * has not been finished, so counting starts from yesterday.
     *
     * @param  Collection<int, string>  $dates
     */
    protected function currentStreak(Collection $dates): int
    {
        if ($dates->isEmpty()) {
            return 0;
        }

        $set = $dates->flip();
        $cursor = Carbon::today();

        if (! $set->has($cursor->toDateString())) {
            $cursor->subDay();

            if (! $set->has($cursor->toDateString())) {
                return 0;
            }
        }

        $streak = 0;

        while ($set->has($cursor->toDateString())) {
            $streak++;
            $cursor->subDay();
        }

        return $streak;
    }

    /**
     * Longest run of consecutive days anywhere in the history.
     *
     * @param  Collection<int, string>  $dates
     */
    protected function longestStreak(Collection $dates): int
    {
        if ($dates->isEmpty()) {
            return 0;
        }

        $longest = 1;
        $run = 1;
        $previous = Carbon::parse($dates->first());

        for ($i = 1; $i < $dates->count(); $i++) {
            $current = Carbon::parse($dates->get($i));

            $run = $current->diffInDays($previous) === 1 ? $run + 1 : 1;
            $longest = max($longest, $run);
            $previous = $current;
        }

        return $longest;
    }

    /**
     * Whether the user logged anything relevant since the streak was last
     * written, which would invalidate the same-day short circuit.
     */
    protected function hasNewActivitySince(User $user, string $streakType, Carbon $since): bool
    {
        return match ($streakType) {
            'workout', 'meal' => UserDailyItem::query()
                ->whereHas('schedule', fn ($q) => $q->where('user_id', $user->id))
                ->where('type', $streakType)
                ->where('status', '!=', 'voided')
                ->where('is_completed', true)
                ->where('completed_at', '>', $since)
                ->exists(),

            'hydration' => UserDailyWaterLog::where('user_id', $user->id)
                ->where('completed_at', '>', $since)
                ->exists(),

            'overall' => UserDailySchedule::where('user_id', $user->id)
                ->where('is_locked', false)
                ->where('target_score', '>', 0)
                ->whereColumn('earned_score', '>=', 'target_score')
                ->where('updated_at', '>', $since)
                ->exists(),

            default => false,
        };
    }
}

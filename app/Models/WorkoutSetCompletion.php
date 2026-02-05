<?php

namespace App\Models;

use App\Observers\WorkoutSetCompletionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([WorkoutSetCompletionObserver::class])]
class WorkoutSetCompletion extends Model
{
    /** @use HasFactory<\Database\Factories\WorkoutSetCompletionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workout_plan_id',
        'day',
        'workout_id',
        'set_number',
        'weight',
        'reps',
        'completed',
        'session_date',
    ];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'weight' => 'decimal:2',
            'reps' => 'integer',
            'set_number' => 'integer',
            'session_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workoutPlan(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlan::class);
    }

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    /**
     * Get the previous session's sets for the same workout.
     * Used for showing "PREVIOUS" column suggestions.
     */
    public static function getPreviousSessionSets(
        int $userId,
        int $workoutId,
        ?string $beforeDate = null
    ): \Illuminate\Database\Eloquent\Collection {
        $query = static::where('user_id', $userId)
            ->where('workout_id', $workoutId)
            ->where('completed', true);

        if ($beforeDate) {
            $query->where('session_date', '<', $beforeDate);
        }

        // Get the most recent session date
        $lastSessionDate = (clone $query)->orderByDesc('session_date')->value('session_date');

        if (! $lastSessionDate) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return static::where('user_id', $userId)
            ->where('workout_id', $workoutId)
            ->where('session_date', $lastSessionDate)
            ->where('completed', true)
            ->orderBy('set_number')
            ->get();
    }
}

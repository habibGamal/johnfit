<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\Expo\ExpoPushToken;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'expo_token',
        'water_target_mode',
        'admin_water_target_ml',
        'water_multiplier_per_kg',
        'admin_water_notes',
        'allow_user_water_override',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'assessment_completed_at' => 'datetime',
            'expo_token' =>  'string',
            'admin_water_target_ml' => 'integer',
            'water_multiplier_per_kg' => 'float',
            'allow_user_water_override' => 'boolean',
        ];
    }


    public function routeNotificationForExpo(): ?ExpoPushToken
    {
        return $this->expo_token
            ? new ExpoPushToken($this->expo_token)
            : null;
    }

    /**
     * The plan assignments that belong to the user.
     */
    public function planAssignments(): HasMany
    {
        return $this->hasMany(UserPlanAssignment::class);
    }

    /**
     * The daily schedules that belong to the user.
     */
    public function dailySchedules(): HasMany
    {
        return $this->hasMany(UserDailySchedule::class);
    }

    /**
     * Today's daily schedule for the user.
     */
    public function todaySchedule(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserDailySchedule::class)->whereDate('date', now()->toDateString());
    }

    /**
     * The InBody logs that belong to the user.
     */
    public function inBodyLogs()
    {
        return $this->hasMany(InBodyLog::class);
    }

    public function dailyWaterLogs(): HasMany
    {
        return $this->hasMany(UserDailyWaterLog::class);
    }

    public function todayWaterLog(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserDailyWaterLog::class)->whereDate('date', now()->toDateString());
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasCompletedAssessment(): bool
    {
        return $this->assessment_completed_at !== null;
    }

    public function assessmentAnswers(): HasMany
    {
        return $this->hasMany(UserAssessmentAnswer::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): ?\App\Models\Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->with('plan')
            ->latest()
            ->first();
    }

    public function latestSubscription(): ?\App\Models\Subscription
    {
        return $this->subscriptions()
            ->with('plan')
            ->latest()
            ->first();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Get the active workout plan assignment.
     */
    public function activeWorkoutPlanAssignment(): ?UserPlanAssignment
    {
        return $this->planAssignments()
            ->where('plan_type', 'workout')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->with('workoutPlan')
            ->latest()
            ->first();
    }

    /**
     * Get the active meal plan assignment.
     */
    public function activeMealPlanAssignment(): ?UserPlanAssignment
    {
        return $this->planAssignments()
            ->where('plan_type', 'meal')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->with('mealPlan')
            ->latest()
            ->first();
    }

    /**
     * Scope users who have an active subscription.
     */
    public function scopeWhereActiveSubscribers($query)
    {
        return $query->whereHas('subscriptions', function ($q) {
            $q->where('status', 'active')
                ->where('end_date', '>', now());
        });
    }

    /**
     * Scope users who have an active workout plan.
     */
    public function scopeWhereHasActiveWorkoutPlan($query)
    {
        return $query->whereHas('planAssignments', function ($q) {
            $q->where('plan_type', 'workout')
                ->where('status', 'active')
                ->where(function ($sub) {
                    $sub->whereNull('end_date')
                        ->orWhere('end_date', '>=', now()->toDateString());
                });
        });
    }

    /**
     * Scope users who have an active meal plan.
     */
    public function scopeWhereHasActiveMealPlan($query)
    {
        return $query->whereHas('planAssignments', function ($q) {
            $q->where('plan_type', 'meal')
                ->where('status', 'active')
                ->where(function ($sub) {
                    $sub->whereNull('end_date')
                        ->orWhere('end_date', '>=', now()->toDateString());
                });
        });
    }

    /**
     * Scope active subscribers who are missing an active workout plan.
     */
    public function scopeWhereNeedsWorkoutPlan($query)
    {
        $today = now()->toDateString();

        return $query->whereActiveSubscribers()->whereDoesntHave('planAssignments', function ($q) use ($today) {
            $q->where('plan_type', 'workout')
                ->where('status', 'active')
                ->where(function ($sub) use ($today) {
                    $sub->whereNull('end_date')
                        ->orWhere('end_date', '>=', $today);
                });
        });
    }

    /**
     * Scope active subscribers who are missing an active meal plan.
     */
    public function scopeWhereNeedsMealPlan($query)
    {
        $today = now()->toDateString();

        return $query->whereActiveSubscribers()->whereDoesntHave('planAssignments', function ($q) use ($today) {
            $q->where('plan_type', 'meal')
                ->where('status', 'active')
                ->where(function ($sub) use ($today) {
                    $sub->whereNull('end_date')
                        ->orWhere('end_date', '>=', $today);
                });
        });
    }

    /**
     * Scope active subscribers who need either a workout plan OR a meal plan assigned.
     */
    public function scopeWhereNeedsPlans($query)
    {
        $today = now()->toDateString();

        return $query->whereActiveSubscribers()->where(function ($q) use ($today) {
            $q->whereDoesntHave('planAssignments', function ($wq) use ($today) {
                $wq->where('plan_type', 'workout')
                    ->where('status', 'active')
                    ->where(function ($sub) use ($today) {
                        $sub->whereNull('end_date')->orWhere('end_date', '>=', $today);
                    });
            })
            ->orWhereDoesntHave('planAssignments', function ($mq) use ($today) {
                $mq->where('plan_type', 'meal')
                    ->where('status', 'active')
                    ->where(function ($sub) use ($today) {
                        $sub->whereNull('end_date')->orWhere('end_date', '>=', $today);
                    });
            });
        });
    }

    /**
     * Scope users with active plans expiring soon (<= $days remaining).
     */
    public function scopeWherePlansExpiringSoon($query, int $days = 3)
    {
        $today = now()->toDateString();
        $threshold = now()->addDays($days)->toDateString();

        return $query->whereHas('planAssignments', function ($q) use ($today, $threshold) {
            $q->where('status', 'active')
                ->whereNotNull('end_date')
                ->whereBetween('end_date', [$today, $threshold]);
        });
    }

    /**
     * Scope active subscribers requiring attention: missing a plan OR having a plan expiring soon.
     */
    public function scopeWhereActionRequired($query, int $days = 3)
    {
        $today = now()->toDateString();
        $threshold = now()->addDays($days)->toDateString();

        return $query->whereActiveSubscribers()->where(function ($q) use ($today, $threshold) {
            $q->whereDoesntHave('planAssignments', function ($wq) use ($today) {
                $wq->where('plan_type', 'workout')
                    ->where('status', 'active')
                    ->where(function ($sub) use ($today) {
                        $sub->whereNull('end_date')->orWhere('end_date', '>=', $today);
                    });
            })
            ->orWhereDoesntHave('planAssignments', function ($mq) use ($today) {
                $mq->where('plan_type', 'meal')
                    ->where('status', 'active')
                    ->where(function ($sub) use ($today) {
                        $sub->whereNull('end_date')->orWhere('end_date', '>=', $today);
                    });
            })
            ->orWhereHas('planAssignments', function ($eq) use ($today, $threshold) {
                $eq->where('status', 'active')
                    ->whereNotNull('end_date')
                    ->whereBetween('end_date', [$today, $threshold]);
            });
        });
    }

    /**
     * Scope active subscribers who have both workout and meal plans active and not expiring soon.
     */
    public function scopeWhereFullyCovered($query, int $days = 3)
    {
        $threshold = now()->addDays($days)->toDateString();

        return $query->whereActiveSubscribers()
            ->whereHas('planAssignments', function ($wq) use ($threshold) {
                $wq->where('plan_type', 'workout')
                    ->where('status', 'active')
                    ->where(function ($sub) use ($threshold) {
                        $sub->whereNull('end_date')->orWhere('end_date', '>', $threshold);
                    });
            })
            ->whereHas('planAssignments', function ($mq) use ($threshold) {
                $mq->where('plan_type', 'meal')
                    ->where('status', 'active')
                    ->where(function ($sub) use ($threshold) {
                        $sub->whereNull('end_date')->orWhere('end_date', '>', $threshold);
                    });
            });
    }
}

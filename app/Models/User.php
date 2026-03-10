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
            'expo_token' => ExpoPushToken::class,
        ];
    }

    public function routeNotificationForExpo(): ?ExpoPushToken
    {
        return $this->expo_token;
    }

    /**
     * The workout plans that belong to the user.
     */
    public function workoutPlans()
    {
        return $this->belongsToMany(WorkoutPlan::class);
    }

    /**
     * The meal plans that belong to the user.
     */
    public function mealPlans()
    {
        return $this->belongsToMany(MealPlan::class, 'user_meal_plan')->withTimestamps();
    }

    /**
     * The InBody logs that belong to the user.
     */
    public function inBodyLogs()
    {
        return $this->hasMany(InBodyLog::class);
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
            ->latest()
            ->first();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }
}

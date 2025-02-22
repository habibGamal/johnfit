<?php

namespace App\Providers;

use App\Models\WorkoutPlan;
use App\Policies\WorkoutPlanPolicy;
use Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
        Gate::policy(WorkoutPlan::class, WorkoutPlanPolicy::class);
    }
}

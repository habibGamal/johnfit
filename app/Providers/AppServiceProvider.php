<?php

namespace App\Providers;

use App\Listeners\HandleKashierWebhook;
use Asciisd\Kashier\Events\KashierWebhookHandled;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\PlanGeneratorInterface::class,
            \App\Services\PlanGenerationService::class
        );

        $this->app->bind(
            \App\Contracts\PlanGenerationEligibilityStrategyInterface::class,
            function ($app) {
                $strategyKey = config('plan_generation.eligibility_strategy', 'once_per_user');
                $strategies = config('plan_generation.eligibility_strategies', []);
                $strategyClass = $strategies[$strategyKey] ?? \App\Services\PlanGeneration\Strategies\OncePerUserStrategy::class;

                return $app->make($strategyClass);
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Model::unguard();

        Event::listen(KashierWebhookHandled::class, HandleKashierWebhook::class);

        if (request()->isSecure() || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}

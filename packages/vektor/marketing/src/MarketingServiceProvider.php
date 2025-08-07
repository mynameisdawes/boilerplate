<?php

namespace Vektor\Marketing;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Vektor\Marketing\Listeners\OnOrder;
use Vektor\Shop\Events\PaymentSuccess;

class MarketingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('marketing.php'),
            ], 'marketing');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'marketing');

        Event::listen(
            PaymentSuccess::class,
            [OnOrder::class, 'handle']
        );
    }
}

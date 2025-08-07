<?php

namespace Vektor\Stripe;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Vektor\Stripe\Http\Middleware\StripeModuleEnabled;

class StripeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('stripe_module_enabled', StripeModuleEnabled::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('stripe.php'),
            ], 'stripe');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'stripe');
    }
}

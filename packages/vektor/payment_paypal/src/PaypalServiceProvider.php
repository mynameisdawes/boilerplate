<?php

namespace Vektor\Paypal;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Vektor\Paypal\Http\Middleware\PaypalModuleEnabled;

class PaypalServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('paypal_module_enabled', PaypalModuleEnabled::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('paypal.php'),
            ], 'paypal');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'paypal');
    }
}

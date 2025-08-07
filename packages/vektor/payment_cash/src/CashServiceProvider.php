<?php

namespace Vektor\Cash;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Vektor\Cash\Http\Middleware\CashModuleEnabled;

class CashServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('cash_module_enabled', CashModuleEnabled::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('cash.php'),
            ], 'cash');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'cash');
    }
}

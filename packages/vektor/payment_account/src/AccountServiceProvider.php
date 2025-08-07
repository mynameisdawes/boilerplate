<?php

namespace Vektor\Account;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Vektor\Account\Http\Middleware\AccountModuleEnabled;

class AccountServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('account_module_enabled', AccountModuleEnabled::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('account.php'),
            ], 'account');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'account');
    }
}

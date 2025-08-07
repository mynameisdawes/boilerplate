<?php

namespace Vektor\PasswordlessAuth;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Vektor\PasswordlessAuth\Console\Commands\CleanExpiredTokens;
use Vektor\PasswordlessAuth\Http\Middleware\PasswordlessAuthModuleEnabled;

class PasswordlessAuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('passwordless_auth_module_enabled', PasswordlessAuthModuleEnabled::class);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('passwordless_auth.php'),
            ], 'passwordless_auth');

            $this->commands([
                CleanExpiredTokens::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'passwordless');
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'passwordless_auth');

        $this->app->singleton('passwordless_auth', function () {
            return new PasswordlessAuth();
        });

        // Register the event service provider
        $this->app->register(\Vektor\PasswordlessAuth\Providers\EventServiceProvider::class);
    }
}

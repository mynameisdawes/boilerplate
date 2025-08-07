<?php

namespace Vektor\ApiKeys;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Vektor\ApiKeys\Http\Middleware\VerifyApiKey;

class ApiKeysServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('api_verify_key', VerifyApiKey::class);

        $this->publishes([
            __DIR__.'/../publishes/ApiKey.php' => app_path('Nova/ApiKey.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('api_keys.php'),
            ], 'api_keys');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'api_keys');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}

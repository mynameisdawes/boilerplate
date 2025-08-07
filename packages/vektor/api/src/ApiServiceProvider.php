<?php

namespace Vektor\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Vektor\Api\Http\Middleware\VerifyStatelessCsrfToken;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('api_csrf', VerifyStatelessCsrfToken::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('api.php'),
            ], 'api');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'api');
    }
}

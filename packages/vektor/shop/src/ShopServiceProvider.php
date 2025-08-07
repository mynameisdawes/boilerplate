<?php

namespace Vektor\Shop;

use Illuminate\Auth\Events\Logout;
use Illuminate\Routing\Router;
use Illuminate\Session\SessionManager;
use Illuminate\Support\ServiceProvider;
use Vektor\Shop\Http\Middleware\ShopModuleEnabled;
use Vektor\Shop\Http\Middleware\ShopOnlyDisabled;
use Vektor\Shop\Http\Middleware\ShopRequiresAuth;
use Vektor\Shop\Http\Middleware\ShopRequiresVerified;

class ShopServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('shop_module_enabled', ShopModuleEnabled::class);
        $router->aliasMiddleware('shop_only_disabled', ShopOnlyDisabled::class);
        $router->aliasMiddleware('shop_requires_auth', ShopRequiresAuth::class);
        $router->aliasMiddleware('shop_requires_verified', ShopRequiresVerified::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->publishes([
            __DIR__.'/../publishes/Discount.php' => app_path('Nova/Discount.php'),
            __DIR__.'/../publishes/DiscountCode.php' => app_path('Nova/DiscountCode.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('shop.php'),
            ], 'shop');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shop');
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'shop');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->app->bind('cart', 'Vektor\Shop\Cart');
        $this->app->bind('customisations', 'Vektor\Shop\Customisations');

        $this->app['events']->listen(Logout::class, function () {
            if ($this->app['config']->get('shop.destroy_on_logout')) {
                $this->app->make(SessionManager::class)->forget('cart');
                $this->app->make(SessionManager::class)->forget('customisations');
            }
        });
    }
}

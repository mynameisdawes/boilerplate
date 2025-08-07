<?php

namespace Vektor\PurchaseOrder;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Vektor\PurchaseOrder\Http\Middleware\PurchaseOrderModuleEnabled;

class PurchaseOrderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('purchase_order_module_enabled', PurchaseOrderModuleEnabled::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('purchase_order.php'),
            ], 'purchase_order');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'purchase_order');
    }
}

<?php

namespace Vektor\OneCRM;

use Illuminate\Auth\Events\Registered;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Vektor\OneCRM\Console\Commands\ClearProducts;
use Vektor\OneCRM\Console\Commands\CreateProducts;
use Vektor\OneCRM\Console\Commands\ImportProducts;
use Vektor\OneCRM\Http\Middleware\OneCRMModuleEnabled;
use Vektor\OneCRM\Listeners\OnOrder;
use Vektor\OneCRM\Listeners\OnUserRegistration;
use Vektor\Shop\Events\PaymentSuccess;

class OneCRMServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('onecrm_module_enabled', OneCRMModuleEnabled::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('onecrm.php'),
            ], 'onecrm');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'onecrm');
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'onecrm');

        $this->commands([
            ImportProducts::class,
            ClearProducts::class,
            CreateProducts::class,
        ]);

        Event::listen(
            PaymentSuccess::class,
            [OnOrder::class, 'handle']
        );

        Event::listen(
            Registered::class,
            [OnUserRegistration::class, 'handle']
        );
    }
}

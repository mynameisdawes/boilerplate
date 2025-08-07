<?php

namespace Vektor\Pages;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Vektor\Pages\Console\Commands\PagesPagePublish;
use Vektor\Pages\Http\Middleware\BaseOverride;
use Vektor\Pages\Http\View\Composers\PageComposer;

class PagesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        View::composer(
            '*',
            PageComposer::class
        );

        $this->app->booted(function () {
            $routes = Route::getRoutes();
            if ($routes->count() > 0) {
                $routes = collect($routes);
                $base_routes = $routes->filter(function ($route) {
                    return 'base' == $route->getName();
                });

                if ($base_routes->count() > 0) {
                    $router = $this->app->make(Router::class);
                    $router->aliasMiddleware('base_override', BaseOverride::class);

                    $base_route = $base_routes->first();
                    $base_route->middleware('base_override');
                }
            }

            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });

        $this->publishes([
            __DIR__.'/../publishes/Page.php' => app_path('Nova/Page.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                PagesPagePublish::class,
            ]);

            // $this->publishes([
            //     __DIR__.'/../config/config.php' => config_path('pages.php'),
            // ], 'pages');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'pages');

        Blade::directive('page', function ($value) {
            return '<?php echo '.Utilities::class."::pageLink({$value}); ?>";
        });

        Blade::directive('page_title', function ($value) {
            return '<?php echo '.Utilities::class."::pageTitle({$value}); ?>";
        });
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'pages');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}

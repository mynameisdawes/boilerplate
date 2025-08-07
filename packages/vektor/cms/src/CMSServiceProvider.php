<?php

namespace Vektor\CMS;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class CMSServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../publishes/Navigation.php' => app_path('Nova/Navigation.php'),
            __DIR__.'/../publishes/NavigationItem.php' => app_path('Nova/NavigationItem.php'),
            __DIR__.'/../publishes/Tag.php' => app_path('Nova/Tag.php'),
        ]);

        if ($this->app->runningInConsole()) {
            // $this->publishes([
            //     __DIR__.'/../config/config.php' => config_path('cms.php'),
            // ], 'cms');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cms');

        Blade::directive('markdown', function ($value) {
            return '<?php echo '.Utilities::class."::markdownParse({$value}); ?>";
        });
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'cms');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}

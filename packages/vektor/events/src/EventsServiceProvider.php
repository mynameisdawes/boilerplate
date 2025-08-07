<?php

namespace Vektor\Events;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Vektor\Events\Console\Commands\EventsEventPublish;

class EventsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->booted(function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });

        $this->publishes([
            __DIR__.'/../publishes/Event.php' => app_path('Nova/Event.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                EventsEventPublish::class,
            ]);

            // $this->publishes([
            //     __DIR__.'/../config/config.php' => config_path('events.php'),
            // ], 'events');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'events');

        Blade::directive('event', function ($value) {
            return '<?php echo '.Utilities::class."::eventLink({$value}); ?>";
        });

        Blade::directive('event_title', function ($value) {
            return '<?php echo '.Utilities::class."::eventTitle({$value}); ?>";
        });
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'events');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}

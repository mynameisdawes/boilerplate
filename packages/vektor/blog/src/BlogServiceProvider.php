<?php

namespace Vektor\Blog;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Vektor\Blog\Console\Commands\BlogPostPublish;

class BlogServiceProvider extends ServiceProvider
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
            __DIR__.'/../publishes/Post.php' => app_path('Nova/Post.php'),
            __DIR__.'/../publishes/PostCategory.php' => app_path('Nova/PostCategory.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                BlogPostPublish::class,
            ]);

            // $this->publishes([
            //     __DIR__.'/../config/config.php' => config_path('posts.php'),
            // ], 'posts');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'posts');

        Blade::directive('post', function ($value) {
            return '<?php echo '.Utilities::class."::postLink({$value}); ?>";
        });

        Blade::directive('post_title', function ($value) {
            return '<?php echo '.Utilities::class."::postTitle({$value}); ?>";
        });
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'posts');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}

<?php

namespace YellowThree\VoyagerBlog;

use Illuminate\Support\ServiceProvider;
use YellowThree\Voyager\Plugins\PluginManager;

class BlogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'voyager-blog');
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Safe check and register plugin to manager
        if (class_exists(PluginManager::class)) {
            app(PluginManager::class)->register(new BlogPlugin());
        }
    }
}

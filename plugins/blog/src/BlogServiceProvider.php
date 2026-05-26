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

        \Livewire\Livewire::addLocation(
            viewPath: __DIR__.'/../resources/views/components',
            classNamespace: 'YellowThree\\VoyagerBlog',
        );

        if (class_exists(PluginManager::class)) {
            app(PluginManager::class)->register(new BlogPlugin());
        }
    }
}

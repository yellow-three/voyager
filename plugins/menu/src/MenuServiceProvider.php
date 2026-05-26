<?php

namespace YellowThree\VoyagerMenu;

use Illuminate\Support\ServiceProvider;
use YellowThree\Voyager\Plugins\PluginManager;

class MenuServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'voyager-menu');
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        \Livewire\Livewire::addLocation(
            viewPath: __DIR__.'/../resources/views/components',
            classNamespace: 'YellowThree\\VoyagerMenu',
        );

        if (class_exists(PluginManager::class)) {
            app(PluginManager::class)->register(new MenuPlugin());
        }
    }
}

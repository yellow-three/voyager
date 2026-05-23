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

        // Register MFC Livewire components for plugin use (e.g. @livewire() directives)
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::component('menu-list', \YellowThree\VoyagerMenu\Http\Livewire\MenuList::class);
            \Livewire\Livewire::component('menu-builder', \YellowThree\VoyagerMenu\Http\Livewire\MenuBuilder::class);
        }

        // Safe check and register plugin to manager
        if (class_exists(PluginManager::class)) {
            app(PluginManager::class)->register(new MenuPlugin());
        }
    }
}
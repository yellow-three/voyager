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

        // Register MFC Livewire components for plugin use (e.g. @livewire() directives)
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::component('blog-post-list', \YellowThree\VoyagerBlog\Http\Livewire\PostList::class);
            \Livewire\Livewire::component('blog-post-form', \YellowThree\VoyagerBlog\Http\Livewire\PostForm::class);
            \Livewire\Livewire::component('blog-page-list', \YellowThree\VoyagerBlog\Http\Livewire\PageList::class);
            \Livewire\Livewire::component('blog-page-form', \YellowThree\VoyagerBlog\Http\Livewire\PageForm::class);
            \Livewire\Livewire::component('blog-category-list', \YellowThree\VoyagerBlog\Http\Livewire\CategoryList::class);
            \Livewire\Livewire::component('blog-category-form', \YellowThree\VoyagerBlog\Http\Livewire\CategoryForm::class);
        }

        // Safe check and register plugin to manager
        if (class_exists(PluginManager::class)) {
            app(PluginManager::class)->register(new BlogPlugin());
        }
    }
}

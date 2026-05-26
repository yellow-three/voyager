<?php

namespace YellowThree\VoyagerBlog;

use YellowThree\Voyager\Plugins\BasePlugin;
use YellowThree\Voyager\Plugins\Contracts\MenuPlugin;

class BlogPlugin extends BasePlugin implements MenuPlugin
{
    public function name(): string
    {
        return 'voyager-blog';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function label(): string
    {
        return 'Blog Plugin';
    }

    public function description(): string
    {
        return 'Publish, categorize, and manage rich text posts, articles, and pages for your public website.';
    }

    public function author(): string
    {
        return 'Yellow Three Team';
    }

    /**
     * Injected sidebar menu items.
     *
     * @return array
     */
    public function menuItems(): array
    {
        return [
            ['title' => 'Blog Posts', 'route' => 'voyager.posts.index', 'icon' => 'news'],
            ['title' => 'Pages', 'route' => 'voyager.pages.index', 'icon' => 'file'],
            ['title' => 'Categories', 'route' => 'voyager.categories.index', 'icon' => 'folder'],
        ];
    }
}

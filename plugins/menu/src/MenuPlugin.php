<?php

namespace YellowThree\VoyagerMenu;

use YellowThree\Voyager\Plugins\BasePlugin;
use YellowThree\Voyager\Plugins\Contracts\MenuPlugin as MenuPluginContract;

class MenuPlugin extends BasePlugin implements MenuPluginContract
{
    public function name(): string
    {
        return 'voyager-menu';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function label(): string
    {
        return 'Menu Builder Plugin';
    }

    public function description(): string
    {
        return 'Drag-and-drop dynamic menu manager, supports nested items, routing, and customizable icons.';
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
            ['title' => 'Menu Builder', 'route' => 'voyager.menus.builder', 'icon' => 'list'],
        ];
    }
}

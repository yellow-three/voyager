# Plugin Development Guide

> Learn how to build plugins for `yellow-three/voyager` v3 using the `BasePlugin` abstract class and granular contract interfaces.

---

## Overview

Voyager v3 uses a plugin architecture where functionality is provided through plugins. Core features (BREAD, Media, Settings, Users, Roles) are built into the core package, while additional functionality is delivered via the plugin system.

---

## Plugin Structure

A Voyager plugin is a standard Laravel package with a few additional conventions:

```
my-voyager-plugin/
├── composer.json           # keywords: voyager-plugin + extra.laravel.providers
├── src/
│   ├── MyPlugin.php       # Extends BasePlugin, implements needed contracts
│   └── MyServiceProvider.php
├── routes/
│   └── web.php
├── resources/
│   └── views/
│       └── components/    # Livewire SFC/MFC views
├── migrations/
└── README.md
```

---

## composer.json Requirements

```json
{
    "name": "vendor/my-voyager-plugin",
    "type": "library",
    "keywords": ["laravel", "voyager-plugin"],
    "require": {
        "yellow-three/voyager": "^3.0"
    },
    "autoload": {
        "psr-4": {
            "Vendor\\MyPlugin\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Vendor\\MyPlugin\\MyServiceProvider"
            ]
        }
    }
}
```

---

## BasePlugin + Contracts

```php
<?php

namespace Vendor\MyPlugin;

use YellowThree\Voyager\Plugins\BasePlugin;
use YellowThree\Voyager\Plugins\Contracts\MenuPlugin;
use YellowThree\Voyager\Plugins\Contracts\FormfieldPlugin;

class MyPlugin extends BasePlugin implements MenuPlugin, FormfieldPlugin
{
    public function name(): string
    {
        return 'my-plugin';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function label(): string
    {
        return 'My Plugin';
    }

    public function description(): string
    {
        return 'A custom Voyager plugin.';
    }

    // MenuPlugin contract
    public function menuItems(): array
    {
        return [
            ['title' => 'My Section', 'route' => 'voyager.my-section.index', 'icon' => 'star'],
        ];
    }

    // FormfieldPlugin contract
    public function formFields(): array
    {
        return [
            \Vendor\MyPlugin\FormFields\MyCustomHandler::class,
        ];
    }
}
```

---

## Available Contracts

| Contract | Method(s) | Purpose |
|---|---|---|
| `FormfieldPlugin` | `formFields(): array` | Register custom FormField handlers |
| `MenuPlugin` | `menuItems(): array` | Add sidebar menu items |
| `WidgetPlugin` | `widgets(): array` | Add dashboard widgets |
| `AuthenticationPlugin` | `login()`, `logout()`, `forgotPassword()` | Replace auth layer (e.g., Sanctum) |
| `AuthorizationPlugin` | `authorize()`, `getPermissionsForUser()` | Replace authorization (e.g., Spatie) |
| `FilterPlugin` | `filterLayouts()`, `filterMenuItems()`, `filterMedia()` | Modify core collections |

---

## ServiceProvider

```php
<?php

namespace Vendor\MyPlugin;

use Illuminate\Support\ServiceProvider;
use YellowThree\Voyager\Plugins\PluginManager;

class MyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'my-plugin');
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if (class_exists(PluginManager::class)) {
            app(PluginManager::class)->register(new MyPlugin());
        }
    }
}
```

---

## Scaffold with CLI Generator

```bash
./vendor/bin/testbench voyager:make:plugin my-plugin
```

This generates the full skeleton automatically.

---

## Custom FormField Handler

```php
<?php

namespace Vendor\MyPlugin\FormFields;

use YellowThree\Voyager\FormFields\HandlerInterface;

class MyCustomHandler implements HandlerInterface
{
    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('my-plugin::formfields.my-custom', compact('row', 'dataType', 'dataTypeContent', 'options'));
    }

    public function editContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('my-plugin::formfields.my-custom', compact('row', 'dataType', 'dataTypeContent', 'options'));
    }

    public function getCodename()
    {
        return 'my_custom';
    }
}
```

---

## Dashboard Widget

```php
// In MyPlugin.php — implements WidgetPlugin
public function widgets(): array
{
    return [
        [
            'view'  => 'my-plugin::widgets.stats',
            'data'  => ['count' => MyModel::count()],
            'width' => 'w-1/3',  // Tailwind width class
        ],
    ];
}
```

Widget view (`resources/views/widgets/stats.blade.php`):

```html
<div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm">
    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">My Stats</h3>
    <p class="text-4xl font-bold text-primary-500 mt-2">{{ $count }}</p>
</div>
```

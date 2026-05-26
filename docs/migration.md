# Migration Guide — `tcg/voyager` → `yellow-three/voyager`

> This guide covers migrating from the original (now archived) `tcg/voyager` package to the community-maintained `yellow-three/voyager` v3.

---

## Overview

| | `tcg/voyager` (archived) | `yellow-three/voyager` v3 |
|---|---|---|
| Status | Archived (Feb 2025) | Active development |
| PHP Namespace | `TCG\Voyager` | `YellowThree\Voyager` |
| Packagist | `tcg/voyager` | `yellow-three/voyager` |
| PHP | ~7.x–~8.2 | ^8.3 |
| Laravel | ~8.x–~11.x | ^13.0 |
| Frontend | Bootstrap 3 + jQuery | Tailwind 4 + Alpine.js |

---

## Quick Migration

### 1. Remove old package

```bash
composer remove tcg/voyager
```

### 2. Require new package

```bash
composer require yellow-three/voyager:^3.0-alpha
```

### 3. Update ServiceProvider (if manually registered)

In `config/app.php` (if not using auto-discovery):

```php
// Remove:
TCG\Voyager\VoyagerServiceProvider::class,

// Add:
YellowThree\Voyager\VoyagerServiceProvider::class,
```

### 4. Update User model

```php
// Before:
use TCG\Voyager\Models\User;
class User extends \TCG\Voyager\Models\User {}

// After:
use YellowThree\Voyager\Models\User;
class User extends \YellowThree\Voyager\Models\User {}
```

### 5. Run Upgrade Wizard

```bash
php artisan voyager:upgrade
```

This handles:
- BREAD export from DB → JSON
- Namespace updates in `data_types.model_name`
- Config and asset republishing

---

## Namespace Reference

| Old Class | New Class |
|---|---|
| `TCG\Voyager\VoyagerServiceProvider` | `YellowThree\Voyager\VoyagerServiceProvider` |
| `TCG\Voyager\Facades\Voyager` | `YellowThree\Voyager\Facades\Voyager` |
| `TCG\Voyager\Models\User` | `YellowThree\Voyager\Models\User` |
| `TCG\Voyager\Models\Role` | `YellowThree\Voyager\Models\Role` |
| `TCG\Voyager\Models\Permission` | `YellowThree\Voyager\Models\Permission` |
| `TCG\Voyager\Models\Setting` | `YellowThree\Voyager\Models\Setting` |
| `TCG\Voyager\Models\DataType` | `YellowThree\Voyager\Models\DataType` |
| `TCG\Voyager\Models\DataRow` | `YellowThree\Voyager\Models\DataRow` |
| `TCG\Voyager\Http\Controllers\VoyagerBaseController` | `YellowThree\Voyager\Http\Controllers\VoyagerBaseController` |
| `TCG\Voyager\FormFields\AbstractHandler` | `YellowThree\Voyager\FormFields\HandlerInterface` |
| `TCG\Voyager\Traits\Translatable` | `YellowThree\Voyager\Traits\Translatable` |
| `TCG\Voyager\Traits\Resizable` | `YellowThree\Voyager\Traits\Resizable` |
| `TCG\Voyager\Traits\Spatial` | `YellowThree\Voyager\Traits\Spatial` |
| `TCG\Voyager\Actions\AbstractAction` | `YellowThree\Voyager\Actions\AbstractAction` |

---

## Custom FormFields

Old `app/FormFields/` pattern still works via shim (with deprecation warning). Migrate to the new plugin approach:

```php
// Old app/FormFields/MyHandler.php:
class MyHandler extends \TCG\Voyager\FormFields\AbstractHandler
{
    protected $codename = 'my_field';
    // ...
}

// New — register in AppServiceProvider:
use YellowThree\Voyager\Facades\Voyager;
use App\FormFields\MyHandler;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Voyager::registerFormField(MyHandler::class);
    }
}
```

---

## Custom Controllers

```php
// Old:
class PostsController extends \TCG\Voyager\Http\Controllers\VoyagerBaseController
{
    // ...
}

// New:
class PostsController extends \YellowThree\Voyager\Http\Controllers\VoyagerBaseController
{
    // ...
}
```

---

## BREAD Definitions

BREAD definitions are now stored as JSON files alongside your version control:

```
storage/voyager/breads/
├── posts.json
├── users.json
└── categories.json
```

Export existing DB BREAD definitions:

```bash
php artisan voyager:export-breads
```

See [bread-json.md](bread-json.md) for the JSON format specification.

---

## Widgets

The `Widget` facade approach is replaced by the `WidgetPlugin` contract:

```php
// Old:
Voyager::addAction(\App\Widgets\MyWidget::class);

// New:
class MyPlugin extends BasePlugin implements WidgetPlugin
{
    public function widgets(): array
    {
        return [
            ['view' => 'my-plugin::widgets.my-widget', 'data' => [], 'width' => 'w-full'],
        ];
    }
}
```

---

## Events

All 24 original events are preserved with the same class names under the new namespace:

```php
// Old:
use TCG\Voyager\Events\BreadDataAdded;

// New:
use YellowThree\Voyager\Events\BreadDataAdded;
```

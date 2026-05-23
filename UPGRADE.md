# Upgrade Guide — v2 → v3

> **Important:** Voyager v3 is a complete rewrite of the community fork `yellow-three/voyager`. It is **not** backward compatible with `tcg/voyager` (archived Feb 2025) or `thedevdojo/voyager`.

---

## Requirements

| Requirement | v2 (tcg/voyager) | v3 (yellow-three/voyager) |
|---|---|---|
| PHP | ~7.x / ~8.0 | ^8.3 |
| Laravel | ~8.x–~11.x | ^13.0 |
| Livewire | — | ^4.0 |
| CSS | Bootstrap 3 | Tailwind CSS 4 |

---

## Step 1 — Update composer.json

Replace `tcg/voyager` with `yellow-three/voyager`:

```bash
composer remove tcg/voyager
composer require yellow-three/voyager:^3.0
```

## Step 2 — Update Namespace Imports

All `TCG\Voyager` namespace references must be changed to `YellowThree\Voyager`:

```bash
# Find all TCG\Voyager references in your app:
grep -r "TCG\\Voyager" app/ --include="*.php"

# Replace (Linux/macOS):
find app/ -name "*.php" -exec sed -i 's/TCG\\Voyager/YellowThree\\Voyager/g' {} +
```

**Common replacements:**

| Old | New |
|---|---|
| `TCG\Voyager\Models\User` | `YellowThree\Voyager\Models\User` |
| `TCG\Voyager\Facades\Voyager` | `YellowThree\Voyager\Facades\Voyager` |
| `TCG\Voyager\Http\Controllers\VoyagerBaseController` | `YellowThree\Voyager\Http\Controllers\VoyagerBaseController` |
| `TCG\Voyager\Traits\Translatable` | `YellowThree\Voyager\Traits\Translatable` |
| `TCG\Voyager\Traits\Resizable` | `YellowThree\Voyager\Traits\Resizable` |

## Step 3 — Run the Upgrade Command

```bash
php artisan voyager:upgrade
```

This command will:
1. Migrate BREAD definitions from database to JSON format (`storage/voyager/breads/`)
2. Update `data_types.model_name` namespace references
3. Publish updated config and assets
4. Run any pending migrations

## Step 4 — Publish Updated Assets

```bash
php artisan vendor:publish --provider="YellowThree\Voyager\VoyagerServiceProvider" --force
```

## Step 5 — Update Custom FormFields

If you have custom FormFields in `app/FormFields/`, they will still work via the backward compatibility shim, but you will see a deprecation warning.

**Migrate to the plugin system:**

```php
// Old (deprecated, still works with warning):
// app/FormFields/MyCustomHandler.php

// New (recommended):
// Register via plugin in your AppServiceProvider:
Voyager::registerFormField(MyCustomHandler::class);
// Or create a full plugin — see docs/plugin-development.md
```

## Step 6 — Update config/voyager.php

Re-publish and review your voyager config:

```bash
php artisan vendor:publish --tag=voyager-config --force
```

Key changes:
- `controllers.namespace` → now `YellowThree\Voyager\Http\Controllers`
- New `bread.json_storage_path` option for JSON BREAD definitions

## Step 7 — Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

## Breaking Changes

| Feature | v2 Behavior | v3 Behavior |
|---|---|---|
| Namespace | `TCG\Voyager` | `YellowThree\Voyager` |
| Frontend | Bootstrap 3 + jQuery + Vue 2 | Tailwind CSS 4 + Alpine.js + Livewire 4 |
| BREAD storage | Database (`data_types`, `data_rows`) | JSON files (DB fallback kept) |
| FormField auto-discovery | `app/FormFields/` scanned | Plugin system (shim for old path) |
| Widget system | `Widget::run()` | Plugin `WidgetPlugin` contract |
| Build system | Laravel Mix / Webpack | Vite |

---

## Need Help?

- [Migration Guide](docs/migration.md)
- [Plugin Development](docs/plugin-development.md)
- [JSON BREAD Format](docs/bread-json.md)
- [GitHub Issues](https://github.com/yellow-three/voyager/issues)

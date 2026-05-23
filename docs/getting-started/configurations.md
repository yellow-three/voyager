# Configurations

After installing Voyager v3, you'll find a configuration file at `config/voyager.php`. This file can be published and customized:

```bash
php artisan vendor:publish --provider="YellowThree\Voyager\VoyagerServiceProvider" --tag=voyager-config
```

> If you cache your configuration files, run `php artisan config:clear` after making changes.

Below are the key configuration sections.

---

## Users

```php
'user' => [
    'add_default_role_on_register' => true,
    'default_role'                 => 'user',
    'admin_permission'             => 'browse_admin',
    'namespace'                    => App\Models\User::class,
    'redirect'                     => '/admin',
],
```

| Key | Description |
|---|---|
| `add_default_role_on_register` | Auto-assign default role to new users |
| `default_role` | Name of the default role |
| `admin_permission` | Permission required to access admin dashboard |
| `namespace` | Your app's User model class |
| `redirect` | Post-login redirect path |

---

## Controllers

```php
'controllers' => [
    'namespace' => 'YellowThree\Voyager\Http\Controllers',
],
```

The controller namespace for Voyager's API controllers. Override individual controllers by binding in your `AppServiceProvider`:

```php
$this->app->bind(
    \YellowThree\Voyager\Http\Controllers\VoyagerBreadController::class,
    \App\Http\Controllers\MyBreadController::class,
);
```

---

## Models

```php
'models' => [
    // 'namespace' => 'App\\',
],
```

Default namespace for models created from the Database Manager section.

---

## Storage

```php
'storage' => [
    'disk' => 'public',
],
```

The filesystem disk used for media storage. Can use any disk defined in `config/filesystems.php` (S3, GCS, etc.).

---

## Database

```php
'database' => [
    'tables' => [
        'hidden' => ['migrations', 'data_rows', 'data_types', 'menu_items', 'password_resets', 'permission_role', 'personal_access_tokens', 'settings'],
    ],
    'autoload_migrations' => true,
],
```

| Key | Description |
|---|---|
| `tables.hidden` | Tables hidden from the Database Manager UI |
| `autoload_migrations` | Auto-load Voyager migrations via `php artisan migrate` |

---

## BREAD (JSON Storage)

```php
'bread' => [
    'json_storage_path' => storage_path('voyager/breads'),
],
```

Path where JSON BREAD definitions are stored. Each BREAD gets its own `.json` file.

---

## Multilingual

```php
'multilingual' => [
    'enabled' => false,
    'default' => 'en',
    'locales' => ['en', 'fr', 'de'],
],
```

Enable multilingual support and specify available locales.

---

## Dashboard

```php
'dashboard' => [
    'navbar_items' => [
        'Profile' => [
            'route'      => 'voyager.profile',
            'icon_class' => 'voyager-person',
        ],
        'Home' => [
            'route'      => '/',
            'icon_class' => 'voyager-home',
        ],
        'Logout' => [
            'route'      => 'voyager.logout',
            'icon_class' => 'voyager-power',
        ],
    ],
    'widgets' => [],
],
```

Configure navbar dropdown items and dashboard widgets (registered via plugins).

---

## Additional CSS/JS

```php
'additional_css' => [
    // 'css/custom.css',
],
'additional_js' => [
    // 'js/custom.js',
],
```

Include custom stylesheets or JavaScript files in the admin panel. Paths are passed to Laravel's `asset()` helper.

---

## Google Maps

```php
'googlemaps' => [
    'key'    => env('GOOGLE_MAPS_KEY', ''),
    'center' => [
        'lat' => env('GOOGLE_MAPS_DEFAULT_CENTER_LAT', '32.715738'),
        'lng' => env('GOOGLE_MAPS_DEFAULT_CENTER_LNG', '-117.161084'),
    ],
    'zoom' => env('GOOGLE_MAPS_DEFAULT_ZOOM', 11),
],
```

Default settings for the Coordinates form field type.

---

## Allowed Mimetypes

```php
'allowed_mimetypes' => '*',
// Or restrict:
'allowed_mimetypes' => [
    'image/jpeg', 'image/png', 'image/gif', 'video/mp4',
],
```

Restrict file upload types in the Media Manager. Use `'*'` to allow all types.

# Installation

Installing Voyager v3 is straightforward. Make sure you've met the [prerequisites](prerequisites.md) first.

## Step 1: Require the Package

```bash
composer require yellow-three/voyager:^3.0-alpha
```

## Step 2: Configure Database

Create a database and add your credentials to `.env`:

```text
APP_URL=http://localhost
DB_HOST=127.0.0.1
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## Step 3: Install Voyager

You can install Voyager with or without dummy data.

### Without dummy data:

```bash
php artisan voyager:install
```

### With dummy data (recommended for evaluation):

```bash
php artisan voyager:install --with-dummy
```

The dummy data includes:
- 1 admin account
- Sample posts, pages, and categories
- Configuration settings
- BREAD definitions

## Step 4: Access the Admin Panel

Start your local development server:

```bash
php artisan serve
```

Then visit [http://localhost:8000/admin](http://localhost:8000/admin) in your browser.

### Default Login (dummy install)

| Field | Value |
|---|---|
| Email | `admin@admin.com` |
| Password | `password` |

## Creating an Admin User (manual install)

If you installed without dummy data, assign admin privileges to an existing user:

```bash
php artisan voyager:admin your@email.com
```

Or create a new admin user:

```bash
php artisan voyager:admin your@email.com --create
```

## Step 5: Publish Assets

After installation, you can publish config and assets:

```bash
php artisan vendor:publish --provider="YellowThree\Voyager\VoyagerServiceProvider" --tag=voyager-config
php artisan vendor:publish --provider="YellowThree\Voyager\VoyagerServiceProvider" --tag=voyager-assets
```

## Next Steps

- [Explore the BREAD system](../bread-json.md)
- [Read about the plugin system](../plugin-development.md)
- [Migrate from v2](../migration.md)

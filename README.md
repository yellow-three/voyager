<p align="center">
  <img width="400" src="https://s3.amazonaws.com/thecontrolgroup/voyager.png" alt="Voyager Admin">
</p>

<p align="center">
  <a href="https://packagist.org/packages/yellow-three/voyager"><img src="https://poser.pugx.org/yellow-three/voyager/v/stable.svg?format=flat" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/yellow-three/voyager"><img src="https://poser.pugx.org/yellow-three/voyager/downloads.svg?format=flat" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/yellow-three/voyager"><img src="https://poser.pugx.org/yellow-three/voyager/license.svg?format=flat" alt="License"></a>
</p>

> [!IMPORTANT]
> **This is `yellow-three/voyager` — a community fork of the archived `thedevdojo/voyager` package.**
> The original `tcg/voyager` / `thedevdojo/voyager` was archived in February 2025.
> This fork targets **Laravel 13, Livewire 4, Tailwind CSS 4** and is actively maintained.

---

# Voyager v3 — The Missing Laravel Admin

A powerful, modern Laravel admin panel built on:

| Technology | Version |
|---|---|
| Laravel | ^13.0 |
| Livewire | ^4.0 (SFC / MFC) |
| Tailwind CSS | 4.x |
| Alpine.js | ^3.0 |
| PHP | ^8.3 \| ^8.4 \| ^8.5 |
| Build | Vite |

---

## Quick Start

### 1. Require the Package

```bash
composer require yellow-three/voyager:^3.0-alpha
```

### 2. Install

```bash
# Without dummy data:
php artisan voyager:install

# With dummy data (1 admin, sample posts, categories, settings):
php artisan voyager:install --with-dummy
```

### 3. Access the Admin Panel

Start your dev server:

```bash
php artisan serve
```

Visit: [http://localhost:8000/admin](http://localhost:8000/admin)

**Default admin credentials (dummy install):**

| | |
|---|---|
| Email | `admin@admin.com` |
| Password | `password` |

---

## Migrating from tcg/voyager?

See the [Migration Guide](docs/migration.md) for full instructions on migrating from the original archived `tcg/voyager` package.

```bash
# Remove old package
composer remove tcg/voyager

# Install new package
composer require yellow-three/voyager:^3.0-alpha

# Run the upgrade wizard
php artisan voyager:upgrade
```

---

## What's New in v3

| Feature | Description |
|---|---|
| **Plugin System** | `BasePlugin` abstract + granular contracts (`FormfieldPlugin`, `MenuPlugin`, `WidgetPlugin`, …) |
| **Hybrid BreadManager** | JSON-first BREAD definitions with DB fallback for v2 compat |
| **Livewire 4 UI** | Full SFC/MFC admin panels replacing Bootstrap/Vue |
| **Tailwind CSS 4** | `@theme` token system, no `tailwind.config.js` |
| **First-party Plugins** | `yellow-three/voyager-blog`, `yellow-three/voyager-menu` |
| **Activity Log** | Built-in admin action logger with API |
| **Upgrade Wizard** | CLI + web wizard for v2 → v3 migration |
| **JSON BREAD** | Version-controlled BREAD definitions (`storage/voyager/breads/*.json`) |

---

## First-Party Plugins

| Plugin | Package | Description |
|---|---|---|
| Menu Builder | `yellow-three/voyager-menu` | Drag-and-drop menu manager |
| Blog | `yellow-three/voyager-blog` | Posts, Pages, Categories |

```bash
composer require yellow-three/voyager-menu
composer require yellow-three/voyager-blog
```

---

## Creating an Admin User

```bash
# Assign admin role to existing user:
php artisan voyager:admin your@email.com

# Create a new admin user:
php artisan voyager:admin your@email.com --create
```

---

## Documentation

| Document | Description |
|---|---|
| [UPGRADE.md](UPGRADE.md) | v2 → v3 upgrade guide |
| [CHANGELOG.md](CHANGELOG.md) | Release history |
| [docs/migration.md](docs/migration.md) | `tcg/voyager` → `yellow-three/voyager` namespace migration |
| [docs/plugin-development.md](docs/plugin-development.md) | Building plugins |
| [docs/bread-json.md](docs/bread-json.md) | JSON BREAD format reference |

---

## Contributing

This is a community-maintained fork. Issues and PRs are welcome at [yellow-three/voyager](https://github.com/yellow-three/voyager).

```bash
git clone https://github.com/yellow-three/voyager.git
cd voyager
composer install
npm install
npm run build
./vendor/bin/pest
```

---

## License

The Voyager package is open-sourced software licensed under the [MIT license](license).

---

> **Original project:** [thedevdojo/voyager](https://github.com/thedevdojo/voyager) (archived February 2025) — `tcg/voyager` on Packagist.

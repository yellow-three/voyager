# Changelog

All notable changes to `yellow-three/voyager` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added
- Plugin system with `BasePlugin` abstract class and granular contract interfaces (`FormfieldPlugin`, `MenuPlugin`, `WidgetPlugin`, `AuthenticationPlugin`, `AuthorizationPlugin`, `FilterPlugin`)
- `PluginManager` for registering and querying plugins
- `VoyagerCorePlugin` — registers all built-in FormFields, menu items, and widgets
- First-party plugins: `yellow-three/voyager-menu` and `yellow-three/voyager-blog`
- Theme system with `ThemeManager` and `Theme` interface
- Hybrid `BreadManager` — JSON-first, database fallback
- `JsonBreadSource` — stores BREAD definitions as versioned JSON files in `storage/voyager/breads/`
- `DatabaseBreadSource` — backward-compatible DB reader (data_types + data_rows)
- `ActivityLog` model and `ActivityLogController` API
- `UpgradeCommand` — 10-step CLI wizard for v2 → v3 migration
- `ExportBreadsCommand` — exports DB BREAD definitions to JSON
- `ImportBreadsCommand` — imports JSON BREAD definitions back to DB (emergency restore)
- `MakePluginCommand` — scaffolds first-party plugin skeleton
- Backward compatibility shims: `FormFieldShim` (auto-registers legacy `app/FormFields/`) and `WidgetShim` (maps `Widget::run()`)
- Livewire 4 SFC/MFC components for all admin UI panels
- Tailwind CSS 4 with `@theme` token system (no `tailwind.config.js`)
- Alpine.js replacing jQuery for interactive components
- Vite replacing Laravel Mix/Webpack
- CodeMirror 6 for code editing
- TinyMCE 6 Alpine wrapper
- New FormField types: `Toggle`, `Slug`, `Tags`, `SimpleArray`, `Repeater`, `Slider`
- `voyager:validate-lang` command — validates translation files for missing keys

### Changed
- PHP namespace: `TCG\Voyager` → `YellowThree\Voyager`
- Packagist package: `tcg/voyager` → `yellow-three/voyager`
- PHP requirement: `^8.3 | ^8.4 | ^8.5`
- Laravel requirement: `^13.0`
- Livewire requirement: `^4.0`
- Test framework: PHPUnit → Pest 3
- Frontend: Bootstrap 3 + jQuery + Vue 2 → Tailwind CSS 4 + Alpine.js + Livewire 4

### Removed
- `app/FormFields/` auto-discovery (replaced by plugin system, shim available)
- Vue 2 SFCs (replaced by Livewire 4 SFC/MFC)
- Bootstrap 3 and Font Awesome (replaced by Tailwind CSS 4 + Heroicons)
- jQuery dependency
- Laravel Mix / Webpack build system

---

## [1.7.x] — Archived

See [thedevdojo/voyager](https://github.com/thedevdojo/voyager) (archived February 2025).
This is the fork source. The `1.7` branch is frozen for historical reference only.

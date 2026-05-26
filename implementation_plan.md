# Voyager v3 Development Stages 8 to 15

Complete the remaining development stages of Voyager v3 sequentially, integrating the First-Party Plugins, backward compatibility shims, routes, assets, and tests, ensuring all validations pass successfully.

## User Review Required

> [!IMPORTANT]
> The test suite currently crashes because the newly registered `MenuServiceProvider` and `BlogServiceProvider` expect their respective routes, migrations, and view folders to exist. We will address this first by creating the necessary skeleton paths for the `menu` and `blog` plugins.

## Proposed Changes

---

### 1. First-Party Plugins (Aşama 8)
We will create skeleton/basic routes, migration directories, and Blade/SFC views for the `blog` and `menu` plugins. This ensures service providers boot smoothly and can register menu items, layouts, and components.

#### [NEW] [routes/web.php](file:///home/abdurrahman/Projects/voyager/plugins/menu/routes/web.php)
- Basic routes for the menu builder.

#### [NEW] [routes/web.php](file:///home/abdurrahman/Projects/voyager/plugins/blog/routes/web.php)
- Basic routes for blog management.

#### [NEW] [menu resources](file:///home/abdurrahman/Projects/voyager/plugins/menu/resources/views/builder.blade.php)
- Basic view for Menu Builder component.

#### [NEW] [blog resources](file:///home/abdurrahman/Projects/voyager/plugins/blog/resources/views/posts.blade.php)
- Basic view for Blog Posts component.

---

### 2. Backward Compatibility & Upgrade Docs (Aşama 9 & 15)
Create complete documentation files mapping TCG to YellowThree migration pathways, upgrade guidelines, and JSON BREAD specification references.

#### [NEW] [UPGRADE.md](file:///home/abdurrahman/Projects/voyager/UPGRADE.md)
#### [NEW] [CHANGELOG.md](file:///home/abdurrahman/Projects/voyager/CHANGELOG.md)
#### [NEW] [migration.md](file:///home/abdurrahman/Projects/voyager/docs/migration.md)
#### [NEW] [plugin-development.md](file:///home/abdurrahman/Projects/voyager/docs/plugin-development.md)
#### [NEW] [bread-json.md](file:///home/abdurrahman/Projects/voyager/docs/bread-json.md)
#### [MODIFY] [README.md](file:///home/abdurrahman/Projects/voyager/README.md)

---

### 3. API & Routes Finalization (Aşama 10, 11, 12)
- Finalize the routes file to bind endpoints for `ActivityLogController` and `UpgradeController`.
- Hook up standard action dispatching inside Livewire components to mirror traditional model events.

#### [MODIFY] [voyager.php](file:///home/abdurrahman/Projects/voyager/routes/voyager.php)
- Map `ActivityLogController` and `UpgradeController` endpoints.

---

### 4. Vite Asset Builds (Aşama 13)
Run asset packaging to output CSS and JS bundles including modern Alpine wrappers to `publishable/assets/build/`.

---

### 5. Integration Tests & Validation (Aşama 14)
Write Pest integration tests verifying the plugin loader and bread manager registries, as well as the translation dictionary key validator command.

#### [NEW] [PluginSystemTest.php](file:///home/abdurrahman/Projects/voyager/tests/PluginSystemTest.php)
- Add unit/feature tests for `PluginManager`, `BasePlugin`, and core providers.

## Verification Plan

### Automated Tests
- Execute Pest test suite: `./vendor/bin/pest`
- Run the code style linter: `./vendor/bin/pint`
- Run Vite build assets: `npm run build`

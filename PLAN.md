# Voyager Migrasyon Planı: Laravel 13 + Livewire 4 (SFC/MFC) + Tailwind CSS 4 + Vite

---

## İçindekiler

1. [Mevcut Durum](#1-mevcut-durum)
2. [Hedef Mimari](#2-hedef-mimari)
3. [SFC / MFC / Class Karar Kriterleri](#3-sfc--mfc--class-karar-kriterleri)
4. [Component Envanteri](#4-component-envanteri)
5. [Yeni Özellik Detayları](#5-yeni-özellik-detayları)
6. [Themes & Plugins](#6-themes--plugins)
7. [Blog Plugin (First-Party)](#7-blog-plugin-first-party)
8. [v2 → v3 Geçiş Stratejisi](#8-v2--v3-geçiş-stratejisi)
9. [Aşamalar](#9-aşamalar)
10. [İş Takvimi](#10-i̇ş-takvimi)
11. [Riskler & Dikkat Edilmesi Gerekenler](#11-riskler--dikkat-edilmesi-gerekenler)
12. [v3.1+ Yol Haritası](#12-v31-yol-haritası)

#### Bagisto'dan Alınan Fikirler

Bu plan, [Bagisto 2.4](https://github.com/bagisto/bagisto) modular monolith e-ticaret platformu incelenerek elde edilen çıkarımlarla güncellenmiştir. Detaylı karşılaştırma için `docs/bagisto-comparison.md`'ye bakın.

---

## 1. Mevcut Durum

| Kategori | Değer |
|---|---|
| PHP kaynak dosya (`src/`) | 205 |
| Blade view | 75 |
| JS kaynak (assets) | 7 |
| Vue SFC | 1 (`admin_menu.vue`) |
| SCSS kaynak | 2 (`app.scss`, `_variables.scss`) |
| CSS kaynak | 1 (`fonts.css`) |
| Migration | 20 |
| Test | 34 |
| Publishable dosya (dil/varlık) | ~1.223 |

### Stack

| Alan | Mevcut | Hedef |
|---|---|---|
| PHP | ^7.3-\^8.3 | ^8.3-\^8.5 |
| Laravel | ~8.0-\~11.0 | ^13.0 |
| Frontend Framework | jQuery 3.x + Vue 2.7 | Livewire 4 + Alpine.js |
| CSS | Bootstrap 3 (SCSS) | Tailwind CSS 4 |
| Build | Laravel Mix 6 (Webpack) | Vite |
| JS Libraries | select2, DataTables, toastr, dropzone, TinyMCE, EasyMDE, Ace, nestable2, vb. | Livewire-native + Alpine (dropzone korunacak) |
| Component Format | Yok | SFC (default) + MFC (karmasik) |
| Extensibility | FormField handler siniflari + Events | Plugin sistemi (unified) |

---

## 2. Hedef Mimari

```
voyager/
├── src/
│   ├── Core/                           # Cekirdek (BREAD, Media, Settings, Users, Roles)
│   │   ├── Models/
│   │   ├── Http/Controllers/           # API controller'lar KALACAK
│   │   ├── FormFields/                  # Extensible field handler'lar
│   │   ├── Livewire/                   # (Mecbur kalirsa class-based)
│   │   ├── Plugins/                    # Plugin sistemi
│   │   │   ├── PluginManager.php
│   │   │   ├── BasePlugin.php
│   │   │   └── Contracts/VoyagerPlugin.php
│   │   ├── Themes/                     # Tema sistemi
│   │   │   ├── ThemeManager.php
│   │   │   └── Contracts/Theme.php
│   │   ├── ActivityLog/                # YENI: Aktivite log sistemi
│   │   ├── Upgrade/                    # YENI: v2→v3 upgrade komutu
│   │   ├── BackwardCompatibility/      # YENI: Shim katmani (app/FormFields/ deprecation)
│   │   └── ...
│   └── plugins/                        # First-party plugin'ler
│       └── blog/                       # YENI: Posts + Pages + Categories plugin'i
│           ├── BlogServiceProvider.php
│           ├── Livewire/
│           ├── Models/ (Post, Page, Category)
│           ├── Migrations/
│           ├── Seeders/
│           ├── resources/views/
│           └── routes/
├── resources/
│   ├── views/
│   │   ├── components/                 # Core Livewire SFC + MFC
│   │   │   ├── ⚡bread-table.blade.php
│   │   │   ├── ⚡bread-form/
│   │   │   ├── ⚡activity-log/          # YENI
│   │   │   ├── ⚡cache-manager/         # YENI
│   │   │   ├── ⚡maintenance-mode/      # YENI
│   │   │   ├── ⚡queue-manager/         # YENI
│   │   │   ├── ⚡impersonation/         # YENI
│   │   │   ├── ⚡upgrade-wizard/        # YENI: v2→v3 migration UI
│   │   │   ├── ⚡media-manager/
│   │   │   ├── ⚡dashboard/
│   │   │   ├── ⚡settings-manager/
│   │   │   ├── ⚡database-manager/
│   │   │   ├── ⚡plugins-manager/
│   │   │   ├── ⚡themes-manager/
│   │   │   ├── ⚡admin-menu.blade.php
│   │   │   └── ...
│   │   ├── formfields/                 # Blade partial (extensible)
│   │   ├── layouts/
│   │   ├── pages/
│   │   │   ├── ⚡login, dashboard, profile
│   │   │   ├── ⚡plugins, themes
│   │   │   └── ⚡activity-log, cache, maintenance, queue
│   │   └── partials/
│   ├── css/app.css
│   └── js/app.js
├── plugins/
│   ├── blog/                            # Blog plugin (Posts, Pages, Categories)
│   └── menu/                            # Menu Builder plugin (eski core'dan tasindi)
├── vite.config.js
├── package.json
└── composer.json
```

### Moduler Yapi: Core + Plugin

```
Proje                                                   Versiyon
├── tcg/voyager (core)                                   ^3.0
│   ├── BREAD, Media, Settings, Users, Roles
│   ├── Plugin/Theme yonetimi
│   ├── Activity Log, Cache, Maintenance, Queue
│   ├── Impersonation, Upgrade
│   └── v2 backward compatibility layer
│
├── tcg/voyager-blog (first-party plugin)                ^1.0
│   ├── Models: Post, Page, Category
│   ├── Post/Page/Category CRUD (Livewire SFC)
│   ├── Kendi migration'lari, seeder'lari
│   ├── Kendi menu item'lari (sidebar)
│   ├── Plugin sistemi uzerinden core'a kaydolur
│   └── composer extra ile kesif
│
└── vendor/custom-plugin (third-party)
    └── Plugin sistemi uzerinden Voyager'a eklenir
```

### Core/Plugin Ayristirmasi

| Oge | Nerede | Gerekce |
|---|---|---|
| BREAD CRUD | Core | Admin panelin temeli |
| Media Manager | Core | Dosya yonetimi temel ozellik |
| Menu Builder | **Menu Plugin** | Her proje frontend navigation ihtiyaci duymaz, ayri plugin olarak yönetilir |
| Users & Roles | Core | Yetkilendirme temel |
| Settings | Core | Konfigurasyon temel |
| Activity Log | Core | Admin panel standardi, her seyi loglar |
| Cache / Maintenance / Queue | Core | Sistem yonetimi |
| Plugin / Theme Manager | Core | Extensibility sistemi bizzat kendisi |
| Upgrade Wizard | Core | v2→v3 gecis icin |
| **Posts** | **Blog Plugin** | Icerik yonetimi, her proje istemeyebilir |
| **Pages** | **Blog Plugin** | Posts ile ayni mantik |
| **Categories** | **Blog Plugin** | Sadece blog/pages icin gerekli |
| **Translations** | **Core** | Model'lere, field'lara, view'lara gomulu |
| **Compass** | **Core** | Developer tool |

---

## 3. SFC / MFC / Class Karar Kriterleri

### Single-File Component (SFC) – `php artisan make:livewire foo --sfc`

| Ne zaman kullanilir | Örnekler |
|---|---|
| Az PHP mantigi (1-2 property, basit islem) | Read view, Role list, User list, Cache Clear |
| < ~50 satir toplam | CRUD detay görüntüleme |
| Harici JS/CSS gerektirmez | Admin menu, Login, Profile, Maintenance mode |
| Tek amaca hizmet eder | Compass, Impersonation button |

### Multi-File Component (MFC) – `php artisan make:livewire foo --mfc`

| Ne zaman kullanilir | Örnekler |
|---|---|
| Çok PHP mantigi (query, validation, event, pagination) | BREAD browse (DataTable), BREAD edit-add (form) |
| > ~80 satir PHP kodu | Media Manager, Menu Builder, Dashboard, Activity Log |
| Harici JS entegrasyonu (Dropzone, TinyMCE, CodeMirror) | Rich text editor, file upload |
| Karmasik state yonetimi | Settings, Database, Plugins, Themes, Queue Manager |

### Class-Based – Kullanilmayacak.

### Karar Tablosu

| Component | Tip | Gerekce |
|---|---|---|
| **BREAD Browse** | **MFC** | Çok state, sorting, pagination, batch actions |
| **BREAD Edit-Add** | **MFC** | Karmasik form, dinamik field rendering, validation |
| **BREAD Read** | **SFC** | Basit veri gösterimi |
| **BREAD Order** | **SFC** | Basit drag-drop |
| **Dashboard** (widgets + dimmers) | **MFC** | Çoklu widget, lazy loading |
| **Menu Builder** | **MFC** | Karmasik drag-drop, JS |
| **Media Manager** | **MFC** | Dropzone + upload + galeri + crop |
| **Activity Log** | **MFC** | Filter, search, pagination, detail modal |
| **Cache Manager** | **SFC** | Butonlar + checkbox'lar, basit |
| **Maintenance Mode** | **SFC** | Toggle, basit form |
| **Queue Manager** | **MFC** | Failed jobs list, retry, pagination |
| **Impersonation** | **SFC** | Basit "login as" butonu |
| **Upgrade Wizard** | **MFC** | Adim adim migration UI |
| **Settings Manager** | **MFC** | Grup ayarlar, validation |
| **Database Manager** | **MFC** | Schema okuma, tablo yönetimi |
| **Plugins Manager** | **MFC** | Plugin listesi, aktif/pasif, yükleme |
| **Themes Manager** | **MFC** | Tema önizleme, aktiflestirme, özellestirme |
| **BREAD Tools** | **SFC** | Orta karmasiklik |
| **Admin Menu** | **SFC** | Vue'dan Livewire'a tasiniyor |
| **Role/User CRUD** | **SFC** | Standart CRUD |
| **Compass** | **SFC** | Statik sayfa |
| **FormField'lar** | **Blade partial** | Extensible yapi korunuyor |
| **Login** | **SFC** | Basit form |

---

## 4. Component Envanteri

### 4A – Layout & Yapisal

| # | Mevcut View | Tip | Yeni Component |
|---|---|---|---|
| 1 | `master.blade.php` | Layout | `layouts::admin` |
| 2 | `auth/master.blade.php` | Layout | `layouts::auth` |
| 3 | `dashboard/navbar.blade.php` | SFC | `components.partials.navbar` |
| 4 | `dashboard/sidebar.blade.php` | SFC | `components.admin-menu` |
| 5 | `partials/app-footer.blade.php` | SFC | `components.partials.app-footer` |
| 6 | `partials/bulk-delete.blade.php` | SFC | `components.partials.bulk-delete` |
| 7 | `partials/coordinates.blade.php` | SFC | `components.partials.coordinates` |

### 4B – BREAD View'leri (CORE)

| # | Mevcut View | Tip | Yeni Component |
|---|---|---|---|
| 8 | `bread/browse.blade.php` | **MFC** | `components.bread-table` |
| 9 | `bread/edit-add.blade.php` | **MFC** | `components.bread-form` |
| 10 | `bread/read.blade.php` | SFC | `components.bread-read` |
| 11 | `bread/order.blade.php` | SFC | `components.bread-order` |

### 4C – FormField View'leri (CORE, Blade partial, plugin ile kaydedilir)

| # | View | Tip |
|---|---|---|
| 12-32 | `formfields/text`, `image`, `checkbox`, `color`, `date`, `file`, `multiple_images`, `media_picker`, `number`, `password`, `radio_btn`, `rich_text_box`, `code_editor`, `markdown_editor`, `select_dropdown`, `select_multiple`, `text_area`, `time`, `timestamp`, `hidden`, `coordinates` | **Blade partial** |

### 4D – Tools View'leri (CORE)

| # | Mevcut View | Tip | Yeni Component |
|---|---|---|---|
| 33 | `tools/bread/browse.blade.php` | SFC | `components.bread-tools` |
| 34 | `tools/bread/edit-add.blade.php` | SFC | `components.bread-tools-form` |
| 35 | `tools/bread/read.blade.php` | SFC | Inline |
| 36 | `tools/database/index.blade.php` | MFC | `components.database-manager` |
| 37 | `tools/database/browse.blade.php` | SFC | Inline |
| 38 | `tools/database/read.blade.php` | SFC | Inline |
| 39-43 | `tools/database/vue-components/*` | MFC | Livewire component'leri |

### 4E – CRUD & Yönetim View'leri (CORE)

| # | Mevcut View | Tip | Yeni Component |
|---|---|---|---|
| 44 | `media/index.blade.php` | MFC | `components.media-manager` |
| 47 | `settings/index.blade.php` | MFC | `components.settings-manager` |
| 48 | `roles/index.blade.php` | SFC | `components.role-list` |
| 49 | `roles/edit-add.blade.php` | SFC | `components.role-form` |
| 50 | `users/index.blade.php` | SFC | `components.user-list` |
| 51 | `users/edit-add.blade.php` | SFC | `components.user-form` |
| 52 | `compass/index.blade.php` | SFC | `components.compass` |

### 4F – Sayfa View'leri (CORE)

| # | Mevcut View | Tip | Yeni Component |
|---|---|---|---|
| 53 | `login.blade.php` | SFC | `pages::login` |
| 54 | `index.blade.php` (dashboard) | MFC | `pages::dashboard` |
| 55 | `profile.blade.php` | SFC | `pages::profile` |
| 56 | `dimmers.blade.php` | SFC | Dashboard icinde |
| 57 | `alerts.blade.php` | SFC | Layout icinde |

### 4G – Extensions View'leri (CORE, YENI)

| # | View | Tip | Yeni Component |
|---|---|---|---|
| 58 | `extensions/plugins.blade.php` | MFC | `pages::plugins` |
| 59 | `extensions/themes.blade.php` | MFC | `pages::themes` |

### 4H – YENI v3.0 Özellik View'leri (CORE)

| # | View | Tip | Yeni Component |
|---|---|---|---|
| 60 | `activity-log/index.blade.php` | MFC | `components.activity-log` |
| 61 | `cache/index.blade.php` | SFC | `components.cache-manager` |
| 62 | `maintenance/index.blade.php` | SFC | `components.maintenance-mode` |
| 63 | `queue/index.blade.php` | MFC | `components.queue-manager` |
| 64 | `impersonation/index.blade.php` | SFC | `components.impersonation` |
| 65 | `upgrade/index.blade.php` | MFC | `components.upgrade-wizard` |

### 4I – Plugin View'leri (First-party plugin'ler, eski core'dan tasindi)

#### Blog Plugin (Posts, Pages, Categories)

| # | Eski View | Tip | Yeni Component |
|---|---|---|---|
| 65 | `posts/index.blade.php` | SFC | `blog::post-list` |
| 66 | `posts/edit-add.blade.php` | SFC | `blog::post-form` |
| 67 | `pages/index.blade.php` | SFC | `blog::page-list` |
| 68 | `pages/edit-add.blade.php` | SFC | `blog::page-form` |
| 69 | `categories/index.blade.php` | SFC | `blog::category-list` |
| 70 | `categories/edit-add.blade.php` | SFC | `blog::category-form` |

#### Menu Plugin

| # | Eski View | Tip | Yeni Component |
|---|---|---|---|
| 71 | `menus/builder.blade.php` | **MFC** | `menu::builder` |
| 72 | `menus/browse.blade.php` | SFC | `menu::list` |

### 4J – Özet: Component Dagitimi

| # | Eski View | Tip | Yeni Component |
|---|---|---|---|
| 66 | `posts/index.blade.php` | SFC | `blog::post-list` |
| 67 | `posts/edit-add.blade.php` | SFC | `blog::post-form` |
| 68 | `pages/index.blade.php` | SFC | `blog::page-list` |
| 69 | `pages/edit-add.blade.php` | SFC | `blog::page-form` |
| 70 | `categories/index.blade.php` | SFC | `blog::category-list` |
| 71 | `categories/edit-add.blade.php` | SFC | `blog::category-form` |

### Özet: Component Dagitimi

| Tip | Adet | Aciklama |
|---|---|---|
| Core Livewire MFC | 12 | bread-table, bread-form, media-manager, dashboard, settings-manager, database-manager, plugins-manager, themes-manager, activity-log, queue-manager, upgrade-wizard, impersonation |
| Core Livewire SFC | ~20 | bread-read, bread-order, admin-menu, role-list, role-form, user-list, user-form, login, profile, bread-tools, bread-tools-form, compass, navbar, app-footer, bulk-delete, coordinates, dimmers, alerts, cache-manager, maintenance-mode, impersonation-btn |
| Core Blade partial (FormField) | ~21 | text, image, checkbox, ... |
| Blog Plugin (SFC) | ~6 | post-list, post-form, page-list, page-form, category-list, category-form |
| Menu Plugin (MFC + SFC) | ~2 | menu::builder (MFC), menu::list (SFC) |
| **Toplam** | **~61** | 75 mevcut view → ~61 (menu + posts/pages plugin'e, yeni özellikler eklendi) |

---

## 5. Yeni Özellik Detaylari

### 5.1 Activity Log

Her admin aksiyonunu logla. Hangi kullanici, ne zaman, hangi model'i, hangi aksiyonu yapti?

- **Veritabani**: `activity_logs` tablosu (model, action, user_id, old_values, new_values, ip, user_agent)
- **UI**: Filter (model, action, kullanici), tarih araligi, detay modal
- **Entegrasyon**: BREAD event'leri + Livewire `#[On]` ile otomatik log
- **Plugin'ler icin**: `ActivityLog::entry(Model $model, string $action)` API
- **MFC**: `components.activity-log`

```php
// Event-based logging (BREAD event'lerinden tetiklenir)
ActivityLog::entry($bread->model, 'created', $data);

// Plugin'ler de loglayabilir
ActivityLog::entry($post, 'published', $post->toArray());
```

### 5.2 Cache Manager

- **UI**: checkbox'lar (config, route, view, event, app), "Clear Selected" butonu
- **SFC**: `components.cache-manager`

### 5.3 Maintenance Mode

- **UI**: Toggle, opsiyonel mesaj / IP beyaz listesi
- Laravel 13 built-in `php artisan down/up` ile Livewire entegrasyonu
- **SFC**: `components.maintenance-mode`

### 5.4 Queue Manager (Failed Jobs)

- **UI**: Failed jobs listesi + retry / forget butonlari
- Laravel built-in `failed_jobs` tablosunu okur
- **MFC**: `components.queue-manager`

### 5.5 User Impersonation

- **UI**: Kullanici listesinde "Login as" butonu, navbar'da "Exit impersonation" bildirimi
- Laravel built-in `Auth::onceUsingId()` ile veya custom guard
- **SFC**: `components.impersonation`

### 5.6 Upgrade Wizard (v2 → v3)

- **UI**: Adim adim migration (veritabani kontrolu → dosya yedekleme → migration calistirma → seeder)
- `php artisan voyager:upgrade` CLI komutu + web UI
- Veritabani schema kontrolu, oneriler, breaking change uyarilari
- **MFC**: `components.upgrade-wizard`

---

## 6. Themes & Plugins

### 6.1 Plugin Sistemi

Voyager'in mevcut extensibility noktalari (FormField'lar, Event'ler, Widget'lar) **korunacak** ve Plugin sistemi altinda birlestirilecek. Her plugin bir Composer paketi olarak kurulur ve `VoyagerPlugin` interface'ini implemente eder.

#### `VoyagerPlugin` Interface

```php
namespace TCG\Voyager\Plugins\Contracts;

interface VoyagerPlugin
{
    public function name(): string;
    public function label(): string;
    public function description(): string;
    public function version(): string;
    public function author(): ?string;

    /** Sidebar menu ogeleri */
    public function menuItems(): array;

    /** Livewire rotalari */
    public function routes(): array;

    /** FormField handler'lari */
    public function formFields(): array;

    /** Dashboard widget'lari */
    public function widgets(): array;

    /** BREAD Action'lari */
    public function actions(): array;

    /** Tema destegi (opsiyonel) */
    public function themeOverrides(): array;

    /** Migration dosyalari */
    public function migrations(): array;

    /** Seeder siniflari */
    public function seeders(): array;
}
```

#### PluginManager

```php
class PluginManager
{
    protected array $plugins = [];
    protected array $discovered = [];

    // CRUD
    public function register(VoyagerPlugin $plugin): void;
    public function unregister(string $name): void;
    public function get(string $name): ?VoyagerPlugin;
    public function all(): array;
    public function active(): array;
    public function isActive(string $name): bool;
    public function activate(string $name): void;
    public function deactivate(string $name): void;

    // Discovery
    public function discover(): void;  // composer.json extra.voyager-plugins

    // Registration
    public function registerFormFields(): void;
    public function registerMenuItems(): void;
    public function registerRoutes(): void;
    public function registerWidgets(): void;
    public function registerActions(): void;
    public function registerMigrations(): void;
    public function registerSeeders(): void;

    // Backward compatibility (v2 -> v3)
    public function addFormField(string $handlerClass): void; // Voyager::registerFormField()

    // Activity Log
    public function log(Model $model, string $action, array $data = []): void;
}
```

#### Plugin Kesfi (composer.json)

```json
{
    "name": "tcg/voyager-blog",
    "extra": {
        "voyager": {
            "plugin": {
                "class": "TCG\\Voyager\\Plugins\\Blog\\BlogServiceProvider",
                "migrations": true,
                "seeders": true
            }
        }
    }
}
```

#### VoyagerCorePlugin (Internal – everything built-in)

```php
class VoyagerCorePlugin extends BasePlugin
{
    // Built-in 20 FormField handler'lari
    // Dashboard widget'lari
    // Menu items (Dashboard, Media, Menus, Settings, vb.)
}
```

### 6.2 Tema Sistemi

#### `Theme` Interface

```php
interface Theme
{
    public function name(): string;
    public function label(): string;
    public function description(): string;
    public function version(): string;
    public function author(): ?string;

    /** Tailwind @theme override'lari */
    public function colors(): array;

    /** Opsiyonel layout override'lari */
    public function layoutOverrides(): array;

    /** Opsiyonel CSS dosyasi */
    public function css(): ?string;

    /** Opsiyonel font ayarlari */
    public function fonts(): array;

    /** Dark mode destegi */
    public function darkMode(): bool;
}
```

#### ThemeManager

```php
class ThemeManager
{
    protected array $themes = [];
    protected ?string $active = null;

    public function register(Theme $theme): void;
    public function get(string $name): ?Theme;
    public function all(): array;
    public function themesWithDarkMode(): array;
    public function setActive(string $name): void;
    public function active(): ?Theme;
    public function themeStyles(): string;  // inline CSS enjeksiyonu
}
```

### 6.3 Extensible FormField Sistemi (Plugin sistemi ile birlesiyor)

FormField'lari **Livewire SFC'ye degil, Blade partial olarak devam edecek**. `HandlerInterface` ve handler class'lari korunuyor.

**Eski (v2):** `app/FormFields/MyField.php` birak → otomatik kesif.
**Yeni (v3):** Plugin yaz → `formFields()` ile kaydet → `PluginManager` yukler.

`app/FormFields/` auto-discovery kalkiyor. Tüm field'lar plugin sistemi uzerinden kaydedilir:

| Kim | Nasil |
|---|---|
| Voyager built-in field'lar (21 adet) | `VoyagerCorePlugin` ile |
| Kullanici field'lari | Plugin yazarak |
| Tek field (plugin yazmadan) | `Voyager::registerFormField(MyHandler::class)` event hook |
| v2'den gecen (backward compat) | `app/FormFields/` shim katmani ile (deprecation warning) |

### 6.4 Veritabani Migrations

Yeni tablolar (core):

```php
// plugins tablosu – plugin aktif/pasif durumu
Schema::create('plugins', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('version');
    $table->boolean('is_active')->default(true);
    $table->json('settings')->nullable();
    $table->timestamps();
});

// themes tablosu – tema secimi
Schema::create('themes', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->boolean('is_active')->default(false);
    $table->json('settings')->nullable();
    $table->timestamps();
});

// activity_logs tablosu
Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable();
    $table->string('model_type');
    $table->unsignedBigInteger('model_id')->nullable();
    $table->string('action'); // created, updated, deleted, restored, etc.
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->string('ip')->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamps();
    $table->index(['model_type', 'model_id']);
    $table->index('action');
});
```

Plugin'ler (blog) kendi migration'larini calistirir:

```php
// plugins/blog/migrations/create_posts_table.php
// plugins/blog/migrations/create_categories_table.php
// plugins/blog/migrations/create_category_post_table.php (pivot)
```

### 6.5 Event Sistemi Uyumlulugu

Mevcut 24 event **korunuyor**, Livewire ile uyumlu hale getiriliyor:

| Eski Event | Yeni Karsiligi |
|---|---|
| `BreadAdded`, `BreadUpdated`, `BreadDeleted` | Aynen korunuyor + Livewire `dispatch()` tetikliyor |
| `BreadDataAdded`, `BreadDataUpdated`, `BreadDataDeleted` | Aynen korunuyor + Activity Log yaziyor |
| `FormFieldsRegistered` | Plugin sistemi ile birlestiriliyor |
| `MenuDisplay` | Aynen korunuyor |
| `SettingUpdated` | Aynen korunuyor + Cache flush |
| `MediaFileAdded`, `FileDeleted` | Aynen korunuyor |
| `Routing`, `RoutingAdmin`, `RoutingAdminAfter`, `RoutingAfter` | Aynen korunuyor |
| `AlertsCollection` | Aynen korunuyor |
| `TableAdded`, `TableChanged`, `TableDeleted`, `TableUpdated` | Aynen korunuyor |

**Mimari:** Eski event'ler Laravel Event system ile calismaya devam ediyor. Livewire `$this->dispatch()` ayri bir kanal. Activity Log, her iki kanali da dinleyerek log yaziyor.

```php
// Eski event hala firlatilir
event(new BreadDataAdded($dataType, $data));

// Livewire dispatch de firlatilir (Browser event)
$this->dispatch('bread-data-added', dataType: $dataType, data: $data);

// Activity Log her ikisini de dinler
#[Listener] // Laravel event listener
public function handleBreadDataAdded(BreadDataAdded $event): void
{
    ActivityLog::entry($event->data, 'created');
}

#[On('bread-data-added')] // Livewire event listener
public function onBreadDataAdded(array $dataType, array $data): void
{
    ActivityLog::entry($data, 'created');
}
```

### 6.6 Backward Compatibility Katmani

v2 kullanicilari icin deprecation + shim katmani:

- **`app/FormFields/` shim**: `VoyagerServiceProvider::boot()`'ta eski dizin taranir, varsa `Voyager::registerFormField()` yapilir, deprecation warning loglanir
- **`Widget::run()` shim**: `arrilot/laravel-widgets` kalkiyor ama `Widget::run()` cagrilirsa `trigger_error()` ile deprecation uyarisi + yonlendirme
- **`window.$` shim**: Admin panelde `window.$ = document.querySelector.bind(document)` saglanabilir (istege bagli)
- **`@extends('voyager::master')` shim**: Yeni layout system'e yonlendiren bir Blade include

### 6.7 CLI Plugin Generator (Bagisto'dan esinlenildi)

`php artisan voyager:make:plugin {name}` komutu ile yeni bir plugin iskeleti olusturulur:

```
php artisan voyager:make:plugin blog

# Olusturduklari:
# plugins/blog/
# ├── composer.json
# ├── BlogPlugin.php (VoyagerPlugin implementasyonu)
# ├── BlogServiceProvider.php
# ├── routes/blog.php
# ├── src/Models/
# ├── src/Migrations/
# ├── src/Seeders/
# └── resources/views/components/
```

Generator su dosyalari olusturur:

- **`BlogPlugin.php`**: `VoyagerPlugin` interface'ini implemente eden hazir skeleton
- **`BlogServiceProvider.php`**: PluginManager'a kayit icin ServiceProvider
- **`composer.json`**: `extra.voyager.plugins` ile discovery ayari
- **`routes/blog.php`**: Bos route dosyasi
- **`src/Models/.gitkeep`**: Model dizini
- **`src/Migrations/`**: Bos migration dizini
- **`resources/views/components/`**: Livewire SFC/MFC icin view dizini

Bu komut, Bagisto'nun `php artisan package:make` CLI generator'undan esinlenilmistir. Plugin gelistirmeyi hizlandirir ve dogru yapiyi guarantee eder.

### 6.8 Model Registry (Plugin Modelleri icin)

Plugin'ler core'a yeni modeller tanitmak icin bir `ModelRegistry` kullanir. Concord'un Contract/Model/Proxy desenine benzer, ama Voyager'in BREAD yapisina uyarlanmistir:

```php
class ModelRegistry
{
    protected array $models = [];

    // Plugin modeli kaydet
    public function register(string $alias, string $class): void;

    // Model sinifini alias ile al
    public function get(string $alias): ?string;

    // Tum kayitli modeller
    public function all(): array;

    // BREAD DataType ile eslestir
    public function forDataType(DataType $dataType): ?string;
}
```

Ornek kullanim:

```php
// BlogPlugin.php
public function registerModels(ModelRegistry $registry): void
{
    $registry->register('post', Post::class);
    $registry->register('page', Page::class);
    $registry->register('category', Category::class);
}
```

`PluginManager`, her plugin'in `registerModels()` cagrisini `ModelRegistry`'e yonlendirir. Bu sayede BREAD, plugin model'lerini de taniyabilir.

### 6.9 Mimari Akis

```
PluginManager.discover()
    |
    ├── composer.json extra.voyager.plugins
    ├── register() ile manuel plugin'ler
    ├── VoyagerCorePlugin (built-in field'lar, menu, widget'lar)
    ├── Voyager::registerFormField() (plugin yazmadan)
    ├── app/FormFields/ shim (deprecation)
    |
    ├── registerMigrations()
    |       └── Plugin migration'lari calistirilir
    |
    ├── registerFormFields()
    |       └── Tum handler'lar tek registry'de
    |
    ├── registerMenuItems()
    |       └── AdminMenu SFC'ye eklenir
    |
    ├── registerRoutes()
    |       └── Route::livewire() plugin sayfalari
    |
    ├── registerWidgets()
    |       └── Dashboard MFC'ye eklenir
    |
    └── registerActions()
            └── BREAD action registry

ActivityLog
    ├── Laravel event'leri dinler (BreadDataAdded, etc.)
    ├── Livewire dispatch'leri dinler (#[On])
    └── activity_logs tablosuna yazar

ThemeManager
    ├── register() ile temalar kaydedilir
    ├── active() ile aktif tema alinir
    ├── VoyagerDefaultTheme her zaman yuklu
    └── Plugin'ler kendi override'larini ekleyebilir
```

---

## 7. First-Party Plugin'ler

Voyager ile birlikte gelen ancak core'dan ayri paketlenen plugin'ler. Ihtiyaca gore `composer require` ile kurulur.

### 7.1 Menu Builder Plugin (tcg/voyager-menu)

Eski core'daki Menu Builder ayri bir plugin'e tasindi. Menu ve MenuItem modelleri, drag-drop builder UI, frontend render helper'i (`menu('footer')`) bu plugin'de.

#### 7.1.1 Neden Plugin?

- Menu + MenuItem kendi DB tablolarina sahip (ayri migration)
- Her projede frontend navigasyon yonetimi gerekmez
- Menu builder'daki BREAD baglantisi zaten zayif (sadece menuler icin kullanilir)
- Core'dan 2 model eksilir, plugin ekosistemi guclenir

#### 7.1.2 Menu Plugin Yapisi

```
plugins/menu/
├── MenuServiceProvider.php
├── MenuPlugin.php
├── composer.json
├── resources/views/
│   ├── components/
│   │   ├── ⚡menu-builder/        (MFC – drag-drop, redisign)
│   │   └── ⚡menu-list.blade.php  (SFC)
│   └── render/
│       └── bootstrap.blade.php    (Tailwind'li render helper)
├── src/
│   ├── Models/Menu.php
│   ├── Models/MenuItem.php
│   ├── Http/Controllers/VoyagerMenuController.php  (API)
│   ├── Migrations/
│   └── Seeders/
└── routes/menu.php
```

#### 7.1.3 MenuPlugin Kaydi

```php
class MenuPlugin extends BasePlugin
{
    public function name(): string { return 'voyager-menu'; }
    public function label(): string { return 'Menu Builder'; }
    public function description(): string { return 'Drag-drop menu builder for frontend navigation'; }

    public function menuItems(): array {
        return [
            ['label' => 'Menus', 'route' => 'voyager.menus', 'icon' => 'list'],
        ];
    }

    public function routes(): array {
        return [
            Route::livewire('/menus', 'menu::list')->name('voyager.menus'),
            Route::livewire('/menus/{menu}/builder', 'menu::builder')->name('voyager.menus.builder'),
        ];
    }
}
```

#### 7.1.4 Admin Sidebar Entegrasyonu

Admin sidebar'daki "Menus" linki menu plugin aktifse gorunur, degilse gizlenir:

```php
// VoyagerCorePlugin – admin sidebar
public function menuItems(): array {
    $items = [
        ['label' => 'Dashboard', 'route' => 'voyager.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Media', 'route' => 'voyager.media', 'icon' => 'images'],
        // ... Menus burada degil, MenuPlugin'de
    ];

    if (app(PluginManager::class)->isActive('voyager-menu')) {
        $items[] = ['label' => 'Menus', 'route' => 'voyager.menus', 'icon' => 'list'];
    }

    return $items;
}
```

### 7.2 Blog Plugin (tcg/voyager-blog)

Posts, Pages, Categories yonetimi.

#### 7.2.1 Neden Plugin?

- Blog ihtiyaci olmayan projeler plugin'i disable eder, core temiz kalir
- Plugin sistemi icin dogal bir referans implementasyon
- Bagimsiz release: ayri versiyonlanir, core'dan bagimsiz guncellenir

#### 7.2.2 Plugin Yapisi

```
plugins/blog/
├── BlogServiceProvider.php
├── BlogPlugin.php
├── composer.json
├── resources/views/
│   ├── components/
│   │   ├── ⚡post-list.blade.php       (SFC)
│   │   ├── ⚡post-form.blade.php       (SFC)
│   │   ├── ⚡page-list.blade.php       (SFC)
│   │   ├── ⚡page-form.blade.php       (SFC)
│   │   ├── ⚡category-list.blade.php   (SFC)
│   │   └── ⚡category-form.blade.php   (SFC)
│   └── formfields/
│       └── blog-category-select.blade.php
├── src/
│   ├── Models/ (Post, Page, Category)
│   ├── FormFields/ (CategorySelectHandler)
│   ├── Migrations/
│   └── Seeders/
└── routes/blog.php
```

#### 7.2.3 BlogPlugin Kaydi

```php
class BlogPlugin extends BasePlugin
{
    public function name(): string { return 'voyager-blog'; }
    public function label(): string { return 'Blog Engine'; }
    public function description(): string { return 'Posts, Pages and Categories management'; }

    public function menuItems(): array {
        return [
            ['label' => 'Posts', 'route' => 'voyager.blog.posts', 'icon' => 'file-text'],
            ['label' => 'Pages', 'route' => 'voyager.blog.pages', 'icon' => 'file'],
            ['label' => 'Categories', 'route' => 'voyager.blog.categories', 'icon' => 'folder'],
        ];
    }

    public function routes(): array {
        return [
            Route::livewire('/blog/posts', 'blog::post-list')->name('voyager.blog.posts'),
            Route::livewire('/blog/posts/create', 'blog::post-form')->name('voyager.blog.posts.create'),
            Route::livewire('/blog/posts/{id}/edit', 'blog::post-form')->name('voyager.blog.posts.edit'),
            Route::livewire('/blog/pages', 'blog::page-list')->name('voyager.blog.pages'),
            Route::livewire('/blog/categories', 'blog::category-list')->name('voyager.blog.categories'),
        ];
    }
}
```

---

## 8. v2 → v3 Geçiş Stratejisi

### 8.1 Upgrade Komutu

```bash
php artisan voyager:upgrade
```

Yapacaklari:
1. Mevcut v2 konfigurasyonunu yedekle
2. Veritabani schema'yi kontrol et (eski migration'lar uyumlu mu?)
3. Yeni migration'lari calistir (plugins, themes, activity_logs)
4. Mevcut `data_types`, `data_rows` kayitlarini koru
5. `app/FormFields/` varsa deprecation warning goster + shim'e ekle
6. `Widget::run()` kullanimlarini tara ve raporla
7. `@extends('voyager::master')` kullanimlarini tara
8. Seeder'lari calistir

### 8.2 UPGRADE.md

v2'den v3'e gecis icin dokuman:
- Breaking changes listesi (cozumleriyle)
- `voyager_asset()` → Vite helper migration
- `@extends('voyager::master')` → Livewire layout migration
- jQuery plugin'leri icin alternatifler
- `voyager::formfields.*` → plugin sistemi
- `Widget::run()` → `@livewire('components.widget')`
- `app/FormFields/` → plugin veya `Voyager::registerFormField()`

### 8.3 Seeders

Mevcut 11 seeder guncelleniyor:
- `DataTypesTableSeeder` → yeni plugin alanlari icin guncellenecek
- `DataRowsTableSeeder` → aynen korunuyor
- `PermissionsTableSeeder` → yeni özellik permission'lari eklenecek (activity-log, cache, maintenance, queue)
- `RolesTableSeeder` → aynen korunuyor
- `MenuItemsTableSeeder` → yeni menu ogeleri eklenecek
- Yeni: `PluginsTableSeeder`, `ThemesTableSeeder`, `ActivityLogsTableSeeder` (opsiyonel)
- Blog plugin kendi seeder'larini getirir (Posts, Pages, Categories)

### 8.4 Database Migration Sirasi

```
1. create_plugins_table          (core v3)
2. create_themes_table           (core v3)
3. create_activity_logs_table    (core v3)
4. create_posts_table            (blog plugin)
5. create_categories_table       (blog plugin)
6. create_category_post_table    (blog plugin)
```

---

## 9. Asamalar

### Asama 1: Proje Altyapisi & Bagimliliklar

- `composer.json` guncelle (PHP ^8.3, Laravel ^13.0, Livewire ^4.0)
- Dev dependency'leri guncelle (`orchestra/testbench ^10.0`, PHPUnit ^11.0)
- `arrilot/laravel-widgets` kaldir
- `package.json` yenile (Vite + Tailwind + Alpine + Dropzone + TinyMCE + CodeMirror)
- `laravel/boost` eklenmeyecek (package oldugu icin)

### Asama 2: Build Sistemi – Mix → Vite

- `vite.config.js` olustur (Laravel Vite plugin + Tailwind)
- Tailwind CSS 4 (`@import "tailwindcss"`, no config file)
- `webpack.mix.js`, `mix.js`, `mix-manifest.json` kaldir

### Asama 3: CSS – Bootstrap 3 → Tailwind CSS 4

- `resources/css/app.css` (Tailwind + `@theme`)
- `_variables.scss` → `@theme` direktifleri
- Tum Bootstrap class'lari Tailwind utility'lerine cevir
- Prefix kullanilmayacak (Tailwind 4 `@layer` ile izolasyon)

### Asama 4: JavaScript – jQuery + Vue 2 → Alpine.js + Livewire

- jQuery + Vue 2 + DataTables + select2 + toastr + nestable2 kaldir
- Alpine.js ekle
- Dropzone Alpine wrapper ile koru
- TinyMCE + CodeMirror Livewire wrapper ile koru
- CropperJS Alpine wrapper ile koru

### Asama 5: Core Views → Livewire + Tailwind

5a. Layout & partial'lar
5b. BREAD Table (MFC)
5c. BREAD Form (MFC) + FormField Blade partial'lar
5d. Media Manager (MFC)
5e. Dashboard (MFC)
5f. Settings, Database, Compass
5g. Login, Profile, Admin Menu
5h. Activity Log (MFC) + Cache (SFC) + Maintenance (SFC)
5i. Queue Manager (MFC) + Impersonation (SFC)
5j. Upgrade Wizard (MFC)

### Asama 6: Plugin & Theme Sistemi

6a. PluginManager + VoyagerPlugin interface
6b. ThemeManager + Theme interface
6c. VoyagerCorePlugin (built-in field'lar, menu, widget'lar)
6d. Plugins Manager + Themes Manager MFC
6e. DB migrations (plugins, themes, activity_logs)
6f. Extensions UI sayfalari
6g. ModelRegistry (plugin model'lerini core'a tanitma mekanizmasi)
6h. `php artisan voyager:make:plugin` CLI generator

### Asama 7: First-Party Plugin'ler

7a. Menu Plugin (Menu + MenuItem modelleri, migration, drag-drop builder MFC)
7b. Blog Plugin (Post, Page, Category modelleri, migration, CRUD SFC'ler)
7c. Her iki plugin'in kaydi (menu items, routes, form fields)
7d. Admin sidebar'in plugin aktif/pasif durumuna gore menu gostermesi

### Asama 8: Backward Compatibility & Upgrade

8a. `app/FormFields/` shim katmani (deprecation warning)
8b. `php artisan voyager:upgrade` CLI komutu
8c. `Widget::run()` shim
8d. `@extends('voyager::master')` → Livewire layout redirect
8e. UPGRADE.md + CHANGELOG.md
8f. v2→v3 migration rehberi

### Asama 9: Event Sistemi Uyumlulugu

9a. 24 event'in korunmasi + dokumantasyon
9b. Activity Log'un event'lere baglanmasi
9c. Livewire dispatch ile eski event'lerin uyumu

### Asama 10: Controller'lar – API Layer KORUNACAK

| Controller | UI (Livewire) | API (Controller) |
|---|---|---|
| `VoyagerBaseController` | bread-table + bread-form MFC | CRUD API KALIR |
| `VoyagerController` | dashboard | Dashboard API KALIR |
| `VoyagerAuthController` | login | Auth API KALIR |
| `VoyagerMediaController` | media-manager | Upload API KALIR |
| `VoyagerMenuController` (menu plugin) | menu::builder | Menu API KALIR (plugin ile gelir) |
| `VoyagerSettingsController` | settings-manager | Settings API KALIR |
| `VoyagerDatabaseController` | database-manager | Database API KALIR |
| `VoyagerBreadController` | bread-tools | BREAD API KALIR |
| `VoyagerRoleController` | role-list + form | Role API KALIR |
| `VoyagerUserController` | user-list + form | User API KALIR |
| `VoyagerCompassController` | compass | Compass API KALIR |
| **YENI:** ActivityLogController | activity-log | Log API KALIR |
| **YENI:** UpgradeController | upgrade-wizard | Upgrade API KALIR |

### Asama 11: Routes & Service Provider

- `routes/voyager.php` (Livewire + API routes)
- `VoyagerServiceProvider` (PluginManager, ThemeManager, Livewire namespace, backward compat shim)

### Asama 12: Publishable Assets

- Vite build → `publishable/assets/`
- TinyMCE/CodeMirror wrapper'lar
- Dil dosyalari (630 dosya, korunuyor)

### Asama 13: Testler & CI

- Mevcut 34 test KORUNACAK
- Livewire component testleri EKLENECEK
- Activity Log testleri
- Plugin sistemi testleri
- Blog plugin testleri
- Upgrade komutu testleri
- Playwright E2E test altyapisi (Bagisto referansi) — temel admin akislari icin
- Translation CI validator — 630 dil dosyasinin eksiksiz oldugunu CI'da kontrol et

### Asama 14: Dokumantasyon

- Repository map in Agents.md (Bagisto'dan esinlenildi)
- `docs/bagisto-comparison.md` — Bagisto mimarisiyle karsilastirma dokumani

---

## 10. İş Takvimi

| Asama | Is | Sure (gun) |
|---|---|---|
| **1** | Proje altyapisi & bagimliliklar | 1 |
| **2** | Build sistemi (Mix → Vite) | 0.5 |
| **3** | CSS donusumu (SCSS → Tailwind) | 2-3 |
| **4** | JavaScript donusumu | 3-4 |
| **5a** | Layout & partial'lar | 1 |
| **5b** | BREAD Table + Form (MFC) | 3-4 |
| **5c** | FormField Blade partial'lar | 1 |
| **5d** | Media Manager (MFC) | 1.5 |
| **5e** | Dashboard (MFC) | 1 |
| **5f** | Settings, Database, Compass | 1.5 |
| **5g** | Login, Profile, Admin Menu | 0.5 |
| **5h** | Activity Log (MFC) + Cache + Maintenance | 2 |
| **5i** | Queue Manager (MFC) + Impersonation | 1.5 |
| **5j** | Upgrade Wizard (MFC) | 1 |
| **6a** | PluginManager + ThemeManager | 1.5 |
| **6b** | VoyagerCorePlugin | 0.5 |
| **6c** | Extensions UI (plugins + themes MFC) | 1.5 |
| **6d** | DB migrations (plugins, themes, activity_logs) | 0.5 |
| **6e** | ModelRegistry | 0.5 |
| **6f** | CLI plugin generator (`voyager:make:plugin`) | 1 |
| **7a** | Menu Plugin | 1.5 |
| **7b** | Blog Plugin | 2 |
| **7c** | Plugin kayit + sidebar entegrasyonu | 0.5 |
| **7d** | Plugin testleri | 0.5 |
| **8a** | Backward compatibility shim | 1 |
| **8b** | `php artisan voyager:upgrade` komutu | 1 |
| **8c** | UPGRADE.md + CHANGELOG.md | 0.5 |
| **9** | Event sistemi uyumlulugu | 1 |
| **10** | Controller API layer | 1.5 |
| **11** | Routes & Service Provider | 0.5 |
| **12** | Publishable assets | 1 |
| **13** | Testler & CI (PHPUnit + Playwright E2E + Translation CI) | 4-5 |
| **14** | Dokumantasyon (Agents.md repo map, Bagisto comparison) | 0.5 |
| | **Toplam** | **~46-55 gun** |

---

## 11. Riskler & Dikkat Edilmesi Gerekenler

### Yüksek Öncelikli

- **v2 → v3 upgrade**: Kullanicilarin sorunsuz gecmesi icin upgrade komutu + dokumasyon sart. Yoksa kimse gecmez.
- **Dynamic BREAD**: DataType + DataRow model'leri üzerinden dinamik CRUD. Livewire component'ler DataType'a gore dinamik olusturulmali.
- **Plugin sistemi tasarimi**: Interface'ler ve kesif mekanizmasi ilk asamada dogru kurgulanmali.
- **Event sistemi**: 24 event var, plugin'ler ve custom kod bunlara bagli. "Düsük öncelik" degil, YÜKSEK.
- **FormField backward compat**: `app/FormFields/` kalkiyor, eski projeler kırılmasın diye shim + deprecation süreci gerekli.

### Orta Öncelikli

- **TinyMCE/CodeMirror wrapper**: Livewire + Alpine ile sarilmali.
- **Dropzone jQuery kaldirma**: Alpine wrapper ile.
- **Blog plugin**: Core'dan ayriliyor, migration'lar ve data uyumu.
- **Ceviri dosyalari (630 dosya)**: Korunuyor, degisiklik gerekmez.
- **Translation CI validator**: Tum dillerde eksik anahtar kontrolu otomatiklesmeli.
- **Activity Log performans**: Çok sayida log yazilabilir, index + periyodik temizlik.
- **E2E test altyapisi (Playwright)**: Bagisto referansiyla temel admin akislari test edilmeli.

### Dusuk Öncelikli

- **Arrilot widgets kaldirma**: Shim ile deprecation.
- **Flysystem versiyon uyumu**: Guncelleme yeterli.
- **Laravel 13 yeni özellikler**: JSON:API Resources, AI SDK, Cache::touch – ileride degerlendirilir.

### Kirilma Noktalari (Breaking Changes)

| Degisiklik | Etki | Cozum |
|---|---|---|
| `voyager_asset()` helper degisiyor | Asset path'leri degisir | Upgrade komutu + migration rehberi |
| Bootstrap 3 → Tailwind CSS | HTML class'lari degisir | View'ler package'dan, host etkilenmez |
| jQuery kalkiyor | `window.$` kullanan kod kirilir | Shim + migration rehberi |
| Controller route'lari degisiyor | API route'lari `/admin/api/` prefix'i | Migration rehberi |
| `@extends('voyager::master')` → Livewire layout | Host view kalip degistiremez | Layout override mekanizmasi |
| `arrilot/laravel-widgets` kalkiyor | `Widget::run()` kirilir | Shim + Livewire widget API |
| `app/FormFields/` auto-discovery kalkiyor | Custom field'ler calismaz | Plugin veya `registerFormField()` + shim |
| **Posts/Pages/Categories** → blog plugin | Bu sayfalara bagimli kod kirilir | Blog plugin kurulum rehberi |
| **Menu Builder** → menu plugin | `Menu`, `MenuItem` model'leri, `menu()`, `voyager_menus` route'lari plugin'e tasindi | Menu plugin kurulumu: `composer require tcg/voyager-menu` |
| **Plugin sistemi** (YENI) | Yeni extensibility API | Dokumantasyon + ornek plugin |
| **Tema sistemi** (YENI) | Admin temasi degistirilebilir | Dokumantasyon + ornek tema |

---

## 12. v3.1+ Yol Haritası

Bu özellikler v3.0'a yetismeyecek ama plana dahil:

| Özellik | Tahmini Sure | Aciklama |
|---|---|---|
| **Backup & Restore** | 3-4 gun | Veritabani + dosya yedekleme UI |
| **API Token Management (Sanctum)** | 2 gun | API token olusturma/iptal UI |
| **Two-Factor Authentication (2FA)** | 3 gun | Admin login icin 2FA |
| **Import / Export (CSV, Excel)** | 3-4 gun | BREAD data icin toplu import/export |
| **Log Viewer** | 2 gun | `storage/logs/laravel.log` okuyucu |
| **Health Check** | 1.5 gun | PHP versiyon, extension'lar, .env kontrolu |
| **Notification System** | 3-4 gun | In-app bildirimler + kanallar |
| **Dark Mode** | 1 gun | Tema sistemine dark mode toggle |
| **Translation Management UI** | 3 gun | 630 dil dosyasini UI'dan yönetme |
| **Webhook Management** | 2 gun | Webhook endpoint yönetimi |
| **SEO Tools** | 2 gun | Sitemap generator, meta analysis |
| **Report Builder** | 4-5 gun | Dashboard chart builder |
| | **Toplam** | **~30-35 gun** |

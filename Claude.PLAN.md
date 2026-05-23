# Voyager Migrasyon Planı: Laravel 13 + Livewire 4 (SFC/MFC) + Tailwind CSS 4 + Vite

---

## İçindekiler

1. [Neden v3?](#1-neden-v3)
2. [Mevcut Durum](#2-mevcut-durum)
3. [Hedef Mimari](#3-hedef-mimari)
4. [SFC / MFC / Class Karar Kriterleri](#4-sfc--mfc--class-karar-kriterleri)
5. [Component Envanteri](#5-component-envanteri)
6. [Yeni Özellik Detayları](#6-yeni-özellik-detayları)
7. [Themes & Plugins](#7-themes--plugins)
8. [Blog Plugin (First-Party)](#8-blog-plugin-first-party)
9. [v2 → v3 Geçiş Stratejisi](#9-v2--v3-geçiş-stratejisi)
10. [Aşamalar](#10-aşamalar)
11. [İş Takvimi](#11-iş-takvimi)
12. [Riskler & Dikkat Edilmesi Gerekenler](#12-riskler--dikkat-edilmesi-gerekenler)
13. [v3.1+ Yol Haritası](#13-v31-yol-haritası)

#### Bagisto'dan Alınan Fikirler

Bu plan, [Bagisto 2.4](https://github.com/bagisto/bagisto) modular monolith e-ticaret platformu incelenerek elde edilen çıkarımlarla güncellenmiştir. Detaylı karşılaştırma için `docs/bagisto-comparison.md`'ye bakın.

---

## 1. Neden v3?

`thedevdojo/voyager` (Packagist: `tcg/voyager`) **7 Şubat 2025'te resmi olarak arşivlendi** ve artık bakım almıyor. Son sürüm v1.8.0 (Eylül 2024) olarak kaldı.

Bu proje, `thedevdojo/voyager@1.7` fork'u üzerinden tamamen yeniden yazılan bir **community-driven v3** girişimidir:

- **Upstream**: `github.com/thedevdojo/voyager` (arşivlenmiş, v1.7 baz alındı)
- **Bu fork**: `github.com/yellow-three/voyager`
- **Packagist**: `yellow-three/voyager`
- **PHP Namespace**: `YellowThree\Voyager`

Hedef, Voyager'ın BREAD + admin panel anlayışını modern PHP/Laravel ekosistemi üzerine taşımak:

| Neden | Açıklama |
|---|---|
| Modern stack | Bootstrap 3 / jQuery / Vue 2 → Tailwind 4 / Alpine.js / Livewire 4 |
| Laravel 13+ uyumu | Orijinal upstream Laravel 13 desteği sunmuyor |
| Extensibility | Dağınık extension noktaları → tutarlı Plugin sistemi |
| Performance | 2.5 MB JS bundle → Vite + tree-shaking |
| Maintainability | Class-based Blade views → SFC/MFC |

---

## 2. Mevcut Durum

> **Baz alınan sürüm:** `yellow-three/voyager` fork'u, `1.7` branch — `thedevdojo/voyager@1.7`'den türetildi.

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

| Alan | Mevcut (v1.7 fork) | Hedef (v3) |
|---|---|---|
| PHP | ^8.2\|^8.3 | ^8.3\|^8.4 |
| Laravel | ~8.0–~11.0 | ^13.0 |
| Frontend Framework | jQuery 3.x + Vue 2.7 | Livewire 4 + Alpine.js |
| CSS | Bootstrap 3 (SCSS) | Tailwind CSS 4 |
| Build | Laravel Mix 6 (Webpack) | Vite |
| JS Libraries | select2, DataTables, toastr, dropzone, TinyMCE, EasyMDE, Ace, nestable2, vb. | Livewire-native + Alpine (dropzone korunacak) |
| Component Format | Yok | SFC (default) + MFC (karmasik) |
| Extensibility | FormField handler siniflari + Events | Plugin sistemi (unified) |
| Test | PHPUnit + orchestra/testbench | PHPUnit + orchestra/testbench ^11.0 |
| Packagist | `tcg/voyager` (arşivlenmiş) | `yellow-three/voyager` |
| PHP Namespace | `TCG\Voyager` | `YellowThree\Voyager` |

---

## 3. Hedef Mimari

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
├── yellow-three/voyager (core)                          ^3.0
│   ├── BREAD, Media, Settings, Users, Roles
│   ├── Plugin/Theme yonetimi
│   ├── Activity Log, Cache, Maintenance, Queue
│   ├── Impersonation, Upgrade
│   └── v2 backward compatibility layer
│
├── yellow-three/voyager-blog (first-party plugin)       ^1.0
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

## 4. SFC / MFC / Class Karar Kriterleri

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

## 5. Component Envanteri

### 5A – Layout & Yapisal

| # | Mevcut View | Tip | Yeni Component |
|---|---|---|---|
| 1 | `master.blade.php` | Layout | `layouts::admin` |
| 2 | `auth/master.blade.php` | Layout | `layouts::auth` |
| 3 | `dashboard/navbar.blade.php` | SFC | `components.partials.navbar` |
| 4 | `dashboard/sidebar.blade.php` | SFC | `components.admin-menu` |
| 5 | `partials/app-footer.blade.php` | SFC | `components.partials.app-footer` |
| 6 | `partials/bulk-delete.blade.php` | SFC | `components.partials.bulk-delete` |
| 7 | `partials/coordinates.blade.php` | SFC | `components.partials.coordinates` |

### 5B – BREAD View'leri (CORE)

| # | Mevcut View | Tip | Yeni Component |
|---|---|---|---|
| 8 | `bread/browse.blade.php` | **MFC** | `components.bread-table` |
| 9 | `bread/edit-add.blade.php` | **MFC** | `components.bread-form` |
| 10 | `bread/read.blade.php` | SFC | `components.bread-read` |
| 11 | `bread/order.blade.php` | SFC | `components.bread-order` |

### 5C – FormField View'leri (CORE, Blade partial, plugin ile kaydedilir)

| # | View | Tip |
|---|---|---|
| 12-32 | `formfields/text`, `image`, `checkbox`, `color`, `date`, `file`, `multiple_images`, `media_picker`, `number`, `password`, `radio_btn`, `rich_text_box`, `code_editor`, `markdown_editor`, `select_dropdown`, `select_multiple`, `text_area`, `time`, `timestamp`, `hidden`, `coordinates` | **Blade partial** |

### 5D – Tools View'leri (CORE)

| # | Mevcut View | Tip | Yeni Component |
|---|---|---|---|
| 33 | `tools/bread/browse.blade.php` | SFC | `components.bread-tools` |
| 34 | `tools/bread/edit-add.blade.php` | SFC | `components.bread-tools-form` |
| 35 | `tools/bread/read.blade.php` | SFC | Inline |
| 36 | `tools/database/index.blade.php` | MFC | `components.database-manager` |
| 37-43 | `tools/database/vue-components/*` | MFC | Livewire component'leri |

### 5E – CRUD & Yönetim View'leri (CORE)

| # | Mevcut View | Tip | Yeni Component |
|---|---|---|---|
| 44 | `media/index.blade.php` | MFC | `components.media-manager` |
| 47 | `settings/index.blade.php` | MFC | `components.settings-manager` |
| 48 | `roles/index.blade.php` | SFC | `components.role-list` |
| 49 | `roles/edit-add.blade.php` | SFC | `components.role-form` |
| 50 | `users/index.blade.php` | SFC | `components.user-list` |
| 51 | `users/edit-add.blade.php` | SFC | `components.user-form` |
| 52 | `compass/index.blade.php` | SFC | `components.compass` |

### 5F – Sayfa View'leri (CORE)

| # | Mevcut View | Tip | Yeni Component |
|---|---|---|---|
| 53 | `login.blade.php` | SFC | `pages::login` |
| 54 | `index.blade.php` (dashboard) | MFC | `pages::dashboard` |
| 55 | `profile.blade.php` | SFC | `pages::profile` |
| 56 | `dimmers.blade.php` | SFC | Dashboard icinde |
| 57 | `alerts.blade.php` | SFC | Layout icinde |

### 5G – Extensions View'leri (CORE, YENI)

| # | View | Tip | Yeni Component |
|---|---|---|---|
| 58 | `extensions/plugins.blade.php` | MFC | `pages::plugins` |
| 59 | `extensions/themes.blade.php` | MFC | `pages::themes` |

### 5H – YENI v3.0 Özellik View'leri (CORE)

| # | View | Tip | Yeni Component |
|---|---|---|---|
| 60 | `activity-log/index.blade.php` | MFC | `components.activity-log` |
| 61 | `cache/index.blade.php` | SFC | `components.cache-manager` |
| 62 | `maintenance/index.blade.php` | SFC | `components.maintenance-mode` |
| 63 | `queue/index.blade.php` | MFC | `components.queue-manager` |
| 64 | `impersonation/index.blade.php` | SFC | `components.impersonation` |
| 65 | `upgrade/index.blade.php` | MFC | `components.upgrade-wizard` |

### 5I – Plugin View'leri (First-party plugin'ler, eski core'dan tasindi)

#### Blog Plugin (Posts, Pages, Categories)

| # | Eski View | Tip | Yeni Component |
|---|---|---|---|
| 66 | `posts/index.blade.php` | SFC | `blog::post-list` |
| 67 | `posts/edit-add.blade.php` | SFC | `blog::post-form` |
| 68 | `pages/index.blade.php` | SFC | `blog::page-list` |
| 69 | `pages/edit-add.blade.php` | SFC | `blog::page-form` |
| 70 | `categories/index.blade.php` | SFC | `blog::category-list` |
| 71 | `categories/edit-add.blade.php` | SFC | `blog::category-form` |

#### Menu Plugin

| # | Eski View | Tip | Yeni Component |
|---|---|---|---|
| 72 | `menus/builder.blade.php` | **MFC** | `menu::builder` |
| 73 | `menus/browse.blade.php` | SFC | `menu::list` |

### Özet: Component Dagitimi

| Tip | Adet | Aciklama |
|---|---|---|
| Core Livewire MFC | 12 | bread-table, bread-form, media-manager, dashboard, settings-manager, database-manager, plugins-manager, themes-manager, activity-log, queue-manager, upgrade-wizard, impersonation |
| Core Livewire SFC | ~20 | bread-read, bread-order, admin-menu, role-list, role-form, user-list, user-form, login, profile, bread-tools, bread-tools-form, compass, navbar, app-footer, bulk-delete, coordinates, dimmers, alerts, cache-manager, maintenance-mode |
| Core Blade partial (FormField) | ~21 | text, image, checkbox, ... |
| Blog Plugin (SFC) | ~6 | post-list, post-form, page-list, page-form, category-list, category-form |
| Menu Plugin (MFC + SFC) | ~2 | menu::builder (MFC), menu::list (SFC) |
| **Toplam** | **~61** | 75 mevcut view → ~61 (menu + posts/pages plugin'e, yeni özellikler eklendi) |

---

## 6. Yeni Özellik Detaylari

### 6.1 Activity Log

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

### 6.2 Cache Manager

- **UI**: checkbox'lar (config, route, view, event, app), "Clear Selected" butonu
- **SFC**: `components.cache-manager`

### 6.3 Maintenance Mode

- **UI**: Toggle, opsiyonel mesaj / IP beyaz listesi
- Laravel 13 built-in `php artisan down/up` ile Livewire entegrasyonu
- **SFC**: `components.maintenance-mode`

### 6.4 Queue Manager (Failed Jobs)

- **UI**: Failed jobs listesi + retry / forget butonlari
- Laravel built-in `failed_jobs` tablosunu okur
- **MFC**: `components.queue-manager`

### 6.5 User Impersonation

- **UI**: Kullanici listesinde "Login as" butonu, navbar'da "Exit impersonation" bildirimi
- Laravel built-in `Auth::onceUsingId()` ile veya custom guard
- **SFC**: `components.impersonation`

### 6.6 Upgrade Wizard (v2 → v3)

- **UI**: Adim adim migration (veritabani kontrolu → dosya yedekleme → migration calistirma → seeder)
- `php artisan voyager:upgrade` CLI komutu + web UI
- Veritabani schema kontrolu, oneriler, breaking change uyarilari
- **MFC**: `components.upgrade-wizard`

---

## 7. Themes & Plugins

### 7.1 Plugin Sistemi

Voyager'in mevcut extensibility noktalari (FormField'lar, Event'ler, Widget'lar) **korunacak** ve Plugin sistemi altinda birlestirilecek. Her plugin bir Composer paketi olarak kurulur ve `VoyagerPlugin` interface'ini implemente eder.

#### `VoyagerPlugin` Interface

```php
namespace YellowThree\Voyager\Plugins\Contracts;

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

#### Plugin Kesfi (composer.json)

```json
{
    "name": "yellow-three/voyager-blog",
    "extra": {
        "voyager": {
            "plugin": {
                "class": "YellowThree\\Voyager\\Plugins\\Blog\\BlogServiceProvider",
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
    // Menu items (Dashboard, Media, Settings, vb.)
}
```

### 7.2 Tema Sistemi

#### `Theme` Interface

```php
namespace YellowThree\Voyager\Themes\Contracts;

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

### 7.3 Extensible FormField Sistemi

FormField'lari **Livewire SFC'ye degil, Blade partial olarak devam edecek**. `HandlerInterface` ve handler class'lari korunuyor.

**Eski (v2):** `app/FormFields/MyField.php` birak → otomatik kesif.
**Yeni (v3):** Plugin yaz → `formFields()` ile kaydet → `PluginManager` yukler.

| Kim | Nasil |
|---|---|
| Voyager built-in field'lar (21 adet) | `VoyagerCorePlugin` ile |
| Kullanici field'lari | Plugin yazarak |
| Tek field (plugin yazmadan) | `Voyager::registerFormField(MyHandler::class)` event hook |
| v2'den gecen (backward compat) | `app/FormFields/` shim katmani ile (deprecation warning) |

### 7.4 Veritabani Migrations

Yeni tablolar (core):

```php
// plugins tablosu
Schema::create('plugins', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('version');
    $table->boolean('is_active')->default(true);
    $table->json('settings')->nullable();
    $table->timestamps();
});

// themes tablosu
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
    $table->string('action');
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->string('ip')->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamps();
    $table->index(['model_type', 'model_id']);
    $table->index('action');
});
```

### 7.5 Event Sistemi Uyumlulugu

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

### 7.6 Backward Compatibility Katmani

v2 kullanicilari icin deprecation + shim katmani:

- **`app/FormFields/` shim**: `VoyagerServiceProvider::boot()`'ta eski dizin taranir, varsa `Voyager::registerFormField()` yapilir, deprecation warning loglanir
- **`Widget::run()` shim**: `arrilot/laravel-widgets` kalkiyor ama `Widget::run()` cagrilirsa `trigger_error()` ile deprecation uyarisi
- **`window.$` shim**: Admin panelde `window.$ = document.querySelector.bind(document)` saglanabilir (istege bagli)
- **`@extends('voyager::master')` shim**: Yeni layout system'e yonlendiren bir Blade include

### 7.7 CLI Plugin Generator

`php artisan voyager:make:plugin {name}` komutu ile yeni bir plugin iskeleti olusturulur:

```bash
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

### 7.8 Model Registry

```php
namespace YellowThree\Voyager\Plugins;

class ModelRegistry
{
    protected array $models = [];

    public function register(string $alias, string $class): void;
    public function get(string $alias): ?string;
    public function all(): array;
    public function forDataType(DataType $dataType): ?string;
}
```

---

## 8. First-Party Plugin'ler

### 8.1 Menu Builder Plugin (`yellow-three/voyager-menu`)

```
plugins/menu/
├── MenuServiceProvider.php
├── MenuPlugin.php
├── composer.json
├── resources/views/
│   ├── components/
│   │   ├── ⚡menu-builder/        (MFC)
│   │   └── ⚡menu-list.blade.php  (SFC)
│   └── render/
│       └── default.blade.php      (Tailwind'li render helper)
├── src/
│   ├── Models/Menu.php
│   ├── Models/MenuItem.php
│   ├── Http/Controllers/MenuController.php  (API)
│   ├── Migrations/
│   └── Seeders/
└── routes/menu.php
```

### 8.2 Blog Plugin (`yellow-three/voyager-blog`)

```
plugins/blog/
├── BlogServiceProvider.php
├── BlogPlugin.php
├── composer.json
├── resources/views/
│   ├── components/
│   │   ├── ⚡post-list.blade.php
│   │   ├── ⚡post-form.blade.php
│   │   ├── ⚡page-list.blade.php
│   │   ├── ⚡page-form.blade.php
│   │   ├── ⚡category-list.blade.php
│   │   └── ⚡category-form.blade.php
│   └── formfields/
│       └── blog-category-select.blade.php
├── src/
│   ├── Models/ (Post, Page, Category)
│   ├── FormFields/ (CategorySelectHandler)
│   ├── Migrations/
│   └── Seeders/
└── routes/blog.php
```

---

## 9. v2 → v3 Geçiş Stratejisi

### 9.1 Upgrade Komutu

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

### 9.2 UPGRADE.md

- Breaking changes listesi (cozumleriyle)
- `voyager_asset()` → Vite helper migration
- `@extends('voyager::master')` → Livewire layout migration
- jQuery plugin'leri icin alternatifler
- `voyager::formfields.*` → plugin sistemi
- `Widget::run()` → `@livewire('components.widget')`
- `app/FormFields/` → plugin veya `Voyager::registerFormField()`
- Namespace: `TCG\Voyager` → `YellowThree\Voyager`

### 9.3 Seeders

Mevcut 11 seeder guncelleniyor:

- `DataTypesTableSeeder` → yeni plugin alanlari icin guncellenecek
- `PermissionsTableSeeder` → yeni özellik permission'lari eklenecek (activity-log, cache, maintenance, queue)
- `MenuItemsTableSeeder` → yeni menu ogeleri eklenecek
- Yeni: `PluginsTableSeeder`, `ThemesTableSeeder`
- Blog plugin kendi seeder'larini getirir

### 9.4 Database Migration Sirasi

```
1. create_plugins_table          (core v3)
2. create_themes_table           (core v3)
3. create_activity_logs_table    (core v3)
4. create_posts_table            (blog plugin)
5. create_categories_table       (blog plugin)
6. create_category_post_table    (blog plugin)
```

---

## 10. Aşamalar

### Asama 1: Proje Altyapisi & Bagimliliklar

- `composer.json` guncelle:
  - `name`: `yellow-three/voyager`
  - PHP: `^8.3|^8.4`
  - Laravel: `^13.0`
  - Livewire: `^4.0`
  - Dev: `orchestra/testbench: ^11.0`, `phpunit/phpunit: ^11.0`
- Namespace: `TCG\Voyager` → `YellowThree\Voyager` (tum `src/` klasoru)
- `arrilot/laravel-widgets` bagimliligini kaldir
- `package.json` yenile (Vite + Tailwind 4 + Alpine + Dropzone + TinyMCE + CodeMirror)
- `laravel/boost` eklenmeyecek (package oldugu icin)
- `.github/workflows/` CI guncelle (PHP 8.3 + 8.4, Laravel 13)
- `git remote add upstream https://github.com/thedevdojo/voyager`

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

6a. PluginManager + VoyagerPlugin interface (`YellowThree\Voyager\Plugins\Contracts`)
6b. ThemeManager + Theme interface (`YellowThree\Voyager\Themes\Contracts`)
6c. VoyagerCorePlugin (built-in field'lar, menu, widget'lar)
6d. Plugins Manager + Themes Manager MFC
6e. DB migrations (plugins, themes, activity_logs)
6f. Extensions UI sayfalari
6g. ModelRegistry (plugin model'lerini core'a tanitma mekanizmasi)
6h. `php artisan voyager:make:plugin` CLI generator

### Asama 7: First-Party Plugin'ler

7a. Menu Plugin (`yellow-three/voyager-menu`) — Menu + MenuItem modelleri, drag-drop builder MFC
7b. Blog Plugin (`yellow-three/voyager-blog`) — Post, Page, Category modelleri, CRUD SFC'ler
7c. Her iki plugin'in kaydi (menu items, routes, form fields)
7d. Admin sidebar'in plugin aktif/pasif durumuna gore menu gostermesi

### Asama 8: Backward Compatibility & Upgrade

8a. `app/FormFields/` shim katmani (deprecation warning)
8b. `php artisan voyager:upgrade` CLI komutu
8c. `Widget::run()` shim
8d. `@extends('voyager::master')` → Livewire layout redirect
8e. UPGRADE.md + CHANGELOG.md
8f. v2→v3 + namespace migration rehberi (`TCG\Voyager` → `YellowThree\Voyager`)

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

- Mevcut 34 test KORUNACAK (namespace guncellenmesi gerekecek)
- Livewire component testleri EKLENECEK
- Activity Log testleri
- Plugin sistemi testleri
- Blog plugin testleri
- Upgrade komutu testleri
- Playwright E2E test altyapisi — temel admin akislari
- Translation CI validator — 630 dil dosyasinin eksiksiz oldugunu CI'da kontrol et

### Asama 14: Dokumantasyon

- Repository map in Agents.md guncelle
- `docs/bagisto-comparison.md` — Bagisto mimarisiyle karsilastirma
- `docs/migration-from-tcg-voyager.md` — `tcg/voyager` → `yellow-three/voyager` gecis rehberi

---

## 11. İş Takvimi

| Asama | Is | Sure (gun) |
|---|---|---|
| **1** | Proje altyapisi & bagimliliklar + namespace migration | 1.5 |
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
| **8c** | UPGRADE.md + CHANGELOG.md + namespace migration rehberi | 1 |
| **9** | Event sistemi uyumlulugu | 1 |
| **10** | Controller API layer | 1.5 |
| **11** | Routes & Service Provider | 0.5 |
| **12** | Publishable assets | 1 |
| **13** | Testler & CI (PHPUnit + Playwright E2E + Translation CI) | 4-5 |
| **14** | Dokumantasyon | 1 |
| | **Toplam** | **~47-57 gun** |

---

## 12. Riskler & Dikkat Edilmesi Gerekenler

### Yüksek Öncelikli

- **Namespace migration**: `TCG\Voyager` → `YellowThree\Voyager` tum `src/` klasorunü etkiliyor. Kullanicilarin import'larını guncelleme rehberi sart.
- **v2 → v3 upgrade**: Kullanicilarin sorunsuz gecmesi icin upgrade komutu + dokumasyon sart.
- **Dynamic BREAD**: DataType + DataRow model'leri üzerinden dinamik CRUD. Livewire component'ler DataType'a gore dinamik olusturulmali.
- **Plugin sistemi tasarimi**: Interface'ler ve kesif mekanizmasi ilk asamada dogru kurgulanmali.
- **Event sistemi**: 24 event var, plugin'ler ve custom kod bunlara bagli.
- **FormField backward compat**: `app/FormFields/` kalkiyor, eski projeler kırılmasın diye shim + deprecation süreci gerekli.

### Orta Öncelikli

- **TinyMCE/CodeMirror wrapper**: Livewire + Alpine ile sarilmali.
- **Dropzone jQuery kaldirma**: Alpine wrapper ile.
- **Blog plugin**: Core'dan ayriliyor, migration'lar ve data uyumu.
- **Ceviri dosyalari (630 dosya)**: Korunuyor, namespace yorumlari gozden gecirilmeli.
- **Translation CI validator**: Tum dillerde eksik anahtar kontrolu otomatiklesmeli.
- **Activity Log performans**: Çok sayida log yazilabilir, index + periyodik temizlik.
- **E2E test altyapisi (Playwright)**: Temel admin akislari test edilmeli.

### Dusuk Öncelikli

- **Arrilot widgets kaldirma**: Shim ile deprecation.
- **Flysystem versiyon uyumu**: Guncelleme yeterli.
- **Laravel 14 hazırlığı**: Gerekirse `^13.0|^14.0` olarak genisletilir.

### Kirilma Noktalari (Breaking Changes)

| Degisiklik | Etki | Cozum |
|---|---|---|
| `TCG\Voyager` → `YellowThree\Voyager` | Tum import'lar kirilir | Namespace migration rehberi |
| `composer require tcg/voyager` → `yellow-three/voyager` | `composer.json` guncellenmeli | docs/migration-from-tcg-voyager.md |
| `voyager_asset()` helper degisiyor | Asset path'leri degisir | Upgrade komutu + migration rehberi |
| Bootstrap 3 → Tailwind CSS | HTML class'lari degisir | View'ler package'dan, host etkilenmez |
| jQuery kalkiyor | `window.$` kullanan kod kirilir | Shim + migration rehberi |
| `@extends('voyager::master')` → Livewire layout | Host view kalip degistiremez | Layout override mekanizmasi |
| `arrilot/laravel-widgets` kalkiyor | `Widget::run()` kirilir | Shim + Livewire widget API |
| `app/FormFields/` auto-discovery kalkiyor | Custom field'ler calismaz | Plugin veya `registerFormField()` + shim |
| **Posts/Pages/Categories** → blog plugin | Bu sayfalara bagimli kod kirilir | Blog plugin kurulum rehberi |
| **Menu Builder** → menu plugin | `Menu`, `MenuItem` model'leri plugin'e tasindi | `composer require yellow-three/voyager-menu` |

---

## 13. v3.1+ Yol Haritası

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

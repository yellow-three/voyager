# Voyager v3 — Migrasyon ve Geliştirme Planı

> **Stack:** Laravel 13 · Livewire 4 (SFC/MFC) · Tailwind CSS 4 · Vite · Alpine.js  
> **Paket:** `yellow-three/voyager` · **Namespace:** `YellowThree\Voyager`  
> **Son Güncelleme:** Mayıs 2025 — Voyager II analizi + mimari kararlar eklendi

---

## İçindekiler

1. [Neden v3?](#1-neden-v3)
2. [Mevcut Durum](#2-mevcut-durum)
3. [Kesinleşmiş Mimari Kararlar](#3-kesinleşmiş-mimari-kararlar)
4. [Hedef Mimari](#4-hedef-mimari)
5. [SFC / MFC Karar Kriterleri](#5-sfc--mfc-karar-kriterleri)
6. [Component Envanteri](#6-component-envanteri)
7. [BreadManager — Hibrit Sistem](#7-breadmanager--hibrit-sistem)
8. [Plugin Sistemi](#8-plugin-sistemi)
9. [Tema Sistemi](#9-tema-sistemi)
10. [FormField Sistemi](#10-formfield-sistemi)
11. [Yeni v3.0 Özellikleri](#11-yeni-v30-özellikleri)
12. [First-Party Pluginler](#12-first-party-pluginler)
13. [v2 → v3 Geçiş Stratejisi](#13-v2--v3-geçiş-stratejisi)
14. [Branch Stratejisi](#14-branch-stratejisi)
15. [Aşamalar](#15-aşamalar)
16. [İş Takvimi](#16-iş-takvimi)
17. [Riskler ve Dikkat Edilmesi Gerekenler](#17-riskler-ve-dikkat-edilmesi-gerekenler)
18. [v3.1+ Yol Haritası](#18-v31-yol-haritası)

---

## 1. Neden v3?

`thedevdojo/voyager` (`tcg/voyager`) **7 Şubat 2025'te resmi olarak arşivlendi**, son sürüm v1.8.0 (Eylül 2024). Bu proje, `thedevdojo/voyager@1.7` fork'u üzerinden tamamen yeniden yazılan community-driven v3 girişimidir.

| | Mevcut (v1.7) | Hedef (v3) |
|---|---|---|
| Frontend | Bootstrap 3 + jQuery + Vue 2 | Tailwind 4 + Alpine.js + Livewire 4 |
| Laravel | ~8.0–~11.0 | ^13.0 |
| Build | Laravel Mix 6 (Webpack) | Vite |
| JS Bundle | ~2.5 MB | tree-shaking ile ~150-300 KB |
| Extensibility | Dağınık FormField/Event/Widget | Tutarlı Plugin sistemi |
| Test | PHPUnit + testbench | Pest 3 + testbench ^11.0 |
| Packagist | `tcg/voyager` (arşivlenmiş) | `yellow-three/voyager` |
| PHP Namespace | `TCG\Voyager` | `YellowThree\Voyager` |

### Referans Projeler İncelendi

- **thedevdojo/voyager@1.7** — Fork kaynağı, arşivlenmiş, referans olarak korunuyor
- **voyager-admin/voyager (Voyager II)** — Aynı fork ağacından çıkmış, Vue 3 tabanlı community rewrite. JSON BREAD, çoklu layout, granüler plugin contracts gibi özellikler bu projeden ilham alındı. Kaynak: `https://voyager-admin.github.io/voyager/`
- **bagisto/bagisto** — Modular monolith yaklaşımı için incelendi

---

## 2. Mevcut Durum

> **Baz:** `yellow-three/voyager`, `1.7` branch — `thedevdojo/voyager@1.7`'den türetildi.

| Kategori | Değer |
|---|---|
| PHP kaynak dosyası (`src/`) | 205 |
| Blade view | 75 |
| Migration | 20 |
| Test | 34 (PHPUnit → Pest'e migrate edilecek) |
| Dil dosyası | ~630 |
| JS kaynağı | 7 |
| Vue SFC | 1 (`admin_menu.vue`) |
| SCSS | 2 (`app.scss`, `_variables.scss`) |

---

## 3. Kesinleşmiş Mimari Kararlar

Bu kararlar tartışmaya kapalıdır. Kodlamaya başlamadan önce bilinmesi gerekir.

### Karar A — Hibrit BreadManager (JSON önce, DB fallback)

**Seçilen:** JSON tabanlı BREAD storage, DB backward compat ile birlikte.

| | JSON (yeni) | DB (eski) |
|---|---|---|
| VCS takibi | `git diff storage/breads/users.json` | DB'de, görünmez |
| Ortam taşıma | Dosya kopyala yeter | Migration + seeder gerekir |
| Backup/Rollback | Built-in snapshot | Manuel |
| v1.x uyumu | Upgrade wizard ile | Native |
| Çoklu layout | JSON yapısı hazır | Tablo şeması değişmeli |

**Uygulama:** `BreadManager` önce `storage/voyager/breads/{slug}.json` arar, yoksa `data_types` tablosuna bakar. JSON her zaman önceliklidir.

**Upgrade path:** `php artisan voyager:export-breads` → DB'deki BREAD tanımlarını JSON'a dönüştürür.

### Karar B — `src/` flat kalır, `src/Core/` wrapper yok

**Seçilen:** Flat `src/` yapısı.

`src/Core/` katmanı gereksiz karmaşıklık ekler, PSR-4 autoload'u komplike kılar, git diff'leri büyütür. `src/` altında anlamlı klasör isimleri yeterli:

```
src/Bread/    src/Models/    src/FormFields/
src/Plugins/  src/Themes/    src/Http/
src/Events/   src/Console/   ...
```

`composer.json`:
```json
"autoload": {
    "psr-4": {
        "YellowThree\\Voyager\\": "src/"
    }
}
```

### Karar C — BasePlugin abstract + granüler contract interface'ler

**Seçilen:** Tek şişirilmiş interface yerine `BasePlugin` abstract class + isteğe bağlı contract'lar.

```php
// Plugin sadece ihtiyacı olanı implement eder
class BlogPlugin extends BasePlugin
    implements FormfieldPlugin, MenuPlugin { ... }

class SanctumPlugin extends BasePlugin
    implements AuthenticationPlugin { ... }

class SpatiePlugin extends BasePlugin
    implements AuthorizationPlugin { ... }
```

Granüler contracts (Voyager II'den ilham): `FormfieldPlugin`, `MenuPlugin`, `WidgetPlugin`, `AuthenticationPlugin`, `AuthorizationPlugin`, `FilterPlugin`.

### Karar D — Livewire SFC/MFC Package Registration

Package içindeki Livewire bileşenleri otomatik keşfedilmez. `VoyagerServiceProvider::register()` içinde explicit path tanımı zorunludur:

```php
Livewire::addComponentPath(
    namespace: 'YellowThree\\Voyager',
    path: __DIR__.'/../resources/views/components',
);
```

Bu olmadan Aşama 5'teki hiçbir bileşen render olmaz.

### Karar E — Plugin Keşfi via `extra.laravel.providers`

Laravel'in native auto-discovery mekanizması kullanılır, custom parser yazılmaz:

```json
{
    "keywords": ["laravel", "admin", "voyager-plugin"],
    "extra": {
        "laravel": {
            "providers": ["YellowThree\\VoyagerBlog\\BlogServiceProvider"]
        }
    }
}
```

Packagist'te `voyager-plugin` keyword'ü ile filtreleme için plugin paketleri bu keyword'ü taşımalı.

---

## 4. Hedef Mimari

```
voyager/
├── src/
│   ├── Bread/                          # Hibrit BreadManager + Sources
│   │   ├── BreadManager.php            # JSON önce, DB fallback
│   │   ├── Bread.php                   # Value object
│   │   ├── BreadLayout.php             # Layout value object (çoklu layout için hazır)
│   │   ├── Sources/
│   │   │   ├── JsonBreadSource.php     # storage/voyager/breads/*.json
│   │   │   └── DatabaseBreadSource.php # data_types + data_rows (backward compat)
│   │   └── Concerns/
│   │       └── HasBread.php            # Model trait
│   ├── Models/                         # DataType*, DataRow*, Setting, Permission, Role, User
│   ├── Http/
│   │   └── Controllers/               # API controller'lar (UI değil, dış entegrasyon)
│   ├── FormFields/                     # HandlerInterface + 27 handler
│   ├── Plugins/
│   │   ├── Contracts/
│   │   │   ├── FormfieldPlugin.php
│   │   │   ├── MenuPlugin.php
│   │   │   ├── WidgetPlugin.php
│   │   │   ├── AuthenticationPlugin.php
│   │   │   ├── AuthorizationPlugin.php
│   │   │   └── FilterPlugin.php
│   │   ├── BasePlugin.php             # Abstract, tüm metodlara boş default
│   │   ├── PluginManager.php
│   │   ├── ModelRegistry.php
│   │   └── VoyagerCorePlugin.php      # Built-in field'lar, menü, widget'lar
│   ├── Themes/
│   │   ├── Contracts/Theme.php
│   │   └── ThemeManager.php
│   ├── ActivityLog/
│   │   ├── ActivityLog.php            # Model
│   │   └── ActivityLogger.php         # Facade arkası
│   ├── Upgrade/
│   │   ├── UpgradeManager.php
│   │   └── Steps/                     # Her adım ayrı class
│   ├── BackwardCompatibility/
│   │   ├── FormFieldShim.php          # app/FormFields/ shim
│   │   └── WidgetShim.php             # Widget::run() shim
│   ├── Events/                        # 24 event (korunuyor)
│   ├── Console/
│   │   ├── InstallCommand.php
│   │   ├── UpgradeCommand.php
│   │   ├── ExportBreadsCommand.php    # YENİ: DB → JSON export
│   │   ├── MakePluginCommand.php
│   │   └── ValidateLangCommand.php
│   └── VoyagerServiceProvider.php
│
├── resources/
│   ├── views/
│   │   ├── components/                # Core Livewire SFC + MFC (⚡ = SFC/MFC)
│   │   │   ├── ⚡bread-table.blade.php         (MFC)
│   │   │   ├── ⚡bread-form/                   (MFC)
│   │   │   ├── ⚡bread-read.blade.php           (SFC)
│   │   │   ├── ⚡bread-order.blade.php          (SFC)
│   │   │   ├── ⚡bread-tools.blade.php          (SFC)
│   │   │   ├── ⚡media-manager/                (MFC)
│   │   │   ├── ⚡dashboard/                    (MFC)
│   │   │   ├── ⚡settings-manager/             (MFC)
│   │   │   ├── ⚡database-manager/             (MFC)
│   │   │   ├── ⚡activity-log/                 (MFC) — YENİ
│   │   │   ├── ⚡cache-manager.blade.php        (SFC) — YENİ
│   │   │   ├── ⚡maintenance-mode.blade.php     (SFC) — YENİ
│   │   │   ├── ⚡queue-manager/                (MFC) — YENİ
│   │   │   ├── ⚡impersonation.blade.php        (SFC) — YENİ
│   │   │   ├── ⚡upgrade-wizard/               (MFC) — YENİ
│   │   │   ├── ⚡plugins-manager/              (MFC)
│   │   │   ├── ⚡themes-manager/               (MFC)
│   │   │   ├── ⚡admin-menu.blade.php           (SFC)
│   │   │   ├── ⚡role-list.blade.php            (SFC)
│   │   │   ├── ⚡role-form.blade.php            (SFC)
│   │   │   ├── ⚡user-list.blade.php            (SFC)
│   │   │   ├── ⚡user-form.blade.php            (SFC)
│   │   │   └── ⚡compass.blade.php              (SFC)
│   │   ├── formfields/                # Blade partial (handler ile eşleşir)
│   │   ├── layouts/
│   │   │   ├── ⚡admin.blade.php
│   │   │   └── ⚡auth.blade.php
│   │   ├── pages/
│   │   │   ├── ⚡login.blade.php
│   │   │   ├── ⚡dashboard.blade.php
│   │   │   ├── ⚡profile.blade.php
│   │   │   ├── ⚡plugins.blade.php
│   │   │   ├── ⚡themes.blade.php
│   │   │   ├── ⚡activity-log.blade.php
│   │   │   ├── ⚡cache.blade.php
│   │   │   ├── ⚡maintenance.blade.php
│   │   │   └── ⚡queue.blade.php
│   │   └── partials/
│   │       ├── navbar.blade.php
│   │       ├── app-footer.blade.php
│   │       ├── bulk-delete.blade.php
│   │       └── coordinates.blade.php
│   ├── css/app.css                   # Tailwind 4 @import + @theme
│   └── js/app.js                     # Alpine.js + Dropzone + TinyMCE + CodeMirror 6
│
├── plugins/                           # First-party plugin'ler (ayrı Composer paketleri)
│   ├── blog/                         # yellow-three/voyager-blog
│   └── menu/                         # yellow-three/voyager-menu
│
├── publishable/
│   ├── assets/build/                 # Vite build çıktısı (edit etme)
│   ├── config/voyager.php
│   └── lang/                         # 630 dil dosyası (korunuyor)
│
├── storage/
│   └── voyager/
│       └── breads/                   # JSON BREAD tanımları (runtime)
│
├── migrations/                        # 20 mevcut + 3 yeni (plugins, themes, activity_logs)
├── routes/voyager.php
├── tests/
│   ├── Unit/
│   ├── Feature/
│   └── E2E/                          # Playwright
├── vite.config.js
├── package.json
└── composer.json
```

> *`DataType`/`DataRow` modelleri `DatabaseBreadSource` için korunuyor — sadece `BreadManager` arkasına gizleniyor.

### Modüler Yapı

```
yellow-three/voyager (core) ^3.0
├── BREAD, Media, Settings, Users, Roles
├── Plugin/Theme yönetimi
├── Activity Log, Cache, Maintenance, Queue
├── Impersonation, Upgrade Wizard
└── v2 backward compatibility layer

yellow-three/voyager-menu ^1.0
├── Models: Menu, MenuItem
├── Drag-drop builder (MFC)
└── Plugin sistemi üzerinden core'a bağlanır

yellow-three/voyager-blog ^1.0
├── Models: Post, Page, Category
├── CRUD bileşenleri (SFC)
└── Plugin sistemi üzerinden core'a bağlanır
```

---

## 5. SFC / MFC Karar Kriterleri

### SFC — `php artisan make:livewire foo --sfc`

Kullanım koşulları:
- Toplam < ~80 satır (PHP + HTML birlikte)
- Az state (1-3 property)
- Harici JS gerektirmez
- Tek amaca hizmet eder

### MFC — `php artisan make:livewire foo --mfc`

Kullanım koşulları:
- PHP mantığı > ~80 satır
- Pagination, sorting, complex validation
- Harici JS entegrasyonu (Dropzone, TinyMCE, CodeMirror 6)
- Karmaşık state yönetimi

### Class-based — Kullanılmaz

### Karar Tablosu

| Bileşen | Tip | Gerekçe |
|---|---|---|
| **bread-table** | **MFC** | Sorting, pagination, bulk actions, DataType-driven dinamik kolonlar |
| **bread-form** | **MFC** | Dinamik field rendering, validation, relationship'ler |
| **bread-read** | SFC | Basit veri gösterimi |
| **bread-order** | SFC | Drag-drop, Alpine.js yeterli |
| **bread-tools** | SFC | BREAD builder arayüzü, orta karmaşıklık |
| **media-manager** | **MFC** | Dropzone + upload + galeri + crop — JS ağır |
| **dashboard** | **MFC** | Widget sistemi, lazy loading, plugin widget'ları |
| **settings-manager** | **MFC** | Grup ayarlar, dinamik tip rendering, validation |
| **database-manager** | **MFC** | Schema okuma, tablo yönetimi, eski Vue SFC'ler burada |
| **activity-log** | **MFC** | Filter, search, pagination, detail modal |
| **queue-manager** | **MFC** | Failed jobs, retry, forget, pagination |
| **upgrade-wizard** | **MFC** | Adım adım migration UI, async steps |
| **plugins-manager** | **MFC** | Plugin listesi, aktif/pasif, install |
| **themes-manager** | **MFC** | Tema listesi, preview, aktifleştirme |
| **menu-builder** | **MFC** (plugin) | Drag-drop nested, karmaşık JS |
| **cache-manager** | SFC | Checkbox'lar + butonlar, basit |
| **maintenance-mode** | SFC | Toggle + form, basit |
| **impersonation** | SFC | "Login as" butonu, basit |
| **admin-menu** | SFC | Vue'dan taşınıyor, Alpine.js yeterli |
| **role-list/form** | SFC | Standart CRUD |
| **user-list/form** | SFC | Standart CRUD |
| **login** | SFC | Basit form |
| **profile** | SFC | Basit form |
| **compass** | SFC | Statik sayfa |
| **FormField view'leri** | **Blade partial** | Extensible yapı, SFC değil |

---

## 6. Component Envanteri

### 6A — Layout ve Yapısal

| # | Mevcut View | Yeni | Tip |
|---|---|---|---|
| 1 | `master.blade.php` | `layouts::admin` | Layout |
| 2 | `auth/master.blade.php` | `layouts::auth` | Layout |
| 3 | `dashboard/navbar.blade.php` | `partials.navbar` | Blade partial |
| 4 | `dashboard/sidebar.blade.php` | `components::admin-menu` | SFC |
| 5 | `partials/app-footer.blade.php` | `partials.app-footer` | Blade partial |
| 6 | `partials/bulk-delete.blade.php` | `partials.bulk-delete` | Blade partial |
| 7 | `partials/coordinates.blade.php` | `partials.coordinates` | Blade partial |

### 6B — BREAD

| # | Mevcut | Yeni | Tip |
|---|---|---|---|
| 8 | `bread/browse.blade.php` | `components::bread-table` | MFC |
| 9 | `bread/edit-add.blade.php` | `components::bread-form` | MFC |
| 10 | `bread/read.blade.php` | `components::bread-read` | SFC |
| 11 | `bread/order.blade.php` | `components::bread-order` | SFC |

### 6C — FormField Blade Partial'ları (27 field — §10'a bakın)

### 6D — Tools

| # | Mevcut | Yeni | Tip |
|---|---|---|---|
| 12 | `tools/bread/browse.blade.php` | `components::bread-tools` | SFC |
| 13 | `tools/bread/edit-add.blade.php` | `components::bread-tools-form` | SFC |
| 14 | `tools/database/index.blade.php` + 5 Vue SFC | `components::database-manager` | MFC |

### 6E — CRUD ve Yönetim

| # | Mevcut | Yeni | Tip |
|---|---|---|---|
| 15 | `media/index.blade.php` | `components::media-manager` | MFC |
| 16 | `settings/index.blade.php` | `components::settings-manager` | MFC |
| 17 | `roles/index.blade.php` | `components::role-list` | SFC |
| 18 | `roles/edit-add.blade.php` | `components::role-form` | SFC |
| 19 | `users/index.blade.php` | `components::user-list` | SFC |
| 20 | `users/edit-add.blade.php` | `components::user-form` | SFC |
| 21 | `compass/index.blade.php` | `components::compass` | SFC |

### 6F — Sayfalar

| # | Mevcut | Yeni | Tip |
|---|---|---|---|
| 22 | `login.blade.php` | `pages::login` | SFC |
| 23 | `index.blade.php` | `pages::dashboard` | MFC |
| 24 | `profile.blade.php` | `pages::profile` | SFC |

### 6G — Extensions (Core)

| # | Yeni | Tip |
|---|---|---|
| 25 | `pages::plugins` | MFC |
| 26 | `pages::themes` | MFC |

### 6H — Yeni v3.0 Özellikler

| # | Yeni | Tip |
|---|---|---|
| 27 | `components::activity-log` | MFC |
| 28 | `components::cache-manager` | SFC |
| 29 | `components::maintenance-mode` | SFC |
| 30 | `components::queue-manager` | MFC |
| 31 | `components::impersonation` | SFC |
| 32 | `components::upgrade-wizard` | MFC |

### 6I — Plugin View'leri

#### Blog Plugin (`yellow-three/voyager-blog`)

| Bileşen | Tip |
|---|---|
| `blog::post-list` | SFC |
| `blog::post-form` | SFC |
| `blog::page-list` | SFC |
| `blog::page-form` | SFC |
| `blog::category-list` | SFC |
| `blog::category-form` | SFC |

#### Menu Plugin (`yellow-three/voyager-menu`)

| Bileşen | Tip |
|---|---|
| `menu::builder` | MFC |
| `menu::list` | SFC |

### Özet

| Kategori | Adet |
|---|---|
| Core MFC | 12 |
| Core SFC | ~20 |
| Core Blade partial (FormField) | 27 |
| Blog Plugin SFC | 6 |
| Menu Plugin MFC+SFC | 2 |
| **Toplam** | **~67** |

---

## 7. BreadManager — Hibrit Sistem

> **Referans:** Voyager II JSON storage yaklaşımından ilham alındı. Ancak v1.x uyumluluğu için hibrit tasarım benimsendi.

### 7.1 Sınıf Yapısı

```php
// src/Bread/BreadManager.php
namespace YellowThree\Voyager\Bread;

class BreadManager
{
    public function __construct(
        protected JsonBreadSource     $json,
        protected DatabaseBreadSource $database,
    ) {}

    // JSON önce, yoksa DB
    public function find(string $slug): ?Bread
    {
        return $this->json->find($slug)
            ?? $this->database->find($slug);
    }

    // JSON öncelikli, DB'dekiler (JSON'da yoksa) eklenir
    public function all(): Collection
    {
        $fromJson = $this->json->all();
        $fromDb   = $this->database->all()
            ->reject(fn($b) => $fromJson->has($b->slug));

        return $fromJson->merge($fromDb);
    }

    // Yeni BREAD'ler JSON'a yazılır
    public function save(Bread $bread): void
    {
        $this->json->save($bread);
    }

    // DB'den JSON'a tek seferlik export
    public function exportAllToJson(): void
    {
        $this->database->all()->each(
            fn($bread) => $this->json->save($bread)
        );
    }
}
```

### 7.2 JSON Yapısı

`storage/voyager/breads/{slug}.json`:

```json
{
    "slug": "users",
    "name": "Users",
    "display_name": "Kullanıcılar",
    "model": "App\\Models\\User",
    "icon": "users",
    "global_search_field": "name",
    "layouts": [
        {
            "name": "Default List",
            "type": "list",
            "scope": null,
            "formfields": [
                { "column": "name",  "type": "text", "searchable": true  },
                { "column": "email", "type": "text", "searchable": true  },
                { "column": "role",  "type": "relationship", "searchable": false }
            ]
        },
        {
            "name": "Default Edit",
            "type": "view",
            "formfields": [
                { "column": "name",     "type": "text",     "validation": "required"       },
                { "column": "email",    "type": "text",     "validation": "required|email" },
                { "column": "password", "type": "password", "validation": "nullable|min:8" },
                { "column": "role_id",  "type": "relationship" }
            ]
        }
    ]
}
```

> **Not:** `layouts` array v3.0'da tek layout destekler. Yapı v3.1 çoklu layout için hazır bırakılmıştır.

### 7.3 ServiceProvider Kaydı

```php
// VoyagerServiceProvider::register()
$this->app->singleton(BreadManager::class, function ($app) {
    return new BreadManager(
        json: new JsonBreadSource(
            path: storage_path('voyager/breads'),
        ),
        database: new DatabaseBreadSource(),
    );
});

$this->app->alias(BreadManager::class, 'voyager.bread');
```

### 7.4 Backup / Rollback

JSON dosyaları snapshot sistemi ile korunur. Her kayıtta önceki versiyon yedeklenir:

```
storage/voyager/breads/
├── users.json
├── users.backup.2025-05-20@14-32-10.json
└── posts.json
```

### 7.5 Artisan Komutları

```bash
# DB'deki tüm BREAD tanımlarını JSON'a dönüştür
php artisan voyager:export-breads

# Belirli bir BREAD'i export et
php artisan voyager:export-breads --slug=users

# JSON'dan DB'ye geri yükle (acil durum)
php artisan voyager:import-breads
```

---

## 8. Plugin Sistemi

> **Referans:** Voyager II plugin contract yaklaşımından ilham alındı (`AuthenticationPlugin`, `AuthorizationPlugin`, `FormfieldPlugin` ayrımı). Kaynak: `voyager-admin.github.io/voyager/plugins/`

### 8.1 BasePlugin Abstract Class

```php
// src/Plugins/BasePlugin.php
namespace YellowThree\Voyager\Plugins;

abstract class BasePlugin
{
    abstract public function name(): string;
    abstract public function version(): string;

    // Tüm metodlar boş default — plugin sadece ihtiyacı olanı override eder
    public function label(): string         { return $this->name(); }
    public function description(): string   { return ''; }
    public function author(): string        { return ''; }

    public function menuItems(): array      { return []; }
    public function routes(): void          {}
    public function widgets(): array        { return []; }
    public function actions(): array        { return []; }
    public function migrations(): array     { return []; }
    public function boot(): void            {}
}
```

### 8.2 Granüler Contract Interface'leri

```php
// src/Plugins/Contracts/FormfieldPlugin.php
interface FormfieldPlugin
{
    /** @return string[] HandlerInterface implementasyonları */
    public function formFields(): array;
}

// src/Plugins/Contracts/MenuPlugin.php
interface MenuPlugin
{
    /** @return array{title: string, route: string, icon?: string}[] */
    public function menuItems(): array;
}

// src/Plugins/Contracts/AuthenticationPlugin.php
interface AuthenticationPlugin
{
    public function login(Request $request): JsonResponse;
    public function logout(Request $request): JsonResponse;
    public function forgotPassword(Request $request): JsonResponse;
}

// src/Plugins/Contracts/AuthorizationPlugin.php
interface AuthorizationPlugin
{
    public function authorize(string $ability, mixed $arguments = []): bool;
    public function getPermissionsForUser(Model $user): array;
}

// src/Plugins/Contracts/FilterPlugin.php
interface FilterPlugin
{
    // Voyager II ilham: hangi layout'un gösterileceğini kontrol et
    public function filterLayouts(Bread $bread, string $action, Collection $layouts): Collection;
    public function filterMenuItems(Collection $items): Collection;
    public function filterMedia(string $path, Collection $files): Collection;
}

// src/Plugins/Contracts/WidgetPlugin.php
interface WidgetPlugin
{
    /** @return array{view: string, data: array, width?: string}[] */
    public function widgets(): array;
}
```

### 8.3 Plugin Keşfi ve Yükleme

Plugin keşfi için `extra.laravel.providers` kullanılır — custom parser yok:

**Plugin `composer.json`:**
```json
{
    "name": "yellow-three/voyager-blog",
    "keywords": ["laravel", "voyager-plugin"],
    "extra": {
        "laravel": {
            "providers": ["YellowThree\\VoyagerBlog\\BlogServiceProvider"]
        }
    }
}
```

**Plugin ServiceProvider:**
```php
class BlogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        app(PluginManager::class)->register(new BlogPlugin());
    }
}
```

**PluginManager:**
```php
// src/Plugins/PluginManager.php
class PluginManager
{
    protected array $plugins = [];

    public function register(BasePlugin $plugin): void
    {
        $this->plugins[$plugin->name()] = $plugin;
        $plugin->boot();
    }

    public function getFormFields(): array
    {
        return collect($this->plugins)
            ->filter(fn($p) => $p instanceof FormfieldPlugin)
            ->flatMap(fn($p) => $p->formFields())
            ->all();
    }

    public function getAuthPlugin(): ?AuthenticationPlugin
    {
        return collect($this->plugins)
            ->first(fn($p) => $p instanceof AuthenticationPlugin);
    }
}
```

### 8.4 VoyagerCorePlugin

Built-in tüm özellikler bu plugin üzerinden kaydedilir:

```php
// src/Plugins/VoyagerCorePlugin.php
class VoyagerCorePlugin extends BasePlugin
    implements FormfieldPlugin, MenuPlugin, WidgetPlugin
{
    public function name(): string    { return 'voyager-core'; }
    public function version(): string { return '3.0.0'; }

    public function formFields(): array
    {
        return [
            TextHandler::class, TextareaHandler::class,
            NumberHandler::class, PasswordHandler::class,
            CheckboxHandler::class, RadioHandler::class,
            ToggleHandler::class,   // YENİ
            SelectHandler::class, SelectMultipleHandler::class,
            ImageHandler::class, MultipleImagesHandler::class,
            MediaPickerHandler::class, FileHandler::class,
            RichTextBoxHandler::class, CodeEditorHandler::class,
            MarkdownEditorHandler::class,
            DateHandler::class, TimeHandler::class, TimestampHandler::class,
            HiddenHandler::class, CoordinatesHandler::class,
            ColorHandler::class,
            SlugHandler::class,         // YENİ
            TagsHandler::class,         // YENİ
            SimpleArrayHandler::class,  // YENİ
            RepeaterHandler::class,     // YENİ
            SliderHandler::class,       // YENİ
        ];
    }

    public function menuItems(): array
    {
        return [
            ['title' => 'Dashboard',  'route' => 'voyager.dashboard',    'icon' => 'home'],
            ['title' => 'Media',      'route' => 'voyager.media.index',  'icon' => 'image'],
            ['title' => 'Users',      'route' => 'voyager.users.index',  'icon' => 'users'],
            ['title' => 'Roles',      'route' => 'voyager.roles.index',  'icon' => 'shield'],
            ['title' => 'Settings',   'route' => 'voyager.settings',     'icon' => 'settings'],
            ['title' => 'Tools',      'route' => 'voyager.tools',        'icon' => 'tool'],
        ];
    }
}
```

### 8.5 CLI Plugin Generator

```bash
./vendor/bin/testbench voyager:make:plugin blog

# Oluşturduğu yapı:
# plugins/blog/
# ├── composer.json          (keywords: voyager-plugin, extra.laravel.providers)
# ├── BlogPlugin.php         (BasePlugin extend, ihtiyaca göre interface)
# ├── BlogServiceProvider.php
# ├── routes/blog.php
# ├── src/Models/
# ├── src/FormFields/
# ├── migrations/
# ├── seeders/
# └── resources/views/components/
```

---

## 9. Tema Sistemi

```php
// src/Themes/Contracts/Theme.php
interface Theme
{
    public function name(): string;
    public function version(): string;

    /** Tailwind @theme override — CSS değişkenleri */
    public function colors(): array;

    /** Opsiyonel layout view override'ları */
    public function layoutOverrides(): array;

    /** Opsiyonel ek CSS dosyası */
    public function css(): ?string;

    /** Font tanımları */
    public function fonts(): array;

    /** Dark mode desteği */
    public function darkMode(): bool;
}
```

Tema `config/voyager.php`'de tanımlanır, `ThemeManager` ilgili CSS değişkenlerini `@theme` bloğuna enjekte eder.

---

## 10. FormField Sistemi

> Voyager II formfield karşılaştırmasından eksik field'lar eklendi.  
> Kaynak: `voyager-admin.github.io/voyager/formfields/`

### Kurallar

- Built-in field'lar `VoyagerCorePlugin::formFields()` ile kaydedilir
- `app/FormFields/` auto-discovery **kaldırıldı** — shim katmanı v3.0'da uyarı verir
- Kullanıcı: plugin yazar veya `Voyager::registerFormField(Handler::class)` çağırır
- View'ler Blade partial (`resources/views/formfields/*.blade.php`) — SFC değil

### 27 Field Listesi

| # | Field | Tip | Durum |
|---|---|---|---|
| 1 | `text` | MFC | v1.x'ten |
| 2 | `textarea` | MFC | v1.x'ten |
| 3 | `number` | MFC | v1.x'ten |
| 4 | `password` | MFC | v1.x'ten |
| 5 | `checkbox` | SFC | v1.x'ten |
| 6 | `radio` | SFC | v1.x'ten |
| 7 | `select` | MFC | v1.x'ten |
| 8 | `select_multiple` | MFC | v1.x'ten |
| 9 | `image` | MFC | v1.x'ten |
| 10 | `multiple_images` | MFC | v1.x'ten |
| 11 | `media_picker` | MFC | v1.x'ten |
| 12 | `file` | MFC | v1.x'ten |
| 13 | `rich_text_box` | MFC | v1.x'ten (TinyMCE) |
| 14 | `code_editor` | MFC | v1.x'ten (CodeMirror 6) |
| 15 | `markdown_editor` | MFC | v1.x'ten |
| 16 | `date` | MFC | v1.x'ten |
| 17 | `time` | MFC | v1.x'ten |
| 18 | `timestamp` | MFC | v1.x'ten |
| 19 | `hidden` | SFC | v1.x'ten |
| 20 | `coordinates` | MFC | v1.x'ten |
| 21 | `color` | SFC | v1.x'ten |
| 22 | `toggle` | SFC | 🆕 Voyager II'den |
| 23 | `slug` | MFC | 🆕 Voyager II'den — başka field'dan otomatik üretim |
| 24 | `tags` | MFC | 🆕 Voyager II'den — JSON array, çoklu etiket |
| 25 | `simple_array` | MFC | 🆕 Voyager II'den — düz liste girişi |
| 26 | `repeater` | MFC | 🆕 Voyager II'den — nested form grupları |
| 27 | `slider` | SFC | 🆕 Voyager II'den — range input |

### HandlerInterface

```php
// src/FormFields/HandlerInterface.php
interface HandlerInterface
{
    public static function getCodename(): string;
    public static function getContentBasedOnValue(mixed $value, DataRow $row): mixed;
    public function createContent(mixed $value, DataType $dataType, DataRow $row, bool $first): View;
    public function editContent(mixed $value, DataType $dataType, DataRow $row): View;
    public function browseContent(mixed $value, DataType $dataType, DataRow $row): View;
    public function readContent(mixed $value, DataType $dataType, DataRow $row): View;
    public function delete(mixed $value, DataType $dataType, DataRow $row): void;
    public static function canBeOrdered(): bool;
}
```

---

## 11. Yeni v3.0 Özellikleri

### 11.1 Activity Log

Her admin aksiyonunu otomatik loglar.

**Tablo:**
```php
Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('model_type');
    $table->unsignedBigInteger('model_id')->nullable();
    $table->string('action');           // created, updated, deleted, restored
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->string('url')->nullable();  // hangi admin sayfasından tetiklendi
    $table->string('ip', 45)->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamps();
    $table->index(['model_type', 'model_id']);
    $table->index('user_id');
    $table->index('action');
    $table->index('created_at');        // tarih filtresi için
});
```

**API:**
```php
// BREAD event'lerinden otomatik tetiklenir
// Plugin'ler de loglayabilir:
ActivityLogger::log($post, 'published', $post->toArray());
```

**Bileşen:** `components::activity-log` (MFC) — filter, search, pagination, detail modal.

### 11.2 Cache Manager

**Bileşen:** `components::cache-manager` (SFC)  
Desteklenen temizleme hedefleri: config, route, view, event, app.

### 11.3 Maintenance Mode

**Bileşen:** `components::maintenance-mode` (SFC)  
Laravel 13 `php artisan down/up` ile Livewire entegrasyonu. Opsiyonel mesaj + IP beyaz listesi.

### 11.4 Queue Manager (Failed Jobs)

**Bileşen:** `components::queue-manager` (MFC)  
Laravel built-in `failed_jobs` tablosunu okur. Retry, forget, bulk forget.

### 11.5 User Impersonation

**Bileşen:** `components::impersonation` (SFC)  
`Auth::onceUsingId()` ile. Navbar'da "Exit impersonation" bildirimi.

### 11.6 Upgrade Wizard (v2 → v3)

**Bileşen:** `components::upgrade-wizard` (MFC)  
`php artisan voyager:upgrade` CLI + web UI.

Adımlar:
1. Mevcut v2 config yedeği
2. Veritabanı schema kontrolü
3. Yeni migration'ları çalıştır (`plugins`, `themes`, `activity_logs`)
4. `data_types.model_name` namespace güncelle (`TCG\Voyager` → `YellowThree\Voyager`)
5. `php artisan voyager:export-breads` — DB → JSON
6. `app/FormFields/` tarama → shim raporu
7. `Widget::run()` kullanımları → tarama raporu
8. `@extends('voyager::master')` → tarama raporu

---

## 12. First-Party Plugin'ler

### 12.1 Menu Builder Plugin (`yellow-three/voyager-menu`)

Menu Builder v1.x'te core'daydı, v3'te ayrı plugin olarak taşındı. **Upgrade wizard bu bağımlılığı otomatik tespit eder ve `composer require` önerir.**

```
plugins/menu/
├── MenuServiceProvider.php
├── MenuPlugin.php              (BasePlugin + MenuPlugin + FormfieldPlugin)
├── composer.json               (keywords: voyager-plugin)
├── resources/views/components/
│   ├── ⚡menu-builder/          (MFC)
│   └── ⚡menu-list.blade.php    (SFC)
├── src/
│   ├── Models/Menu.php
│   ├── Models/MenuItem.php
│   ├── Http/Controllers/MenuController.php  (API korunuyor)
│   ├── migrations/
│   └── seeders/
└── routes/menu.php
```

### 12.2 Blog Plugin (`yellow-three/voyager-blog`)

Posts, Pages, Categories v1.x'te core'daydı, v3'te ayrı plugin.

```
plugins/blog/
├── BlogServiceProvider.php
├── BlogPlugin.php              (BasePlugin + FormfieldPlugin + MenuPlugin)
├── composer.json               (keywords: voyager-plugin)
├── resources/views/components/
│   ├── ⚡post-list.blade.php, ⚡post-form.blade.php
│   ├── ⚡page-list.blade.php, ⚡page-form.blade.php
│   └── ⚡category-list.blade.php, ⚡category-form.blade.php
├── resources/views/formfields/
│   └── blog-category-select.blade.php
├── src/
│   ├── Models/ (Post, Page, Category)
│   ├── FormFields/CategorySelectHandler.php
│   ├── migrations/
│   └── seeders/
└── routes/blog.php
```

---

## 13. v2 → v3 Geçiş Stratejisi

### 13.1 Upgrade Komutu

```bash
php artisan voyager:upgrade
```

Yaptıkları (sırayla):
1. `config/voyager.php` yedekle
2. DB schema kontrolü (eski migration'lar uyumlu mu?)
3. Yeni migration'ları çalıştır: `plugins`, `themes`, `activity_logs`
4. `data_types.model_name` güncelle: `TCG\Voyager\` → `YellowThree\Voyager\`
5. `php artisan voyager:export-breads` çalıştır (DB → JSON)
6. `app/FormFields/` dizini tara → varsa deprecation raporu
7. `Widget::run()` kullanımlarını tara → rapor
8. `@extends('voyager::master')` kullanımlarını tara → rapor
9. `menu`, `posts`, `pages`, `categories` tablolarını kontrol et → plugin önerisi
10. Seeder'ları çalıştır

### 13.2 Backward Compatibility Shim'leri

```php
// src/BackwardCompatibility/FormFieldShim.php
// VoyagerServiceProvider::boot() içinde çalışır:
if (is_dir(app_path('FormFields'))) {
    foreach (glob(app_path('FormFields/*.php')) as $file) {
        $class = 'App\\FormFields\\'.basename($file, '.php');
        Voyager::registerFormField($class);
        trigger_error(
            "app/FormFields/ auto-discovery deprecated in v3. Use plugin system instead.",
            E_USER_DEPRECATED
        );
    }
}
```

```php
// src/BackwardCompatibility/WidgetShim.php
class Widget
{
    public static function run(string $widget, array $params = []): string
    {
        trigger_error(
            "Widget::run() is deprecated. Use @livewire() instead.",
            E_USER_DEPRECATED
        );
        return '';
    }
}
```

### 13.3 Breaking Changes Tablosu

| Değişiklik | Etki | Çözüm |
|---|---|---|
| `TCG\Voyager` → `YellowThree\Voyager` | Tüm import'lar kırılır | `docs/migration.md` + upgrade komutu |
| `tcg/voyager` → `yellow-three/voyager` | `composer.json` güncellenmeli | `docs/migration.md` |
| Bootstrap 3 → Tailwind 4 | Host HTML class'ları | View'ler package'dan gelir, host etkilenmez |
| jQuery kaldırıldı | `window.$` kullanan kod | Shim + migration rehberi |
| `@extends('voyager::master')` | Host view kalıpları | Layout override mekanizması |
| `arrilot/laravel-widgets` kaldırıldı | `Widget::run()` | Shim + `@livewire()` |
| `app/FormFields/` auto-discovery kaldırıldı | Custom field'lar | Plugin sistemi veya `registerFormField()` + shim |
| Posts/Pages/Categories → blog plugin | Bu modellere bağlı kod | `composer require yellow-three/voyager-blog` |
| Menu Builder → menu plugin | `Menu`, `MenuItem` modelleri | `composer require yellow-three/voyager-menu` |
| `data_types`/`data_rows` → BreadManager | Direkt model erişimi | `app(BreadManager::class)->find($slug)` |
| `voyager_asset()` helper değişiyor | Asset path'leri | Vite helper + upgrade komutu |

### 13.4 Yeni Veritabanı Migration Sırası

```
1. create_plugins_table
2. create_themes_table
3. create_activity_logs_table
4. (blog plugin) create_posts_table
5. (blog plugin) create_categories_table
6. (blog plugin) create_category_post_table
```

---

## 14. Branch Stratejisi

```
1.7   → dondurulmuş, sadece tarihsel referans (değişiklik yapılmaz)
3.x   → aktif geliştirme (default branch)
main  → stabil release (3.0.0 çıkınca 3.x → main merge)
```

- Tüm kodlama `3.x` branch'inde
- `3.x` açıldıktan hemen sonra default yapılır
- Hotfix: `3.x`'te açılır, `main`'e cherry-pick
- `main` boş/README-only kalır, ilk release'e kadar

---

## 15. Aşamalar

### Aşama 1a — Proje Altyapısı (BAŞLANGIÇ NOKTASI) [x] YAPILDI

**Hedef:** Pipeline çalışır hale getir. Bir SFC render olana kadar ileriye geçme.

```bash
# 1. composer.json güncelle
# name, namespace, dependencies, extra.laravel.providers

# 2. Namespace migration
find src/ -name '*.php' -exec sed -i 's/namespace TCG\\Voyager/namespace YellowThree\\Voyager/g' {} +
find src/ -name '*.php' -exec sed -i 's/use TCG\\Voyager/use YellowThree\\Voyager/g' {} +
find tests/ -name '*.php' -exec sed -i 's/TCG\\Voyager/YellowThree\\Voyager/g' {} +
sed -i 's/TCG\\Voyager\\Models/YellowThree\\Voyager\\Models/g' publishable/config/voyager.php

# 3. PHPUnit → Pest
./vendor/bin/pest --migrate

# 4. Upstream remote (sadece referans)
git remote add upstream https://github.com/thedevdojo/voyager
git remote set-url --push upstream DISABLED

# 5. 3.x branch'i oluştur
git checkout -b 3.x
```

`composer.json` hedef hali:
```json
{
    "name": "yellow-three/voyager",
    "description": "The Missing Laravel Admin — Community Fork (v3)",
    "keywords": ["laravel", "admin", "bread", "voyager"],
    "require": {
        "php": "^8.3|^8.4|^8.5",
        "laravel/framework": "^13.0",
        "livewire/livewire": "^4.0",
        "intervention/image": "^3.0"
    },
    "require-dev": {
        "orchestra/testbench": "^11.0",
        "pestphp/pest": "^3.0",
        "pestphp/pest-plugin-livewire": "^3.0",
        "pestphp/pest-plugin-laravel": "^3.0"
    },
    "autoload": {
        "psr-4": {
            "YellowThree\\Voyager\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": ["YellowThree\\Voyager\\VoyagerServiceProvider"]
        }
    }
}
```

`VoyagerServiceProvider` zorunlu içerik:
```php
public function register(): void
{
    // KRITIK: Package SFC/MFC'leri için zorunlu
    Livewire::addComponentPath(
        namespace: 'YellowThree\\Voyager',
        path: __DIR__.'/../resources/views/components',
    );

    // BreadManager singleton
    $this->app->singleton(BreadManager::class, fn() => new BreadManager(
        json: new JsonBreadSource(storage_path('voyager/breads')),
        database: new DatabaseBreadSource(),
    ));
    $this->app->alias(BreadManager::class, 'voyager.bread');

    // PluginManager singleton
    $this->app->singleton(PluginManager::class);
}

public function boot(): void
{
    $this->loadViewsFrom(__DIR__.'/../resources/views', 'voyager');
    $this->loadMigrationsFrom(__DIR__.'/../migrations');
    $this->loadRoutesFrom(__DIR__.'/../routes/voyager.php');
    $this->loadTranslationsFrom(__DIR__.'/../publishable/lang', 'voyager');

    // VoyagerCorePlugin kaydet
    $this->app[PluginManager::class]->register(new VoyagerCorePlugin());

    // Backward compat shim'leri
    $this->bootFormFieldShim();
    $this->bootWidgetShim();

    // Publishable'lar
    $this->publishes([
        __DIR__.'/../publishable/config/voyager.php' => config_path('voyager.php'),
    ], 'voyager-config');

    $this->publishes([
        __DIR__.'/../publishable/assets/build' => public_path('vendor/voyager'),
    ], 'voyager-assets');
}
```

**Aşama 1a tamamlandı kriteri:** `./vendor/bin/pest` yeşil, basit bir SFC (`components::compass`) render oluyor.

### Aşama 1b — src/ Klasör Yapısı [x] YAPILDI

Flat `src/` içinde klasörleri oluştur ve dosyaları taşı (`git mv` ile — history korunsun):

```
src/Bread/      src/Models/     src/FormFields/
src/Plugins/    src/Themes/     src/Http/
src/ActivityLog/ src/Upgrade/   src/BackwardCompatibility/
src/Events/     src/Console/
```

### Aşama 2 — Build Sistemi: Mix → Vite [x] YAPILDI

```js
// vite.config.js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        outDir: 'publishable/assets/build',
        manifest: true,
        rollupOptions: {
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: '[name].[ext]',
            },
        },
    },
})
```

`webpack.mix.js`, `mix.js`, `mix-manifest.json` silinir.

### Aşama 3 — CSS: Bootstrap 3 → Tailwind 4 [x] YAPILDI

```css
/* resources/css/app.css */
@import "tailwindcss";

@theme {
    --color-primary:      #22A7F0;
    --color-sidebar-bg:   #1e1e2e;
    --color-sidebar-text: #cdd6f4;
    --font-sans:          "Inter", sans-serif;
}

/* Admin layout bileşenleri */
@layer components {
    .voyager-sidebar        { @apply w-64 min-h-screen bg-(--color-sidebar-bg); }
    .voyager-card           { @apply bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6; }
    .voyager-btn-primary    { @apply bg-(--color-primary) text-white rounded-lg px-4 py-2; }
    .voyager-table          { @apply w-full text-sm text-left; }
    .voyager-table th       { @apply px-4 py-3 font-medium text-gray-500 dark:text-gray-400; }
    .voyager-table td       { @apply px-4 py-3 border-t border-gray-100 dark:border-gray-700; }
}
```

Prefix kullanılmıyor. Host app çakışması `@layer` ile önlenir.

### Aşama 4 — JavaScript: jQuery + Vue 2 → Alpine.js [x] YAPILDI

Kaldırılanlar: `jquery`, `vue@2`, `datatables.net`, `select2`, `toastr`, `nestable2`, `@vue/compiler-sfc`

Korunanlar (Alpine wrapper ile):
- **Dropzone** → `x-data="dropzone()"` Alpine component
- **TinyMCE** → Livewire `@entangle` + Alpine init
- **CodeMirror 6** → Livewire `@entangle` + Alpine init
- **CropperJS** → Alpine component

```js
// resources/js/app.js
import Alpine from 'alpinejs'
import { initDropzone } from './alpine/dropzone'
import { initTinyMCE }  from './alpine/tinymce'
import { initCodeMirror } from './alpine/codemirror'

Alpine.data('voyagerDropzone', initDropzone)
Alpine.data('voyagerEditor',   initTinyMCE)
Alpine.data('voyagerCode',     initCodeMirror)

Alpine.start()
```

### Aşama 5 — Core Views → Livewire + Tailwind [x] YAPILDI

```
5a. Layout & partials (admin.blade.php, auth.blade.php, navbar, sidebar, footer) [x] YAPILDI
5b. BREAD Table MFC (bread-table) — dinamik kolonlar, sorting, pagination, bulk actions [x] YAPILDI
5c. BREAD Form MFC (bread-form) + 27 FormField Blade partial [x] YAPILDI
5d. BREAD Read SFC + BREAD Order SFC [x] YAPILDI
5e. Media Manager MFC (Dropzone entegrasyonu) [x] YAPILDI
5f. Dashboard MFC (widget sistemi, plugin widget'ları) [x] YAPILDI
5g. Settings Manager MFC [x] YAPILDI
5h. Database Manager MFC (eski 5 Vue SFC burada çözülür) [x] YAPILDI
5i. Login, Profile, Admin Menu SFC'leri [x] YAPILDI
5j. Role/User list+form SFC'leri, Compass SFC [x] YAPILDI
5k. Activity Log MFC + Cache Manager SFC + Maintenance Mode SFC [x] YAPILDI
5l. Queue Manager MFC + Impersonation SFC [x] YAPILDI
5m. Upgrade Wizard MFC [x] YAPILDI
```

### Aşama 6 — Plugin ve Tema Sistemi

```
6a. BasePlugin abstract + 6 contract interface
6b. PluginManager (register, boot, getFormFields, getAuthPlugin, vb.)
6c. VoyagerCorePlugin (27 field, menü items, widget'lar)
6d. ThemeManager + Theme interface
6e. Plugins Manager MFC + Themes Manager MFC
6f. DB migrations: plugins, themes tabloları
6g. ModelRegistry (plugin modellerini core'a tanıtma)
6h. voyager:make:plugin CLI generator
```

### Aşama 7 — BreadManager Tam Implementasyonu

```
7a. JsonBreadSource (okuma, yazma, backup snapshot)
7b. DatabaseBreadSource (data_types + data_rows → Bread value object)
7c. BreadManager (hibrit resolver, all(), find(), save())
7d. voyager:export-breads komutu
7e. voyager:import-breads komutu (acil durum)
7f. BREAD builder arayüzü (bread-tools SFC güncelleme)
```

### Aşama 8 — First-Party Plugin'ler

```
8a. Menu Plugin (yellow-three/voyager-menu) — Menu, MenuItem, menu-builder MFC
8b. Blog Plugin (yellow-three/voyager-blog) — Post, Page, Category, SFC'ler
8c. Her iki plugin için: routes, migrations, seeders, menü items
8d. Admin sidebar → plugin aktif/pasif durumuna göre menü
```

### Aşama 9 — Backward Compatibility ve Upgrade

```
9a. FormFieldShim (app/FormFields/ deprecation warning + auto-register)
9b. WidgetShim (Widget::run() deprecation)
9c. voyager:upgrade CLI komutu (10 adımlı)
9d. voyager:export-breads komutu (DB → JSON)
9e. UPGRADE.md + CHANGELOG.md
9f. docs/migration.md (TCG\Voyager → YellowThree\Voyager rehberi)
```

### Aşama 10 — Event Sistemi Uyumluluğu

24 event korunuyor, Livewire `dispatch()` ile bağlanıyor:

| Eski | Yeni Karşılığı |
|---|---|
| `BreadDataAdded/Updated/Deleted` | Korunuyor + Activity Log + `dispatch()` |
| `FormFieldsRegistered` | Plugin sistemi ile birleştirildi |
| `SettingUpdated` | Korunuyor + cache flush |
| `MediaFileAdded/FileDeleted` | Korunuyor |
| `Routing*` (4 event) | Korunuyor |
| Diğer 14 event | Korunuyor |

### Aşama 11 — API Controller'lar

UI Livewire'a taşındı, API controller'lar **korunuyor** (dış entegrasyon için):

| Controller | API |
|---|---|
| VoyagerBaseController | CRUD API |
| VoyagerMediaController | Upload API |
| VoyagerSettingsController | Settings API |
| VoyagerDatabaseController | Database API |
| ActivityLogController (YENİ) | Log API |
| UpgradeController (YENİ) | Upgrade API |

### Aşama 12 — Routes ve ServiceProvider Finalizasyonu

- `routes/voyager.php`: Livewire route'ları + API route'ları
- Middleware: `VoyagerAdminMiddleware`, auth guard
- ServiceProvider: tüm bind'lar, publish tag'leri

### Aşama 13 — Publishable Assets

- `npm run build` → `publishable/assets/build/`
- TinyMCE, CodeMirror 6 Alpine wrapper'ları bundle'a dahil
- `publishable/lang/` — 630 dosya korunuyor

### Aşama 14 — Testler ve CI

```
14a. 34 mevcut test Pest formatında (--migrate ile yapıldı)
14b. BreadManager unit testleri (JSON + DB source)
14c. Plugin sistemi testleri (register, boot, getFormFields)
14d. Livewire bileşen testleri (Pest Livewire plugin)
14e. Activity Log testleri
14f. Upgrade komutu testleri
14g. Blog + Menu plugin testleri
14h. Playwright E2E: temel admin akışları (login, BREAD browse/create/edit/delete)
14i. Translation CI validator: voyager:validate-lang
14j. GitHub Actions CI matrix:
```

```yaml
strategy:
  matrix:
    php: [8.3, 8.4, 8.5]
    laravel: [13.*]
    stability: [prefer-lowest, prefer-stable]
```

```
14k. Dependabot: .github/dependabot.yml (composer + npm, weekly)
```

### Aşama 15 — Dokümantasyon ve Repo Kurulum

```
15a. README — fork bildirimi + kurulum + quick start
15b. AGENTS.md — güncel repo map ve kurallar
15c. docs/migration.md — tcg/voyager → yellow-three/voyager rehberi
15d. docs/plugin-development.md — plugin yazma kılavuzu
15e. docs/bread-json.md — JSON BREAD formatı referansı
15f. Packagist kaydı (v3-alpha olarak)
15g. GitHub release: v3.0.0-alpha
```

---

## 16. İş Takvimi

| Aşama | İş | Süre (gün) |
|---|---|---|
| **1a** | Altyapı, namespace, ServiceProvider, pipeline doğrulama | 2 |
| **1b** | src/ klasör yapısı (git mv) | 0.5 |
| **2** | Vite build sistemi | 0.5 |
| **3** | CSS Bootstrap → Tailwind 4 | 4-5 |
| **4** | JS jQuery/Vue → Alpine.js | 3-4 |
| **5a** | Layout ve partials | 1 |
| **5b** | BREAD Table MFC | 7-9 |
| **5c** | BREAD Form MFC + 27 FormField partial | 3-4 |
| **5d** | BREAD Read + Order SFC | 0.5 |
| **5e** | Media Manager MFC | 2-3 |
| **5f** | Dashboard MFC | 1.5 |
| **5g** | Settings Manager MFC | 1.5 |
| **5h** | Database Manager MFC (5 Vue SFC → Livewire) | 2 |
| **5i-5j** | Login, Profile, Menu, Role, User, Compass SFC | 1.5 |
| **5k** | Activity Log MFC + Cache SFC + Maintenance SFC | 2 |
| **5l** | Queue Manager MFC + Impersonation SFC | 1.5 |
| **5m** | Upgrade Wizard MFC | 1.5 |
| **6** | Plugin + Tema sistemi | 8-10 |
| **7** | BreadManager hibrit + JSON source | 3-4 |
| **8** | Menu + Blog plugin | 4-5 |
| **9** | Backward compat + upgrade komutu | 3-4 |
| **10** | Event sistemi uyumu | 1 |
| **11** | API controller'lar | 1.5 |
| **12** | Routes + ServiceProvider finalizasyon | 0.5 |
| **13** | Publishable assets | 1 |
| **14** | Testler + CI + Playwright + Dependabot | 7-9 |
| **15** | Dokümantasyon + Packagist + release | 2 |
| | **Toplam** | **~62-78 gün** |

---

## 17. Riskler ve Dikkat Edilmesi Gerekenler

### Yüksek Öncelikli

| Risk | Etki | Önlem |
|---|---|---|
| Livewire `addComponentPath` unutulursa | Hiçbir SFC çalışmaz | Aşama 1a'da ilk eklenen satır |
| BreadManager karar gecikmesi | Aşama 5b iki kez yazılır | Karar A kesinleşti (hibrit) |
| Namespace migration eksik | Import hataları, sessiz kırılma | `find` + `sed` + grep ile doğrula |
| `src/Core/` wrapper eklenmesi | PSR-4 karmaşası, gereksiz iş | Kesinlikle flat kalacak (Karar B) |
| Plugin interface şişirilmesi | Her plugin 9 metod implement eder | BasePlugin abstract + granüler contracts (Karar C) |

### Orta Öncelikli

| Risk | Önlem |
|---|---|
| TinyMCE/CodeMirror Alpine wrapper | Livewire `@entangle` + Alpine init pattern ile çözülür |
| Tailwind host app çakışması | `@layer` ile izolasyon, prefix kullanılmıyor |
| JSON BREAD eşzamanlı yazma | File lock veya queue ile çözülür |
| Menu Plugin ayrılması kullanıcıyı kırar | Upgrade wizard `composer require` önerir |
| Activity log performansı | Index'ler tanımlandı, periyodik temizlik komutu |

### Düşük Öncelikli

| Risk | Önlem |
|---|---|
| `arrilot/laravel-widgets` kaldırma | `Widget::run()` shim ile deprecation |
| Flysystem versiyon uyumu | `intervention/image: ^3.0` ile çözüldü |
| Laravel 14 hazırlığı | `^13.0\|^14.0` olarak genişletilebilir |

---

## 18. v3.1+ Yol Haritası

| Özellik | Süre | Açıklama |
|---|---|---|
| **Çoklu Layout** | 3-4 gün | JSON yapısı hazır, UI eklenir (Voyager II'den ilham) |
| **Eloquent Scope per Layout** | 2 gün | BREAD bazlı otomatik scope, multi-tenant için kritik |
| **Computed Properties** | 2 gün | Accessor/mutator desteği (Voyager II'den) |
| **Gelişmiş Actions** | 3 gün | bulk, download, soft-delete aware (Voyager II'den) |
| **Translatable Trait v2** | 2 gün | JSON sütun tabanlı, ayrı join query yok (Voyager II'den) |
| **Plugin Filter Sistemi** | 3 gün | Layout/Menu/Widget/Media filtresi (Voyager II'den) |
| **Backup & Restore** | 3-4 gün | DB + dosya yedekleme UI |
| **API Token Management** | 2 gün | Sanctum token oluşturma/iptal UI |
| **Two-Factor Authentication** | 3 gün | Admin login için 2FA |
| **Import / Export** | 3-4 gün | BREAD data için CSV/Excel |
| **Log Viewer** | 2 gün | `storage/logs/laravel.log` okuyucu |
| **Dark Mode** | 1 gün | Tema sistemine dark mode toggle |
| **Translation Management UI** | 3 gün | 630 dil dosyasını UI'dan yönetme |
| **Report Builder** | 4-5 gün | Dashboard chart builder |
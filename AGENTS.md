# Voyager Agent Protocol

> **Son Güncelleme:** Mayıs 2025  
> Voyager II analizi, mimari kararlar ve pipeline düzeltmeleri eklendi.  
> Referans: `PLAN.md` — tüm kararların gerekçesi orada.

---

## Başlangıç Protokolu

Her yeni oturumda, kodlamaya **BAŞLAMADAN ÖNCE**:

1. `mempalace search "[görev konusu]"` — geçmişi ve bağlamı hatırla
2. `graphify query "[görev konusu]"` — proje grafiğini sorgula
3. `cat PLAN.md` veya ilgili bölümü oku — mevcut planı kontrol et
4. Onay al, sonra kodlamaya başla

---

## Proje Kütüphanesi

| Alan | Değer |
|---|---|
| **GitHub** | `yellow-three/voyager` |
| **Upstream (arşivlenmiş fork kaynağı)** | `thedevdojo/voyager` (Şubat 2025'te arşivlendi — sadece tarihsel referans) |
| **Packagist** | `yellow-three/voyager` |
| **PHP Namespace** | `YellowThree\Voyager` |
| Tip | Laravel 13 paketi (uygulama değil) |
| PHP | ^8.3\|^8.4\|^8.5 |
| Laravel | ^13.0 |
| Livewire | ^4.0 (SFC default, MFC karmaşık) |
| CSS | Tailwind CSS 4 (CSS-first, `tailwind.config.js` yok) |
| Build | Vite (`@tailwindcss/vite` plugin) |
| JS | Alpine.js, Dropzone, TinyMCE, CodeMirror 6 |
| Test | Pest 3 + orchestra/testbench ^11.0 |
| CI/CD | GitHub Actions |
| E2E | Playwright |

---

## Kesinleşmiş Mimari Kararlar

Bu kararlar **tartışmaya kapalıdır.** Detaylı gerekçe için `PLAN.md §3`.

### Karar A — Hibrit BreadManager

JSON önce (`storage/voyager/breads/{slug}.json`), yoksa `data_types` DB tablosu. JSON her zaman önceliklidir. Upgrade path: `php artisan voyager:export-breads`.

### Karar B — `src/` flat kalır

`src/Core/` wrapper **yok.** Flat `src/` altında anlamlı klasörler:

```
src/Bread/  src/Models/  src/FormFields/  src/Plugins/
src/Themes/ src/Http/    src/Events/      src/Console/
src/ActivityLog/  src/Upgrade/  src/BackwardCompatibility/
```

### Karar C — BasePlugin abstract + granüler contracts

Tek şişirilmiş interface değil. Plugin sadece ihtiyacı olan contract'ı implement eder:

```
FormfieldPlugin  MenuPlugin  WidgetPlugin
AuthenticationPlugin  AuthorizationPlugin  FilterPlugin
```

### Karar D — Livewire Package Path (KRİTİK)

Package SFC/MFC'leri otomatik keşfedilmez. `VoyagerServiceProvider::register()` içinde **zorunlu:**

```php
Livewire::addComponentPath(
    namespace: 'YellowThree\\Voyager',
    path: __DIR__.'/../resources/views/components',
);
```

Bu satır olmadan hiçbir Livewire bileşeni render olmaz.

### Karar E — Plugin Keşfi

`extra.laravel.providers` kullanılır, custom parser yok. Plugin `composer.json`'ında `keywords: ["voyager-plugin"]` da taşımalı.

---

## Mimari Kurallar

### Livewire 4 Bileşenleri

| Tip | Komut | Ne zaman |
|---|---|---|
| SFC | `php artisan make:livewire foo --sfc` | Varsayılan. < ~80 satır, az state, JS gerektirmez |
| MFC | `php artisan make:livewire foo --mfc` | Karmaşık state, JS entegrasyonu, pagination |
| Class-based | **Kullanma** | SFC + MFC her zaman yeterli |

### Karar Tablosu (Özet)

| Bileşen | Tip |
|---|---|
| bread-table | **MFC** — dinamik kolonlar, pagination, bulk actions |
| bread-form | **MFC** — dinamik field rendering, validation |
| media-manager | **MFC** — Dropzone + upload + galeri |
| dashboard | **MFC** — widget sistemi, plugin widget'ları |
| settings-manager | **MFC** — grup ayarlar, dinamik tip rendering |
| database-manager | **MFC** — schema okuma, 5 Vue SFC burada çözülür |
| activity-log | **MFC** — filter, search, pagination |
| queue-manager | **MFC** — failed jobs, retry, pagination |
| upgrade-wizard | **MFC** — adım adım async migration UI |
| plugins-manager | **MFC** — install, aktif/pasif |
| themes-manager | **MFC** — preview, aktifleştirme |
| menu-builder *(plugin)* | **MFC** — drag-drop nested |
| bread-read, bread-order | SFC |
| bread-tools | SFC |
| cache-manager, maintenance-mode, impersonation | SFC |
| admin-menu, role-*, user-*, login, profile, compass | SFC |
| **FormField view'leri** | **Blade partial** — SFC değil, extensible |

### Namespace Kuralları

```
components::bread-table    → resources/views/components/⚡bread-table.blade.php
pages::dashboard           → resources/views/pages/⚡dashboard.blade.php
layouts::admin             → resources/views/layouts/⚡admin.blade.php
```

PHP namespace: `YellowThree\Voyager\...` — eski `TCG\Voyager` **kullanılmıyor.**

### Livewire Kayıt

SFC/MFC için `Livewire::addComponentPath()` yeterli (Karar D). Class-based varsa:

```php
// Sadece class-based için gerekli (kullanılmıyor)
Livewire::addComponent(name: 'foo', class: Foo::class);
```

### CSS — Tailwind 4

`tailwind.config.js` yok. Tema `@theme` ile `resources/css/app.css`'de:

```css
@import "tailwindcss";

@theme {
    --color-primary:    #22A7F0;
    --color-sidebar-bg: #1e1e2e;
    --font-sans:        "Inter", sans-serif;
}
```

Prefix kullanılmıyor. Host app çakışması `@layer` ile engellenir.

### FormField Sistemi

- Built-in 27 field → `VoyagerCorePlugin::formFields()` ile kaydedilir
- `app/FormFields/` auto-discovery **kaldırıldı** — shim uyarı verir, v3.0'da çalışır
- Kullanıcı: plugin yazar **veya** `Voyager::registerFormField(Handler::class)` çağırır
- View'ler Blade partial (`resources/views/formfields/`) — SFC değil

### Controller'lar

UI Livewire'a taşındı. API endpoint'leri controller olarak **kalır** (dış entegrasyon için).

---

## Proje Haritası (Repository Map)

```
voyager/
├── src/
│   ├── Bread/
│   │   ├── BreadManager.php            # Hibrit resolver (JSON önce, DB fallback)
│   │   ├── Bread.php                   # Value object
│   │   ├── BreadLayout.php             # Layout value object (v3.1 çoklu layout için hazır)
│   │   ├── Sources/
│   │   │   ├── JsonBreadSource.php     # storage/voyager/breads/*.json
│   │   │   └── DatabaseBreadSource.php # data_types + data_rows (backward compat)
│   │   └── Concerns/HasBread.php       # Model trait
│   ├── Models/                         # DataType*, DataRow*, Setting, Permission, Role, User
│   ├── Http/
│   │   └── Controllers/               # API controller'lar (UI değil)
│   ├── FormFields/                     # HandlerInterface + 27 handler
│   ├── Plugins/
│   │   ├── Contracts/
│   │   │   ├── FormfieldPlugin.php
│   │   │   ├── MenuPlugin.php
│   │   │   ├── WidgetPlugin.php
│   │   │   ├── AuthenticationPlugin.php
│   │   │   ├── AuthorizationPlugin.php
│   │   │   └── FilterPlugin.php
│   │   ├── BasePlugin.php             # Abstract — tüm metodlara boş default
│   │   ├── PluginManager.php
│   │   ├── ModelRegistry.php
│   │   └── VoyagerCorePlugin.php      # 27 field, menü, widget'lar
│   ├── Themes/
│   │   ├── Contracts/Theme.php
│   │   └── ThemeManager.php
│   ├── ActivityLog/
│   ├── Upgrade/
│   │   └── Steps/                     # Her adım ayrı class
│   ├── BackwardCompatibility/
│   │   ├── FormFieldShim.php          # app/FormFields/ deprecation + auto-register
│   │   └── WidgetShim.php             # Widget::run() deprecation
│   ├── Events/                        # 24 event (korunuyor)
│   ├── Console/
│   │   ├── InstallCommand.php
│   │   ├── UpgradeCommand.php
│   │   ├── ExportBreadsCommand.php    # DB → JSON
│   │   ├── ImportBreadsCommand.php    # JSON → DB (acil durum)
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
│   │   │   ├── ⚡activity-log/                 (MFC)
│   │   │   ├── ⚡cache-manager.blade.php        (SFC)
│   │   │   ├── ⚡maintenance-mode.blade.php     (SFC)
│   │   │   ├── ⚡queue-manager/                (MFC)
│   │   │   ├── ⚡impersonation.blade.php        (SFC)
│   │   │   ├── ⚡upgrade-wizard/               (MFC)
│   │   │   ├── ⚡plugins-manager/              (MFC)
│   │   │   ├── ⚡themes-manager/               (MFC)
│   │   │   ├── ⚡admin-menu.blade.php           (SFC)
│   │   │   ├── ⚡role-list.blade.php            (SFC)
│   │   │   ├── ⚡role-form.blade.php            (SFC)
│   │   │   ├── ⚡user-list.blade.php            (SFC)
│   │   │   ├── ⚡user-form.blade.php            (SFC)
│   │   │   └── ⚡compass.blade.php              (SFC)
│   │   ├── formfields/                # Blade partial — 27 field handler ile eşleşir
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
│   ├── assets/build/                 # Vite çıktısı — DÜZENLEME
│   ├── config/voyager.php
│   └── lang/                         # 630 dil dosyası — DÜZENLEME
│
├── storage/voyager/breads/           # JSON BREAD tanımları (runtime)
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

> *`DataType`/`DataRow` modelleri korunuyor — `DatabaseBreadSource` için gerekli.

---

## Plugin Generator

```bash
./vendor/bin/testbench voyager:make:plugin {name}

# Örnek:
./vendor/bin/testbench voyager:make:plugin blog
# → plugins/blog/ iskeletini oluşturur:
#   BlogPlugin.php (BasePlugin extend, gerekli contracts)
#   BlogServiceProvider.php (extra.laravel.providers ile keşfedilir)
#   composer.json (keywords: voyager-plugin)
#   routes/, src/Models/, src/FormFields/, migrations/, resources/views/components/
```

---

## Branch Stratejisi

```
1.7  → dondurulmuş, sadece tarihsel referans (değişiklik yapılmaz)
3.x  → aktif geliştirme (default branch)
main → stabil release (3.0.0 çıkınca 3.x → main merge)
```

Tüm kodlama `3.x` branch'inde yapılır. `1.7` üzerinde değişiklik yapılmaz.

---

## Repo Kurulumu

- **Packagist**: `yellow-three/voyager` — `packagist.org/packages/yellow-three/voyager`
- **Plugin'ler**: `yellow-three/voyager-blog`, `yellow-three/voyager-menu`
- **README**: Fork bildirimi başlıkta yer alıyor mu?
- **Default branch**: `3.x` açılır açılmaz default yapılır

---

## Validation Checklist (Görev Tamamlama)

- [ ] Pint çalışıyor (`./vendor/bin/pint`)
- [ ] Pest testler geçiyor (`./vendor/bin/pest`)
- [ ] Namespace `YellowThree\Voyager` — eski `TCG\Voyager` **yok**
- [ ] Yeni Livewire bileşen SFC/MFC kurallarına uygun
- [ ] Yeni plugin `BasePlugin` extend ediyor, sadece ihtiyacı olan contract'ı implement ediyor
- [ ] `Livewire::addComponentPath()` `VoyagerServiceProvider::register()`'da mevcut
- [ ] `BreadManager` singleton `VoyagerServiceProvider::register()`'da kayıtlı
- [ ] Dil dosyası anahtarları (varsa) eklendi
- [ ] Playwright E2E (varsa) geçiyor
- [ ] Vite build hatasız (`npm run build`)
- [ ] `src/Core/` klasörü **yok** (flat `src/` korunuyor)

---

## "Do Not Edit" Listesi

- `vendor/`, `node_modules/`, `build/` — harici bağımlılıklar
- `publishable/assets/build/` — Vite build çıktısı, kaynak değil
- `publishable/lang/` — 630 dil dosyası, sadece yeni anahtar eklenir
- `.env`, `.env.example` — host uygulama için
- `composer.lock`, `package-lock.json` — lock dosyaları
- `1.7` branch — dondurulmuş, değişiklik yapılmaz

---

## gh CLI Komutları

### Issue

```bash
gh issue create --title "[title]" --body "[description]" --label "bug,enhancement"
gh issue list --assignee @me --state open
gh issue view <number>
gh issue close <number>
```

### Pull Request

```bash
gh pr create --title "[title]" --body "[summary]" --label "migration"
gh pr list --state open --assignee @me
gh pr checkout <number>
gh pr review <number> --approve
gh pr merge <number> --squash
gh pr view <number>
gh pr diff <number>
```

### CI/CD

```bash
gh run list --limit 5
gh run view <id> --log
gh run watch <id>
gh workflow list
gh workflow run <name>
```

### Release

```bash
gh release create v3.0.0-alpha --title "v3.0.0-alpha" \
  --notes "Community fork: Laravel 13 + Livewire 4 + Tailwind 4 + Vite (yellow-three/voyager)"
gh release list
gh release view v3.0.0-alpha
```

### Repo

```bash
gh repo view yellow-three/voyager
gh repo set-default yellow-three/voyager
```

### API (GitHub API)

```bash
gh api repos/yellow-three/voyager/issues
gh api repos/yellow-three/voyager/pulls/123/comments
```

---

## Git Remote Kurulumu

Bu proje `thedevdojo/voyager@1.7`'nin fork'udur. Remote'lar:

```bash
# Fork (çalışılan repo)
git remote -v
# origin  https://github.com/yellow-three/voyager.git (fetch)
# origin  https://github.com/yellow-three/voyager.git (push)

# Upstream (arşivlenmiş kaynak — sadece referans, PR/merge yok)
git remote add upstream https://github.com/thedevdojo/voyager
git remote set-url --push upstream DISABLED

# Upstream'den sadece okuma (gerekirse)
git fetch upstream
git log upstream/1.7..HEAD --oneline
```

> **Not:** Upstream Şubat 2025'te arşivlenmiş durumda (read-only). Sadece tarihsel karşılaştırma için kullanılır, merge yapılmaz.

---

## Testbench Komutları

Bu proje bir **Laravel paketidir**, uygulama değil. `php artisan` komutları doğrudan çalıştırılmaz, `orchestra/testbench ^11.0` üzerinden çalışır:

```bash
# Artisan komutlarını testbench ile çalıştır
./vendor/bin/testbench [artisan-komutu]

# Örnekler:
./vendor/bin/testbench voyager:install
./vendor/bin/testbench voyager:upgrade
./vendor/bin/testbench voyager:export-breads
./vendor/bin/testbench voyager:make:plugin my-plugin
./vendor/bin/testbench migrate
./vendor/bin/testbench db:seed --class=VoyagerDatabaseSeeder

# Test suite (Pest)
./vendor/bin/pest
./vendor/bin/pest --filter=BreadTableTest
./vendor/bin/pest --filter=BreadManagerTest
./vendor/bin/pest --filter=PluginSystemTest

# PHPUnit'den Pest'e (ilk seferinde)
./vendor/bin/pest --migrate

# Pint (kod stili)
./vendor/bin/pint

# Vite build
npm run build

# Playwright E2E
npx playwright test
npx playwright test tests/E2E/

# Translation CI validator
./vendor/bin/testbench voyager:validate-lang
```

---

## Başlangıç Sırası (İlk Oturum)

Henüz Aşama 1a'daysan bu sırayı takip et:

```bash
# 1. composer.json güncelle (name, namespace, dependencies, extra.laravel.providers)

# 2. Namespace migration
find src/ -name '*.php' -exec sed -i 's/namespace TCG\\Voyager/namespace YellowThree\\Voyager/g' {} +
find src/ -name '*.php' -exec sed -i 's/use TCG\\Voyager/use YellowThree\\Voyager/g' {} +
find tests/ -name '*.php' -exec sed -i 's/TCG\\Voyager/YellowThree\\Voyager/g' {} +
sed -i 's/TCG\\Voyager/YellowThree\\Voyager/g' publishable/config/voyager.php

# 3. Doğrula — eski namespace kalmamalı
grep -r 'TCG\\Voyager' src/ tests/ publishable/config/

# 4. PHPUnit → Pest
./vendor/bin/pest --migrate

# 5. 3.x branch
git checkout -b 3.x

# 6. VoyagerServiceProvider'a ekle (Karar D):
#    Livewire::addComponentPath(namespace: ..., path: ...)
#    BreadManager singleton
#    PluginManager singleton

# 7. Basit SFC test: compass bileşeni render oluyor mu?
#    → Evet: Aşama 1a tamamlandı, 1b'ye geç
#    → Hayır: addComponentPath'i kontrol et
```

---

## Commit Mesaj Formatı

```
feat(scope): kısa açıklama     # Yeni özellik
refactor(scope): kısa açıklama # Kod iyileştirme
fix(scope): kısa açıklama      # Hata düzeltme
docs(scope): kısa açıklama     # Dokümantasyon
wip(scope): kısa açıklama      # Devam eden iş
```

**Scope örnekleri:**

```
bread-table     bread-form      bread-manager   bread-json
media-manager   plugin-system   theme-system    formfields
activity-log    queue-manager   cache-manager   upgrade-wizard
impersonation   deps            vite            tailwind
tests           e2e             ci              docs
namespace       bread-tools     blog-plugin     menu-plugin
backward-compat events          api             routes
```

---

## Bitiş Protokolu

Bir görevi tamamlayınca:

### 1. Hafıza & Grafik Güncelle

```bash
mempalace mine . --mode convos
graphify extract . --update
```

### 2. Commit & Push

```bash
# Durum kontrolü
git status

# Dosyaları ekle (sadece ilgili dosyalar, "git add ." kullanma!)
git add <path/to/file>

# Commit
git commit -m "feat(scope): kısa açıklama"

# Push
git push origin HEAD
```

### 3. Kullanıcıyı Bilgilendir

Commit mesajı ve push durumunu söyle.

---

## NOTLAR

1. Bu proje bir **Laravel paketidir**, uygulama değil. `orchestra/testbench ^11.0` ile test edilir. Test framework'ü **Pest 3**'tür.
2. `php artisan` komutları doğrudan değil, `./vendor/bin/testbench [komut]` ile çalıştırılır.
3. `laravel/boost` kullanılmıyor (paket olduğu için anlamsız).
4. `npm run build` ile Vite assets derlenir, `publishable/assets/build/`'e çıkar.
5. Playwright E2E testleri `tests/E2E/` altında, `npx playwright test` ile çalıştırılır.
6. Translation CI validator: `./vendor/bin/testbench voyager:validate-lang` komutuyla tüm dillerde eksik anahtar kontrolü.
7. PHP Namespace **`YellowThree\Voyager`** — eski `TCG\Voyager` namespace'i kesinlikle kullanılmıyor.
8. Packagist paket adı **`yellow-three/voyager`** — plugin'ler için `yellow-three/voyager-blog`, `yellow-three/voyager-menu`.
9. Upstream `thedevdojo/voyager` Şubat 2025'te arşivlenmiştir. Sadece tarihsel referans amaçlıdır.
10. **`src/Core/` klasörü yoktur** — flat `src/` yapısı kullanılır (Karar B).
11. **`Livewire::addComponentPath()`** `VoyagerServiceProvider::register()` içinde zorunludur (Karar D). Eksikse hiçbir SFC çalışmaz.
12. **BreadManager** singleton olarak kaydedilir. BREAD'e erişim her zaman `app(BreadManager::class)` veya `app('voyager.bread')` üzerinden.
13. **Plugin** yazarken `BasePlugin` extend et, sadece ihtiyacın olan contract'ı implement et. 9 metod implement etmek zorunda değilsin.
14. **FormField view'leri** Blade partial'dır, SFC değildir. `resources/views/formfields/` altında.
15. **JSON BREAD backup'ları** otomatik alınır: `storage/voyager/breads/users.backup.{timestamp}.json`.

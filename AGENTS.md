# Voyager Agent Protocol

## Baslangic Protokolu

Her yeni oturumda, kodlamaya BASLAMADAN ONCE:

1. `mempalace search "[gorev konusu]"` — gecmisi ve baglami hatirla
2. `graphify query "[gorev konusu]"` — proje grafigini sorgula
3. `cat PLAN.md` veya ilgili bolumu oku — mevcut plani kontrol et
4. Onay al, sonra kodlamaya basla

---

## Proje Kutuphanesi

| Alan | Deger |
|---|---|
| **GitHub** | `yellow-three/voyager` |
| **Upstream (arşivlenmiş fork kaynağı)** | `thedevdojo/voyager` (Feb 2025'te arşivlendi) |
| **Packagist** | `yellow-three/voyager` |
| **PHP Namespace** | `YellowThree\Voyager` |
| Tip | Laravel 13 paketi (uygulama degil) |
| PHP | ^8.3\|^8.4\|^8.5 |
| Laravel | ^13.0 |
| Livewire | ^4.0 (SFC default, MFC karmasik) |
| CSS | Tailwind CSS 4 |
| Build | Vite |
| JS | Alpine.js, Dropzone, TinyMCE, CodeMirror 6 |
| Test | Pest 3 + orchestra/testbench ^11.0 |
| CI/CD | GitHub Actions |

---

## Mimari Kurallar

### Livewire 4 Bilesenleri

| Tip | Komut | Ne zaman |
|---|---|---|
| SFC | `php artisan make:livewire foo --sfc` | Varsayilan. Kucuk/orta bilesenler |
| MFC | `php artisan make:livewire foo --mfc` | Karmasik state, JS entegrasyonu |
| Class-based | Kullanma | SFC + MFC yeterli |

### Namespace'ler

```
components::bread-table    → resources/views/components/⚡bread-table.blade.php
pages::dashboard           → resources/views/pages/⚡dashboard.blade.php
layouts::admin             → resources/views/layouts/⚡admin.blade.php
```

PHP namespace: `YellowThree\Voyager\...` (eski `TCG\Voyager` artık kullanılmıyor)

### Kayit

SFC/MFC otomatik kesfedilir, namespace yeterli. Class-based olsaydi manuel kayit gerekirdi:

```php
// Sadece class-based icin gerekli, SFC/MFC'de gerekmez
Livewire::addComponent(name: 'foo', class: Foo::class);
```

### CSS

Tailwind 4. `tailwind.config.js` yok. Tema `@theme` ile `app.css`'de tanimli.

```css
@import "tailwindcss";
@theme { --color-primary: #22A7F0; ... }
```

Prefix kullanilmiyor. Host app ile cakisma Tailwind 4 `@layer` ile engellenir.

### FormField Sistemi

- Built-in field'lar `VoyagerCorePlugin` ile kaydedilir (plugin sistemi uzerinden)
- `app/FormFields/` auto-discovery KALKMISTIR
- HandlerInterface + handler class'lari aynen durur
- Kullanici plugin yazarak veya `Voyager::registerFormField()` ile field ekler
- View'ler Blade partial'tir (SFC degil)

### Controller'lar

UI Livewire'a tasinir. API endpoint'leri controller olarak KALIR (dis entegrasyonlar icin).

---

## Proje Haritasi (Repository Map)

```
voyager/
├── src/
│   ├── Core/                          # Çekirdek: BREAD, Media, Settings, Users, Roles
│   │   ├── Models/                    # DataType, DataRow, Permission, Role, User, Setting, Translation
│   │   ├── Http/Controllers/          # API controller'lar (UI degil, dis entegrasyon icin)
│   │   ├── FormFields/               # Extensible field handler'lar (HandlerInterface)
│   │   ├── Plugins/                   # Plugin sistemi (PluginManager, BasePlugin, VoyagerPlugin)
│   │   │   ├── ModelRegistry.php     # Plugin modellerini core'a tanitir
│   │   │   └── Console/              # voyager:make:plugin CLI generator
│   │   ├── Themes/                    # Tema sistemi (ThemeManager, Theme interface)
│   │   ├── ActivityLog/              # Aktivite log sistemi (model, controller)
│   │   ├── Upgrade/                  # v2→v3 upgrade CLI + wizard
│   │   ├── BackwardCompatibility/    # Shim katmani (FormFields, Widget, jQuery)
│   │   └── Events/                   # 24 event (korunuyor)
│   ├── plugins/                       # First-party plugin'ler
│   │   ├── blog/                     # Post, Page, Category (yellow-three/voyager-blog)
│   │   └── menu/                     # Menu, MenuItem (yellow-three/voyager-menu)
├── resources/
│   ├── views/
│   │   ├── components/               # Core Livewire SFC + MFC
│   │   │   ├── ⚡bread-table.blade.php
│   │   │   ├── ⚡bread-form/
│   │   │   ├── ⚡media-manager/
│   │   │   ├── ⚡dashboard/
│   │   │   ├── ⚡settings-manager/
│   │   │   ├── ⚡database-manager/
│   │   │   ├── ⚡activity-log/
│   │   │   ├── ⚡cache-manager/
│   │   │   ├── ⚡maintenance-mode/
│   │   │   ├── ⚡queue-manager/
│   │   │   ├── ⚡impersonation/
│   │   │   ├── ⚡upgrade-wizard/
│   │   │   ├── ⚡plugins-manager/
│   │   │   ├── ⚡themes-manager/
│   │   │   └── ⚡admin-menu.blade.php
│   │   ├── formfields/               # Blade partial (handler ile eslesir)
│   │   ├── layouts/                  # admin, auth
│   │   ├── pages/                    # login, dashboard, profile, plugins, themes, vb.
│   │   └── partials/                # navbar, app-footer, bulk-delete, coordinates
│   ├── css/app.css                  # Tailwind 4 @import + @theme
│   └── js/app.js                    # Alpine.js + Dropzone + TinyMCE + CodeMirror wrapper
├── publishable/
│   ├── assets/build/                # Vite build ciktisi
│   ├── config/voyager.php           # Yayinlanabilir config
│   └── lang/                        # 630 dil dosyasi (korunuyor)
├── routes/voyager.php               # Livewire + API route'lari
├── migrations/                       # 20 migration (korunuyor) + yenileri
├── tests/                            # Pest 3 + Playwright E2E
├── vite.config.js
├── package.json
└── composer.json
```

## Plugin Generator

```bash
./vendor/bin/testbench voyager:make:plugin {name}

# Ornek:
./vendor/bin/testbench voyager:make:plugin blog
# → plugins/blog/ iskeletini olusturur
# → BlogPlugin.php, BlogServiceProvider.php, composer.json, routes, views, vb.
```

## Branch Stratejisi

```
1.7  → donmus, sadece referans
3.x  → aktif gelistirme (default)
main → stabil release
```

Tum kodlama `3.x` branch'inde yapilir. `1.7` uzerinde degisiklik yapilmaz.

## Repo Kurulumu

- **Packagist**: `yellow-three/voyager` kaydedildi mi? → `packagist.org/packages/yellow-three/voyager`
- **Plugin'ler**: `yellow-three/voyager-blog`, `yellow-three/voyager-menu`
- **README**: Fork bildirimi baslikta yer aliyor mu?

## Validation Checklist (Gorev Tamamlama)

- [ ] Pint calisiyor (`./vendor/bin/pint`)
- [ ] Pest testler geciyor (`./vendor/bin/pest`)
- [ ] Namespace `YellowThree\Voyager` (eski `TCG\Voyager` yok)
- [ ] Yeni Livewire component SFC/MFC kurallarina uygun
- [ ] Plugin interface metodlari dogru implemente edilmis
- [ ] Dil dosyasi anahtarlari (varsa) eklendi
- [ ] Playwright E2E (varsa) geciyor
- [ ] Vite build hatasiz (`npm run build`)

## "Do Not Edit" Listesi

- `vendor/`, `node_modules/`, `build/` — harici bagimliliklar
- `publishable/assets/build/` — Vite build ciktisi, kaynak degil
- `publishable/lang/` — 630 dil dosyasi, sadece yeni anahtar eklenir
- `.env`, `.env.example` — host uygulama icin
- `composer.lock`, `package-lock.json` — lock dosyalari

---

## gh CLI Komutlari

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
gh pr create --title "[title]" --body "$(cat <<'EOF'\n## Summary\n...\nEOF\n)"
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
gh release create v3.0.0 --title "v3.0.0" --notes "Community fork: Laravel 13 + Livewire 4 + Tailwind 4 + Vite (yellow-three/voyager)"
gh release list
gh release view v3.0.0
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
# Fork (calisilan repo)
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

> **Not:** Upstream artık arşivlenmiş durumda (read-only). Sadece tarihsel karşılaştırma için kullanılır, merge yapılmaz.

---

## Testbench Komutlari

Bu proje bir **Laravel paketidir**, uygulama degil. `php artisan` komutlari dogrudan calistirilmaz, `orchestra/testbench` üzerinden calisir:

```bash
# Artisan komutlarini testbench ile calistir
./vendor/bin/testbench [artisan-komutu]

# Ornekler:
./vendor/bin/testbench voyager:install
./vendor/bin/testbench voyager:upgrade
./vendor/bin/testbench voyager:make:plugin my-plugin
./vendor/bin/testbench migrate
./vendor/bin/testbench db:seed --class=VoyagerDatabaseSeeder

# Test suite (Pest)
./vendor/bin/pest
./vendor/bin/pest --filter=BreadTableTest
# PHPUnit'den Pest'e migrate: ./vendor/bin/pest --migrate

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

## Bitis Protokolu

Bir gorevi tamamlayinca:

### 1. Hafiza & Grafik Guncelle

```bash
mempalace mine . --mode convos
graphify extract . --update
```

### 2. Commit & Push

```bash
# Durum kontrolu
git status

# Dosyalari ekle (sadece ilgili dosyalar, "git add ." kullanma!)
git add <path/to/file>

# Commit
git commit -m "feat(scope): kisa aciklama"

# gh ile push
git push origin HEAD
```

### Commit Mesaj Format

```
feat(scope): mesaj          # Yeni ozellik
refactor(scope): mesaj      # Kod iyilestirme
fix(scope): mesaj           # Hata duzeltme
docs(scope): mesaj          # Dokumantasyon
wip(scope): mesaj           # Devam eden is
```

Scope ornekleri: `bread-table`, `bread-form`, `media-manager`, `plugin-system`, `theme-system`, `deps`, `vite`, `tailwind`, `tests`, `cli-generator`, `model-registry`, `e2e`, `ci`, `docs`, `namespace`, `branch-strategy`, `pest`

### 3. Kullaniciyi Bilgilendir

Commit mesaji ve push durumunu soyle.

---

## NOTLAR

1. Bu proje bir **Laravel paketidir**, uygulama degil. `orchestra/testbench ^11.0` ile test edilir. Test framework'u **Pest 3**'tur.
2. `php artisan` komutlari dogrudan degil, `./vendor/bin/testbench [komut]` ile calistirilir.
3. `laravel/boost` kullanilmiyor (package oldugu icin anlamsiz).
4. `npm run build` ile Vite assets derlenir, `publishable/assets/build/`'e cikar.
5. Playwright E2E testleri `tests/E2E/` altinda, `npx playwright test` ile calistirilir.
6. Translation CI validator: `./vendor/bin/testbench voyager:validate-lang` komutuyla tum dillerde eksik anahtar kontrolu.
7. PHP Namespace **`YellowThree\Voyager`** — eski `TCG\Voyager` namespace'i artık kullanılmıyor. Yeni dosya/sinif yazarken dikkat et.
8. Packagist paket adı **`yellow-three/voyager`** — plugin'ler icin `yellow-three/voyager-blog`, `yellow-three/voyager-menu`.
9. Upstream `thedevdojo/voyager` arşivlenmiştir (Şubat 2025). Sadece tarihsel referans amaçlıdır.

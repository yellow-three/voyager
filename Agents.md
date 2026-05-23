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
| Paket | `tcg/voyager` |
| Tip | Laravel 13 paketi (uygulama degil) |
| PHP | ^8.3 |
| Laravel | ^13.0 |
| Livewire | ^4.0 (SFC default, MFC karmasik) |
| CSS | Tailwind CSS 4 |
| Build | Vite |
| JS | Alpine.js, Dropzone, TinyMCE, CodeMirror 6 |
| Test | PHPUnit |
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
│   │   ├── blog/                     # Post, Page, Category (tcg/voyager-blog)
│   │   └── menu/                     # Menu, MenuItem (tcg/voyager-menu)
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
├── tests/                            # PHPUnit + Playwright E2E
├── vite.config.js
├── package.json
└── composer.json
```

## Plugin Generator

```bash
php artisan voyager:make:plugin {name}

# Ornek:
php artisan voyager:make:plugin blog
# → plugins/blog/ iskeletini olusturur
# → BlogPlugin.php, BlogServiceProvider.php, composer.json, routes, views, vb.
```

## Validation Checklist (Gorev Tamamlama)

- [ ] Pint calisiyor (`./vendor/bin/pint`)
- [ ] PHPUnit testler geciyor (`./vendor/bin/phpunit`)
- [ ] Yeni Livewire component SFC/MFC kurallarina uygun
- [ ] Plugin interface metodlari dogru implemente edilmis
- [ ] Dil dosyasi anahtarlari (varsa) eklendi
- [ ] Playwright E2E (varsa) gecmiyor
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
gh release create v3.0.0 --title "v3.0.0" --notes "Migration to Laravel 13 + Livewire 4 + Tailwind + Vite"
gh release list
gh release view v3.0.0
```

### Repo

```bash
gh repo view
gh repo set-default
```

### API (GitHub API)

```bash
gh api repos/owner/repo/issues
gh api repos/owner/repo/pulls/123/comments
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

Scope ornekleri: `bread-table`, `bread-form`, `media-manager`, `plugin-system`, `theme-system`, `deps`, `vite`, `tailwind`, `tests`, `cli-generator`, `model-registry`, `e2e`, `ci`, `docs`

### 3. Kullaniciyi Bilgilendir

Commit mesaji ve push durumunu soyle.

---

## NOTLAR

1. Bu proje bir **Laravel paketidir**, uygulama degil. `orchestra/testbench` ile test edilir.
2. `php artisan` komutlari proje kokunde degil, testbench icinde calisir.
3. `laravel/boost` kullanilmiyor (package oldugu icin anlamsiz).
4. `npm run build` ile Vite assets derlenir, `publishable/assets/build/`'e cikar.
5. Playwright E2E testleri `tests/E2E/` altinda, `npx playwright test` ile calistirilir.
6. Translation CI validator: `php artisan voyager:validate-lang` komutuyla tum dillerde eksik anahtar kontrolu.

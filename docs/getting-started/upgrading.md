# Upgrading

## v2 → v3 Migration

Voyager v3 is a complete rewrite of the community fork `yellow-three/voyager`. If you're migrating from the original `tcg/voyager` or `thedevdojo/voyager` (v1.x), see:

- **[UPGRADE.md](../../UPGRADE.md)** — Step-by-step upgrade guide
- **[Migration Guide](../migration.md)** — Namespace migration reference

### Quick Start

```bash
# Remove old package
composer remove tcg/voyager

# Install v3
composer require yellow-three/voyager:^3.0-alpha

# Run upgrade wizard
php artisan voyager:upgrade
```

## v3 Minor Upgrades

```bash
composer update yellow-three/voyager
```

Check [CHANGELOG.md](../../CHANGELOG.md) for breaking changes between minor versions.

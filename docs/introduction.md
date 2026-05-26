# Introduction

Voyager v3 (`yellow-three/voyager`) is a community-maintained fork of the now-archived `thedevdojo/voyager` package — rebuilt for **Laravel 13, Livewire 4, Tailwind CSS 4, and Vite**.

This documentation will guide you through installing, configuring, and extending Voyager to build powerful admin panels for your Laravel applications.

---

## What's New in v3

| Feature | Description |
|---|---|
| **Livewire 4 UI** | Full SFC/MFC admin panels — no more Bootstrap or jQuery |
| **Tailwind CSS 4** | `@theme` token system, no `tailwind.config.js` required |
| **Plugin System** | `BasePlugin` + granular contracts for extensibility |
| **Hybrid BreadManager** | JSON-first BREAD definitions with DB fallback |
| **First-party Plugins** | `voyager-menu`, `voyager-blog` as separate packages |
| **Activity Log** | Built-in admin action logger |
| **Upgrade Wizard** | `voyager:upgrade` CLI for v2 → v3 migration |

---

## Before You Start

- **PHP:** ^8.3
- **Laravel:** ^13.0
- **Database:** MySQL 8.0+, MariaDB 10.3+, PostgreSQL 12+, or SQLite

Ready? Head over to [Installation](getting-started/installation.md).

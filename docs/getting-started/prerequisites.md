# Prerequisites

Before installing Voyager v3, ensure your environment meets the following requirements:

## PHP

| Requirement | Minimum |
|---|---|
| PHP version | ^8.3 |
| Extensions | `json`, `mbstring`, `gd` (for image processing), `xml` |

## Laravel

| Requirement | Version |
|---|---|
| Laravel | ^13.0 |

## Database

One of the following databases is required:

- MySQL 8.0+
- MariaDB 10.3+
- PostgreSQL 12+
- SQLite

## Node.js (for building assets)

Node.js 18+ and npm are required for asset compilation:

```bash
npm install
npm run build
```

> Voyager v3 uses Vite for asset bundling. No Laravel Mix or Webpack configuration is needed.

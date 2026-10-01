# Shared Hosting Guide

Cartxis is designed so merchants on **shared hosting** (cPanel, Plesk, AlwaysData, etc.) can install **without Node.js**. Frontend assets ship inside the Shared Hosting release zip.

## Preferred path (no Node)

1. Download the latest **`cartxis-{version}-shared-hosting.zip`** from [GitHub Releases](https://github.com/cartxis/cartxis/releases).
2. Upload and extract on your host.
3. Point the **document root** to the `public/` directory (not the project root). This is required for Laravel security and correct asset URLs.
4. Copy `.env.example` → `.env`. Set at least:
   - `APP_URL` — your public site URL (https://…)
   - `DB_*` — MySQL credentials from your host panel
5. Make `storage/` and `bootstrap/cache/` writable by PHP.
6. Finish install:

### Option A — SSH / terminal (recommended)

```bash
php artisan key:generate
php artisan cartxis:install
```

If Node is missing but `public/build/manifest.json` is present (it is, in this zip), the installer prints:

> Using pre-built frontend assets (shared hosting package)

Then open `https://YOUR-DOMAIN/setup` to complete the browser setup wizard.

### Option B — Limited panel (minimal CLI)

1. `php artisan key:generate` (many panels offer a one-off PHP CLI or “Artisan” runner).
2. Create the database in the host panel and fill `DB_*` in `.env`.
3. Run migrations/seed if your panel allows Artisan; otherwise use SSH once, or ask host support.
4. Visit `/setup` in the browser once the database is migrated and an admin user exists.

If you see the **“Frontend assets are missing”** page (HTTP 503), you uploaded a **git clone / source tree** without `public/build`. Switch to the Shared Hosting zip, or build assets elsewhere and upload `public/build`.

## Requirements

| Item | Requirement |
|------|-------------|
| PHP | 8.3+ |
| Extensions | OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath |
| Database | MySQL 8.0+ or MariaDB 10.6+ |
| Document root | Must be `public/` |
| Node.js | **Not required** for the Shared Hosting zip |

## What the Shared Hosting zip includes

- Application source
- `public/build/` (Vite production assets + `manifest.json`)
- `vendor/` (Composer `--no-dev` dependencies)
- `INSTALL-SHARED-HOSTING.txt` / `DIST_README.txt` with short upload steps

Excluded: `.git`, `node_modules`, `tests`, `.env`, log/cache contents, agent/IDE folders.

## Building the zip yourself

On a machine with Node and Composer:

```bash
npm ci && npm run build
./scripts/package-release.sh --with-vendor
# → dist/cartxis-{version}-shared-hosting.zip
```

Flags:

- `--with-vendor` (default) — run `composer install --no-dev` and include `vendor/`
- `--no-vendor` — omit `vendor/` (host must run Composer)
- `--skip-npm` — reuse an existing `public/build` (CI uses this after a prior build)

Tagged releases (`v*`) run [`.github/workflows/release.yml`](../.github/workflows/release.yml), which builds assets, packages the zip, and attaches it to the GitHub Release.

## Developer / source install (needs Node)

For contributing or local development:

```bash
git clone https://github.com/cartxis/cartxis.git
cd cartxis
composer install
npm install && npm run build
cp .env.example .env
php artisan cartxis:install
```

`public/build` is gitignored; without `npm run build` (or the Shared Hosting zip), `/setup` and the admin UI will not load.

CI/dev only: `php artisan cartxis:install --skip-assets` allows an incomplete asset tree. Do **not** use that for production browser traffic.

## Troubleshooting

| Symptom | Fix |
|---------|-----|
| Vite / blank admin / “Frontend assets are missing” | Use Shared Hosting zip or run `npm run build` and upload `public/build` |
| 404 on all routes | Document root is not `public/` |
| Permission errors writing cache/logs | `chmod -R ug+rwx storage bootstrap/cache` (adjust for your host) |
| Installer says assets missing and exits | Download Shared Hosting zip, or build assets, or pass `--skip-assets` only for non-browser jobs |

See also [USER_GUIDE.md](./USER_GUIDE.md) §2 Installation.

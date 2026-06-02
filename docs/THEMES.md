# Storefront Themes & Template Zone

This guide covers installing, activating, and troubleshooting Cartxis storefront themes (templates).

---

## How themes are stored

Cartxis uses a **single template layout** — there is no separate legacy `themes/` folder for new installs.

| Location | Purpose |
|----------|---------|
| `templates/storefront/{category}/{slug}/` | Theme source (Vue pages, `theme.json`, demo data, static assets) |
| `public/templates/{slug}/` | Published assets copied at install/activate (`css/theme.css`, `assets/*`, screenshots) |
| `public/build/` | Vite bundle — must include theme Vue pages (built with `npm run build`) |

**Bundled with OSS:** `templates/storefront/general/cartxis-default/`  
**Optional themes** (e.g. Dmart Electronics): install from **Admin → Appearance → Browse Themes** or the remote theme directory.

---

## First-time installation

### 1. Run the installer

```bash
composer create-project cartxis/cartxis my-store
cd my-store
php artisan cartxis:install
```

The installer will:

- Create/configure `.env`
- Register a **theme directory API key** (when `https://cartxis.com/api` is reachable)
- Run migrations and seed the default theme
- Prompt you to build frontend assets

If you see **“Could not register theme directory key”** during install, the store still works — fix it after go-live (see [Theme directory API](#theme-directory-api) below).

### 2. Theme directory `.env` keys

Auto-written by `cartxis:install` when registration succeeds:

```env
CARTXIS_THEME_DIRECTORY_URL=https://cartxis.com/api
CARTXIS_THEME_API_KEY=ctx_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

> **Important:** Use `https://cartxis.com/api` — **not** `https://www.cartxis.com/api`. The `www` host redirects POST requests and breaks API key registration (HTTP 405).

Recover or verify registration:

```bash
php artisan theme:directory:register
php artisan theme:directory:register --force   # new key if needed
```

---

## Installing a theme (merchants)

1. Log in to **Admin → Appearance → Browse Themes**
2. Filter by category or search
3. Click **Install** on the theme you want
4. Optionally check **Activate** during install

Cartxis automatically:

1. Downloads/copies the template to `templates/storefront/{category}/{slug}/`
2. Registers it in the database
3. Publishes static assets to `public/templates/{slug}/`
4. Runs `php artisan optimize:clear`
5. Runs `npm run build` (when `CARTXIS_THEME_REBUILD_ASSETS=true`, the default)

After install, **hard-refresh** the storefront (`Cmd+Shift+R` / `Ctrl+Shift+R`).

### CLI install

```bash
php artisan template:install dmart-electronics --activate
php artisan theme:list
php artisan theme:activate cartxis-default
```

---

## Activating & customizing

| Task | Where |
|------|--------|
| Activate a theme | **Admin → Appearance → Themes** or **Browse Themes** |
| Colors, layout, features | **Admin → Appearance** (settings tabs) |
| Native homepage editor | **Appearance → Features** tab (themes with `native_homepage: true`) |
| UI Editor homepage | **Admin → UI Editor** (themes using block layouts) |
| Import demo data | **Appearance → Import theme data** |

When you **activate** a theme from admin, Cartxis runs the same post-activate steps as install (discover, cache clear, asset rebuild).

---

## Artisan commands

| Command | Purpose |
|---------|---------|
| `php artisan theme:discover` | Scan `templates/storefront/`, register themes, publish public assets |
| `php artisan theme:list` | List installed themes |
| `php artisan theme:activate {slug}` | Activate a theme |
| `php artisan template:install {slug}` | Install from local catalog or remote directory |
| `php artisan theme:import-data {slug}` | Import blocks, menus, settings from `data/theme-data.json` |
| `php artisan theme:directory:register` | Register/verify theme directory API key |
| `php artisan optimize:clear` | Clear config, route, view, and application cache |

---

## Theme directory API

Remote catalog is hosted at **[cartxis.com](https://cartxis.com)**. See [THEME_DIRECTORY_API.md](./THEME_DIRECTORY_API.md) for API details.

**Browse** (public): categories and theme list  
**Install** (authenticated): downloads ZIP with integrity hash (`X-Theme-Hash`)

---

## Troubleshooting

### Theme directory key failed during install

**Symptoms:** Installer shows `Could not register theme directory key`.

**Fix:**

```bash
# 1. Set correct URL in .env (no www)
CARTXIS_THEME_DIRECTORY_URL=https://cartxis.com/api

# 2. Register key
php artisan theme:directory:register

# 3. Confirm
php artisan theme:directory:register   # should say "already saved"
```

Check network access to `https://cartxis.com/api/theme-categories`.

---

### Broken CSS or layout after installing/activating a theme

**Symptoms:** Storefront looks unstyled, wrong colors, shop sidebar missing, or default theme layout appears.

**Causes:** Stale Vite build, stale caches, or missing published CSS.

**Fix (automatic since v1.0.13+):** Re-activate the theme in admin — this runs discover, `optimize:clear`, and `npm run build`.

**Manual fix:**

```bash
php artisan theme:discover
php artisan optimize:clear
npm run build
```

Then hard-refresh the browser. Verify:

```bash
ls public/templates/YOUR-THEME-SLUG/css/theme.css
ls public/build/manifest.json
```

---

### Storefront page says “Template page not found”

Theme Vue pages are bundled at **build time**. Installing a theme after the last build leaves pages out of the manifest.

```bash
npm run build
php artisan optimize:clear
```

Ensure `CARTXIS_THEME_REBUILD_ASSETS=true` in `.env` (default) so future installs rebuild automatically.

---

### Theme installed but not listed under Appearance → Themes

Catalog packages (with `template.json`) are browse-only until installed through **Browse Themes** or `template:install`.

```bash
php artisan template:install your-theme-slug --activate
php artisan theme:discover
```

---

### `npm run build` fails after remote theme install

Remote ZIPs sometimes include macOS junk (`__MACOSX/`, `._*.vue`). Cartxis removes these on discover/install; if build still fails:

```bash
find templates/storefront -name '__MACOSX' -exec rm -rf {} +
find templates/storefront -name '._*' -delete
php artisan theme:discover
npm run build
```

---

### Wrong theme active after `config:cache`

`theme.active` is set at **runtime** from the database. If you cached config before switching themes, run:

```bash
php artisan optimize:clear
```

Do not rely on a hard-coded `theme.active` value in `config/theme.php` — it should remain `null`.

---

### Images or hero sliders 404

Static images live in the theme’s `assets/` folder and are copied to `public/templates/{slug}/assets/` on discover.

```bash
php artisan theme:discover
ls templates/storefront/general/cartxis-default/assets/
ls public/templates/cartxis-default/assets/
```

---

### Shop filter sidebar not visible

1. Open **Admin → Appearance → Layout**
2. Confirm **Shop Sidebar** is **Left** or **Right** (not **None**)
3. Sidebar is desktop-only (`lg:` breakpoint) — check a wide viewport
4. Ensure the active theme’s `theme.css` is loaded (see broken CSS section above)

---

### Production deployment checklist (themes)

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan theme:discover
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

After **activating or installing a theme on production**, run `php artisan optimize:clear` and `npm run build` (or use admin activate, which triggers this automatically).

---

## For theme developers

- Package path: `templates/storefront/{category}/{slug}/`
- Required: `theme.json`, `resources/views/pages/`, `components/`, `layouts/`
- Optional: `template.json` (catalog metadata), `data/theme-data.json`, `config/settings.php`, `resources/css/theme.css`
- Block overrides: see [THEME_BLOCK_ARCHITECTURE.md](./THEME_BLOCK_ARCHITECTURE.md)
- Architecture overview: [ARCHITECTURE.md](./ARCHITECTURE.md)

---

## Related docs

- [THEME_DIRECTORY_API.md](./THEME_DIRECTORY_API.md) — Remote API & env vars
- [USER_GUIDE.md](./USER_GUIDE.md) — Full admin manual
- [QUICK_REFERENCE.md](./QUICK_REFERENCE.md) — Developer command reference

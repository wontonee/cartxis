# Theme Directory API

External theme directory API hosted on **cartxis-home** (`https://cartxis.com`). The open-source Cartxis app uses these routes for **Appearance → Browse Themes** and one-click installs.

See also: [THEMES.md](./THEMES.md) (merchant install & troubleshooting guide) · [cartxis-home theme API docs](https://github.com/wontonee/cartxis-home/blob/main/docs/theme-directory-api.md)

## Base URL

| Environment | Base URL |
|-------------|----------|
| Production | `https://cartxis.com/api` |
| Local cartxis-home (developers) | `https://cartxis-home.test/api` |

> **Do not use `https://www.cartxis.com/api`** — the `www` host redirects POST requests and breaks API key registration (HTTP 405).

---

## Environment variables (this app)

Set in your Cartxis store `.env`. Auto-populated by `php artisan cartxis:install`.

| Variable | Required | Default | Purpose |
|----------|----------|---------|---------|
| `CARTXIS_THEME_DIRECTORY_URL` | Yes | `https://cartxis.com/api` | cartxis-home API base URL |
| `CARTXIS_THEME_API_KEY` | Yes* | *(empty)* | Bearer token for theme installs |
| `CARTXIS_THEME_DIRECTORY_CACHE_TTL` | No | `3600` | Cache remote catalog (seconds) |
| `CARTXIS_THEME_REBUILD_ASSETS` | No | `true` | Run `npm run build` after theme install/activate |

\* Auto-generated during install via `POST /api/theme-keys/register`. Recover with `php artisan theme:directory:register`.

**Legacy alias:** `CARTXIS_TEMPLATE_CATALOG_URL`

**Example `.env`:**

```env
CARTXIS_THEME_DIRECTORY_URL=https://cartxis.com/api
CARTXIS_THEME_API_KEY=ctx_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
# CARTXIS_THEME_DIRECTORY_CACHE_TTL=3600
# CARTXIS_THEME_REBUILD_ASSETS=true
```

### Registration commands

```bash
php artisan theme:directory:register          # verify or create key
php artisan theme:directory:register --force  # new key even if one exists
```

---

## Public routes (no auth) — 60 req/min

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/api/theme-categories` | List active categories |
| `GET` | `/api/themes` | List themes (`categories[]`, `search`, `page`, `per_page`) |
| `POST` | `/api/theme-keys/register` | Register install key (used by `cartxis:install`) |

### `GET /api/themes` query params

| Param | Description |
|-------|-------------|
| `categories[]` | Filter by category slug |
| `search` | Search name/description/slug |
| `page` | Page number (default 1) |
| `per_page` | Per page (default 24, max 50) |

### `POST /api/theme-keys/register` body

```json
{
  "name": "Cartxis install: My Store",
  "app_url": "https://shop.example.com"
}
```

Response `201`:

```json
{
  "plain_text_key": "ctx_...",
  "key_prefix": "ctx_abcd"
}
```

---

## Protected routes (Bearer token) — 10 req/min

```
Authorization: Bearer {CARTXIS_THEME_API_KEY}
```

| Method | Route | Description |
|--------|-------|-------------|
| `POST` | `/api/themes/{slug}/install` | Download theme ZIP |

Response headers: `Content-Disposition`, `X-Theme-Hash` (SHA-256 for integrity check).

Errors: `401` (bad key), `404` (theme missing), `429` (rate limit).

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Install wizard: “Could not register theme directory key” | Use `https://cartxis.com/api` (no `www`); run `php artisan theme:directory:register` |
| Browse Themes empty but API works | Check `CARTXIS_THEME_DIRECTORY_URL`; run `php artisan optimize:clear` |
| Install button disabled | Set `CARTXIS_THEME_API_KEY`; run `theme:directory:register` |
| Theme installed but storefront broken | See [THEMES.md](./THEMES.md) — run `theme:discover`, `optimize:clear`, `npm run build` |

---

## Full documentation

For request/response examples, admin routes, and flow diagrams, see **`docs/theme-directory-api.md`** in the cartxis-home repository.

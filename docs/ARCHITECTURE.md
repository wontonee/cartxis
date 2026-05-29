# Cartxis Architecture

> High-level reference for contributors, extension authors, and agent-assisted development.

## Overview

Cartxis is a **modular Laravel 12 eCommerce monolith** with a **Vue 3 + TypeScript + Inertia.js** admin and storefront, **MySQL 8**, and optional **REST API** for the open-source mobile app.

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         Browser / Mobile App                             │
├──────────────────────────────┬──────────────────────────────────────────┤
│  Storefront (Inertia+Theme)  │  Admin Panel (Inertia, /admin/*)         │
│  Shop, CMS, Blog, Cart       │  Product, Sales, Settings, Reports...    │
├──────────────────────────────┴──────────────────────────────────────────┤
│                    Laravel HTTP Kernel + Middleware                      │
│  Fortify (auth) · Sanctum (API) · Separate admin session cookie          │
├─────────────────────────────────────────────────────────────────────────┤
│                    packages/Cartxis/* (domain packages)                  │
├─────────────────────────────────────────────────────────────────────────┤
│  Core services: cartxis.hook · cartxis.menu · cartxis.setting ·             │
│                 cartxis.extension · cartxis.theme · cartxis.payment.gateway │
├─────────────────────────────────────────────────────────────────────────┤
│                         MySQL 8 (settings, catalog, orders)              │
└─────────────────────────────────────────────────────────────────────────┘
```

## Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.2+, Laravel 12 |
| Admin/Shop UI | Inertia.js 2, Vue 3.5, TypeScript, Tailwind 4 |
| Auth | Laravel Fortify (web), Sanctum (API tokens) |
| DB | MySQL 8.0+ |
| PDF | mPDF |
| Images | Intervention Image 3 |
| Payments | Stripe, Razorpay, PayPal, PhonePe (bundled SDK), PayUMoney |
| Backup | spatie/laravel-backup |
| Tests | Pest 4 (PHP), Playwright (e2e in `tests/e2e/`) |

## Repository Layout

```
cartxis/
├── app/                    # Thin Laravel app shell (Fortify, middleware)
├── bootstrap/providers.php # Registers all Cartxis service providers
├── packages/Cartxis/       # All domain logic (PSR-4 Cartxis\*)
├── resources/js/           # Admin + shared Vue (pages, UI Editor, components)
├── templates/storefront/   # Storefront template packages (cartxis-default, …)
├── routes/                 # Core web, auth, settings only
├── database/               # App-level seeders/factories
├── extension/              # Optional third-party extensions (gitignored clones)
└── tests/                  # Pest + Playwright
```

## Package Map

Each package owns migrations, routes (via its `*ServiceProvider`), models, and admin menus where applicable.

| Package | Responsibility |
|---------|----------------|
| **Core** | Installer (`cartxis:install`), hooks, extensions, themes, settings store, payment gateway manager, tax/shipping base, RBAC |
| **Setup** | First-run wizard (must load before CMS catch-all routes) |
| **Admin** | Admin shell, dashboard, notifications |
| **Product** | Catalog, variants, attributes, inventory, reviews, AI product descriptions |
| **Cart** | Session cart, tax calculation, cart API |
| **Shop** | Storefront controllers, checkout, orders (shop-side), account area |
| **Sales** | Admin order lifecycle: invoices, shipments, credit memos, transactions |
| **Customer** | Customer accounts, groups, addresses |
| **Marketing** | Coupons, promotions, discount engine |
| **Settings** | Store config, locales, channels, payment/shipping/tax admin, **AI settings** |
| **CMS** | Static pages, content blocks |
| **UIEditor** | Visual page builder (layouts JSON: Section → Column → Block) |
| **Blog** | Blog posts (admin + storefront + API) |
| **Reports** | Sales, customer, product reports |
| **System** | Maintenance, backup, cache, migrations (WooCommerce/Bagisto), API sync, Shiprocket |
| **API** | `/api/v1/*` for mobile (Sanctum) |
| **Stripe / Razorpay / PayPal / PhonePe / PayUMoney** | Gateway-specific routes + config UI |

Payment gateways register with `PaymentGatewayManager` in Core; credentials live in DB via admin, not `.env`.

## Request Flow

### Storefront (Inertia)

1. `ShopServiceProvider` loads `packages/Cartxis/Shop/src/Routes/web.php`
2. `ShareFrontendData` middleware shares cart, currency, theme data
3. Controllers return `Inertia::render()` — theme resolves views via `ThemeViewResolver`
4. CMS pages with published UI layouts use `UIBlockRenderer` (shared in `resources/js`)

### Admin

1. Prefix `/admin`, guard `admin`, separate session cookie (`SetAdminSessionCookie`)
2. Each package registers `Routes/admin.php` and menu items via `cartxis.menu`
3. Pages live under `resources/js/pages/Admin/`

### REST API (Mobile)

- Base: `/api/v1`
- Public: products, categories, search, banners, auth register/login
- Authenticated (Sanctum): cart, checkout, orders, wishlist, profile
- See `packages/Cartxis/API/Routes/api.php` and `packages/Cartxis/API/INSTALLATION.md`

## Extensibility

### Hooks (WordPress-style)

Global helpers in `packages/Cartxis/Core/src/Helpers/hooks.php`:

- `add_action` / `do_action`
- `add_filter` / `apply_filters`

Backed by `app('cartxis.hook')` singleton.

### Extensions

- CLI: `php artisan cartxis:extension:make`, `extensions:install`, `extensions:activate`
- Registry table `extensions`; menu/settings keyed by `extension_code`
- Frontend pages: `resources/js/pages/Extensions/{ExtensionName}/...` resolved in `resources/js/app.ts`
- Guide: `specificationandtask/EXTENSION-DEVELOPMENT-GUIDE.md`

### Themes

- `templates/storefront/{category}/{slug}/` with `resources/views/pages`, layouts, components
- Active theme: `config/theme.php`
- `php artisan cartxis:theme:activate`
- **Templates (OSS)**: themes ship `theme.json` + optional `data/theme-data.json` for demo homepage/layout import (`theme:import-data`). Cursor agent: `@cartxis-templates`.

## Data Model (Conceptual)

```
Product ──┬── ProductVariant, ProductImage, Attribute values
          └── Categories, Brands, Reviews

Cart (session) ──► Checkout ──► Order (Shop) ──► Sales admin (Invoice, Shipment, CreditMemo)

Customer ── CustomerAddress, Wishlist
User (Fortify) ── linked for storefront login

Setting (key/value/json, grouped) ── store, AI, payment methods, etc.
PageLayout (UI Editor JSON) ── CMS pages + homepage
```

Orders are created in **Shop** (`CheckoutService`); fulfillment documents in **Sales**.

## AI System (In-App)

Configured in **Admin → Settings → AI** (`AiSettingsController`, `resources/js/pages/Admin/Settings/AI/Index.vue`):

| Concept | Storage |
|---------|---------|
| Providers | `ai.providers` (OpenAI, Anthropic, custom compatible) |
| Models | `ai.models` |
| Agents | `ai.agents` JSON array |
| Defaults | `ai.default_agent`, `ai.product_description_agent`, `ai.price_comparison_agent` |

**System agents** (seeded, non-deletable):

- Product Description Agent → `AiProductDescriptionService`
- Price Comparison Agent → pricing modal on product edit

Custom agents can be added in admin; extensions (e.g. SalesChat) may define their own agent names.

## Security Model

- **Admin**: separate guard + session cookie; permissions via Core RBAC
- **Customer**: Fortify `web` guard + Customer model
- **API**: Sanctum personal access tokens, throttled auth routes
- **Payments**: server-side gateway callbacks; secrets in DB settings

## Build & Deploy

```bash
composer install --no-dev   # production
npm run build
php artisan config:cache && route:cache && view:cache
php artisan queue:work      # Supervisor in prod
# cron: * * * * * php artisan schedule:run
```

## Testing

- **PHPUnit/Pest**: `tests/Feature`, `tests/Unit`
- **E2E**: `tests/e2e/` (Playwright) — includes SalesChat extension scenarios

## Developer agents: maintenance vs upgrades

| Concern | Cursor agent |
|---------|----------------|
| Store downtime banner, backups, cache (`System` package) | `@cartxis-core` |
| Laravel / Composer / NPM version upgrades | `@cartxis-upgrades` |
| Release changelog and OSS PR after upgrade | `@cartxis-oss` |

## Roadmap Touchpoints (from README)

Upcoming work areas agents should respect:

- iOS app, 2FA, multi-vendor, subscriptions, i18n, SEO, analytics, integration marketplace

## Related Docs

- [README.md](../README.md) — install, features, roadmap
- [USER_GUIDE.md](./USER_GUIDE.md) — admin user documentation
- [EXTENSION-DEVELOPMENT-GUIDE.md](../specificationandtask/EXTENSION-DEVELOPMENT-GUIDE.md)
- [API INSTALLATION](../packages/Cartxis/API/INSTALLATION.md)

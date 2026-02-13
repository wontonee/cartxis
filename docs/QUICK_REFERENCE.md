# Cartxis — Developer Guide

> Architecture reference for the Cartxis e-commerce platform.  
> **Version**: 1.0.4 &nbsp;|&nbsp; **Stack**: Laravel 12 · Vue 3.5 · Inertia.js · TailwindCSS 4.1

---

## Table of Contents

1. [Tech Stack](#1-tech-stack)
2. [Project Structure](#2-project-structure)
3. [Package Architecture](#3-package-architecture)
4. [Service Provider Boot Order](#4-service-provider-boot-order)
5. [Core Package — The Kernel](#5-core-package--the-kernel)
6. [Package Catalog](#6-package-catalog)
7. [Routing Architecture](#7-routing-architecture)
8. [Frontend Architecture](#8-frontend-architecture)
9. [Theme System](#9-theme-system)
10. [Extension System](#10-extension-system)
11. [Payment Gateway Pattern](#11-payment-gateway-pattern)
12. [Database & Migrations](#12-database--migrations)
13. [Design Patterns](#13-design-patterns)
14. [Configuration](#14-configuration)
15. [Testing](#15-testing)
16. [CLI Commands](#16-cli-commands)
17. [Development Workflow](#17-development-workflow)

---

## 1. Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Framework | Laravel | 12.x |
| PHP | PHP | ≥ 8.2 |
| Frontend | Vue 3 (Composition API) | 3.5 |
| SPA Bridge | Inertia.js | 2.x |
| State Management | Pinia | 3.x |
| CSS | TailwindCSS | 4.1 |
| UI Component Library | shadcn-vue (reka-ui primitives) | 2.4 |
| Rich Text Editor | TipTap | 3.x |
| Charts | Chart.js + vue-chartjs | 4.x / 5.x |
| Icons | lucide-vue-next | — |
| Drag & Drop | vuedraggable | 4.x |
| API Auth | Laravel Sanctum (Bearer tokens) | 4.x |
| 2FA | Laravel Fortify | 1.x |
| Route Helpers | Laravel Wayfinder | 0.1.x |
| Image Processing | Intervention Image | 3.x |
| PDF Generation | mPDF | 8.x |
| HTML Sanitization | mews/purifier | 3.x |
| Backups | spatie/laravel-backup | 9.x |
| Build Tool | Vite | 7.x |
| TypeScript | TypeScript | 5.2 |
| Linting | ESLint 9 + Prettier 3 + Laravel Pint | — |
| Testing | Pest | 4.x |
| SSR | Inertia SSR (Node cluster) | — |
| Database | MySQL | ≥ 8.0 |

---

## 2. Project Structure

```
cartxis/
├── app/                    # Laravel application layer
│   ├── Events/             # Application events
│   ├── Http/
│   │   ├── Controllers/    # Auth & Settings controllers
│   │   ├── Middleware/      # HandleInertiaRequests, HandleAppearance
│   │   └── Requests/       # Form request validation
│   ├── Models/             # User model only — domain models live in packages
│   └── Providers/          # AppServiceProvider, FortifyServiceProvider, ThemeServiceProvider
│
├── packages/Cartxis/       # ★ CORE — 19 modular domain packages (see §3)
│
├── resources/
│   ├── js/                 # Vue 3 frontend source
│   │   ├── app.ts          # Client entry — Inertia + Vue + Pinia
│   │   ├── ssr.ts          # SSR entry (Node cluster mode)
│   │   ├── pages/          # Inertia page components (Admin/, auth/, settings/, Setup/)
│   │   ├── components/     # Shared components (Admin/, ui/)
│   │   ├── composables/    # Vue composables (8 files)
│   │   ├── layouts/        # AdminLayout, AppLayout, AuthLayout
│   │   ├── Stores/         # Pinia stores (cartStore.ts)
│   │   ├── lib/            # axios.ts, utils.ts
│   │   ├── types/          # TypeScript type definitions
│   │   └── wayfinder/      # Auto-generated Laravel route helpers
│   ├── admin/              # Admin-specific assets, pages, CSS
│   ├── css/                # Global stylesheets
│   └── views/              # Blade templates (app.blade.php root)
│
├── themes/                 # Theme packages (cartxis-default)
├── extension/              # Extension development guide & templates
├── routes/                 # App-level route files
├── config/                 # Laravel & package configuration
├── database/               # App-level migrations, factories, seeders
├── tests/                  # Pest test suites
├── public/                 # Web root (index.php, build output)
├── storage/                # Logs, cache, uploads
├── docs/                   # End-user documentation
└── specificationandtask/   # Internal specs & implementation plans
```

### Key Principle

**The `app/` directory is intentionally thin.** All domain logic — models, controllers, services, repositories, routes, migrations — lives inside `packages/Cartxis/`. The `app/` layer handles only framework bootstrapping, user authentication, and Inertia middleware.

---

## 3. Package Architecture

The platform is organized into **19 self-contained packages** under `packages/Cartxis/`. Each package owns its:

- **Models** — Eloquent models for its domain
- **Controllers** — Admin and/or web controllers
- **Routes** — Package-specific route files loaded by its ServiceProvider
- **Migrations** — Database schema (loaded automatically)
- **Services** — Business logic layer
- **Repositories** — Data access abstraction (where used)
- **Config** — Package configuration (merged into Laravel config)
- **Seeders** — Default data

### Two Package Structures

Packages follow one of two directory layouts:

**Flat layout** (older packages — Core, Admin, Product, Cart, Shop, Settings, API):
```
packages/Cartxis/Product/
├── Config/
├── Database/
│   ├── Migrations/
│   └── Seeders/
├── Http/Controllers/Admin/
├── Models/
├── Providers/
├── Routes/
└── Services/
```

**`src/` layout** (newer packages — Sales, Customer, CMS, System, Reports, Marketing, Setup, payment gateways):
```
packages/Cartxis/Sales/
└── src/
    ├── Database/
    ├── Http/Controllers/Admin/
    ├── Mail/
    ├── Models/
    ├── Providers/
    ├── Repositories/
    ├── Routes/
    └── Services/
```

> The namespace mapping is handled in `composer.json` PSR-4 autoload — flat packages map to `packages/Cartxis/PackageName/`, `src/`-based packages map to `packages/Cartxis/PackageName/src/`.

---

## 4. Service Provider Boot Order

Providers are registered in `bootstrap/providers.php` in a deliberate sequence:

```
 1. App\Providers\AppServiceProvider         # Laravel defaults
 2. App\Providers\FortifyServiceProvider     # Fortify auth (2FA, registration, password reset)
 3. App\Providers\ThemeServiceProvider       # Theme view paths + Inertia share
 4. Cartxis\Core\CoreServiceProvider         # ★ Core singletons (hook, menu, setting, extension, theme, payment gateway)
 5. Cartxis\Core\MailConfigServiceProvider   # Dynamic mail config from DB
 6. Cartxis\Admin\AdminServiceProvider       # Admin auth, middleware, routes
 7. Cartxis\Product\ProductServiceProvider   # Products, categories, attributes, brands, reviews
 8. Cartxis\Cart\CartServiceProvider         # Cart logic, tax/shipping calculators
 9. Cartxis\Shop\ShopServiceProvider         # Storefront — repositories, services, frontend middleware
10. Cartxis\Settings\SettingsServiceProvider # Admin settings pages
11. Cartxis\Stripe\StripeServiceProvider     # Stripe payment gateway (extension)
12. Cartxis\Razorpay\RazorpayServiceProvider # Razorpay gateway (extension)
13. Cartxis\PhonePe\PhonePeServiceProvider   # PhonePe gateway (extension)
14. Cartxis\Sales\SalesServiceProvider       # Orders, invoices, shipments, credit memos, transactions
15. Cartxis\Customer\CustomerServiceProvider # Customer management, groups, addresses, wishlists
16. Cartxis\Setup\SetupServiceProvider       # ★ Must be before CMS — prevents catch-all route conflict
17. Cartxis\CMS\CMSServiceProvider           # Pages, blocks, media library, storefront menus
18. Cartxis\System\SystemServiceProvider     # Cache, extensions UI, permissions, maintenance, backups
19. Cartxis\Reports\ReportsServiceProvider   # Sales/product/customer reports
20. Cartxis\Marketing\MarketingServiceProvider # Coupons, promotions
21. Cartxis\API\APIServiceProvider           # REST API v1
```

> **Important:** Setup must load before CMS. CMS registers a catch-all `/{slug}` route for dynamic pages — if loaded first, it would intercept the setup wizard routes.

---

## 5. Core Package — The Kernel

`Cartxis\Core` is the foundational package. It registers 7 singletons that other packages depend on:

| Container Key | Class | Purpose |
|--------------|-------|---------|
| `vortex.hook` | `HookService` | Event hook system for extensions |
| `vortex.menu` | `MenuService` | Dynamic admin & shop menu builder |
| `vortex.setting` | `SettingService` | Database-backed key/value settings |
| `vortex.extension` | `ExtensionService` | Extension discovery, install, activate lifecycle |
| `vortex.theme` | `ThemeService` | Theme management |
| `vortex.theme.resolver` | `ThemeViewResolver` | Theme-aware Inertia page resolution |
| `vortex.payment.gateway` | `PaymentGatewayManager` | Payment gateway registry |

### Core Facades

| Facade | Singleton |
|--------|-----------|
| `Extension` | `vortex.extension` |
| `Hook` | `vortex.hook` |
| `Menu` | `vortex.menu` |
| `Setting` | `vortex.setting` |

### Core Models (20)

Channel, Currency, EmailConfiguration, EmailTemplate, Extension, Locale, MenuItem, PaymentMethod, Permission, Role, Setting, ShippingMethod, ShippingRate, TaxClass, TaxRate, TaxRule, TaxZone, TaxZoneLocation, Theme, ThemeSetting

### Core Console Commands

| Command | Purpose |
|---------|---------|
| `cartxis:extensions:list` | List all discovered extensions |
| `cartxis:extensions:sync` | Sync filesystem extensions to database |
| `cartxis:extensions:install` | Install an extension |
| `cartxis:extensions:activate` | Activate an installed extension |
| `cartxis:extensions:deactivate` | Deactivate an extension |
| `cartxis:extensions:uninstall` | Remove an extension |

### Extension Boot

During `CoreServiceProvider::boot()`, all **active** extensions are dynamically loaded. The service provider iterates the `extensions` table, discovers each extension's manifest, and registers its ServiceProvider at runtime.

---

## 6. Package Catalog

### Product — `Cartxis\Product`

| Component | Items |
|-----------|-------|
| **Models** (9) | Product, Category, Attribute, AttributeOption, Brand, ProductImage, ProductReview, ProductAttributeValue, InventoryAdjustment |
| **Admin Controllers** (7) | ProductController, CategoryController, AttributeController, BrandController, ReviewController, ProductAiController, ProductImageController |
| **Services** | AiProductDescriptionService |
| **Routes** | admin.php, web.php |

### Cart — `Cartxis\Cart`

| Component | Items |
|-----------|-------|
| **Models** (2) | Cart, CartItem |
| **Controllers** | CartController (web), Api/CartController (API) |
| **Services** (2) | CartShippingCalculator, CartTaxCalculator |
| **Routes** | web.php, api.php |

### Shop — `Cartxis\Shop`

| Component | Items |
|-----------|-------|
| **Models** (4) | Address, Order, OrderItem |
| **Controllers** | HomeController, ProductController, CategoryController, SearchController, NewsletterController, Account/(Dashboard, Order, Address, Profile, Wishlist), Checkout/CheckoutController, Api/SearchController |
| **Services** (5) | HomeService, ProductService, CategoryService, CheckoutService, ShopService |
| **Repositories** (4) | ProductRepository, CategoryRepository, OrderRepository, ShopRepository |
| **Contracts** (4) | ProductRepositoryInterface, CategoryRepositoryInterface, OrderRepositoryInterface, ShopRepositoryInterface |
| **Config** | shop.php (homepage settings, featured products count, hero block config) |
| **Routes** | web.php, api.php |

> Shop is the only package that fully implements the **Repository Pattern with Contracts** — interfaces are bound to implementations via `ServiceProvider::registerRepositories()`.

### Admin — `Cartxis\Admin`

| Component | Items |
|-----------|-------|
| **Controllers** | Auth/ (admin login flow), PasswordController, ProfileController, UserController |
| **Middleware** (4) | IsAdmin, PreventAdminFrontendAccess, PreventUserAdminAccess, RedirectIfAdminAuthenticated |
| **Config** | admin.php |
| **Routes** | admin.php |

### Settings — `Cartxis\Settings`

| Component | Items |
|-----------|-------|
| **Models** | Setting |
| **Admin Controllers** (12) | GeneralSettingsController, StoreConfigurationController, AiSettingsController, LocalesController, ChannelsController, PaymentMethodsController, ShippingMethodsController, TaxClassesController, TaxRatesController, TaxRulesController, TaxZonesController, EmailController |
| **Routes** | admin.php |

### Sales — `Cartxis\Sales`

| Component | Items |
|-----------|-------|
| **Models** (7) | Invoice, CreditMemo, CreditMemoItem, Shipment, ShipmentItem, Transaction, OrderHistory |
| **Admin Controllers** (5) | OrderController, InvoiceController, ShipmentController, CreditMemoController, TransactionController |
| **Services** (7) | OrderService, InvoiceService, InvoicePdfService, ShipmentService, CreditMemoService, CreditMemoPdfService, TransactionService |
| **Repositories** (4) | OrderRepository, InvoiceRepository, CreditMemoRepository, TransactionRepository |
| **Mail** | Email templates for order/invoice notifications |
| **Routes** | admin.php |

### Customer — `Cartxis\Customer`

| Component | Items |
|-----------|-------|
| **Models** (5) | Customer, CustomerAddress, CustomerGroup, CustomerNote, Wishlist |
| **Controllers** (3) | CustomerController, CustomerAddressController, CustomerGroupController |
| **Routes** | customer.php |

### CMS — `Cartxis\CMS`

| Component | Items |
|-----------|-------|
| **Models** (5) | Page, Block, MediaFile, MediaFolder, MediaUsage |
| **Admin Controllers** (5) | PagesController, BlocksController, StorefrontMenuController, MediaController, FolderController |
| **Frontend Controllers** | PageController, MenuController |
| **Services** (3) | PageService, BlockService, MediaService |
| **Repositories** (3) | PageRepository, BlockRepository, MediaRepository |
| **Routes** | admin.php, web.php |

### Marketing — `Cartxis\Marketing`

| Component | Items |
|-----------|-------|
| **Models** (3) | Coupon, CouponUsage, Promotion |
| **Controllers** (2) | CouponController, PromotionController |
| **Services** (3) | CouponService, PromotionService, DiscountCalculator |
| **Routes** | admin.php, shop.php |

### Reports — `Cartxis\Reports`

| Component | Items |
|-----------|-------|
| **Controllers** (3) | SalesReportController, ProductReportController, CustomerReportController |
| **Services** (4) | SalesReportService, ProductReportService, CustomerReportService, ReportCacheService |
| **Repositories** (3) | SalesReportRepository, ProductReportRepository, CustomerReportRepository |
| **Routes** | admin.php |

### System — `Cartxis\System`

| Component | Items |
|-----------|-------|
| **Models** | MaintenanceLog |
| **Admin Controllers** (8) | CacheController, ExtensionsController, PermissionController, MaintenanceController, BackupController, ApiSyncController, DataMigrationController, MenuController |
| **Services** (3) | CacheService, BackupService, MaintenanceService |
| **Jobs** (2) | EnableScheduledMaintenance, DisableScheduledMaintenance |
| **Console Commands** | Migration commands |
| **Routes** | system.php |

### API — `Cartxis\API`

| Component | Items |
|-----------|-------|
| **V1 Controllers** (14) | AuthController, ProductController, CategoryController, CartController, CheckoutController, CustomerController, OrderController, ReviewController, WishlistController, SearchController, CurrencyController, BannerController, ProductAiController, ApiSyncController |
| **Middleware** | TrackApiSync (auto-updates connectivity on authenticated requests) |
| **Helpers** | ApiResponse (standardized JSON responses) |
| **Config** | api.php (rate limits, token expiry, pagination, feature flags) |
| **Docs** | Postman collection + environment files |
| **Routes** | api.php |

### Setup — `Cartxis\Setup`

| Component | Items |
|-----------|-------|
| **Controller** | SetupController |
| **Services** | DemoDataService |
| **Seeders** | All default data seeders |
| **Routes** | setup.php |

### Payment Gateways (5 packages)

Each follows the same structure and extension pattern:

| Package | Namespace |
|---------|-----------|
| Stripe | `Cartxis\Stripe` |
| RazorPay | `Cartxis\Razorpay` |
| PayPal | `Cartxis\PayPal` |
| PayUMoney | `Cartxis\PayUMoney` |
| PhonePe | `Cartxis\PhonePe` |

Each contains: `extension.json`, Config/, Http/Controllers/, Providers/, Routes/ (admin.php + web.php), Services/ (gateway implementation).

---

## 7. Routing Architecture

Routes are loaded from two sources: **app-level** (`routes/`) and **package-level** (each package's ServiceProvider).

### App-Level Routes (`routes/`)

| File | Scope | Contents |
|------|-------|----------|
| `web.php` | Web | Dashboard redirect (admin vs shop based on guard), requires `settings.php` + `auth.php` |
| `api.php` | API | Single `GET /user` (sanctum) — bulk API routes in API package |
| `auth.php` | Web (guest + auth) | Register, login, forgot/reset password, email verification, logout |
| `settings.php` | Web (auth) | Profile CRUD, password, appearance, two-factor — under `/settings/` |
| `console.php` | Artisan | Default `inspire` command |

### Package Route Files

| Package | Route File | Prefix | Middleware |
|---------|-----------|--------|-----------|
| Core | admin.php | `/admin` | `web`, `auth`, `is_admin` |
| Admin | admin.php | `/admin` | `web`, `auth`, `is_admin` |
| Product | admin.php | `/admin/products` | `web`, `auth`, `is_admin` |
| Product | web.php | `/` | `web` |
| Cart | web.php | `/cart` | `web` |
| Cart | api.php | `/api/v1/cart` | `api`, `auth:sanctum` |
| Shop | web.php | `/` | `web` |
| Shop | api.php | `/api/v1` | `api` |
| Settings | admin.php | `/admin/settings` | `web`, `auth`, `is_admin` |
| Sales | admin.php | `/admin/sales` | `web`, `auth`, `is_admin` |
| Customer | customer.php | `/admin/customers` | `web`, `auth`, `is_admin` |
| CMS | admin.php | `/admin/cms` | `web`, `auth`, `is_admin` |
| CMS | web.php | `/{slug}` | `web` (catch-all for pages) |
| System | system.php | `/admin/system` | `web`, `auth`, `is_admin` |
| Reports | admin.php | `/admin/reports` | `web`, `auth`, `is_admin` |
| Marketing | admin.php | `/admin/marketing` | `web`, `auth`, `is_admin` |
| Marketing | shop.php | `/` | `web` (coupon application) |
| API | api.php | `/api/v1` | `api` (some routes auth, some public) |
| Setup | setup.php | `/setup` | `web` |
| Stripe | admin.php + web.php | varies | gateway-specific |

### Auth Guards

| Guard | Purpose | Session |
|-------|---------|---------|
| `web` (default) | Customer / user authentication | Cookie-based |
| `admin` | Admin panel access | Cookie-based |
| `sanctum` | API token auth | Bearer token |

### Key Middleware

| Middleware | Location | Purpose |
|-----------|----------|---------|
| `HandleInertiaRequests` | `app/Http/Middleware/` | Shares auth, menu, currency, flash, theme, sidebar state to all Inertia pages |
| `HandleAppearance` | `app/Http/Middleware/` | Dark/light mode handling |
| `IsAdmin` | `Cartxis\Admin` | Verifies admin guard |
| `PreventAdminFrontendAccess` | `Cartxis\Admin` | Blocks admin users from customer frontend |
| `PreventUserAdminAccess` | `Cartxis\Admin` | Blocks customers from admin panel |
| `RedirectIfAdminAuthenticated` | `Cartxis\Admin` | Redirects authenticated admins from login |
| `ShareFrontendData` | `Cartxis\Shop` | Shares storefront-specific data |
| `ThemeInertiaResponseFactory` | `Cartxis\Core` | Theme-aware Inertia responses |
| `TrackApiSync` | `Cartxis\API` | Auto-updates API connectivity status on auth requests |

---

## 8. Frontend Architecture

### Entry Points

| File | Purpose |
|------|---------|
| `resources/js/app.ts` | Client-side Inertia app — Vue 3 + Pinia + theme resolution |
| `resources/js/ssr.ts` | Server-side rendering entry (Node cluster mode) |
| `resources/admin/css/styles.css` | Admin-specific styles |

### Inertia Page Resolution

```typescript
// In app.ts — theme-aware page resolution
resolve: (name) => {
    if (name.startsWith('themes/')) {
        // Theme page: "themes/cartxis-default/pages/Home"
        // Resolves from: ../../themes/{slug}/resources/views/{path}.vue
        return resolvePageComponent(
            `../../themes/${themeSlug}/resources/views/${componentPath}.vue`,
            import.meta.glob('../../themes/**/resources/views/**/*.vue'),
        );
    }
    // Default: resolves from ./pages/{name}.vue
    return resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue'));
}
```

### Vite Path Aliases

| Alias | Maps To |
|-------|---------|
| `@` | `/resources/js` |
| `@/Layouts` | `/resources/js/layouts` |
| `@admin` | `/resources/admin` |
| `@themes` | `/themes` |

### Page Directory Structure (`resources/js/pages/`)

```
pages/
├── Admin/
│   ├── Attributes/         # Attribute management
│   ├── Auth/               # Admin login
│   ├── Brands/             # Brand management
│   ├── CMS/                # Pages, Blocks, Media Library
│   ├── Categories/         # Category management
│   ├── Content/            # Content management
│   ├── Customer/           # Customer & groups
│   ├── Dashboard/          # Admin dashboard
│   ├── Marketing/          # Coupons & promotions
│   ├── PaymentMethods/     # Payment method config
│   ├── Products/           # Product CRUD (4 types)
│   ├── Profile/            # Admin profile
│   ├── Reports/            # Sales/product/customer reports
│   ├── Reviews/            # Review moderation
│   ├── Sales/              # Orders, invoices, shipments, credit memos, transactions
│   ├── Settings/           # All settings pages
│   └── System/             # Cache, extensions, permissions, maintenance, backups
├── Setup/                  # First-run wizard (Welcome → BusinessType → Settings → DemoData → Finish)
├── auth/                   # Customer auth (login, register, forgot/reset password, 2FA, verify email)
├── settings/               # Customer settings (profile, password, appearance, 2FA)
└── Wishlist.vue
```

### Composables (`resources/js/composables/`)

| Composable | Purpose |
|------------|---------|
| `useAppearance` | Dark/light mode handling with system preference detection |
| `useCart` | Cart operations (add, remove, update, clear) via Pinia |
| `useCurrency` | Currency formatting with symbol position, decimal places |
| `useInitials` | Generate user initials from name |
| `useMenuIcons` | Map menu item icons to Lucide components |
| `useStorefrontMenu` | Build storefront navigation from menu data |
| `useTwoFactorAuth` | 2FA setup/verification flow |
| `useWishlist` | Wishlist add/remove/check operations |

### UI Component Library

The project uses **shadcn-vue** with reka-ui primitives. Components live in `resources/js/components/ui/`:

alert, avatar, badge, breadcrumb, button, card, checkbox, collapsible, dialog, dropdown-menu, input, label, navigation-menu, pin-input, separator, sheet, sidebar, skeleton, textarea, tooltip

Configuration in `components.json` — style: default, baseColor: neutral, CSS variables enabled, icon library: lucide.

### Layouts

| Layout | Usage |
|--------|-------|
| `AdminLayout.vue` | All admin panel pages |
| `AppLayout.vue` | Customer-facing authenticated pages |
| `AuthLayout.vue` | Login, register, forgot password pages |

### State Management

| Store | File | Purpose |
|-------|------|---------|
| Cart Store | `Stores/cartStore.ts` | Cart state, item management, totals |

### Shared Inertia Props

`HandleInertiaRequests` middleware shares these props to **all** pages:

| Prop | Type | Content |
|------|------|---------|
| `name` | string | App name from config |
| `appVersion` | string | App version from config |
| `auth.user` | object\|null | Authenticated user |
| `adminConfig` | object\|null | Admin logo + site name (admin routes only) |
| `menu.admin` | array | Admin sidebar menu tree |
| `menu.shop` | array | Shop navigation menu tree |
| `flash` | object | Session flash messages (success, error, warning, info, payment_response) |
| `sidebarOpen` | boolean | Sidebar collapse state (from cookie) |
| `currency` | object\|null | Default currency config (code, symbol, symbolPosition, decimalPlaces) |
| `ziggy.location` | string | Current URL for routing |

---

## 9. Theme System

### Structure

```
themes/cartxis-default/
├── theme.json              # Manifest — name, slug, version, supports, settings
├── hooks.php               # Theme lifecycle hooks
├── config/                 # Theme configuration
├── screenshot.png          # Theme preview image
└── resources/views/
    ├── layouts/            # ThemeLayout.vue
    ├── components/         # Theme-specific components (7)
    │   ├── CartIcon.vue
    │   ├── CartItemSkeleton.vue
    │   ├── ProductCard.vue
    │   ├── ProductSkeleton.vue
    │   ├── QuickViewModal.vue
    │   ├── ThemeFooter.vue
    │   └── ThemeHeader.vue
    └── pages/              # Theme page overrides (10 sections)
        ├── Account/        # Dashboard, Orders, Addresses, Profile, Wishlist
        ├── Auth/           # Login, Register
        ├── CMS/            # Dynamic CMS pages
        ├── Cart/           # Shopping cart
        ├── Category/       # Category listing
        ├── Checkout/       # Checkout flow
        ├── Home/           # Homepage
        ├── Products/       # Product detail
        ├── Search/         # Search results
        └── Stripe/         # Stripe payment pages
```

### theme.json

```json
{
    "name": "Cartxis Default",
    "slug": "cartxis-default",
    "version": "1.0.0",
    "supports": ["widgets", "menus", "custom-logo", "custom-colors", "responsive", "dark-mode"],
    "settings": {
        "colors": { "primary": "#3b82f6", "secondary": "#8b5cf6", ... },
        "typography": { "font_family": "Inter, sans-serif" },
        "layout": { "container_width": "1280px" },
        "features": { "sticky_header": true, "wishlist": true, "quick_view": true, ... }
    }
}
```

### How Theme Resolution Works

1. **Backend** — Controller returns `Inertia::render('themes/cartxis-default/pages/Home')`.
2. **Vite** — `app.ts` detects the `themes/` prefix and resolves from `../../themes/{slug}/resources/views/{path}.vue` via glob import.
3. **Fallback** — Pages without `themes/` prefix resolve from `resources/js/pages/`.

### Creating a Theme

1. Create `themes/{slug}/` directory.
2. Add `theme.json` manifest.
3. Add `resources/views/` with layouts, components, and pages.
4. Register theme in admin (Settings → Themes).

---

## 10. Extension System

### Architecture

Extensions are database-backed plugins with a filesystem discovery mechanism. There are two sources:

| Source | Path | Example |
|--------|------|---------|
| **Bundled** (first-party) | `packages/Cartxis/*/extension.json` | Payment gateways |
| **Filesystem** (third-party) | `extensions/*/extension.json` | User-installed |

### Extension Lifecycle

```
Discoverable → Installed → Active
     ↑              ↑          ↑
  Filesystem    extensions   ServiceProvider
  scanning     DB record     dynamically loaded
```

### extension.json Manifest

```json
{
    "code": "stripe",
    "name": "Stripe Payment Gateway",
    "version": "1.0.0",
    "description": "Accept payments via Stripe",
    "author": "Cartxis Team",
    "icon": "credit-card",
    "requires": [],
    "provider": "Cartxis\\Stripe\\Providers\\StripeServiceProvider",
    "provider_file": "src/Providers/StripeServiceProvider.php",
    "category": "payment-gateway"
}
```

### Key Fields

| Field | Purpose |
|-------|---------|
| `code` | Unique identifier — must match DB extension record |
| `provider` | Fully-qualified ServiceProvider class name |
| `provider_file` | Fallback file path if autoloading isn't available |
| `requires` | Array of extension codes this extension depends on |
| `category` | Classification (e.g., `payment-gateway`, `shipping`, `analytics`) |

### Extension Discovery Flow

1. `ExtensionService::discover()` scans both `extensions/` and `packages/Cartxis/` for `extension.json` files.
2. Validated manifests are matched against the `extensions` database table.
3. During boot, `CoreServiceProvider::bootExtensions()` loads the ServiceProvider of every **active** extension.
4. Bundled extensions use PSR-4 autoloading; filesystem extensions use `require_once` on `provider_file`.

### Extension Template

An extension template is provided at `extension/templates/payment-gateway/` with scaffold files for creating new payment gateway extensions.

---

## 11. Payment Gateway Pattern

Payment gateways implement `Cartxis\Core\Contracts\PaymentGatewayInterface`:

```php
interface PaymentGatewayInterface
{
    public function getCode(): string;
    public function getName(): string;
    public function supports(string $paymentMethod): bool;
    public function processPayment(Order $order, array $data = []);
    public function handleCallback(array $data): array;
    public function verifyPayment(Order $order): bool;
    public function refund(Order $order, ?float $amount = null, ?string $reason = null): array;
    public function getConfigFields(): array;
    public function isConfigured(): bool;
}
```

### Registration Flow

1. Gateway package implements `PaymentGatewayInterface` in its Service class.
2. Gateway's `ServiceProvider::boot()` registers itself with `PaymentGatewayManager`:
   ```php
   $gatewayManager = app('vortex.payment.gateway');
   $gatewayManager->register(new StripeGateway());
   ```
3. Gateway seeds a record into the `payment_methods` table.
4. Gateway checks `extension.json` active status before booting.

### Active Payment Gateways

| Gateway | Config Keys |
|---------|-------------|
| Stripe | `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` |
| RazorPay | `RAZORPAY_KEY`, `RAZORPAY_SECRET` |
| PayPal | `PAYPAL_CLIENT_ID`, `PAYPAL_SECRET` |
| PayUMoney | `PAYU_MERCHANT_KEY`, `PAYU_SALT` |
| PhonePe | `PHONEPE_MERCHANT_ID`, `PHONEPE_SALT_KEY` |

---

## 12. Database & Migrations

### Migration Distribution

| Source | Count | Location |
|--------|-------|----------|
| App-level | 5 | `database/migrations/` |
| Package-level | ~72 | `packages/Cartxis/*/Database/Migrations/` |
| **Total** | **~77** | — |

App-level migrations handle the User model and framework tables (cache, jobs). All domain tables are defined in their respective package migrations.

### Key Tables by Package

| Package | Main Tables |
|---------|-------------|
| Core | channels, currencies, email_configurations, email_templates, extensions, locales, menu_items, payment_methods, permissions, roles, settings, shipping_methods, shipping_rates, tax_classes, tax_rates, tax_rules, tax_zones, themes, theme_settings |
| Product | products, categories, attributes, attribute_options, brands, product_images, product_reviews, product_attribute_values, inventory_adjustments |
| Cart | carts, cart_items |
| Shop | addresses, orders, order_items |
| Sales | invoices, credit_memos, credit_memo_items, shipments, shipment_items, transactions, order_histories |
| Customer | customers, customer_addresses, customer_groups, customer_notes, wishlists |
| CMS | pages, blocks, media_files, media_folders, media_usages |
| Marketing | coupons, coupon_usages, promotions |
| System | maintenance_logs |
| Reports | (uses views/queries on existing tables) |

### Seeders

| Location | Seeders |
|----------|---------|
| `database/seeders/` | DatabaseSeeder, MobileHeroBannersSeeder, TestUserSeeder, TransactionSeeder |
| Package seeders | Each package contributes seeders via `packages/Cartxis/*/Database/Seeders/` |

---

## 13. Design Patterns

### 1. Repository Pattern (with Contracts)

Used in **Shop** (fully with interfaces), **CMS**, **Sales**, **Reports**, **Marketing**.

```
Contracts/ProductRepositoryInterface  ← bound in ServiceProvider
    ↓
Repositories/ProductRepository       ← concrete implementation
    ↓
Services/ProductService              ← injects interface, not implementation
```

**Shop** registers repositories as singletons in its ServiceProvider with explicit interface bindings:
```php
$this->app->singleton(
    ProductRepositoryInterface::class,
    ProductRepository::class
);
```

### 2. Service Layer

Every package with business logic has a `Services/` directory. Services encapsulate domain operations and are registered as singletons with constructor injection:

```php
$this->app->singleton(CheckoutService::class, function ($app) {
    return new CheckoutService(
        $app->make(OrderRepositoryInterface::class)
    );
});
```

### 3. Singleton Container Bindings

Core services use `vortex.*` naming convention, bound via `$this->app->singleton()` with companion class bindings:

```php
$this->app->singleton('vortex.theme', fn ($app) => new ThemeService());
$this->app->bind(ThemeService::class, fn ($app) => $app->make('vortex.theme'));
```

### 4. Facade Pattern

Core provides 4 facades (Extension, Hook, Menu, Setting) that resolve `vortex.*` singletons.

### 5. Extension/Plugin Architecture

Database-backed with filesystem discovery. Extensions follow an explicit lifecycle (discover → install → activate) and dynamically register their ServiceProviders.

### 6. Guard-Based Authentication

Separate `admin` and `web` guards with dedicated middleware layers. Admin routes require the `is_admin` middleware. API uses Sanctum bearer tokens.

### 7. Theme Override Pattern

Themes can override any storefront page by providing a Vue component at the same path. The Inertia resolver checks the theme path first.

### 8. Inertia SPA with Server-Side Routing

All page navigation goes through Laravel routes → controllers return `Inertia::render()` → Vue pages render client-side (or SSR). No client-side router — routing is fully server-driven.

---

## 14. Configuration

### Laravel Config (`config/`)

| File | Purpose |
|------|---------|
| `app.php` | App name, URL, timezone, version (`APP_VERSION`) |
| `auth.php` | Guards (web, admin), providers, password resets |
| `database.php` | MySQL connection config |
| `inertia.php` | SSR enabled (`127.0.0.1:13714`), page paths, extensions |
| `fortify.php` | Fortify features (registration, password reset, 2FA) |
| `sanctum.php` | Sanctum token expiration, middleware |
| `session.php` | Session driver, lifetime |
| `mail.php` | Mail driver config |
| `filesystems.php` | Disk config (local, public, S3 via AWS SDK) |
| `cache.php` | Cache drivers |
| `queue.php` | Queue connections |
| `logging.php` | Log channels |
| `services.php` | Third-party service credentials |

### Package Configs

| Package | Config Key | Key Settings |
|---------|-----------|-------------|
| Core | `core` | extensions_path, bundled_extensions_path |
| Admin | `admin` | Admin-specific settings |
| Shop | `shop` | Homepage settings, featured products count, hero block config |
| API | `vortex-api` | Rate limits (60 guest / 300 auth / 10 payment / 5 login per min), token expiry (24hr), pagination (20/page, max 100), feature flags, upload limits, search config, cache TTLs |
| Stripe | `stripe` | Stripe API keys, webhook secret |

### API Feature Flags (`config('vortex-api.features')`)

| Flag | Status |
|------|--------|
| `wishlist` | ✅ Enabled |
| `reviews` | ✅ Enabled |
| `coupons` | ✅ Enabled |
| `gift_cards` | ❌ Disabled |
| `loyalty_points` | ❌ Disabled |

---

## 15. Testing

### Framework

**Pest v4** with Laravel plugin. Config in `phpunit.xml`.

### Test Structure

```
tests/
├── Pest.php              # Pest config & test helpers
├── TestCase.php          # Base test case
├── Feature/
│   ├── Auth/             # Authentication tests (7 files)
│   ├── Settings/         # Settings tests (3 files)
│   ├── Customer/         # CustomerAddressTest
│   ├── DashboardTest.php
│   └── ExampleTest.php
└── Unit/
    └── ExampleTest.php
```

Additional package-specific tests exist in `packages/Cartxis/Shop/Tests/` and `packages/Cartxis/Reports/src/Tests/`.

### Test Environment

| Setting | Value |
|---------|-------|
| DB | MySQL `cartxis` (127.0.0.1:3306) |
| Cache | array |
| Mail | array |
| Session | array |
| Queue | sync |

### Running Tests

```bash
# Via composer script (clears config first)
composer test

# Direct Pest
./vendor/bin/pest

# Specific suite
./vendor/bin/pest --testsuite=Feature
./vendor/bin/pest --testsuite=Unit
```

---

## 16. CLI Commands

### Composer Scripts

| Command | Action |
|---------|--------|
| `composer setup` | Full install: composer install, .env copy, key:generate, migrate, npm install/build |
| `composer dev` | Concurrently runs: `php artisan serve` + `queue:listen` + `npm run dev` |
| `composer dev:ssr` | Runs serve + queue + pail + Inertia SSR |
| `composer test` | `config:clear` + `pest` |

### NPM Scripts

| Command | Action |
|---------|--------|
| `npm run dev` | Vite dev server (HMR) |
| `npm run build` | Production build |
| `npm run build:ssr` | Production build + SSR bundle |
| `npm run lint` | ESLint fix |
| `npm run format` | Prettier format resources/ |
| `npm run format:check` | Prettier check (CI) |

### Artisan Extension Commands

| Command | Purpose |
|---------|---------|
| `php artisan cartxis:extensions:list` | Show all discovered extensions |
| `php artisan cartxis:extensions:sync` | Sync filesystem to database |
| `php artisan cartxis:extensions:install {code}` | Install an extension |
| `php artisan cartxis:extensions:activate {code}` | Activate an extension |
| `php artisan cartxis:extensions:deactivate {code}` | Deactivate an extension |
| `php artisan cartxis:extensions:uninstall {code}` | Remove an extension |

---

## 17. Development Workflow

### Initial Setup

```bash
git clone <repo-url> cartxis
cd cartxis
composer setup
# Configure .env (APP_URL, DB credentials, payment gateway keys)
```

### Daily Development

```bash
composer dev
# → http://localhost:8000 (Laravel)
# → http://localhost:5173 (Vite HMR)
# → Queue worker active
```

### Adding a New Package

1. Create directory: `packages/Cartxis/NewPackage/`
2. Add ServiceProvider in `Providers/`
3. Add namespace to `composer.json` PSR-4 autoload
4. Register provider in `bootstrap/providers.php`
5. Run `composer dump-autoload`
6. Add routes, models, controllers, migrations as needed

### Adding a Payment Gateway

1. Copy template from `extension/templates/payment-gateway/`
2. Implement `PaymentGatewayInterface`
3. Create `extension.json` manifest
4. Register gateway in ServiceProvider with `PaymentGatewayManager`
5. Seed payment method record
6. Add admin config routes for managing API keys
7. Install and activate via CLI: `php artisan cartxis:extensions:install {code} && php artisan cartxis:extensions:activate {code}`

### Creating a Theme

1. Create `themes/{slug}/` with `theme.json`
2. Add `resources/views/layouts/ThemeLayout.vue`
3. Override pages in `resources/views/pages/`
4. Add theme-specific components in `resources/views/components/`
5. Activate in Admin → Settings → Themes

### Code Quality

```bash
# PHP formatting
./vendor/bin/pint

# JS/Vue linting + formatting
npm run lint
npm run format

# Type checking
npx vue-tsc --noEmit

# Tests
composer test
```

### Namespace Reference

| Namespace | Path |
|-----------|------|
| `App\` | `app/` |
| `Cartxis\Core\` | `packages/Cartxis/Core/` |
| `Cartxis\Admin\` | `packages/Cartxis/Admin/` |
| `Cartxis\Product\` | `packages/Cartxis/Product/` |
| `Cartxis\Cart\` | `packages/Cartxis/Cart/` |
| `Cartxis\Shop\` | `packages/Cartxis/Shop/` |
| `Cartxis\Settings\` | `packages/Cartxis/Settings/` |
| `Cartxis\Sales\` | `packages/Cartxis/Sales/src/` |
| `Cartxis\Customer\` | `packages/Cartxis/Customer/src/` |
| `Cartxis\CMS\` | `packages/Cartxis/CMS/src/` |
| `Cartxis\System\` | `packages/Cartxis/System/src/` |
| `Cartxis\Reports\` | `packages/Cartxis/Reports/src/` |
| `Cartxis\Marketing\` | `packages/Cartxis/Marketing/src/` |
| `Cartxis\API\` | `packages/Cartxis/API/` |
| `Cartxis\Setup\` | `packages/Cartxis/Setup/src/` |
| `Cartxis\Stripe\` | `packages/Cartxis/Stripe/src/` |
| `Cartxis\Razorpay\` | `packages/Cartxis/Razorpay/src/` |
| `Cartxis\PayPal\` | `packages/Cartxis/PayPal/src/` |
| `Cartxis\PayUMoney\` | `packages/Cartxis/PayUMoney/src/` |
| `Cartxis\PhonePe\` | `packages/Cartxis/PhonePe/src/` |
| `Tests\` | `tests/` |

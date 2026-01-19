# Cartxis Extension Development Guide

This guide explains how to create a Cartxis extension (plugin) that can be installed/activated/deactivated via the `extensions` table.

Cartxis supports two extension sources:

1. **Bundled extensions** (first-party): discovered from `packages/Cartxis/*/extension.json`.
2. **Filesystem extensions** (third-party / custom): discovered from `extensions/<your-extension>/extension.json`.

The extension system is backed by the database table **`extensions`** and a JSON manifest file **`extension.json`**.

---

## 1) Concepts

### Extension manifest (`extension.json`)
An extension is “discoverable” if it has an `extension.json` file with at least:

- `code` (unique)
- `name`
- `version`

Optional but recommended:

- `description`, `author`, `author_url`, `icon`
- `requires` (dependencies)
- `provider` (Laravel Service Provider class)
- `provider_file` (path to provider PHP file; required for filesystem extensions unless you have Composer autoload)
- `category` (e.g., `payment-gateway`, `shipping`, `theme`, etc.)

### Activation model
- **Discoverable**: present in filesystem with a valid manifest.
- **Installed**: present in the `extensions` table with `installed = true`.
- **Active**: `active = true` in `extensions` table.

Only **active** extensions should register routes, gateways, hooks, menus, etc.

---

## 2) Where to put your extension

### Option A — Third-party/custom extension (recommended)
Create a new folder under:

- `extensions/<vendor-or-extension-name>/`

Example:

- `extensions/acme-payments/`

### Option B — Bundled/first-party extension
Add a new package folder under:

- `packages/Cartxis/<ExtensionName>/`

Bundled packages are discovered automatically via their `extension.json`.

---

## 3) Minimum folder structure (filesystem extension)

Example:

```
extensions/acme-example-gateway/
  extension.json
  src/
    Providers/
      AcmeExampleServiceProvider.php
    Services/
      AcmeExampleGateway.php
```

---

## 4) Example `extension.json`

For filesystem extensions, include `provider_file` so Cartxis can `require_once` your provider without Composer autoload.

```json
{
  "code": "acme-example-gateway",
  "name": "Acme Example Gateway",
  "description": "Example payment gateway extension.",
  "version": "1.0.0",
  "author": "Acme Inc.",
  "author_url": "https://acme.test",
  "icon": "credit-card",
  "category": "payment-gateway",
  "requires": {
    "php": "^8.2",
    "cartxis/core": "^1.0"
  },
  "provider": "Acme\\ExampleGateway\\Providers\\AcmeExampleServiceProvider",
  "provider_file": "src/Providers/AcmeExampleServiceProvider.php"
}
```

Notes:
- `code` must be unique. Prefer `vendor-name.extension-name` or `vendor-extension-name`.
- `provider` is the fully-qualified class name (FQCN).
- `provider_file` is relative to the extension folder.

---

## 5) Provider: how your extension boots

Your service provider is where you register anything your extension needs.

Best practice: 
- Always keep **seeding / lightweight checks** safe.
- Only register routes/gateways/hooks if the extension is active.

Cartxis already gates bundled Stripe/Razorpay by the `extensions` table. For third-party extensions, you should do the same.

Typical provider responsibilities:
- Register your gateway into `Cartxis\Core\Services\PaymentGatewayManager`
- Load your routes
- Load your migrations
- Register hooks/menus
- Seed a `payment_methods` record (if you are a payment gateway)

---

## 6) Payment gateway extensions

Cartxis uses:
- `Cartxis\Core\Contracts\PaymentGatewayInterface` for gateway implementations
- `Cartxis\Core\Services\PaymentGatewayManager` as the registry
- `payment_methods` table for admin configuration (API keys, enabled/disabled, etc.)

A typical gateway extension:
1. Adds a row in `payment_methods` (e.g. `code = acme_gateway`)
2. Implements `PaymentGatewayInterface`
3. Registers itself with `PaymentGatewayManager`
4. Checkout uses the order’s `payment_method` to select the gateway

---

## 7) Managing extensions (CLI)

These commands are available:

- `php artisan cartxis:extensions:list`
- `php artisan cartxis:extensions:sync`
- `php artisan cartxis:extensions:install <code>`
- `php artisan cartxis:extensions:activate <code>`
- `php artisan cartxis:extensions:deactivate <code>`
- `php artisan cartxis:extensions:uninstall <code>`

Recommended workflow (filesystem extension):

1. Place your extension folder under `extensions/`.
2. Run `php artisan cartxis:extensions:sync`.
3. Run `php artisan cartxis:extensions:install <code>`.
4. Run `php artisan cartxis:extensions:activate <code>`.

---

## 8) Template

A working template is included here:

- `extension/templates/payment-gateway/`

Copy it into `extensions/` and rename the namespace + code.

---

## 9) Common pitfalls

- **Forgetting `provider_file`** for filesystem extensions: provider won’t load unless your code is autoloaded by Composer.
- **Mismatch between extension code and payment method code**:
  - Extension `code` is used by the extensions system (install/activate).
  - Payment method `code` is used at checkout (order.payment_method).
  These are related but not required to be identical.
- **Booting routes/gateways even when disabled**: always gate with `extensions.active`.

---

## 10) Quick checklist

- [ ] `extension.json` present and valid
- [ ] `provider` class exists
- [ ] `provider_file` present (filesystem extension)
- [ ] Extension synced/installed/active in DB
- [ ] Gateway registered in `PaymentGatewayManager`
- [ ] Payment method seeded in `payment_methods`

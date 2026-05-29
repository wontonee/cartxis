# Cartxis Upgrade Guide

This document covers platform upgrades for existing Cartxis installations.

## Laravel 13 (Cartxis 1.1+)

**Released:** March 2026  
**Minimum PHP:** 8.3 (PHP 8.2 is no longer supported)

### Requirements

| Component | Version |
|-----------|---------|
| PHP | 8.3 or higher |
| Composer | 2.x |
| Node.js | 18.x or higher |
| MySQL | 8.0 or higher |

### Upgrade steps

1. **Upgrade PHP** to 8.3+ on your server or local environment.
2. **Back up** your database and `storage/` directory.
3. Pull the latest Cartxis release (or merge the `upgrade/laravel-13` branch).
4. Update dependencies:

```bash
composer install
npm install
npm run build
php artisan migrate
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

5. Verify the application:

```bash
php artisan about
php artisan route:list
```

### Dependency changes

| Package | From | To |
|---------|------|-----|
| `laravel/framework` | ^12.0 | ^13.0 |
| `laravel/tinker` | ^2.x | ^3.0 |
| `mews/purifier` | ^3.4 | ^3.4.4+ |
| `spatie/laravel-backup` | ^9.4 | ^10.0 |

### Breaking changes to review

#### PHP 8.3 required

Laravel 13 drops PHP 8.2. Update hosting, Docker images, and CI before deploying.

#### CSRF middleware rename

Laravel renamed `VerifyCsrfToken` / `ValidateCsrfToken` to `PreventRequestForgery`. Cartxis updates `config/sanctum.php` accordingly. If you customized middleware exclusions in tests or routes, use:

```php
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
```

#### Cache `serializable_classes`

Laravel 13 adds `serializable_classes => false` in `config/cache.php` to harden cache unserialization. If you store PHP objects in cache, allow-list those classes explicitly.

#### Session / cache key prefixes

Laravel 13 changes default cache and session cookie name patterns. Cartxis keeps explicit `CACHE_PREFIX` and `SESSION_COOKIE` defaults in config for backward compatibility with existing sessions.

#### Spatie Laravel Backup 10

Admin → System backups use `spatie/laravel-backup` v10. Re-publish config only if you customized `config/backup.php`:

```bash
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider" --tag=backup-config
```

Compare your changes with the new defaults before overwriting.

### Extension and theme authors

- Require `php: ^8.3` and `laravel/framework: ^13.0` in extension `composer.json` / `extension.json`.
- Avoid deprecated CSRF middleware class names in custom Sanctum or route middleware config.
- Re-run `php artisan cartxis:extension:sync` after upgrading.

### Rollback

If you need to roll back:

1. Restore your database and files backup.
2. Check out the previous Cartxis tag or branch.
3. Run `composer install` and `npm run build` against the older lock file.

---

## Laravel 12

Cartxis 1.0.x targets Laravel 12 with PHP 8.2+. See [Laravel 12 upgrade guide](https://laravel.com/docs/12.x/upgrade) for framework-level changes when upgrading from Laravel 11 or earlier.

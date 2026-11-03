# 1.0.18 — Locale catalog on upgrade (patch)

Follow-up to 1.0.17: existing stores that only ran `php artisan migrate` now get ME / Europe / Africa locale rows via migration `2026_11_03_000001_seed_i18n_locale_catalog` (does not reset store default / active flags). `LocaleSeeder` is safe to re-run.

Upgrade:

```bash
php artisan migrate
php artisan cache:clear
```

---

# 1.0.17 — Multi-language support

Cartxis 1.0.17 ships first-class internationalization for storefront, admin, API, and extensions.

## Highlights

- **15+ language packs** covering the Middle East, Europe, and Africa (`en`, `ar`, `fr`, `de`, `es`, `pt`, `tr`, `sw` full; others partial with English fallback)
- **RTL** for Arabic, Hebrew, and Persian (`dir=rtl` on chrome + checkout)
- Storefront and admin **language switcher** (cookie + session `cartxis_locale`)
- **Product & category translations** in admin; storefront resolves by active locale
- Mobile / API: `GET /api/v1/locales`, `Accept-Language`, `X-Locale`, `?locale=`
- **Extension language packs**: ship `resources/locales/{code}.json` under `extensions.{snake_code}.*` — merged by `LocaleService` (see [I18N.md](./I18N.md#extension-language-packs))

## Merchant setup

1. After upgrading, run `php artisan migrate` (seeds the locale catalog if missing)
2. Settings → Locales — activate languages and set the store default
3. Use the header language switcher (or visit `/locale/{code}`)
4. Translate products/categories in the admin Translations panel
5. Translate CMS / UI Editor / menu copy in the language you author them

> **Note:** 1.0.17 shipped the UI packs + `LocaleSeeder` for new installs. Existing
> stores that only pulled files without a catalog migration may still show only
> English until they migrate (patch includes `seed_i18n_locale_catalog`) or run:
> `php artisan db:seed --class=Cartxis\\Core\\Database\\Seeders\\LocaleSeeder`

## Developers / extension authors

```bash
# Extension scaffold includes resources/locales/en.json
php artisan cartxis:extension:make "My Feature"
```

Vue: `t('extensions.my_feature.settings')`  
Filter hook: `add_filter('cartxis.i18n.messages', ...)`.

Full guide: [I18N.md](./I18N.md).

## Shared hosting

Download **`cartxis-1.0.17-shared-hosting.zip`** from GitHub Releases (includes `public/build`). No Node required on the server — locale JSON for core is baked into the Vite build; extension packs load at runtime via PHP.

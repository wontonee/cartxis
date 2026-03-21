# Codebase Cleanup & Architecture Fix Plan

> Generated: March 7, 2026  
> Based on full dead-code and architecture audit of the Cartxis monorepo.

---

## Background

During a routes architecture fix (moving shop API endpoints from the core `routes/api.php` into the Shop package's own `packages/Cartxis/Shop/src/Routes/api.php`), a full application audit was also performed. The audit identified **14 items** spanning critical runtime bugs, dead frontend files, dead backend code, and architectural issues.

---

## Completed Work

| Item | Description | Status |
|------|-------------|--------|
| Routes architecture | Moved all 4 shop endpoints from `routes/api.php` → `packages/Cartxis/Shop/src/Routes/api.php` under `/api/shop/` prefix with `throttle:60,1` | ✅ Done |
| Vue components | Updated `ProductsGridBlock.vue` and `CategoriesGridBlock.vue` to use `/api/shop/*` URLs | ✅ Done |
| **#1** Critical bug fix | Moved `resources/admin/Pages/Users/Index.vue` + `Edit.vue` → `resources/js/pages/Admin/Users/`; fixed `@/Layouts` → `@/layouts` imports | ✅ Done |
| **#2** Duplicate Themes pages | Deleted `resources/admin/Pages/Themes/Index.vue` + `Settings.vue` | ✅ Done |
| **#3** PlaceholderPattern.vue | Deleted `resources/js/components/PlaceholderPattern.vue` | ✅ Done |
| **#4** Stale `.backup` files | Deleted 3 `.backup` files in StoreConfiguration and Coupons | ✅ Done |
| **#5** Orphaned Vite CSS entry | Removed `resources/admin/css/styles.css` from `vite.config.ts` input; deleted the file; removed dead `import` from `Login.vue` | ✅ Done |
| **#9** Empty Product `web.php` | Deleted `packages/Cartxis/Product/src/Routes/web.php`; removed `loadRoutesFrom` call from `ProductServiceProvider` | ✅ Done |
| **#11** `@/Layouts` alias casing | Removed the `^@\/Layouts` regex alias from `vite.config.ts`; all active files already use lowercase `@/layouts` | ✅ Done |
| **#12** Demo images in git | Added `/resources/admin/media` to `.gitignore` | ✅ Done |

---

## Audit Results — 14 Items

### 🔴 Priority 1 — Critical Bug (Breaks Runtime)

#### #1 — Users pages missing — `/admin/users` returns 500

- **Problem:** `UserController` calls `Inertia::render('Admin/Users/Index')`, which Inertia resolves to `resources/js/pages/Admin/Users/Index.vue`. That file **does not exist**. The actual files are in `resources/admin/Pages/Users/` — a directory outside the Inertia resolver's glob paths.
- **Files:** `resources/admin/Pages/Users/Index.vue`, `resources/admin/Pages/Users/Edit.vue`
- **Fix:** Move both files to `resources/js/pages/Admin/Users/`

---

### 🔴 Priority 2 — Dead Frontend Files (Simple Deletions)

#### #2 — Duplicate Themes pages (never served)

- **Problem:** `resources/admin/Pages/Themes/Index.vue` and `Settings.vue` exist outside Inertia's resolver paths and duplicate the working pages at `resources/js/pages/Admin/Themes/`.
- **Files:** `resources/admin/Pages/Themes/Index.vue`, `resources/admin/Pages/Themes/Settings.vue`
- **Fix:** Delete both files.

#### #3 — `PlaceholderPattern.vue` — zero imports anywhere

- **Problem:** Component exists but is never imported or used in any file.
- **File:** `resources/js/components/PlaceholderPattern.vue`
- **Fix:** Delete the file.

#### #4 — Three stale `.backup` files

- **Problem:** Leftover backup files committed to the repo.
- **Files:**
  - `resources/js/pages/Admin/Settings/StoreConfiguration/Index.vue.backup`
  - `resources/js/pages/Admin/Marketing/Coupons/Edit.vue.backup`
  - `resources/js/pages/Admin/Marketing/Coupons/Create.vue.backup`
- **Fix:** Delete all three.

---

### 🟠 Priority 3 — Dead/Broken CSS & Vite Entry

#### #5 — `resources/admin/css/styles.css` — orphaned Vite entry

- **Problem:** CSS file defines classes (`.admin-card`, `.admin-input`, `.admin-btn`, etc.) that are used **nowhere** in the codebase. It is listed as a standalone entry in `vite.config.ts` (line 12), generating a CSS bundle that no Blade layout loads. The only reference is a JS `import` inside `Login.vue`, which could use `resources/css/app.css` instead.
- **Files:** `resources/admin/css/styles.css`, `vite.config.ts`
- **Fix:** Remove the Vite entry from `vite.config.ts`. Delete the CSS file or migrate any needed rules to `resources/css/app.css`.

---

### 🟠 Priority 4 — Dead Backend (PHP) — Decisions Required

#### #6 — CMS Block backend orphaned

- **Problem:** `Block.php`, `BlockRepository.php`, and `BlockService.php` are registered as singletons in `CMSServiceProvider`, and a `@block()` Blade directive is wired up. However, no admin controller or CRUD routes exist — blocks cannot be created or managed through the UI.
- **Files:**
  - `packages/Cartxis/CMS/src/Models/Block.php`
  - `packages/Cartxis/CMS/src/Repositories/BlockRepository.php`
  - `packages/Cartxis/CMS/src/Services/BlockService.php`
- **Decision Required:**
  - **Option A (Restore):** Add `BlocksController` + admin routes to bring the feature back.
  - **Option B (Remove):** Delete model, repository, service, and associated migrations; remove singleton bindings from `CMSServiceProvider`.

#### #7 — `SearchController` unreachable

- **Problem:** `packages/Cartxis/Shop/src/Http/Controllers/Api/SearchController.php` exists, but both routes pointing to it are commented out in the Shop `api.php`.
- **Decision Required:**
  - **Option A:** Uncomment the routes to enable search.
  - **Option B:** Delete the controller.

#### #8 — Wishlist API unreachable

- **Problem:** Wishlist CRUD routes in `packages/Cartxis/Shop/src/Routes/api.php` are commented out. The corresponding controller is dead code.
- **Decision Required:**
  - **Option A:** Uncomment the routes to enable wishlist functionality.
  - **Option B:** Delete the controller and remove the commented routes.

#### #9 — Empty `Product/web.php` loaded on every boot

- **Problem:** `packages/Cartxis/Product/src/Routes/web.php` contains only comments — no actual routes. `ProductServiceProvider` still calls `loadRoutesFrom` on it, adding unnecessary overhead on every request.
- **Files:** `packages/Cartxis/Product/src/Routes/web.php`, `packages/Cartxis/Product/src/Providers/ProductServiceProvider.php`
- **Fix:** Delete `web.php` and remove the `loadRoutesFrom` call from `ProductServiceProvider`.

#### #10 — `uieditor_saved_blocks` table orphaned

- **Problem:** Two migrations exist for a "Snippets/SavedBlocks" feature that was removed from the UIEditor UI. The table and migrations remain but the feature is inaccessible.
- **Files:**
  - `database/migrations/2026_03_02_000000_create_uieditor_saved_blocks_table.php`
  - `database/migrations/2026_03_05_000000_add_is_builtin_to_uieditor_saved_blocks.php`
- **Decision Required:**
  - **Option A (Restore):** Re-add Snippets/SavedBlocks UI to UIEditor; also consolidate the two migrations into one.
  - **Option B (Remove):** Drop the `uieditor_saved_blocks` table from the database and delete both migration files.

---

### 🟡 Priority 5 — Architectural Issues

#### #11 — `@/Layouts` alias case mismatch (Linux breakage risk)

- **Problem:** `vite.config.ts` defines alias `@/Layouts` (capital L) pointing to `/resources/js/layouts` (lowercase). This works on Windows (case-insensitive FS) but **will break on Linux production** servers. The 4 files in `resources/admin/Pages/` use the uppercase import; the rest use lowercase.
- **Fix:** Standardize all imports to `@/layouts` (lowercase) and update the alias in `vite.config.ts` to match.

#### #12 — 270+ demo images committed to git

- **Problem:** `resources/admin/media/images/` contains 270+ 600×600 JPEG demo images committed as binary blobs, inflating repo size.
- **Fix:** Move images to `storage/app/public/` or an external CDN; add the directory to `.gitignore`; distribute via a seeder download script.

#### #13 — Two consecutive migrations for the same orphaned feature

- **Problem:** The two `uieditor_saved_blocks` migrations are consecutive: one creates the table, the next immediately adds a column. This is poor migration hygiene.
- **Fix:** If restoring the feature (see #10 Option A), consolidate into a single migration. If removing (Option B), simply delete both.

#### #14 — Inconsistent repository DI pattern in Shop package

- **Problem:** `ShopRepository` is an abstract base class with 3 concrete implementations registered in the service container, but there is no consistent interface-to-implementation binding. Repositories are resolved by concrete class rather than by interface contract.
- **Fix:** Define repository interfaces and bind them in `ShopServiceProvider` following Laravel's standard `$this->app->bind(Interface::class, ConcreteClass::class)` pattern.

---

## Action Checklist

### Immediate (no decisions needed)

- [x] **#1** Move `resources/admin/Pages/Users/*.vue` → `resources/js/pages/Admin/Users/`
- [x] **#2** Delete `resources/admin/Pages/Themes/Index.vue` and `Settings.vue`
- [x] **#3** Delete `resources/js/components/PlaceholderPattern.vue`
- [x] **#4** Delete 3 `.backup` files in Settings/StoreConfiguration and Marketing/Coupons
- [x] **#5** Remove `resources/admin/css/styles.css` Vite entry from `vite.config.ts`; deleted file; removed dead `import` from `Login.vue`
- [x] **#9** Delete `packages/Cartxis/Product/src/Routes/web.php`; remove `loadRoutesFrom` from `ProductServiceProvider`
- [x] **#11** `@/Layouts` regex alias removed from `vite.config.ts`; all active files already used lowercase
- [x] **#12** `/resources/admin/media` added to `.gitignore`

### Requires Decision

- [ ] **#6** CMS Block backend — **Restore admin UI** or **remove model/repo/service/migrations**?
- [ ] **#7** SearchController — **Enable routes** or **delete controller**?
- [ ] **#8** Wishlist API — **Enable routes** or **delete controller**?
- [ ] **#10 / #13** UIEditor saved_blocks — **Restore Snippets feature** or **drop table + migrations**?
- [ ] **#14** Shop repository DI — **Bind interfaces** or leave as-is (low priority)?

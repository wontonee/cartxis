# Theme Block Architecture

This document explains the multi-theme block system used by the UIEditor. Read this before creating blocks for a new theme.

---

## Overview

The UIEditor renders "blocks" (ProductsGrid, CategoriesGrid, Heading, etc.) on the storefront. Each block has two parts: **logic** (composable) and **presentation** (`.vue` template). Themes can override the presentation without touching the logic.

```
Layout JSON  →  UIBlockRenderer  →  Theme Block (if exists)
                                 ↘  Shared Fallback Block
                                     ↘  composable (shared logic)
```

---

## Layer 1 — Shared Logic Composables

**Location:** `resources/js/composables/`

These composables hold all data-fetching, state management, and utility logic. They are **shared across all themes** — never duplicated.

| File | Exports | Used By |
|------|---------|---------|
| `useProductsGrid.ts` | `products, loading, addingToCart, addedToCart, formatPrice, handleAddToCart, colsClass` | All ProductsGridBlock variants |
| `useCategoriesGrid.ts` | `categories, loading, colsClass` | All CategoriesGridBlock variants |
| `useCurrency.ts` | `formatPrice, symbol, currency` | Price display everywhere |

**Rules:**
- All API calls, cart interactions, and business logic go here
- Theme block files call the composable — they never re-implement logic
- Adding a new block type means creating a new composable here first

---

## Layer 2 — Theme-Aware Block Resolution

**File:** `resources/js/components/UIEditor/UIBlockRenderer.vue`

When rendering a block of type e.g. `products_grid`, the resolver:

1. Reads the active theme slug from `page.props.theme?.slug`
2. Tries to load `@themes/{slug}/blocks/{PascalCase}Block.vue`
3. If not found (import fails), falls back to `resources/js/components/UIEditor/blocks/{Type}Block.vue`
4. If that also fails, renders `TextBlock.vue` as a last resort

**The `@themes` alias** is configured in `vite.config.ts`:
```ts
{ find: '@themes', replacement: '/themes' }
```

**Type → filename mapping** (handled by `toPascal()` in UIBlockRenderer):
- `products_grid` → `ProductsGridBlock.vue`
- `categories_grid` → `CategoriesGridBlock.vue`
- `heading` → `HeadingBlock.vue`
- `text` → `TextBlock.vue`
- etc.

---

## Layer 3 — Shared Fallback Blocks

**Location:** `resources/js/components/UIEditor/blocks/`

These are **thin shells** used when the active theme has no override. They call the composable and render a plain, unstyled (or lightly styled) template.

```vue
<!-- Example: ProductsGridBlock.vue (shared fallback) -->
<script setup lang="ts">
import { useProductsGrid } from '@/composables/useProductsGrid'
const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()
const { products, loading, addingToCart, addedToCart, formatPrice, handleAddToCart, colsClass } = useProductsGrid(props.settings, props.editorMode)
</script>
```

**Rules:**
- No business logic here — only composable call + template
- Template should work without any theme CSS (plain Tailwind utility classes only)
- Always support `editorMode` prop (show placeholders when no real data)

---

## Layer 4 — Theme-Specific Block Overrides

**Location:** `themes/{theme-slug}/blocks/`

These files contain the **visual presentation only** for a specific theme. They use the same composable as the shared fallback but render a completely different template — matching the theme's design system.

**Current theme overrides:**

```
themes/
  cartxis-default/
    blocks/
      ProductsGridBlock.vue    ← rounded-2xl, shadow-md hover:shadow-2xl, indigo-600 palette
      CategoriesGridBlock.vue  ← same design language, indigo hover border overlay
```

---

## Creating Blocks for a New Theme

Suppose you are adding theme `dmart-electronics`. Follow these steps:

### Step 1 — Create the blocks directory

```
themes/dmart-electronics/blocks/
```

### Step 2 — Create the block file

```vue
<!-- themes/dmart-electronics/blocks/ProductsGridBlock.vue -->
<script setup lang="ts">
import { useProductsGrid } from '@/composables/useProductsGrid'

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

const {
    products, loading, addingToCart, addedToCart,
    formatPrice, handleAddToCart, colsClass,
} = useProductsGrid(props.settings, props.editorMode)
</script>

<template>
    <!-- Your theme's visual design here -->
    <!-- Use the theme's own CSS classes, colour palette, typography, etc. -->
    <!-- The composable already handles: API fetch, cart state, currency, column layout -->
</template>
```

### Step 3 — No other changes needed

The `UIBlockRenderer` automatically picks up the new file when `page.props.theme.slug === 'dmart-electronics'`. No registration, no config changes.

---

## Props Contract

Every block file (shared or theme-specific) must accept these props:

```ts
defineProps<{
    settings: Record<string, unknown>  // block settings from the layout JSON
    editorMode?: boolean               // true when rendered inside the UIEditor admin panel
}>()
```

**Common `settings` keys by block type:**

### ProductsGridBlock
| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `source` | `'featured' \| 'latest' \| 'manual'` | `'manual'` | Where to fetch products from |
| `product_ids` | `number[]` | `[]` | Used when `source === 'manual'` |
| `limit` | `number` | `8` | Max products to show |
| `columns` | `number` | `4` | Grid columns (1–6) |
| `show_price` | `boolean` | `true` | Display the price |
| `show_cart` | `boolean` | `true` | Display the Add to Cart button |

### CategoriesGridBlock
| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `limit` | `number` | `8` | Max categories to show |
| `columns` | `number` | `4` | Grid columns (1–6) |
| `show_count` | `boolean` | `true` | Show product count per category |

---

## Composable API Reference

### `useProductsGrid(settings, editorMode?)`

```ts
import { useProductsGrid } from '@/composables/useProductsGrid'

const {
    products,       // Ref<GridProduct[]>
    loading,        // Ref<boolean>
    addingToCart,   // Ref<Record<number, boolean>>  — per-product spinner state
    addedToCart,    // Ref<Record<number, boolean>>  — per-product success state (auto-clears after 2s)
    formatPrice,    // (amount: number) => string    — uses active currency from DB
    handleAddToCart,// (product: GridProduct) => Promise<void>
    colsClass,      // (cols: unknown) => string     — returns e.g. "grid-cols-4"
} = useProductsGrid(props.settings, props.editorMode)
```

`GridProduct` interface:
```ts
interface GridProduct {
    id: number
    name: string
    slug: string
    price: number
    thumbnail: string | null
    formatted_price?: string
}
```

### `useCategoriesGrid(settings)`

```ts
import { useCategoriesGrid } from '@/composables/useCategoriesGrid'

const {
    categories,  // Ref<GridCategory[]>
    loading,     // Ref<boolean>
    colsClass,   // (cols: unknown) => string
} = useCategoriesGrid(props.settings)
```

`GridCategory` interface:
```ts
interface GridCategory {
    id: number
    name: string
    slug: string
    image_url: string | null
    products_count: number
}
```

---

## Adding a New Block Type (e.g. `PromoBanner`)

1. **Add composable** `resources/js/composables/usePromoBanner.ts` (if logic is needed)
2. **Add shared fallback** `resources/js/components/UIEditor/blocks/PromoBannerBlock.vue`
3. **Register in UIEditor panel** (block type sidebar) — add `promo_banner` to available block types
4. **Theme overrides** (optional) `themes/{slug}/blocks/PromoBannerBlock.vue`

The block name in the layout JSON must be the snake_case version: `promo_banner` → `PromoBannerBlock.vue`.

---

## Key Files Quick Reference

| Purpose | Path |
|---------|------|
| Block resolver | `resources/js/components/UIEditor/UIBlockRenderer.vue` |
| Shared fallback blocks | `resources/js/components/UIEditor/blocks/` |
| Shared composables | `resources/js/composables/` |
| cartxis-default theme blocks | `themes/cartxis-default/blocks/` |
| Vite alias config | `vite.config.ts` → `@themes` → `/themes` |
| Theme slug (runtime) | `usePage().props.theme?.slug` (shared by `ShareFrontendData` middleware) |
| Shop API routes | `packages/Cartxis/Shop/src/Routes/api.php` |
| Shop service provider | `packages/Cartxis/Shop/src/Providers/ShopServiceProvider.php` |

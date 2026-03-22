<script setup lang="ts">
/**
 * SHARED FALLBACK BLOCK — used when the active theme has no ProductsGridBlock override.
 * For theme-specific presentations, create:
 *   themes/{theme-slug}/blocks/ProductsGridBlock.vue
 * All data/cart logic lives in useProductsGrid composable.
 */
import { useProductsGrid } from '@/composables/useProductsGrid'

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

const {
  products, loading, addingToCart, addedToCart,
  formatPrice, handleAddToCart, colsClass,
} = useProductsGrid(props.settings, props.editorMode)
</script>

<template>
  <div class="w-full">
    <!-- Loading -->
    <div v-if="loading" class="grid gap-4" :class="colsClass(settings.columns)">
      <div v-for="n in (settings.limit ?? 8)" :key="n" class="h-56 bg-gray-100 dark:bg-gray-800 rounded-lg animate-pulse" />
    </div>

    <!-- Products -->
    <div v-else-if="products.length" class="grid gap-4" :class="colsClass(settings.columns)">
      <div
        v-for="product in products"
        :key="product.id"
        class="group bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow"
      >
        <div class="aspect-square bg-gray-100 dark:bg-gray-700 overflow-hidden">
          <img
            v-if="product.thumbnail"
            :src="product.thumbnail"
            :alt="product.name"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01" />
            </svg>
          </div>
        </div>
        <div class="p-3">
          <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ product.name }}</p>
          <p v-if="settings.show_price" class="text-sm font-bold text-indigo-600 dark:text-indigo-400 mt-1">
            {{ formatPrice(typeof product.price === 'string' ? parseFloat(product.price) : product.price) }}
          </p>
          <button
            v-if="settings.show_cart"
            @click="handleAddToCart(product)"
            :disabled="addingToCart[product.id] || !!addedToCart[product.id]"
            class="mt-2 w-full py-3 px-4 rounded-lg font-semibold transition-all flex items-center justify-center gap-2"
            :class="{
              'bg-green-600 text-white': addedToCart[product.id],
              'bg-indigo-600 hover:bg-indigo-700 text-white hover:shadow-lg': !addedToCart[product.id] && !addingToCart[product.id],
              'opacity-75 cursor-not-allowed': addingToCart[product.id],
            }"
          >
            <!-- Adding spinner -->
            <template v-if="addingToCart[product.id]">
              <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              <span>Adding...</span>
            </template>
            <!-- Added confirmation -->
            <template v-else-if="addedToCart[product.id]">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Added!</span>
            </template>
            <!-- Default -->
            <template v-else>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span>Add to Cart</span>
            </template>
          </button>
        </div>
      </div>
    </div>

    <!-- Empty state for editor -->
    <div
      v-else-if="editorMode"
      class="grid gap-4"
      :class="colsClass(settings.columns)"
    >
      <div
        v-for="n in (settings.limit ?? 4)"
        :key="n"
        class="h-56 bg-gray-100 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center text-gray-400 text-xs"
      >
        Product {{ n }}
      </div>
    </div>
  </div>
</template>

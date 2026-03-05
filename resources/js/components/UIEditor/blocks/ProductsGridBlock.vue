<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

interface Product {
  id: number
  name: string
  slug: string
  price: number
  thumbnail: string | null
  formatted_price?: string
}

const products = ref<Product[]>([])
const loading  = ref(false)

onMounted(async () => {
  const ids = props.settings.product_ids as number[]
  if (!ids?.length) return
  loading.value = true
  try {
    // In the editor, use the admin search endpoint (auth required).
    // On the storefront (editorMode=false), use the public API endpoint.
    const url = props.editorMode
      ? route('admin.uieditor.products.search')
      : '/api/products/by-ids'
    const res = await axios.get(url, {
      params: { ids: ids.join(','), per_page: props.settings.limit ?? 8 },
    })
    products.value = res.data.data ?? []
  } catch {
    products.value = []
  } finally {
    loading.value = false
  }
})

const colsClass = (cols: unknown) => {
  const n = Number(cols) || 4
  return `grid-cols-${Math.min(n, 6)}`
}
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
          <p v-if="settings.show_price" class="text-sm font-bold text-blue-600 dark:text-blue-400 mt-1">
            {{ product.formatted_price ?? product.price }}
          </p>
          <button
            v-if="settings.show_cart"
            class="mt-2 w-full py-1.5 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors"
          >
            Add to Cart
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

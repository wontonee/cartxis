<script setup lang="ts">
/**
 * SHARED FALLBACK BLOCK — used when the active theme has no CategoriesGridBlock override.
 * For theme-specific presentations, create:
 *   themes/{theme-slug}/blocks/CategoriesGridBlock.vue
 * All data logic lives in useCategoriesGrid composable.
 */
import { useCategoriesGrid } from '@/composables/useCategoriesGrid'

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

const { categories, loading, colsClass } = useCategoriesGrid(props.settings)
</script>

<template>
  <div class="w-full">
    <!-- Loading skeleton -->
    <div v-if="loading" class="grid gap-6" :class="colsClass(settings.columns)">
      <div v-for="n in (settings.limit ?? 8)" :key="n" class="h-48 bg-gray-100 dark:bg-gray-800 rounded-2xl animate-pulse" />
    </div>

    <!-- Categories grid -->
    <div v-else-if="categories.length" class="grid gap-6" :class="colsClass(settings.columns)">
      <a
        v-for="category in categories"
        :key="category.id"
        :href="`/products?category=${category.slug}`"
        class="group relative bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100 dark:border-gray-700"
      >
        <!-- Category image -->
        <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 overflow-hidden">
          <img
            v-if="category.image_url"
            :src="category.image_url"
            :alt="category.name"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <svg class="w-12 h-12 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
            </svg>
          </div>
        </div>

        <!-- Category info -->
        <div class="p-4 text-center">
          <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors truncate">
            {{ category.name }}
          </h3>
          <p v-if="settings.show_count" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            {{ category.products_count }} {{ category.products_count === 1 ? 'product' : 'products' }}
          </p>
        </div>

        <!-- Hover border overlay -->
        <div class="absolute inset-0 border-2 border-transparent group-hover:border-blue-500 rounded-2xl transition-colors pointer-events-none" />
      </a>
    </div>

    <!-- Empty state (editor only) -->
    <div v-else-if="editorMode" class="grid gap-6" :class="colsClass(settings.columns)">
      <div
        v-for="n in (settings.limit ?? 4)"
        :key="n"
        class="h-48 bg-gray-100 dark:bg-gray-800 rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center gap-2 text-gray-400 dark:text-gray-500 text-xs"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        Category {{ n }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
/**
 * cartxis-default theme override for CategoriesGridBlock.
 * All data/logic comes from the shared composable; this file owns presentation only.
 */
import { useCategoriesGrid } from '@/composables/useCategoriesGrid'

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

const { categories, loading, colsClass } = useCategoriesGrid(props.settings)
</script>

<template>
    <div class="w-full">
        <!-- Loading skeleton -->
        <div v-if="loading" class="grid gap-6" :class="colsClass(settings.columns)">
            <div
                v-for="n in (settings.limit ?? 8)"
                :key="n"
                class="rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 animate-pulse"
            >
                <div class="aspect-video" />
                <div class="p-4 space-y-2">
                    <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded-full w-2/3 mx-auto" />
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full w-1/3 mx-auto" />
                </div>
            </div>
        </div>

        <!-- Categories grid -->
        <div v-else-if="categories.length" class="grid gap-6" :class="colsClass(settings.columns)">
            <a
                v-for="category in categories"
                :key="category.id"
                :href="`/products?category=${category.slug}`"
                class="group relative bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1"
            >
                <!-- Category image -->
                <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 overflow-hidden">
                    <img
                        v-if="category.image_url"
                        :src="category.image_url"
                        :alt="category.name"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        loading="lazy"
                    />
                    <div v-else class="absolute inset-0 flex items-center justify-center text-4xl opacity-30">
                        🗂️
                    </div>
                </div>

                <!-- Category info -->
                <div class="p-4 text-center">
                    <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">
                        {{ category.name }}
                    </h3>
                    <p v-if="settings.show_count" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ category.products_count }}
                        {{ category.products_count === 1 ? 'product' : 'products' }}
                    </p>
                </div>

                <!-- Indigo hover border overlay -->
                <div class="absolute inset-0 border-2 border-transparent group-hover:border-indigo-500 rounded-2xl transition-colors pointer-events-none" />
            </a>
        </div>

        <!-- Empty state (editor only) -->
        <div v-else-if="editorMode" class="grid gap-6" :class="colsClass(settings.columns)">
            <div
                v-for="n in (settings.limit ?? 4)"
                :key="n"
                class="rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 border-2 border-dashed border-gray-200 dark:border-gray-600 flex flex-col items-center justify-center gap-2 py-12 text-gray-400 dark:text-gray-500"
            >
                <span class="text-3xl opacity-40">🗂️</span>
                <span class="text-xs">Category {{ n }}</span>
            </div>
        </div>
    </div>
</template>

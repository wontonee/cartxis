<script setup lang="ts">
/**
 * cartxis-default theme override for CategoriesGridBlock.
 */
import { useCategoriesGrid } from '@/composables/useCategoriesGrid'
import ThemePlaceholderIcon from '../resources/views/components/ThemePlaceholderIcon.vue'

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

const { categories, loading, colsClass } = useCategoriesGrid(props.settings)
</script>

<template>
    <div class="w-full">
        <div v-if="loading" class="grid gap-6" :class="colsClass(settings.columns)">
            <div
                v-for="n in (settings.limit ?? 8)"
                :key="n"
                class="rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 animate-pulse"
            >
                <div class="aspect-video" />
                <div class="p-4 space-y-2">
                    <div class="h-4 bg-slate-300 rounded-full w-2/3 mx-auto" />
                    <div class="h-3 bg-slate-200 rounded-full w-1/3 mx-auto" />
                </div>
            </div>
        </div>

        <div v-else-if="categories.length" class="grid gap-6" :class="colsClass(settings.columns)">
            <a
                v-for="category in categories"
                :key="category.id"
                :href="`/category/${category.slug}`"
                class="group relative bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1"
            >
                <div class="aspect-video bg-gradient-to-br from-blue-50 to-slate-100 overflow-hidden">
                    <img
                        v-if="category.image_url"
                        :src="category.image_url"
                        :alt="category.name"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        loading="lazy"
                    />
                    <div v-else class="absolute inset-0 flex items-center justify-center opacity-40">
                        <ThemePlaceholderIcon variant="category" />
                    </div>
                </div>

                <div class="p-4 text-center">
                    <h3 class="font-semibold text-slate-900 truncate transition-colors group-hover:text-[var(--theme-primary)]">
                        {{ category.name }}
                    </h3>
                    <p v-if="settings.show_count" class="text-xs text-slate-500 mt-1">
                        {{ category.products_count }}
                        {{ category.products_count === 1 ? 'product' : 'products' }}
                    </p>
                </div>

                <div
                    class="absolute inset-0 border-2 border-transparent group-hover:border-[var(--theme-primary)] rounded-2xl transition-colors pointer-events-none"
                />
            </a>
        </div>

        <div v-else-if="editorMode" class="grid gap-6" :class="colsClass(settings.columns)">
            <div
                v-for="n in (settings.limit ?? 4)"
                :key="n"
                class="rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center gap-2 py-12 text-slate-400"
            >
                <ThemePlaceholderIcon variant="category" />
                <span class="text-xs">Category {{ n }}</span>
            </div>
        </div>
    </div>
</template>

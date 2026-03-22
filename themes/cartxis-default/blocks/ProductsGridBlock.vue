<script setup lang="ts">
/**
 * cartxis-default theme override for ProductsGridBlock.
 * All data/logic comes from the shared composable; this file owns presentation only.
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
        <!-- Loading skeleton -->
        <div v-if="loading" class="grid gap-6" :class="colsClass(settings.columns)">
            <div
                v-for="n in (settings.limit ?? 8)"
                :key="n"
                class="rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 animate-pulse"
            >
                <div class="aspect-square" />
                <div class="p-4 space-y-2">
                    <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded-full w-3/4" />
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded-full w-1/2" />
                    <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded-xl mt-3" />
                </div>
            </div>
        </div>

        <!-- Products grid -->
        <div v-else-if="products.length" class="grid gap-6" :class="colsClass(settings.columns)">
            <div
                v-for="product in products"
                :key="product.id"
                class="group relative bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300"
            >
                <!-- Image -->
                <a :href="`/product/${product.slug}`" class="block">
                    <div class="relative aspect-square bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 overflow-hidden">
                        <img
                            v-if="product.thumbnail"
                            :src="product.thumbnail"
                            :alt="product.name"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            loading="lazy"
                        />
                        <div v-else class="absolute inset-0 flex items-center justify-center text-5xl opacity-40">
                            📦
                        </div>
                    </div>
                </a>

                <!-- Info -->
                <div class="p-4">
                    <a :href="`/product/${product.slug}`" class="block">
                        <h3 class="font-semibold text-gray-900 dark:text-white truncate hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                            {{ product.name }}
                        </h3>
                    </a>

                    <p v-if="settings.show_price" class="mt-1 text-lg font-bold text-indigo-600 dark:text-indigo-400">
                        {{ formatPrice(
                            typeof product.price === 'string' ? parseFloat(product.price) : product.price
                        ) }}
                    </p>

                    <button
                        v-if="settings.show_cart"
                        @click="handleAddToCart(product)"
                        :disabled="addingToCart[product.id] || !!addedToCart[product.id]"
                        class="mt-3 w-full py-3 px-4 rounded-xl font-semibold text-sm transition-all duration-200 flex items-center justify-center gap-2 disabled:cursor-not-allowed"
                        :class="{
                            'bg-green-500 hover:bg-green-600 text-white': addedToCart[product.id],
                            'bg-indigo-600 hover:bg-indigo-700 text-white hover:shadow-lg': !addedToCart[product.id] && !addingToCart[product.id],
                            'bg-indigo-400 text-white opacity-75': addingToCart[product.id],
                        }"
                    >
                        <!-- Spinner -->
                        <template v-if="addingToCart[product.id]">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            <span>Adding…</span>
                        </template>
                        <!-- Added -->
                        <template v-else-if="addedToCart[product.id]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Added!</span>
                        </template>
                        <!-- Default -->
                        <template v-else>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Add to Cart</span>
                        </template>
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty state (editor only) -->
        <div v-else-if="editorMode" class="grid gap-6" :class="colsClass(settings.columns)">
            <div
                v-for="n in (settings.limit ?? 4)"
                :key="n"
                class="rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 border-2 border-dashed border-gray-200 dark:border-gray-600 flex flex-col items-center justify-center gap-2 text-gray-400 dark:text-gray-500 py-12"
            >
                <span class="text-3xl opacity-40">📦</span>
                <span class="text-xs">Product {{ n }}</span>
            </div>
        </div>
    </div>
</template>

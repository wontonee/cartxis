<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import { computed } from 'vue';

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: number;
    special_price?: number;
    image: string;
    rating?: number;
    reviews_count?: number;
}

interface Props {
    query: string;
    products: {
        data: Product[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    } | Product[];
    resultsCount?: number;
    message?: string;
    theme?: any;
    siteConfig?: any;
}

const props = defineProps<Props>();

const productsData = computed(() => {
    if (Array.isArray(props.products)) {
        return props.products;
    }
    return props.products?.data || [];
});

const hasResults = computed(() => productsData.value.length > 0);

const totalResults = computed(() => {
    if (Array.isArray(props.products)) {
        return props.products.length;
    }
    return props.products?.total || 0;
});
</script>

<template>
    <ThemeLayout>
        <Head>
            <title>Search Results for "{{ query }}"</title>
        </Head>

        <div class="bg-gray-50 min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Search Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Search Results
                    </h1>
                    <p class="mt-2 text-lg text-gray-600">
                        <template v-if="query">
                            {{ totalResults }} result{{ totalResults !== 1 ? 's' : '' }} for 
                            <span class="font-semibold">"{{ query }}"</span>
                        </template>
                        <template v-else>
                            Please enter a search term
                        </template>
                    </p>
                </div>

                <!-- No Results Message -->
                <div v-if="!hasResults && query" class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No products found</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Try adjusting your search terms or browse our categories
                    </p>
                    <div class="mt-6">
                        <a 
                            href="/products" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                        >
                            Browse All Products
                        </a>
                    </div>
                </div>

                <!-- Results Grid -->
                <div v-else-if="hasResults" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a
                        v-for="product in productsData"
                        :key="product.id"
                        :href="`/product/${product.slug}`"
                        class="group bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden"
                    >
                        <!-- Product Image -->
                        <div class="aspect-square bg-gray-100 overflow-hidden">
                            <img
                                v-if="product.image"
                                :src="product.image"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                <span class="text-6xl">📦</span>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="p-4">
                            <h3 class="text-sm font-medium text-gray-900 group-hover:text-indigo-600 line-clamp-2">
                                {{ product.name }}
                            </h3>
                            
                            <!-- Rating -->
                            <div v-if="product.rating" class="flex items-center mt-2">
                                <div class="flex items-center">
                                    <span class="text-yellow-400">★</span>
                                    <span class="ml-1 text-sm text-gray-600">{{ product.rating }}</span>
                                </div>
                                <span v-if="product.reviews_count" class="ml-2 text-xs text-gray-500">
                                    ({{ product.reviews_count }})
                                </span>
                            </div>

                            <!-- Price -->
                            <div class="mt-2 flex items-center space-x-2">
                                <span 
                                    v-if="product.special_price" 
                                    class="text-lg font-bold text-red-600"
                                >
                                    ${{ Number(product.special_price).toFixed(2) }}
                                </span>
                                <span 
                                    :class="[
                                        'font-semibold',
                                        product.special_price ? 'text-sm text-gray-400 line-through' : 'text-lg text-gray-900'
                                    ]"
                                >
                                    ${{ Number(product.price).toFixed(2) }}
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </ThemeLayout>
</template>

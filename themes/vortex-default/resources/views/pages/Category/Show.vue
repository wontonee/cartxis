<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import { computed } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    image?: string;
    children?: Category[];
}

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    special_price?: number;
    image?: string;
    rating?: number;
    reviews_count?: number;
}

interface Props {
    category: Category;
    products: {
        data: Product[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
    };
    filters: {
        per_page: number;
        sort: string;
    };
    seo: {
        title: string;
        description: string;
        keywords: string;
    };
}

const props = defineProps<Props>();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
    }).format(price);
};

const hasDiscount = (product: Product) => {
    return product.special_price && product.special_price < product.price;
};

const discountPercentage = (product: Product) => {
    if (!hasDiscount(product)) return 0;
    return Math.round(((product.price - (product.special_price || 0)) / product.price) * 100);
};
</script>

<template>
    <Head>
        <title>{{ seo.title }}</title>
        <meta name="description" :content="seo.description" />
        <meta name="keywords" :content="seo.keywords" />
    </Head>

    <ThemeLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Breadcrumb -->
            <nav class="flex mb-6 text-sm">
                <Link href="/" class="text-gray-600 hover:text-gray-900">Home</Link>
                <span class="mx-2 text-gray-400">/</span>
                <Link href="/products" class="text-gray-600 hover:text-gray-900">Products</Link>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900 font-medium">{{ category.name }}</span>
            </nav>

            <!-- Category Header -->
            <div class="mb-8">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ category.name }}</h1>
                        <p v-if="category.description" class="text-gray-600 max-w-3xl" v-html="category.description"></p>
                    </div>
                    <img 
                        v-if="category.image" 
                        :src="category.image" 
                        :alt="category.name"
                        class="w-32 h-32 object-cover rounded-lg ml-6"
                    />
                </div>

                <!-- Subcategories -->
                <div v-if="category.children && category.children.length > 0" class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Shop by Category</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <Link
                            v-for="child in category.children"
                            :key="child.id"
                            :href="`/category/${child.slug}`"
                            class="flex flex-col items-center p-4 bg-white rounded-lg border border-gray-200 hover:border-indigo-500 hover:shadow-md transition-all"
                        >
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-2">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-900 text-center">{{ child.name }}</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Products Header -->
            <div class="flex items-center justify-between mb-6 border-b pb-4">
                <div class="text-sm text-gray-600">
                    Showing {{ products.data.length }} of {{ products.total }} products
                </div>
                <div class="flex items-center space-x-4">
                    <label class="text-sm text-gray-700">
                        Sort by:
                        <select 
                            class="ml-2 border border-gray-300 rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            @change="(e) => $inertia.get(`/category/${category.slug}`, { sort: (e.target as HTMLSelectElement).value })"
                        >
                            <option value="position">Featured</option>
                            <option value="name">Name (A-Z)</option>
                            <option value="-name">Name (Z-A)</option>
                            <option value="price">Price: Low to High</option>
                            <option value="-price">Price: High to Low</option>
                            <option value="-created_at">Newest First</option>
                        </select>
                    </label>
                </div>
            </div>

            <!-- Products Grid -->
            <div v-if="products.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                <Link
                    v-for="product in products.data"
                    :key="product.id"
                    :href="`/product/${product.slug}`"
                    class="group bg-white rounded-lg border border-gray-200 hover:border-indigo-500 hover:shadow-lg transition-all overflow-hidden"
                >
                    <div class="relative aspect-square overflow-hidden bg-gray-100">
                        <img
                            v-if="product.image"
                            :src="product.image"
                            :alt="product.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <svg class="w-20 h-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div v-if="hasDiscount(product)" class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                            -{{ discountPercentage(product) }}%
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors">
                            {{ product.name }}
                        </h3>
                        <div class="flex items-center justify-between">
                            <div>
                                <div v-if="hasDiscount(product)" class="flex items-baseline space-x-2">
                                    <span class="text-lg font-bold text-indigo-600">{{ formatPrice(product.special_price!) }}</span>
                                    <span class="text-sm text-gray-500 line-through">{{ formatPrice(product.price) }}</span>
                                </div>
                                <div v-else>
                                    <span class="text-lg font-bold text-gray-900">{{ formatPrice(product.price) }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-if="product.rating" class="flex items-center mt-2">
                            <div class="flex items-center">
                                <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= product.rating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                            <span class="ml-1 text-xs text-gray-500">({{ product.reviews_count || 0 }})</span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <svg class="mx-auto h-24 w-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No products found</h3>
                <p class="mt-2 text-gray-500">This category doesn't have any products yet.</p>
                <Link href="/products" class="mt-6 inline-block px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    Browse All Products
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="products.last_page > 1" class="flex items-center justify-center space-x-2 mt-8">
                <Link
                    v-for="page in products.last_page"
                    :key="page"
                    :href="`/category/${category.slug}?page=${page}`"
                    class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                    :class="page === products.current_page 
                        ? 'bg-indigo-600 text-white' 
                        : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'"
                >
                    {{ page }}
                </Link>
            </div>
        </div>
    </ThemeLayout>
</template>

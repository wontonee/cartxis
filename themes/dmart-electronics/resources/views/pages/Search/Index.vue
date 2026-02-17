<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import Breadcrumb from '../../components/Breadcrumb.vue';
import ProductCard from '../../components/ProductCard.vue';
import QuickViewModal from '../../components/QuickViewModal.vue';
import { Search, Grid3x3, List } from 'lucide-vue-next';

interface Product {
    id: number; name: string; slug: string; price: number;
    special_price: number | null; image: string | null; rating: number;
    reviews_count: number; in_stock: boolean; stock_quantity: number;
    brand?: { id: number; name: string; slug: string };
    badges?: Array<{ text: string; class: string }>;
}
interface PaginationLink { url: string | null; label: string; active: boolean; }
interface PaginatedProducts {
    data: Product[]; current_page: number; last_page: number;
    per_page: number; total: number; links: PaginationLink[];
}

interface Props {
    query: string;
    products: PaginatedProducts;
    theme: { name: string; slug: string };
    siteConfig: { name: string };
}

const props = defineProps<Props>();
const viewMode = ref<'grid' | 'list'>('grid');
const quickViewSlug = ref<string | null>(null);
const searchQuery = ref(props.query || '');

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        router.get('/search', { q: searchQuery.value.trim() }, { preserveState: false });
    }
};

const breadcrumbs = computed(() => [
    { label: 'Shop', url: '/products' },
    { label: `Search: "${props.query}"` },
]);
</script>

<template>
    <ThemeLayout>
        <Head :title="`Search: ${query}`" />

        <Breadcrumb :items="breadcrumbs" />

        <section class="py-8 lg:py-12">
            <div class="dmart-container">
                <!-- Search Header -->
                <div class="max-w-2xl mx-auto text-center mb-10">
                    <h1 class="text-2xl lg:text-3xl font-extrabold mb-4 text-title font-title">
                        Search Results
                    </h1>
                    <form @submit.prevent="handleSearch" class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search products..."
                            class="w-full pl-5 pr-14 py-4 rounded-full border-2 text-base focus:border-theme-1 focus:ring-0 transition-colors"
                        />
                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full flex items-center justify-center text-white bg-theme-1">
                            <Search class="w-5 h-5" />
                        </button>
                    </form>
                    <p class="text-gray-500 mt-3">
                        <strong>{{ products.total }}</strong> results for "<strong>{{ query }}</strong>"
                    </p>
                </div>

                <!-- Toolbar -->
                <div v-if="products.data.length > 0" class="flex items-center justify-between mb-6 pb-4 border-b">
                    <p class="text-sm text-gray-500">
                        Showing {{ products.data.length }} of {{ products.total }}
                    </p>
                    <div class="hidden sm:flex items-center gap-1 border rounded-lg p-0.5">
                        <button @click="viewMode = 'grid'" class="p-1.5 rounded" :class="viewMode === 'grid' ? 'bg-theme-1 text-white' : 'text-gray-400'">
                            <Grid3x3 class="w-4 h-4" />
                        </button>
                        <button @click="viewMode = 'list'" class="p-1.5 rounded" :class="viewMode === 'list' ? 'bg-theme-1 text-white' : 'text-gray-400'">
                            <List class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- Results -->
                <div
                    v-if="products.data.length > 0"
                    :class="viewMode === 'grid' ? 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5' : 'space-y-4'"
                >
                    <ProductCard
                        v-for="product in products.data"
                        :key="product.id"
                        :product="product"
                        :variant="viewMode === 'list' ? 'list' : 'default'"
                        @quick-view="quickViewSlug = $event"
                    />
                </div>

                <div v-else class="text-center py-20">
                    <div class="text-6xl mb-4">🔍</div>
                    <h3 class="text-xl font-bold mb-2 text-title font-title">No results found</h3>
                    <p class="text-gray-500 mb-6">Try a different search term or browse categories.</p>
                    <Link href="/products" class="dmart-btn dmart-btn-primary">Browse All Products</Link>
                </div>

                <!-- Pagination -->
                <nav v-if="products.last_page > 1" class="flex items-center justify-center gap-1 mt-10">
                    <template v-for="link in products.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                            :class="link.active ? 'bg-theme-1 text-white' : 'hover:bg-gray-100 text-gray-600'"
                            v-html="link.label"
                            preserve-state preserve-scroll
                        />
                        <span v-else class="px-3 py-2 text-sm text-gray-300" v-html="link.label" />
                    </template>
                </nav>
            </div>
        </section>

        <QuickViewModal :slug="quickViewSlug" @close="quickViewSlug = null" />
    </ThemeLayout>
</template>

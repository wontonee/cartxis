<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import Breadcrumb from '../../components/Breadcrumb.vue';
import ProductCard from '../../components/ProductCard.vue';
import QuickViewModal from '../../components/QuickViewModal.vue';
import { Grid3x3, List, ChevronRight } from 'lucide-vue-next';

interface Category {
    id: number; name: string; slug: string; description: string;
    image: string | null; products_count: number;
    children?: Category[]; parent?: { id: number; name: string; slug: string };
}
interface Product {
    id: number; name: string; slug: string; price: number;
    special_price: number | null; image: string | null; rating: number;
    reviews_count: number; in_stock: boolean; stock_quantity: number;
    brand?: { id: number; name: string; slug: string };
    badges?: Array<{ text: string; class: string }>;
}
interface PaginationLink { url: string | null; label: string; active: boolean; }
interface PaginatedProducts {
    data: Product[]; current_page: number; last_page: number; per_page: number;
    total: number; links: PaginationLink[];
}

interface Props {
    category: Category;
    products: PaginatedProducts;
    subcategories?: Category[];
    theme: { name: string; slug: string };
    siteConfig: { name: string };
}

const props = defineProps<Props>();

const viewMode = ref<'grid' | 'list'>('grid');
const sortBy = ref('newest');
const quickViewSlug = ref<string | null>(null);

const sortOptions = [
    { value: 'newest', label: 'Newest First' },
    { value: 'price_asc', label: 'Price: Low to High' },
    { value: 'price_desc', label: 'Price: High to Low' },
    { value: 'name_asc', label: 'Name: A - Z' },
    { value: 'rating', label: 'Top Rated' },
];

watch(sortBy, (val) => {
    router.get(`/category/${props.category.slug}`, val !== 'newest' ? { sort: val } : {}, { preserveState: true, preserveScroll: true });
});

const breadcrumbs = computed(() => {
    const items = [{ label: 'Shop', url: '/products' }];
    if (props.category.parent) items.push({ label: props.category.parent.name, url: `/category/${props.category.parent.slug}` });
    items.push({ label: props.category.name });
    return items;
});

// Dynamic grid from theme settings
const themeSettings = computed(() => (usePage().props.theme as any)?.settings ?? {});
const categoryGridClass = computed(() => {
    const cols = themeSettings.value['layout.products_per_row'] || '4';
    const map: Record<string, string> = { '2': 'md:grid-cols-2', '3': 'md:grid-cols-3', '4': 'md:grid-cols-3 lg:grid-cols-4', '5': 'md:grid-cols-3 lg:grid-cols-5', '6': 'md:grid-cols-3 lg:grid-cols-6' };
    return map[cols] || 'md:grid-cols-3 lg:grid-cols-4';
});
</script>

<template>
    <ThemeLayout>
        <Head :title="category.name" />

        <Breadcrumb :items="breadcrumbs" />

        <!-- Category Header -->
        <section class="py-8 bg-bg-6">
            <div class="dmart-container">
                <h1 class="text-2xl lg:text-3xl font-extrabold text-title font-title">
                    {{ category.name }}
                </h1>
                <p v-if="category.description" class="text-gray-500 mt-2 max-w-2xl">{{ category.description }}</p>
            </div>
        </section>

        <!-- Subcategories -->
        <section v-if="subcategories?.length" class="py-6 border-b">
            <div class="dmart-container">
                <div class="flex gap-3 overflow-x-auto pb-2">
                    <Link
                        v-for="sub in subcategories"
                        :key="sub.id"
                        :href="`/category/${sub.slug}`"
                        class="flex-shrink-0 px-5 py-2.5 rounded-full border text-sm font-semibold transition-all hover:bg-theme-1 hover:text-white hover:border-theme-1 font-title"
                    >
                        {{ sub.name }}
                        <span class="text-gray-400 ml-1">({{ sub.products_count }})</span>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Products -->
        <section class="py-8 lg:py-12">
            <div class="dmart-container">
                <!-- Toolbar -->
                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b">
                    <p class="text-sm text-gray-500">
                        Showing <strong>{{ products.data.length }}</strong> of <strong>{{ products.total }}</strong> products
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="hidden sm:flex items-center gap-1 border rounded-lg p-0.5">
                            <button @click="viewMode = 'grid'" class="p-1.5 rounded" :class="viewMode === 'grid' ? 'bg-theme-1 text-white' : 'text-gray-400'">
                                <Grid3x3 class="w-4 h-4" />
                            </button>
                            <button @click="viewMode = 'list'" class="p-1.5 rounded" :class="viewMode === 'list' ? 'bg-theme-1 text-white' : 'text-gray-400'">
                                <List class="w-4 h-4" />
                            </button>
                        </div>
                        <select v-model="sortBy" class="border rounded-lg px-3 py-2 text-sm bg-white">
                            <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                    </div>
                </div>

                <div
                    v-if="products.data.length > 0"
                    :class="viewMode === 'grid' ? `grid grid-cols-2 ${categoryGridClass} gap-5` : 'space-y-4'"
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
                    <div class="text-6xl mb-4">📦</div>
                    <h3 class="text-xl font-bold mb-2 text-title font-title">No products found</h3>
                    <p class="text-gray-500 mb-6">This category doesn't have any products yet.</p>
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

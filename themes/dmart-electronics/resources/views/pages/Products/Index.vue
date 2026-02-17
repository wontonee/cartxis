<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import Breadcrumb from '../../components/Breadcrumb.vue';
import ProductCard from '../../components/ProductCard.vue';
import ProductSkeleton from '../../components/ProductSkeleton.vue';
import QuickViewModal from '../../components/QuickViewModal.vue';
import { useCurrency } from '@/composables/useCurrency';
import {
    Grid3x3, List, ChevronDown, X, SlidersHorizontal, ChevronLeft, ChevronRight,
} from 'lucide-vue-next';

interface Brand { id: number; name: string; slug: string; products_count: number; }
interface Category { id: number; name: string; slug: string; products_count: number; }
interface Product {
    id: number; name: string; slug: string; price: number;
    special_price: number | null; image: string | null; rating: number;
    reviews_count: number; in_stock: boolean; stock_quantity: number;
    brand?: Brand; badges?: Array<{ text: string; class: string }>;
}
interface PaginationLink { url: string | null; label: string; active: boolean; }
interface PaginatedProducts {
    data: Product[]; current_page: number; last_page: number; per_page: number;
    total: number; links: PaginationLink[];
}
interface Filters {
    categories: Category[]; brands: Brand[];
    priceRange: { min: number; max: number };
}

interface Props {
    products: PaginatedProducts;
    filters: Filters;
    activeFilters?: {
        category?: string; brand?: string; price_min?: number;
        price_max?: number; min_price?: number; max_price?: number; sort?: string; search?: string;
    };
    theme: { name: string; slug: string };
    siteConfig: { name: string };
}

const props = defineProps<Props>();
const { formatPrice } = useCurrency();

const viewMode = ref<'grid' | 'list'>('grid');
const mobileFiltersOpen = ref(false);
const quickViewSlug = ref<string | null>(null);

// Local filter state
const selectedCategory = ref(props.activeFilters?.category || '');
const selectedBrand = ref(props.activeFilters?.brand || '');
const defaultMinPrice = props.filters?.priceRange?.min ?? 0;
const defaultMaxPrice = props.filters?.priceRange?.max ?? 10000;
const minPrice = ref(props.activeFilters?.price_min ?? props.activeFilters?.min_price ?? defaultMinPrice);
const maxPrice = ref(props.activeFilters?.price_max ?? props.activeFilters?.max_price ?? defaultMaxPrice);
const sortBy = ref(props.activeFilters?.sort || 'newest');

const minLimit = computed(() => props.filters?.priceRange?.min || 0);
const maxLimit = computed(() => props.filters?.priceRange?.max || 10000);

const priceRangePercent = computed(() => {
    const span = Math.max(maxLimit.value - minLimit.value, 1);
    const start = ((minPrice.value - minLimit.value) / span) * 100;
    const end = ((maxPrice.value - minLimit.value) / span) * 100;

    return {
        start: Math.min(Math.max(start, 0), 100),
        end: Math.min(Math.max(end, 0), 100),
    };
});

const minRangeInput = () => {
    if (minPrice.value > maxPrice.value) {
        maxPrice.value = minPrice.value;
    }
};

const maxRangeInput = () => {
    if (maxPrice.value < minPrice.value) {
        minPrice.value = maxPrice.value;
    }
};

const applyPriceFilters = () => {
    if (minPrice.value < minLimit.value) minPrice.value = minLimit.value;
    if (maxPrice.value > maxLimit.value) maxPrice.value = maxLimit.value;
    if (minPrice.value > maxPrice.value) minPrice.value = maxPrice.value;

    applyFilters();
};

const sortOptions = [
    { value: 'newest', label: 'Newest First' },
    { value: 'price_asc', label: 'Price: Low to High' },
    { value: 'price_desc', label: 'Price: High to Low' },
    { value: 'name_asc', label: 'Name: A - Z' },
    { value: 'name_desc', label: 'Name: Z - A' },
    { value: 'rating', label: 'Top Rated' },
];

const activeFilterCount = computed(() => {
    let count = 0;
    if (selectedCategory.value) count++;
    if (selectedBrand.value) count++;
    if (minPrice.value > (props.filters?.priceRange?.min || 0)) count++;
    if (maxPrice.value < (props.filters?.priceRange?.max || 10000)) count++;
    return count;
});

const applyFilters = () => {
    const params: Record<string, string | number> = {};
    if (selectedCategory.value) params.category = selectedCategory.value;
    if (selectedBrand.value) params.brand = selectedBrand.value;
    if (minPrice.value > (props.filters?.priceRange?.min || 0)) params.price_min = minPrice.value;
    if (maxPrice.value < (props.filters?.priceRange?.max || 10000)) params.price_max = maxPrice.value;
    if (sortBy.value !== 'newest') params.sort = sortBy.value;
    if (props.activeFilters?.search) params.search = props.activeFilters.search;

    router.get('/products', params, { preserveState: true, preserveScroll: true });
    mobileFiltersOpen.value = false;
};

const clearFilters = () => {
    selectedCategory.value = '';
    selectedBrand.value = '';
    minPrice.value = props.filters?.priceRange?.min || 0;
    maxPrice.value = props.filters?.priceRange?.max || 10000;
    sortBy.value = 'newest';
    router.get('/products', {}, { preserveState: false });
};

watch(sortBy, () => applyFilters());

watch(minPrice, (value) => {
    if (value < minLimit.value) minPrice.value = minLimit.value;
    if (value > maxPrice.value) minPrice.value = maxPrice.value;
});

watch(maxPrice, (value) => {
    if (value > maxLimit.value) maxPrice.value = maxLimit.value;
    if (value < minPrice.value) maxPrice.value = minPrice.value;
});

const breadcrumbs = computed(() => {
    const items = [{ label: 'Shop', url: '/products' }];
    if (props.activeFilters?.search) items.push({ label: `Search: "${props.activeFilters.search}"` });
    return items;
});

// Dynamic layout from theme settings
const themeSettings = computed(() => (usePage().props.theme as any)?.settings ?? {});
const productsGridClass = computed(() => {
    const cols = themeSettings.value['layout.products_per_row'] || '4';
    const map: Record<string, string> = { '2': 'md:grid-cols-2', '3': 'md:grid-cols-3', '4': 'md:grid-cols-3 lg:grid-cols-4', '5': 'md:grid-cols-3 lg:grid-cols-5', '6': 'md:grid-cols-3 lg:grid-cols-6' };
    return map[cols] || 'md:grid-cols-3 lg:grid-cols-4';
});
const sidebarPosition = computed(() => themeSettings.value['layout.sidebar_position'] || 'left');
</script>

<template>
    <ThemeLayout>
        <Head title="Shop" />

        <Breadcrumb :items="breadcrumbs" />

        <section class="py-8 lg:py-12">
            <div class="dmart-container">
                <div class="lg:grid gap-8" :class="sidebarPosition === 'right' ? 'lg:grid-cols-[1fr_280px]' : sidebarPosition === 'none' ? '' : 'lg:grid-cols-[280px_1fr]'">
                    <!-- Sidebar Filters (Desktop) -->
                    <aside class="hidden lg:block space-y-6" :class="{ 'order-2': sidebarPosition === 'right' }" v-if="sidebarPosition !== 'none'">
                        <div class="dmart-filter-section">
                            <h3 class="dmart-filter-title">Categories</h3>
                            <ul class="space-y-2">
                                <li v-for="cat in filters?.categories" :key="cat.id">
                                    <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-600 hover:text-theme-1">
                                        <input
                                            type="radio"
                                            name="category"
                                            :value="cat.slug"
                                            v-model="selectedCategory"
                                            @change="applyFilters"
                                            class="accent-theme-1"
                                        />
                                        {{ cat.name }}
                                        <span class="text-gray-400 ml-auto">({{ cat.products_count }})</span>
                                    </label>
                                </li>
                            </ul>
                        </div>

                        <div class="dmart-filter-section" v-if="filters?.brands?.length">
                            <h3 class="dmart-filter-title">Brands</h3>
                            <ul class="space-y-2">
                                <li v-for="brand in filters.brands" :key="brand.id">
                                    <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-600 hover:text-theme-1">
                                        <input
                                            type="radio"
                                            name="brand"
                                            :value="brand.slug"
                                            v-model="selectedBrand"
                                            @change="applyFilters"
                                            class="accent-theme-1"
                                        />
                                        {{ brand.name }}
                                        <span class="text-gray-400 ml-auto">({{ brand.products_count }})</span>
                                    </label>
                                </li>
                            </ul>
                        </div>

                        <div class="dmart-filter-section">
                            <h3 class="dmart-filter-title">Price Range</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between gap-2 text-xs font-semibold text-gray-500">
                                    <span>{{ formatPrice(minPrice) }}</span>
                                    <span>{{ formatPrice(maxPrice) }}</span>
                                </div>

                                <div class="relative pt-1 pb-2">
                                    <div class="h-1.5 rounded-full bg-gray-200"></div>
                                    <div
                                        class="absolute top-1 h-1.5 rounded-full bg-theme-1"
                                        :style="{
                                            left: `${priceRangePercent.start}%`,
                                            width: `${priceRangePercent.end - priceRangePercent.start}%`,
                                        }"
                                    ></div>

                                    <input
                                        type="range"
                                        v-model.number="minPrice"
                                        :min="minLimit"
                                        :max="maxLimit"
                                        @input="minRangeInput"
                                        @change="applyPriceFilters"
                                        class="dmart-range"
                                    />
                                    <input
                                        type="range"
                                        v-model.number="maxPrice"
                                        :min="minLimit"
                                        :max="maxLimit"
                                        @input="maxRangeInput"
                                        @change="applyPriceFilters"
                                        class="dmart-range"
                                    />
                                </div>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="number"
                                        v-model.number="minPrice"
                                        :min="minLimit"
                                        :max="maxPrice"
                                        @change="applyPriceFilters"
                                        class="w-full border rounded-lg px-3 py-2 text-sm"
                                    />
                                    <span class="text-gray-400">—</span>
                                    <input
                                        type="number"
                                        v-model.number="maxPrice"
                                        :min="minPrice"
                                        :max="maxLimit"
                                        @change="applyPriceFilters"
                                        class="w-full border rounded-lg px-3 py-2 text-sm"
                                    />
                                </div>
                            </div>
                        </div>

                        <button
                            v-if="activeFilterCount > 0"
                            @click="clearFilters"
                            class="w-full text-center text-sm font-semibold py-2 rounded-lg border hover:bg-gray-50 transition-colors text-theme-1 border-theme-1"
                        >
                            Clear All Filters ({{ activeFilterCount }})
                        </button>
                    </aside>

                    <!-- Main Content -->
                    <div>
                        <!-- Toolbar -->
                        <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b">
                            <div class="flex items-center gap-3">
                                <button
                                    @click="mobileFiltersOpen = true"
                                    class="lg:hidden flex items-center gap-1.5 px-3 py-2 border rounded-lg text-sm"
                                >
                                    <SlidersHorizontal class="w-4 h-4" />
                                    Filters
                                    <span
                                        v-if="activeFilterCount > 0"
                                        class="w-5 h-5 rounded-full text-white text-xs flex items-center justify-center bg-theme-1"
                                    >{{ activeFilterCount }}</span>
                                </button>
                                <p class="text-sm text-gray-500">
                                    Showing <strong>{{ products.data.length }}</strong> of <strong>{{ products.total }}</strong> products
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="hidden sm:flex items-center gap-1 border rounded-lg p-0.5">
                                    <button
                                        @click="viewMode = 'grid'"
                                        class="p-1.5 rounded"
                                        :class="viewMode === 'grid' ? 'bg-theme-1 text-white' : 'text-gray-400'"
                                    >
                                        <Grid3x3 class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="viewMode = 'list'"
                                        class="p-1.5 rounded"
                                        :class="viewMode === 'list' ? 'bg-theme-1 text-white' : 'text-gray-400'"
                                    >
                                        <List class="w-4 h-4" />
                                    </button>
                                </div>
                                <select
                                    v-model="sortBy"
                                    class="border rounded-lg px-3 py-2 text-sm bg-white focus:ring-theme-1 focus:border-theme-1"
                                >
                                    <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Products Grid -->
                        <div
                            v-if="products.data.length > 0"
                            :class="viewMode === 'grid' ? `grid grid-cols-2 ${productsGridClass} gap-5` : 'space-y-4'"
                        >
                            <ProductCard
                                v-for="product in products.data"
                                :key="product.id"
                                :product="product"
                                :variant="viewMode === 'list' ? 'list' : 'default'"
                                @quick-view="quickViewSlug = $event"
                            />
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-20">
                            <div class="text-6xl mb-4">🔍</div>
                            <h3 class="text-xl font-bold mb-2 text-title font-title">
                                No products found
                            </h3>
                            <p class="text-gray-500 mb-6">Try adjusting your filters or search criteria.</p>
                            <button @click="clearFilters" class="dmart-btn dmart-btn-primary">Clear All Filters</button>
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
                                    preserve-state
                                    preserve-scroll
                                />
                                <span
                                    v-else
                                    class="px-3 py-2 text-sm text-gray-300"
                                    v-html="link.label"
                                />
                            </template>
                        </nav>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mobile Filters Drawer -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="mobileFiltersOpen" class="fixed inset-0 bg-black/50 z-50" @click="mobileFiltersOpen = false" />
            </Transition>
            <Transition name="slide-left">
                <div v-if="mobileFiltersOpen" class="fixed inset-y-0 left-0 w-[300px] bg-white z-[51] overflow-y-auto shadow-xl">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="font-bold text-lg font-title">Filters</h3>
                        <button @click="mobileFiltersOpen = false"><X class="w-5 h-5" /></button>
                    </div>
                    <div class="p-4 space-y-6">
                        <div class="dmart-filter-section">
                            <h3 class="dmart-filter-title">Categories</h3>
                            <ul class="space-y-2">
                                <li v-for="cat in filters?.categories" :key="cat.id">
                                    <label class="flex items-center gap-2 cursor-pointer text-sm">
                                        <input type="radio" name="m_category" :value="cat.slug" v-model="selectedCategory" @change="applyFilters" class="accent-theme-1" />
                                        {{ cat.name }} ({{ cat.products_count }})
                                    </label>
                                </li>
                            </ul>
                        </div>
                        <div class="dmart-filter-section" v-if="filters?.brands?.length">
                            <h3 class="dmart-filter-title">Brands</h3>
                            <ul class="space-y-2">
                                <li v-for="brand in filters.brands" :key="brand.id">
                                    <label class="flex items-center gap-2 cursor-pointer text-sm">
                                        <input type="radio" name="m_brand" :value="brand.slug" v-model="selectedBrand" @change="applyFilters" class="accent-theme-1" />
                                        {{ brand.name }} ({{ brand.products_count }})
                                    </label>
                                </li>
                            </ul>
                        </div>
                        <div class="dmart-filter-section">
                            <h3 class="dmart-filter-title">Price Range</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between gap-2 text-xs font-semibold text-gray-500">
                                    <span>{{ formatPrice(minPrice) }}</span>
                                    <span>{{ formatPrice(maxPrice) }}</span>
                                </div>

                                <div class="relative pt-1 pb-2">
                                    <div class="h-1.5 rounded-full bg-gray-200"></div>
                                    <div
                                        class="absolute top-1 h-1.5 rounded-full bg-theme-1"
                                        :style="{
                                            left: `${priceRangePercent.start}%`,
                                            width: `${priceRangePercent.end - priceRangePercent.start}%`,
                                        }"
                                    ></div>

                                    <input
                                        type="range"
                                        v-model.number="minPrice"
                                        :min="minLimit"
                                        :max="maxLimit"
                                        @input="minRangeInput"
                                        @change="applyPriceFilters"
                                        class="dmart-range"
                                    />
                                    <input
                                        type="range"
                                        v-model.number="maxPrice"
                                        :min="minLimit"
                                        :max="maxLimit"
                                        @input="maxRangeInput"
                                        @change="applyPriceFilters"
                                        class="dmart-range"
                                    />
                                </div>

                                <div class="flex items-center gap-2">
                                    <input type="number" v-model.number="minPrice" :min="minLimit" :max="maxPrice" @change="applyPriceFilters" class="w-full border rounded-lg px-3 py-2 text-sm" />
                                    <span>—</span>
                                    <input type="number" v-model.number="maxPrice" :min="minPrice" :max="maxLimit" @change="applyPriceFilters" class="w-full border rounded-lg px-3 py-2 text-sm" />
                                </div>
                            </div>
                        </div>
                        <button v-if="activeFilterCount > 0" @click="clearFilters" class="w-full text-center text-sm py-2 text-theme-1">
                            Clear All
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <QuickViewModal :slug="quickViewSlug" @close="quickViewSlug = null" />
    </ThemeLayout>
</template>

<style scoped>
.slide-left-enter-active, .slide-left-leave-active { transition: transform 0.3s ease; }
.slide-left-enter-from, .slide-left-leave-to { transform: translateX(-100%); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.dmart-range {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 14px;
    background: transparent;
    pointer-events: none;
    -webkit-appearance: none;
    appearance: none;
}

.dmart-range::-webkit-slider-thumb {
    width: 14px;
    height: 14px;
    border-radius: 9999px;
    background: var(--color-theme-1);
    border: 0;
    pointer-events: auto;
    -webkit-appearance: none;
    appearance: none;
    cursor: pointer;
}

.dmart-range::-moz-range-thumb {
    width: 14px;
    height: 14px;
    border-radius: 9999px;
    background: var(--color-theme-1);
    border: 0;
    pointer-events: auto;
    cursor: pointer;
}

.dmart-range::-webkit-slider-runnable-track,
.dmart-range::-moz-range-track {
    background: transparent;
}
</style>

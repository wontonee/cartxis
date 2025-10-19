<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ThemeLayout from '@/../../themes/vortex-default/resources/views/layouts/ThemeLayout.vue';
import ProductCard from '@/../../themes/vortex-default/resources/views/components/ProductCard.vue';
import ProductSkeleton from '@/../../themes/vortex-default/resources/views/components/ProductSkeleton.vue';
import QuickViewModal from '@/../../themes/vortex-default/resources/views/components/QuickViewModal.vue';

interface Product {
    id: number;
    name: string;
    slug: string;
    sku: string;
    price: number;
    special_price: number | null;
    image: string | null;
    rating: number;
    reviews_count: number;
    stock_quantity: number;
    in_stock: boolean;
    brand?: {
        id: number;
        name: string;
        slug: string;
    };
}

interface PaginatedProducts {
    data: Product[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Category {
    id: number;
    name: string;
    slug: string;
    products_count: number;
}

interface Brand {
    id: number;
    name: string;
    slug: string;
    products_count: number;
}

interface Props {
    products: PaginatedProducts;
    filters: {
        categories: Category[];
        brands: Brand[];
        priceRange: {
            min: number;
            max: number;
        };
    };
    activeFilters: {
        category?: string;
        brand?: string;
        search?: string;
        price_min?: number;
        price_max?: number;
        rating?: number;
        in_stock?: boolean;
        sort?: string;
    };
}

const props = defineProps<Props>();

const viewMode = ref<'grid' | 'list'>('grid');
const isLoading = ref(false);
const showQuickView = ref(false);
const selectedProductSlug = ref<string | null>(null);

const sortOptions = [
    { value: 'newest', label: 'Newest' },
    { value: 'price_low', label: 'Price: Low to High' },
    { value: 'price_high', label: 'Price: High to Low' },
    { value: 'name', label: 'Name: A-Z' },
    { value: 'rating', label: 'Best Rating' },
];

// Handle sort change while preserving filters using Inertia
const handleSortChange = (event: Event) => {
    const sort = (event.target as HTMLSelectElement).value;
    const params = new URLSearchParams(window.location.search);
    params.set('sort', sort);
    
    isLoading.value = true;
    router.get(`/products?${params.toString()}`, {}, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

// Handle filter clicks using Inertia with multiple selection support
const handleFilterClick = (filterType: string, filterValue: string, event: Event) => {
    event.preventDefault();
    const params = new URLSearchParams(window.location.search);
    
    // Set or update the filter parameter
    if (filterValue) {
        params.set(filterType, filterValue);
    } else {
        params.delete(filterType);
    }
    
    // Reset to page 1 when filters change
    params.delete('page');
    
    isLoading.value = true;
    router.get(`/products?${params.toString()}`, {}, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

// Handle price filter with multiple selection support
const handlePriceFilter = (minPrice: number | null, maxPrice: number | null, event: Event) => {
    event.preventDefault();
    const params = new URLSearchParams(window.location.search);
    
    if (minPrice !== null) {
        params.set('price_min', minPrice.toString());
    } else {
        params.delete('price_min');
    }
    
    if (maxPrice !== null) {
        params.set('price_max', maxPrice.toString());
    } else {
        params.delete('price_max');
    }
    
    // Reset to page 1 when filters change
    params.delete('page');
    
    isLoading.value = true;
    router.get(`/products?${params.toString()}`, {}, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

// Handle checkbox filters
const handleCheckboxFilter = (filterType: string, checked: boolean, event: Event) => {
    event.preventDefault();
    const params = new URLSearchParams(window.location.search);
    
    if (checked) {
        params.set(filterType, '1');
    } else {
        params.delete(filterType);
    }
    
    // Reset to page 1 when filters change
    params.delete('page');
    
    isLoading.value = true;
    router.get(`/products?${params.toString()}`, {}, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

// Handle pagination using Inertia
const handlePageChange = (page: number, event: Event) => {
    event.preventDefault();
    const params = new URLSearchParams(window.location.search);
    params.set('page', page.toString());
    
    isLoading.value = true;
    router.get(`/products?${params.toString()}`, {}, {
        preserveState: true,
        preserveScroll: false,
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

// Handle quick view
const handleQuickView = (slug: string) => {
    selectedProductSlug.value = slug;
    showQuickView.value = true;
};

const closeQuickView = () => {
    showQuickView.value = false;
    selectedProductSlug.value = null;
};

// Remove individual filter
const removeFilter = (filterType: string, event: Event) => {
    event.preventDefault();
    const params = new URLSearchParams(window.location.search);
    
    // Remove the specific filter
    params.delete(filterType);
    if (filterType === 'price_min' || filterType === 'price_max') {
        params.delete('price_min');
        params.delete('price_max');
    }
    
    isLoading.value = true;
    router.get(`/products?${params.toString()}`, {}, {
        preserveState: true,
        preserveScroll: false,
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

// Clear all filters
const clearAllFilters = (event: Event) => {
    event.preventDefault();
    isLoading.value = true;
    // Keep only the sort parameter if it exists
    const params = new URLSearchParams();
    if (props.activeFilters.sort) {
        params.set('sort', props.activeFilters.sort);
    }
    const url = params.toString() ? `/products?${params.toString()}` : '/products';
    router.get(url, {}, {
        preserveState: true,
        preserveScroll: false,
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return props.activeFilters.category || props.activeFilters.brand || props.activeFilters.search || 
           props.activeFilters.price_min || props.activeFilters.price_max || props.activeFilters.rating || props.activeFilters.in_stock;
});

// Get active filter badges
const activeFilterBadges = computed(() => {
    const badges = [];
    
    if (props.activeFilters.category) {
        const category = props.filters.categories.find((c: any) => c.slug === props.activeFilters.category);
        badges.push({ type: 'category', label: category?.name || props.activeFilters.category, key: 'category' });
    }
    
    if (props.activeFilters.brand) {
        const brand = props.filters.brands.find((b: any) => b.slug === props.activeFilters.brand);
        badges.push({ type: 'brand', label: brand?.name || props.activeFilters.brand, key: 'brand' });
    }
    
    if (props.activeFilters.search) {
        badges.push({ type: 'search', label: `Search: "${props.activeFilters.search}"`, key: 'search' });
    }
    
    if (props.activeFilters.price_min || props.activeFilters.price_max) {
        const min = props.activeFilters.price_min || 0;
        const max = props.activeFilters.price_max || '∞';
        badges.push({ type: 'price', label: `Price: $${min} - $${max}`, key: 'price_min' });
    }
    
    if (props.activeFilters.rating) {
        badges.push({ type: 'rating', label: `${props.activeFilters.rating}+ Stars`, key: 'rating' });
    }
    
    if (props.activeFilters.in_stock) {
        badges.push({ type: 'stock', label: 'In Stock', key: 'in_stock' });
    }
    
    return badges;
});

</script>

<template>
    <ThemeLayout>
        <Head title="Shop All Products" />

        <div class="bg-gray-50 min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">Shop All Products</h1>
                    <p class="text-gray-600">
                        Showing {{ products.from }}-{{ products.to }} of {{ products.total }} products
                    </p>
                </div>

                <!-- Active Filters -->
                <div v-if="hasActiveFilters" class="mb-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <div class="flex items-center justify-between flex-wrap gap-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-medium text-gray-700">Active Filters:</span>
                                <div
                                    v-for="badge in activeFilterBadges"
                                    :key="badge.key"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-full text-sm font-medium border border-indigo-200"
                                >
                                    <span>{{ badge.label }}</span>
                                    <button
                                        @click="removeFilter(badge.key, $event)"
                                        class="hover:bg-indigo-100 rounded-full p-0.5 transition-colors"
                                        :title="`Remove ${badge.type} filter`"
                                    >
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <button
                                @click="clearAllFilters"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:underline transition-colors"
                            >
                                Clear All
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Filters Sidebar -->
                    <aside class="lg:w-64 flex-shrink-0">
                        <div class="bg-white rounded-2xl shadow-md p-6 sticky top-24">
                            <h2 class="text-xl font-bold text-gray-900 mb-6">Filters</h2>

                            <!-- Categories -->
                            <div class="mb-6">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Categories</h3>
                                <div class="space-y-2">
                                    <a
                                        v-for="category in filters.categories.slice(0, 6)"
                                        :key="category.id"
                                        href="#"
                                        @click="handleFilterClick('category', category.slug, $event)"
                                        class="flex items-center justify-between text-sm text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer"
                                        :class="{ 'text-indigo-600 font-semibold': activeFilters.category === category.slug }"
                                    >
                                        <span>{{ category.name }}</span>
                                        <span class="text-xs text-gray-400">({{ category.products_count }})</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Brands -->
                            <div class="mb-6">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Brands</h3>
                                <div class="space-y-2">
                                    <a
                                        v-for="brand in filters.brands.slice(0, 6)"
                                        :key="brand.id"
                                        href="#"
                                        @click="handleFilterClick('brand', brand.slug, $event)"
                                        class="flex items-center justify-between text-sm text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer"
                                        :class="{ 'text-indigo-600 font-semibold': activeFilters.brand === brand.slug }"
                                    >
                                        <span>{{ brand.name }}</span>
                                        <span class="text-xs text-gray-400">({{ brand.products_count }})</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Price Range -->
                            <div class="mb-6">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Price Range</h3>
                                <div class="space-y-2">
                                    <a
                                        href="#"
                                        @click="handlePriceFilter(0, 50, $event)"
                                        class="block text-sm text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer"
                                        :class="{ 'text-indigo-600 font-semibold': activeFilters.price_min === 0 && activeFilters.price_max === 50 }"
                                    >
                                        Under $50
                                    </a>
                                    <a
                                        href="#"
                                        @click="handlePriceFilter(50, 100, $event)"
                                        class="block text-sm text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer"
                                        :class="{ 'text-indigo-600 font-semibold': activeFilters.price_min === 50 && activeFilters.price_max === 100 }"
                                    >
                                        $50 - $100
                                    </a>
                                    <a
                                        href="#"
                                        @click="handlePriceFilter(100, 200, $event)"
                                        class="block text-sm text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer"
                                        :class="{ 'text-indigo-600 font-semibold': activeFilters.price_min === 100 && activeFilters.price_max === 200 }"
                                    >
                                        $100 - $200
                                    </a>
                                    <a
                                        href="#"
                                        @click="handlePriceFilter(200, null, $event)"
                                        class="block text-sm text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer"
                                        :class="{ 'text-indigo-600 font-semibold': activeFilters.price_min === 200 && !activeFilters.price_max }"
                                    >
                                        Over $200
                                    </a>
                                </div>
                            </div>

                            <!-- Rating -->
                            <div class="mb-6">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Rating</h3>
                                <div class="space-y-2">
                                    <a
                                        v-for="rating in [4, 3, 2, 1]"
                                        :key="rating"
                                        href="#"
                                        @click="handleFilterClick('rating', rating.toString(), $event)"
                                        class="flex items-center text-sm text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer"
                                        :class="{ 'text-indigo-600 font-semibold': activeFilters.rating === rating }"
                                    >
                                        <span class="text-yellow-400 mr-2">{{ '★'.repeat(rating) }}{{ '☆'.repeat(5 - rating) }}</span>
                                        <span>& Up</span>
                                    </a>
                                </div>
                            </div>

                            <!-- In Stock -->
                            <div>
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        :checked="activeFilters.in_stock"
                                        @change="handleCheckboxFilter('in_stock', ($event.target as HTMLInputElement).checked, $event)"
                                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">In Stock Only</span>
                                </label>
                            </div>
                        </div>
                    </aside>

                    <!-- Main Content -->
                    <main class="flex-1">
                        <!-- Toolbar -->
                        <div class="bg-white rounded-2xl shadow-md p-4 mb-6 flex items-center justify-between">
                            <!-- View Toggle -->
                            <div class="flex gap-2">
                                <button
                                    @click="viewMode = 'grid'"
                                    class="p-2 rounded-lg transition-colors"
                                    :class="viewMode === 'grid' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                >
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                </button>
                                <button
                                    @click="viewMode = 'list'"
                                    class="p-2 rounded-lg transition-colors"
                                    :class="viewMode === 'list' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                >
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Sort Dropdown -->
                            <div class="flex items-center gap-3">
                                <label class="text-sm font-medium text-gray-700">Sort by:</label>
                                <select
                                    :value="activeFilters.sort || 'newest'"
                                    @change="handleSortChange"
                                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option
                                        v-for="option in sortOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Product Grid -->
                        <div
                            class="grid gap-6"
                            :class="{
                                'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3': viewMode === 'grid',
                                'grid-cols-1': viewMode === 'list'
                            }"
                        >
                            <!-- Show skeletons when loading -->
                            <template v-if="isLoading">
                                <ProductSkeleton v-for="i in 9" :key="`skeleton-${i}`" />
                            </template>
                            
                            <!-- Show actual products when not loading -->
                            <template v-else>
                                <ProductCard
                                    v-for="product in products.data"
                                    :key="product.id"
                                    :product="product"
                                    @quick-view="handleQuickView"
                                />
                            </template>
                        </div>

                        <!-- Empty State -->
                        <div v-if="products.data.length === 0 && !isLoading" class="text-center py-16">
                            <div class="text-6xl mb-4">🔍</div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">No products found</h3>
                            <p class="text-gray-600 mb-6">Try adjusting your filters or search terms</p>
                            <a
                                href="/products"
                                @click="handleFilterClick('/products', $event)"
                                class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors"
                            >
                                Clear Filters
                            </a>
                        </div>

                        <!-- Pagination -->
                        <div v-if="products.last_page > 1" class="mt-8 flex justify-center">
                            <nav class="flex items-center gap-2">
                                <a
                                    v-if="products.current_page > 1"
                                    href="#"
                                    @click="handlePageChange(products.current_page - 1, $event)"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                                >
                                    Previous
                                </a>

                                <template v-for="page in products.last_page" :key="page">
                                    <a
                                        v-if="Math.abs(page - products.current_page) <= 2 || page === 1 || page === products.last_page"
                                        href="#"
                                        @click="handlePageChange(page, $event)"
                                        class="px-4 py-2 rounded-lg transition-colors"
                                        :class="page === products.current_page
                                            ? 'bg-indigo-600 text-white'
                                            : 'border border-gray-300 text-gray-700 hover:bg-gray-50'"
                                    >
                                        {{ page }}
                                    </a>
                                    <span
                                        v-else-if="Math.abs(page - products.current_page) === 3"
                                        class="px-2 text-gray-400"
                                    >
                                        ...
                                    </span>
                                </template>

                                <a
                                    v-if="products.current_page < products.last_page"
                                    href="#"
                                    @click="handlePageChange(products.current_page + 1, $event)"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                                >
                                    Next
                                </a>
                            </nav>
                        </div>
                    </main>
                </div>
            </div>
        </div>

        <!-- Quick View Modal -->
        <QuickViewModal
            :is-open="showQuickView"
            :product-slug="selectedProductSlug"
            @close="closeQuickView"
        />
    </ThemeLayout>
</template>

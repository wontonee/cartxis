<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import * as reviewRoutes from '@/routes/admin/catalog/reviews';
import Pagination from '@/components/Admin/Pagination.vue';
import { 
    Search, Filter, X, CheckCircle, Trash2, Edit, Eye, 
    Star, MessageSquare, PlusCircle, MinusCircle, 
    ArrowUp, ArrowDown, ArrowUpDown, ThumbsUp, ThumbsDown 
} from 'lucide-vue-next';

interface Review {
    id: number;
    product: {
        id: number;
        name: string;
        slug: string;
        featured_image?: string;
    };
    reviewer: string;
    rating: number;
    title: string;
    comment: string;
    status: string;
    admin_reply: string | null;
    admin_replier: { id: number; name: string } | null;
    admin_replied_at: string | null;
    created_at: string;
}

interface Product {
    id: number;
    name: string;
}

interface Props {
    reviews: {
        data: Review[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: {
        total: number;
        pending: number;
        approved: number;
        rejected: number;
    };
    products: Product[];
    filters: {
        status?: string;
        rating?: string;
        product_id?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

const selectedReviews = ref<number[]>([]);
const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedRating = ref(props.filters.rating || '');
const selectedProduct = ref(props.filters.product_id || '');
const isLoading = ref(false);
const showBulkDeleteModal = ref(false);
const expandedRows = ref<number[]>([]);

const selectAll = computed(() => {
    return props.reviews.data.length > 0 && selectedReviews.value.length === props.reviews.data.length;
});

const someSelected = computed(() => {
  return selectedReviews.value.length > 0 && selectedReviews.value.length < props.reviews.data.length;
});

function toggleRow(id: number) {
  if (expandedRows.value.includes(id)) {
    expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
  } else {
    expandedRows.value.push(id);
  }
}

function toggleSelectAll() {
    if (selectAll.value) {
        selectedReviews.value = [];
    } else {
        selectedReviews.value = props.reviews.data.map(r => r.id);
    }
}

function applyFilters() {
    router.get(reviewRoutes.index().url, {
        search: search.value || undefined,
        status: selectedStatus.value || undefined,
        rating: selectedRating.value || undefined,
        product_id: selectedProduct.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilters() {
    search.value = '';
    selectedStatus.value = '';
    selectedRating.value = '';
    selectedProduct.value = '';
    applyFilters();
}

function changePage(page: number) {
    if (page < 1 || page > props.reviews.last_page) return;
    
    router.get(reviewRoutes.index().url, {
        ...props.filters,
        page,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function bulkApprove() {
    if (selectedReviews.value.length === 0 || isLoading.value) return;
    
    isLoading.value = true;
    router.post(reviewRoutes.bulkAction().url, {
        action: 'approve',
        review_ids: selectedReviews.value,
    }, {
        onFinish: () => {
            isLoading.value = false;
            selectedReviews.value = [];
        },
    });
}

function bulkReject() {
    if (selectedReviews.value.length === 0 || isLoading.value) return;
    
    isLoading.value = true;
    router.post(reviewRoutes.bulkAction().url, {
        action: 'reject',
        review_ids: selectedReviews.value,
    }, {
        onFinish: () => {
            isLoading.value = false;
            selectedReviews.value = [];
        },
    });
}

function bulkDelete() {
    if (selectedReviews.value.length === 0 || isLoading.value) return;
    
    showBulkDeleteModal.value = true;
}

function confirmBulkDelete() {
    isLoading.value = true;
    router.post(reviewRoutes.bulkAction().url, {
        action: 'delete',
        review_ids: selectedReviews.value,
    }, {
        onFinish: () => {
            isLoading.value = false;
            selectedReviews.value = [];
            showBulkDeleteModal.value = false;
        },
    });
}
</script>

<template>
    <Head title="Product Reviews" />
    
    <AdminLayout title="Product Reviews">
        <div class="p-6 space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Product Reviews</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage customer reviews and ratings</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Reviews -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Reviews</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                            <MessageSquare class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <!-- Pending -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
                            <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">{{ stats.pending }}</p>
                        </div>
                        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl">
                            <Eye class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                        </div>
                    </div>
                </div>

                <!-- Approved -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Approved</p>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ stats.approved }}</p>
                        </div>
                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                            <ThumbsUp class="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>

                <!-- Rejected -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rejected</p>
                            <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">{{ stats.rejected }}</p>
                        </div>
                        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl">
                            <ThumbsDown class="w-6 h-6 text-red-600 dark:text-red-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Card -->
             <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                         <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="search"
                                @keyup.enter="applyFilters"
                                type="text"
                                placeholder="Search reviews..."
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
                            />
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
                        <div class="relative">
                            <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <select
                                v-model="selectedStatus"
                                @change="applyFilters"
                                class="w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
                            >
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                             <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <ArrowUpDown class="w-3 h-3 text-gray-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Rating Filter -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Rating</label>
                         <div class="relative">
                            <Star class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <select
                                v-model="selectedRating"
                                @change="applyFilters"
                                class="w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
                            >
                                <option value="">All Ratings</option>
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <ArrowUpDown class="w-3 h-3 text-gray-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Product Filter -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Product</label>
                        <div class="relative">
                            <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <select
                                v-model="selectedProduct"
                                @change="applyFilters"
                                class="w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
                            >
                                <option value="">All Products</option>
                                <option v-for="product in products" :key="product.id" :value="product.id">
                                    {{ product.name }}
                                </option>
                            </select>
                             <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <ArrowUpDown class="w-3 h-3 text-gray-400" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clear Filters Button -->
                <div v-if="search || selectedStatus || selectedRating || selectedProduct" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button
                        @click="clearFilters"
                         class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors flex items-center gap-2"
                    >
                        <X class="w-4 h-4" />
                        Clear Filters
                    </button>
                </div>
            </div>

             <!-- Bulk Actions -->
            <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                <div v-if="selectedReviews.length > 0" class="bg-blue-600 rounded-xl shadow-lg p-3 text-white flex items-center justify-between sticky top-4 z-10 px-6">
                    <span class="text-sm font-semibold flex items-center">
                        <CheckCircle class="w-4 h-4 mr-2" />
                        {{ selectedReviews.length }} {{ selectedReviews.length === 1 ? 'review' : 'reviews' }} selected
                    </span>
                    <div class="flex gap-2">
                        <button
                            @click="bulkApprove"
                            :disabled="isLoading"
                            class="px-3 py-1.5 text-xs font-bold text-green-600 bg-white rounded-lg hover:bg-green-50 transition-colors uppercase tracking-wide disabled:opacity-50"
                        >
                            Approve
                        </button>
                        <button
                            @click="bulkReject"
                            :disabled="isLoading"
                            class="px-3 py-1.5 text-xs font-bold text-yellow-600 bg-white rounded-lg hover:bg-yellow-50 transition-colors uppercase tracking-wide disabled:opacity-50"
                        >
                            Reject
                        </button>
                        <div class="w-px h-6 bg-blue-400 mx-1"></div>
                        <button
                            @click="bulkDelete"
                            :disabled="isLoading"
                            class="px-3 py-1.5 text-xs font-bold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors flex items-center uppercase tracking-wide disabled:opacity-50"
                        >
                            <Trash2 class="w-3 h-3 mr-1.5" />
                            Delete
                        </button>
                    </div>
                </div>
            </transition>

            <!-- Reviews Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="w-12 px-6 py-4">
                                    <input
                                        type="checkbox"
                                        :checked="selectAll"
                                        :indeterminate.prop="someSelected"
                                        @change="toggleSelectAll"
                                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 transition-all"
                                    />
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product</th>
                                <th scope="col" class="hidden md:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reviewer</th>
                                <th scope="col" class="hidden sm:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rating</th>
                                <th scope="col" class="hidden lg:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Review</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="hidden xl:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                            <template v-for="review in reviews.data" :key="review.id">
                            <tr class="group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                                <td class="px-6 py-4 relative">
                                    <div class="flex items-center">
                                         <button 
                                            @click="toggleRow(review.id)" 
                                            class="xl:hidden absolute left-2 p-1 text-blue-600 hover:text-blue-800 focus:outline-none"
                                        >
                                            <MinusCircle v-if="expandedRows.includes(review.id)" class="w-5 h-5 fill-blue-100 dark:fill-blue-900/30" />
                                            <PlusCircle v-else class="w-5 h-5 fill-blue-50 dark:fill-blue-900/20" />
                                        </button>
                                        <input
                                            type="checkbox"
                                            :value="review.id"
                                            v-model="selectedReviews"
                                            class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer transition-all ml-4 xl:ml-0"
                                        />
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                     <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 flex-shrink-0 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 flex items-center justify-center">
                                            <img v-if="review.product.featured_image" :src="`/storage/${review.product.featured_image}`" :alt="review.product.name" class="w-full h-full object-cover" />
                                            <div v-else class="text-xs font-semibold text-gray-400 text-center p-1 leading-tight">{{ review.product.name.substring(0, 2).toUpperCase() }}</div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900 dark:text-white line-clamp-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ review.product.name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ review.product.slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center border border-gray-200 dark:border-gray-600">
                                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ review.reviewer.charAt(0).toUpperCase() }}</span>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ review.reviewer }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center bg-yellow-50 dark:bg-yellow-900/10 px-2 py-1 rounded-lg w-fit">
                                         <span class="text-sm font-bold text-yellow-700 dark:text-yellow-500 mr-1.5">{{ review.rating }}.0</span>
                                        <div class="flex">
                                            <template v-for="star in 5" :key="star">
                                                <Star
                                                    :class="star <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300 dark:text-gray-600'"
                                                    class="w-3.5 h-3.5"
                                                />
                                            </template>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden lg:table-cell px-6 py-4">
                                    <div class="max-w-xs">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1 line-clamp-1">{{ review.title }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">{{ review.comment }}</div>
                                        <div v-if="review.admin_reply" class="mt-2 flex items-center text-xs text-green-600 dark:text-green-400 font-medium bg-green-50 dark:bg-green-900/10 w-fit px-2 py-1 rounded">
                                            <MessageSquare class="w-3 h-3 mr-1.5" />
                                            Admin replied
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span
                                        :class="{
                                            'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800': review.status === 'approved',
                                            'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800': review.status === 'pending',
                                            'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800': review.status === 'rejected'
                                        }"
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border shadow-sm"
                                    >
                                        <template v-if="review.status === 'approved'"><CheckCircle class="w-3 h-3 mr-1" /> Approved</template>
                                        <template v-else-if="review.status === 'pending'"><Eye class="w-3 h-3 mr-1" /> Pending</template>
                                        <template v-else><X class="w-3 h-3 mr-1" /> Rejected</template>
                                    </span>
                                </td>
                                <td class="hidden xl:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-mono">
                                    {{ new Date(review.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                        <Link
                                            :href="reviewRoutes.show({ review: review.id }).url"
                                            class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                            title="View Details"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <!-- Expanded Mobile Row -->
                            <tr v-if="expandedRows.includes(review.id)" class="bg-gray-50/50 dark:bg-gray-900/50 xl:hidden">
                                <td colspan="8" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                                        <div class="md:hidden flex flex-col gap-1">
                                             <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Reviewer</span>
                                             <div class="flex items-center">
                                                <div class="flex-shrink-0 h-6 w-6 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center border border-gray-200 dark:border-gray-600 mr-2">
                                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ review.reviewer.charAt(0).toUpperCase() }}</span>
                                                </div>
                                                <span class="text-gray-900 dark:text-white">{{ review.reviewer }}</span>
                                            </div>
                                        </div>
                                        <div class="sm:hidden flex flex-col gap-1">
                                             <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Rating</span>
                                             <div class="flex items-center">
                                                <span class="text-sm font-bold text-gray-900 dark:text-white mr-2">{{ review.rating }}/5</span>
                                                <div class="flex">
                                                    <template v-for="star in 5" :key="star">
                                                        <Star
                                                            :class="star <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300 dark:text-gray-600'"
                                                            class="w-3.5 h-3.5"
                                                        />
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                         <div class="lg:hidden flex flex-col gap-2 col-span-1 md:col-span-2">
                                            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Review Content</span>
                                             <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                                                <div class="font-bold text-gray-900 dark:text-white mb-1">{{ review.title }}</div>
                                                <div class="text-gray-600 dark:text-gray-400 italic">"{{ review.comment }}"</div>
                                                <div v-if="review.admin_reply" class="mt-3 pl-3 border-l-2 border-green-500">
                                                    <div class="text-xs text-green-600 font-semibold mb-0.5">Admin Response</div>
                                                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ review.admin_reply }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="xl:hidden flex flex-col gap-1">
                                            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Date</span>
                                            <span class="text-gray-700 dark:text-gray-300 font-mono">{{ new Date(review.created_at).toLocaleString() }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </template>
                            <tr v-if="reviews.data.length === 0">
                                <td colspan="8" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                                            <MessageSquare class="w-8 h-8" />
                                        </div>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">No reviews found</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm">Try adjusting your search or filters.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <Pagination :data="reviews" resource-name="reviews" />
            </div>

            <!-- Bulk Delete Confirmation Modal -->
                        <div v-if="showBulkDeleteModal" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Delete Reviews</h3>
                    <p class="text-sm text-gray-500 mb-6">
                        Are you sure you want to delete {{ selectedReviews.length }} review{{ selectedReviews.length > 1 ? 's' : '' }}? This action cannot be undone.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button
                            @click="showBulkDeleteModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            @click="confirmBulkDelete"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import * as reviewRoutes from '@/routes/admin/catalog/reviews';
import Pagination from '@/components/Admin/Pagination.vue';

interface Review {
    id: number;
    product: {
        id: number;
        name: string;
        slug: string;
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

const selectAll = computed(() => {
    return props.reviews.data.length > 0 && selectedReviews.value.length === props.reviews.data.length;
});

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
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Reviews -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Reviews</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.total }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Pending -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Pending</p>
                            <p class="text-3xl font-bold text-yellow-600 mt-1">{{ stats.pending }}</p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Approved -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Approved</p>
                            <p class="text-3xl font-bold text-green-600 mt-1">{{ stats.approved }}</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Rejected -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Rejected</p>
                            <p class="text-3xl font-bold text-red-600 mt-1">{{ stats.rejected }}</p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Search by title, comment, product..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            v-model="selectedStatus"
                            @change="applyFilters"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <!-- Rating Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                        <select
                            v-model="selectedRating"
                            @change="applyFilters"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">All Ratings</option>
                            <option value="5">5 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="2">2 Stars</option>
                            <option value="1">1 Star</option>
                        </select>
                    </div>

                    <!-- Product Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                        <select
                            v-model="selectedProduct"
                            @change="applyFilters"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">All Products</option>
                            <option v-for="product in products" :key="product.id" :value="product.id">
                                {{ product.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Clear Filters Button -->
                <div v-if="search || selectedStatus || selectedRating || selectedProduct" class="mt-4">
                    <button
                        @click="clearFilters"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div v-if="selectedReviews.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-blue-900">
                        {{ selectedReviews.length }} review(s) selected
                    </span>
                    <div class="flex gap-2">
                        <button
                            @click="bulkApprove"
                            :disabled="isLoading"
                            class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50"
                        >
                            Approve
                        </button>
                        <button
                            @click="bulkReject"
                            :disabled="isLoading"
                            class="px-3 py-1.5 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 disabled:opacity-50"
                        >
                            Reject
                        </button>
                        <button
                            @click="bulkDelete"
                            :disabled="isLoading"
                            class="px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Reviews Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-12 px-6 py-3">
                                    <input
                                        type="checkbox"
                                        :checked="selectAll"
                                        @change="toggleSelectAll"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviewer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Review</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="review in reviews.data" :key="review.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input
                                        type="checkbox"
                                        :value="review.id"
                                        v-model="selectedReviews"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ review.product.name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ review.reviewer }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <template v-for="star in 5" :key="star">
                                            <svg
                                                :class="star <= review.rating ? 'text-yellow-400' : 'text-gray-300'"
                                                class="w-5 h-5"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 mb-1">{{ review.title }}</div>
                                    <div class="text-sm text-gray-500 line-clamp-2">{{ review.comment }}</div>
                                    <div v-if="review.admin_reply" class="mt-2 flex items-center text-xs text-green-600">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Admin replied
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-800': review.status === 'approved',
                                            'bg-yellow-100 text-yellow-800': review.status === 'pending',
                                            'bg-red-100 text-red-800': review.status === 'rejected'
                                        }"
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    >
                                        {{ review.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ review.created_at }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <Link
                                        :href="reviewRoutes.show({ review: review.id }).url"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="View Details"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="reviews.data.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                    No reviews found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <Pagination :data="reviews" resource-name="reviews" />
            </div>

            <!-- Bulk Delete Confirmation Modal -->
            <div v-if="showBulkDeleteModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
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

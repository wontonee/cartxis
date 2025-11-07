<script setup lang="ts">
import { ref } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import * as reviewRoutes from '@/routes/admin/catalog/reviews';

interface Review {
    id: number;
    product: {
        id: number;
        name: string;
        slug: string;
    };
    reviewer: string;
    user_id: number | null;
    rating: number;
    title: string;
    comment: string;
    status: string;
    admin_reply: string | null;
    admin_replier: {
        id: number;
        name: string;
    } | null;
    admin_replied_at: string | null;
    helpful_count: number;
    verified_purchase: boolean;
    created_at: string;
}

interface Props {
    review: Review;
}

const props = defineProps<Props>();

const replyText = ref('');
const isLoading = ref(false);
const showDeleteModal = ref(false);
const deleteId = ref<number | null>(null);

function approveReview() {
    if (isLoading.value) return;
    
    isLoading.value = true;
    router.post(reviewRoutes.updateStatus({ review: props.review.id }).url, {
        status: 'approved',
    }, {
        onFinish: () => {
            isLoading.value = false;
        },
    });
}

function rejectReview() {
    if (isLoading.value) return;
    
    isLoading.value = true;
    router.post(reviewRoutes.updateStatus({ review: props.review.id }).url, {
        status: 'rejected',
    }, {
        onFinish: () => {
            isLoading.value = false;
        },
    });
}

function submitReply() {
    if (!replyText.value.trim() || isLoading.value) return;
    
    isLoading.value = true;
    router.post(reviewRoutes.reply({ review: props.review.id }).url, {
        reply: replyText.value,
    }, {
        onSuccess: () => {
            replyText.value = '';
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
}

function deleteReview() {
    showDeleteModal.value = true;
    deleteId.value = props.review.id;
}

function confirmDelete() {
    if (!deleteId.value) return;
    
    router.delete(reviewRoutes.destroy({ review: deleteId.value }).url, {
        onFinish: () => {
            showDeleteModal.value = false;
            deleteId.value = null;
        },
    });
}
</script>

<template>
    <Head :title="`Review by ${review.reviewer}`" />
    
    <AdminLayout :title="`Review by ${review.reviewer}`">
        <div class="p-6 space-y-6">
            <!-- Header with Actions -->
            <div class="flex items-center justify-between">
                <Link
                    :href="reviewRoutes.index().url"
                    class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Reviews
                </Link>

                <div class="flex items-center gap-2">
                    <button
                        v-if="review.status === 'pending'"
                        @click="approveReview"
                        :disabled="isLoading"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Approve
                    </button>
                    <button
                        v-if="review.status === 'pending' || review.status === 'approved'"
                        @click="rejectReview"
                        :disabled="isLoading"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Reject
                    </button>
                    <button
                        @click="deleteReview"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Review Details Card -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 h-12 w-12 bg-gray-200 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ review.reviewer }}</h3>
                                    <div class="flex items-center gap-2 mt-1">
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
                                        <span class="text-sm text-gray-500">{{ review.created_at }}</span>
                                    </div>
                                </div>
                            </div>
                            <span
                                :class="{
                                    'bg-green-100 text-green-800': review.status === 'approved',
                                    'bg-yellow-100 text-yellow-800': review.status === 'pending',
                                    'bg-red-100 text-red-800': review.status === 'rejected'
                                }"
                                class="px-3 py-1 text-sm font-semibold rounded-full"
                            >
                                {{ review.status }}
                            </span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h4 class="text-xl font-semibold text-gray-900 mb-2">{{ review.title }}</h4>
                                <p class="text-gray-700 leading-relaxed">{{ review.comment }}</p>
                            </div>

                            <div v-if="review.verified_purchase" class="flex items-center gap-2 text-sm text-green-600">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Verified Purchase
                            </div>

                            <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                                    </svg>
                                    <span>{{ review.helpful_count }} found this helpful</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Reply Card -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Admin Response</h3>
                        
                        <div v-if="review.admin_reply" class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 h-10 w-10 bg-blue-200 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ review.admin_replier?.name || 'Admin' }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ review.admin_replied_at }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700">{{ review.admin_reply }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-else>
                            <div class="space-y-3">
                                <label class="block text-sm font-medium text-gray-700">Reply to Customer</label>
                                <textarea
                                    v-model="replyText"
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Write your response..."
                                ></textarea>
                                <button
                                    @click="submitReply"
                                    :disabled="!replyText.trim() || isLoading"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                    Send Reply
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Product Info Card -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Product</h3>
                        <div>
                            <Link
                                :href="`/admin/catalog/products/${review.product.id}`"
                                class="text-blue-600 hover:text-blue-900 font-medium"
                            >
                                {{ review.product.name }}
                            </Link>
                        </div>
                    </div>

                    <!-- Review Stats Card -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Review Stats</h3>
                        <dl class="space-y-3">
                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-600">Rating</dt>
                                <dd class="flex items-center gap-1">
                                    <template v-for="star in 5" :key="star">
                                        <svg
                                            :class="star <= review.rating ? 'text-yellow-400' : 'text-gray-300'"
                                            class="w-4 h-4"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </template>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-600">Status</dt>
                                <dd>
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-800': review.status === 'approved',
                                            'bg-yellow-100 text-yellow-800': review.status === 'pending',
                                            'bg-red-100 text-red-800': review.status === 'rejected'
                                        }"
                                        class="px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ review.status }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-600">Helpful Votes</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ review.helpful_count }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-600">Verified Purchase</dt>
                                <dd>
                                    <span v-if="review.verified_purchase" class="text-green-600">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    <span v-else class="text-gray-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-600">Submitted</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ review.created_at }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <ConfirmDeleteModal
                v-model:show="showDeleteModal"
                title="Review"
                message="Are you sure you want to delete this review? This action cannot be undone."
                @confirm="confirmDelete"
            />
        </div>
    </AdminLayout>
</template>

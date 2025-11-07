<template>
    <AdminLayout>
        <Head :title="`Edit Block: ${blockData.title}`" />

        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Edit Block</h1>
                        <p class="mt-1 text-sm text-gray-600">Update block: {{ blockData.title }}</p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            @click="deleteBlock"
                            class="px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-lg hover:bg-red-50"
                        >
                            Delete
                        </button>
                        <Link
                            :href="blockRoutes.index.url()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </Link>
                    </div>
                </div>

                <!-- Meta Information -->
                <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600 space-y-1">
                    <div class="flex gap-4">
                        <span><strong>Created:</strong> {{ formatDate(blockData.created_at) }} by {{ blockData.creator?.name }}</span>
                        <span><strong>Updated:</strong> {{ formatDate(blockData.updated_at) }} by {{ blockData.updater?.name }}</span>
                    </div>
                    <div>
                        <strong>Usage:</strong> <code class="text-blue-600 bg-white px-2 py-1 rounded">@block('{{ blockData.identifier }}')</code>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-lg shadow">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': form.errors.title }"
                        />
                        <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                    </div>

                    <!-- Identifier -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Identifier <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <input
                                v-model="form.identifier"
                                type="text"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.identifier }"
                                @blur="checkIdentifier"
                            />
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Use format: lowercase-with-hyphens. Used as <code class="text-blue-600">@block('{{ form.identifier }}')</code>
                        </p>
                        <p v-if="form.errors.identifier" class="mt-1 text-sm text-red-600">{{ form.errors.identifier }}</p>
                        <p v-if="identifierStatus === 'taken'" class="mt-1 text-sm text-red-600">This identifier is already taken</p>
                        <p v-if="identifierStatus === 'available'" class="mt-1 text-sm text-green-600">This identifier is available</p>
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Block Type <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': form.errors.type }"
                        >
                            <option value="">Select a type</option>
                            <option value="text">Text - Plain text content</option>
                            <option value="html">HTML - Rich HTML content</option>
                            <option value="banner">Banner - Image with CTA button</option>
                            <option value="promotion">Promotion - Special offer/discount</option>
                            <option value="newsletter">Newsletter - Email signup form</option>
                        </select>
                        <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
                    </div>

                    <!-- Dynamic Content Fields Based on Type -->
                    <div v-if="form.type" class="border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Block Content</h3>

                        <!-- Text Type -->
                        <div v-if="form.type === 'text'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Text Content</label>
                            <textarea
                                v-model="form.content"
                                rows="6"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter your text content..."
                            ></textarea>
                        </div>

                        <!-- HTML Type -->
                        <div v-if="form.type === 'html'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rich Text Content</label>
                            <TipTapEditor
                                v-model="form.content"
                                placeholder="Write your HTML content here..."
                                :show-character-count="true"
                            />
                        </div>

                        <!-- Banner Type -->
                        <div v-if="form.type === 'banner'" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Banner Title</label>
                                <input
                                    v-model="bannerData.title"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="e.g., Welcome to Our Store"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea
                                    v-model="bannerData.description"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="Brief description..."
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                                <input
                                    v-model="bannerData.image"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="/images/banner.jpg"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Image Alt Text</label>
                                <input
                                    v-model="bannerData.alt"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="Image description"
                                />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">CTA Button Text</label>
                                    <input
                                        v-model="bannerData.cta_text"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                        placeholder="Shop Now"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">CTA Button URL</label>
                                    <input
                                        v-model="bannerData.cta_url"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                        placeholder="/shop"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Promotion Type -->
                        <div v-if="form.type === 'promotion'" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Heading</label>
                                <input
                                    v-model="promotionData.heading"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="e.g., Summer Sale"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Discount</label>
                                <input
                                    v-model="promotionData.discount"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="e.g., Up to 50% OFF"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea
                                    v-model="promotionData.description"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="Promotion details..."
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Promo Code</label>
                                <input
                                    v-model="promotionData.code"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="e.g., SUMMER2025"
                                />
                            </div>
                        </div>

                        <!-- Newsletter Type -->
                        <div v-if="form.type === 'newsletter'" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Heading</label>
                                <input
                                    v-model="newsletterData.heading"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="e.g., Subscribe to our Newsletter"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea
                                    v-model="newsletterData.description"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="Get the latest updates..."
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Placeholder</label>
                                <input
                                    v-model="newsletterData.placeholder"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="Enter your email"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                                <input
                                    v-model="newsletterData.button_text"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="Subscribe"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Form Action URL</label>
                                <input
                                    v-model="newsletterData.action"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="/newsletter/subscribe"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            v-model="form.status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <!-- Schedule (Optional) -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Schedule (Optional)</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input
                                    v-model="form.start_date"
                                    type="datetime-local"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                />
                                <p class="mt-1 text-xs text-gray-500">Leave empty for always visible</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <input
                                    v-model="form.end_date"
                                    type="datetime-local"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                />
                                <p class="mt-1 text-xs text-gray-500">Leave empty for no expiry</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 border-t pt-6">
                        <Link
                            :href="blockRoutes.index.url()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Updating...' : 'Update Block' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmDeleteModal
            v-model:show="showDeleteModal"
            :title="blockData.title"
            :message="`Are you sure you want to delete '${blockData.title}'? This action cannot be undone.`"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import TipTapEditor from '@/components/Admin/CMS/TipTapEditor.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import { debounce } from 'lodash';
import axios from 'axios';
import * as blockRoutes from '@/routes/admin/content/blocks';

const props = defineProps({
    block: {
        type: Object,
        required: true,
    },
});

// Handle Laravel Resource wrapping - block comes as { data: {...} }
const blockData = props.block.data || props.block;

const form = useForm({
    title: blockData.title || '',
    identifier: blockData.identifier || '',
    type: blockData.type || '',
    content: blockData.content || '',
    status: blockData.status || 'active',
    start_date: blockData.start_date ? formatDatetimeLocal(blockData.start_date) : '',
    end_date: blockData.end_date ? formatDatetimeLocal(blockData.end_date) : '',
});

const identifierStatus = ref('');

const bannerData = reactive({
    title: '',
    description: '',
    image: '',
    alt: '',
    cta_text: '',
    cta_url: '',
});

const promotionData = reactive({
    heading: '',
    discount: '',
    description: '',
    code: '',
});

const newsletterData = reactive({
    heading: '',
    description: '',
    placeholder: 'Enter your email',
    button_text: 'Subscribe',
    action: '/newsletter/subscribe',
});

// Initialize structured data from existing content
onMounted(() => {
    if (blockData.content_array) {
        if (form.type === 'banner') {
            Object.assign(bannerData, blockData.content_array);
        } else if (form.type === 'promotion') {
            Object.assign(promotionData, blockData.content_array);
        } else if (form.type === 'newsletter') {
            Object.assign(newsletterData, blockData.content_array);
        }
    }
});

const checkIdentifier = debounce(() => {
    if (!form.identifier || form.identifier === blockData.identifier) {
        identifierStatus.value = '';
        return;
    }
    
    axios.post(blockRoutes.checkIdentifier.url(), {
        identifier: form.identifier,
        exclude_id: blockData.id,
    }).then(response => {
        identifierStatus.value = response.data.available ? 'available' : 'taken';
    });
}, 500);

// Watch for type changes and update content accordingly
watch(() => form.type, (newType) => {
    if (newType === 'banner') {
        form.content = JSON.stringify(bannerData);
    } else if (newType === 'promotion') {
        form.content = JSON.stringify(promotionData);
    } else if (newType === 'newsletter') {
        form.content = JSON.stringify(newsletterData);
    }
});

// Watch for structured data changes
watch(bannerData, () => {
    if (form.type === 'banner') {
        form.content = JSON.stringify(bannerData);
    }
}, { deep: true });

watch(promotionData, () => {
    if (form.type === 'promotion') {
        form.content = JSON.stringify(promotionData);
    }
}, { deep: true });

watch(newsletterData, () => {
    if (form.type === 'newsletter') {
        form.content = JSON.stringify(newsletterData);
    }
}, { deep: true });

const showDeleteModal = ref(false);

const submit = () => {
    form.put(blockRoutes.update.url({ block: blockData.id }));
};

const deleteBlock = () => {
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    router.delete(blockRoutes.destroy.url({ block: blockData.id }), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

function formatDate(date) {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString();
}

function formatDatetimeLocal(datetime) {
    if (!datetime) return '';
    const date = new Date(datetime);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}
</script>

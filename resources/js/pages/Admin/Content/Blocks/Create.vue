<template>
    <AdminLayout>
        <Head title="Create Block" />

        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Create Block</h1>
                        <p class="mt-1 text-sm text-gray-600">Create a new reusable content block</p>
                    </div>
                    <Link
                        :href="blockRoutes.index.url()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </Link>
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
                            @input="generateIdentifier"
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
                            <button
                                type="button"
                                @click="regenerateIdentifier"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                Regenerate
                            </button>
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">Target Device</label>
                                <select
                                    v-model="bannerData.device"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                >
                                    <option value="desktop">Desktop / Web</option>
                                    <option value="mobile">Mobile App</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Mobile banners use identifier prefix <code class="text-blue-600">mobile-</code>.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Label (Badge)</label>
                                    <input
                                        v-model="bannerData.label"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                        placeholder="PROMO / NEW"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                                    <input
                                        v-model="bannerData.subtitle"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                        placeholder="Up to 50% Off Electronics"
                                    />
                                </div>
                            </div>
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
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Overlay Color (Hex with alpha)</label>
                                <input
                                    v-model="bannerData.overlay_color"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="#00000099"
                                />
                                <p class="mt-1 text-xs text-gray-500">Example: <code class="text-blue-600">#00000099</code> = black @ 60% opacity.</p>
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
                            {{ form.processing ? 'Creating...' : 'Create Block' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import TipTapEditor from '@/components/Admin/CMS/TipTapEditor.vue';
import { debounce } from 'lodash';
import axios from 'axios';
import * as blockRoutes from '@/routes/admin/content/blocks';

const form = useForm({
    title: '',
    identifier: '',
    type: '',
    content: '',
    status: 'active',
    start_date: '',
    end_date: '',
});

const identifierStatus = ref('');

const bannerData = reactive({
    device: 'desktop',
    label: '',
    subtitle: '',
    overlay_color: '',
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

const generateIdentifier = debounce(() => {
    if (!form.title) return;
    
    const slug = form.title
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    
    form.identifier = bannerData.device === 'mobile' && !slug.startsWith('mobile-')
        ? `mobile-${slug}`
        : slug;
}, 300);

const regenerateIdentifier = () => {
    if (form.title) {
        generateIdentifier();
        checkIdentifier();
    }
};

watch(() => bannerData.device, (device) => {
    if (form.type !== 'banner') return;
    if (!form.identifier) return;

    if (device === 'mobile' && !form.identifier.startsWith('mobile-')) {
        form.identifier = `mobile-${form.identifier}`;
        checkIdentifier();
    }

    if (device === 'desktop' && form.identifier.startsWith('mobile-')) {
        form.identifier = form.identifier.replace(/^mobile-/, '');
        checkIdentifier();
    }
});

const checkIdentifier = debounce(() => {
    if (!form.identifier) return;
    
    axios.post(blockRoutes.checkIdentifier.url(), {
        identifier: form.identifier,
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

const submit = () => {
    form.post(blockRoutes.store.url());
};
</script>

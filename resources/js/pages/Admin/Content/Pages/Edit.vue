<template>
    <Head :title="`Edit ${page.title}`" />

    <AdminLayout :title="`Edit ${page.title}`">
        <div class="p-6 space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Page</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update "{{ page.title }}" content page</p>
                </div>
                <div class="flex gap-2">
                    <a
                        :href="page.url"
                        target="_blank"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Preview
                    </a>
                    <Link
                        :href="pageRoutes.index().url"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Pages
                    </Link>
                </div>
            </div>

            <!-- Info Banner -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-blue-900 dark:text-blue-200">Page Information</h4>
                        <div class="mt-1 text-xs text-blue-700 dark:text-blue-300 space-y-1">
                            <p>Created by <strong>{{ page.creator?.name }}</strong> on {{ formatDate(page.created_at) }}</p>
                            <p v-if="page.updater">Last updated by <strong>{{ page.updater.name }}</strong> on {{ formatDate(page.updated_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submitForm" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div class="p-6 space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="title"
                            v-model="form.title"
                            @input="generateSlug"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            :class="{ 'border-red-500': form.errors.title }"
                            placeholder="Enter page title"
                        />
                        <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                    </div>

                    <!-- URL Key -->
                    <div>
                        <label for="url_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            URL Key <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-2">
                                                        <div class="flex-1">
                                <input
                                    v-model="form.url_key"
                                    type="text"
                                    placeholder="page-url-key"
                                    class="input"
                                    @input="checkSlugAvailability"
                                    :class="{ 'border-red-500': form.errors.url_key || (!slugAvailable && form.url_key) }"
                                />
                                <p v-if="form.errors.url_key" class="mt-1 text-sm text-red-600">{{ form.errors.url_key }}</p>
                                <p v-else-if="!slugAvailable && form.url_key" class="mt-1 text-sm text-red-600">This URL key is already taken</p>
                                <p v-else-if="slugAvailable && form.url_key && slugChecked" class="mt-1 text-sm text-green-600">✓ URL key is available</p>
                            </div>
                            <button
                                type="button"
                                @click="generateSlug"
                                class="px-4 py-2 h-10 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 whitespace-nowrap"
                            >
                                Regenerate
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Lowercase letters, numbers, and hyphens only. Will be: {{ previewUrl }}
                        </p>
                    </div>

                    <!-- Content Editor -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Content <span class="text-red-500">*</span>
                        </label>
                        <TipTapEditor
                            v-model="form.content"
                            placeholder="Write your page content here..."
                            :show-character-count="true"
                        />
                        <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
                    </div>

                    <!-- SEO Meta Fields -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">SEO Settings</h3>
                        
                        <!-- Meta Title -->
                        <div class="mb-4">
                            <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Meta Title
                            </label>
                            <input
                                id="meta_title"
                                v-model="form.meta_title"
                                type="text"
                                maxlength="255"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="{ 'border-red-500': form.errors.meta_title }"
                                placeholder="SEO title for search engines"
                            />
                            <p v-if="form.errors.meta_title" class="mt-1 text-sm text-red-600">{{ form.errors.meta_title }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ form.meta_title?.length || 0 }}/255 characters. Leave empty to use page title.
                            </p>
                        </div>

                        <!-- Meta Description -->
                        <div class="mb-4">
                            <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Meta Description
                            </label>
                            <textarea
                                id="meta_description"
                                v-model="form.meta_description"
                                rows="3"
                                maxlength="500"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="{ 'border-red-500': form.errors.meta_description }"
                                placeholder="Brief description for search results"
                            ></textarea>
                            <p v-if="form.errors.meta_description" class="mt-1 text-sm text-red-600">{{ form.errors.meta_description }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ form.meta_description?.length || 0 }}/500 characters. Recommended: 150-160 characters.
                            </p>
                        </div>

                        <!-- Meta Keywords -->
                        <div>
                            <label for="meta_keywords" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Meta Keywords
                            </label>
                            <input
                                id="meta_keywords"
                                v-model="form.meta_keywords"
                                type="text"
                                maxlength="500"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="{ 'border-red-500': form.errors.meta_keywords }"
                                placeholder="keyword1, keyword2, keyword3"
                            />
                            <p v-if="form.errors.meta_keywords" class="mt-1 text-sm text-red-600">{{ form.errors.meta_keywords }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Comma-separated keywords. Not widely used by search engines anymore.
                            </p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="status"
                            v-model="form.status"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            :class="{ 'border-red-500': form.errors.status }"
                        >
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="disabled">Disabled</option>
                        </select>
                        <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Draft pages are not visible on storefront. Published pages are live.
                        </p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex items-center justify-between rounded-b-lg">
                    <Link
                        :href="pageRoutes.index().url"
                        class="text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                    >
                        Cancel
                    </Link>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="deletePage"
                            class="px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors dark:bg-gray-600 dark:border-red-500 dark:hover:bg-red-600 dark:hover:text-white"
                            :disabled="form.processing"
                        >
                            Delete
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="form.processing || !slugAvailable"
                        >
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Update Page</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import TipTapEditor from '@/components/Admin/CMS/TipTapEditor.vue';
import type { Page } from '@/types/cms';
import { useDebounceFn } from '@vueuse/core';
import * as pageRoutes from '@/routes/admin/content/pages';
import axios from 'axios';

interface Props {
    page: Page;
}

const props = defineProps<Props>();

const form = useForm({
    title: props.page.title,
    url_key: props.page.url_key,
    content: props.page.content,
    meta_title: props.page.meta_title || '',
    meta_description: props.page.meta_description || '',
    meta_keywords: props.page.meta_keywords || '',
    status: props.page.status,
});

const slugAvailable = ref(true);
const slugChecked = ref(false);
const originalSlug = props.page.url_key;

const previewUrl = computed(() => {
    return form.url_key ? `${window.location.origin}/${form.url_key}` : 'Enter URL key to preview';
});

const generateSlug = () => {
    if (!form.title) return;
    
    // Convert to lowercase and replace spaces with hyphens
    let slug = form.title
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '') // Remove special characters
        .replace(/\s+/g, '-') // Replace spaces with hyphens
        .replace(/-+/g, '-') // Replace multiple hyphens with single hyphen
        .replace(/^-+|-+$/g, ''); // Remove leading and trailing hyphens
    
    form.url_key = slug;
    checkSlugAvailability();
};

const checkSlugAvailability = useDebounceFn(async () => {
    if (!form.url_key) {
        slugChecked.value = false;
        slugAvailable.value = true;
        return;
    }

    // If slug hasn't changed, it's available
    if (form.url_key === originalSlug) {
        slugAvailable.value = true;
        slugChecked.value = true;
        return;
    }

    try {
        const response = await axios.post(pageRoutes.checkSlug().url, {
            slug: form.url_key,
            exclude_id: props.page.id,
        });
        slugAvailable.value = response.data.available;
        slugChecked.value = true;
    } catch (error) {
        console.error('Error checking slug:', error);
        slugAvailable.value = true;
        slugChecked.value = false;
    }
}, 500);

const submitForm = () => {
    if (!slugAvailable.value) return;
    
    form.put(pageRoutes.update({ page: props.page.id }).url, {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};

const deletePage = () => {
    if (!confirm(`Are you sure you want to delete "${props.page.title}"? This action cannot be undone.`)) {
        return;
    }

    router.delete(pageRoutes.destroy({ page: props.page.id }).url);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

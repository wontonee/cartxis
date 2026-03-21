<template>
    <AdminLayout title="Create Blog Post">
        <Head title="Create Blog Post" />

        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Blog Post</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Write a new article for your blog</p>
                </div>
                <Link
                    :href="blogRoutes.index().url"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Posts
                </Link>
            </div>

            <form @submit.prevent="submitForm">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 space-y-6">
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
                                    placeholder="Enter post title"
                                />
                                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                            </div>

                            <!-- Slug -->
                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    URL Slug <span class="text-red-500">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <div class="flex-1">
                                        <input
                                            id="slug"
                                            v-model="form.slug"
                                            @input="checkSlugAvailability"
                                            type="text"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white font-mono"
                                            :class="{ 'border-red-500': form.errors.slug || !slugAvailable }"
                                            placeholder="post-url-slug"
                                        />
                                        <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                                        <p v-else-if="!slugAvailable && form.slug" class="mt-1 text-sm text-red-600">This slug is already taken</p>
                                        <p v-else-if="slugAvailable && form.slug && slugChecked" class="mt-1 text-sm text-green-600">✓ Slug is available</p>
                                    </div>
                                    <button type="button" @click="generateSlug" class="px-4 py-2 h-10 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                                        Generate
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Preview: {{ previewUrl }}</p>
                            </div>

                            <!-- Excerpt -->
                            <div>
                                <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Excerpt</label>
                                <textarea
                                    id="excerpt"
                                    v-model="form.excerpt"
                                    rows="2"
                                    maxlength="500"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Brief summary shown in post listings (max 500 chars)"
                                ></textarea>
                                <p class="mt-1 text-xs text-gray-500">{{ form.excerpt?.length || 0 }}/500 characters</p>
                            </div>

                            <!-- Content -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Content <span class="text-red-500">*</span>
                                </label>
                                <TipTapEditor
                                    v-model="form.content"
                                    placeholder="Write your blog post content here..."
                                    :show-character-count="true"
                                />
                                <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
                            </div>
                        </div>

                        <!-- SEO -->
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">SEO Settings</h3>
                            <div>
                                <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Title</label>
                                <input id="meta_title" v-model="form.meta_title" type="text" maxlength="255" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="SEO title for search engines" />
                                <p class="mt-1 text-xs text-gray-500">{{ form.meta_title?.length || 0 }}/255 characters</p>
                            </div>
                            <div>
                                <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Description</label>
                                <textarea id="meta_description" v-model="form.meta_description" rows="2" maxlength="500" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Brief description for search results"></textarea>
                                <p class="mt-1 text-xs text-gray-500">{{ form.meta_description?.length || 0 }}/500 characters</p>
                            </div>
                            <div>
                                <label for="meta_keywords" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Keywords</label>
                                <input id="meta_keywords" v-model="form.meta_keywords" type="text" maxlength="255" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="keyword1, keyword2, keyword3" />
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Publish Settings -->
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 space-y-4">
                            <h3 class="text-base font-medium text-gray-900 dark:text-white">Publish Settings</h3>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status <span class="text-red-500">*</span></label>
                                <select id="status" v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="scheduled">Scheduled</option>
                                </select>
                            </div>
                            <div v-if="form.status === 'scheduled'">
                                <label for="published_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Publish Date</label>
                                <input id="published_at" v-model="form.published_at" type="datetime-local" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                            </div>
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex flex-col gap-2">
                                <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" :disabled="form.processing || !slugAvailable">
                                    <span v-if="form.processing">Saving...</span>
                                    <span v-else>{{ form.status === 'published' ? 'Publish Post' : 'Save Post' }}</span>
                                </button>
                                <button type="button" @click="saveDraft" class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600" :disabled="form.processing">
                                    Save as Draft
                                </button>
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <h3 class="text-base font-medium text-gray-900 dark:text-white mb-4">Category</h3>
                            <select v-model="form.blog_category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option :value="null">No Category</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>

                        <!-- Featured Image -->
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <h3 class="text-base font-medium text-gray-900 dark:text-white mb-4">Featured Image</h3>
                            <div v-if="form.featured_image" class="mb-3">
                                <img :src="form.featured_image" alt="Featured image" class="w-full h-36 object-cover rounded-lg" />
                            </div>
                            <input
                                v-model="form.featured_image"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm"
                                placeholder="Enter image URL"
                            />
                            <p class="mt-1 text-xs text-gray-500">Paste a URL or upload via Media Library</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import TipTapEditor from '@/components/Admin/CMS/TipTapEditor.vue';
import { useDebounceFn } from '@vueuse/core';
import * as blogRoutes from '@/routes/admin/blog';
import axios from 'axios';

interface Category { id: number; name: string; slug: string }

const props = defineProps<{ categories: Category[] }>();

const form = useForm({
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    featured_image: '',
    status: 'draft' as 'draft' | 'published' | 'scheduled',
    published_at: '',
    blog_category_id: null as number | null,
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
});

const slugAvailable = ref(true);
const slugChecked = ref(false);

const previewUrl = computed(() =>
    form.slug ? `${window.location.origin}/blog/${form.slug}` : 'Enter slug to preview'
);

const generateSlug = () => {
    if (!form.title) return;
    form.slug = form.title
        .toLowerCase().trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-+|-+$/g, '');
    checkSlugAvailability();
};

const checkSlugAvailability = useDebounceFn(async () => {
    if (!form.slug) { slugChecked.value = false; slugAvailable.value = true; return; }
    try {
        const response = await axios.post(blogRoutes.checkSlug().url, { slug: form.slug });
        slugAvailable.value = response.data.available;
        slugChecked.value = true;
    } catch {
        slugAvailable.value = true;
        slugChecked.value = false;
    }
}, 500);

const submitForm = () => {
    if (!slugAvailable.value) return;
    form.post(blogRoutes.store().url);
};

const saveDraft = () => {
    form.status = 'draft';
    submitForm();
};
</script>

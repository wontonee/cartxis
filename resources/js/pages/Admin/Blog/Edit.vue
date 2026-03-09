<template>
    <AdminLayout title="Edit Blog Post">
        <Head :title="`Edit: ${post.title}`" />

        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Blog Post</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ post.title }}</p>
                </div>
                <div class="flex gap-2">
                    <a :href="`/blog/${post.slug}`" target="_blank" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600">
                        <ExternalLink class="w-4 h-4" />
                        View Post
                    </a>
                    <Link :href="blogRoutes.index().url" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Posts
                    </Link>
                </div>
            </div>

            <!-- Flash messages -->
            <div v-if="$page.props.flash?.success" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 text-green-800 dark:text-green-300 text-sm">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 text-red-800 dark:text-red-300 text-sm">
                {{ $page.props.flash.error }}
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
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    :class="{ 'border-red-500': form.errors.title }"
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
                                        />
                                        <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                                        <p v-else-if="!slugAvailable && form.slug" class="mt-1 text-sm text-red-600">This slug is already taken</p>
                                        <p v-else-if="slugAvailable && slugChecked" class="mt-1 text-sm text-green-600">✓ Slug is available</p>
                                    </div>
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
                                <input id="meta_title" v-model="form.meta_title" type="text" maxlength="255" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                            </div>
                            <div>
                                <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Description</label>
                                <textarea id="meta_description" v-model="form.meta_description" rows="2" maxlength="500" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                            </div>
                            <div>
                                <label for="meta_keywords" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Keywords</label>
                                <input id="meta_keywords" v-model="form.meta_keywords" type="text" maxlength="255" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Publish Settings -->
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 space-y-4">
                            <h3 class="text-base font-medium text-gray-900 dark:text-white">Publish Settings</h3>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
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
                                    <span v-else>Save Changes</span>
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
                        </div>

                        <!-- Post Info -->
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <h3 class="text-base font-medium text-gray-900 dark:text-white mb-4">Post Info</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Views</dt>
                                    <dd class="text-gray-900 dark:text-white font-medium">{{ post.view_count }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Created</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ formatDate(post.created_at) }}</dd>
                                </div>
                                <div v-if="post.creator" class="flex justify-between">
                                    <dt class="text-gray-500">Author</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ post.creator.name }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Danger Zone -->
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 border border-red-200 dark:border-red-800/50">
                            <h3 class="text-base font-medium text-red-700 dark:text-red-400 mb-4">Danger Zone</h3>
                            <button
                                type="button"
                                @click="deletePost"
                                class="w-full px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors dark:bg-red-900/20 dark:text-red-400 dark:border-red-800"
                            >
                                Delete Post
                            </button>
                        </div>
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
import { useDebounceFn } from '@vueuse/core';
import * as blogRoutes from '@/routes/admin/blog';
import axios from 'axios';
import { ExternalLink } from 'lucide-vue-next';

interface Category { id: number; name: string; slug: string }
interface Post {
    id: number; title: string; slug: string; excerpt: string; content: string;
    featured_image: string; status: string; published_at: string | null;
    blog_category_id: number | null; meta_title: string; meta_description: string;
    meta_keywords: string; view_count: number; created_at: string;
    creator: { name: string } | null;
}

const props = defineProps<{ post: Post; categories: Category[] }>();

const form = useForm({
    title: props.post.title,
    slug: props.post.slug,
    excerpt: props.post.excerpt || '',
    content: props.post.content || '',
    featured_image: props.post.featured_image || '',
    status: props.post.status as 'draft' | 'published' | 'scheduled',
    published_at: props.post.published_at
        ? new Date(props.post.published_at).toISOString().slice(0, 16)
        : '',
    blog_category_id: props.post.blog_category_id,
    meta_title: props.post.meta_title || '',
    meta_description: props.post.meta_description || '',
    meta_keywords: props.post.meta_keywords || '',
});

const slugAvailable = ref(true);
const slugChecked = ref(false);

const previewUrl = computed(() =>
    form.slug ? `${window.location.origin}/blog/${form.slug}` : ''
);

const checkSlugAvailability = useDebounceFn(async () => {
    if (!form.slug || form.slug === props.post.slug) {
        slugAvailable.value = true;
        slugChecked.value = false;
        return;
    }
    try {
        const response = await axios.post(blogRoutes.checkSlug().url, {
            slug: form.slug,
            exclude_id: props.post.id,
        });
        slugAvailable.value = response.data.available;
        slugChecked.value = true;
    } catch {
        slugAvailable.value = true;
        slugChecked.value = false;
    }
}, 500);

const submitForm = () => {
    if (!slugAvailable.value) return;
    form.put(blogRoutes.update({ post: props.post.id }).url);
};

const deletePost = () => {
    if (!confirm(`Are you sure you want to delete "${props.post.title}"?`)) return;
    router.delete(blogRoutes.destroy({ post: props.post.id }).url);
};

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
</script>

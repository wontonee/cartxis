<template>
    <AdminLayout :title="`Edit: ${category.name}`">
        <Head :title="`Edit Category: ${category.name}`" />

        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Category</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ category.posts_count }} post{{ category.posts_count !== 1 ? 's' : '' }} in this category
                    </p>
                </div>
                <Link
                    :href="categoryRoutes.index().url"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Categories
                </Link>
            </div>

            <!-- Flash messages -->
            <div v-if="$page.props.flash?.success" class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                {{ $page.props.flash.error }}
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 space-y-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    :class="{ 'border-red-500': form.errors.name }"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <!-- Slug -->
                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    URL Slug <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="slug"
                                    v-model="form.slug"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white font-mono"
                                    :class="{ 'border-red-500': form.errors.slug }"
                                />
                                <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                                <p class="mt-1 text-xs text-yellow-600 dark:text-yellow-400" v-if="form.slug !== category.slug">
                                    ⚠ Changing the slug will break existing URLs for this category.
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                ></textarea>
                            </div>
                        </div>

                        <!-- SEO -->
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">SEO Settings</h3>
                            <div>
                                <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Title</label>
                                <input id="meta_title" v-model="form.meta_title" type="text" maxlength="255" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                                <p class="mt-1 text-xs text-gray-500">{{ form.meta_title?.length || 0 }}/255</p>
                            </div>
                            <div>
                                <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Description</label>
                                <textarea id="meta_description" v-model="form.meta_description" rows="2" maxlength="500" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                <p class="mt-1 text-xs text-gray-500">{{ form.meta_description?.length || 0 }}/500</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 space-y-4">
                            <h3 class="text-base font-medium text-gray-900 dark:text-white">Settings</h3>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                <select id="status" v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex flex-col gap-2">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="form.processing">Saving...</span>
                                    <span v-else>Save Changes</span>
                                </button>
                                <button
                                    v-if="category.posts_count === 0"
                                    type="button"
                                    @click="deleteCategory"
                                    class="w-full px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors dark:bg-transparent dark:border-red-700 dark:hover:bg-red-900/20"
                                >
                                    Delete Category
                                </button>
                                <p v-else class="text-xs text-center text-gray-400 dark:text-gray-500">
                                    Cannot delete — category has {{ category.posts_count }} post{{ category.posts_count !== 1 ? 's' : '' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import * as categoryRoutes from '@/routes/admin/blog/categories';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    status: 'active' | 'inactive';
    meta_title: string | null;
    meta_description: string | null;
    posts_count: number;
}

const props = defineProps<{ category: Category }>();

const form = useForm({
    name: props.category.name,
    slug: props.category.slug,
    description: props.category.description ?? '',
    status: props.category.status,
    meta_title: props.category.meta_title ?? '',
    meta_description: props.category.meta_description ?? '',
});

function submit() {
    form.put(categoryRoutes.update({ category: props.category.slug }).url);
}

function deleteCategory() {
    if (!confirm(`Delete "${props.category.name}"? This cannot be undone.`)) return;
    router.delete(categoryRoutes.destroy({ category: props.category.slug }).url);
}
</script>

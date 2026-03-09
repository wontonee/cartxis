<template>
    <AdminLayout title="Blog Categories">
        <Head title="Blog Categories" />

        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Blog Categories</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Organise your blog posts into categories</p>
                </div>
                <Link
                    :href="categoryRoutes.create().url"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <PlusCircle class="w-4 h-4 mr-2" />
                    New Category
                </Link>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.total }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <Tags class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-green-200 dark:hover:border-green-800 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.active }}</p>
                        </div>
                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <CheckCircle class="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-gray-200 dark:hover:border-gray-600 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Inactive</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.inactive }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <XCircle class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-purple-200 dark:hover:border-purple-800 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">With Posts</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.with_posts }}</p>
                        </div>
                        <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                            <FileText class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-6 lg:col-span-5">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="filters.search"
                                @input="debouncedSearch"
                                type="text"
                                placeholder="Search by name or slug..."
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
                            />
                        </div>
                    </div>
                    <div class="md:col-span-3 lg:col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
                        <div class="relative">
                            <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <select
                                v-model="filters.status"
                                @change="applyFilters"
                                class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
                            >
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <ArrowUpDown class="w-3 h-3 text-gray-400" />
                            </div>
                        </div>
                    </div>
                    <div class="md:col-span-3 lg:col-span-2 flex items-end" v-if="filters.search || filters.status">
                        <button
                            @click="clearFilters"
                            class="w-full py-2.5 px-4 text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Slug</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Posts</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-if="categories.data.length === 0">
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full">
                                        <Tags class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium">No categories found.</p>
                                    <Link :href="categoryRoutes.create().url" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Create your first category →</Link>
                                </div>
                            </td>
                        </tr>
                        <tr
                            v-for="category in categories.data"
                            :key="category.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center flex-shrink-0">
                                        <Tag class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ category.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <code class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded">{{ category.slug }}</code>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full dark:bg-blue-900/30 dark:text-blue-300">
                                    {{ category.posts_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                    :class="category.status === 'active'
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                                        : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :class="category.status === 'active' ? 'bg-green-500' : 'bg-gray-400'"></span>
                                    {{ category.status === 'active' ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link
                                        :href="categoryRoutes.edit({ category: category.slug }).url"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors dark:text-blue-400 dark:bg-blue-900/20 dark:hover:bg-blue-900/40"
                                        title="Edit"
                                    >
                                        <Pencil class="w-3.5 h-3.5" />
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteCategory(category)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
                                        :class="category.posts_count > 0
                                            ? 'text-gray-400 bg-gray-50 dark:bg-gray-700/50 cursor-not-allowed opacity-50'
                                            : 'text-red-700 bg-red-50 hover:bg-red-100 dark:text-red-400 dark:bg-red-900/20 dark:hover:bg-red-900/40'"
                                        title="Delete"
                                        :disabled="category.posts_count > 0"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="categories.last_page > 1" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Showing {{ categories.from }}–{{ categories.to }} of {{ categories.total }} categories
                    </p>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in categories.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            v-html="link.label"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors"
                            :class="link.active
                                ? 'bg-blue-600 text-white border-blue-600'
                                : 'text-gray-600 border-gray-200 hover:bg-gray-50 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700'"
                            :aria-disabled="!link.url"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusCircle, Pencil, Trash2, Tag, Tags, Search, Filter, ArrowUpDown, CheckCircle, XCircle, FileText } from 'lucide-vue-next';
import { useDebounceFn } from '@vueuse/core';
import AdminLayout from '@/layouts/AdminLayout.vue';
import * as categoryRoutes from '@/routes/admin/blog/categories';

interface Category {
    id: number;
    name: string;
    slug: string;
    status: 'active' | 'inactive';
    posts_count: number;
}

interface Paginator<T> {
    data: T[];
    from: number;
    to: number;
    total: number;
    last_page: number;
    links: { label: string; url: string | null; active: boolean }[];
}

interface Statistics {
    total: number;
    active: number;
    inactive: number;
    with_posts: number;
}

const props = defineProps<{
    categories: Paginator<Category>;
    filters: { search?: string; status?: string };
    statistics: Statistics;
}>();

const filters = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
});

function applyFilters() {
    router.get(categoryRoutes.index().url, filters, { preserveState: true, replace: true });
}

const debouncedSearch = useDebounceFn(applyFilters, 350);

function clearFilters() {
    filters.search = '';
    filters.status = '';
    applyFilters();
}

function deleteCategory(category: Category) {
    if (category.posts_count > 0) return;
    if (!confirm(`Delete category "${category.name}"? This cannot be undone.`)) return;
    router.delete(categoryRoutes.destroy({ category: category.slug }).url);
}
</script>

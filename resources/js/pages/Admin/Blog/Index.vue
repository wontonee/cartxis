<template>
    <AdminLayout title="Blog Posts">
        <Head title="Blog Posts" />

        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Blog Posts</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage blog content for your store</p>
                </div>
                <Link
                    :href="blogRoutes.create().url"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <PlusCircle class="w-4 h-4 mr-2" />
                    Create Post
                </Link>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Posts</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.total }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <Newspaper class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-green-200 dark:hover:border-green-800 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Published</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.published }}</p>
                        </div>
                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <CheckCircle class="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-yellow-200 dark:hover:border-yellow-800 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Draft</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.draft }}</p>
                        </div>
                        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                            <PenTool class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-purple-200 dark:hover:border-purple-800 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Scheduled</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.scheduled }}</p>
                        </div>
                        <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                            <Clock class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-5 lg:col-span-4">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="form.search"
                                type="text"
                                placeholder="Search by title or slug..."
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
                                @input="debouncedSearch"
                            />
                        </div>
                    </div>
                    <div class="md:col-span-3 lg:col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
                        <div class="relative">
                            <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <select
                                v-model="form.status"
                                class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
                                @change="applyFilters"
                            >
                                <option value="">All Status</option>
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                                <option value="scheduled">Scheduled</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <ArrowUpDown class="w-3 h-3 text-gray-400" />
                            </div>
                        </div>
                    </div>
                    <div class="md:col-span-4 lg:col-span-3">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Category</label>
                        <div class="relative">
                            <Folder class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <select
                                v-model="form.category_id"
                                class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
                                @change="applyFilters"
                            >
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <ArrowUpDown class="w-3 h-3 text-gray-400" />
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="form.search || form.status || form.category_id" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button
                        @click="clearFilters"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors flex items-center gap-2"
                    >
                        <X class="w-4 h-4" />
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <!-- Bulk Actions -->
                <div v-if="selectedPosts.length > 0" class="bg-blue-50 dark:bg-blue-900/20 p-4 border-b border-blue-100 dark:border-blue-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <CheckCircle class="w-4 h-4 mr-2 text-blue-600 dark:text-blue-400" />
                            <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">{{ selectedPosts.length }} post(s) selected</span>
                        </div>
                        <div class="flex gap-2">
                            <button @click="bulkAction('publish')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300">
                                <Check class="w-4 h-4 mr-1.5" />Publish
                            </button>
                            <button @click="bulkAction('draft')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded text-orange-700 bg-orange-100 hover:bg-orange-200 dark:bg-orange-900/40 dark:text-orange-300">
                                <PenTool class="w-4 h-4 mr-1.5" />Draft
                            </button>
                            <button @click="bulkAction('delete')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300">
                                <Trash2 class="w-4 h-4 mr-1.5" />Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left w-12">
                                    <input type="checkbox" :checked="allSelected" @change="toggleAll" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4" />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Title</th>
                                <th class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="hidden lg:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Published</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="post in posts.data"
                                :key="post.id"
                                :class="['group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors', { 'bg-blue-50 dark:bg-blue-900/10': selectedPosts.includes(post.id) }]"
                            >
                                <td class="px-6 py-4">
                                    <input type="checkbox" :value="post.id" v-model="selectedPosts" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer" />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ post.title }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">/blog/{{ post.slug }}</div>
                                </td>
                                <td class="hidden md:table-cell px-6 py-4">
                                    <span v-if="post.category" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                        {{ post.category.name }}
                                    </span>
                                    <span v-else class="text-xs text-gray-400">—</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm" :class="statusClass(post.status)">
                                        {{ post.status.charAt(0).toUpperCase() + post.status.slice(1) }}
                                    </span>
                                </td>
                                <td class="hidden lg:table-cell px-6 py-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ post.published_at ? formatDate(post.published_at) : '—' }}
                                    </div>
                                    <div v-if="post.creator" class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                        <User class="w-3 h-3" />{{ post.creator.name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                        <a :href="`/blog/${post.slug}`" target="_blank" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Preview Post">
                                            <ExternalLink class="w-4 h-4" />
                                        </a>
                                        <Link :href="blogRoutes.edit({ post: post.slug }).url" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors" title="Edit Post">
                                            <Edit class="w-4 h-4" />
                                        </Link>
                                        <button @click="deletePost(post)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete Post">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="posts.data.length === 0" class="text-center py-12">
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                            <Newspaper class="w-8 h-8" />
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No posts found</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Get started by creating your first blog post.</p>
                        <Link :href="blogRoutes.create().url" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                            <PlusCircle class="w-4 h-4 mr-2" />Create Post
                        </Link>
                    </div>
                </div>

                <div v-if="posts.data.length > 0" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    <Pagination :data="posts" resource-name="posts" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import { debounce } from 'lodash';
import * as blogRoutes from '@/routes/admin/blog';
import {
    PlusCircle, Newspaper, CheckCircle, PenTool, Clock, Search, Filter, ArrowUpDown,
    X, ExternalLink, Edit, Trash2, Check, User, Folder
} from 'lucide-vue-next';

interface Category { id: number; name: string; slug: string }
interface Post {
    id: number; title: string; slug: string; status: string;
    published_at: string | null;
    category: Category | null;
    creator: { name: string } | null;
}
interface PaginatedPosts { data: Post[]; links: unknown; meta: unknown }

const props = defineProps<{
    posts: PaginatedPosts;
    statistics: { total: number; published: number; draft: number; scheduled: number };
    categories: Category[];
    filters: Record<string, string>;
}>();

const form = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    category_id: props.filters.category_id || '',
});

const selectedPosts = ref<number[]>([]);

const allSelected = computed(
    () => props.posts.data.length > 0 && selectedPosts.value.length === props.posts.data.length
);

const toggleAll = () => {
    if (allSelected.value) {
        selectedPosts.value = [];
    } else {
        selectedPosts.value = props.posts.data.map((p) => p.id);
    }
};

const applyFilters = () => {
    router.get(
        blogRoutes.index().url,
        {
            search: form.value.search || undefined,
            status: form.value.status || undefined,
            category_id: form.value.category_id || undefined,
        },
        { preserveState: true, preserveScroll: true }
    );
};

const clearFilters = () => {
    form.value.search = '';
    form.value.status = '';
    form.value.category_id = '';
    applyFilters();
};

const debouncedSearch = debounce(() => applyFilters(), 500);

const bulkAction = (action: string) => {
    if (selectedPosts.value.length === 0) return;
    if (!confirm(`Are you sure you want to ${action} the selected posts?`)) return;
    router.post(
        blogRoutes.bulkAction().url,
        { ids: selectedPosts.value, action },
        { onSuccess: () => { selectedPosts.value = []; } }
    );
};

const deletePost = (post: Post) => {
    if (!confirm(`Are you sure you want to delete "${post.title}"?`)) return;
    router.delete(blogRoutes.destroy({ post: post.slug }).url);
};

const statusClass = (status: string) => {
    const classes: Record<string, string> = {
        published: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800',
        draft: 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
        scheduled: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800',
    };
    return classes[status] || 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-900/20 dark:text-gray-300 dark:border-gray-800';
};

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
</script>

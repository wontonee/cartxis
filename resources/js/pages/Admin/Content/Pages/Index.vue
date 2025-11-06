<template>
    <AdminLayout>
        <Head title="Pages" />

        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Pages</h1>
                        <p class="mt-1 text-sm text-gray-600">Manage static content pages for your store</p>
                    </div>
                    <Link
                        :href="pageRoutes.create().url"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Page
                    </Link>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Total Pages</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ statistics.total }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Published</div>
                    <div class="mt-2 text-3xl font-semibold text-green-600">{{ statistics.published }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Draft</div>
                    <div class="mt-2 text-3xl font-semibold text-yellow-600">{{ statistics.draft }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Disabled</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-600">{{ statistics.disabled }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Search
                        </label>
                        <input
                            v-model="form.search"
                            type="text"
                            placeholder="Search by title or URL..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            @input="debouncedSearch"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Status
                        </label>
                        <select 
                            v-model="form.status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                            @change="applyFilters"
                        >
                            <option value="">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Sort By
                        </label>
                        <select 
                            v-model="form.sort_by" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                            @change="applyFilters"
                        >
                            <option value="created_at">Created Date</option>
                            <option value="updated_at">Updated Date</option>
                            <option value="title">Title</option>
                            <option value="status">Status</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Pages Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <!-- Bulk Actions -->
                <div
                    v-if="selectedPages.length > 0"
                    class="bg-blue-50 dark:bg-blue-900 p-4 border-b border-blue-200 dark:border-blue-700"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-blue-700 dark:text-blue-200">
                            {{ selectedPages.length }} page(s) selected
                        </span>
                        <div class="flex gap-2">
                            <button
                                @click="bulkAction('enable')"
                                class="btn-sm btn-success"
                            >
                                <Icon name="check" :size="14" class="mr-1" />
                                Enable
                            </button>
                            <button
                                @click="bulkAction('disable')"
                                class="btn-sm btn-secondary"
                            >
                                <Icon name="x" :size="14" class="mr-1" />
                                Disable
                            </button>
                            <button
                                @click="bulkAction('delete')"
                                class="btn-sm btn-danger"
                            >
                                <Icon name="trash-2" :size="14" class="mr-1" />
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        :checked="allSelected"
                                        @change="toggleAllPages"
                                        class="checkbox"
                                    />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Title
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    URL Key
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Created
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="page in pages.data"
                                :key="page.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <td class="px-6 py-4">
                                    <input
                                        type="checkbox"
                                        :value="page.id"
                                        v-model="selectedPages"
                                        class="checkbox"
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ page.title }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400 font-mono">
                                        {{ page.url_key }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="statusClass(page.status)">
                                        {{ page.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ formatDate(page.created_at) }}
                                    </div>
                                    <div v-if="page.creator" class="text-xs text-gray-500">
                                        by {{ page.creator.name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            :href="page.url"
                                            target="_blank"
                                            class="text-blue-600 hover:text-blue-900 cursor-pointer"
                                            title="Preview Page"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <Link
                                            :href="pageRoutes.edit({ page: page.id }).url"
                                            class="text-indigo-600 hover:text-indigo-900 cursor-pointer"
                                            title="Edit Page"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button
                                            @click="deletePage(page)"
                                            class="text-red-600 hover:text-red-900 cursor-pointer"
                                            title="Delete Page"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div
                    v-if="pages.data.length === 0"
                    class="text-center py-12"
                >
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No pages found</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Get started by creating your first page.</p>
                    <Link
                        :href="pageRoutes.create().url"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create Page
                    </Link>
                </div>

                <!-- Pagination -->
                <div
                    v-if="pages.data.length > 0"
                    class="px-4 py-3 border-t border-gray-200 dark:border-gray-700"
                >
                    <Pagination :data="pages" resource-name="pages" />
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
import type { Page, PaginatedPages, PageStatistics, PageFilters } from '@/types/cms';
import { debounce } from 'lodash';
import * as pageRoutes from '@/routes/admin/content/pages';

interface Props {
    pages: PaginatedPages;
    statistics: PageStatistics;
    filters: PageFilters;
}

const props = defineProps<Props>();

const form = ref<PageFilters>({
    search: props.filters.search || '',
    status: (props.filters.status || '') as any,
    sort_by: props.filters.sort_by || 'created_at',
    sort_order: props.filters.sort_order || 'desc',
});

const selectedPages = ref<number[]>([]);

const allSelected = computed(() => {
    return (
        props.pages.data.length > 0 &&
        selectedPages.value.length === props.pages.data.length
    );
});

const toggleAllPages = () => {
    if (allSelected.value) {
        selectedPages.value = [];
    } else {
        selectedPages.value = props.pages.data.map((page) => page.id);
    }
};

const applyFilters = () => {
    router.get(
        pageRoutes.index().url,
        {
            search: form.value.search || undefined,
            status: form.value.status || undefined,
            sort_by: form.value.sort_by,
            sort_order: form.value.sort_order,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const debouncedSearch = debounce(() => {
    applyFilters();
}, 500);

const bulkAction = (action: string) => {
    if (selectedPages.value.length === 0) return;

    const message =
        action === 'delete'
            ? 'Are you sure you want to delete the selected pages?'
            : `Are you sure you want to ${action} the selected pages?`;

    if (!confirm(message)) return;

    router.post(
        pageRoutes.bulkAction().url,
        {
            ids: selectedPages.value,
            action,
        },
        {
            onSuccess: () => {
                selectedPages.value = [];
            },
        }
    );
};

const deletePage = (page: Page) => {
    if (!confirm(`Are you sure you want to delete "${page.title}"?`)) return;

    router.delete(pageRoutes.destroy({ page: page.id }).url);
};

const statusClass = (status: string) => {
    const classes = {
        published: 'badge-success',
        draft: 'badge-warning',
        disabled: 'badge-secondary',
    };
    return classes[status as keyof typeof classes] || 'badge-secondary';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

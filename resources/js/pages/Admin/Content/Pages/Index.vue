<template>
    <AdminLayout title="Pages">
        <Head title="Pages" />

        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pages</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage static content pages for your store</p>
                </div>
                <Link
                    :href="pageRoutes.create().url"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <PlusCircle class="w-4 h-4 mr-2" />
                    Create Page
                </Link>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Pages</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.total }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg group-hover:bg-blue-100 dark:group-hover:bg-blue-900/40 transition-colors">
                            <FileText class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-green-200 dark:hover:border-green-800 transition-colors">
                     <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Published</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.published }}</p>
                        </div>
                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg group-hover:bg-green-100 dark:group-hover:bg-green-900/40 transition-colors">
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
                         <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg group-hover:bg-yellow-100 dark:group-hover:bg-yellow-900/40 transition-colors">
                            <PenTool class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-gray-200 dark:hover:border-gray-600 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Disabled</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.disabled }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg group-hover:bg-gray-100 dark:group-hover:bg-gray-600 transition-colors">
                             <XCircle class="w-6 h-6 text-gray-600 dark:text-gray-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-6 lg:col-span-4">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
                         <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="form.search"
                                type="text"
                                placeholder="Search by title or URL..."
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
                                @input="debouncedSearch"
                            />
                        </div>
                    </div>
                    
                    <!-- Status Filter -->
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
                                <option value="disabled">Disabled</option>
                            </select>
                             <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <ArrowUpDown class="w-3 h-3 text-gray-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Sort Filter -->
                    <div class="md:col-span-3 lg:col-span-2">
                         <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Sort By</label>
                         <div class="relative">
                            <ArrowUpDown class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <select 
                                v-model="form.sort_by" 
                                class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
                                @change="applyFilters"
                            >
                                <option value="created_at">Created Date</option>
                                <option value="updated_at">Updated Date</option>
                                <option value="title">Title</option>
                                <option value="status">Status</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <ArrowUpDown class="w-3 h-3 text-gray-400" />
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- Filter Actions -->
                <div v-if="form.search || form.status" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button
                    @click="clearFilters"
                    class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors flex items-center gap-2"
                    >
                    <X class="w-4 h-4" />
                    Clear Filters
                    </button>
                </div>
            </div>

            <!-- Pages Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <!-- Bulk Actions -->
                <div
                    v-if="selectedPages.length > 0"
                    class="bg-blue-50 dark:bg-blue-900/20 p-4 border-b border-blue-100 dark:border-blue-800"
                >
                    <div class="flex items-center justify-between">
                         <div class="flex items-center">
                            <CheckCircle class="w-4 h-4 mr-2 text-blue-600 dark:text-blue-400" />
                            <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">
                                {{ selectedPages.length }} page(s) selected
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="bulkAction('enable')"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:bg-green-900/40 dark:text-green-300 dark:hover:bg-green-900/60"
                            >
                                <Check class="w-4 h-4 mr-1.5" />
                                Enable
                            </button>
                            <button
                                @click="bulkAction('disable')"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-orange-700 bg-orange-100 hover:bg-orange-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:bg-orange-900/40 dark:text-orange-300 dark:hover:bg-orange-900/60"
                            >
                                <XCircle class="w-4 h-4 mr-1.5" />
                                Disable
                            </button>
                            <button
                                @click="bulkAction('delete')"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60"
                            >
                                <Trash2 class="w-4 h-4 mr-1.5" />
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left w-12">
                                    <input
                                        type="checkbox"
                                        :checked="allSelected"
                                        @change="toggleAllPages"
                                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 transition-all"
                                    />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Title
                                </th>
                                <th class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    URL Key
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="hidden lg:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Created
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template v-for="page in pages.data" :key="page.id">
                                <tr :class="['group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors', {'bg-blue-50 dark:bg-blue-900/10': selectedPages.includes(page.id)}]">
                                    <td class="px-6 py-4 relative">
                                        <div class="flex items-center">
                                            <button
                                                @click="toggleRow(page.id)"
                                                class="md:hidden absolute left-2 p-1 text-blue-600 hover:text-blue-800 focus:outline-none"
                                            >
                                                <MinusCircle v-if="expandedRows.includes(page.id)" class="w-5 h-5 fill-blue-100 dark:fill-blue-900/30" />
                                                <PlusCircle v-else class="w-5 h-5 fill-blue-50 dark:fill-blue-900/20" />
                                            </button>
                                            <input
                                                type="checkbox"
                                                :value="page.id"
                                                v-model="selectedPages"
                                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer transition-all ml-4 md:ml-0"
                                            />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ page.title }}
                                                </div>
                                                <div class="md:hidden text-xs text-gray-500 mt-1 font-mono">
                                                    /{{ page.url_key }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="hidden md:table-cell px-6 py-4">
                                        <div class="text-sm text-gray-600 dark:text-gray-400 font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded inline-block">
                                            /{{ page.url_key }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span 
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm"
                                            :class="statusClass(page.status)"
                                        >
                                            {{ page.status.charAt(0).toUpperCase() + page.status.slice(1) }}
                                        </span>
                                    </td>
                                    <td class="hidden lg:table-cell px-6 py-4">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ formatDate(page.created_at) }}
                                        </div>
                                        <div v-if="page.creator" class="text-xs text-gray-500 dark:text-gray-500 mt-0.5 flex items-center gap-1">
                                            <User class="w-3 h-3" />
                                            {{ page.creator.name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                            <a
                                                :href="page.url"
                                                target="_blank"
                                                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                                title="Preview Page"
                                            >
                                                <ExternalLink class="w-4 h-4" />
                                            </a>
                                            <Link
                                                :href="pageRoutes.edit({ page: page.id }).url"
                                                class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors"
                                                title="Edit Page"
                                            >
                                                <Edit class="w-4 h-4" />
                                            </Link>
                                            <button
                                                @click="deletePage(page)"
                                                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                title="Delete Page"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Mobile Expanded Row -->
                                <tr v-if="expandedRows.includes(page.id)" class="bg-gray-50/50 dark:bg-gray-900/50 md:hidden">
                                     <td colspan="6" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                                        <div class="grid grid-cols-1 gap-4 text-sm">
                                            <div>
                                                <span class="text-xs text-gray-500 font-medium uppercase tracking-wider block mb-1">Created At</span>
                                                <div class="text-sm text-gray-900 dark:text-white">{{ formatDate(page.created_at) }}</div>
                                                 <div v-if="page.creator" class="text-xs text-gray-500 mt-1">
                                                    by {{ page.creator.name }}
                                                </div>
                                            </div>
                                        </div>
                                     </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div
                    v-if="pages.data.length === 0"
                    class="text-center py-12"
                >
                     <div class="flex flex-col items-center justify-center">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                            <FileText class="w-8 h-8" />
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No pages found</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Get started by creating your first page.</p>
                        <Link
                            :href="pageRoutes.create().url"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <PlusCircle class="w-4 h-4 mr-2" />
                            Create Page
                        </Link>
                    </div>
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
import { 
    PlusCircle, 
    FileText, 
    CheckCircle, 
    PenTool, 
    XCircle, 
    Search, 
    Filter, 
    ArrowUpDown, 
    X,
    ExternalLink,
    Edit,
    Trash2,
    Check,
    MinusCircle,
    User
} from 'lucide-vue-next';

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
const expandedRows = ref<number[]>([]);

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

const toggleRow = (id: number) => {
    if (expandedRows.value.includes(id)) {
        expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
    } else {
        expandedRows.value.push(id);
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

const clearFilters = () => {
    form.value.search = '';
    form.value.status = '';
    applyFilters();
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
        published: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800',
        draft: 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
        disabled: 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-900/20 dark:text-gray-300 dark:border-gray-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-900/20 dark:text-gray-300 dark:border-gray-800';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

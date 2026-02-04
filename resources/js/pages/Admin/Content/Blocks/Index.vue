<template>
    <AdminLayout>
        <Head title="Blocks" />

        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Content Blocks</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage reusable content blocks for your store</p>
                    </div>
                    <Link
                        :href="blockRoutes.create.url()"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Block
                    </Link>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Blocks</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ statistics.total }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Active</div>
                    <div class="mt-2 text-3xl font-semibold text-green-600 dark:text-green-400">{{ statistics.active }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Inactive</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-600 dark:text-gray-400">{{ statistics.inactive }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Scheduled</div>
                    <div class="mt-2 text-3xl font-semibold text-blue-600 dark:text-blue-400">{{ statistics.scheduled }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                        <input
                            v-model="form.search"
                            type="text"
                            placeholder="Search by title or identifier..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            @input="debouncedSearch"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select 
                            v-model="form.status" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white" 
                            @change="applyFilters"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                        <select 
                            v-model="form.type" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white" 
                            @change="applyFilters"
                        >
                            <option value="">All Types</option>
                            <option value="text">Text</option>
                            <option value="html">HTML</option>
                            <option value="banner">Banner</option>
                            <option value="promotion">Promotion</option>
                            <option value="newsletter">Newsletter</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Blocks Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Bulk Actions -->
                <div
                    v-if="selectedBlocks.length > 0"
                    class="bg-blue-50 dark:bg-blue-900/20 p-4 border-b border-blue-200 dark:border-blue-800"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-blue-700 dark:text-blue-300">
                            {{ selectedBlocks.length }} block(s) selected
                        </span>
                        <div class="flex gap-2">
                            <button
                                @click="bulkAction('activate')"
                                class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700"
                            >
                                Activate
                            </button>
                            <button
                                @click="bulkAction('deactivate')"
                                class="px-3 py-1 text-sm bg-gray-600 text-white rounded hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600"
                            >
                                Deactivate
                            </button>
                            <button
                                @click="bulkAction('delete')"
                                class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        :checked="allSelected"
                                        @change="toggleAllBlocks"
                                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700"
                                    />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Title
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Identifier
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Schedule
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="block in blocks.data" :key="block.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4">
                                    <input
                                        type="checkbox"
                                        :value="block.id"
                                        v-model="selectedBlocks"
                                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700"
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ block.title }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <code class="text-sm text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-1 rounded">
                                        @block('{{ block.identifier }}')
                                    </code>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getTypeClass(block.type)">
                                        {{ block.type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getStatusClass(block.status)">
                                        {{ block.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <div v-if="block.start_date || block.end_date">
                                        <div v-if="block.start_date">Start: {{ formatDate(block.start_date) }}</div>
                                        <div v-if="block.end_date">End: {{ formatDate(block.end_date) }}</div>
                                    </div>
                                    <span v-else class="text-gray-400 dark:text-gray-500">Always visible</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="blockRoutes.edit.url({ block: block.id })"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 cursor-pointer"
                                            title="Edit Block"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button
                                            @click="deleteBlock(block.id, block.title)"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 cursor-pointer"
                                            title="Delete Block"
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
                <div v-if="blocks.data.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No blocks found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new block.</p>
                    <div class="mt-6">
                        <Link
                            :href="blockRoutes.create.url()"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                        >
                            Create Block
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="blocks.data.length > 0"
                    class="px-4 py-3 border-t border-gray-200 dark:border-gray-700"
                >
                    <Pagination :data="blocks" resource-name="blocks" />
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmDeleteModal
            v-model:show="showDeleteModal"
            :title="deletingBlock?.title ?? ''"
            :message="`Are you sure you want to delete '${deletingBlock?.title}'? This action cannot be undone.`"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import { debounce } from 'lodash';
import * as blockRoutes from '@/routes/admin/content/blocks';


interface Block {
    id: number;
    title: string;
    identifier: string;
    type: string;
    status: string;
    content?: string;
    start_date?: string;
    end_date?: string;
}

interface BlockStatistics {
    total: number;
    active: number;
    inactive: number;
    scheduled: number;
}

interface BlockFilters {
    search?: string;
    status?: string;
    type?: string;
    sort_by?: string;
    sort_order?: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationData {
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    per_page: number;
    links?: PaginationLink[];
    data: Block[];
}

interface Props {
    blocks: PaginationData;
    statistics: BlockStatistics;
    filters: BlockFilters;
}

const props = defineProps<Props>();

const form = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    type: props.filters?.type || '',
    sort_by: props.filters?.sort_by || 'created_at',
    sort_order: props.filters?.sort_order || 'desc',
});

const selectedBlocks = ref<number[]>([]);
const showDeleteModal = ref(false);
const deletingBlock = ref<Block | null>(null);

const allSelected = computed(() => {
    return props.blocks.data.length > 0 && selectedBlocks.value.length === props.blocks.data.length;
});


const toggleAllBlocks = () => {
    if (allSelected.value) {
        selectedBlocks.value = [];
    } else {
        selectedBlocks.value = props.blocks.data.map(block => block.id);
    }
};

const applyFilters = () => {
    router.get(
        blockRoutes.index.url(),
        {
            search: form.value.search || undefined,
            status: form.value.status || undefined,
            type: form.value.type || undefined,
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
    if (!confirm(`Are you sure you want to ${action} ${selectedBlocks.value.length} block(s)?`)) {
        return;
    }

    router.post(blockRoutes.bulkAction.url(), {
        action,
        ids: selectedBlocks.value,
    }, {
        onSuccess: () => {
            selectedBlocks.value = [];
        },
    });
};

const deleteBlock = (id: number, title: string) => {
    // simplified for the modal which just needs title and id, but typed as Block for safety
    // Constructing a partial block object to satisfy the ref type
    deletingBlock.value = { id, title } as Block; 
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!deletingBlock.value) return;

    router.delete(blockRoutes.destroy.url({ block: deletingBlock.value.id }), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingBlock.value = null;
        },
    });
};

const getStatusClass = (status: string) => {
    return status === 'active'
        ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300'
        : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
};

const getTypeClass = (type: string) => {
    const classes: Record<string, string> = {
        text: 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
        html: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
        banner: 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300',
        promotion: 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300',
        newsletter: 'bg-pink-100 dark:bg-pink-900/30 text-pink-800 dark:text-pink-300',
    };
    return classes[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

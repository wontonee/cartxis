<template>
    <AdminLayout title="Blocks">
        <Head title="Blocks" />

        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Content Blocks</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage reusable content blocks for your store</p>
                </div>
                <Link
                    :href="blockRoutes.create.url()"
                     class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <PlusCircle class="w-4 h-4 mr-2" />
                    Create Block
                </Link>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                 <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
                    <div class="flex items-center justify-between">
                         <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Blocks</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.total }}</p>
                         </div>
                         <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg group-hover:bg-blue-100 dark:group-hover:bg-blue-900/40 transition-colors">
                            <Box class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                         </div>
                    </div>
                </div>
                 <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-green-200 dark:hover:border-green-800 transition-colors">
                     <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.active }}</p>
                        </div>
                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg group-hover:bg-green-100 dark:group-hover:bg-green-900/40 transition-colors">
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
                         <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg group-hover:bg-gray-100 dark:group-hover:bg-gray-600 transition-colors">
                            <Slash class="w-6 h-6 text-gray-600 dark:text-gray-400" />
                        </div>
                    </div>
                </div>
                 <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-indigo-200 dark:hover:border-indigo-800 transition-colors">
                     <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Scheduled</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.scheduled }}</p>
                        </div>
                        <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/40 transition-colors">
                            <Calendar class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                        </div>
                     </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Search -->
                     <div class="md:col-span-12 lg:col-span-6">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="form.search"
                                type="text"
                                placeholder="Search by title or identifier..."
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
                                @input="debouncedSearch"
                            />
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="md:col-span-6 lg:col-span-3">
                         <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <Filter class="w-4 h-4 text-gray-400" />
                            </div>
                            <select 
                                v-model="form.status" 
                                class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
                                @change="applyFilters"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <ArrowUpDown class="w-3 h-3 text-gray-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Type Filter -->
                     <div class="md:col-span-6 lg:col-span-3">
                         <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Type</label>
                         <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <Layout class="w-4 h-4 text-gray-400" />
                            </div>
                            <select 
                                v-model="form.type" 
                                class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
                                @change="applyFilters"
                            >
                                <option value="">All Types</option>
                                <option value="text">Text</option>
                                <option value="html">HTML</option>
                                <option value="banner">Banner</option>
                                <option value="promotion">Promotion</option>
                                <option value="newsletter">Newsletter</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <ArrowUpDown class="w-3 h-3 text-gray-400" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Actions -->
                <div v-if="form.search || form.status || form.type" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button
                        @click="clearFilters"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors flex items-center gap-2"
                    >
                        <X class="w-4 h-4" />
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Blocks Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <!-- Bulk Actions -->
                <div
                    v-if="selectedBlocks.length > 0"
                    class="bg-blue-50 dark:bg-blue-900/20 p-4 border-b border-blue-100 dark:border-blue-800"
                >
                    <div class="flex items-center justify-between">
                         <div class="flex items-center">
                            <CheckCircle class="w-4 h-4 mr-2 text-blue-600 dark:text-blue-400" />
                             <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">
                                {{ selectedBlocks.length }} block(s) selected
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="bulkAction('activate')"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:bg-green-900/40 dark:text-green-300 dark:hover:bg-green-900/60"
                            >
                                <Check class="w-4 h-4 mr-1.5" />
                                Activate
                            </button>
                            <button
                                @click="bulkAction('deactivate')"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-orange-700 bg-orange-100 hover:bg-orange-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:bg-orange-900/40 dark:text-orange-300 dark:hover:bg-orange-900/60"
                            >
                                <XCircle class="w-4 h-4 mr-1.5" />
                                Deactivate
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
                                        @change="toggleAllBlocks"
                                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 transition-all"
                                    />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Title
                                </th>
                                <th class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Identifier
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="hidden lg:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Schedule
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                       <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template v-for="block in blocks.data" :key="block.id">
                                <tr :class="['group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors', {'bg-blue-50 dark:bg-blue-900/10': selectedBlocks.includes(block.id)}]">
                                    <td class="px-6 py-4 relative">
                                         <div class="flex items-center">
                                            <button
                                                @click="toggleRow(block.id)"
                                                class="md:hidden absolute left-2 p-1 text-blue-600 hover:text-blue-800 focus:outline-none"
                                            >
                                                <MinusCircle v-if="expandedRows.includes(block.id)" class="w-5 h-5 fill-blue-100 dark:fill-blue-900/30" />
                                                <PlusCircle v-else class="w-5 h-5 fill-blue-50 dark:fill-blue-900/20" />
                                            </button>
                                            <input
                                                type="checkbox"
                                                :value="block.id"
                                                v-model="selectedBlocks"
                                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer transition-all ml-4 md:ml-0"
                                            />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                         <div class="text-sm font-medium text-gray-900 dark:text-white">{{ block.title }}</div>
                                         <div class="md:hidden text-xs text-blue-600 font-mono mt-1">@block('{{ block.identifier }}')</div>
                                    </td>
                                    <td class="hidden md:table-cell px-6 py-4">
                                        <code class="text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-1 rounded border border-blue-100 dark:border-blue-900">
                                            @block('{{ block.identifier }}')
                                        </code>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span 
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm capitalize"
                                            :class="getTypeClass(block.type)"
                                        >
                                            {{ block.type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span 
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm capitalize"
                                            :class="getStatusClass(block.status)"
                                        >
                                            {{ block.status }}
                                        </span>
                                    </td>
                                    <td class="hidden lg:table-cell px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        <div v-if="block.start_date || block.end_date" class="text-xs">
                                            <div v-if="block.start_date" class="flex items-center gap-1"><Calendar class="w-3 h-3 text-green-500" /> Start: {{ formatDate(block.start_date) }}</div>
                                            <div v-if="block.end_date" class="flex items-center gap-1 mt-0.5"><Calendar class="w-3 h-3 text-red-500" /> End: {{ formatDate(block.end_date) }}</div>
                                        </div>
                                        <span v-else class="text-xs text-gray-400 dark:text-gray-500 italic">Always visible</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                            <Link
                                                :href="blockRoutes.edit.url({ block: block.id })"
                                                class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors"
                                                title="Edit Block"
                                            >
                                                <Edit class="w-4 h-4" />
                                            </Link>
                                            <button
                                                @click="deleteBlock(block.id, block.title)"
                                                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                title="Delete Block"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                 <!-- Mobile Expanded Row -->
                                <tr v-if="expandedRows.includes(block.id)" class="bg-gray-50/50 dark:bg-gray-900/50 md:hidden">
                                     <td colspan="7" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                                         <div class="grid grid-cols-1 gap-4 text-sm">
                                            <div>
                                                 <span class="text-xs text-gray-500 font-medium uppercase tracking-wider block mb-1">Schedule</span>
                                                  <div v-if="block.start_date || block.end_date" class="text-sm">
                                                    <div v-if="block.start_date">Start: {{ formatDate(block.start_date) }}</div>
                                                    <div v-if="block.end_date">End: {{ formatDate(block.end_date) }}</div>
                                                </div>
                                                <span v-else class="text-sm text-gray-500 italic">Always visible</span>
                                            </div>
                                         </div>
                                     </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="blocks.data.length === 0" class="text-center py-12">
                     <div class="flex flex-col items-center justify-center">
                         <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                            <Box class="w-8 h-8" />
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No blocks found</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Get started by creating a new block.</p>
                       <Link
                            :href="blockRoutes.create.url()"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <PlusCircle class="w-4 h-4 mr-2" />
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
import {
    PlusCircle,
    Box,
    CheckCircle,
    Slash,
    Calendar,
    Search,
    Filter,
    ArrowUpDown,
    Layout,
    X,
    Check,
    XCircle,
    Trash2,
    Edit,
    MinusCircle
} from 'lucide-vue-next';


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
    url_label?: string; // Add optional property to match some pagination implementations
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
const expandedRows = ref<number[]>([]);
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

const toggleRow = (id: number) => {
    if (expandedRows.value.includes(id)) {
        expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
    } else {
        expandedRows.value.push(id);
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

const clearFilters = () => {
    form.value.search = '';
    form.value.status = '';
    form.value.type = '';
    applyFilters();
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
        ? 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800'
        : 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-900/20 dark:text-gray-300 dark:border-gray-800';
};

const getTypeClass = (type: string) => {
    const classes: Record<string, string> = {
        text: 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-900/20 dark:text-gray-300 dark:border-gray-800',
        html: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
        banner: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800',
        promotion: 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/20 dark:text-orange-300 dark:border-orange-800',
        newsletter: 'bg-pink-50 text-pink-700 border-pink-200 dark:bg-pink-900/20 dark:text-pink-300 dark:border-pink-800',
    };
    return classes[type] || 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-900/20 dark:text-gray-300 dark:border-gray-800';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

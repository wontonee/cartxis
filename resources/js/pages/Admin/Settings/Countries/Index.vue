<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import { debounce } from 'lodash';
import {
    Globe, Plus, Edit, Trash2, Check, X, Search,
    Filter, ChevronDown, CheckCircle, XCircle
} from 'lucide-vue-next';

interface Country {
    id: number;
    name: string;
    code: string;
    code3: string | null;
    phone_code: string | null;
    currency_code: string | null;
    currency_symbol: string | null;
    is_active: boolean;
    sort_order: number;
}

interface PaginatedCountries {
    data: Country[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    countries: PaginatedCountries;
    filters: { search?: string; status?: string };
}>();

const page = usePage();
const flash = computed(() => (page.props as any).flash);
const errors = computed(() => page.props.errors as Record<string, string>);

// Search & Filter
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const applyFilters = () => {
    router.get('/admin/settings/countries', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
};

const performSearch = debounce(() => applyFilters(), 300);

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    applyFilters();
};

// Modal state
const showModal = ref(false);
const modalMode = ref<'add' | 'edit'>('add');
const processing = ref(false);
const form = ref<Partial<Country>>({
    name: '', code: '', code3: '', phone_code: '',
    currency_code: '', currency_symbol: '', is_active: true, sort_order: 0,
});

const openAdd = () => {
    modalMode.value = 'add';
    form.value = { name: '', code: '', code3: '', phone_code: '', currency_code: '', currency_symbol: '', is_active: true, sort_order: 0 };
    showModal.value = true;
};

const openEdit = (country: Country) => {
    modalMode.value = 'edit';
    form.value = { ...country };
    showModal.value = true;
};

const saveCountry = () => {
    processing.value = true;
    const url = modalMode.value === 'add'
        ? '/admin/settings/countries'
        : `/admin/settings/countries/${form.value.id}`;
    const method = modalMode.value === 'add' ? 'post' : 'put';

    router[method](url, form.value as any, {
        preserveScroll: true,
        onSuccess: () => { showModal.value = false; processing.value = false; },
        onError: () => { processing.value = false; },
    });
};

const toggleStatus = (country: Country) => {
    router.post(`/admin/settings/countries/${country.id}/toggle`, {}, { preserveScroll: true });
};

// Bulk selection
const selectedIds = ref<number[]>([]);
const allSelected = computed(() => {
    return props.countries.data.length > 0 && selectedIds.value.length === props.countries.data.length;
});
const someSelected = computed(() => {
    return selectedIds.value.length > 0 && selectedIds.value.length < props.countries.data.length;
});
const toggleAll = () => {
    if (allSelected.value) { selectedIds.value = []; }
    else { selectedIds.value = props.countries.data.map(c => c.id); }
};

const bulkEnable = () => {
    if (!selectedIds.value.length) return;
    router.post('/admin/settings/countries/bulk-toggle', { ids: selectedIds.value, is_active: true }, { preserveScroll: true, onSuccess: () => { selectedIds.value = []; } });
};
const bulkDisable = () => {
    if (!selectedIds.value.length) return;
    router.post('/admin/settings/countries/bulk-toggle', { ids: selectedIds.value, is_active: false }, { preserveScroll: true, onSuccess: () => { selectedIds.value = []; } });
};

// Delete
const showDeleteModal = ref(false);
const deleteTarget = ref<Country | null>(null);
const openDelete = (country: Country) => { deleteTarget.value = country; showDeleteModal.value = true; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/admin/settings/countries/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { showDeleteModal.value = false; deleteTarget.value = null; },
    });
};
</script>

<template>
    <Head title="Countries" />

    <AdminLayout title="Countries">
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Countries</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage countries available in your store.</p>
                </div>
                <button
                    @click="openAdd"
                    class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 dark:focus:ring-offset-gray-900 transition-all shadow-sm hover:shadow-md"
                >
                    <Plus class="w-5 h-5 mr-2" />
                    Add Country
                </button>
            </div>

            <!-- Flash -->
            <div v-if="flash?.success" class="p-3 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-sm text-green-700 dark:text-green-300">
                {{ flash.success }}
            </div>

            <!-- Filters Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-6 lg:col-span-5">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="search"
                                @input="performSearch"
                                @keyup.enter="applyFilters"
                                type="text"
                                placeholder="Search by name, code, or phone code..."
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
                            />
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="md:col-span-3 lg:col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
                        <div class="relative">
                            <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <select
                                v-model="statusFilter"
                                @change="applyFilters"
                                class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
                            >
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <ChevronDown class="w-3 h-3 text-gray-400" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active filters & clear -->
                <div v-if="search || statusFilter" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex flex-wrap gap-2">
                        <span v-if="search" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-100 dark:border-blue-800">
                            Search: {{ search }}
                            <button @click="search = ''; performSearch()" class="ml-1.5 hover:text-blue-900 dark:hover:text-blue-100"><X class="w-3 h-3" /></button>
                        </span>
                        <span v-if="statusFilter" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                            Status: {{ statusFilter === '1' ? 'Active' : 'Inactive' }}
                            <button @click="statusFilter = ''; applyFilters()" class="ml-1.5 hover:text-gray-900 dark:hover:text-white"><X class="w-3 h-3" /></button>
                        </span>
                    </div>
                    <button
                        @click="clearFilters"
                        class="text-sm text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 font-medium flex items-center transition-colors"
                    >
                        <X class="w-4 h-4 mr-1" />
                        Clear All
                    </button>
                </div>
            </div>

            <!-- Bulk Actions -->
            <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="selectedIds.length > 0" class="bg-blue-600 rounded-xl shadow-lg p-3 text-white flex items-center justify-between sticky top-4 z-10 px-6">
                    <span class="text-sm font-semibold flex items-center">
                        <CheckCircle class="w-4 h-4 mr-2" />
                        {{ selectedIds.length }} {{ selectedIds.length === 1 ? 'country' : 'countries' }} selected
                    </span>
                    <div class="flex gap-2">
                        <button
                            @click="bulkEnable"
                            class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-white rounded-lg hover:bg-blue-50 transition-colors uppercase tracking-wide"
                        >
                            Enable
                        </button>
                        <button
                            @click="bulkDisable"
                            class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-white rounded-lg hover:bg-blue-50 transition-colors uppercase tracking-wide"
                        >
                            Disable
                        </button>
                        <div class="w-px h-6 bg-blue-400 mx-1"></div>
                        <button
                            @click="selectedIds = []"
                            class="px-3 py-1.5 text-xs font-bold text-blue-200 hover:text-white rounded-lg transition-colors uppercase tracking-wide"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </transition>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/50">
                            <tr>
                                <th class="w-12 px-6 py-4">
                                    <input
                                        type="checkbox"
                                        :checked="allSelected"
                                        :indeterminate.prop="someSelected"
                                        @change="toggleAll"
                                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 transition-all"
                                    />
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                <th class="hidden md:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Phone</th>
                                <th class="hidden md:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Currency</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-if="countries.data.length === 0">
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                                            <Globe class="w-8 h-8" />
                                        </div>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">No countries found</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm">No countries matched your search criteria. Try adjusting your filters or add a new country.</p>
                                        <button v-if="search || statusFilter" @click="clearFilters" class="mt-4 text-blue-600 hover:text-blue-700 font-medium text-sm">Clear all filters</button>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="country in countries.data"
                                :key="country.id"
                                class="group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors"
                            >
                                <td class="px-6 py-4">
                                    <input
                                        v-model="selectedIds"
                                        type="checkbox"
                                        :value="country.id"
                                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer transition-all"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase tracking-wider border border-gray-200 dark:border-gray-600">{{ country.code }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ country.name }}</span>
                                </td>
                                <td class="hidden md:table-cell px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ country.phone_code || '-' }}
                                </td>
                                <td class="hidden md:table-cell px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <span v-if="country.currency_code">{{ country.currency_symbol }} {{ country.currency_code }}</span>
                                    <span v-else class="text-gray-300 dark:text-gray-600">â€”</span>
                                </td>
                                <td class="px-6 py-4">
                                    <button @click="toggleStatus(country)">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border shadow-sm"
                                            :class="country.is_active
                                                ? 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800'
                                                : 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'"
                                        >
                                            <CheckCircle v-if="country.is_active" class="w-3 h-3 mr-1" />
                                            <XCircle v-else class="w-3 h-3 mr-1" />
                                            {{ country.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                        <button
                                            @click="openEdit(country)"
                                            class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                            title="Edit"
                                        >
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button
                                            @click="openDelete(country)"
                                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                            title="Delete"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <Pagination :data="countries" resource-name="countries" />
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <Globe class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ modalMode === 'add' ? 'Add Country' : 'Edit Country' }}</h3>
                    </div>
                    <button @click="showModal = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <div class="p-6 overflow-y-auto space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country Name *</label>
                        <input v-model="form.name" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500" />
                        <p v-if="errors.name" class="mt-1 text-xs text-red-500">{{ errors.name }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code (2-letter) *</label>
                            <input v-model="form.code" type="text" maxlength="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 uppercase" />
                            <p v-if="errors.code" class="mt-1 text-xs text-red-500">{{ errors.code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code (3-letter)</label>
                            <input v-model="form.code3" type="text" maxlength="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 uppercase" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Code</label>
                        <input v-model="form.phone_code" type="text" placeholder="+91" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Currency Code</label>
                            <input v-model="form.currency_code" type="text" maxlength="3" placeholder="INR" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 uppercase" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Currency Symbol</label>
                            <input v-model="form.currency_symbol" type="text" maxlength="10" placeholder="â‚¹" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500" />
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <button
                        @click="showModal = false"
                        class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveCountry"
                        :disabled="processing"
                        class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 disabled:opacity-50 transition-colors"
                    >
                        {{ processing ? 'Saving...' : (modalMode === 'add' ? 'Add Country' : 'Save Changes') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <ConfirmDeleteModal
            v-model:show="showDeleteModal"
            :title="deleteTarget?.name ?? ''"
            :message="`Are you sure you want to delete '${deleteTarget?.name}'? This action cannot be undone.`"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>

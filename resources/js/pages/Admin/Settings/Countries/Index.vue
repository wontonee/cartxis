<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import {
    Globe, Plus, Edit, Trash2, Check, X, Search,
    ChevronLeft, ChevronRight, ToggleLeft, ToggleRight,
    Phone, Coins, Save, Filter
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
    }, { preserveState: true, replace: true });
};

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

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
    <AdminLayout>
        <Head title="Countries" />

        <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                            <Globe class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        Countries
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage countries available in your store. {{ countries.total }} total.
                    </p>
                </div>
                <button @click="openAdd" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 shadow-sm transition">
                    <Plus class="w-4 h-4" /> Add Country
                </button>
            </div>

            <!-- Flash -->
            <div v-if="flash?.success" class="mb-4 p-3 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-sm text-green-700 dark:text-green-300">
                {{ flash.success }}
            </div>

            <!-- Search & Filter Bar -->
            <div class="flex flex-col sm:flex-row gap-3 mb-4">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input v-model="search" type="text" placeholder="Search by name, code, or phone code..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                </div>
                <select v-model="statusFilter" @change="applyFilters"
                    class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <!-- Bulk Actions -->
            <div v-if="selectedIds.length" class="flex items-center gap-3 mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                <span class="text-sm font-medium text-blue-700 dark:text-blue-300">{{ selectedIds.length }} selected</span>
                <button @click="bulkEnable" class="px-3 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-lg hover:bg-green-200">Enable</button>
                <button @click="bulkDisable" class="px-3 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-lg hover:bg-red-200">Disable</button>
                <button @click="selectedIds = []" class="px-3 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Clear</button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 w-10">
                                <input type="checkbox" :checked="allSelected" @change="toggleAll" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500" />
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Code</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Phone</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Currency</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="country in countries.data" :key="country.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3">
                                <input type="checkbox" :value="country.id" v-model="selectedIds" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500" />
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase">{{ country.code }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ country.name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ country.phone_code || '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                <span v-if="country.currency_code">{{ country.currency_symbol }} {{ country.currency_code }}</span>
                                <span v-else>-</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button @click="toggleStatus(country)" class="inline-flex items-center">
                                    <span v-if="country.is_active" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                        <Check class="w-3 h-3" /> Active
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                        <X class="w-3 h-3" /> Inactive
                                    </span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEdit(country)" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition">
                                        <Edit class="w-4 h-4" />
                                    </button>
                                    <button @click="openDelete(country)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!countries.data.length">
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">No countries found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="countries.last_page > 1" class="flex items-center justify-between mt-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Showing {{ (countries.current_page - 1) * countries.per_page + 1 }}-{{ Math.min(countries.current_page * countries.per_page, countries.total) }} of {{ countries.total }}
                </p>
                <div class="flex gap-1">
                    <template v-for="link in countries.links" :key="link.label">
                        <button
                            v-if="link.url"
                            @click="router.get(link.url, {}, { preserveState: true })"
                            class="px-3 py-1.5 text-sm rounded-lg border transition"
                            :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                            v-html="link.label"
                        />
                        <span v-else class="px-3 py-1.5 text-sm text-gray-400" v-html="link.label" />
                    </template>
                </div>
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
                            <input v-model="form.currency_symbol" type="text" maxlength="10" placeholder="₹" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500" />
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button @click="saveCountry" :disabled="processing" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 disabled:opacity-50 inline-flex items-center gap-2">
                        <Save class="w-4 h-4" />
                        {{ processing ? 'Saving...' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <ConfirmDeleteModal
            :show="showDeleteModal"
            :title="`Delete ${deleteTarget?.name || 'Country'}`"
            :message="`Are you sure you want to delete ${deleteTarget?.name}? This action cannot be undone.`"
            @confirm="confirmDelete"
            @cancel="showDeleteModal = false"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import { debounce } from 'lodash';
import { 
    Languages, 
    Coins, 
    Plus, 
    Edit, 
    Trash2, 
    Check, 
    X, 
    Globe,
    Save,
    Search,
    Filter,
    ChevronDown,
    CheckCircle,
    MoreHorizontal
} from 'lucide-vue-next';

interface Locale {
    id: number;
    code: string;
    name: string;
    native_name: string | null;
    direction: 'ltr' | 'rtl';
    is_default: boolean;
    is_active: boolean;
    sort_order: number;
}

interface Currency {
    id: number;
    code: string;
    name: string;
    symbol: string;
    symbol_position: 'before' | 'after';
    decimal_places: number;
    exchange_rate: number;
    is_default: boolean;
    is_active: boolean;
    sort_order: number;
}

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

const props = defineProps<{
    locales: Locale[];
    currencies: Currency[];
    countries: Country[];
}>();

const page = usePage();
const errors = computed(() => page.props.errors as Record<string, string>);

// Active tab
const activeTab = ref<'countries' | 'languages' | 'currencies'>('countries');

// Locale Modal State
const showLocaleModal = ref(false);
const localeMode = ref<'add' | 'edit'>('add');
const localeProcessing = ref(false);
const localeForm = ref<Partial<Locale>>({
    code: '',
    name: '',
    native_name: '',
    direction: 'ltr',
    is_default: false,
    is_active: true,
    sort_order: 0,
});

// Currency Modal State
const showCurrencyModal = ref(false);
const currencyMode = ref<'add' | 'edit'>('add');
const currencyProcessing = ref(false);
const currencyForm = ref<Partial<Currency>>({
    code: '',
    name: '',
    symbol: '',
    symbol_position: 'before',
    decimal_places: 2,
    exchange_rate: 1.0,
    is_default: false,
    is_active: true,
    sort_order: 0,
});

// Country State
const countrySearch = ref('');
const countryStatusFilter = ref('');
const filteredCountries = computed(() => {
    return props.countries.filter(c => {
        const matchesSearch = !countrySearch.value ||
            c.name.toLowerCase().includes(countrySearch.value.toLowerCase()) ||
            c.code.toLowerCase().includes(countrySearch.value.toLowerCase()) ||
            (c.phone_code || '').includes(countrySearch.value);
        const matchesStatus = !countryStatusFilter.value ||
            (countryStatusFilter.value === '1' ? c.is_active : !c.is_active);
        return matchesSearch && matchesStatus;
    });
});

const showCountryModal = ref(false);
const countryMode = ref<'add' | 'edit'>('add');
const countryProcessing = ref(false);
const countryForm = ref<Partial<Country>>({
    name: '', code: '', code3: '', phone_code: '',
    currency_code: '', currency_symbol: '', is_active: true, sort_order: 0,
});

const openAddCountryModal = () => {
    countryMode.value = 'add';
    countryForm.value = { name: '', code: '', code3: '', phone_code: '', currency_code: '', currency_symbol: '', is_active: true, sort_order: 0 };
    showCountryModal.value = true;
};

const openEditCountryModal = (country: Country) => {
    countryMode.value = 'edit';
    countryForm.value = { ...country };
    showCountryModal.value = true;
};

const saveCountry = () => {
    countryProcessing.value = true;
    const url = countryMode.value === 'add'
        ? '/admin/settings/countries'
        : `/admin/settings/countries/${countryForm.value.id}`;
    const method = countryMode.value === 'add' ? 'post' : 'put';
    router[method](url, countryForm.value as any, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => { showCountryModal.value = false; countryProcessing.value = false; },
        onError: () => { countryProcessing.value = false; },
    });
};

const toggleCountryStatus = (country: Country) => {
    router.post(`/admin/settings/countries/${country.id}/toggle`, {}, { preserveScroll: true, preserveState: true });
};

const countrySelectedIds = ref<number[]>([]);
const allCountriesSelected = computed(() =>
    filteredCountries.value.length > 0 && countrySelectedIds.value.length === filteredCountries.value.length
);
const someCountriesSelected = computed(() =>
    countrySelectedIds.value.length > 0 && countrySelectedIds.value.length < filteredCountries.value.length
);
const toggleAllCountries = () => {
    countrySelectedIds.value = allCountriesSelected.value ? [] : filteredCountries.value.map(c => c.id);
};
const bulkEnableCountries = () => {
    if (!countrySelectedIds.value.length) return;
    router.post('/admin/settings/countries/bulk-toggle', { ids: countrySelectedIds.value, is_active: true }, {
        preserveScroll: true, preserveState: true, onSuccess: () => { countrySelectedIds.value = []; },
    });
};
const bulkDisableCountries = () => {
    if (!countrySelectedIds.value.length) return;
    router.post('/admin/settings/countries/bulk-toggle', { ids: countrySelectedIds.value, is_active: false }, {
        preserveScroll: true, preserveState: true, onSuccess: () => { countrySelectedIds.value = []; },
    });
};

// Delete Modal State
const showDeleteModal = ref(false);
const deleteTarget = ref<{ type: 'locale' | 'currency' | 'country'; item: any } | null>(null);

// Locale Functions
const openAddLocaleModal = () => {
    localeMode.value = 'add';
    localeForm.value = {
        code: '',
        name: '',
        native_name: '',
        direction: 'ltr',
        is_default: false,
        is_active: true,
        sort_order: 0,
    };
    showLocaleModal.value = true;
};

const openEditLocaleModal = (locale: Locale) => {
    localeMode.value = 'edit';
    localeForm.value = { ...locale };
    showLocaleModal.value = true;
};

const saveLocale = () => {
    const url = localeMode.value === 'add'
        ? '/admin/settings/locales/locale'
        : `/admin/settings/locales/locale/${localeForm.value.id}`;
    
    const method = localeMode.value === 'add' ? 'post' : 'put';

    localeProcessing.value = true;
    router[method](url, localeForm.value, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            localeProcessing.value = false;
            showLocaleModal.value = false;
        },
        onError: () => {
            localeProcessing.value = false;
        },
        onFinish: () => {
            localeProcessing.value = false;
        },
    });
};

const deleteLocale = (locale: Locale) => {
    deleteTarget.value = { type: 'locale', item: locale };
    showDeleteModal.value = true;
};

// Currency Functions
const openAddCurrencyModal = () => {
    currencyMode.value = 'add';
    currencyForm.value = {
        code: '',
        name: '',
        symbol: '',
        symbol_position: 'before',
        decimal_places: 2,
        exchange_rate: 1.0,
        is_default: false,
        is_active: true,
        sort_order: 0,
    };
    showCurrencyModal.value = true;
};

const openEditCurrencyModal = (currency: Currency) => {
    currencyMode.value = 'edit';
    currencyForm.value = { ...currency };
    showCurrencyModal.value = true;
};

const saveCurrency = () => {
    const url = currencyMode.value === 'add'
        ? '/admin/settings/locales/currency'
        : `/admin/settings/locales/currency/${currencyForm.value.id}`;

    const method = currencyMode.value === 'add' ? 'post' : 'put';

    currencyProcessing.value = true;
    router[method](url, currencyForm.value, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            currencyProcessing.value = false;
            showCurrencyModal.value = false;
        },
        onError: () => {
            currencyProcessing.value = false;
        },
        onFinish: () => {
            currencyProcessing.value = false;
        },
    });
};

const deleteCurrency = (currency: Currency) => {
    deleteTarget.value = { type: 'currency', item: currency };
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!deleteTarget.value) return;
    
    const { type, item } = deleteTarget.value;
    const url = type === 'locale'
        ? `/admin/settings/locales/locale/${item.id}`
        : type === 'currency'
        ? `/admin/settings/locales/currency/${item.id}`
        : `/admin/settings/countries/${item.id}`;
    
    router.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            deleteTarget.value = null;
        },
    });
};
</script>

<template>
    <AdminLayout title="Locales & Countries">
        <Head title="Locales & Countries" />
        
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Locales & Countries</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage countries, languages and currencies for your store</p>
                </div>
                <div class="flex gap-3">
                    <button
                        v-if="activeTab === 'countries'"
                        @click="openAddCountryModal"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-xl text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200"
                    >
                        <Plus class="w-4 h-4 mr-2" />
                        Add Country
                    </button>
                    <button
                        v-if="activeTab === 'languages'"
                        @click="openAddLocaleModal"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-xl text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200"
                    >
                        <Plus class="w-4 h-4 mr-2" />
                        Add Language
                    </button>
                    <button
                        v-if="activeTab === 'currencies'"
                        @click="openAddCurrencyModal"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-xl text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200"
                    >
                        <Plus class="w-4 h-4 mr-2" />
                        Add Currency
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800">
                    <div class="flex space-x-8 px-6">
                        <button
                            @click="activeTab = 'countries'"
                            :class="[
                                'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
                                activeTab === 'countries'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'
                            ]"
                        >
                            <Globe class="w-4 h-4 mr-2" :class="activeTab === 'countries' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
                            Countries
                        </button>
                        <button
                            @click="activeTab = 'languages'"
                            :class="[
                                'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
                                activeTab === 'languages'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'
                            ]"
                        >
                            <Languages class="w-4 h-4 mr-2" :class="activeTab === 'languages' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
                            Languages
                        </button>
                        <button
                            @click="activeTab = 'currencies'"
                            :class="[
                                'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
                                activeTab === 'currencies'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'
                            ]"
                        >
                            <Coins class="w-4 h-4 mr-2" :class="activeTab === 'currencies' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
                            Currencies
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Countries Tab -->
                    <div v-show="activeTab === 'countries'">
                        <!-- Search & Filter -->
                        <div class="mb-4 flex flex-col sm:flex-row gap-3">
                            <div class="relative flex-1">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input
                                    v-model="countrySearch"
                                    type="text"
                                    placeholder="Search by name, code or phone code..."
                                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
                                />
                            </div>
                            <div class="relative w-40">
                                <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <select
                                    v-model="countryStatusFilter"
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

                        <!-- Bulk Actions Bar -->
                        <transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-2"
                        >
                            <div v-if="countrySelectedIds.length > 0" class="mb-3 bg-blue-600 rounded-xl p-3 text-white flex items-center justify-between px-4">
                                <span class="text-sm font-semibold flex items-center">
                                    <CheckCircle class="w-4 h-4 mr-2" />
                                    {{ countrySelectedIds.length }} {{ countrySelectedIds.length === 1 ? 'country' : 'countries' }} selected
                                </span>
                                <div class="flex gap-2">
                                    <button @click="bulkEnableCountries" class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-white rounded-lg hover:bg-blue-50 transition-colors uppercase tracking-wide">Enable</button>
                                    <button @click="bulkDisableCountries" class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-white rounded-lg hover:bg-blue-50 transition-colors uppercase tracking-wide">Disable</button>
                                    <div class="w-px h-6 bg-blue-400 mx-1"></div>
                                    <button @click="countrySelectedIds = []" class="px-3 py-1.5 text-xs font-bold text-blue-200 hover:text-white rounded-lg transition-colors uppercase tracking-wide">Clear</button>
                                </div>
                            </div>
                        </transition>

                        <!-- Table -->
                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="w-12 px-6 py-3">
                                            <input
                                                type="checkbox"
                                                :checked="allCountriesSelected"
                                                :indeterminate.prop="someCountriesSelected"
                                                @change="toggleAllCountries"
                                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4"
                                            />
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                        <th class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Phone</th>
                                        <th class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Currency</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-if="filteredCountries.length === 0">
                                        <td colspan="7" class="px-6 py-12 text-center">
                                            <Globe class="mx-auto h-12 w-12 text-gray-400" />
                                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No countries found</h3>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search or filters.</p>
                                        </td>
                                    </tr>
                                    <tr v-for="country in filteredCountries" :key="country.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="w-12 px-6 py-4">
                                            <input type="checkbox" :value="country.id" v-model="countrySelectedIds" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white uppercase">{{ country.code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ country.name }}</td>
                                        <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ country.phone_code || '-' }}</td>
                                        <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ country.currency_code || '-' }}
                                            <span v-if="country.currency_symbol" class="ml-1 text-gray-400">({{ country.currency_symbol }})</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button
                                                @click="toggleCountryStatus(country)"
                                                :class="country.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                                                class="px-2.5 py-0.5 inline-flex items-center text-xs font-medium rounded-full cursor-pointer hover:opacity-75 transition-opacity"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="country.is_active ? 'bg-green-500' : 'bg-gray-500'"></span>
                                                {{ country.is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <button @click="openEditCountryModal(country)" class="text-gray-400 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 p-1.5 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Edit">
                                                <Edit class="w-4 h-4" />
                                            </button>
                                            <button @click="deleteTarget = { type: 'country', item: country }; showDeleteModal = true" class="text-gray-400 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 p-1.5 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete">
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Showing {{ filteredCountries.length }} of {{ countries.length }} countries</p>
                    </div>

                    <!-- Languages Tab -->
                    <div v-show="activeTab === 'languages'">
                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Native Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Direction</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Default</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="locale in locales" :key="locale.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white uppercase">{{ locale.code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ locale.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ locale.native_name || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ locale.direction.toUpperCase() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="locale.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'" class="px-2.5 py-0.5 inline-flex items-center text-xs font-medium rounded-full">
                                                <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="locale.is_active ? 'bg-green-500' : 'bg-gray-500'"></span>
                                                {{ locale.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <span v-if="locale.is_default" class="inline-flex items-center text-blue-600 dark:text-blue-400 font-medium text-xs bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded">
                                                <Check class="w-3 h-3 mr-1" />
                                                Default
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <button 
                                                @click="openEditLocaleModal(locale)" 
                                                class="text-gray-400 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 p-1.5 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" 
                                                title="Edit"
                                            >
                                                <Edit class="w-4 h-4" />
                                            </button>
                                            <button 
                                                @click="deleteLocale(locale)" 
                                                class="text-gray-400 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 p-1.5 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" 
                                                title="Delete"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="locales.length === 0">
                                        <td colspan="7" class="px-6 py-12 text-center">
                                            <Globe class="mx-auto h-12 w-12 text-gray-400" />
                                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No languages</h3>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new language.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Currencies Tab -->
                    <div v-show="activeTab === 'currencies'">
                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Symbol</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Position</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Exchange Rate</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Default</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="currency in currencies" :key="currency.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white uppercase">{{ currency.code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ currency.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ currency.symbol }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white capitalize">{{ currency.symbol_position }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ currency.exchange_rate }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="currency.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'" class="px-2.5 py-0.5 inline-flex items-center text-xs font-medium rounded-full">
                                                <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="currency.is_active ? 'bg-green-500' : 'bg-gray-500'"></span>
                                                {{ currency.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <span v-if="currency.is_default" class="inline-flex items-center text-blue-600 dark:text-blue-400 font-medium text-xs bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded">
                                                <Check class="w-3 h-3 mr-1" />
                                                Default
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <button 
                                                @click="openEditCurrencyModal(currency)" 
                                                class="text-gray-400 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 p-1.5 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" 
                                                title="Edit"
                                            >
                                                <Edit class="w-4 h-4" />
                                            </button>
                                            <button 
                                                @click="deleteCurrency(currency)" 
                                                class="text-gray-400 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 p-1.5 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" 
                                                title="Delete"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="currencies.length === 0">
                                        <td colspan="8" class="px-6 py-12 text-center">
                                            <Coins class="mx-auto h-12 w-12 text-gray-400" />
                                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No currencies</h3>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new currency.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Locale Modal -->
        <div v-if="showLocaleModal" class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showLocaleModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <Languages class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ localeMode === 'add' ? 'Add Language' : 'Edit Language' }}</h3>
                    </div>
                    <button
                        type="button"
                        @click="showLocaleModal = false"
                        class="p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <div class="px-6 py-6 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Language Code <span class="text-red-500">*</span></label>
                        <input 
                            v-model="localeForm.code" 
                            type="text" 
                            placeholder="en, es, fr..." 
                            maxlength="10" 
                            :class="['w-full px-4 py-2.5 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white dark:border-gray-600', errors.code ? 'border-red-500' : 'border-gray-300']"
                        >
                        <p v-if="errors.code" class="mt-1 text-sm text-red-600">{{ errors.code }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Display Name <span class="text-red-500">*</span></label>
                        <input 
                            v-model="localeForm.name" 
                            type="text" 
                            placeholder="English, Spanish..." 
                            :class="['w-full px-4 py-2.5 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white dark:border-gray-600', errors.name ? 'border-red-500' : 'border-gray-300']"
                        >
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Native Name</label>
                        <input 
                            v-model="localeForm.native_name" 
                            type="text" 
                            placeholder="Español, Français..." 
                            :class="['w-full px-4 py-2.5 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white dark:border-gray-600', errors.native_name ? 'border-red-500' : 'border-gray-300']"
                        >
                        <p v-if="errors.native_name" class="mt-1 text-sm text-red-600">{{ errors.native_name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Text Direction <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select 
                                v-model="localeForm.direction" 
                                :class="['w-full px-4 py-2.5 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white dark:border-gray-600 appearance-none', errors.direction ? 'border-red-500' : 'border-gray-300']"
                            >
                                <option value="ltr">Left to Right (LTR)</option>
                                <option value="rtl">Right to Left (RTL)</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <p v-if="errors.direction" class="mt-1 text-sm text-red-600">{{ errors.direction }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Sort Order</label>
                        <input 
                            v-model.number="localeForm.sort_order" 
                            type="number" 
                            :class="['w-full px-4 py-2.5 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white dark:border-gray-600', errors.sort_order ? 'border-red-500' : 'border-gray-300']"
                        >
                        <p v-if="errors.sort_order" class="mt-1 text-sm text-red-600">{{ errors.sort_order }}</p>
                    </div>
                    
                    <div class="flex items-center space-x-6 pt-2">
                        <label class="flex items-center cursor-pointer">
                            <input v-model="localeForm.is_active" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 select-none">Active</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input v-model="localeForm.is_default" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 select-none">Set as Default</span>
                        </label>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-3 flex-shrink-0 border-t border-gray-200 dark:border-gray-700">
                    <button 
                        @click="showLocaleModal = false" 
                        class="px-5 py-2.5 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="saveLocale" 
                        :disabled="localeProcessing" 
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium shadow-sm transition-all"
                    >
                        <Save v-if="!localeProcessing" class="w-4 h-4" />
                        <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ localeProcessing ? 'Saving...' : (localeMode === 'add' ? 'Add Language' : 'Save Changes') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Country Modal -->
        <div v-if="showCountryModal" class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showCountryModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] flex flex-col overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <Globe class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ countryMode === 'add' ? 'Add Country' : 'Edit Country' }}</h3>
                    </div>
                    <button type="button" @click="showCountryModal = false" class="p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <div class="px-6 py-6 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Country Name <span class="text-red-500">*</span></label>
                            <input v-model="countryForm.name" type="text" placeholder="United States" :class="['w-full px-4 py-2.5 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white dark:border-gray-600', errors.name ? 'border-red-500' : 'border-gray-300']">
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">ISO Code (2-char) <span class="text-red-500">*</span></label>
                            <input v-model="countryForm.code" type="text" placeholder="US" maxlength="2" :class="['w-full px-4 py-2.5 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white dark:border-gray-600 uppercase', errors.code ? 'border-red-500' : 'border-gray-300']">
                            <p v-if="errors.code" class="mt-1 text-sm text-red-600">{{ errors.code }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">ISO Code (3-char)</label>
                            <input v-model="countryForm.code3" type="text" placeholder="USA" maxlength="3" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white uppercase">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Phone Code</label>
                            <input v-model="countryForm.phone_code" type="text" placeholder="+1" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Currency Code</label>
                            <input v-model="countryForm.currency_code" type="text" placeholder="USD" maxlength="3" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white uppercase">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Currency Symbol</label>
                            <input v-model="countryForm.currency_symbol" type="text" placeholder="$" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                    <div class="flex items-center gap-6 pt-2">
                        <label class="flex items-center cursor-pointer">
                            <input v-model="countryForm.is_active" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 select-none">Active</span>
                        </label>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sort Order</label>
                            <input v-model.number="countryForm.sort_order" type="number" class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-3 flex-shrink-0 border-t border-gray-200 dark:border-gray-700">
                    <button @click="showCountryModal = false" class="px-5 py-2.5 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-colors">Cancel</button>
                    <button @click="saveCountry" :disabled="countryProcessing" class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium shadow-sm transition-all">
                        <Save v-if="!countryProcessing" class="w-4 h-4" />
                        <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ countryProcessing ? 'Saving...' : (countryMode === 'add' ? 'Add Country' : 'Save Changes') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Currency Modal -->
        <div v-if="showCurrencyModal" class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showCurrencyModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <Coins class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ currencyMode === 'add' ? 'Add Currency' : 'Edit Currency' }}</h3>
                    </div>
                    <button
                        type="button"
                        @click="showCurrencyModal = false"
                        class="p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <div class="px-6 py-6 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Currency Code <span class="text-red-500">*</span></label>
                        <input 
                            v-model="currencyForm.code" 
                            type="text" 
                            placeholder="USD, EUR, GBP..." 
                            maxlength="10" 
                            :class="['w-full px-4 py-2.5 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white dark:border-gray-600', errors.code ? 'border-red-500' : 'border-gray-300']"
                        >
                        <p v-if="errors.code" class="mt-1 text-sm text-red-600">{{ errors.code }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Display Name <span class="text-red-500">*</span></label>
                        <input 
                            v-model="currencyForm.name" 
                            type="text" 
                            placeholder="US Dollar, Euro..." 
                            :class="['w-full px-4 py-2.5 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white dark:border-gray-600', errors.name ? 'border-red-500' : 'border-gray-300']"
                        >
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Symbol <span class="text-red-500">*</span></label>
                            <input 
                                v-model="currencyForm.symbol" 
                                type="text" 
                                placeholder="$, €, £..." 
                                :class="['w-full px-4 py-2.5 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white dark:border-gray-600', errors.symbol ? 'border-red-500' : 'border-gray-300']"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Position <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select 
                                    v-model="currencyForm.symbol_position" 
                                    :class="['w-full px-4 py-2.5 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white dark:border-gray-600 appearance-none', errors.symbol_position ? 'border-red-500' : 'border-gray-300']"
                                >
                                    <option value="before">Before ($100)</option>
                                    <option value="after">After (100€)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-if="errors.symbol" class="mt-1 text-sm text-red-600">{{ errors.symbol }}</p>
                    <p v-if="errors.symbol_position" class="mt-1 text-sm text-red-600">{{ errors.symbol_position }}</p>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Decimal Places <span class="text-red-500">*</span></label>
                            <input 
                                v-model.number="currencyForm.decimal_places" 
                                type="number" 
                                min="0" 
                                max="10" 
                                :class="['w-full px-4 py-2.5 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white dark:border-gray-600', errors.decimal_places ? 'border-red-500' : 'border-gray-300']"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Exchange Rate <span class="text-red-500">*</span></label>
                            <input 
                                v-model.number="currencyForm.exchange_rate" 
                                type="number" 
                                step="0.0001" 
                                :class="['w-full px-4 py-2.5 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white dark:border-gray-600', errors.exchange_rate ? 'border-red-500' : 'border-gray-300']"
                            >
                        </div>
                    </div>
                    <p v-if="errors.decimal_places" class="mt-1 text-sm text-red-600">{{ errors.decimal_places }}</p>
                    <p v-if="errors.exchange_rate" class="mt-1 text-sm text-red-600">{{ errors.exchange_rate }}</p>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Sort Order</label>
                        <input 
                            v-model.number="currencyForm.sort_order" 
                            type="number" 
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        >
                    </div>
                    
                    <div class="flex items-center space-x-6 pt-2">
                        <label class="flex items-center cursor-pointer">
                            <input v-model="currencyForm.is_active" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 select-none">Active</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input v-model="currencyForm.is_default" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 select-none">Set as Default</span>
                        </label>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-3 flex-shrink-0 border-t border-gray-200 dark:border-gray-700">
                    <button 
                        @click="showCurrencyModal = false" 
                        class="px-5 py-2.5 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="saveCurrency" 
                        :disabled="currencyProcessing" 
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium shadow-sm transition-all"
                    >
                        <Save v-if="!currencyProcessing" class="w-4 h-4" />
                        <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ currencyProcessing ? 'Saving...' : (currencyMode === 'add' ? 'Add Currency' : 'Save Changes') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmDeleteModal
            v-model:show="showDeleteModal"
            :title="deleteTarget?.item?.name ?? deleteTarget?.item?.code ?? ''"
            :message="`Are you sure you want to delete '${deleteTarget?.item?.name ?? deleteTarget?.item?.code}'? This action cannot be undone.`"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>

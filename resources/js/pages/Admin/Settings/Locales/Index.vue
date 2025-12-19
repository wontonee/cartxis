<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';

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

const props = defineProps<{
    locales: Locale[];
    currencies: Currency[];
}>();

const page = usePage();
const errors = computed(() => page.props.errors as Record<string, string>);

// Active tab
const activeTab = ref<'languages' | 'currencies'>('languages');

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

// Delete Modal State
const showDeleteModal = ref(false);
const deleteTarget = ref<{ type: 'locale' | 'currency'; item: Locale | Currency } | null>(null);

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
        : `/admin/settings/locales/currency/${item.id}`;
    
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
    <AdminLayout title="Locales & Currencies">
        <Head title="Locales & Currencies" />
        
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Locales & Currencies</h1>
                <p class="mt-1 text-sm text-gray-600">Manage languages and currencies for your store</p>
            </div>

            <div class="bg-white rounded-lg shadow">
                <!-- Tabs -->
                <div class="border-b border-gray-200">
                    <div class="flex space-x-8 px-6">
                        <button
                            @click="activeTab = 'languages'"
                            :class="[
                                'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                                activeTab === 'languages'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]"
                        >
                            Languages
                        </button>
                        <button
                            @click="activeTab = 'currencies'"
                            :class="[
                                'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                                activeTab === 'currencies'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]"
                        >
                            Currencies
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Languages Tab -->
                    <div v-if="activeTab === 'languages'">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-medium text-gray-900">Languages</h2>
                            <button
                                @click="openAddLocaleModal"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                            >
                                Add Language
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Native Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Direction</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="locale in locales" :key="locale.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ locale.code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ locale.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ locale.native_name || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ locale.direction.toUpperCase() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="locale.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ locale.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span v-if="locale.is_default" class="text-blue-600 font-medium">✓ Default</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <button @click="openEditLocaleModal(locale)" class="text-blue-600 hover:text-blue-900" title="Edit">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button @click="deleteLocale(locale)" class="text-red-600 hover:text-red-900" title="Delete">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="locales.length === 0">
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">No languages found. Add your first language.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Currencies Tab -->
                    <div v-if="activeTab === 'currencies'">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-medium text-gray-900">Currencies</h2>
                            <button
                                @click="openAddCurrencyModal"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                            >
                                Add Currency
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Symbol</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exchange Rate</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="currency in currencies" :key="currency.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ currency.code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ currency.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ currency.symbol }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ currency.symbol_position }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ currency.exchange_rate }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="currency.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ currency.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span v-if="currency.is_default" class="text-blue-600 font-medium">✓ Default</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <button @click="openEditCurrencyModal(currency)" class="text-blue-600 hover:text-blue-900" title="Edit">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button @click="deleteCurrency(currency)" class="text-red-600 hover:text-red-900" title="Delete">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="currencies.length === 0">
                                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">No currencies found. Add your first currency.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Locale Modal -->
        <div v-if="showLocaleModal" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 flex items-center justify-center z-50 p-4" @click.self="showLocaleModal = false">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 flex-shrink-0">
                    <h3 class="text-lg font-medium text-gray-900">{{ localeMode === 'add' ? 'Add Language' : 'Edit Language' }}</h3>
                </div>
                
                <div class="px-6 py-4 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Language Code *</label>
                        <input v-model="localeForm.code" type="text" placeholder="en, es, fr..." maxlength="10" :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500', errors.code ? 'border-red-500' : 'border-gray-300']">
                        <p v-if="errors.code" class="mt-1 text-sm text-red-600">{{ errors.code }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Display Name *</label>
                        <input v-model="localeForm.name" type="text" placeholder="English, Spanish..." :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500', errors.name ? 'border-red-500' : 'border-gray-300']">
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Native Name</label>
                        <input v-model="localeForm.native_name" type="text" placeholder="Español, Français..." :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500', errors.native_name ? 'border-red-500' : 'border-gray-300']">
                        <p v-if="errors.native_name" class="mt-1 text-sm text-red-600">{{ errors.native_name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Text Direction *</label>
                        <select v-model="localeForm.direction" :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500', errors.direction ? 'border-red-500' : 'border-gray-300']">
                            <option value="ltr">Left to Right (LTR)</option>
                            <option value="rtl">Right to Left (RTL)</option>
                        </select>
                        <p v-if="errors.direction" class="mt-1 text-sm text-red-600">{{ errors.direction }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                        <input v-model.number="localeForm.sort_order" type="number" :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500', errors.sort_order ? 'border-red-500' : 'border-gray-300']">
                        <p v-if="errors.sort_order" class="mt-1 text-sm text-red-600">{{ errors.sort_order }}</p>
                    </div>
                    
                    <div class="flex items-center space-x-6">
                        <label class="flex items-center">
                            <input v-model="localeForm.is_active" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                        <label class="flex items-center">
                            <input v-model="localeForm.is_default" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Set as Default</span>
                        </label>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3 flex-shrink-0 border-t border-gray-200">
                    <button @click="showLocaleModal = false" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Cancel</button>
                    <button @click="saveLocale" :disabled="localeProcessing" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg v-if="!localeProcessing" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ localeProcessing ? 'Saving...' : (localeMode === 'add' ? 'Add' : 'Save') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Currency Modal -->
        <div v-if="showCurrencyModal" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 flex items-center justify-center z-50 p-4" @click.self="showCurrencyModal = false">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 flex-shrink-0">
                    <h3 class="text-lg font-medium text-gray-900">{{ currencyMode === 'add' ? 'Add Currency' : 'Edit Currency' }}</h3>
                </div>
                
                <div class="px-6 py-4 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Currency Code *</label>
                        <input v-model="currencyForm.code" type="text" placeholder="USD, EUR, GBP..." maxlength="10" :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500', errors.code ? 'border-red-500' : 'border-gray-300']">
                        <p v-if="errors.code" class="mt-1 text-sm text-red-600">{{ errors.code }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Display Name *</label>
                        <input v-model="currencyForm.name" type="text" placeholder="US Dollar, Euro..." :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500', errors.name ? 'border-red-500' : 'border-gray-300']">
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Symbol *</label>
                        <input v-model="currencyForm.symbol" type="text" placeholder="$, €, £..." :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500', errors.symbol ? 'border-red-500' : 'border-gray-300']">
                        <p v-if="errors.symbol" class="mt-1 text-sm text-red-600">{{ errors.symbol }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Symbol Position *</label>
                        <select v-model="currencyForm.symbol_position" :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500', errors.symbol_position ? 'border-red-500' : 'border-gray-300']">
                            <option value="before">Before Amount ($100)</option>
                            <option value="after">After Amount (100€)</option>
                        </select>
                        <p v-if="errors.symbol_position" class="mt-1 text-sm text-red-600">{{ errors.symbol_position }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Decimal Places *</label>
                        <input v-model.number="currencyForm.decimal_places" type="number" min="0" max="10" :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500', errors.decimal_places ? 'border-red-500' : 'border-gray-300']">
                        <p v-if="errors.decimal_places" class="mt-1 text-sm text-red-600">{{ errors.decimal_places }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Exchange Rate *</label>
                        <input v-model.number="currencyForm.exchange_rate" type="number" step="0.0001" :class="['w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500', errors.exchange_rate ? 'border-red-500' : 'border-gray-300']">
                        <p v-if="errors.exchange_rate" class="mt-1 text-sm text-red-600">{{ errors.exchange_rate }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                        <input v-model.number="currencyForm.sort_order" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="flex items-center space-x-6">
                        <label class="flex items-center">
                            <input v-model="currencyForm.is_active" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                        <label class="flex items-center">
                            <input v-model="currencyForm.is_default" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Set as Default</span>
                        </label>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3 flex-shrink-0 border-t border-gray-200">
                    <button @click="showCurrencyModal = false" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">Cancel</button>
                    <button @click="saveCurrency" :disabled="currencyProcessing" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg v-if="!currencyProcessing" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ currencyProcessing ? 'Saving...' : (currencyMode === 'add' ? 'Add' : 'Save') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmDeleteModal
            v-model:show="showDeleteModal"
            :title="deleteTarget?.item?.name ?? ''"
            :message="`Are you sure you want to delete '${deleteTarget?.item?.name}'? This action cannot be undone.`"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>

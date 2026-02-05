<script setup lang="ts">
import { ref, reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { 
    ShoppingCart, 
    Store,
    CheckCircle,
    XCircle,
    ArrowRightLeft,
    Play,
    Terminal
} from 'lucide-vue-next';

const props = defineProps({
    sources: Array,
});

const selectedSource = ref(null);
const selectedEntity = ref('all');
const dryRun = ref(true);
const isProcessing = ref(false);
const migrationOutput = ref('');

const entities = [
    { value: 'all', label: 'All Data', description: 'Migrate all categories, customers, products, and orders' },
    { value: 'categories', label: 'Categories', description: 'Product categories and hierarchies' },
    { value: 'customers', label: 'Customers', description: 'Customer accounts and addresses' },
    { value: 'products', label: 'Products', description: 'Products with inventory and pricing' },
    { value: 'orders', label: 'Orders', description: 'Order history and line items' },
];

const getIcon = (iconName) => {
    const icons = {
        'shopping-cart': ShoppingCart,
        'store': Store,
    };
    return icons[iconName] || ShoppingCart;
};

const selectSource = (source) => {
    selectedSource.value = source;
    migrationOutput.value = '';
};

const showToast = (message, type = 'success') => {
    window.dispatchEvent(new CustomEvent('show-toast', { 
        detail: { message, type }
    }));
};

const testConnection = async () => {
    if (!selectedSource.value) return;
    
    isProcessing.value = true;
    
    try {
        const response = await fetch('/admin/system/migration/test-connection', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                source: selectedSource.value.id,
            }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Connection successful!', 'success');
        } else {
            showToast('Connection failed: ' + data.message, 'error');
        }
    } catch (error) {
        showToast('Connection test failed: ' + error.message, 'error');
    } finally {
        isProcessing.value = false;
    }
};

const startMigration = async () => {
    if (!selectedSource.value) return;
    
    isProcessing.value = true;
    migrationOutput.value = 'Starting migration...\n';
    
    try {
        const response = await fetch('/admin/system/migration/migrate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                source: selectedSource.value.id,
                entity: selectedEntity.value,
                dry_run: dryRun.value,
            }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            migrationOutput.value += '\n' + data.output + '\n\n✓ ' + data.message;
        } else {
            migrationOutput.value += '\n\n✗ ' + data.message;
        }
    } catch (error) {
        migrationOutput.value += '\n\n✗ Migration failed: ' + error.message;
    } finally {
        isProcessing.value = false;
    }
};
</script>

<template>
    <Head title="Data Migration" />

    <AdminLayout title="Data Migration">
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="px-6 py-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between shadow-sm z-10">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <ArrowRightLeft class="w-5 h-5 text-blue-600" />
                        Data Migration
                    </h1>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 p-6 overflow-auto bg-gray-50 dark:bg-gray-900">
                <div class="space-y-6">
                    <!-- Source Selection -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Select Migration Source</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div
                                    v-for="source in sources"
                                    :key="source.id"
                                    @click="selectSource(source)"
                                    class="relative rounded-lg border-2 p-4 cursor-pointer transition-all flex items-start group"
                                    :class="selectedSource?.id === source.id
                                        ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                                        : 'border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-700 hover:shadow-sm'"
                                >
                                    <div class="p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-600 shadow-sm">
                                        <component
                                            :is="getIcon(source.icon)"
                                            class="h-6 w-6 text-gray-600 dark:text-gray-400"
                                        />
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">
                                            {{ source.name }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ source.description }}
                                        </p>
                                        <div class="mt-3 flex items-center">
                                            <component
                                                :is="source.configured ? CheckCircle : XCircle"
                                                class="h-4 w-4"
                                                :class="source.configured ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                            />
                                            <span
                                                class="ml-1.5 text-xs font-medium"
                                                :class="source.configured ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'"
                                            >
                                                {{ source.configured ? 'Configured' : 'Not Configured' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Migration Options -->
                    <div
                        v-if="selectedSource"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
                    >
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Migration Options</h2>
                        </div>
                        
                        <div class="p-6">
                            <!-- Entity Selection -->
                            <div class="mb-6 max-w-xl">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    What to migrate
                                </label>
                                <select
                                    v-model="selectedEntity"
                                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                >
                                    <option
                                        v-for="entity in entities"
                                        :key="entity.value"
                                        :value="entity.value"
                                    >
                                        {{ entity.label }} - {{ entity.description }}
                                    </option>
                                </select>
                            </div>

                            <!-- Dry Run Toggle -->
                            <div class="flex items-center mb-6">
                                <input
                                    id="dry-run"
                                    v-model="dryRun"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700"
                                />
                                <label for="dry-run" class="ml-2 text-sm text-gray-700 dark:text-gray-300 select-none cursor-pointer">
                                    Dry run (preview without making changes)
                                </label>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button
                                    @click="testConnection"
                                    :disabled="isProcessing || !selectedSource.configured"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm"
                                >
                                    Test Connection
                                </button>
                                <button
                                    @click="startMigration"
                                    :disabled="isProcessing || !selectedSource.configured"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm"
                                >
                                    <component :is="dryRun ? Terminal : Play" class="w-4 h-4" />
                                    {{ dryRun ? 'Preview Migration' : 'Start Migration' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Output Console -->
                    <div
                        v-if="migrationOutput"
                        class="bg-gray-900 rounded-lg shadow-sm border border-gray-700 overflow-hidden"
                    >
                        <div class="px-4 py-2 bg-gray-800 border-b border-gray-700 flex items-center justify-between">
                            <h2 class="text-xs font-mono text-gray-400 uppercase tracking-wider">Migration Output</h2>
                        </div>
                        <div class="p-4 font-mono text-xs text-gray-300 whitespace-pre-wrap leading-relaxed max-h-96 overflow-y-auto">
                            {{ migrationOutput }}
                        </div>
                    </div>

                    <!-- Configuration Help -->
                    <div
                        v-if="selectedSource && !selectedSource.configured"
                        class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4"
                    >
                        <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200 mb-2">
                            Configuration Required
                        </h3>
                        <p class="text-sm text-amber-700 dark:text-amber-300 mb-3">
                            Add the following to your .env file:
                        </p>
                        <pre class="text-xs bg-white dark:bg-gray-900 text-amber-900 dark:text-amber-100 p-4 rounded-lg border border-amber-100 dark:border-amber-800/50 font-mono shadow-sm overflow-x-auto">{{ selectedSource.id === 'woocommerce' ? `WOOCOMMERCE_DB_HOST=127.0.0.1
WOOCOMMERCE_DB_PORT=3306
WOOCOMMERCE_DB_DATABASE=wordpress
WOOCOMMERCE_DB_USERNAME=root
WOOCOMMERCE_DB_PASSWORD=
WOOCOMMERCE_DB_PREFIX=wp_
WOOCOMMERCE_HPOS_ENABLED=false` : `BAGISTO_DB_HOST=127.0.0.1
BAGISTO_DB_PORT=3306
BAGISTO_DB_DATABASE=bagisto
BAGISTO_DB_USERNAME=root
BAGISTO_DB_PASSWORD=` }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

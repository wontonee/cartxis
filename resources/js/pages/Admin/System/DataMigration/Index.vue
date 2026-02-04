<script setup lang="ts">
import { ref, reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { 
    ShoppingCart, 
    Store,
    CheckCircle,
    XCircle
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

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

    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Data Migration</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                Import data from WooCommerce or Bagisto to Cartxis
            </p>
        </div>

        <!-- Source Selection -->
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6 mb-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Select Migration Source</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                    v-for="source in sources"
                    :key="source.id"
                    @click="selectSource(source)"
                    :class="[
                        'relative rounded-lg border-2 p-4 cursor-pointer transition-all',
                        selectedSource?.id === source.id
                            ? 'border-blue-600 bg-blue-50 dark:bg-blue-950/40'
                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                    ]"
                >
                    <div class="flex items-start">
                        <component
                            :is="getIcon(source.icon)"
                            class="h-6 w-6 text-gray-400 dark:text-gray-500 mt-1"
                        />
                        <div class="ml-3 flex-1">
                            <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">
                                {{ source.name }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                {{ source.description }}
                            </p>
                            <div class="mt-2 flex items-center">
                                <component
                                    :is="source.configured ? CheckCircle : XCircle"
                                    :class="[
                                        'h-4 w-4',
                                        source.configured ? 'text-green-600' : 'text-red-600'
                                    ]"
                                />
                                <span
                                    :class="[
                                        'ml-1 text-xs',
                                        source.configured ? 'text-green-600' : 'text-red-600'
                                    ]"
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
            class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6 mb-6"
        >
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Migration Options</h2>
            
            <!-- Entity Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    What to migrate
                </label>
                <select
                    v-model="selectedEntity"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
            <div class="flex items-center mb-4">
                <input
                    id="dry-run"
                    v-model="dryRun"
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500"
                />
                <label for="dry-run" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    Dry run (preview without making changes)
                </label>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button
                    @click="testConnection"
                    :disabled="isProcessing || !selectedSource.configured"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Test Connection
                </button>
                <button
                    @click="startMigration"
                    :disabled="isProcessing || !selectedSource.configured"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ dryRun ? 'Preview Migration' : 'Start Migration' }}
                </button>
            </div>
        </div>

        <!-- Output Console -->
        <div
            v-if="migrationOutput"
            class="bg-gray-900 rounded-lg shadow-sm border border-gray-700 p-4"
        >
            <h2 class="text-sm font-medium text-gray-300 mb-2">Migration Output</h2>
            <pre class="text-xs text-gray-300 font-mono whitespace-pre-wrap">{{ migrationOutput }}</pre>
        </div>

        <!-- Configuration Help -->
        <div
            v-if="selectedSource && !selectedSource.configured"
            class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4"
        >
            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">
                Configuration Required
            </h3>
            <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-2">
                Add the following to your .env file:
            </p>
            <pre class="text-xs bg-yellow-100 dark:bg-yellow-900/30 text-yellow-900 dark:text-yellow-100 p-3 rounded border border-yellow-200 dark:border-yellow-800 font-mono">{{ selectedSource.id === 'woocommerce' ? `WOOCOMMERCE_DB_HOST=127.0.0.1
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
</template>

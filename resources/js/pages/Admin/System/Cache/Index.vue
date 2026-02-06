<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Icon from '@/components/Icon.vue';
import { Database } from 'lucide-vue-next';

interface CacheStatistics {
    driver: string;
    total_size: string;
    total_keys: number;
    hit_rate?: string;
    miss_rate?: string;
    uptime?: string;
    memory_usage?: string;
    cache_types: {
        [key: string]: {
            size: string;
            keys: number;
        };
    };
}

interface Props {
    statistics: CacheStatistics;
}

const props = defineProps<Props>();

const statistics = ref<CacheStatistics>(props.statistics);
const selectedTypes = ref<string[]>([]);
const isLoading = ref(false);
const autoRefresh = ref(true);
let refreshInterval: number | null = null;

const cacheTypes = [
    { 
        value: 'application', 
        label: 'Application Cache', 
        description: 'General application cache store',
        icon: 'database'
    },
    { 
        value: 'config', 
        label: 'Configuration', 
        description: 'Bootstrap configuration cache',
        icon: 'settings'
    },
    { 
        value: 'route', 
        label: 'Routes', 
        description: 'Compiled route definitions',
        icon: 'signpost'
    },
    { 
        value: 'view', 
        label: 'Views', 
        description: 'Compiled Blade templates',
        icon: 'eye'
    },
    { 
        value: 'event', 
        label: 'Events', 
        description: 'Event listener mappings',
        icon: 'zap'
    },
];

const rebuildableTypes = ['config', 'route', 'event'];

function toggleSelectAll() {
    if (selectedTypes.value.length === cacheTypes.length) {
        selectedTypes.value = [];
    } else {
        selectedTypes.value = cacheTypes.map(t => t.value);
    }
}

async function fetchStatistics() {
    try {
        const response = await fetch('/admin/system/cache/statistics');
        const result = await response.json();
        if (result.success && result.data) {
            statistics.value = result.data;
        }
    } catch (error) {
        console.error('Failed to fetch statistics:', error);
    }
}

function clearCache(types: string[]) {
    if (types.length === 0) {
        return;
    }

    isLoading.value = true;
    router.post('/admin/system/cache/clear', {
        types,
    }, {
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
            selectedTypes.value = [];
            fetchStatistics();
        },
    });
}

function rebuildCache(types: string[]) {
    const validTypes = types.filter(t => rebuildableTypes.includes(t));
    
    if (validTypes.length === 0) {
        // Show message when trying to rebuild non-rebuildable cache types
        const invalidTypes = types.filter(t => !rebuildableTypes.includes(t));
        if (invalidTypes.length > 0) {
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: {
                    message: `Cannot rebuild ${invalidTypes.join(', ')} cache. Only Config, Route, and Event caches can be rebuilt.`,
                    type: 'error'
                }
            }));
        }
        return;
    }

    isLoading.value = true;
    router.post('/admin/system/cache/rebuild', {
        types: validTypes,
    }, {
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
            fetchStatistics();
        },
    });
}

function clearSingle(type: string) {
    clearCache([type]);
}

function rebuildSingle(type: string) {
    if (rebuildableTypes.includes(type)) {
        rebuildCache([type]);
    }
}

onMounted(() => {
    if (autoRefresh.value) {
        refreshInterval = setInterval(fetchStatistics, 30000); // Refresh every 30s
    }
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<template>
    <Head title="Cache Management" />
    <AdminLayout title="Cache Management">
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        Cache Management
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage and monitor application cache performance and storage.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <input
                                v-model="autoRefresh"
                                type="checkbox"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700"
                            />
                            Auto-refresh
                        </label>
                        <button
                            @click="fetchStatistics"
                            :disabled="isLoading"
                            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh
                        </button>
                    </div>
            </div>

            <!-- Content -->
            <div class="overflow-auto rounded-xl">

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Cache Driver</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.driver }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Size</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.total_size }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Keys</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.total_keys?.toLocaleString() ?? '0' }}</div>
                </div>
                <div v-if="statistics.hit_rate" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Hit Rate</div>
                    <div class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ statistics.hit_rate }}</div>
                </div>
            </div>

            <!-- Additional Stats (Redis only) -->
            <div v-if="statistics.memory_usage || statistics.uptime" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div v-if="statistics.memory_usage" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Memory Usage</div>
                    <div class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ statistics.memory_usage }}</div>
                </div>
                <div v-if="statistics.uptime" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Cache Uptime</div>
                    <div class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ statistics.uptime }}</div>
                </div>
            </div>

            <!-- Cache Types -->
            <div>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        Cache Types
                        <span class="px-2.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-xs font-medium text-gray-600 dark:text-gray-400">
                            {{ cacheTypes.length }}
                        </span>
                    </h2>
                    <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                        <button
                            @click="toggleSelectAll"
                            class="flex-1 sm:flex-none inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                        >
                            {{ selectedTypes.length === cacheTypes.length ? 'Deselect All' : 'Select All' }}
                        </button>
                        <button
                            @click="clearCache(selectedTypes)"
                            :disabled="isLoading || selectedTypes.length === 0"
                            class="flex-1 sm:flex-none inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Clear Selected
                        </button>
                        <button
                            @click="rebuildCache(selectedTypes)"
                            :disabled="isLoading || selectedTypes.length === 0"
                            class="flex-1 sm:flex-none inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Rebuild Selected
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <div
                        v-for="type in cacheTypes"
                        :key="type.value"
                        class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col hover:shadow-md transition-shadow"
                        :class="{'ring-2 ring-blue-500 border-transparent': selectedTypes.includes(type.value)}"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform duration-200">
                                <Icon :name="type.icon" class="w-6 h-6" />
                            </div>
                            <input
                                v-model="selectedTypes"
                                :value="type.value"
                                type="checkbox"
                                class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700 cursor-pointer"
                            />
                        </div>

                        <div class="mb-6 flex-1">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ type.label }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ type.description }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 py-4 border-t border-gray-100 dark:border-gray-700 mb-4 bg-gray-50/50 dark:bg-gray-900/20 -mx-6 px-6">
                            <div>
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Size</div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ statistics.cache_types?.[type.value]?.size || '0 B' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Keys</div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ statistics.cache_types?.[type.value]?.keys || 0 }}</div>
                            </div>
                        </div>

                        <div class="flex gap-2 mt-auto">
                            <button
                                @click="clearSingle(type.value)"
                                :disabled="isLoading"
                                class="flex-1 inline-flex justify-center items-center px-3 py-2 text-sm font-medium text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-transparent rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                Clear
                            </button>
                            <button
                                v-if="rebuildableTypes.includes(type.value)"
                                @click="rebuildSingle(type.value)"
                                :disabled="isLoading"
                                class="flex-1 inline-flex justify-center items-center px-3 py-2 text-sm font-medium text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border border-transparent rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                Rebuild
                            </button>
                            <div v-else class="flex-1"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Cache Types -->
        </div>
    </div>
    </AdminLayout>
</template>

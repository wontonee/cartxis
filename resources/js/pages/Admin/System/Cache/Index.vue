<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Icon from '@/components/Icon.vue';

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
        icon: 'cog'
    },
    { 
        value: 'route', 
        label: 'Routes', 
        description: 'Compiled route definitions',
        icon: 'route'
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
        icon: 'lightning-bolt'
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
        const data = await response.json();
        statistics.value = data;
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
    <AdminLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Cache Management</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage and monitor application cache</p>
                </div>
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input
                            v-model="autoRefresh"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        Auto-refresh
                    </label>
                    <button
                        @click="fetchStatistics"
                        :disabled="isLoading"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Cache Driver</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ statistics.driver }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Total Size</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ statistics.total_size }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Total Keys</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ statistics.total_keys?.toLocaleString() ?? '0' }}</div>
                </div>
                <div v-if="statistics.hit_rate" class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Hit Rate</div>
                    <div class="mt-2 text-2xl font-bold text-green-600">{{ statistics.hit_rate }}</div>
                </div>
            </div>

            <!-- Additional Stats (Redis only) -->
            <div v-if="statistics.memory_usage || statistics.uptime" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-if="statistics.memory_usage" class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Memory Usage</div>
                    <div class="mt-2 text-lg font-semibold text-gray-900">{{ statistics.memory_usage }}</div>
                </div>
                <div v-if="statistics.uptime" class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Cache Uptime</div>
                    <div class="mt-2 text-lg font-semibold text-gray-900">{{ statistics.uptime }}</div>
                </div>
            </div>

            <!-- Cache Types -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-medium text-gray-900">Cache Types</h2>
                        <div class="flex gap-2">
                            <button
                                @click="toggleSelectAll"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                {{ selectedTypes.length === cacheTypes.length ? 'Deselect All' : 'Select All' }}
                            </button>
                            <button
                                @click="clearCache(selectedTypes)"
                                :disabled="isLoading || selectedTypes.length === 0"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Clear Selected
                            </button>
                            <button
                                @click="rebuildCache(selectedTypes)"
                                :disabled="isLoading || selectedTypes.length === 0"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Rebuild Selected
                            </button>
                        </div>
                    </div>
                </div>
                <div class="divide-y divide-gray-200">
                    <div
                        v-for="type in cacheTypes"
                        :key="type.value"
                        class="px-6 py-4 hover:bg-gray-50"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <input
                                    v-model="selectedTypes"
                                    :value="type.value"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <Icon :name="type.icon" class="w-5 h-5 text-gray-500" />
                                        <span class="font-medium text-gray-900">{{ type.label }}</span>
                                    </div>
                                    <p class="text-sm text-gray-500">{{ type.description }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ statistics.cache_types?.[type.value]?.size || '0 B' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ statistics.cache_types?.[type.value]?.keys || 0 }} keys
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        @click="clearSingle(type.value)"
                                        :disabled="isLoading"
                                        class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Clear
                                    </button>
                                    <button
                                        v-if="rebuildableTypes.includes(type.value)"
                                        @click="rebuildSingle(type.value)"
                                        :disabled="isLoading"
                                        class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Rebuild
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

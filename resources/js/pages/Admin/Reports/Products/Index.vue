<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { 
    Calendar, 
    Filter, 
    Download, 
    RefreshCw, 
    AlertTriangle, 
    Package, 
    BarChart2, 
    ShoppingCart,
    DollarSign,
    Box,
    ChevronDown 
} from 'lucide-vue-next';
import { Bar, Doughnut, Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    Filler
} from 'chart.js';

// Register Chart.js components
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    Filler
);

interface Statistics {
    total_quantity_sold: number;
    total_revenue: number;
    unique_products_sold: number;
    total_orders: number;
    total_products: number;
    low_stock_count: number;
    out_of_stock_count: number;
    avg_revenue_per_product: number;
}

interface Product {
    id: number;
    name: string;
    sku: string;
    quantity?: number;
    price?: number;
    total_quantity?: number;
    total_revenue?: number;
    order_count?: number;
    sold_quantity?: number;
}

interface Category {
    category_id: number;
    category_name: string;
    total_quantity: number;
    total_revenue: number;
    product_count: number;
    order_count: number;
}

interface ChartData {
    labels: string[];
    datasets: any[];
}

interface Filters {
    start_date: string;
    end_date: string;
    category_id?: number;
    low_stock_threshold: number;
}

interface Props {
    statistics: Statistics;
    bestSellers: Product[];
    lowStock: Product[];
    outOfStock: Product[];
    categoryPerformance: Category[];
    slowMoving: Product[];
    bestSellersChart: ChartData;
    categoryChart: ChartData;
    salesTrendChart: ChartData;
    filters: Filters;
}

const props = defineProps<Props>();

// Filters
const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);
const categoryFilter = ref(props.filters.category_id || '');
const lowStockThreshold = ref(props.filters.low_stock_threshold);

// Apply filters
const applyFilters = () => {
    router.get('/admin/reports/products', {
        start_date: startDate.value,
        end_date: endDate.value,
        category_id: categoryFilter.value || undefined,
        low_stock_threshold: lowStockThreshold.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Clear filters
const clearFilters = () => {
    startDate.value = '';
    endDate.value = '';
    categoryFilter.value = '';
    lowStockThreshold.value = 10;
    applyFilters();
};

// Chart options
const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            callbacks: {
                label: (context: any) => `Sold: ${context.parsed.y} units`
            }
        }
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(0, 0, 0, 0.05)',
            },
        },
    },
};

const doughnutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'right' as const,
        },
        tooltip: {
            callbacks: {
                label: (context: any) => {
                    const { formatPrice } = useCurrency();
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    return `${label}: ${formatPrice(value)}`;
                }
            }
        }
    },
};

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top' as const,
        },
        tooltip: {
            mode: 'index' as const,
            intersect: false,
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(0, 0, 0, 0.05)',
            },
        },
    },
};

// Format currency
const { formatPrice: formatCurrency } = useCurrency();

// Format number
const formatNumber = (num: number): string => {
    return new Intl.NumberFormat('en-US').format(num);
};
</script>

<template>
    <Head title="Product Reports" />
    <AdminLayout title="Product Reports">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Product Reports</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Track product performance, inventory, and sales trends
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                        <Download :size="16" />
                        Export Report
                    </button>
                    <button 
                        @click="router.reload()"
                        class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm"
                        title="Refresh"
                    >
                        <RefreshCw :size="18" />
                    </button>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Start Date -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Start Date</label>
                        <div class="relative">
                            <Calendar :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
                            <input
                                v-model="startDate"
                                type="date"
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-colors"
                            />
                        </div>
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">End Date</label>
                        <div class="relative">
                            <Calendar :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
                            <input
                                v-model="endDate"
                                type="date"
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-colors"
                            />
                        </div>
                    </div>

                    <!-- Low Stock Threshold -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Low Stock Limit</label>
                        <div class="relative">
                            <AlertTriangle :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
                            <input
                                v-model.number="lowStockThreshold"
                                type="number"
                                min="1"
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-colors"
                            />
                        </div>
                    </div>

                    <!-- Apply Button -->
                    <div class="flex items-end gap-2">
                        <button
                            @click="applyFilters"
                            class="flex-1 px-4 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-500/20 transition-all shadow-sm shadow-blue-600/20"
                        >
                            Apply Filters
                        </button>
                        <button
                            v-if="startDate || endDate || categoryFilter"
                            @click="clearFilters"
                            class="px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Products Sold -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 transition-all hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Quantity Sold</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                            {{ formatNumber(statistics.total_quantity_sold) }}
                        </h3>
                        <p class="text-xs text-gray-400 mt-2.5">Units sold in period</p>
                    </div>
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                        <Box :size="22" class="text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 transition-all hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total Revenue</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                            {{ formatCurrency(statistics.total_revenue) }}
                        </h3>
                        <p class="text-xs text-gray-400 mt-2.5">Revenue generated</p>
                    </div>
                    <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                        <DollarSign :size="22" class="text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 transition-all hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Low Stock</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                            {{ statistics.low_stock_count }}
                        </h3>
                        <p class="text-xs text-gray-400 mt-2.5">Products running low</p>
                    </div>
                    <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl">
                        <AlertTriangle :size="22" class="text-yellow-600 dark:text-yellow-400" />
                    </div>
                </div>
            </div>

            <!-- Out of Stock -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 transition-all hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Out of Stock</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                            {{ statistics.out_of_stock_count }}
                        </h3>
                        <p class="text-xs text-gray-400 mt-2.5">Products unavailable</p>
                    </div>
                    <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl">
                        <BarChart2 :size="22" class="text-red-600 dark:text-red-400" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Best Sellers Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-6">Top 10 Best Sellers</h2>
                <div class="relative w-full h-80">
                    <Bar :data="bestSellersChart" :options="barChartOptions" />
                </div>
            </div>

            <!-- Category Performance -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-6">Revenue by Category</h2>
                <div class="relative w-full h-80 flex justify-center">
                    <Doughnut :data="categoryChart" :options="doughnutChartOptions" />
                </div>
            </div>
        </div>

        <!-- Sales Trend Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 mb-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-6">Sales Trend</h2>
            <div class="relative w-full h-80">
                <Line :data="salesTrendChart" :options="lineChartOptions" />
            </div>
        </div>

        <!-- Tables Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Low Stock Products -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/20">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Low Stock Products</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Product
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    SKU
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Quantity
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="product in lowStock" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-3.5 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ product.name }}
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ product.sku }}
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800">
                                        {{ product.quantity }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="lowStock.length === 0">
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No low stock products
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Slow Moving Products -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/20">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Slow Moving Products</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Product
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    SKU
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Sold
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="product in slowMoving" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-3.5 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ product.name }}
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ product.sku }}
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ product.sold_quantity || 0 }} units
                                </td>
                            </tr>
                            <tr v-if="slowMoving.length === 0">
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No slow moving products
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>
    </AdminLayout>
</template>

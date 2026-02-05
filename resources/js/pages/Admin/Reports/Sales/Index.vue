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
    TrendingUp, 
    ShoppingCart, 
    DollarSign, 
    CreditCard,
    ChevronDown 
} from 'lucide-vue-next';
import { Line, Bar, Doughnut } from 'vue-chartjs';
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
    total_revenue: number;
    order_count: number;
    avg_order_value: number;
    growth_percentage: number;
}

interface ChartData {
    labels: string[];
    datasets: any[];
}

interface Order {
    id: number;
    increment_id: number | string;
    customer_name: string;
    total: string | number;
    status: string;
    created_at: string;
}

interface Filters {
    start_date: string;
    end_date: string;
    status?: string[];
    payment_method?: string;
}

interface Props {
    statistics: Statistics;
    revenueChart: ChartData;
    ordersChart: ChartData;
    paymentChart: ChartData;
    topOrders: Order[];
    filters: Filters;
}

const props = defineProps<Props>();

// Filters
const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);
const statusFilter = ref(props.filters.status?.[0] || '');

// Apply filters
const applyFilters = () => {
    router.get('/admin/reports/sales', {
        start_date: startDate.value,
        end_date: endDate.value,
        status: statusFilter.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Clear filters
const clearFilters = () => {
    startDate.value = '';
    endDate.value = '';
    statusFilter.value = '';
    applyFilters();
};

// Chart options
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
        y: {
            beginAtZero: true,
            ticks: {
                callback: function(value: any) {
                    const { formatPrice } = useCurrency();
                    return formatPrice(value);
                }
            }
        }
    }
};

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
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
    },
};

// Format currency
const { formatPrice: formatCurrency } = useCurrency();

// Status badge classes
const getStatusClass = (status: string) => {
    const classes: Record<string, string> = {
        'completed': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'processing': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'cancelled': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'refunded': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Sales Reports" />
    <AdminLayout title="Sales Reports">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Sales Reports</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Track revenue, orders, and financial performance
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

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Order Status</label>
                        <div class="relative">
                            <Filter :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
                            <select
                                v-model="statusFilter"
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-colors appearance-none"
                            >
                                <option value="">All Statuses</option>
                                <option value="completed">Completed</option>
                                <option value="processing">Processing</option>
                                <option value="pending">Pending</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <ChevronDown :size="16" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
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
                            v-if="startDate || endDate || statusFilter"
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
            <!-- Total Revenue -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 transition-all hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total Revenue</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                            {{ formatCurrency(statistics.total_revenue) }}
                        </h3>
                        <div v-if="statistics.growth_percentage !== 0" class="flex items-center mt-2.5">
                            <span :class="[
                                'text-xs font-medium rounded-full px-2 py-0.5 flex items-center gap-1',
                                statistics.growth_percentage > 0 
                                    ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400' 
                                    : 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                            ]">
                                <TrendingUp :size="12" v-if="statistics.growth_percentage > 0" />
                                <TrendingUp :size="12" class="transform rotate-180" v-else />
                                {{ statistics.growth_percentage > 0 ? '+' : '' }}{{ statistics.growth_percentage.toFixed(1) }}%
                            </span>
                            <span class="text-xs text-gray-400 ml-1.5">vs last period</span>
                        </div>
                    </div>
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                        <DollarSign :size="22" class="text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            <!-- Total Orders -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 transition-all hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total Orders</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                            {{ statistics.order_count }}
                        </h3>
                        <p class="text-xs text-gray-400 mt-2.5">Orders placed in period</p>
                    </div>
                    <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                        <ShoppingCart :size="22" class="text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>

            <!-- Average Order Value -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 transition-all hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Avg. Order Value</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                            {{ formatCurrency(statistics.avg_order_value) }}
                        </h3>
                        <p class="text-xs text-gray-400 mt-2.5">Revenue per order</p>
                    </div>
                    <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl">
                        <CreditCard :size="22" class="text-yellow-600 dark:text-yellow-400" />
                    </div>
                </div>
            </div>

            <!-- Growth -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 transition-all hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total Growth</p>
                        <h3 class="text-2xl font-bold tracking-tight" :class="[
                            statistics.growth_percentage > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                        ]">
                            {{ statistics.growth_percentage > 0 ? '+' : '' }}{{ statistics.growth_percentage.toFixed(2) }}%
                        </h3>
                        <p class="text-xs text-gray-400 mt-2.5">Overall performance</p>
                    </div>
                    <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                        <TrendingUp :size="22" class="text-purple-600 dark:text-purple-400" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 mb-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-6">Revenue Over Time</h2>
            <div class="relative w-full h-80">
                <Line :data="revenueChart" :options="lineChartOptions" />
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Orders by Status -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-6">Orders by Status</h2>
                <div class="relative w-full h-72">
                    <Bar :data="ordersChart" :options="barChartOptions" />
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-6">Payment Methods</h2>
                <div class="relative w-full h-72 flex justify-center">
                    <Doughnut :data="paymentChart" :options="doughnutChartOptions" />
                </div>
            </div>
        </div>

        <!-- Top Orders Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/20">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Top 10 Orders</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Order ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Customer
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-for="order in topOrders" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-3.5 whitespace-nowrap text-sm font-medium text-blue-600 dark:text-blue-400">
                                #{{ order.increment_id }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                {{ order.customer_name }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap text-sm text-gray-900 dark:text-white font-semibold">
                                {{ typeof order.total === 'string' ? order.total : formatCurrency(order.total) }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ order.created_at }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <span :class="['px-2.5 py-0.5 inline-flex text-xs font-medium rounded-full', getStatusClass(order.status)]">
                                    {{ order.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </AdminLayout>
</template>

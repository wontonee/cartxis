<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
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
    total_customers: number;
    new_customers: number;
    repeat_customers: number;
    total_revenue: number;
    avg_order_value: number;
    total_orders: number;
    avg_lifetime_value: number;
    repeat_rate: number;
}

interface Customer {
    customer_id: number;
    customer_name: string;
    customer_email: string;
    order_count: number;
    total_spent: number;
    avg_order_value: number;
}

interface RFMCustomer {
    id: number;
    name: string;
    email: string;
    recency_days: number;
    frequency: number;
    monetary: number;
    recency_score: number;
    frequency_score: number;
    monetary_score: number;
    rfm_score: number;
    segment: string;
}

interface GeographicData {
    country: string;
    customer_count: number;
    order_count: number;
    total_revenue: number;
}

interface ChartData {
    labels: string[];
    datasets: Array<{
        label: string;
        data: number[];
        borderColor?: string;
        backgroundColor?: string | string[];
        tension?: number;
        fill?: boolean;
    }>;
}

interface Filters {
    start_date: string;
    end_date: string;
}

const props = defineProps<{
    statistics: Statistics;
    topCustomers: Customer[];
    acquisitionTrend: Array<any>;
    rfmSegmentation: RFMCustomer[];
    geographicDistribution: GeographicData[];
    lifetimeValueDistribution: Record<string, number>;
    acquisitionChart: ChartData;
    rfmChart: ChartData;
    geographicChart: ChartData;
    ltvChart: ChartData;
    filters: Filters;
}>();

// Filter refs
const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

// Check if filters are active
const hasFilters = computed(() => {
    return startDate.value !== props.filters.start_date ||
           endDate.value !== props.filters.end_date;
});

// Apply filters
const applyFilters = () => {
    router.get(route('admin.reports.customers'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Clear filters
const clearFilters = () => {
    startDate.value = props.filters.start_date;
    endDate.value = props.filters.end_date;
    router.get(route('admin.reports.customers'), {}, {
        preserveState: true,
        preserveScroll: true,
    });
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
        },
    },
};

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top' as const,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
        },
    },
};

const doughnutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'right' as const,
        },
    },
};

// Utility functions
const { formatPrice: formatCurrency } = useCurrency();

const formatNumber = (value: number) => {
    return new Intl.NumberFormat('en-US').format(value);
};

const formatPercentage = (value: number) => {
    return `${value.toFixed(2)}%`;
};

// Get segment badge color
const getSegmentColor = (segment: string) => {
    const colors: Record<string, string> = {
        'Champions': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'Loyal Customers': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'Recent Customers': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        'Frequent Shoppers': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        'Big Spenders': 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300',
        'At Risk': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'Lost': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        'Potential Loyalists': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return colors[segment] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Customer Reports</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Analyze customer behavior, segments, and lifetime value
            </p>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Start Date
                    </label>
                    <input
                        type="date"
                        v-model="startDate"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        End Date
                    </label>
                    <input
                        type="date"
                        v-model="endDate"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                    />
                </div>
                <div class="flex items-end">
                    <button
                        @click="applyFilters"
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                    >
                        Apply Filters
                    </button>
                </div>
            </div>
            <div v-if="hasFilters" class="mt-3 flex justify-end">
                <button
                    @click="clearFilters"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                >
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Customers -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Customers</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatNumber(statistics.total_customers) }}</p>
                    </div>
                </div>
            </div>

            <!-- New Customers -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">New Customers</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatNumber(statistics.new_customers) }}</p>
                    </div>
                </div>
            </div>

            <!-- Avg Lifetime Value -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg Lifetime Value</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(statistics.avg_lifetime_value) }}</p>
                    </div>
                </div>
            </div>

            <!-- Repeat Rate -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-orange-100 dark:bg-orange-900 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Repeat Rate</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatPercentage(statistics.repeat_rate) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Customer Acquisition Trend -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Customer Acquisition Trend</h2>
                <div class="h-80">
                    <Line :data="acquisitionChart" :options="lineChartOptions" />
                </div>
            </div>

            <!-- RFM Segmentation -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Customer Segmentation</h2>
                <div class="h-80">
                    <Doughnut :data="rfmChart" :options="doughnutChartOptions" />
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Geographic Distribution -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Revenue by Country</h2>
                <div class="h-80">
                    <Bar :data="geographicChart" :options="barChartOptions" />
                </div>
            </div>

            <!-- Lifetime Value Distribution -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Lifetime Value Distribution</h2>
                <div class="h-80">
                    <Bar :data="ltvChart" :options="barChartOptions" />
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Customers -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Customers</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Orders
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Total Spent
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="customer in topCustomers" :key="customer.customer_id">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ customer.customer_name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ customer.customer_email }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ customer.order_count }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatCurrency(customer.total_spent) }}
                                </td>
                            </tr>
                            <tr v-if="topCustomers.length === 0">
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No customer data available
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Customer Segments -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Customer Segments (Top 10)</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    RFM Score
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Segment
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="customer in rfmSegmentation.slice(0, 10)" :key="customer.id">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ customer.name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ customer.email }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ customer.rfm_score }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span :class="getSegmentColor(customer.segment)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ customer.segment }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="rfmSegmentation.length === 0">
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No segmentation data available
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

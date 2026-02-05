<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { Line, Bar, Doughnut } from 'vue-chartjs';
import {
    Users,
    UserPlus,
    CreditCard,
    RefreshCcw,
    Map,
    PieChart,
    TrendingUp,
    Filter,
    Download,
    Search,
    Calendar,
    ArrowUpRight,
    ArrowDownRight
} from 'lucide-vue-next';
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
    <Head title="Customer Reports" />
    <AdminLayout title="Customer Reports">
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Customer Reports</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Analyze customer behavior, segments, and lifetime value
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200">
                        <Download class="w-4 h-4" />
                        Export Report
                    </button>
                    <button
                        @click="applyFilters"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-xl text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200"
                    >
                        <Filter class="w-4 h-4" />
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Date Range -->
                    <div class="col-span-2 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                Start Date
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Calendar class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    type="date"
                                    v-model="startDate"
                                    class="block w-full pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                End Date
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Calendar class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    type="date"
                                    v-model="endDate"
                                    class="block w-full pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Active Filters & Clear -->
                <div v-if="hasFilters" class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                    <button
                        @click="clearFilters"
                        class="text-sm text-red-600 hover:text-red-700 font-medium flex items-center gap-2"
                    >
                        <RefreshCcw class="w-4 h-4" />
                        Reset Filters
                    </button>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Customers -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Customers</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatNumber(statistics.total_customers) }}</p>
                            <div class="mt-2 flex items-center text-xs text-green-600 bg-green-50 w-fit px-2 py-1 rounded-full">
                                <ArrowUpRight class="w-3 h-3 mr-1" />
                                <span class="font-medium">Active Base</span>
                            </div>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                            <Users class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                </div>

                <!-- New Customers -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">New Customers</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatNumber(statistics.new_customers) }}</p>
                            <div class="mt-2 flex items-center text-xs text-blue-600 bg-blue-50 w-fit px-2 py-1 rounded-full">
                                <UserPlus class="w-3 h-3 mr-1" />
                                <span class="font-medium">Recently Joined</span>
                            </div>
                        </div>
                        <div class="p-3 bg-green-50 rounded-xl border border-green-100">
                            <UserPlus class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <!-- Avg Lifetime Value -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Avg LTV</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatCurrency(statistics.avg_lifetime_value) }}</p>
                            <div class="mt-2 flex items-center text-xs text-purple-600 bg-purple-50 w-fit px-2 py-1 rounded-full">
                                <TrendingUp class="w-3 h-3 mr-1" />
                                <span class="font-medium">Per Customer</span>
                            </div>
                        </div>
                        <div class="p-3 bg-purple-50 rounded-xl border border-purple-100">
                            <CreditCard class="w-6 h-6 text-purple-600" />
                        </div>
                    </div>
                </div>

                <!-- Repeat Rate -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Repeat Rate</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatPercentage(statistics.repeat_rate) }}</p>
                            <div class="mt-2 flex items-center text-xs text-orange-600 bg-orange-50 w-fit px-2 py-1 rounded-full">
                                <RefreshCcw class="w-3 h-3 mr-1" />
                                <span class="font-medium">Retention</span>
                            </div>
                        </div>
                        <div class="p-3 bg-orange-50 rounded-xl border border-orange-100">
                            <RefreshCcw class="w-6 h-6 text-orange-600" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Customer Acquisition Trend -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-50 rounded-lg">
                                <UserPlus class="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Customer Acquisition</h2>
                                <p class="text-xs text-gray-500">New customer signups over time</p>
                            </div>
                        </div>
                    </div>
                    <div class="h-80">
                        <Line :data="acquisitionChart" :options="lineChartOptions" />
                    </div>
                </div>

                <!-- RFM Segmentation -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-purple-50 rounded-lg">
                                <PieChart class="w-5 h-5 text-purple-600" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Customer Segments</h2>
                                <p class="text-xs text-gray-500">Distribution by customer type</p>
                            </div>
                        </div>
                    </div>
                    <div class="h-80">
                        <Doughnut :data="rfmChart" :options="doughnutChartOptions" />
                    </div>
                </div>

                <!-- Geographic Distribution -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-50 rounded-lg">
                                <Map class="w-5 h-5 text-green-600" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Revenue by Country</h2>
                                <p class="text-xs text-gray-500">Sales performance by location</p>
                            </div>
                        </div>
                    </div>
                    <div class="h-80">
                        <Bar :data="geographicChart" :options="barChartOptions" />
                    </div>
                </div>

                <!-- Lifetime Value Distribution -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-orange-50 rounded-lg">
                                <CreditCard class="w-5 h-5 text-orange-600" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">LTV Distribution</h2>
                                <p class="text-xs text-gray-500">Customer value analysis</p>
                            </div>
                        </div>
                    </div>
                    <div class="h-80">
                        <Bar :data="ltvChart" :options="barChartOptions" />
                    </div>
                </div>
            </div>

            <!-- Tables Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Customers -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex flex-col h-full">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-yellow-50 rounded-lg">
                                <TrendingUp class="w-5 h-5 text-yellow-600" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Top Customers</h2>
                                <p class="text-xs text-gray-500">Highest spending customers by value</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Orders</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Total Spent</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="customer in topCustomers" :key="customer.customer_id" class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-900">{{ customer.customer_name }}</span>
                                            <span class="text-xs text-gray-500">{{ customer.customer_email }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                            {{ customer.order_count }} orders
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-bold text-gray-900">{{ formatCurrency(customer.total_spent) }}</span>
                                    </td>
                                </tr>
                                <tr v-if="topCustomers.length === 0">
                                    <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No customer data available
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Customer Segments List -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex flex-col h-full">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-indigo-50 rounded-lg">
                                <Users class="w-5 h-5 text-indigo-600" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Segment Analysis</h2>
                                <p class="text-xs text-gray-500">Top 10 customers by RFM score</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">RFM Score</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Segment</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="customer in rfmSegmentation.slice(0, 10)" :key="customer.id" class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-900">{{ customer.name }}</span>
                                            <span class="text-xs text-gray-500">{{ customer.email }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-mono text-xs">{{ customer.rfm_score }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span :class="getSegmentColor(customer.segment)" class="px-2 py-1 text-xs font-semibold rounded-full border border-opacity-20 border-gray-300">
                                            {{ customer.segment }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="rfmSegmentation.length === 0">
                                    <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No segmentation data available
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

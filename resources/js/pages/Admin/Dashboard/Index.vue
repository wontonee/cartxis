<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import {
  TrendingUp,
  TrendingDown,
  ShoppingCart,
  DollarSign,
  Users,
  Package,
  ArrowUpRight,
  ArrowDownRight,
  MoreVertical,
  Activity,
  TrendingUpIcon
} from 'lucide-vue-next'
import { computed } from 'vue'

interface StatItem {
  title: string
  value: string
  change: string
  trend: 'up' | 'down'
  icon: string
  color: 'blue' | 'green' | 'purple' | 'orange'
}

interface OrderItem {
  id: string
  customer: string
  product: string
  amount: string
  status: 'completed' | 'processing' | 'pending' | 'cancelled'
  date: string
}

interface ProductItem {
  name: string
  sales: number
  revenue: string
  trend: 'up' | 'down'
  change: string
}

interface SalesChartData {
  labels: string[]
  data: number[]
}

const props = defineProps<{
  auth?: {
    user: {
      name: string
      email: string
    }
  }
  stats?: StatItem[]
  recentOrders?: OrderItem[]
  topProducts?: ProductItem[]
  salesChart?: SalesChartData
  currencySymbol?: string
}>()

// Icon mapping
const iconMap = {
  DollarSign,
  ShoppingCart,
  Users,
  Package
}

// Compute max value for chart scaling
const maxSales = computed(() => {
  if (!props.salesChart?.data?.length) return 1000
  return Math.max(...props.salesChart.data, 1) * 1.2 // Add 20% padding
})

// Compute chart bars
const chartBars = computed(() => {
  if (!props.salesChart?.data) return []
  return props.salesChart.data.map((value, index) => ({
    label: props.salesChart.labels[index] || '',
    value: value,
    height: maxSales.value > 0 ? (value / maxSales.value) * 100 : 0
  }))
})
</script>

<template>
  <Head title="Admin Dashboard" />

  <AdminLayout title="Dashboard">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 mb-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold mb-2">Welcome back, {{ auth?.user?.name || 'Admin' }}! 👋</h2>
          <p class="text-blue-100">Here's what's happening with your store today.</p>
        </div>
        <div class="hidden md:block">
          <div class="bg-white/20 backdrop-blur-sm rounded-lg px-6 py-4">
            <div class="text-sm text-blue-100 mb-1">Today's Date</div>
            <div class="text-xl font-semibold">{{ new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <div
        v-for="stat in (props.stats || [])"
        :key="stat.title"
        class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition-shadow"
      >
        <div class="flex items-center justify-between mb-4">
          <div
            :class="[
              'w-12 h-12 rounded-lg flex items-center justify-center',
              stat.color === 'blue' && 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
              stat.color === 'green' && 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
              stat.color === 'purple' && 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
              stat.color === 'orange' && 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400'
            ]"
          >
            <component :is="iconMap[stat.icon]" class="w-6 h-6" />
          </div>
          <div
            :class="[
              'flex items-center text-sm font-semibold',
              stat.trend === 'up' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
            ]"
          >
            <component :is="stat.trend === 'up' ? ArrowUpRight : ArrowDownRight" class="w-4 h-4 mr-1" />
            {{ stat.change }}
          </div>
        </div>
        <div>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ stat.title }}</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <!-- Sales Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sales Overview</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Last 7 days revenue</p>
        </div>
        <div class="flex items-center space-x-2">
          <Activity class="w-5 h-5 text-blue-600" />
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Daily Sales</span>
        </div>
      </div>
      <div v-if="chartBars.length > 0" class="flex items-end justify-between space-x-2 h-48">
        <div
          v-for="(bar, index) in chartBars"
          :key="index"
          class="flex-1 flex flex-col items-center"
        >
          <div class="w-full flex items-end justify-center" style="height: 160px;">
            <div
              :style="{ height: bar.height + '%' }"
              class="w-full bg-gradient-to-t from-blue-500 to-blue-400 rounded-t-lg hover:from-blue-600 hover:to-blue-500 transition-all cursor-pointer relative group"
              :title="`${props.currencySymbol || '$'}${bar.value.toFixed(2)}`"
            >
              <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                {{ props.currencySymbol || '$' }}{{ bar.value.toFixed(2) }}
              </div>
            </div>
          </div>
          <div class="text-xs text-gray-600 dark:text-gray-400 mt-2 text-center">{{ bar.label }}</div>
        </div>
      </div>
      <div v-else class="h-48 flex items-center justify-center text-gray-500 dark:text-gray-400">
        No sales data available
      </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      <!-- Recent Orders - Takes 2 columns -->
      <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
            <Link href="/admin/sales/orders" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium cursor-pointer transition-colors">View All</Link>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr
                v-if="!props.recentOrders || props.recentOrders.length === 0"
                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
              >
                <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                  No orders yet
                </td>
              </tr>
              <tr
                v-for="order in (props.recentOrders || [])"
                :key="order.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-medium text-gray-900 dark:text-white">{{ order.id }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-900 dark:text-white">{{ order.customer }}</span>
                </td>
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-600 dark:text-gray-300">{{ order.product }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ order.amount }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-semibold rounded-full',
                      order.status === 'completed' && 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                      order.status === 'processing' && 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                      order.status === 'pending' && 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                      order.status === 'cancelled' && 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                    ]"
                  >
                    {{ order.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-500 dark:text-gray-400">{{ order.date }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Top Products - Takes 1 column -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Products</h3>
        </div>
        <div class="p-6">
          <div v-if="!props.topProducts || props.topProducts.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            No products data available
          </div>
          <div v-else class="space-y-4">
            <div
              v-for="(product, index) in (props.topProducts || [])"
              :key="product.name"
              class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow">
                  <span class="text-sm font-bold text-white">{{ index + 1 }}</span>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ product.name }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ product.sales }} sales</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ product.revenue }}</p>
                <div class="flex items-center justify-end">
                  <component
                    :is="product.trend === 'up' ? TrendingUp : TrendingDown"
                    :class="[
                      'w-3 h-3 mr-1',
                      product.trend === 'up' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                    ]"
                  />
                  <span
                    :class="[
                      'text-xs font-medium',
                      product.trend === 'up' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                    ]"
                  >
                    {{ product.change }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <button class="flex items-center justify-center px-4 py-3 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors cursor-pointer">
          <Package class="w-5 h-5 mr-2" />
          <span class="font-medium">Add Product</span>
        </button>
        <button class="flex items-center justify-center px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors cursor-pointer">
          <ShoppingCart class="w-5 h-5 mr-2" />
          <span class="font-medium">View Orders</span>
        </button>
        <button class="flex items-center justify-center px-4 py-3 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/40 transition-colors cursor-pointer">
          <Users class="w-5 h-5 mr-2" />
          <span class="font-medium">Manage Customers</span>
        </button>
        <button class="flex items-center justify-center px-4 py-3 bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors cursor-pointer">
          <DollarSign class="w-5 h-5 mr-2" />
          <span class="font-medium">Sales Report</span>
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

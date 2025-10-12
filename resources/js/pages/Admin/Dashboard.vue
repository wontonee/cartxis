<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  TrendingUp,
  TrendingDown,
  ShoppingCart,
  DollarSign,
  Users,
  Package,
  ArrowUpRight,
  ArrowDownRight,
  MoreVertical
} from 'lucide-vue-next'

defineProps<{
  auth?: {
    user: {
      name: string
      email: string
    }
  }
}>()

// Mock data - Replace with real API data later
const stats = [
  {
    title: 'Total Revenue',
    value: '$45,231.89',
    change: '+20.1%',
    trend: 'up',
    icon: DollarSign,
    color: 'blue'
  },
  {
    title: 'Orders',
    value: '2,350',
    change: '+15.3%',
    trend: 'up',
    icon: ShoppingCart,
    color: 'green'
  },
  {
    title: 'Customers',
    value: '1,234',
    change: '+8.2%',
    trend: 'up',
    icon: Users,
    color: 'purple'
  },
  {
    title: 'Products',
    value: '543',
    change: '-2.4%',
    trend: 'down',
    icon: Package,
    color: 'orange'
  }
]

const recentOrders = [
  { id: '#ORD-001', customer: 'John Doe', product: 'Laptop Pro 15"', amount: '$2,399.00', status: 'completed', date: '2 min ago' },
  { id: '#ORD-002', customer: 'Jane Smith', product: 'Wireless Mouse', amount: '$49.99', status: 'processing', date: '15 min ago' },
  { id: '#ORD-003', customer: 'Bob Johnson', product: 'USB-C Hub', amount: '$79.99', status: 'completed', date: '1 hour ago' },
  { id: '#ORD-004', customer: 'Alice Brown', product: 'Mechanical Keyboard', amount: '$159.99', status: 'pending', date: '2 hours ago' },
  { id: '#ORD-005', customer: 'Charlie Wilson', product: 'Monitor 27"', amount: '$399.99', status: 'completed', date: '3 hours ago' }
]

const topProducts = [
  { name: 'Laptop Pro 15"', sales: 156, revenue: '$374,244', trend: 'up', change: '+12%' },
  { name: 'Wireless Mouse', sales: 432, revenue: '$21,595', trend: 'up', change: '+8%' },
  { name: 'USB-C Hub', sales: 234, revenue: '$18,717', trend: 'down', change: '-3%' },
  { name: 'Mechanical Keyboard', sales: 189, revenue: '$30,238', trend: 'up', change: '+15%' },
  { name: 'Monitor 27"', sales: 98, revenue: '$39,199', trend: 'up', change: '+5%' }
]
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
        v-for="stat in stats"
        :key="stat.title"
        class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition-shadow"
      >
        <div class="flex items-center justify-between mb-4">
          <div
            :class="[
              'w-12 h-12 rounded-lg flex items-center justify-center',
              stat.color === 'blue' && 'bg-blue-100 text-blue-600',
              stat.color === 'green' && 'bg-green-100 text-green-600',
              stat.color === 'purple' && 'bg-purple-100 text-purple-600',
              stat.color === 'orange' && 'bg-orange-100 text-orange-600'
            ]"
          >
            <component :is="stat.icon" class="w-6 h-6" />
          </div>
          <div
            :class="[
              'flex items-center text-sm font-semibold',
              stat.trend === 'up' ? 'text-green-600' : 'text-red-600'
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

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      <!-- Recent Orders - Takes 2 columns -->
      <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
            <button class="text-sm text-blue-600 hover:text-blue-700 font-medium cursor-pointer transition-colors">View All</button>
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
                v-for="order in recentOrders"
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
                      order.status === 'completed' && 'bg-green-100 text-green-800',
                      order.status === 'processing' && 'bg-blue-100 text-blue-800',
                      order.status === 'pending' && 'bg-yellow-100 text-yellow-800'
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
          <div class="space-y-4">
            <div
              v-for="(product, index) in topProducts"
              :key="product.name"
              class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0 w-8 h-8 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                  <span class="text-sm font-bold text-gray-600 dark:text-gray-300">{{ index + 1 }}</span>
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
                      product.trend === 'up' ? 'text-green-600' : 'text-red-600'
                    ]"
                  />
                  <span
                    :class="[
                      'text-xs font-medium',
                      product.trend === 'up' ? 'text-green-600' : 'text-red-600'
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

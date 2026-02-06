<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { useCurrency } from '@/composables/useCurrency'
import {
  TrendingUp,
  TrendingDown,
  ShoppingCart,
  DollarSign,
  CreditCard,
  Users,
  Package,
  ArrowUpRight,
  ArrowDownRight,
  MoreVertical,
  Activity,
  TrendingUpIcon,
  Plus,
  FileText,
  BarChart3,
  Settings,
  Tag,
  Megaphone,
  Calendar,
  ChevronRight
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

const { formatPrice } = useCurrency()

// Icon mapping
const iconMap = {
  DollarSign,
  CreditCard,
  ShoppingCart,
  Users,
  Package
}

const normalizedSales = computed(() => {
  return (props.salesChart?.data || []).map((value) => {
    const numeric = Number(value)
    return Number.isFinite(numeric) ? numeric : 0
  })
})

// Compute max value for chart scaling
const maxSales = computed(() => {
  if (!normalizedSales.value.length) return 1000
  return Math.max(...normalizedSales.value, 1) * 1.2 // Add 20% padding
})

// Compute chart bars
const chartBars = computed(() => {
  if (!props.salesChart?.labels?.length) return []
  return normalizedSales.value.map((value, index) => ({
    label: props.salesChart?.labels?.[index] || '',
    value: value,
    height: maxSales.value > 0 ? (value / maxSales.value) * 100 : 0
  }))
})
</script>

<template>
  <Head title="Admin Dashboard" />

  <AdminLayout title="Dashboard">
    <!-- Welcome Banner -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-indigo-600 to-violet-700 dark:from-blue-700 dark:via-indigo-800 dark:to-violet-900 rounded-2xl p-8 mb-8 text-white">
      <!-- Decorative elements -->
      <div class="absolute top-0 right-0 w-80 h-80 bg-white/5 rounded-full blur-3xl -translate-y-1/3 translate-x-1/4"></div>
      <div class="absolute bottom-0 left-0 w-60 h-60 bg-indigo-400/10 rounded-full blur-3xl translate-y-1/3 -translate-x-1/4"></div>
      <div class="absolute top-1/2 left-1/2 w-40 h-40 bg-violet-300/5 rounded-full blur-2xl"></div>
      
      <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
          <h2 class="text-2xl md:text-3xl font-bold mb-1.5 tracking-tight">Welcome back, {{ auth?.user?.name || 'Admin' }}!</h2>
          <p class="text-blue-100/80 text-sm md:text-base">Here's what's happening with your store today.</p>
        </div>
        <div class="hidden md:block">
          <div class="flex items-center gap-3 bg-white/10 backdrop-blur-xl border border-white/15 rounded-xl px-4 py-3">
            <div class="w-9 h-9 bg-white/10 rounded-lg flex items-center justify-center">
              <Calendar class="w-4 h-4 text-blue-200" />
            </div>
            <div>
              <div class="text-[10px] text-blue-200/70 font-semibold uppercase tracking-widest">Today</div>
              <div class="text-sm font-semibold">{{ new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 lg:gap-5 mb-8">
      <div
        v-for="stat in (props.stats || [])"
        :key="stat.title"
        class="group bg-white dark:bg-gray-800/80 rounded-xl hover:shadow-md transition-all duration-300 p-5 border border-gray-100 dark:border-gray-700/60 relative overflow-hidden"
      >
        <!-- Subtle background accent -->
        <div 
          class="absolute -top-6 -right-6 w-20 h-20 rounded-full opacity-[0.04] group-hover:opacity-[0.07] transition-opacity duration-500"
          :class="{
            'bg-blue-500': stat.color === 'blue',
            'bg-green-500': stat.color === 'green',
            'bg-purple-500': stat.color === 'purple',
            'bg-orange-500': stat.color === 'orange'
          }"
        ></div>
        
        <div class="flex items-start justify-between mb-3 relative z-10">
          <div
            :class="[
              'w-10 h-10 rounded-lg flex items-center justify-center',
              stat.color === 'blue' && 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
              stat.color === 'green' && 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400',
              stat.color === 'purple' && 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400',
              stat.color === 'orange' && 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400'
            ]"
          >
            <component :is="iconMap[stat.icon]" class="w-5 h-5" />
          </div>
          <div
            :class="[
              'flex items-center text-[11px] font-semibold px-2 py-0.5 rounded-full',
              stat.trend === 'up' 
                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' 
                : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400'
            ]"
          >
            <component :is="stat.trend === 'up' ? ArrowUpRight : ArrowDownRight" class="w-3 h-3 mr-0.5" />
            {{ stat.change }}
          </div>
        </div>
        <div class="relative z-10">
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ stat.title }}</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <!-- Sales Chart -->
    <div class="bg-white dark:bg-gray-800/80 rounded-xl border border-gray-100 dark:border-gray-700/60 p-6 mb-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-base font-semibold text-gray-900 dark:text-white">Sales Overview</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Revenue performance over the last 7 days</p>
        </div>
        <div class="flex items-center gap-1.5 bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 px-2.5 py-1.5 rounded-lg border border-blue-100 dark:border-blue-500/20">
          <Activity class="w-3.5 h-3.5" />
          <span class="text-xs font-semibold">Daily Sales</span>
        </div>
      </div>
      
      <div v-if="chartBars.length > 0" class="relative h-64">
        <!-- Grid lines -->
        <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
          <div class="border-b border-gray-100 dark:border-gray-700 w-full h-full"></div>
          <div class="border-b border-gray-100 dark:border-gray-700 w-full h-full"></div>
          <div class="border-b border-gray-100 dark:border-gray-700 w-full h-full"></div>
          <div class="border-b border-gray-100 dark:border-gray-700 w-full h-full"></div>
        </div>

        <div class="flex items-end justify-between space-x-4 h-full relative z-10 px-2">
          <div
            v-for="(bar, index) in chartBars"
            :key="index"
            class="flex-1 flex flex-col items-center group h-full"
          >
            <div class="w-full flex items-end justify-center flex-1 pb-6">
              <div
                class="w-full max-w-[60px] bg-gradient-to-t from-blue-500 to-indigo-500 dark:from-blue-600 dark:to-indigo-600 rounded-t-lg transition-all duration-300 hover:from-blue-600 hover:to-indigo-600 relative"
                :style="{ height: bar.value > 0 ? `${Math.max(bar.height, 4)}%` : '0%', opacity: bar.value > 0 ? 1 : 0 }"
              >
                <!-- Tooltip -->
                <div class="absolute -top-12 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs font-bold px-3 py-1.5 rounded shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none whitespace-nowrap z-20">
                  {{ formatPrice(bar.value, { decimals: 2 }) }}
                  <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-1 border-4 border-transparent border-t-gray-900"></div>
                </div>
              </div>
            </div>
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 absolute bottom-0">{{ bar.label }}</div>
          </div>
        </div>
      </div>
      
      <div v-else class="h-64 flex flex-col items-center justify-center text-gray-400 bg-gray-50 dark:bg-gray-800/50 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-700">
        <BarChart3 class="w-10 h-10 mb-2 opacity-20" />
        <span class="text-sm">No sales data available for this period</span>
      </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">
      <!-- Recent Orders - Takes 2 columns -->
      <div class="lg:col-span-2 bg-white dark:bg-gray-800/80 rounded-xl border border-gray-100 dark:border-gray-700/60 flex flex-col">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
          <Link 
            href="/admin/sales/orders" 
            class="group flex items-center text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
          >
            View All
            <ChevronRight class="w-3.5 h-3.5 ml-0.5 transform group-hover:translate-x-0.5 transition-transform" />
          </Link>
        </div>
        <div class="overflow-x-auto flex-1">
          <table class="w-full">
            <thead class="bg-gray-50/50 dark:bg-gray-800/50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order Items</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr
                v-if="!props.recentOrders || props.recentOrders.length === 0"
              >
                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 flex flex-col items-center justify-center">
                  <Package class="w-12 h-12 text-gray-300 mb-2" />
                  <p>No recent orders found</p>
                </td>
              </tr>
              <tr
                v-for="order in (props.recentOrders || [])"
                :key="order.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group"
              >
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-blue-600 transition-colors">{{ order.id }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ order.product }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-300 mr-3">
                      {{ order.customer.charAt(0).toUpperCase() }}
                    </div>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ order.customer }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-bold text-gray-900 dark:text-white">{{ order.amount }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border',
                      order.status === 'completed' && 'bg-green-50 text-green-700 border-green-100 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800',
                      order.status === 'processing' && 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800',
                      order.status === 'pending' && 'bg-yellow-50 text-yellow-700 border-yellow-100 dark:bg-yellow-900/20 dark:text-yellow-400 dark:border-yellow-800',
                      order.status === 'cancelled' && 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800'
                    ]"
                  >
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="{
                      'bg-green-500': order.status === 'completed',
                      'bg-blue-500': order.status === 'processing',
                      'bg-yellow-500': order.status === 'pending',
                      'bg-red-500': order.status === 'cancelled'
                    }"></span>
                    {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <span class="text-sm text-gray-500 dark:text-gray-400">{{ order.date }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Top Products - Takes 1 column -->
      <div class="bg-white dark:bg-gray-800/80 rounded-xl border border-gray-100 dark:border-gray-700/60">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/60">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Products</h3>
        </div>
        <div class="p-5">
          <div v-if="!props.topProducts || props.topProducts.length === 0" class="flex flex-col items-center justify-center py-10 text-gray-500 dark:text-gray-400">
            <Tag class="w-10 h-10 mb-2 opacity-20" />
            <p class="text-sm">No products data available</p>
          </div>
          <div v-else class="space-y-4">
            <div
              v-for="(product, index) in (props.topProducts || [])"
              :key="product.name"
              class="group flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200 border border-transparent hover:border-gray-100 dark:hover:border-gray-700"
            >
              <div class="flex items-center space-x-3">
                <div 
                  class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center shadow-sm text-white font-bold"
                  :class="[
                    index === 0 ? 'bg-gradient-to-br from-yellow-400 to-yellow-600' :
                    index === 1 ? 'bg-gradient-to-br from-gray-300 to-gray-500' :
                    index === 2 ? 'bg-gradient-to-br from-orange-400 to-orange-600' :
                    'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'
                  ]"
                >
                  {{ index + 1 }}
                </div>
                <div>
                  <p class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-1 group-hover:text-blue-600 transition-colors">{{ product.name }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ product.sales }} unit{{ product.sales !== 1 ? 's' : '' }} sold</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ product.revenue }}</p>
                <div class="flex items-center justify-end mt-0.5">
                  <component
                    :is="product.trend === 'up' ? TrendingUp : TrendingDown"
                    :class="[
                      'w-3 h-3 mr-1',
                      product.trend === 'up' ? 'text-green-500' : 'text-red-500'
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
    <div class="bg-white dark:bg-gray-800/80 rounded-xl border border-gray-100 dark:border-gray-700/60 p-6">
      <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-5">Quick Actions</h3>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <Link 
          href="/admin/catalog/products/create" 
          class="group flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 dark:border-gray-700/50 hover:border-blue-200 dark:hover:border-blue-500/30 bg-gray-50/50 dark:bg-gray-800/50 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all duration-200"
        >
          <div class="w-10 h-10 bg-blue-100 dark:bg-blue-500/10 rounded-lg flex items-center justify-center mb-2.5 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
            <Plus class="w-5 h-5" />
          </div>
          <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Add Product</span>
        </Link>

        <Link 
          href="/admin/sales/orders" 
          class="group flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 dark:border-gray-700/50 hover:border-emerald-200 dark:hover:border-emerald-500/30 bg-gray-50/50 dark:bg-gray-800/50 hover:bg-emerald-50 dark:hover:bg-emerald-500/5 transition-all duration-200"
        >
          <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-500/10 rounded-lg flex items-center justify-center mb-2.5 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
            <ShoppingCart class="w-5 h-5" />
          </div>
          <span class="text-xs font-medium text-gray-700 dark:text-gray-300">View Orders</span>
        </Link>

        <Link 
          href="/admin/customers" 
          class="group flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 dark:border-gray-700/50 hover:border-purple-200 dark:hover:border-purple-500/30 bg-gray-50/50 dark:bg-gray-800/50 hover:bg-purple-50 dark:hover:bg-purple-500/5 transition-all duration-200"
        >
          <div class="w-10 h-10 bg-purple-100 dark:bg-purple-500/10 rounded-lg flex items-center justify-center mb-2.5 text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform">
            <Users class="w-5 h-5" />
          </div>
          <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Customers</span>
        </Link>

        <Link 
          href="/admin/reports/sales" 
          class="group flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 dark:border-gray-700/50 hover:border-orange-200 dark:hover:border-orange-500/30 bg-gray-50/50 dark:bg-gray-800/50 hover:bg-orange-50 dark:hover:bg-orange-500/5 transition-all duration-200"
        >
          <div class="w-10 h-10 bg-orange-100 dark:bg-orange-500/10 rounded-lg flex items-center justify-center mb-2.5 text-orange-600 dark:text-orange-400 group-hover:scale-110 transition-transform">
            <BarChart3 class="w-5 h-5" />
          </div>
          <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Sales Report</span>
        </Link>

        <Link 
          href="/admin/marketing/promotions" 
          class="group flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 dark:border-gray-700/50 hover:border-pink-200 dark:hover:border-pink-500/30 bg-gray-50/50 dark:bg-gray-800/50 hover:bg-pink-50 dark:hover:bg-pink-500/5 transition-all duration-200"
        >
          <div class="w-10 h-10 bg-pink-100 dark:bg-pink-500/10 rounded-lg flex items-center justify-center mb-2.5 text-pink-600 dark:text-pink-400 group-hover:scale-110 transition-transform">
            <Tag class="w-5 h-5" />
          </div>
          <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Promotions</span>
        </Link>

        <Link 
          href="/admin/settings/general" 
          class="group flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 dark:border-gray-700/50 hover:border-gray-200 dark:hover:border-gray-600 bg-gray-50/50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/30 transition-all duration-200"
        >
          <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700/50 rounded-lg flex items-center justify-center mb-2.5 text-gray-600 dark:text-gray-400 group-hover:scale-110 transition-transform">
            <Settings class="w-5 h-5" />
          </div>
          <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Settings</span>
        </Link>
      </div>
    </div>
  </AdminLayout>
</template>

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
  Activity,
  Plus,
  BarChart3,
  Settings,
  Megaphone,
  ChevronRight,
  UserPlus,
  AlertTriangle,
  Zap,
} from 'lucide-vue-next'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Filler,
  Tooltip,
  Legend,
} from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend)

// ─── Types ────────────────────────────────────────────────────────────────────

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
  orders: number[]
}

interface ActivityItem {
  type: string
  message: string
  time: string
  icon: string
  color: string
}

interface LowStockItem {
  name: string
  sku: string
  stock_status: string
  notify_qty: number
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
  auth?: { user: { name: string; email: string } }
  stats?: StatItem[]
  recentOrders?: OrderItem[]
  topProducts?: ProductItem[]
  salesChart?: SalesChartData
  activityFeed?: ActivityItem[]
  lowStockProducts?: LowStockItem[]
  currencySymbol?: string
}>()

const { formatPrice, getSymbol } = useCurrency()

// ─── Dark-mode detection (reactive) ──────────────────────────────────────────

const isDark = ref(false)

const syncDark = () => {
  isDark.value = document.documentElement.classList.contains('dark')
}

let observer: MutationObserver | null = null

onMounted(() => {
  syncDark()
  observer = new MutationObserver(syncDark)
  observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] })
})

onUnmounted(() => observer?.disconnect())

// ─── Icon maps ─────────────────────────────────────────────────────────────────

const statIconMap: Record<string, unknown> = { DollarSign, CreditCard, ShoppingCart, Users, Package }
const activityIconMap: Record<string, unknown> = { ShoppingCart, UserPlus, Package }

// ─── Chart: grid / tick colours that switch with theme ───────────────────────

const chartGridColor  = computed(() => isDark.value ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.06)')
const chartTickColor  = computed(() => isDark.value ? '#64748b' : '#94a3b8')
const chartTooltipBg  = computed(() => isDark.value ? 'rgba(15,20,40,0.95)' : 'rgba(255,255,255,0.97)')
const chartTooltipTitle = computed(() => isDark.value ? '#94a3b8' : '#64748b')
const chartTooltipBody = computed(() => isDark.value ? '#fff' : '#0f172a')
const chartTooltipBorder = computed(() => isDark.value ? 'rgba(99,102,241,0.3)' : 'rgba(99,102,241,0.2)')

// ─── Chart: 7-day Revenue Area ────────────────────────────────────────────────

const salesChartData = computed(() => ({
  labels: props.salesChart?.labels ?? [],
  datasets: [{
    label: 'Revenue',
    data: props.salesChart?.data ?? [],
    fill: true,
    backgroundColor: isDark.value ? 'rgba(99,102,241,0.15)' : 'rgba(99,102,241,0.08)',
    borderColor: '#6366f1',
    borderWidth: 2,
    tension: 0.45,
    pointRadius: 3,
    pointBackgroundColor: '#6366f1',
    pointBorderColor: isDark.value ? '#080d1a' : '#fff',
    pointBorderWidth: 2,
  }],
}))

const salesChartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: chartTooltipBg.value,
      titleColor: chartTooltipTitle.value,
      bodyColor: chartTooltipBody.value,
      borderColor: chartTooltipBorder.value,
      borderWidth: 1,
      padding: 10,
      callbacks: { label: (ctx: any) => ' ' + formatPrice(ctx.raw, { decimals: 2 }) },
    },
  },
  scales: {
    x: {
      grid: { color: chartGridColor.value },
      ticks: { color: chartTickColor.value, font: { size: 11 } },
      border: { color: 'transparent' },
    },
    y: {
      grid: { color: chartGridColor.value },
      ticks: { color: chartTickColor.value, font: { size: 11 } },
      border: { color: 'transparent' },
    },
  },
}))

// ─── Chart: dual-axis Revenue vs Orders ───────────────────────────────────────

const dualChartData = computed(() => ({
  labels: props.salesChart?.labels ?? [],
  datasets: [
    {
      label: 'Revenue',
      data: props.salesChart?.data ?? [],
      fill: false,
      borderColor: '#6366f1',
      borderWidth: 2,
      tension: 0.45,
      pointRadius: 3,
      pointBackgroundColor: '#6366f1',
      pointBorderColor: isDark.value ? '#080d1a' : '#fff',
      pointBorderWidth: 2,
      yAxisID: 'yRevenue',
    },
    {
      label: 'Orders',
      data: props.salesChart?.orders ?? [],
      fill: false,
      borderColor: '#22d3ee',
      borderWidth: 2,
      tension: 0.45,
      pointRadius: 3,
      pointBackgroundColor: '#22d3ee',
      pointBorderColor: isDark.value ? '#080d1a' : '#fff',
      pointBorderWidth: 2,
      yAxisID: 'yOrders',
    },
  ],
}))

const dualChartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  interaction: { mode: 'index' as const, intersect: false },
  plugins: {
    legend: {
      display: true,
      labels: { color: chartTickColor.value, boxWidth: 12, font: { size: 11 } },
    },
    tooltip: {
      backgroundColor: chartTooltipBg.value,
      titleColor: chartTooltipTitle.value,
      bodyColor: chartTooltipBody.value,
      borderColor: chartTooltipBorder.value,
      borderWidth: 1,
      padding: 10,
    },
  },
  scales: {
    x: {
      grid: { color: chartGridColor.value },
      ticks: { color: chartTickColor.value, font: { size: 11 } },
      border: { color: 'transparent' },
    },
    yRevenue: {
      type: 'linear' as const,
      position: 'left' as const,
      grid: { color: chartGridColor.value },
      ticks: { color: '#818cf8', font: { size: 11 } },
      border: { color: 'transparent' },
    },
    yOrders: {
      type: 'linear' as const,
      position: 'right' as const,
      grid: { drawOnChartArea: false },
      ticks: { color: '#22d3ee', font: { size: 11 } },
      border: { color: 'transparent' },
    },
  },
}))

// ─── Colour helpers ───────────────────────────────────────────────────────────

const statGlow: Record<string, string> = {
  blue:   'dark:shadow-[0_0_24px_rgba(99,102,241,0.15)]   border-t-indigo-500',
  green:  'dark:shadow-[0_0_24px_rgba(16,185,129,0.12)]   border-t-emerald-500',
  purple: 'dark:shadow-[0_0_24px_rgba(168,85,247,0.12)]   border-t-purple-500',
  orange: 'dark:shadow-[0_0_24px_rgba(249,115,22,0.12)]   border-t-orange-500',
}
const statIconBg: Record<string, string> = {
  blue:   'bg-indigo-50  text-indigo-600  dark:bg-indigo-500/10  dark:text-indigo-400',
  green:  'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
  purple: 'bg-purple-50  text-purple-600  dark:bg-purple-500/10  dark:text-purple-400',
  orange: 'bg-orange-50  text-orange-600  dark:bg-orange-500/10  dark:text-orange-400',
}
const activityDot: Record<string, string> = {
  blue:   'bg-indigo-500',
  green:  'bg-emerald-500',
  red:    'bg-red-500',
  purple: 'bg-purple-500',
  orange: 'bg-orange-500',
}
const activityIconBg: Record<string, string> = {
  blue:   'bg-indigo-50  text-indigo-600  dark:bg-indigo-500/15  dark:text-indigo-400',
  green:  'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400',
  red:    'bg-red-50     text-red-600     dark:bg-red-500/15     dark:text-red-400',
  purple: 'bg-purple-50  text-purple-600  dark:bg-purple-500/15  dark:text-purple-400',
  orange: 'bg-orange-50  text-orange-600  dark:bg-orange-500/15  dark:text-orange-400',
}

const maxProductSales = computed(() =>
  Math.max(...(props.topProducts ?? []).map((p) => p.sales), 1)
)
</script>

<template>
  <Head title="Admin Dashboard" />
  <AdminLayout title="Dashboard">

    <!--
      Light: clean white/gray card surfaces
      Dark:  deep navy + frosted glass panels + glow orbs
    -->
    <div class="relative min-h-screen -mx-6 -mt-6 px-6 pt-6 pb-10
                bg-gray-50 dark:bg-[#080d1a] overflow-hidden transition-colors duration-300">

      <!-- Ambient glow orbs (dark only) -->
      <div class="pointer-events-none absolute inset-0 overflow-hidden dark:block hidden">
        <div class="absolute -top-40 -left-40 w-[480px] h-[480px] rounded-full bg-indigo-600/10 blur-[120px]"></div>
        <div class="absolute top-60 right-0 w-[360px] h-[360px] rounded-full bg-cyan-500/[0.08] blur-[100px]"></div>
        <div class="absolute bottom-0 left-1/3 w-[400px] h-[300px] rounded-full bg-violet-600/[0.08] blur-[120px]"></div>
      </div>

      <div class="relative z-10 space-y-5">

        <!-- ── Welcome Banner ─────────────────────────────────────────────── -->
        <div class="rounded-2xl px-6 py-4 flex items-center justify-between
                    bg-white border border-gray-200 shadow-sm
                    dark:bg-white/[0.04] dark:backdrop-blur-xl dark:border-white/[0.08] dark:shadow-none">
          <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white tracking-tight">
              Welcome back,
              <span class="text-indigo-600 dark:text-indigo-400">{{ auth?.user?.name || 'Admin' }}</span>
            </h2>
            <p class="text-xs text-gray-500 dark:text-slate-500 mt-0.5">Here's what's happening with your store today.</p>
          </div>
          <div class="hidden md:flex items-center gap-2
                      bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5
                      dark:bg-white/[0.05] dark:border-white/[0.07]">
            <Zap class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400" />
            <span class="text-xs font-medium text-gray-700 dark:text-slate-300">
              {{ new Date().toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) }}
            </span>
          </div>
        </div>

        <!-- ── Stat Cards ─────────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
          <div
            v-for="stat in (props.stats ?? [])"
            :key="stat.title"
            class="border-t-2 rounded-2xl p-5 transition-all duration-300
                   bg-white border border-gray-200 shadow-sm hover:shadow-md
                   dark:bg-white/[0.04] dark:backdrop-blur-xl dark:border-white/[0.08] dark:hover:bg-white/[0.07]"
            :class="statGlow[stat.color]"
          >
            <div class="flex items-start justify-between mb-4">
              <div class="w-9 h-9 rounded-xl flex items-center justify-center" :class="statIconBg[stat.color]">
                <span v-if="stat.icon === 'DollarSign'" class="text-sm font-bold leading-none">{{ getSymbol() }}</span>
                <component v-else :is="statIconMap[stat.icon]" class="w-4 h-4" />
              </div>
              <div
                class="flex items-center text-[11px] font-semibold px-2 py-0.5 rounded-full"
                :class="stat.trend === 'up'
                  ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400'
                  : 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400'"
              >
                <component :is="stat.trend === 'up' ? ArrowUpRight : ArrowDownRight" class="w-3 h-3 mr-0.5" />
                {{ stat.change }}
              </div>
            </div>
            <p class="text-[11px] font-medium text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">{{ stat.title }}</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">{{ stat.value }}</p>
          </div>
        </div>

        <!-- ── Charts Row ──────────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

          <!-- 7-day Revenue Area Chart -->
          <div class="rounded-2xl p-5
                      bg-white border border-gray-200 shadow-sm
                      dark:bg-white/[0.04] dark:backdrop-blur-xl dark:border-white/[0.08] dark:shadow-none">
            <div class="flex items-center justify-between mb-5">
              <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">7-Day Revenue</h3>
                <p class="text-xs text-gray-500 dark:text-slate-500 mt-0.5">Daily revenue over the past week</p>
              </div>
              <div class="flex items-center gap-1.5 bg-indigo-50 border border-indigo-100 text-indigo-600
                          dark:bg-indigo-500/10 dark:border-indigo-500/20 dark:text-indigo-400
                          px-2.5 py-1.5 rounded-lg">
                <Activity class="w-3.5 h-3.5" />
                <span class="text-[11px] font-semibold">Revenue</span>
              </div>
            </div>
            <div class="h-52">
              <Line
                v-if="(props.salesChart?.labels?.length ?? 0) > 0"
                :data="salesChartData"
                :options="salesChartOptions"
              />
              <div v-else class="h-full flex flex-col items-center justify-center
                                 text-gray-400 dark:text-slate-600
                                 border-2 border-dashed border-gray-200 dark:border-white/[0.06] rounded-xl">
                <BarChart3 class="w-8 h-8 mb-2 opacity-30" />
                <span class="text-xs">No data yet</span>
              </div>
            </div>
          </div>

          <!-- Dual-Axis Revenue vs Orders -->
          <div class="rounded-2xl p-5
                      bg-white border border-gray-200 shadow-sm
                      dark:bg-white/[0.04] dark:backdrop-blur-xl dark:border-white/[0.08] dark:shadow-none">
            <div class="flex items-center justify-between mb-5">
              <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Revenue vs Orders</h3>
                <p class="text-xs text-gray-500 dark:text-slate-500 mt-0.5">Dual-axis comparison this week</p>
              </div>
              <div class="flex items-center gap-3 text-[11px] font-medium">
                <span class="flex items-center gap-1 text-indigo-600 dark:text-indigo-400">
                  <span class="w-2 h-2 rounded-full bg-indigo-500 inline-block"></span>Revenue
                </span>
                <span class="flex items-center gap-1 text-cyan-600 dark:text-cyan-400">
                  <span class="w-2 h-2 rounded-full bg-cyan-400 inline-block"></span>Orders
                </span>
              </div>
            </div>
            <div class="h-52">
              <Line
                v-if="(props.salesChart?.labels?.length ?? 0) > 0"
                :data="dualChartData"
                :options="dualChartOptions"
              />
              <div v-else class="h-full flex flex-col items-center justify-center
                                 text-gray-400 dark:text-slate-600
                                 border-2 border-dashed border-gray-200 dark:border-white/[0.06] rounded-xl">
                <BarChart3 class="w-8 h-8 mb-2 opacity-30" />
                <span class="text-xs">No data yet</span>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Recent Orders + Top Products ──────────────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

          <!-- Recent Orders (2-col) -->
          <div class="lg:col-span-2 rounded-2xl overflow-hidden
                      bg-white border border-gray-200 shadow-sm
                      dark:bg-white/[0.04] dark:backdrop-blur-xl dark:border-white/[0.08] dark:shadow-none">
            <div class="px-5 py-4 flex items-center justify-between
                        border-b border-gray-100 dark:border-white/[0.06]">
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
              <Link href="/admin/sales/orders"
                class="group flex items-center text-xs font-medium text-indigo-600 hover:text-indigo-700
                       dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                View All
                <ChevronRight class="w-3.5 h-3.5 ml-0.5 group-hover:translate-x-0.5 transition-transform" />
              </Link>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b border-gray-50 dark:border-white/[0.04]">
                    <th class="px-5 py-3 text-left text-[10px] font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Order</th>
                    <th class="px-5 py-3 text-left text-[10px] font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Customer</th>
                    <th class="px-5 py-3 text-left text-[10px] font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Amount</th>
                    <th class="px-5 py-3 text-left text-[10px] font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-right text-[10px] font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Time</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!props.recentOrders?.length">
                    <td colspan="5" class="px-5 py-12 text-center text-gray-400 dark:text-slate-600">
                      <Package class="w-10 h-10 mx-auto mb-2 opacity-20" />
                      <p class="text-xs">No recent orders</p>
                    </td>
                  </tr>
                  <tr
                    v-for="order in (props.recentOrders ?? [])"
                    :key="order.id"
                    class="border-b border-gray-50 dark:border-white/[0.03]
                           hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors"
                  >
                    <td class="px-5 py-3.5">
                      <p class="text-sm font-bold text-gray-900 dark:text-white">{{ order.id }}</p>
                      <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 max-w-[160px] truncate">{{ order.product }}</p>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                      <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                                    bg-indigo-100 text-indigo-700
                                    dark:bg-gradient-to-br dark:from-indigo-500/30 dark:to-violet-500/30 dark:border dark:border-white/10 dark:text-slate-300">
                          {{ order.customer.charAt(0).toUpperCase() }}
                        </div>
                        <span class="text-sm text-gray-700 dark:text-slate-300">{{ order.customer }}</span>
                      </div>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                      <span class="text-sm font-bold text-gray-900 dark:text-white">{{ order.amount }}</span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                      <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold"
                        :class="{
                          'bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20': order.status === 'completed',
                          'bg-indigo-50 text-indigo-700 border border-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20': order.status === 'processing',
                          'bg-yellow-50 text-yellow-700 border border-yellow-100 dark:bg-yellow-500/10 dark:text-yellow-400 dark:border-yellow-500/20': order.status === 'pending',
                          'bg-red-50 text-red-700 border border-red-100 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20': order.status === 'cancelled',
                        }"
                      >
                        <span class="w-1.5 h-1.5 rounded-full" :class="{
                          'bg-emerald-500': order.status === 'completed',
                          'bg-indigo-500': order.status === 'processing',
                          'bg-yellow-500': order.status === 'pending',
                          'bg-red-500': order.status === 'cancelled',
                        }"></span>
                        {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                      </span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                      <span class="text-xs text-gray-400 dark:text-slate-500">{{ order.date }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Top Products with progress bars (1-col) -->
          <div class="rounded-2xl overflow-hidden
                      bg-white border border-gray-200 shadow-sm
                      dark:bg-white/[0.04] dark:backdrop-blur-xl dark:border-white/[0.08] dark:shadow-none">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-white/[0.06]">
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Products</h3>
            </div>
            <div class="p-5">
              <div v-if="!props.topProducts?.length" class="flex flex-col items-center justify-center py-10 text-gray-400 dark:text-slate-600">
                <Package class="w-8 h-8 mb-2 opacity-20" />
                <p class="text-xs">No data</p>
              </div>
              <div v-else class="space-y-4">
                <div v-for="(product, index) in (props.topProducts ?? [])" :key="product.name">
                  <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                      <span
                        class="w-5 h-5 rounded-md text-[10px] font-bold flex items-center justify-center shrink-0"
                        :class="[
                          index === 0 ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400' :
                          index === 1 ? 'bg-gray-100  text-gray-600  dark:bg-slate-500/20  dark:text-slate-400' :
                          index === 2 ? 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400' :
                                        'bg-gray-50   text-gray-500  dark:bg-white/5        dark:text-slate-500'
                        ]"
                      >{{ index + 1 }}</span>
                      <span class="text-sm font-medium text-gray-800 dark:text-slate-200 truncate max-w-[120px]">{{ product.name }}</span>
                    </div>
                    <span class="text-xs font-bold text-gray-900 dark:text-white shrink-0">{{ product.revenue }}</span>
                  </div>
                  <div class="h-1.5 bg-gray-100 dark:bg-white/[0.06] rounded-full overflow-hidden">
                    <div
                      class="h-full rounded-full transition-all duration-700"
                      :class="[
                        index === 0 ? 'bg-gradient-to-r from-indigo-500 to-violet-500' :
                        index === 1 ? 'bg-gradient-to-r from-cyan-500 to-blue-500' :
                        index === 2 ? 'bg-gradient-to-r from-emerald-500 to-teal-500' :
                                      'bg-gray-300 dark:bg-white/20'
                      ]"
                      :style="{ width: `${Math.round((product.sales / maxProductSales) * 100)}%` }"
                    ></div>
                  </div>
                  <div class="flex items-center justify-between mt-1">
                    <span class="text-[10px] text-gray-400 dark:text-slate-600">{{ product.sales }} sold</span>
                    <div class="flex items-center gap-1">
                      <component
                        :is="product.trend === 'up' ? TrendingUp : TrendingDown"
                        class="w-2.5 h-2.5"
                        :class="product.trend === 'up' ? 'text-emerald-500' : 'text-red-500'"
                      />
                      <span class="text-[10px] font-medium" :class="product.trend === 'up' ? 'text-emerald-600 dark:text-emerald-500' : 'text-red-600 dark:text-red-500'">
                        {{ product.change }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!--  Activity Feed + Stock Alerts  -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

          <!-- Live Activity Feed -->
          <div class="rounded-2xl overflow-hidden
                      bg-white border border-gray-200 shadow-sm
                      dark:bg-white/[0.04] dark:backdrop-blur-xl dark:border-white/[0.08] dark:shadow-none">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-white/[0.06] flex items-center gap-2">
              <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Live Activity</h3>
            </div>
            <div class="p-5">
              <div v-if="!props.activityFeed?.length" class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-slate-600">
                <Activity class="w-8 h-8 mb-2 opacity-20" />
                <p class="text-xs">No recent activity</p>
              </div>
              <div v-else class="space-y-3">
                <div v-for="(event, i) in (props.activityFeed ?? [])" :key="i" class="flex items-start gap-3">
                  <div
                    class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                    :class="activityIconBg[event.color] ?? 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-slate-400'"
                  >
                    <component :is="activityIconMap[event.icon] ?? Package" class="w-3.5 h-3.5" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-700 dark:text-slate-300 leading-relaxed truncate">{{ event.message }}</p>
                    <p class="text-[10px] text-gray-400 dark:text-slate-600 mt-0.5">{{ event.time }}</p>
                  </div>
                  <div class="w-1.5 h-1.5 rounded-full mt-2 shrink-0" :class="activityDot[event.color] ?? 'bg-gray-300 dark:bg-slate-600'"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Low Stock / Out-of-Stock Alerts -->
          <div class="rounded-2xl overflow-hidden
                      bg-white border border-gray-200 shadow-sm
                      dark:bg-white/[0.04] dark:backdrop-blur-xl dark:border-white/[0.08] dark:shadow-none">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-white/[0.06] flex items-center justify-between">
              <div class="flex items-center gap-2">
                <AlertTriangle class="w-4 h-4 text-orange-500 dark:text-orange-400" />
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Stock Alerts</h3>
              </div>
              <Link href="/admin/catalog/products"
                class="text-xs font-medium text-gray-400 hover:text-gray-600 dark:text-slate-500 dark:hover:text-slate-300 transition-colors">
                Manage
              </Link>
            </div>
            <div class="p-5">
              <div v-if="!props.lowStockProducts?.length" class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-slate-600">
                <Package class="w-8 h-8 mb-2 opacity-20" />
                <p class="text-xs">All products in stock</p>
              </div>
              <div v-else class="space-y-2.5">
                <div
                  v-for="item in (props.lowStockProducts ?? [])"
                  :key="item.sku"
                  class="flex items-center justify-between gap-3 p-3 rounded-xl transition-colors
                         bg-gray-50 border border-gray-100 hover:bg-gray-100
                         dark:bg-white/[0.03] dark:border-white/[0.06] dark:hover:bg-white/[0.06]"
                >
                  <div class="min-w-0">
                    <p class="text-xs font-semibold text-gray-800 dark:text-slate-200 truncate">{{ item.name }}</p>
                    <p class="text-[10px] text-gray-400 dark:text-slate-600 mt-0.5">SKU: {{ item.sku }}</p>
                  </div>
                  <span
                    class="shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full border"
                    :class="item.stock_status === 'out_of_stock'
                      ? 'bg-red-50 text-red-700 border-red-100 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20'
                      : 'bg-yellow-50 text-yellow-700 border-yellow-100 dark:bg-yellow-500/10 dark:text-yellow-400 dark:border-yellow-500/20'"
                  >
                    {{ item.stock_status === 'out_of_stock' ? 'Out of Stock' : 'Backorder' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!--  Quick Actions  -->
        <div class="rounded-2xl p-5
                    bg-white border border-gray-200 shadow-sm
                    dark:bg-white/[0.04] dark:backdrop-blur-xl dark:border-white/[0.08] dark:shadow-none">
          <h3 class="text-xs font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-4">Quick Actions</h3>
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <Link href="/admin/catalog/products/create"
              class="group flex flex-col items-center justify-center gap-2.5 p-4 rounded-xl transition-all duration-200
                     bg-gray-50 border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50
                     dark:bg-white/[0.03] dark:border-white/[0.07] dark:hover:border-indigo-500/40 dark:hover:bg-indigo-500/5">
              <div class="w-9 h-9 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform
                          bg-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <Plus class="w-4 h-4" />
              </div>
              <span class="text-[11px] font-medium text-gray-600 dark:text-slate-400">Add Product</span>
            </Link>
            <Link href="/admin/sales/orders"
              class="group flex flex-col items-center justify-center gap-2.5 p-4 rounded-xl transition-all duration-200
                     bg-gray-50 border border-gray-100 hover:border-emerald-200 hover:bg-emerald-50
                     dark:bg-white/[0.03] dark:border-white/[0.07] dark:hover:border-emerald-500/40 dark:hover:bg-emerald-500/5">
              <div class="w-9 h-9 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform
                          bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <ShoppingCart class="w-4 h-4" />
              </div>
              <span class="text-[11px] font-medium text-gray-600 dark:text-slate-400">Orders</span>
            </Link>
            <Link href="/admin/customers"
              class="group flex flex-col items-center justify-center gap-2.5 p-4 rounded-xl transition-all duration-200
                     bg-gray-50 border border-gray-100 hover:border-purple-200 hover:bg-purple-50
                     dark:bg-white/[0.03] dark:border-white/[0.07] dark:hover:border-purple-500/40 dark:hover:bg-purple-500/5">
              <div class="w-9 h-9 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform
                          bg-purple-100 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400">
                <Users class="w-4 h-4" />
              </div>
              <span class="text-[11px] font-medium text-gray-600 dark:text-slate-400">Customers</span>
            </Link>
            <Link href="/admin/reports"
              class="group flex flex-col items-center justify-center gap-2.5 p-4 rounded-xl transition-all duration-200
                     bg-gray-50 border border-gray-100 hover:border-cyan-200 hover:bg-cyan-50
                     dark:bg-white/[0.03] dark:border-white/[0.07] dark:hover:border-cyan-500/40 dark:hover:bg-cyan-500/5">
              <div class="w-9 h-9 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform
                          bg-cyan-100 text-cyan-600 dark:bg-cyan-500/10 dark:text-cyan-400">
                <BarChart3 class="w-4 h-4" />
              </div>
              <span class="text-[11px] font-medium text-gray-600 dark:text-slate-400">Reports</span>
            </Link>
            <Link href="/admin/marketing"
              class="group flex flex-col items-center justify-center gap-2.5 p-4 rounded-xl transition-all duration-200
                     bg-gray-50 border border-gray-100 hover:border-pink-200 hover:bg-pink-50
                     dark:bg-white/[0.03] dark:border-white/[0.07] dark:hover:border-pink-500/40 dark:hover:bg-pink-500/5">
              <div class="w-9 h-9 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform
                          bg-pink-100 text-pink-600 dark:bg-pink-500/10 dark:text-pink-400">
                <Megaphone class="w-4 h-4" />
              </div>
              <span class="text-[11px] font-medium text-gray-600 dark:text-slate-400">Marketing</span>
            </Link>
            <Link href="/admin/settings"
              class="group flex flex-col items-center justify-center gap-2.5 p-4 rounded-xl transition-all duration-200
                     bg-gray-50 border border-gray-100 hover:border-slate-300 hover:bg-slate-50
                     dark:bg-white/[0.03] dark:border-white/[0.07] dark:hover:border-slate-500/40 dark:hover:bg-slate-500/5">
              <div class="w-9 h-9 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform
                          bg-slate-100 text-slate-600 dark:bg-slate-500/10 dark:text-slate-400">
                <Settings class="w-4 h-4" />
              </div>
              <span class="text-[11px] font-medium text-gray-600 dark:text-slate-400">Settings</span>
            </Link>
          </div>
        </div>

      </div><!-- /z-10 -->
    </div><!-- /page wrapper -->

  </AdminLayout>
</template>

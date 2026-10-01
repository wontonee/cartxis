<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Pagination from '@/components/Admin/Pagination.vue'
import {
  ArrowUpDown,
  Eye,
  FileText,
  Filter,
  Search,
} from 'lucide-vue-next'

interface Quote {
  id: number
  reference: string
  customer_name: string
  customer_email: string
  product_name: string | null
  quantity: number
  status: string
  created_at: string
  product?: { id: number; name: string; sku: string } | null
}

const props = defineProps<{
  quotes: {
    data: Quote[]
    current_page?: number
    last_page?: number
    per_page?: number
    total?: number
    links?: any
  }
  filters: { search?: string; status?: string }
  stats: {
    total: number
    new: number
    reviewed: number
    quoted: number
    closed: number
  }
}>()

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')

const applyFilters = () => {
  router.get('/admin/catalog/quote-requests', {
    search: search.value || undefined,
    status: status.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

watch(status, () => applyFilters())

const clearFilters = () => {
  search.value = ''
  status.value = ''
  applyFilters()
}

const hasActiveFilters = computed(() => !!(search.value || status.value))

const statusClass = (value: string) => {
  switch (value) {
    case 'new':
      return 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800'
    case 'reviewed':
      return 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800'
    case 'quoted':
      return 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/20 dark:text-indigo-300 dark:border-indigo-800'
    case 'closed':
      return 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'
    default:
      return 'bg-gray-100 text-gray-700 border-gray-200'
  }
}

const formatDate = (value: string) => {
  try {
    return new Date(value).toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    })
  } catch {
    return value
  }
}
</script>

<template>
  <Head title="Quote Requests" />

  <AdminLayout title="Quote Requests">
    <div class="p-6 space-y-6">
      <!-- Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total</p>
          <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400">New</p>
          <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">{{ stats.new }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Reviewed</p>
          <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ stats.reviewed }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Quoted</p>
          <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ stats.quoted }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Closed</p>
          <p class="text-3xl font-bold text-gray-600 dark:text-gray-300 mt-1">{{ stats.closed }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="search"
                type="search"
                placeholder="Search reference, customer, product…"
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
                @keyup.enter="applyFilters"
              />
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
            <div class="relative">
              <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <select
                v-model="status"
                class="w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
              >
                <option value="">All statuses</option>
                <option value="new">New</option>
                <option value="reviewed">Reviewed</option>
                <option value="quoted">Quoted</option>
                <option value="closed">Closed</option>
              </select>
              <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <ArrowUpDown class="w-3 h-3 text-gray-400" />
              </div>
            </div>
          </div>
          <div class="flex items-end gap-2">
            <button
              type="button"
              @click="applyFilters"
              class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
            >
              Apply
            </button>
            <button
              v-if="hasActiveFilters"
              type="button"
              @click="clearFilters"
              class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
            >
              Clear
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/40">
              <tr>
                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reference</th>
                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Qty</th>
                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/80">
              <tr
                v-for="quote in quotes.data"
                :key="quote.id"
                class="hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-900/20">
                      <FileText class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ quote.reference }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ quote.customer_name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ quote.customer_email }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-700 dark:text-gray-300 max-w-[220px] truncate">
                    {{ quote.product_name || quote.product?.name || '—' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                  {{ quote.quantity }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full border capitalize"
                    :class="statusClass(quote.status)"
                  >
                    {{ quote.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ formatDate(quote.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <Link
                    :href="`/admin/catalog/quote-requests/${quote.id}`"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors"
                  >
                    <Eye class="w-3.5 h-3.5" />
                    View
                  </Link>
                </td>
              </tr>
              <tr v-if="quotes.data.length === 0">
                <td colspan="7" class="px-6 py-16 text-center">
                  <div class="mx-auto w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                    <FileText class="w-6 h-6 text-gray-400" />
                  </div>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">No quote requests yet</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">RFQ submissions from quote products will appear here.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          v-if="quotes.last_page && quotes.last_page > 1"
          class="px-6 py-4 border-t border-gray-100 dark:border-gray-700"
        >
          <Pagination :data="quotes" resource-name="quote requests" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

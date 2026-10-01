<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

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
    links: any
  }
  filters: { search?: string; status?: string }
}>()

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')

watch([search, status], () => {
  router.get('/admin/catalog/quote-requests', {
    search: search.value || undefined,
    status: status.value || undefined,
  }, { preserveState: true, replace: true })
})
</script>

<template>
  <Head title="Quote Requests" />
  <AdminLayout title="Quote Requests">
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Quote Requests</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">RFQ submissions from quote-only products</p>
      </div>

      <div class="flex flex-col sm:flex-row gap-3">
        <input
          v-model="search"
          type="search"
          placeholder="Search reference, customer, product…"
          class="w-full sm:max-w-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm"
        />
        <select v-model="status" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm">
          <option value="">All statuses</option>
          <option value="new">New</option>
          <option value="reviewed">Reviewed</option>
          <option value="quoted">Quoted</option>
          <option value="closed">Closed</option>
        </select>
      </div>

      <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-900/40">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="quote in quotes.data" :key="quote.id">
              <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ quote.reference }}</td>
              <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                <div>{{ quote.customer_name }}</div>
                <div class="text-xs text-gray-400">{{ quote.customer_email }}</div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ quote.product_name }}</td>
              <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ quote.quantity }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex rounded-full bg-gray-100 dark:bg-gray-700 px-2.5 py-0.5 text-xs font-medium capitalize">{{ quote.status }}</span>
              </td>
              <td class="px-4 py-3 text-right">
                <Link :href="`/admin/catalog/quote-requests/${quote.id}`" class="text-sm text-blue-600 hover:underline">View</Link>
              </td>
            </tr>
            <tr v-if="quotes.data.length === 0">
              <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No quote requests yet.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

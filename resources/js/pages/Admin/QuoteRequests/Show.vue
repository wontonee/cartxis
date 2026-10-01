<script setup lang="ts">
import { useForm, Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

interface Quote {
  id: number
  reference: string
  customer_name: string
  customer_email: string
  customer_phone: string | null
  company: string | null
  quantity: number
  message: string | null
  status: string
  admin_notes: string | null
  product_name: string | null
  product_sku: string | null
  created_at: string
  product?: { id: number; name: string; slug?: string } | null
}

const props = defineProps<{ quote: Quote }>()

const form = useForm({
  status: props.quote.status,
  admin_notes: props.quote.admin_notes || '',
})

const save = () => {
  form.put(`/admin/catalog/quote-requests/${props.quote.id}`, { preserveScroll: true })
}

const destroy = () => {
  if (!confirm('Delete this quote request?')) return
  router.delete(`/admin/catalog/quote-requests/${props.quote.id}`)
}
</script>

<template>
  <Head :title="`Quote ${quote.reference}`" />
  <AdminLayout :title="`Quote ${quote.reference}`">
    <div class="space-y-6 max-w-3xl">
      <div class="flex items-center justify-between gap-4">
        <div>
          <Link href="/admin/catalog/quote-requests" class="text-sm text-blue-600 hover:underline">← Quote Requests</Link>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ quote.reference }}</h1>
        </div>
        <button type="button" class="text-sm text-red-600 hover:underline" @click="destroy">Delete</button>
      </div>

      <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-gray-500">Customer</p>
            <p class="font-medium text-gray-900 dark:text-white">{{ quote.customer_name }}</p>
            <p class="text-gray-600">{{ quote.customer_email }}</p>
            <p v-if="quote.customer_phone" class="text-gray-600">{{ quote.customer_phone }}</p>
            <p v-if="quote.company" class="text-gray-600">{{ quote.company }}</p>
          </div>
          <div>
            <p class="text-gray-500">Product</p>
            <p class="font-medium text-gray-900 dark:text-white">{{ quote.product_name }}</p>
            <p class="text-gray-600">SKU: {{ quote.product_sku || '—' }}</p>
            <p class="text-gray-600">Quantity: {{ quote.quantity }}</p>
          </div>
        </div>
        <div v-if="quote.message">
          <p class="text-gray-500 text-sm">Message</p>
          <p class="mt-1 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ quote.message }}</p>
        </div>
      </div>

      <form class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 space-y-4" @submit.prevent="save">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
          <select v-model="form.status" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
            <option value="new">New</option>
            <option value="reviewed">Reviewed</option>
            <option value="quoted">Quoted</option>
            <option value="closed">Closed</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Admin notes</label>
          <textarea v-model="form.admin_notes" rows="4" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
        </div>
        <button
          type="submit"
          class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500 disabled:opacity-50"
          :disabled="form.processing"
        >
          Save
        </button>
      </form>
    </div>
  </AdminLayout>
</template>

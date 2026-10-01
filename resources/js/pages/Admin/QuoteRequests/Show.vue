<script setup lang="ts">
import { computed } from 'vue'
import { useForm, Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue'
import { ref } from 'vue'
import {
  ArrowLeft,
  Building2,
  Calendar,
  FileText,
  Mail,
  Package,
  Phone,
  Save,
  Trash2,
  User,
} from 'lucide-vue-next'

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
  product?: { id: number; name: string; slug?: string; sku?: string } | null
}

const props = defineProps<{ quote: Quote }>()

const form = useForm({
  status: props.quote.status,
  admin_notes: props.quote.admin_notes || '',
})

const showDeleteModal = ref(false)

const statusOptions = [
  { value: 'new', label: 'New' },
  { value: 'reviewed', label: 'Reviewed' },
  { value: 'quoted', label: 'Quoted' },
  { value: 'closed', label: 'Closed' },
]

const statusBadgeClass = computed(() => {
  switch (props.quote.status) {
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
})

const save = () => {
  form.put(`/admin/catalog/quote-requests/${props.quote.id}`, { preserveScroll: true })
}

const confirmDelete = () => {
  router.delete(`/admin/catalog/quote-requests/${props.quote.id}`, {
    onFinish: () => {
      showDeleteModal.value = false
    },
  })
}

const formatDate = (value: string) => {
  try {
    return new Date(value).toLocaleString()
  } catch {
    return value
  }
}
</script>

<template>
  <Head :title="`Quote ${quote.reference}`" />

  <AdminLayout :title="`Quote ${quote.reference}`">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <Link
          href="/admin/catalog/quote-requests"
          class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
        >
          <ArrowLeft class="w-4 h-4" />
          Back to Quote Requests
        </Link>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="save"
            :disabled="form.processing"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
          >
            <Save class="w-4 h-4" />
            {{ form.processing ? 'Saving…' : 'Save Changes' }}
          </button>
          <button
            type="button"
            @click="showDeleteModal = true"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700 transition-colors"
          >
            <Trash2 class="w-4 h-4" />
            Delete
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Customer request -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-start justify-between gap-4 mb-6">
              <div class="flex items-center gap-4">
                <div class="flex-shrink-0 h-12 w-12 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                  <User class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ quote.customer_name }}</h2>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ quote.reference }}</p>
                </div>
              </div>
              <span
                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full border capitalize"
                :class="statusBadgeClass"
              >
                {{ quote.status }}
              </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
              <div class="flex items-start gap-3 rounded-lg bg-gray-50 dark:bg-gray-900/40 p-3">
                <Mail class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                <div class="min-w-0">
                  <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Email</p>
                  <a :href="`mailto:${quote.customer_email}`" class="text-sm text-blue-600 dark:text-blue-400 hover:underline break-all">
                    {{ quote.customer_email }}
                  </a>
                </div>
              </div>
              <div v-if="quote.customer_phone" class="flex items-start gap-3 rounded-lg bg-gray-50 dark:bg-gray-900/40 p-3">
                <Phone class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Phone</p>
                  <p class="text-sm text-gray-900 dark:text-white">{{ quote.customer_phone }}</p>
                </div>
              </div>
              <div v-if="quote.company" class="flex items-start gap-3 rounded-lg bg-gray-50 dark:bg-gray-900/40 p-3">
                <Building2 class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</p>
                  <p class="text-sm text-gray-900 dark:text-white">{{ quote.company }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3 rounded-lg bg-gray-50 dark:bg-gray-900/40 p-3">
                <Calendar class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Submitted</p>
                  <p class="text-sm text-gray-900 dark:text-white">{{ formatDate(quote.created_at) }}</p>
                </div>
              </div>
            </div>

            <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
              <div class="flex items-center gap-2 mb-3">
                <FileText class="w-4 h-4 text-gray-400" />
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Customer message</h3>
              </div>
              <div
                v-if="quote.message"
                class="rounded-lg bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700 p-4 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed"
              >
                {{ quote.message }}
              </div>
              <p v-else class="text-sm text-gray-500 dark:text-gray-400 italic">No message provided.</p>
            </div>
          </div>

          <!-- Admin notes -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Admin notes</h3>
            <form class="space-y-4" @submit.prevent="save">
              <textarea
                v-model="form.admin_notes"
                rows="5"
                placeholder="Internal notes about this quote request…"
                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
              />
              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
                >
                  <Save class="w-4 h-4" />
                  {{ form.processing ? 'Saving…' : 'Save Notes' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status</h3>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
              Update status
            </label>
            <select
              v-model="form.status"
              class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
            >
              <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
            <button
              type="button"
              @click="save"
              :disabled="form.processing"
              class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
            >
              Update Status
            </button>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Product</h3>
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0 h-10 w-10 bg-amber-50 dark:bg-amber-900/20 rounded-lg flex items-center justify-center">
                <Package class="w-5 h-5 text-amber-600 dark:text-amber-400" />
              </div>
              <div class="min-w-0">
                <Link
                  v-if="quote.product?.id"
                  :href="`/admin/catalog/products/${quote.product.id}/edit`"
                  class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline"
                >
                  {{ quote.product_name || quote.product.name }}
                </Link>
                <p v-else class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ quote.product_name || '—' }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  SKU: {{ quote.product_sku || quote.product?.sku || '—' }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Quantity requested: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ quote.quantity }}</span>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ConfirmDeleteModal
      v-model:show="showDeleteModal"
      title="quote request"
      :message="`Are you sure you want to delete '${quote.reference}'? This action cannot be undone.`"
      @confirm="confirmDelete"
    />
  </AdminLayout>
</template>

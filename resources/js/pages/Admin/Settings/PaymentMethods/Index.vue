<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import PaymentMethodCard from './Components/PaymentMethodCard.vue'

interface PaymentMethod {
  id: number
  code: string
  name: string
  description: string
  type: string
  is_active: boolean
  is_default: boolean
  sort_order: number
  instructions: string
  configuration: Record<string, any>
  created_at: string
  updated_at: string
}

const props = defineProps<{
  paymentMethods: PaymentMethod[]
}>()

const page = usePage()
const errors = computed(() => page.props.errors as Record<string, string>)
const processing = ref(false)

const handleToggle = (method: PaymentMethod) => {
  processing.value = true
  router.post(`/admin/settings/payment-methods/${method.id}/toggle`, {}, {
    preserveScroll: true,
    preserveState: false,
    onFinish: () => {
      processing.value = false
    },
  })
}

const handleSetDefault = (method: PaymentMethod) => {
  processing.value = true
  router.post(`/admin/settings/payment-methods/${method.id}/set-default`, {}, {
    preserveScroll: true,
    preserveState: false,
    onFinish: () => {
      processing.value = false
    },
  })
}

const handleConfigure = (method: PaymentMethod) => {
  router.get(`/admin/settings/payment-methods/${method.code}/configure`)
}
</script>

<template>
  <AdminLayout title="Payment Methods">
    <Head title="Payment Methods" />

    <div class="p-6">
      <!-- Page Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Payment Methods</h1>
        <p class="mt-1 text-sm text-gray-500">Manage and configure available payment methods for your store</p>
      </div>

      <!-- Payment Methods Grid -->
      <div v-if="props.paymentMethods.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <PaymentMethodCard
          v-for="method in props.paymentMethods"
          :key="method.id"
          :payment-method="method"
          :processing="processing"
          @toggle="handleToggle(method)"
          @configure="handleConfigure(method)"
          @set-default="handleSetDefault(method)"
        />
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
        <p class="text-gray-600">No payment methods found.</p>
      </div>

      <!-- Help Text -->
      <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <h3 class="font-semibold text-blue-900 mb-2">Payment Methods Management</h3>
        <ul class="text-sm text-blue-800 space-y-1">
          <li>• Use the Configure button to set up payment method details</li>
          <li>• Enable/Disable payment methods with the toggle buttons</li>
          <li>• Set a default payment method for customer checkout</li>
          <li>• Each payment method can have custom instructions and settings</li>
        </ul>
      </div>
    </div>
  </AdminLayout>
</template>

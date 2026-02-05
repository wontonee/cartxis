<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import PaymentMethodCard from './Components/PaymentMethodCard.vue'
import { CreditCard, Info, ShieldCheck, Zap, AlertCircle } from 'lucide-vue-next'

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
      <div class="mb-8 flex items-start justify-between">
        <div>
          <div class="flex items-center gap-3 mb-2">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
              <CreditCard class="w-6 h-6" />
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payment Methods</h1>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400 max-w-2xl leading-relaxed ml-11">
            Configure how your customers pay for their orders. Enable multiple gateways to provide flexibility and increase conversion rates.
          </p>
        </div>
      </div>

      <!-- Payment Methods Grid -->
      <div v-if="props.paymentMethods.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
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
      <div v-else class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-8">
        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-4">
          <CreditCard class="w-8 h-8" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No payment methods found</h3>
        <p class="text-gray-500 dark:text-gray-400">No payment gateways are currently installed or configured.</p>
      </div>

      <!-- Tips Section -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
         <!-- Help Text -->
        <div class="p-6 bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800/30 rounded-xl relative overflow-hidden">
          <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-blue-100 dark:bg-blue-800/20 rounded-full opacity-50 blur-xl"></div>
          
          <div class="flex items-start gap-4 relative z-10">
            <div class="p-2 bg-blue-100 dark:bg-blue-800/30 rounded-lg text-blue-600 dark:text-blue-400 mt-1">
              <ShieldCheck class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-bold text-blue-900 dark:text-blue-100 mb-2">Secure Transactions</h3>
              <p class="text-sm text-blue-800 dark:text-blue-300 leading-relaxed mb-3">
                Ensure your payment keys are kept secret. Never share your API secrets or private keys. Set up SSL on your domain to ensure customer data is encrypted during checkout.
              </p>
            </div>
          </div>
        </div>

        <div class="p-6 bg-purple-50 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-800/30 rounded-xl relative overflow-hidden">
          <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-purple-100 dark:bg-purple-800/20 rounded-full opacity-50 blur-xl"></div>
          
          <div class="flex items-start gap-4 relative z-10">
            <div class="p-2 bg-purple-100 dark:bg-purple-800/30 rounded-lg text-purple-600 dark:text-purple-400 mt-1">
              <Zap class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-bold text-purple-900 dark:text-purple-100 mb-2">Checkout Experience</h3>
              <p class="text-sm text-purple-800 dark:text-purple-300 leading-relaxed">
                Offering a "Default" payment method speeds up checkout. Consider enabling "Guest Checkout" in General Settings to reduce friction for new customers.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

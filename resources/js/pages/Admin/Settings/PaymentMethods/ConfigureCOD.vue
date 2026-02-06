<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, useForm, usePage, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

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
  method: PaymentMethod
}>()

const page = usePage()
const errors = computed(() => page.props.errors as Record<string, string>)

const form = useForm({
  name: props.method.name || 'Cash on Delivery',
  description: props.method.description || '',
  instructions: props.method.instructions || '',
  configuration: {
    min_order_amount: props.method.configuration?.min_order_amount || 0,
    max_order_amount: props.method.configuration?.max_order_amount || 10000,
    min_order_amount_currency: props.method.configuration?.min_order_amount_currency || 'USD',
    handling_fee: props.method.configuration?.handling_fee || 0,
    handling_fee_type: props.method.configuration?.handling_fee_type || 'fixed',
    handling_fee_minimum: props.method.configuration?.handling_fee_minimum || 0,
    handling_fee_maximum: props.method.configuration?.handling_fee_maximum || 100,
    enabled_countries: props.method.configuration?.enabled_countries || [],
    enabled_regions: props.method.configuration?.enabled_regions || [],
  },
})

const save = () => {
  form.post(`/admin/settings/payment-methods/cod/save`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <AdminLayout title="Payment Methods - Cash on Delivery">
    <Head title="Configure Cash on Delivery" />

    <div>
      <!-- Page Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-white font-bold">Cash on Delivery Configuration</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Configure payment settings for cash on delivery method</p>
          </div>
          <Link 
            href="/admin/settings/payment-methods"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Payment Methods
          </Link>
        </div>
      </div>

      <!-- Configuration Form -->
      <form @submit.prevent="save" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <!-- Basic Information Section -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h2>

          <div class="space-y-4">
            <!-- Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Method Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent', errors.name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                placeholder="Cash on Delivery"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Describe this payment method..."
              ></textarea>
            </div>

            <!-- Instructions -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Customer Instructions</label>
              <textarea
                v-model="form.instructions"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Instructions shown to customers at checkout..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Order Amount Limits Section -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Amount Limits</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Minimum Order Amount -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Minimum Order Amount</label>
              <input
                v-model.number="form.configuration.min_order_amount"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="0.00"
              />
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave at 0 for no minimum</p>
            </div>

            <!-- Maximum Order Amount -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Maximum Order Amount</label>
              <input
                v-model.number="form.configuration.max_order_amount"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="10000.00"
              />
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave at 0 for no maximum</p>
            </div>
          </div>
        </div>

        <!-- Handling Fee Section -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Handling Fee</h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Handling Fee Amount -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Handling Fee Amount</label>
              <input
                v-model.number="form.configuration.handling_fee"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="0.00"
              />
            </div>

            <!-- Fee Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fee Type</label>
              <select
                v-model="form.configuration.handling_fee_type"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="fixed">Fixed Amount</option>
                <option value="percentage">Percentage</option>
              </select>
            </div>

            <!-- Fee Maximum -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Maximum Fee</label>
              <input
                v-model.number="form.configuration.handling_fee_maximum"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="100.00"
              />
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex gap-3 justify-end">
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition"
          >
            {{ form.processing ? 'Saving...' : 'Save Configuration' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

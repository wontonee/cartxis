<script setup lang="ts">
import { computed } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
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
  name: props.method.name || 'PayUMoney',
  description: props.method.description || '',
  instructions: props.method.instructions || '',
  configuration: {
    merchant_key: props.method.configuration?.merchant_key || '',
    merchant_salt: props.method.configuration?.merchant_salt || '',
    mode: props.method.configuration?.mode || 'test',
    auth_header: props.method.configuration?.auth_header || '',
  },
})

const save = () => {
  form.post('/admin/settings/payment-methods/payumoney/save', {
    preserveScroll: true,
  })
}
</script>

<template>
  <AdminLayout title="Payment Methods - PayUMoney">
    <Head title="Configure PayUMoney" />

    <div class="p-6">
      <div class="mb-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl md:text-3xl text-gray-800 font-bold">PayUMoney Configuration</h1>
            <p class="text-sm text-gray-600 mt-1">Configure PayUMoney payment gateway settings</p>
          </div>
          <a
            href="/admin/settings/payment-methods"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Payment Methods
          </a>
        </div>
      </div>

      <form @submit.prevent="save" class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Method Name <span class="text-red-500">*</span></label>
              <input
                v-model="form.name"
                type="text"
                :class="['w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent', errors.name ? 'border-red-500' : 'border-gray-300']"
                placeholder="PayUMoney"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Describe this payment method..."
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Customer Instructions</label>
              <textarea
                v-model="form.instructions"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Instructions shown to customers at checkout..."
              ></textarea>
            </div>
          </div>
        </div>

        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">PayUMoney Credentials</h2>

          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <p class="text-sm text-blue-900">
              <strong>Get your credentials:</strong> Log in to your PayUMoney merchant dashboard
            </p>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Merchant Key <span class="text-red-500">*</span></label>
              <input
                v-model="form.configuration.merchant_key"
                type="text"
                :class="['w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm', errors['configuration.merchant_key'] ? 'border-red-500' : 'border-gray-300']"
                placeholder="Merchant Key"
              />
              <p v-if="errors['configuration.merchant_key']" class="mt-1 text-sm text-red-600">{{ errors['configuration.merchant_key'] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Merchant Salt <span class="text-red-500">*</span></label>
              <input
                v-model="form.configuration.merchant_salt"
                type="password"
                :class="['w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm', errors['configuration.merchant_salt'] ? 'border-red-500' : 'border-gray-300']"
                placeholder="Merchant Salt"
              />
              <p v-if="errors['configuration.merchant_salt']" class="mt-1 text-sm text-red-600">{{ errors['configuration.merchant_salt'] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Mode <span class="text-red-500">*</span></label>
              <select
                v-model="form.configuration.mode"
                :class="['w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent', errors['configuration.mode'] ? 'border-red-500' : 'border-gray-300']"
              >
                <option value="test">Test</option>
                <option value="production">Production</option>
              </select>
              <p v-if="errors['configuration.mode']" class="mt-1 text-sm text-red-600">{{ errors['configuration.mode'] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Auth Header <span class="text-gray-400">(Optional)</span></label>
              <input
                v-model="form.configuration.auth_header"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                placeholder="Authorization Header"
              />
            </div>
          </div>
        </div>

        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end">
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : 'Save Configuration' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

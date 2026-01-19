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
  name: props.method.name || 'PayPal',
  description: props.method.description || '',
  instructions: props.method.instructions || '',
  configuration: {
    client_id: props.method.configuration?.client_id || '',
    client_secret: props.method.configuration?.client_secret || '',
    mode: props.method.configuration?.mode || 'sandbox',
    webhook_id: props.method.configuration?.webhook_id || '',
  },
})

const save = () => {
  form.post('/admin/settings/payment-methods/paypal/save', {
    preserveScroll: true,
  })
}
</script>

<template>
  <AdminLayout title="Payment Methods - PayPal">
    <Head title="Configure PayPal" />

    <div class="p-6">
      <div class="mb-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl md:text-3xl text-gray-800 font-bold">PayPal Configuration</h1>
            <p class="text-sm text-gray-600 mt-1">Configure PayPal payment gateway settings</p>
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
                placeholder="PayPal"
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
          <h2 class="text-lg font-semibold text-gray-900 mb-4">PayPal API Credentials</h2>

          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <p class="text-sm text-blue-900">
              <strong>Get your credentials:</strong> Log in to your <a href="https://developer.paypal.com/" target="_blank" class="underline">PayPal Developer Dashboard</a> → Apps &amp; Credentials
            </p>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Client ID <span class="text-red-500">*</span></label>
              <input
                v-model="form.configuration.client_id"
                type="text"
                :class="['w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm', errors['configuration.client_id'] ? 'border-red-500' : 'border-gray-300']"
                placeholder="PayPal Client ID"
              />
              <p v-if="errors['configuration.client_id']" class="mt-1 text-sm text-red-600">{{ errors['configuration.client_id'] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Client Secret <span class="text-red-500">*</span></label>
              <input
                v-model="form.configuration.client_secret"
                type="password"
                :class="['w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm', errors['configuration.client_secret'] ? 'border-red-500' : 'border-gray-300']"
                placeholder="PayPal Client Secret"
              />
              <p v-if="errors['configuration.client_secret']" class="mt-1 text-sm text-red-600">{{ errors['configuration.client_secret'] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Mode <span class="text-red-500">*</span></label>
              <select
                v-model="form.configuration.mode"
                :class="['w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent', errors['configuration.mode'] ? 'border-red-500' : 'border-gray-300']"
              >
                <option value="sandbox">Sandbox</option>
                <option value="live">Live</option>
              </select>
              <p v-if="errors['configuration.mode']" class="mt-1 text-sm text-red-600">{{ errors['configuration.mode'] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Webhook ID <span class="text-gray-400">(Optional)</span></label>
              <input
                v-model="form.configuration.webhook_id"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                placeholder="Webhook ID"
              />
              <p class="mt-1 text-xs text-gray-500">Configure webhooks in your PayPal app settings</p>
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

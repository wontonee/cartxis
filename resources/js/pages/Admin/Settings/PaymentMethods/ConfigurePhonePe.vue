<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
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

// Webhook URL - computed on client side
const webhookUrl = ref('/phonepe/webhook')

onMounted(() => {
  if (typeof window !== 'undefined') {
    webhookUrl.value = `${window.location.origin}/phonepe/webhook`
  }
})

const form = useForm({
  name: props.method.name || 'PhonePe',
  description: props.method.description || '',
  instructions: props.method.instructions || '',
  configuration: {
    client_id: props.method.configuration?.client_id || '',
    client_secret: props.method.configuration?.client_secret || '',
    client_version: props.method.configuration?.client_version || 1,
    callback_username: props.method.configuration?.callback_username || '',
    callback_password: props.method.configuration?.callback_password || '',
    payment_methods: {
      upi: props.method.configuration?.payment_methods?.upi ?? true,
      card: props.method.configuration?.payment_methods?.card ?? true,
      netbanking: props.method.configuration?.payment_methods?.netbanking ?? true,
      wallet: props.method.configuration?.payment_methods?.wallet ?? true,
    },
  },
})

const save = () => {
  form.post(`/admin/settings/payment-methods/phonepe/save`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <AdminLayout title="Payment Methods - PhonePe">
    <Head title="Configure PhonePe" />

    <div>
      <!-- Page Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-white font-bold">PhonePe Configuration</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Configure PhonePe payment gateway settings</p>
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
                placeholder="PhonePe"
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

        <!-- PhonePe API Keys Section -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">PhonePe API Credentials</h2>

          <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
            <p class="text-sm text-blue-900 dark:text-blue-200">
              <strong>Get your credentials:</strong> Log in to your <a href="https://business.phonepe.com/" target="_blank" class="underline">PhonePe Business Dashboard</a> → Settings → API Credentials
            </p>
          </div>

          <div class="space-y-4">
            <!-- Client ID -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Client ID <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.configuration.client_id"
                type="text"
                :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm', errors['configuration.client_id'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                placeholder="Enter your PhonePe Client ID"
              />
              <p v-if="errors['configuration.client_id']" class="mt-1 text-sm text-red-600">{{ errors['configuration.client_id'] }}</p>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Your PhonePe merchant Client ID</p>
            </div>

            <!-- Client Secret -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Client Secret <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.configuration.client_secret"
                type="password"
                :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm', errors['configuration.client_secret'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                placeholder="Enter your PhonePe Client Secret"
              />
              <p v-if="errors['configuration.client_secret']" class="mt-1 text-sm text-red-600">{{ errors['configuration.client_secret'] }}</p>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Keep this secret secure - never share it publicly</p>
            </div>

            <!-- Client Version -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Client Version <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="form.configuration.client_version"
                type="number"
                min="1"
                :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent', errors['configuration.client_version'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                placeholder="1"
              />
              <p v-if="errors['configuration.client_version']" class="mt-1 text-sm text-red-600">{{ errors['configuration.client_version'] }}</p>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PhonePe API version (usually 1)</p>
            </div>
          </div>
        </div>

        <!-- Callback Authentication Section -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Webhook Authentication</h2>

          <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
            <p class="text-sm text-yellow-900 dark:text-yellow-200">
              <strong>Important:</strong> PhonePe uses Basic Authentication for webhook callbacks. Set these credentials in your PhonePe Business Dashboard.
            </p>
          </div>

          <div class="space-y-4">
            <!-- Callback Username -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Callback Username <span class="text-gray-400 dark:text-gray-500">(Optional)</span>
              </label>
              <input
                v-model="form.configuration.callback_username"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Enter webhook username"
              />
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Username for webhook Basic Authentication</p>
            </div>

            <!-- Callback Password -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Callback Password <span class="text-gray-400 dark:text-gray-500">(Optional)</span>
              </label>
              <input
                v-model="form.configuration.callback_password"
                type="password"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Enter webhook password"
              />
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Password for webhook Basic Authentication</p>
            </div>
          </div>
        </div>

        <!-- Payment Methods Section -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Enabled Payment Methods</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Select which payment methods to enable for customers</p>

          <div class="space-y-3">
            <!-- UPI -->
            <label class="flex items-center">
              <input
                v-model="form.configuration.payment_methods.upi"
                type="checkbox"
                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700"
              />
              <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">UPI (Google Pay, PhonePe, Paytm, etc.)</span>
            </label>

            <!-- Cards -->
            <label class="flex items-center">
              <input
                v-model="form.configuration.payment_methods.card"
                type="checkbox"
                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700"
              />
              <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Credit & Debit Cards (Visa, Mastercard, RuPay)</span>
            </label>

            <!-- Net Banking -->
            <label class="flex items-center">
              <input
                v-model="form.configuration.payment_methods.netbanking"
                type="checkbox"
                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700"
              />
              <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Net Banking (All major Indian banks)</span>
            </label>

            <!-- Wallet -->
            <label class="flex items-center">
              <input
                v-model="form.configuration.payment_methods.wallet"
                type="checkbox"
                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700"
              />
              <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">PhonePe Wallet</span>
            </label>
          </div>
        </div>

        <!-- Webhook URL Info -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Webhook Configuration</h2>
          <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <p class="text-sm text-green-900 dark:text-green-200 mb-2">
              <strong>Webhook URL:</strong> Configure this URL in your PhonePe Business Dashboard
            </p>
            <div class="bg-white dark:bg-gray-800 border border-green-300 dark:border-green-700 rounded px-3 py-2">
              <code class="text-sm text-green-800 dark:text-green-300 break-all">{{ webhookUrl }}</code>
            </div>
            <p class="mt-2 text-xs text-green-700 dark:text-green-400">
              PhonePe will send payment notifications to this URL for real-time order updates.
            </p>
          </div>
        </div>

        <!-- Gateway Redirect Notice -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <p class="text-sm text-blue-900 dark:text-blue-200">
              <strong>Payment Processing:</strong> Customers will be redirected to PhonePe's secure payment page to complete their transaction. All payments are processed through PhonePe's secure gateway.
            </p>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="p-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex justify-end">
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

<style scoped>
code {
  font-family: 'Courier New', monospace;
  font-size: 12px;
}
</style>

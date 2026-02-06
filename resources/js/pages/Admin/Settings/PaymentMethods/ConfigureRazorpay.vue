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
  name: props.method.name || 'Razorpay',
  description: props.method.description || '',
  instructions: props.method.instructions || '',
  configuration: {
    key_id: props.method.configuration?.key_id || '',
    key_secret: props.method.configuration?.key_secret || '',
    currency: props.method.configuration?.currency || 'INR',
    webhook_secret: props.method.configuration?.webhook_secret || '',
    auto_capture: props.method.configuration?.auto_capture ?? true,
  },
})

const save = () => {
  form.post(`/admin/settings/payment-methods/razorpay/save`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <AdminLayout title="Payment Methods - Razorpay">
    <Head title="Configure Razorpay" />

    <div>
      <!-- Page Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-white font-bold">Razorpay Configuration</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Configure Razorpay payment gateway settings</p>
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
                placeholder="Razorpay"
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

        <!-- Razorpay API Keys Section -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Razorpay API Keys</h2>

          <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
            <p class="text-sm text-blue-900 dark:text-blue-200">
              <strong>Get your keys:</strong> Log in to your <a href="https://dashboard.razorpay.com/app/keys" target="_blank" class="underline">Razorpay Dashboard</a> → Settings → API Keys
            </p>
          </div>

          <div class="space-y-4">
            <!-- Key ID -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Key ID <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.configuration.key_id"
                type="text"
                :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm', errors['configuration.key_id'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                placeholder="rzp_test_..."
              />
              <p v-if="errors['configuration.key_id']" class="mt-1 text-sm text-red-600">{{ errors['configuration.key_id'] }}</p>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Starts with <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">rzp_test_</code> or <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">rzp_live_</code></p>
            </div>

            <!-- Key Secret -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Key Secret <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.configuration.key_secret"
                type="password"
                :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm', errors['configuration.key_secret'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                placeholder="Enter your Razorpay Key Secret"
              />
              <p v-if="errors['configuration.key_secret']" class="mt-1 text-sm text-red-600">{{ errors['configuration.key_secret'] }}</p>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Keep this secret secure - never share it publicly</p>
            </div>

            <!-- Currency -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Currency <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.configuration.currency"
                :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent', errors['configuration.currency'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
              >
                <option value="INR">Indian Rupee (INR)</option>
                <option value="USD">US Dollar (USD)</option>
                <option value="EUR">Euro (EUR)</option>
                <option value="GBP">British Pound (GBP)</option>
              </select>
              <p v-if="errors['configuration.currency']" class="mt-1 text-sm text-red-600">{{ errors['configuration.currency'] }}</p>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Razorpay primarily supports INR for domestic Indian payments</p>
            </div>

            <!-- Webhook Secret (Optional) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Webhook Secret <span class="text-gray-400 dark:text-gray-500">(Optional)</span>
              </label>
              <input
                v-model="form.configuration.webhook_secret"
                type="password"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                placeholder="whsec_..."
              />
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Configure webhooks in your <a href="https://dashboard.razorpay.com/app/webhooks" target="_blank" class="text-blue-600 dark:text-blue-400 underline">Razorpay Dashboard</a>
              </p>
            </div>

            <!-- Auto Capture -->
            <div>
              <label class="flex items-center">
                <input
                  v-model="form.configuration.auto_capture"
                  type="checkbox"
                  class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700"
                />
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Auto-capture payments</span>
              </label>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 ml-6">Automatically capture payments after authorization (recommended)</p>
            </div>
          </div>
        </div>

        <!-- Payment Methods Info -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Supported Payment Methods</h2>
          <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <p class="text-sm text-green-900 dark:text-green-200 mb-2">
              <strong>Razorpay supports multiple payment methods:</strong>
            </p>
            <ul class="text-sm text-green-800 dark:text-green-300 space-y-1 ml-4 list-disc">
              <li>Credit & Debit Cards (Visa, Mastercard, Maestro, RuPay)</li>
              <li>UPI (Google Pay, PhonePe, Paytm, etc.)</li>
              <li>Net Banking (All major Indian banks)</li>
              <li>Wallets (Paytm, Mobikwik, Freecharge, etc.)</li>
              <li>EMI (Easy Monthly Installments)</li>
            </ul>
          </div>
        </div>

        <!-- Gateway Redirect Notice -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <p class="text-sm text-blue-900 dark:text-blue-200">
              <strong>Payment Processing:</strong> Customers will see the Razorpay payment interface to complete their transactions securely. All payments are processed through Razorpay's secure gateway.
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

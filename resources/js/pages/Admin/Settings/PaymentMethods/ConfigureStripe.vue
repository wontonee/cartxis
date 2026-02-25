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
const webhookUrl = ref('/stripe/webhook')
onMounted(() => {
  if (typeof window !== 'undefined') {
    webhookUrl.value = `${window.location.origin}/webhooks/stripe`
  }
})

const form = useForm({
  name: props.method.name || 'Stripe',
  description: props.method.description || '',
  instructions: props.method.instructions || '',
  configuration: {
    mode: props.method.configuration?.mode || 'live',
    // Live credentials
    publishable_key: props.method.configuration?.publishable_key || props.method.configuration?.public_key || '',
    secret_key: props.method.configuration?.secret_key || '',
    webhook_secret: props.method.configuration?.webhook_secret || '',
    // Test credentials
    test_publishable_key: props.method.configuration?.test_publishable_key || '',
    test_secret_key: props.method.configuration?.test_secret_key || '',
    test_webhook_secret: props.method.configuration?.test_webhook_secret || '',
  },
})

const save = () => {
  form.post(`/admin/settings/payment-methods/stripe/save`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <AdminLayout title="Payment Methods - Stripe">
    <Head title="Configure Stripe" />

    <div>
      <!-- Page Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-white font-bold">Stripe Configuration</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Configure Stripe payment gateway settings</p>
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
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Method Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent', errors.name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                placeholder="Stripe"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Describe this payment method..."
              ></textarea>
            </div>
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

        <!-- Environment Mode Toggle -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Environment Mode</h2>

          <div class="flex items-center gap-4 mb-6">
            <!-- Test Mode -->
            <label
              class="relative flex items-center gap-3 px-4 py-3 border-2 rounded-lg cursor-pointer transition-all"
              :class="form.configuration.mode === 'test' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-200 dark:border-gray-600'"
            >
              <input v-model="form.configuration.mode" type="radio" value="test" class="sr-only" />
              <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center" :class="form.configuration.mode === 'test' ? 'border-amber-500' : 'border-gray-300'">
                <div v-if="form.configuration.mode === 'test'" class="w-2 h-2 rounded-full bg-amber-500"></div>
              </div>
              <div>
                <div class="text-sm font-semibold" :class="form.configuration.mode === 'test' ? 'text-amber-700 dark:text-amber-400' : 'text-gray-700 dark:text-gray-300'">Test Mode</div>
                <div class="text-xs text-gray-500">No real charges</div>
              </div>
            </label>

            <!-- Live Mode -->
            <label
              class="relative flex items-center gap-3 px-4 py-3 border-2 rounded-lg cursor-pointer transition-all"
              :class="form.configuration.mode === 'live' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-600'"
            >
              <input v-model="form.configuration.mode" type="radio" value="live" class="sr-only" />
              <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center" :class="form.configuration.mode === 'live' ? 'border-green-500' : 'border-gray-300'">
                <div v-if="form.configuration.mode === 'live'" class="w-2 h-2 rounded-full bg-green-500"></div>
              </div>
              <div>
                <div class="text-sm font-semibold" :class="form.configuration.mode === 'live' ? 'text-green-700 dark:text-green-400' : 'text-gray-700 dark:text-gray-300'">Live Mode</div>
                <div class="text-xs text-gray-500">Real transactions</div>
              </div>
            </label>
          </div>

          <div v-if="form.configuration.mode === 'test'" class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
            <p class="text-sm text-amber-800 dark:text-amber-200">
              <strong>&#9888; Test Mode Active:</strong> No real charges will be made. Use Stripe test keys (<code>pk_test_</code> / <code>sk_test_</code>) and test card numbers.
            </p>
          </div>
          <div v-if="form.configuration.mode === 'live'" class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <p class="text-sm text-green-800 dark:text-green-200">
              <strong>&#10003; Live Mode:</strong> Real charges will be processed via Stripe's production environment.
            </p>
          </div>
        </div>

        <!-- Stripe API Keys -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            {{ form.configuration.mode === 'test' ? 'Test' : 'Live' }} API Keys
          </h2>

          <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
            <p class="text-sm text-blue-900 dark:text-blue-200">
              <strong>Get your keys:</strong> Log in to your
              <a href="https://dashboard.stripe.com/apikeys" target="_blank" class="underline">Stripe Dashboard</a>
              → Developers → API Keys
            </p>
          </div>

          <div class="space-y-4">

            <!-- Test keys -->
            <template v-if="form.configuration.mode === 'test'">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Test Publishable Key <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.configuration.test_publishable_key"
                  type="text"
                  :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm', errors['configuration.test_publishable_key'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                  placeholder="pk_test_..."
                />
                <p v-if="errors['configuration.test_publishable_key']" class="mt-1 text-sm text-red-600">{{ errors['configuration.test_publishable_key'] }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Starts with <code>pk_test_</code></p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Test Secret Key <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.configuration.test_secret_key"
                  type="password"
                  :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm', errors['configuration.test_secret_key'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                  placeholder="sk_test_..."
                />
                <p v-if="errors['configuration.test_secret_key']" class="mt-1 text-sm text-red-600">{{ errors['configuration.test_secret_key'] }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Starts with <code>sk_test_</code> — keep secure</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Test Webhook Secret <span class="text-gray-400 text-xs">(Optional)</span>
                </label>
                <input
                  v-model="form.configuration.test_webhook_secret"
                  type="password"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                  placeholder="whsec_..."
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Starts with <code>whsec_</code></p>
              </div>
            </template>

            <!-- Live keys -->
            <template v-else>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Live Publishable Key <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.configuration.publishable_key"
                  type="text"
                  :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm', errors['configuration.publishable_key'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                  placeholder="pk_live_..."
                />
                <p v-if="errors['configuration.publishable_key']" class="mt-1 text-sm text-red-600">{{ errors['configuration.publishable_key'] }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Starts with <code>pk_live_</code></p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Live Secret Key <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.configuration.secret_key"
                  type="password"
                  :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm', errors['configuration.secret_key'] ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                  placeholder="sk_live_..."
                />
                <p v-if="errors['configuration.secret_key']" class="mt-1 text-sm text-red-600">{{ errors['configuration.secret_key'] }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Starts with <code>sk_live_</code> — keep secure</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Live Webhook Secret <span class="text-gray-400 text-xs">(Optional)</span>
                </label>
                <input
                  v-model="form.configuration.webhook_secret"
                  type="password"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                  placeholder="whsec_..."
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Starts with <code>whsec_</code></p>
              </div>
            </template>

          </div>
        </div>

        <!-- Webhook URL Info -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Webhook Configuration</h2>
          <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <p class="text-sm text-green-900 dark:text-green-200 mb-2">
              <strong>Webhook URL:</strong> Configure this URL in your Stripe Dashboard → Developers → Webhooks
            </p>
            <div class="bg-white dark:bg-gray-800 border border-green-300 dark:border-green-700 rounded px-3 py-2">
              <code class="text-sm text-green-800 dark:text-green-300 break-all">{{ webhookUrl }}</code>
            </div>
            <p class="mt-2 text-xs text-green-700 dark:text-green-400">
              Stripe will send payment events to this URL for real-time order updates.
            </p>
          </div>
        </div>

        <!-- Gateway Notice -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <p class="text-sm text-blue-900 dark:text-blue-200">
              <strong>Payment Processing:</strong> Mobile app customers pay via the Stripe Flutter SDK (Payment Sheet). Web customers are redirected to the Stripe Checkout page.
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

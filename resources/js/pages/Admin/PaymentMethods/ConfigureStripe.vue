<script setup lang="ts">
import { ref, computed } from 'vue'
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
  name: props.method.name || 'Stripe',
  description: props.method.description || '',
  instructions: props.method.instructions || '',
  configuration: {
    public_key: props.method.configuration?.public_key || '',
    enable_3d_secure: props.method.configuration?.enable_3d_secure ?? true,
    save_payment_method: props.method.configuration?.save_payment_method ?? true,
    payment_methods: {
      card: props.method.configuration?.payment_methods?.card ?? true,
      apple_pay: props.method.configuration?.payment_methods?.apple_pay ?? true,
      google_pay: props.method.configuration?.payment_methods?.google_pay ?? true,
      ideal: props.method.configuration?.payment_methods?.ideal ?? false,
      bancontact: props.method.configuration?.payment_methods?.bancontact ?? false,
      eps: props.method.configuration?.payment_methods?.eps ?? false,
      giropay: props.method.configuration?.payment_methods?.giropay ?? false,
      klarna: props.method.configuration?.payment_methods?.klarna ?? false,
      p24: props.method.configuration?.payment_methods?.p24 ?? false,
      alipay: props.method.configuration?.payment_methods?.alipay ?? false,
    },
  },
})

const save = () => {
  form.post(`/admin/payment-methods/stripe/save`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <AdminLayout title="Payment Methods - Stripe">
    <Head title="Configure Stripe" />

    <div class="p-6">
      <!-- Page Header -->
      <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
          <a href="/admin/settings/payment-methods" class="text-blue-600 hover:text-blue-700 font-medium">← Payment Methods</a>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Stripe Configuration</h1>
        <p class="mt-1 text-sm text-gray-500">Configure Stripe payment gateway settings</p>
      </div>

      <!-- Configuration Form -->
      <form @submit.prevent="save" class="bg-white rounded-lg shadow-sm border border-gray-200">
        <!-- Basic Information Section -->
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>

          <div class="space-y-4">
            <!-- Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Method Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                :class="['w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent', errors.name ? 'border-red-500' : 'border-gray-300']"
                placeholder="Stripe"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Describe this payment method..."
              ></textarea>
            </div>

            <!-- Instructions -->
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

        <!-- Stripe API Keys Section -->
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Stripe API Keys</h2>

          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <p class="text-sm text-blue-900">
              <strong>Get your keys:</strong> Log in to your Stripe Dashboard → Settings → API Keys
            </p>
          </div>

          <div class="space-y-4">
            <!-- Public Key -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Publishable Key <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.configuration.public_key"
                type="text"
                :class="['w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm', errors['configuration.public_key'] ? 'border-red-500' : 'border-gray-300']"
                placeholder="pk_live_..."
              />
              <p v-if="errors['configuration.public_key']" class="mt-1 text-sm text-red-600">{{ errors['configuration.public_key'] }}</p>
              <p class="mt-1 text-xs text-gray-500">Starts with <code class="bg-gray-100 px-1 rounded">pk_</code></p>
            </div>
          </div>
        </div>

        <!-- Payment Features Section -->
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Features</h2>

          <div class="space-y-4">
            <!-- Enable 3D Secure -->
            <div class="flex items-center">
              <input
                v-model="form.configuration.enable_3d_secure"
                type="checkbox"
                id="enable_3d_secure"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="enable_3d_secure" class="ml-3 flex flex-col">
                <span class="block text-sm font-medium text-gray-700">Enable 3D Secure</span>
                <span class="text-xs text-gray-500">Adds an extra layer of security to card payments</span>
              </label>
            </div>

            <!-- Save Payment Method -->
            <div class="flex items-center">
              <input
                v-model="form.configuration.save_payment_method"
                type="checkbox"
                id="save_payment_method"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="save_payment_method" class="ml-3 flex flex-col">
                <span class="block text-sm font-medium text-gray-700">Save Payment Methods</span>
                <span class="text-xs text-gray-500">Allow customers to save payment methods for future purchases</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Supported Payment Methods Section -->
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Supported Payment Methods</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Card -->
            <div class="flex items-center">
              <input
                v-model="form.configuration.payment_methods.card"
                type="checkbox"
                id="card"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="card" class="ml-3 block text-sm font-medium text-gray-700">Credit/Debit Card</label>
            </div>

            <!-- Apple Pay -->
            <div class="flex items-center">
              <input
                v-model="form.configuration.payment_methods.apple_pay"
                type="checkbox"
                id="apple_pay"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="apple_pay" class="ml-3 block text-sm font-medium text-gray-700">Apple Pay</label>
            </div>

            <!-- Google Pay -->
            <div class="flex items-center">
              <input
                v-model="form.configuration.payment_methods.google_pay"
                type="checkbox"
                id="google_pay"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="google_pay" class="ml-3 block text-sm font-medium text-gray-700">Google Pay</label>
            </div>

            <!-- iDEAL (Netherlands) -->
            <div class="flex items-center">
              <input
                v-model="form.configuration.payment_methods.ideal"
                type="checkbox"
                id="ideal"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="ideal" class="ml-3 block text-sm font-medium text-gray-700">iDEAL (Netherlands)</label>
            </div>

            <!-- Bancontact (Belgium) -->
            <div class="flex items-center">
              <input
                v-model="form.configuration.payment_methods.bancontact"
                type="checkbox"
                id="bancontact"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="bancontact" class="ml-3 block text-sm font-medium text-gray-700">Bancontact (Belgium)</label>
            </div>

            <!-- EPS (Austria) -->
            <div class="flex items-center">
              <input
                v-model="form.configuration.payment_methods.eps"
                type="checkbox"
                id="eps"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="eps" class="ml-3 block text-sm font-medium text-gray-700">EPS (Austria)</label>
            </div>

            <!-- Giropay (Germany) -->
            <div class="flex items-center">
              <input
                v-model="form.configuration.payment_methods.giropay"
                type="checkbox"
                id="giropay"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="giropay" class="ml-3 block text-sm font-medium text-gray-700">Giropay (Germany)</label>
            </div>

            <!-- Klarna -->
            <div class="flex items-center">
              <input
                v-model="form.configuration.payment_methods.klarna"
                type="checkbox"
                id="klarna"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="klarna" class="ml-3 block text-sm font-medium text-gray-700">Klarna (Buy Now Pay Later)</label>
            </div>

            <!-- Przelewy24 (Poland) -->
            <div class="flex items-center">
              <input
                v-model="form.configuration.payment_methods.p24"
                type="checkbox"
                id="p24"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="p24" class="ml-3 block text-sm font-medium text-gray-700">Przelewy24 (Poland)</label>
            </div>

            <!-- Alipay (China) -->
            <div class="flex items-center">
              <input
                v-model="form.configuration.payment_methods.alipay"
                type="checkbox"
                id="alipay"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="alipay" class="ml-3 block text-sm font-medium text-gray-700">Alipay (China)</label>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="p-6 bg-gray-50 border-t border-gray-200 flex justify-between">
          <a href="/admin/settings/payment-methods" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
            Cancel
          </a>
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


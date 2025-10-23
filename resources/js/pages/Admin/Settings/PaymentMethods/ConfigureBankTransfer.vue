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
  name: props.method.name || 'Bank Transfer',
  description: props.method.description || '',
  instructions: props.method.instructions || '',
  configuration: {
    bank_name: props.method.configuration?.bank_name || '',
    account_holder_name: props.method.configuration?.account_holder_name || '',
    account_number: props.method.configuration?.account_number || '',
    account_type: props.method.configuration?.account_type || 'checking',
    iban: props.method.configuration?.iban || '',
    swift_code: props.method.configuration?.swift_code || '',
    ifsc_code: props.method.configuration?.ifsc_code || '',
    routing_number: props.method.configuration?.routing_number || '',
    bank_address: props.method.configuration?.bank_address || '',
    correspondence_bank: props.method.configuration?.correspondence_bank || '',
    correspondent_swift: props.method.configuration?.correspondent_swift || '',
    reference_format: props.method.configuration?.reference_format || 'order_id',
    verification_required: props.method.configuration?.verification_required ?? false,
    verification_days: props.method.configuration?.verification_days || 3,
    min_order_amount: props.method.configuration?.min_order_amount || 0,
    max_order_amount: props.method.configuration?.max_order_amount || 100000,
    enabled_countries: props.method.configuration?.enabled_countries || [],
    fees_buyer: props.method.configuration?.fees_buyer ?? false,
    fee_amount: props.method.configuration?.fee_amount || 0,
    fee_type: props.method.configuration?.fee_type || 'fixed',
  },
})

const save = () => {
  form.post(`/admin/settings/payment-methods/bank_transfer/save`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <AdminLayout title="Payment Methods - Bank Transfer">
    <Head title="Configure Bank Transfer" />

    <div class="p-6">
      <!-- Page Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl md:text-3xl text-gray-800 font-bold">Bank Transfer Configuration</h1>
            <p class="text-sm text-gray-600 mt-1">Configure bank account details and payment settings for bank transfer method</p>
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
                placeholder="Bank Transfer"
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

        <!-- Bank Account Details Section -->
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Bank Account Details</h2>

          <div class="space-y-4">
            <!-- Bank Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Bank Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.configuration.bank_name"
                type="text"
                :class="['w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent', errors['configuration.bank_name'] ? 'border-red-500' : 'border-gray-300']"
                placeholder="e.g., First National Bank"
              />
              <p v-if="errors['configuration.bank_name']" class="mt-1 text-sm text-red-600">{{ errors['configuration.bank_name'] }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Account Holder Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Account Holder Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.configuration.account_holder_name"
                  type="text"
                  :class="['w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent', errors['configuration.account_holder_name'] ? 'border-red-500' : 'border-gray-300']"
                  placeholder="Full name on account"
                />
                <p v-if="errors['configuration.account_holder_name']" class="mt-1 text-sm text-red-600">{{ errors['configuration.account_holder_name'] }}</p>
              </div>

              <!-- Account Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Account Type</label>
                <select
                  v-model="form.configuration.account_type"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="checking">Checking</option>
                  <option value="savings">Savings</option>
                  <option value="business">Business</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Account Number -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Account Number <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.configuration.account_number"
                  type="text"
                  :class="['w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent', errors['configuration.account_number'] ? 'border-red-500' : 'border-gray-300']"
                  placeholder="1234567890"
                />
                <p v-if="errors['configuration.account_number']" class="mt-1 text-sm text-red-600">{{ errors['configuration.account_number'] }}</p>
              </div>

              <!-- Routing Number -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Routing Number (US)</label>
                <input
                  v-model="form.configuration.routing_number"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="021000021"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- IBAN -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">IBAN (International)</label>
                <input
                  v-model="form.configuration.iban"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="DE89370400440532013000"
                />
              </div>

              <!-- SWIFT Code -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SWIFT Code</label>
                <input
                  v-model="form.configuration.swift_code"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="DEUTDEFF"
                />
              </div>
            </div>

            <!-- IFSC Code (India) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">IFSC Code (India)</label>
              <input
                v-model="form.configuration.ifsc_code"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="SBIN0001234"
                maxlength="11"
              />
              <p class="mt-1 text-xs text-gray-500">Indian Financial System Code - 11 character code for Indian banks</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Bank Address</label>
              <textarea
                v-model="form.configuration.bank_address"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Full address of the bank..."
              ></textarea>
            </div>

            <!-- Correspondent Bank Details -->
            <div class="pt-4 border-t border-gray-200">
              <h3 class="text-sm font-semibold text-gray-900 mb-3">Correspondent Bank (Optional)</h3>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Correspondent Bank Name</label>
                  <input
                    v-model="form.configuration.correspondence_bank"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="For international transfers"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Correspondent SWIFT Code</label>
                  <input
                    v-model="form.configuration.correspondent_swift"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="SWIFT code of correspondent"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Transfer Settings Section -->
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Transfer Settings</h2>

          <div class="space-y-4">
            <!-- Reference Format -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Reference Format</label>
              <select
                v-model="form.configuration.reference_format"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="order_id">Order ID</option>
                <option value="invoice_number">Invoice Number</option>
                <option value="custom">Custom</option>
              </select>
              <p class="text-xs text-gray-500 mt-1">Format for bank transfer reference field</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Verification Required -->
              <div class="flex items-center">
                <input
                  v-model="form.configuration.verification_required"
                  type="checkbox"
                  class="h-4 w-4 border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                />
                <label class="ml-2 text-sm font-medium text-gray-700">Verification Required</label>
              </div>

              <!-- Verification Days -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Verification Days</label>
                <input
                  v-model.number="form.configuration.verification_days"
                  type="number"
                  min="1"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="3"
                />
              </div>

              <!-- Reference Format (Custom) -->
              <div></div>
            </div>
          </div>
        </div>

        <!-- Order Amount Limits Section -->
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Amount Limits</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Minimum Order Amount -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Order Amount</label>
              <input
                v-model.number="form.configuration.min_order_amount"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="0.00"
              />
            </div>

            <!-- Maximum Order Amount -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Order Amount</label>
              <input
                v-model.number="form.configuration.max_order_amount"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="100000.00"
              />
            </div>
          </div>
        </div>

        <!-- Fees Section -->
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Fees</h2>

          <div class="space-y-4">
            <div class="flex items-center">
              <input
                v-model="form.configuration.fees_buyer"
                type="checkbox"
                class="h-4 w-4 border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
              />
              <label class="ml-2 text-sm font-medium text-gray-700">Buyer Pays Transfer Fees</label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Fee Amount -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fee Amount</label>
                <input
                  v-model.number="form.configuration.fee_amount"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="0.00"
                />
              </div>

              <!-- Fee Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fee Type</label>
                <select
                  v-model="form.configuration.fee_type"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="fixed">Fixed Amount</option>
                  <option value="percentage">Percentage</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3 justify-end">
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

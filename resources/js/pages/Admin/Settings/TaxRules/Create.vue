<template>
  <AdminLayout title="Create Tax Rule">
    <template #default>
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Create Tax Rule</h1>
          <p class="mt-2 text-sm text-gray-600">Add a new tax rule to your store configuration</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow p-6">
          <form @submit.prevent="submit">
            <!-- Rule Name -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Rule Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="e.g., US Standard Tax"
                required
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <!-- Tax Class -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Tax Class <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.tax_class_id"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Select Tax Class...</option>
                <option v-for="taxClass in taxClasses" :key="taxClass.id" :value="taxClass.id">
                  {{ taxClass.name }}
                </option>
              </select>
              <p v-if="errors.tax_class_id" class="mt-1 text-sm text-red-600">{{ errors.tax_class_id }}</p>
            </div>

            <!-- Tax Zone -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Tax Zone <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.tax_zone_id"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Select Tax Zone...</option>
                <option v-for="zone in taxZones" :key="zone.id" :value="zone.id">
                  {{ zone.name }}
                </option>
              </select>
              <p v-if="errors.tax_zone_id" class="mt-1 text-sm text-red-600">{{ errors.tax_zone_id }}</p>
            </div>

            <!-- Tax Rate -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Tax Rate <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.tax_rate_id"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Select Tax Rate...</option>
                <option v-for="rate in taxRates" :key="rate.id" :value="rate.id">
                  {{ rate.name }} ({{ rate.percentage }}%)
                </option>
              </select>
              <p v-if="errors.tax_rate_id" class="mt-1 text-sm text-red-600">{{ errors.tax_rate_id }}</p>
            </div>

            <!-- Priority -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Priority
              </label>
              <input
                v-model.number="form.priority"
                type="number"
                min="0"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="0"
              />
              <p class="mt-1 text-xs text-gray-500">Lower numbers have higher priority in tax calculations</p>
              <p v-if="errors.priority" class="mt-1 text-sm text-red-600">{{ errors.priority }}</p>
            </div>

            <!-- Calculate Shipping -->
            <div class="mb-6">
              <label class="flex items-center">
                <input
                  v-model="form.calculate_shipping"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <span class="ml-2 text-sm text-gray-700">Include shipping in tax calculation</span>
              </label>
            </div>

            <!-- Active Status -->
            <div class="mb-6">
              <label class="flex items-center">
                <input
                  v-model="form.is_active"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <span class="ml-2 text-sm text-gray-700">Active</span>
              </label>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
              <Link
                href="/admin/settings/tax-rules"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </Link>
              <button
                type="submit"
                :disabled="processing"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="processing">Creating...</span>
                <span v-else>Create Tax Rule</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </template>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

interface TaxClass {
  id: number
  name: string
}

interface TaxZone {
  id: number
  name: string
}

interface TaxRate {
  id: number
  name: string
  percentage: string
}

interface Props {
  taxClasses: TaxClass[]
  taxZones: TaxZone[]
  taxRates: TaxRate[]
}

const props = defineProps<Props>()

const form = reactive({
  name: '',
  tax_class_id: '',
  tax_zone_id: '',
  tax_rate_id: '',
  priority: 0,
  calculate_shipping: false,
  is_active: true,
})

const errors = ref<Record<string, string>>({})
const processing = ref(false)

const submit = () => {
  processing.value = true
  errors.value = {}

  router.post('/admin/settings/tax-rules', form, {
    onError: (err) => {
      errors.value = err
      processing.value = false
    },
    onFinish: () => {
      processing.value = false
    },
  })
}
</script>

<template>
  <Head title="Edit Tax Rule" />

  <AdminLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <Link
              href="/admin/settings/tax-rules"
              class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors"
            >
              <ArrowLeft class="w-5 h-5" />
            </Link>
            <h2 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300">
              Edit Tax Rule
            </h2>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400 ml-7">
            Update tax rule configuration
          </p>
        </div>
      </div>

      <!-- Main Form -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <form @submit.prevent="submit" class="p-6 space-y-6">
          <!-- Basic Info Section -->
          <div class="space-y-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center gap-2 pb-4 border-b border-gray-100 dark:border-gray-700">
              <Sparkles class="w-5 h-5 text-blue-500" />
              Rule Details
            </h3>

            <!-- Rule Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Rule Name <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input
                  v-model="form.name"
                  type="text"
                  class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white transition-all pl-10"
                  placeholder="e.g., US Standard Tax"
                  required
                />
                <Type class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
              </div>
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Tax Class -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                  Tax Class <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <select
                    v-model="form.tax_class_id"
                    class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white appearance-none pl-10"
                    required
                  >
                    <option value="">Select Tax Class...</option>
                    <option v-for="taxClass in taxClasses" :key="taxClass.id" :value="taxClass.id">
                      {{ taxClass.name }}
                    </option>
                  </select>
                  <Tag class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                  <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none">
                    <ChevronDown class="h-4 w-4 text-gray-400" />
                  </div>
                </div>
                <p v-if="errors.tax_class_id" class="mt-1 text-sm text-red-600">{{ errors.tax_class_id }}</p>
              </div>

              <!-- Tax Zone -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                  Tax Zone <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <select
                    v-model="form.tax_zone_id"
                    class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white appearance-none pl-10"
                    required
                  >
                    <option value="">Select Tax Zone...</option>
                    <option v-for="zone in taxZones" :key="zone.id" :value="zone.id">
                      {{ zone.name }}
                    </option>
                  </select>
                  <Map class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                  <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none">
                    <ChevronDown class="h-4 w-4 text-gray-400" />
                  </div>
                </div>
                <p v-if="errors.tax_zone_id" class="mt-1 text-sm text-red-600">{{ errors.tax_zone_id }}</p>
              </div>

              <!-- Tax Rate -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                  Tax Rate <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <select
                    v-model="form.tax_rate_id"
                    class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white appearance-none pl-10"
                    required
                  >
                    <option value="">Select Tax Rate...</option>
                    <option v-for="rate in taxRates" :key="rate.id" :value="rate.id">
                      {{ rate.name }} ({{ rate.percentage }}%)
                    </option>
                  </select>
                  <Percent class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                  <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none">
                    <ChevronDown class="h-4 w-4 text-gray-400" />
                  </div>
                </div>
                <p v-if="errors.tax_rate_id" class="mt-1 text-sm text-red-600">{{ errors.tax_rate_id }}</p>
              </div>

              <!-- Priority -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                  Priority
                </label>
                <div class="relative">
                  <input
                    v-model.number="form.priority"
                    type="number"
                    min="0"
                    class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white transition-all pl-10"
                    placeholder="0"
                  />
                  <Layers class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                </div>
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Lower numbers have higher priority in calculation.</p>
                <p v-if="errors.priority" class="mt-1 text-sm text-red-600">{{ errors.priority }}</p>
              </div>
            </div>
          </div>

          <!-- Settings Section -->
          <div class="space-y-6 pt-6 border-t border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center gap-2 pb-4">
              <Settings class="w-5 h-5 text-blue-500" />
              Settings
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Calculate Shipping -->
              <div class="relative flex items-start p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 hover:border-blue-500 dark:hover:border-blue-500 transition-colors cursor-pointer" @click="form.calculate_shipping = !form.calculate_shipping">
                <div class="flex items-center h-5">
                  <input
                    v-model="form.calculate_shipping"
                    type="checkbox"
                    class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:bg-gray-700"
                    @click.stop
                  />
                </div>
                <div class="ml-3 text-sm">
                  <label class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer">Include Shipping</label>
                  <p class="text-gray-500 dark:text-gray-400 text-xs mt-0.5">Include shipping cost in tax calculation.</p>
                </div>
              </div>

              <!-- Active Status -->
              <div class="relative flex items-start p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 hover:border-blue-500 dark:hover:border-blue-500 transition-colors cursor-pointer" @click="form.is_active = !form.is_active">
                <div class="flex items-center h-5">
                  <input
                    v-model="form.is_active"
                    type="checkbox"
                    class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:bg-gray-700"
                    @click.stop
                  />
                </div>
                <div class="ml-3 text-sm">
                  <label class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer">Active Status</label>
                  <p class="text-gray-500 dark:text-gray-400 text-xs mt-0.5">Enable or disable this tax rule.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-700">
            <Link
              href="/admin/settings/tax-rules"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 font-medium text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="processing"
              class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium text-sm transition-all shadow-sm flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
            >
              <Save v-if="!processing" class="w-4 h-4" />
              <div v-else class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
              <span>{{ processing ? 'Updating...' : 'Update Tax Rule' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router, Link, Head } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { 
  ArrowLeft, 
  Sparkles, 
  Settings, 
  Save, 
  Type, 
  Tag, 
  Map, 
  Percent, 
  Layers,
  ChevronDown
} from 'lucide-vue-next'

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

interface TaxRule {
  id: number
  name: string
  tax_class_id: number
  tax_zone_id: number
  tax_rate_id: number
  priority: number
  calculate_shipping: boolean
  is_active: boolean
}

interface Props {
  taxRule: TaxRule
  taxClasses: TaxClass[]
  taxZones: TaxZone[]
  taxRates: TaxRate[]
}

const props = defineProps<Props>()

const form = reactive({
  name: props.taxRule.name,
  tax_class_id: props.taxRule.tax_class_id,
  tax_zone_id: props.taxRule.tax_zone_id,
  tax_rate_id: props.taxRule.tax_rate_id,
  priority: props.taxRule.priority,
  calculate_shipping: props.taxRule.calculate_shipping,
  is_active: props.taxRule.is_active,
})

const errors = ref<Record<string, string>>({})
const processing = ref(false)

const submit = () => {
  processing.value = true
  errors.value = {}

  router.put(`/admin/settings/tax-rules/${props.taxRule.id}`, form, {
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

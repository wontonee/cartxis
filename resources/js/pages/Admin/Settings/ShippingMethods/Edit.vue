<template>
  <Head title="Edit Shipping Method" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300">
            Edit Shipping Method
          </h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <Truck class="w-4 h-4" />
            {{ method.name }}
          </p>
        </div>
        <Link
          href="/admin/settings/shipping-methods"
          class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-sm"
        >
          <ChevronLeft class="w-4 h-4 mr-2" />
          Back to List
        </Link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <form @submit.prevent="submit" class="space-y-6">
              <!-- Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Method Name</label>
                <input
                  v-model="form.name"
                  type="text"
                  class="block w-full px-3 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow dark:text-white"
                />
                <p v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name }}</p>
              </div>

              <!-- Slug -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Slug</label>
                <input
                  v-model="form.slug"
                  type="text"
                  class="block w-full px-3 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow dark:text-white"
                />
                <p v-if="errors.slug" class="text-red-600 text-sm mt-1">{{ errors.slug }}</p>
              </div>

              <!-- Type (Read-only) -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Shipping Type</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <component :is="form.type === 'flat-rate' ? Package : Scale" class="h-5 w-5 text-gray-400" />
                  </div>
                  <input
                    :value="form.type === 'flat-rate' ? 'Flat Rate' : 'Calculated'"
                    type="text"
                    disabled
                    class="block w-full pl-10 pr-3 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-500 dark:text-gray-400 shadow-sm cursor-not-allowed"
                  />
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">Type cannot be changed after creation</p>
              </div>

              <!-- Cost Fields -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Base Cost ($)</label>
                  <input
                    v-model="form.base_cost"
                    type="number"
                    step="0.01"
                    min="0"
                    class="block w-full px-3 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow dark:text-white"
                  />
                  <p v-if="errors.base_cost" class="text-red-600 text-sm mt-1">{{ errors.base_cost }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Cost per Kg ($)</label>
                  <input
                    v-model="form.cost_per_kg"
                    type="number"
                    step="0.0001"
                    min="0"
                    class="block w-full px-3 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow dark:text-white"
                  />
                  <p v-if="errors.cost_per_kg" class="text-red-600 text-sm mt-1">{{ errors.cost_per_kg }}</p>
                </div>
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Description</label>
                <textarea
                  v-model="form.description"
                  rows="4"
                  class="block w-full px-3 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow dark:text-white"
                ></textarea>
                <p v-if="errors.description" class="text-red-600 text-sm mt-1">{{ errors.description }}</p>
              </div>

              <!-- Default -->
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <label class="flex items-center gap-3 cursor-pointer">
                  <input
                    v-model="form.is_default"
                    type="checkbox"
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                  />
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Set as default shipping method</span>
                </label>
              </div>

              <!-- Actions -->
              <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <Link
                  href="/admin/settings/shipping-methods"
                  class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 font-medium text-sm transition-colors"
                >
                  Cancel
                </Link>
                <button
                  type="submit"
                  :disabled="loading"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <Save class="w-4 h-4 mr-2" />
                  {{ loading ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Status Card -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <Activity class="w-5 h-5 text-gray-400" />
              Status
            </h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-600 dark:text-gray-400">Current Status</span>
                <span :class="[
                  'px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
                  method.status === 'active' 
                    ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' 
                    : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                ]">
                  {{ method.status }}
                </span>
              </div>
              <button
                @click="toggleStatus"
                :disabled="loading"
                class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 font-medium text-sm transition-colors disabled:opacity-50"
              >
                <Power class="w-4 h-4 mr-2" />
                {{ method.status === 'active' ? 'Deactivate Method' : 'Activate Method' }}
              </button>
            </div>
          </div>

          <!-- Info Card -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Details</h3>
            <div class="space-y-3 text-sm">
              <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Created</span>
                <div class="text-gray-900 dark:text-white font-medium flex items-center gap-1.5">
                  <Calendar class="w-3.5 h-3.5 text-gray-400" />
                  {{ formatDate(method.created_at) }}
                </div>
              </div>
              <div v-if="method.rates && method.rates.length" class="flex justify-between items-center pt-3 border-t border-gray-100 dark:border-gray-700">
                <span class="text-gray-600 dark:text-gray-400">Rates</span>
                <div class="text-gray-900 dark:text-white font-medium">
                  {{ method.rates.length }} configured
                </div>
              </div>
            </div>
          </div>

          <!-- Rates Preview -->
          <div v-if="method.type === 'calculated' && method.rates && method.rates.length" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Shipping Rates</h3>
            <div class="space-y-2 text-sm max-h-48 overflow-y-auto">
              <div v-for="rate in method.rates" :key="rate.id" class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-700 last:border-0 last:pb-0">
                <div class="flex-1">
                  <div class="font-medium text-gray-900 dark:text-white">{{ rate.country }}</div>
                  <div class="text-gray-500 text-xs">{{ rate.min_weight }}-{{ rate.max_weight }}kg</div>
                </div>
                <div class="text-right">
                  <div class="font-medium text-gray-900 dark:text-white">${{ parseFloat(rate.base_cost).toFixed(2) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link, usePage, router, Head } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { 
  Truck, 
  ChevronLeft, 
  Save, 
  Activity, 
  Calendar, 
  Package, 
  Scale,
  Power
} from 'lucide-vue-next'
import axios from 'axios'

interface ShippingMethod {
  id: number
  name: string
  slug: string
  type: string
  status: string
  base_cost: number
  cost_per_kg: number
  description: string
  is_default: boolean
  rates: any[]
  created_at: string
  updated_at: string
}

const page = usePage()
const method = ref<ShippingMethod>(page.props.method as ShippingMethod)

const form = ref({
  name: method.value.name,
  slug: method.value.slug,
  type: method.value.type,
  base_cost: method.value.base_cost,
  cost_per_kg: method.value.cost_per_kg,
  description: method.value.description,
  is_default: method.value.is_default,
})

const errors = ref<Record<string, string>>({})
const loading = ref(false)

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const submit = () => {
  loading.value = true
  errors.value = {}

  router.put(`/admin/settings/shipping-methods/${method.value.id}`, form.value, {
    onSuccess: () => {
      loading.value = false
    },
    onError: (errs: Record<string, string>) => {
      errors.value = errs
      loading.value = false
    },
  })
}

const toggleStatus = async () => {
  loading.value = true
  try {
    const response = await axios.post(`/admin/settings/shipping-methods/${method.value.id}/toggle-status`)
    method.value.status = response.data.status
  } catch (error) {
    console.error('Error toggling status:', error)
  } finally {
    loading.value = false
  }
}
</script>

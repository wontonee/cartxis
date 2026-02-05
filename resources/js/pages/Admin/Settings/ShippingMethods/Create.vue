<script setup lang="ts">
import { ref } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { 
  Truck, 
  ChevronLeft, 
  Save, 
  Info,
  Package,
  Scale
} from 'lucide-vue-next'

const form = ref({
  name: '',
  slug: '',
  type: 'flat-rate',
  base_cost: '0.00',
  cost_per_kg: '0.00',
  description: '',
  is_default: false,
})

const errors = ref<Record<string, string>>({})
const loading = ref(false)

const submit = () => {
  loading.value = true
  errors.value = {}

  router.post('/admin/settings/shipping-methods', form.value, {
    onSuccess: () => {
      loading.value = false
    },
    onError: (errs: Record<string, string>) => {
      errors.value = errs
      loading.value = false
    },
  })
}
</script>

<template>
  <Head title="Create Shipping Method" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300">
            Create Shipping Method
          </h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <Truck class="w-4 h-4" />
            Add a new shipping method to your store
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
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <form @submit.prevent="submit" class="space-y-6">
              <!-- Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Method Name</label>
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="e.g., Standard Shipping"
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
                  placeholder="e.g., standard-shipping"
                  class="block w-full px-3 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow dark:text-white"
                />
                <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">URL-friendly identifier</p>
                <p v-if="errors.slug" class="text-red-600 text-sm mt-1">{{ errors.slug }}</p>
              </div>

              <!-- Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Shipping Type</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="relative flex items-start p-4 border rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors"
                    :class="form.type === 'flat-rate' ? 'border-blue-500 ring-1 ring-blue-500 bg-blue-50/50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800'"
                    @click="form.type = 'flat-rate'"
                  >
                    <div class="flex items-center h-5">
                      <input
                        v-model="form.type"
                        value="flat-rate"
                        type="radio"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600"
                      />
                    </div>
                    <div class="ml-3">
                      <label class="font-medium text-gray-900 dark:text-white flex items-center gap-2">
                        <Package class="w-4 h-4 text-gray-500" />
                        Flat Rate
                      </label>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Fixed price regardless of weight</p>
                    </div>
                  </div>

                  <div class="relative flex items-start p-4 border rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors"
                    :class="form.type === 'calculated' ? 'border-blue-500 ring-1 ring-blue-500 bg-blue-50/50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800'"
                    @click="form.type = 'calculated'"
                  >
                    <div class="flex items-center h-5">
                      <input
                        v-model="form.type"
                        value="calculated"
                        type="radio"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600"
                      />
                    </div>
                    <div class="ml-3">
                      <label class="font-medium text-gray-900 dark:text-white flex items-center gap-2">
                        <Scale class="w-4 h-4 text-gray-500" />
                        Calculated
                      </label>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Based on weight and rules</p>
                    </div>
                  </div>
                </div>
                <p v-if="errors.type" class="text-red-600 text-sm mt-1">{{ errors.type }}</p>
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
                    placeholder="0.00"
                    class="block w-full px-3 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow dark:text-white"
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
                    placeholder="0.0000"
                    class="block w-full px-3 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow dark:text-white"
                  />
                  <p v-if="errors.cost_per_kg" class="text-red-600 text-sm mt-1">{{ errors.cost_per_kg }}</p>
                </div>
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Description</label>
                <textarea
                  v-model="form.description"
                  placeholder="Describe this shipping method..."
                  rows="4"
                  class="block w-full px-3 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow dark:text-white"
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
                  {{ loading ? 'Creating...' : 'Create Method' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800">
            <div class="flex">
              <div class="flex-shrink-0">
                <Info class="h-5 w-5 text-blue-400" aria-hidden="true" />
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">
                  Shipping Configuration
                </h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-400 space-y-3">
                  <p>
                    <span class="font-semibold block mb-1">Flat Rate:</span>
                    Charges a fixed base cost regardless of the total weight of the order.
                  </p>
                  <p>
                    <span class="font-semibold block mb-1">Calculated:</span>
                    Combines the Base Cost with the Cost per Kg multiplied by the total weight.
                  </p>
                  <p class="text-xs opacity-75">
                    Example: Base $5 + ($2/kg * 2kg) = $9 Total
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

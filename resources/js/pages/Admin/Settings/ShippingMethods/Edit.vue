<template>
  <AdminLayout title="Edit Shipping Method">
    <template #default>
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Shipping Method</h1>
            <p class="text-gray-600 mt-1">{{ method.name }}</p>
          </div>
          <Link
            href="/admin/settings/shipping-methods"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Shipping Methods
          </Link>
        </div>
      </div>

      <!-- Main Form -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow-sm p-8 space-y-6">
            <form @submit.prevent="submit">
              <!-- Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Method Name</label>
                <input
                  v-model="form.name"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name }}</p>
              </div>

              <!-- Slug -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                <input
                  v-model="form.slug"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="errors.slug" class="text-red-600 text-sm mt-1">{{ errors.slug }}</p>
              </div>

              <!-- Type (Read-only) -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Type</label>
                <input
                  :value="form.type === 'flat-rate' ? 'Flat Rate' : 'Calculated'"
                  type="text"
                  disabled
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600"
                />
                <p class="text-gray-500 text-sm mt-1">Type cannot be changed after creation</p>
              </div>

              <!-- Base Cost -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Base Cost ($)</label>
                <input
                  v-model="form.base_cost"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="errors.base_cost" class="text-red-600 text-sm mt-1">{{ errors.base_cost }}</p>
              </div>

              <!-- Cost Per KG -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cost per Kilogram ($)</label>
                <input
                  v-model="form.cost_per_kg"
                  type="number"
                  step="0.0001"
                  min="0"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="errors.cost_per_kg" class="text-red-600 text-sm mt-1">{{ errors.cost_per_kg }}</p>
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea
                  v-model="form.description"
                  rows="4"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                ></textarea>
                <p v-if="errors.description" class="text-red-600 text-sm mt-1">{{ errors.description }}</p>
              </div>

              <!-- Default -->
              <div>
                <label class="flex items-center gap-3">
                  <input
                    v-model="form.is_default"
                    type="checkbox"
                    class="w-4 h-4 border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                  />
                  <span class="text-sm font-medium text-gray-700">Set as default shipping method</span>
                </label>
              </div>

              <!-- Buttons -->
              <div class="flex gap-3 pt-6 border-t">
                <button
                  type="submit"
                  :disabled="loading"
                  class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50"
                >
                  {{ loading ? 'Saving...' : 'Save Changes' }}
                </button>
                <Link
                  href="/admin/settings/shipping-methods"
                  class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium"
                >
                  Cancel
                </Link>
              </div>
            </form>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Status Card -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between pb-3 border-b">
                <span class="text-gray-600">Current Status</span>
                <span
                  :class="[
                    'px-3 py-1 rounded-full text-sm font-medium',
                    method.status === 'active'
                      ? 'bg-emerald-50 text-emerald-700'
                      : 'bg-gray-100 text-gray-700'
                  ]"
                >
                  {{ method.status === 'active' ? 'Active' : 'Inactive' }}
                </span>
              </div>
              <button
                @click="toggleStatus"
                :disabled="loading"
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium disabled:opacity-50"
              >
                {{ method.status === 'active' ? 'Deactivate' : 'Activate' }}
              </button>
            </div>
          </div>

          <!-- Info Card -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Details</h3>
            <div class="space-y-3 text-sm">
              <div>
                <span class="text-gray-600">Type</span>
                <div class="text-gray-900 font-medium">
                  {{ method.type === 'flat-rate' ? 'Flat Rate' : 'Calculated' }}
                </div>
              </div>
              <div>
                <span class="text-gray-600">Default</span>
                <div class="text-gray-900 font-medium">
                  {{ method.is_default ? 'Yes' : 'No' }}
                </div>
              </div>
              <div v-if="method.rates && method.rates.length">
                <span class="text-gray-600">Rates</span>
                <div class="text-gray-900 font-medium">
                  {{ method.rates.length }} configured
                </div>
              </div>
              <div>
                <span class="text-gray-600">Created</span>
                <div class="text-gray-900 font-medium">
                  {{ formatDate(method.created_at) }}
                </div>
              </div>
            </div>
          </div>

          <!-- Rates Preview -->
          <div v-if="method.type === 'calculated' && method.rates && method.rates.length" class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Shipping Rates</h3>
            <div class="space-y-2 text-sm max-h-48 overflow-y-auto">
              <div v-for="rate in method.rates" :key="rate.id" class="flex items-center justify-between pb-2 border-b last:border-0">
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ rate.country }}</div>
                  <div class="text-gray-500 text-xs">{{ rate.min_weight }}-{{ rate.max_weight }}kg</div>
                </div>
                <div class="text-right">
                  <div class="font-medium text-gray-900">${{ parseFloat(rate.base_cost).toFixed(2) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

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
const method = ref<ShippingMethod>(page.props.method)

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
    onError: (errors: Record<string, string>) => {
      Object.assign(errors.value, errors)
      loading.value = false
    },
  })
}

const toggleStatus = async () => {
  loading.value = true
  try {
    const response = await fetch(`/admin/settings/shipping-methods/${method.value.id}/toggle-status`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    })
    const data = await response.json()
    method.value.status = data.status
  } catch (error) {
    console.error('Error toggling status:', error)
  } finally {
    loading.value = false
  }
}
</script>

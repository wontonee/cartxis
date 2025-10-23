<template>
  <AdminLayout title="Create Shipping Method">
    <template #default>
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Create Shipping Method</h1>
            <p class="text-gray-600 mt-1">Add a new shipping method to your store</p>
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
            <form @submit.prevent="submit" class="space-y-6">
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Method Name</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g., Standard Shipping"
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
              placeholder="e.g., standard-shipping"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <p class="text-gray-500 text-sm mt-1">URL-friendly identifier</p>
            <p v-if="errors.slug" class="text-red-600 text-sm mt-1">{{ errors.slug }}</p>
          </div>

          <!-- Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Type</label>
            <select
              v-model="form.type"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="flat-rate">Flat Rate (Fixed price)</option>
              <option value="calculated">Calculated (Weight-based)</option>
            </select>
            <p class="text-gray-500 text-sm mt-1">
              {{ form.type === 'flat-rate' 
                ? 'Flat rate shipping charges a fixed price regardless of weight' 
                : 'Calculated shipping charges based on weight and location' }}
            </p>
            <p v-if="errors.type" class="text-red-600 text-sm mt-1">{{ errors.type }}</p>
          </div>

          <!-- Base Cost -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Base Cost ($)</label>
            <input
              v-model="form.base_cost"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <p class="text-gray-500 text-sm mt-1">Starting cost for this shipping method</p>
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
              placeholder="0.0000"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <p class="text-gray-500 text-sm mt-1">Additional cost per kilogram of weight</p>
            <p v-if="errors.cost_per_kg" class="text-red-600 text-sm mt-1">{{ errors.cost_per_kg }}</p>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea
              v-model="form.description"
              placeholder="Describe this shipping method..."
              rows="4"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
            <p class="text-gray-500 text-sm mt-1">Optional description for internal use</p>
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
                  {{ loading ? 'Creating...' : 'Create Method' }}
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
          <!-- Info Card -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Information</h3>
            <div class="space-y-4 text-sm">
              <div>
                <h4 class="font-medium text-gray-900 mb-2">Flat Rate vs Calculated</h4>
                <p class="text-gray-600">Flat Rate charges a fixed price. Calculated pricing is based on weight and location.</p>
              </div>
              <div>
                <h4 class="font-medium text-gray-900 mb-2">Slug</h4>
                <p class="text-gray-600">URL-friendly identifier used in the system. Use lowercase letters, numbers, and hyphens.</p>
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
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

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
    onError: (errors: Record<string, string>) => {
      Object.assign(errors.value, errors)
      loading.value = false
    },
  })
}
</script>

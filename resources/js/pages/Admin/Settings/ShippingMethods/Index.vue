<template>
  <AdminLayout title="Shipping Methods">
    <template #default>
      <Head title="Shipping Methods" />
      <!-- Header -->
      <div class="mb-8 flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Shipping Methods</h1>
          <p class="text-gray-600 mt-1">Manage your shipping rates and methods</p>
        </div>
        <Link
          href="/admin/settings/shipping-methods/create"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium flex items-center gap-2"
        >
          <Plus :size="20" />
          Add Shipping Method
        </Link>
      </div>

      <!-- Filters & Search -->
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              v-model="search"
              type="text"
              placeholder="Search by name..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Type Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
            <select
              v-model="typeFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Types</option>
              <option value="flat-rate">Flat Rate</option>
              <option value="calculated">Calculated</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="statusFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Methods Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Base Cost</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Cost/kg</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Rates</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Default</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="method in filteredMethods" :key="method.id" class="hover:bg-gray-50">
              <!-- Name -->
              <td class="px-6 py-4">
                <div class="font-medium text-gray-900">{{ method.name }}</div>
                <div v-if="method.description" class="text-sm text-gray-500">{{ method.description }}</div>
              </td>

              <!-- Type -->
              <td class="px-6 py-4">
                <span
                  :class="[
                    'px-3 py-1 rounded-full text-sm font-medium',
                    method.type === 'flat-rate'
                      ? 'bg-blue-50 text-blue-700'
                      : 'bg-purple-50 text-purple-700'
                  ]"
                >
                  {{ method.type === 'flat-rate' ? 'Flat Rate' : 'Calculated' }}
                </span>
              </td>

              <!-- Base Cost -->
              <td class="px-6 py-4 text-gray-900">
                ${{ parseFloat(method.base_cost).toFixed(2) }}
              </td>

              <!-- Cost per KG -->
              <td class="px-6 py-4 text-gray-900">
                ${{ parseFloat(method.cost_per_kg).toFixed(4) }}/kg
              </td>

              <!-- Rates Count -->
              <td class="px-6 py-4">
                <span v-if="method.rates && method.rates.length > 0" class="text-gray-900">
                  {{ method.rates.length }} rates
                </span>
                <span v-else class="text-gray-500">-</span>
              </td>

              <!-- Status Toggle -->
              <td class="px-6 py-4">
                <button
                  @click="toggleStatus(method)"
                  :class="[
                    'px-3 py-1 rounded-md text-sm font-medium transition-colors',
                    method.status === 'active'
                      ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  ]"
                >
                  {{ method.status === 'active' ? 'Active' : 'Inactive' }}
                </button>
              </td>

              <!-- Default Indicator -->
              <td class="px-6 py-4">
                <button
                  v-if="!method.is_default"
                  @click="setDefault(method)"
                  title="Set as default"
                  class="text-gray-400 hover:text-yellow-500 transition-colors"
                >
                  <Star :size="20" />
                </button>
                <div v-else class="text-yellow-500">
                  <Star :size="20" fill="currentColor" />
                </div>
              </td>

              <!-- Actions -->
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <Link
                    :href="`/admin/settings/shipping-methods/${method.id}/edit`"
                    class="text-blue-600 hover:text-blue-900"
                    title="Edit"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </Link>
                  <button
                    v-if="!method.is_default"
                    @click="deleteMethod(method)"
                    class="text-red-600 hover:text-red-900"
                    title="Delete"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <div v-if="filteredMethods.length === 0" class="px-6 py-12 text-center">
          <Truck class="mx-auto text-gray-400 mb-4" :size="48" />
          <p class="text-gray-600 font-medium">No shipping methods found</p>
          <p class="text-gray-500 text-sm mt-1">Create your first shipping method to get started</p>
        </div>
      </div>
    </template>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, usePage, Head } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Plus, Star, Truck } from 'lucide-vue-next'
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
const methods = ref<ShippingMethod[]>(page.props.methods?.data || [])

const search = ref('')
const typeFilter = ref('')
const statusFilter = ref('')

const filteredMethods = computed(() => {
  return methods.value.filter(method => {
    const matchesSearch = !search.value || 
      method.name.toLowerCase().includes(search.value.toLowerCase()) ||
      (method.description && method.description.toLowerCase().includes(search.value.toLowerCase()))
    
    const matchesType = !typeFilter.value || method.type === typeFilter.value
    const matchesStatus = !statusFilter.value || method.status === statusFilter.value

    return matchesSearch && matchesType && matchesStatus
  })
})

const toggleStatus = async (method: ShippingMethod) => {
  try {
    const response = await axios.post(`/admin/settings/shipping-methods/${method.id}/toggle-status`)
    method.status = response.data.status
  } catch (error) {
    console.error('Error toggling status:', error)
  }
}

const setDefault = async (method: ShippingMethod) => {
  try {
    await axios.post(`/admin/settings/shipping-methods/${method.id}/set-default`)
    // Update local state
    methods.value.forEach(m => m.is_default = m.id === method.id)
  } catch (error) {
    console.error('Error setting default:', error)
  }
}

const deleteMethod = async (method: ShippingMethod) => {
  if (!confirm(`Are you sure you want to delete "${method.name}"?`)) {
    return
  }

  try {
    await axios.delete(`/admin/settings/shipping-methods/${method.id}`)
    methods.value = methods.value.filter(m => m.id !== method.id)
  } catch (error) {
    console.error('Error deleting method:', error)
  }
}
</script>

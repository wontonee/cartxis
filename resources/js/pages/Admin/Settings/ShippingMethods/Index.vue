<template>
  <Head title="Shipping Methods" />

  <AdminLayout title="Shipment Method">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300">
            Shipping Methods
          </h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <Truck class="w-4 h-4" />
            Manage your shipping rates and delivery options
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Link
            href="/admin/settings/shipping-methods/create"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <Plus class="w-4 h-4 mr-2" />
            Add Shipping Method
          </Link>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Shipment Extensions</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enable delivery aggregators and configure each integration separately.</p>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Delivery</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Create and track shipments via Delhivery/Delivery extension.</p>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                  {{ extensions.delivery.enabled ? 'Enabled' : 'Disabled' }}
                </span>
                <Switch
                  :model-value="extensions.delivery.enabled"
                  @update:model-value="(val) => toggleExtension('delivery', val)"
                />
              </div>
            </div>

            <div class="mt-4 flex items-center justify-between">
              <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ extensions.delivery.enabled ? 'Extension is active' : 'Enable extension to configure settings' }}
              </span>
              <Link
                v-if="extensions.delivery.enabled"
                href="/admin/settings/delivery"
                class="inline-flex items-center px-3 py-1.5 border border-indigo-300 text-indigo-700 hover:bg-indigo-50 rounded-lg text-xs font-medium transition-colors"
              >
                Configure
              </Link>
            </div>
          </div>

          <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Shiprocket</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Create and track shipments via Shiprocket.</p>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                  {{ extensions.shiprocket.enabled ? 'Enabled' : 'Disabled' }}
                </span>
                <Switch
                  :model-value="extensions.shiprocket.enabled"
                  @update:model-value="(val) => toggleExtension('shiprocket', val)"
                />
              </div>
            </div>

            <div class="mt-4 flex items-center justify-between">
              <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ extensions.shiprocket.enabled ? 'Extension is active' : 'Enable extension to configure settings' }}
              </span>
              <Link
                v-if="extensions.shiprocket.enabled"
                href="/admin/settings/shiprocket"
                class="inline-flex items-center px-3 py-1.5 border border-cyan-300 text-cyan-700 hover:bg-cyan-50 rounded-lg text-xs font-medium transition-colors"
              >
                Configure
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search -->
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input
            v-model="search"
            type="text"
            placeholder="Search details..."
            class="w-full pl-9 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
          />
        </div>

        <!-- Type Filter -->
        <div class="relative">
          <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <select
            v-model="typeFilter"
            class="w-full pl-9 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow appearance-none"
          >
            <option value="">All Types</option>
            <option value="flat-rate">Flat Rate</option>
            <option value="calculated">Calculated</option>
          </select>
        </div>

        <!-- Status Filter -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <div class="h-1.5 w-1.5 rounded-full" :class="statusFilter === 'active' ? 'bg-green-500' : (statusFilter === 'inactive' ? 'bg-red-500' : 'bg-gray-400')"></div>
          </div>
          <select
            v-model="statusFilter"
            class="w-full pl-9 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow appearance-none"
          >
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>

      <!-- Methods Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
              <tr>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Method Details
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Type
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Rates
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Default
                </th>
                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
              <tr v-for="method in filteredMethods" :key="method.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-start">
                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400">
                      <component :is="method.type === 'flat-rate' ? Package : Scale" class="w-5 h-5" />
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900 dark:text-white">{{ method.name }}</div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">{{ method.description || 'No description' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 capitalize">
                    {{ method.type.replace('-', ' ') }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white font-medium">
                    ${{ Number(method.base_cost).toFixed(2) }}
                    <span class="text-gray-400 mx-1">+</span>
                    ${{ Number(method.cost_per_kg).toFixed(2) }}<span class="text-gray-500 text-xs">/kg</span>
                  </div>
                  <div class="text-xs text-gray-500 mt-0.5">
                    {{ method.rates?.length || 0 }} defined tiers
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <button
                    @click="toggleStatus(method)"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    :class="method.status === 'active' 
                      ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-900/50' 
                      : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50'"
                  >
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5"
                      :class="method.status === 'active' ? 'bg-green-500' : 'bg-red-500'"></span>
                    {{ method.status }}
                  </button>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <button
                    v-if="!method.is_default"
                    @click="setDefault(method)"
                    title="Set as default"
                    class="text-gray-400 hover:text-yellow-500 dark:hover:text-yellow-400 transition-colors"
                  >
                    <Star class="w-5 h-5" />
                  </button>
                  <div v-else class="text-yellow-500 dark:text-yellow-400" title="Default Method">
                    <Star class="w-5 h-5 fill-current" />
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end gap-3">
                    <Link
                      :href="`/admin/settings/shipping-methods/${method.id}/edit`"
                      class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors"
                    >
                      <Pencil class="w-4 h-4" />
                    </Link>
                    <button
                      v-if="!method.is_default"
                      @click="deleteMethod(method)"
                      class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="filteredMethods.length === 0" class="px-6 py-12 text-center bg-gray-50 dark:bg-gray-800/50">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4 text-gray-400">
            <Truck class="w-8 h-8" />
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">No shipping methods found</h3>
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
            Get started by creating your first shipping method for your store.
          </p>
          <div class="mt-6">
            <Link
              href="/admin/settings/shipping-methods/create"
              class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors shadow-sm"
            >
              <Plus class="w-4 h-4 mr-2" />
              Create Method
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { 
  Plus, 
  Star, 
  Truck, 
  Pencil, 
  Trash2, 
  Search, 
  Filter,
  Package,
  Scale
} from 'lucide-vue-next'
import { Switch } from '@/components/ui/switch'
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

const props = defineProps<{
  methods: {
    data: ShippingMethod[]
  },
  extensions: {
    delivery: {
      enabled: boolean
    }
    shiprocket: {
      enabled: boolean
    }
  }
}>()

const methods = ref<ShippingMethod[]>(props.methods?.data || [])
const extensions = ref(props.extensions || { delivery: { enabled: false }, shiprocket: { enabled: false } })

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

const toggleExtension = async (extension: 'shiprocket' | 'delivery', enabled: boolean | 'indeterminate') => {
  const normalizedEnabled = enabled === true

  try {
    const response = await axios.post(`/admin/settings/shipping-extensions/${extension}/toggle`, {
      enabled: normalizedEnabled,
    })

    extensions.value[extension].enabled = Boolean(response.data?.enabled)
  } catch (error) {
    console.error('Error toggling shipment extension:', error)
    // Revert state if api call fails
    extensions.value[extension].enabled = !normalizedEnabled
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

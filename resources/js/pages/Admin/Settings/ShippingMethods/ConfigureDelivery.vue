<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import axios from '@/lib/axios'

interface OptionItem {
  value: string
  label: string
}

interface Props {
  settings: {
    enabled: boolean
    api_token: string
    pickup_location: string
    base_url: string
  }
}

const props = defineProps<Props>()
const page = usePage()
const errors = computed(() => page.props.errors as Record<string, string>)
const pickupOptions = ref<OptionItem[]>(
  props.settings.pickup_location
    ? [{ value: props.settings.pickup_location, label: props.settings.pickup_location }]
    : []
)

const form = useForm({
  enabled: props.settings.enabled ?? false,
  api_token: props.settings.api_token ?? '',
  pickup_location: props.settings.pickup_location ?? '',
  base_url: props.settings.base_url ?? 'https://track.delhivery.com',
})

const fetchState = useForm({
  processing: false,
})

const save = () => {
  form.post('/admin/settings/delivery', {
    preserveScroll: true,
  })
}

const applyFetchedOptions = (responseData: any) => {
  const fetchedPickupOptions = Array.isArray(responseData?.pickup_locations)
    ? responseData.pickup_locations as OptionItem[]
    : []

  pickupOptions.value = fetchedPickupOptions

  const hasSelectedPickup = fetchedPickupOptions.some(option => option.value === form.pickup_location)
  if (!hasSelectedPickup && fetchedPickupOptions.length > 0) {
    form.pickup_location = fetchedPickupOptions[0].value
  }
}

const fetchPickupLocations = async () => {
  if (!form.api_token) {
    window.dispatchEvent(new CustomEvent('show-toast', {
      detail: { message: 'Enter Delivery API token before fetching pickup locations.', type: 'error' },
    }))

    return
  }

  fetchState.processing = true

  try {
    const response = await axios.post('/admin/settings/delivery/test-connection', {
      api_token: form.api_token,
      base_url: form.base_url,
    })

    applyFetchedOptions(response.data)

    window.dispatchEvent(new CustomEvent('show-toast', {
      detail: {
        message: response.data?.pickup_locations?.length
          ? 'Connection successful. Pickup suggestions were returned.'
          : 'Connection successful.',
        type: 'success',
      },
    }))
  } catch (error: any) {
    const message = error?.response?.data?.message || error.message || 'Connection test failed.'
    window.dispatchEvent(new CustomEvent('show-toast', {
      detail: { message, type: 'error' },
    }))
  } finally {
    fetchState.processing = false
  }
}
</script>

<template>
  <Head title="Delivery Settings" />

  <AdminLayout title="Delivery Settings">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Delivery Shipment Settings</h1>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Configure Delivery API credentials and defaults.</p>
        </div>
        <Link
          href="/admin/settings/shipping-methods"
          class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
        >
          Back to Shipping Methods
        </Link>
      </div>

      <form @submit.prevent="save" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Connection</h2>

          <label class="inline-flex items-center gap-2 mb-4">
            <input v-model="form.enabled" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500" />
            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Enable Delivery integration</span>
          </label>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API Token <span class="text-red-500">*</span></label>
              <input
                v-model="form.api_token"
                type="password"
                :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white', errors.api_token ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                placeholder="Delhivery API token"
              />
              <p v-if="errors.api_token" class="mt-1 text-sm text-red-600">{{ errors.api_token }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pickup Location</label>
              <input
                v-model="form.pickup_location"
                type="text"
                :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white', errors.pickup_location ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                placeholder="Warehouse / pickup code"
                list="delivery-pickup-locations"
              />
              <datalist id="delivery-pickup-locations">
                <option v-for="location in pickupOptions" :key="location.value" :value="location.value">
                  {{ location.label }}
                </option>
              </datalist>
              <p v-if="errors.pickup_location" class="mt-1 text-sm text-red-600">{{ errors.pickup_location }}</p>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Delhivery pickup list API may not be available for all accounts. Enter exact warehouse name/code manually.</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Base URL</label>
              <input
                v-model="form.base_url"
                type="url"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                placeholder="https://track.delhivery.com"
              />
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Endpoint contract wiring is the next step after API schema confirmation.</p>
            </div>
          </div>
        </div>

        <div class="p-6 flex items-center justify-between gap-3">
          <button
            type="button"
            @click="fetchPickupLocations"
            :disabled="fetchState.processing"
            class="inline-flex items-center px-4 py-2 border border-indigo-300 text-indigo-700 dark:text-indigo-300 dark:border-indigo-700 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 text-sm font-medium disabled:opacity-50"
          >
            {{ fetchState.processing ? 'Testing Connection...' : 'Test Connection' }}
          </button>

          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : 'Save Delivery Settings' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

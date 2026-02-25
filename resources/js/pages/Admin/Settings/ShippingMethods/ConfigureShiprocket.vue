<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
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
    email: string
    password: string
    pickup_location: string
    channel_id: string
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
const channelOptions = ref<OptionItem[]>(
  props.settings.channel_id
    ? [{ value: props.settings.channel_id, label: props.settings.channel_id }]
    : []
)

const form = useForm({
  enabled: props.settings.enabled ?? false,
  email: props.settings.email ?? '',
  password: props.settings.password ?? '',
  pickup_location: props.settings.pickup_location ?? 'Primary',
  channel_id: props.settings.channel_id ?? '',
})

const testState = useForm({
  email: props.settings.email ?? '',
  password: props.settings.password ?? '',
})

const save = () => {
  form.post('/admin/settings/shiprocket', {
    preserveScroll: true,
  })
}

const applyFetchedOptions = (responseData: any) => {
  const fetchedPickupOptions = Array.isArray(responseData?.pickup_locations)
    ? responseData.pickup_locations as OptionItem[]
    : []
  const fetchedChannelOptions = Array.isArray(responseData?.channels)
    ? responseData.channels as OptionItem[]
    : []

  pickupOptions.value = fetchedPickupOptions
  channelOptions.value = fetchedChannelOptions

  const hasSelectedPickup = fetchedPickupOptions.some(option => option.value === form.pickup_location)
  if (!hasSelectedPickup && fetchedPickupOptions.length > 0) {
    form.pickup_location = fetchedPickupOptions[0].value
  }

  const hasSelectedChannel = fetchedChannelOptions.some(option => option.value === form.channel_id)
  if (!hasSelectedChannel) {
    form.channel_id = fetchedChannelOptions[0]?.value || ''
  }
}

const testConnection = async () => {
  testState.processing = true

  try {
    const response = await axios.post('/admin/settings/shiprocket/test-connection', {
      email: testState.email || form.email,
      password: testState.password || form.password,
    })

    applyFetchedOptions(response.data)

    window.dispatchEvent(new CustomEvent('show-toast', {
      detail: { message: response.data?.message || 'Shiprocket connection successful.', type: 'success' },
    }))
  } catch (error: any) {
    const message = error?.response?.data?.message || error.message || 'Shiprocket connection failed.'
    window.dispatchEvent(new CustomEvent('show-toast', {
      detail: { message, type: 'error' },
    }))
  } finally {
    testState.processing = false
  }
}

onMounted(async () => {
  if (!form.enabled || !form.email || !form.password) {
    return
  }

  try {
    const response = await axios.get('/admin/settings/shiprocket/metadata')
    applyFetchedOptions(response.data)
  } catch {
    // keep existing saved fallback values when metadata fetch fails
  }
})
</script>

<template>
  <Head title="Shiprocket Settings" />

  <AdminLayout title="Shiprocket Settings">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Shiprocket Shipment Settings</h1>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Configure Shiprocket API credentials and defaults.</p>
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
            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Enable Shiprocket integration</span>
          </label>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email <span class="text-red-500">*</span></label>
              <input
                v-model="form.email"
                type="email"
                :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white', errors.email ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                placeholder="your-email@example.com"
              />
              <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password <span class="text-red-500">*</span></label>
              <input
                v-model="form.password"
                type="password"
                :class="['w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white', errors.password ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                placeholder="Shiprocket account password"
              />
              <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pickup Location</label>
              <select
                v-model="form.pickup_location"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              >
                <option value="">Select pickup location</option>
                <option v-for="location in pickupOptions" :key="location.value" :value="location.value">
                  {{ location.label }}
                </option>
              </select>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Locations are auto-fetched from Shiprocket when this page loads.</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Channel ID (Optional)</label>
              <select
                v-model="form.channel_id"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              >
                <option value="">No channel</option>
                <option v-for="channel in channelOptions" :key="channel.value" :value="channel.value">
                  {{ channel.label }}
                </option>
              </select>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Channels are auto-fetched from Shiprocket when this page loads.</p>
            </div>
          </div>
        </div>

        <div class="p-6 flex items-center justify-between gap-3">
          <button
            type="button"
            @click="testConnection"
            :disabled="testState.processing"
            class="inline-flex items-center px-4 py-2 border border-indigo-300 text-indigo-700 dark:text-indigo-300 dark:border-indigo-700 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 text-sm font-medium disabled:opacity-50"
          >
            {{ testState.processing ? 'Testing...' : 'Test Connection' }}
          </button>

          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : 'Save Shiprocket Settings' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

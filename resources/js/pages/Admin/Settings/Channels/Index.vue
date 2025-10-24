<template>
  <AdminLayout title="Channels">
    <template #default>
      <Head title="Channels" />
      <!-- Title -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Channel - Theme Management</h1>
        <p class="mt-2 text-sm text-gray-600">Manage theme settings for your sales channels</p>
      </div>

      <!-- Channels Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="channels.data.length === 0" class="p-6 text-center text-gray-500">
        <p>No channels found.</p>
      </div>

      <table v-else class="w-full">
        <thead class="bg-gray-50 border-b border-gray-300">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Channel Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Theme</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Select Theme</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="channel in channels.data" :key="channel.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ channel.name }}</p>
                <p v-if="channel.description" class="text-xs text-gray-500 truncate max-w-xs">{{ channel.description }}</p>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-blue-50 text-blue-700 border border-blue-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
                {{ channel.theme?.name || 'No Theme' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <select 
                :value="channel.theme_id"
                @change="updateTheme(channel.id, ($event.target as HTMLSelectElement).value)"
                class="px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
              >
                <option value="">Select Theme...</option>
                <option v-for="theme in availableThemes" :key="theme.id" :value="theme.id">
                  {{ theme.name }}
                </option>
              </select>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="[
                  'px-3 py-1 rounded-md text-sm font-medium inline-flex items-center',
                  channel.status === 'active' 
                    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' 
                    : 'bg-gray-100 text-gray-700 border border-gray-300'
                ]"
              >
                <svg v-if="channel.status === 'active'" class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                {{ channel.status === 'active' ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <span v-if="channel.is_default" class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-yellow-50 text-yellow-700 border border-yellow-200">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                Default
              </span>
              <span v-else class="text-gray-400 text-sm">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Info Box -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
      <div class="flex items-start">
        <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
        </svg>
        <div>
          <h3 class="text-sm font-medium text-blue-900">Theme Management</h3>
          <p class="mt-1 text-sm text-blue-700">
            Select a theme from the dropdown to change the appearance of your channel. Themes are located in the <code class="px-2 py-1 bg-blue-100 rounded text-xs">themes/</code> directory.
          </p>
        </div>
      </div>
    </div>
    </template>
  </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'

interface Channel {
  id: number
  name: string
  description: string | null
  slug: string
  theme_id: number | null
  theme?: {
    id: number
    name: string
    slug: string
  }
  status: 'active' | 'inactive'
  is_default: boolean
  url: string | null
  created_at: string
  updated_at: string
}

interface Theme {
  id: number
  name: string
  slug: string
  description: string | null
}

interface Props {
  channels: {
    data: Channel[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  availableThemes: Theme[]
}

import { Head } from '@inertiajs/vue3'

defineProps<Props>()

const updateTheme = (channelId: number, themeId: string) => {
  if (!themeId) {
    alert('Please select a theme')
    return
  }

  router.post(`/admin/settings/channels/${channelId}/update-theme`, {
    theme_id: parseInt(themeId)
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // Theme updated successfully
    },
    onError: (errors) => {
      console.error('Error updating theme:', errors)
      alert('Failed to update theme. Please try again.')
    }
  })
}
</script>

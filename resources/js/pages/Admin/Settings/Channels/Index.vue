<template>
  <Head title="Channels" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300">
            Channels
          </h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <Monitor class="w-4 h-4" />
            Manage your sales channels and their themes
          </p>
        </div>
      </div>

      <!-- Channels List -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
              <tr>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Channel Info
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Theme
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
              <tr v-for="channel in channels.data" :key="channel.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400">
                      <Globe class="w-5 h-5" />
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900 dark:text-white flex items-center gap-2">
                        {{ channel.name }}
                      </div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ channel.url || 'No URL configured' }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <Palette class="w-4 h-4 text-gray-400" />
                    <select
                      :value="channel.theme_id"
                      @change="(e) => updateTheme(channel.id, (e.target as HTMLSelectElement).value)"
                      class="block w-full max-w-xs pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-xl bg-gray-50 dark:bg-gray-700/50 dark:text-white transition-shadow"
                    >
                      <option value="">Select a Theme</option>
                      <option v-for="theme in availableThemes" :key="theme.id" :value="theme.id">
                        {{ theme.name }}
                      </option>
                    </select>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                    :class="channel.status === 'active' 
                      ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' 
                      : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'">
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5"
                      :class="channel.status === 'active' ? 'bg-green-500' : 'bg-red-500'"></span>
                    {{ channel.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div v-if="channel.is_default" class="flex items-center text-green-600 dark:text-green-400">
                    <CheckCircle2 class="w-5 h-5 mr-1.5" />
                    <span class="text-sm font-medium">Default</span>
                  </div>
                  <div v-else class="flex items-center text-gray-400">
                    <div class="w-5 h-5 mr-1.5 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-full"></div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button v-if="channel.url" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors">
                    <ExternalLink class="w-4 h-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Theme Info Box -->
      <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800">
        <div class="flex">
          <div class="flex-shrink-0">
            <Info class="h-5 w-5 text-blue-400" aria-hidden="true" />
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">
              Theme Management
            </h3>
            <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
              <p>
                Changing the theme will immediately update the visual appearance of your storefront. 
                Make sure to preview changes in a staging environment if available.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue'
import { router, Head } from '@inertiajs/vue3'
import { 
  Monitor, 
  Palette, 
  CheckCircle2, 
  AlertCircle, 
  Info, 
  Globe, 
  Star,
  Layout,
  ExternalLink
} from 'lucide-vue-next'

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

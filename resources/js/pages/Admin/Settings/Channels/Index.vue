<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <Link href="/admin/settings" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Settings
        </Link>
      </div>
      <Link href="/admin/settings/channels/create" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create Channel
      </Link>
    </div>

    <!-- Title -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Channels</h1>
      <p class="mt-2 text-sm text-gray-600">Manage your sales channels and assign themes to each channel</p>
    </div>

    <!-- Search & Filter -->
    <div class="mb-6 bg-white p-4 rounded-lg border border-gray-300">
      <div class="flex items-center gap-4">
        <div class="flex-1">
          <input 
            v-model="searchQuery"
            type="text"
            placeholder="Search channels..."
            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <select 
            v-model="filterStatus"
            class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div>
          <select 
            v-model="sortBy"
            class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="name">Sort by Name</option>
            <option value="created_at">Sort by Created</option>
            <option value="updated_at">Sort by Modified</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Channels Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="filteredChannels.length === 0" class="p-6 text-center text-gray-500">
        <p>No channels found. Create your first channel to get started.</p>
      </div>

      <table v-else class="w-full">
        <thead class="bg-gray-50 border-b border-gray-300">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              <input type="checkbox" v-model="selectAll" class="h-4 w-4" />
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Channel Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Theme</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modified</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="channel in filteredChannels" :key="channel.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <input type="checkbox" :value="channel.id" v-model="selectedChannels" class="h-4 w-4" />
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ channel.name }}</p>
                <p v-if="channel.description" class="text-xs text-gray-500">{{ channel.description }}</p>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center gap-2">
                <select 
                  :value="channel.theme_id"
                  @change="updateTheme(channel.id, $event.target.value)"
                  class="px-3 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option v-for="theme in availableThemes" :key="theme.id" :value="theme.id">
                    {{ theme.name }}
                  </option>
                </select>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <button
                @click="toggleStatus(channel.id, channel.status)"
                :class="[
                  'px-3 py-1 rounded-md text-sm font-medium',
                  channel.status === 'active' 
                    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' 
                    : 'bg-gray-100 text-gray-700 border border-gray-300'
                ]"
              >
                <span class="flex items-center gap-1">
                  <svg v-if="channel.status === 'active'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  {{ channel.status === 'active' ? 'Active' : 'Inactive' }}
                </span>
              </button>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <button
                v-if="!channel.is_default"
                @click="setDefault(channel.id)"
                class="text-gray-300 hover:text-yellow-500 text-xl"
                title="Click to set as default channel"
              >
                ★
              </button>
              <span v-else class="text-yellow-500 text-xl">★</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(channel.updated_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <Link :href="`/admin/settings/channels/${channel.id}/edit`" class="text-blue-600 hover:text-blue-900 mr-4">
                Edit
              </Link>
              <button
                @click="deleteChannel(channel.id)"
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Info -->
    <div v-if="channels" class="mt-4 text-sm text-gray-600">
      Showing {{ filteredChannels.length }} of {{ channels.length }} channels
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'

interface Channel {
  id: number
  name: string
  description: string
  slug: string
  theme_id: number
  theme: {
    id: number
    name: string
  }
  status: 'active' | 'inactive'
  is_default: boolean
  url: string
  created_at: string
  updated_at: string
}

interface Theme {
  id: number
  name: string
  slug: string
  description: string
}

interface Props {
  channels: Channel[]
  availableThemes: Theme[]
}

const props = withDefaults(defineProps<Props>(), {})

const searchQuery = ref('')
const filterStatus = ref('')
const sortBy = ref('name')
const selectedChannels = ref<number[]>([])
const selectAll = ref(false)

const filteredChannels = computed(() => {
  let filtered = [...props.channels]

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(c => 
      c.name.toLowerCase().includes(query) || 
      (c.description && c.description.toLowerCase().includes(query))
    )
  }

  // Filter by status
  if (filterStatus.value) {
    filtered = filtered.filter(c => c.status === filterStatus.value)
  }

  // Sort
  if (sortBy.value === 'name') {
    filtered.sort((a, b) => a.name.localeCompare(b.name))
  } else if (sortBy.value === 'created_at') {
    filtered.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
  } else if (sortBy.value === 'updated_at') {
    filtered.sort((a, b) => new Date(b.updated_at).getTime() - new Date(a.updated_at).getTime())
  }

  return filtered
})

const formatDate = (date: string) => {
  const now = new Date()
  const then = new Date(date)
  const diff = Math.floor((now.getTime() - then.getTime()) / 1000)

  if (diff < 60) return 'just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`
  
  return then.toLocaleDateString()
}

const updateTheme = (channelId: number, themeId: string) => {
  axios.post(`/admin/settings/channels/${channelId}/update-theme`, {
    theme_id: parseInt(themeId)
  }).then(() => {
    // Show success notification
    console.log('Theme updated successfully')
  }).catch(error => {
    console.error('Error updating theme:', error)
  })
}

const toggleStatus = (channelId: number, currentStatus: string) => {
  const newStatus = currentStatus === 'active' ? 'inactive' : 'active'
  
  axios.post(`/admin/settings/channels/${channelId}/toggle-status`, {
    status: newStatus
  }).then(() => {
    // Refresh the page or update the channel in the list
    location.reload()
  }).catch(error => {
    console.error('Error toggling status:', error)
  })
}

const setDefault = (channelId: number) => {
  if (confirm('Set this channel as the default channel?')) {
    axios.post(`/admin/settings/channels/${channelId}/set-default`).then(() => {
      location.reload()
    }).catch(error => {
      console.error('Error setting default:', error)
    })
  }
}

const deleteChannel = (channelId: number) => {
  if (confirm('Are you sure you want to delete this channel?')) {
    axios.delete(`/admin/settings/channels/${channelId}`).then(() => {
      location.reload()
    }).catch(error => {
      console.error('Error deleting channel:', error)
    })
  }
}
</script>

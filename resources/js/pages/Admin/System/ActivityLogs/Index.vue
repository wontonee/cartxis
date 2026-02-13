<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import axios from '@/lib/axios'
import { RefreshCcw, UserCircle2 } from 'lucide-vue-next'

interface ActivityActor {
  id: number
  name: string
  email: string
}

interface ActivityLogItem {
  id: number
  action: string
  level: 'info' | 'warning' | 'error' | string
  description: string | null
  entity_type: string | null
  entity_id: number | null
  context: Record<string, unknown> | null
  actor: ActivityActor | null
  created_at: string
  created_at_human: string
}

const logs = ref<ActivityLogItem[]>([])
const loading = ref(false)
const errorMessage = ref<string | null>(null)
let pollTimer: ReturnType<typeof setInterval> | null = null

const fetchLogs = async (silent = false) => {
  if (!silent) {
    loading.value = true
  }

  try {
    const response = await axios.get('/admin/activity-logs/data', {
      params: {
        limit: 50,
      },
      headers: {
        Accept: 'application/json',
      },
    })

    logs.value = response.data?.logs || []
    errorMessage.value = null
  } catch (error) {
    console.error('Failed to load activity logs:', error)
    errorMessage.value = 'Failed to load activity logs. Please try again.'
  } finally {
    if (!silent) {
      loading.value = false
    }
  }
}

const levelBadgeClass = (level: string) => {
  if (level === 'error') {
    return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
  }

  if (level === 'warning') {
    return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
  }

  return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
}

onMounted(async () => {
  await fetchLogs()

  pollTimer = setInterval(() => {
    fetchLogs(true)
  }, 30000)
})

onUnmounted(() => {
  if (pollTimer) {
    clearInterval(pollTimer)
  }
})
</script>

<template>
  <Head title="Admin Activity Logs" />

  <AdminLayout title="Activity Logs">
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Admin Activity Logs</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Recent admin and system activity events.</p>
        </div>

        <button
          @click="fetchLogs()"
          :disabled="loading"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-60 disabled:cursor-not-allowed"
        >
          <RefreshCcw class="w-4 h-4" :class="loading ? 'animate-spin' : ''" />
          Refresh
        </button>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div v-if="loading" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
          Loading activity logs...
        </div>

        <div v-else-if="errorMessage" class="px-6 py-8 text-sm text-red-600 dark:text-red-400">
          {{ errorMessage }}
        </div>

        <div v-else-if="logs.length === 0" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
          No activity logs available yet.
        </div>

        <div v-else class="overflow-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/40">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Event</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Level</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Actor</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Entity</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Time</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                <td class="px-6 py-4 align-top">
                  <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ log.action }}</p>
                  <p v-if="log.description" class="text-xs mt-1 text-gray-600 dark:text-gray-300">{{ log.description }}</p>
                </td>
                <td class="px-6 py-4 align-top">
                  <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium" :class="levelBadgeClass(log.level)">
                    {{ log.level }}
                  </span>
                </td>
                <td class="px-6 py-4 align-top">
                  <div v-if="log.actor" class="flex items-center gap-2">
                    <UserCircle2 class="w-4 h-4 text-gray-400" />
                    <div>
                      <p class="text-sm text-gray-800 dark:text-gray-100">{{ log.actor.name }}</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">{{ log.actor.email }}</p>
                    </div>
                  </div>
                  <p v-else class="text-sm text-gray-500 dark:text-gray-400">System</p>
                </td>
                <td class="px-6 py-4 align-top text-sm text-gray-700 dark:text-gray-300">
                  <span v-if="log.entity_type">{{ log.entity_type }}#{{ log.entity_id ?? '-' }}</span>
                  <span v-else>-</span>
                </td>
                <td class="px-6 py-4 align-top">
                  <p class="text-sm text-gray-800 dark:text-gray-100">{{ log.created_at_human }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ log.created_at }}</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

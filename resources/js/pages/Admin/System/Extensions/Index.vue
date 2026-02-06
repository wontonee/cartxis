<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue'
import { RefreshCcw, Puzzle, Box, Power, Play, Pause, Trash2, Download } from 'lucide-vue-next'

type ExtensionSource = 'bundled' | 'filesystem' | null

interface ExtensionItem {
  code: string
  name: string
  description?: string | null
  version: string
  author?: string | null
  author_url?: string | null
  source: ExtensionSource
  path?: string | null
  installed: boolean
  active: boolean
  installed_at?: string | null
  has_db_row: boolean
}

interface Props {
  extensions: ExtensionItem[]
}

const props = defineProps<Props>()

const isSyncing = ref(false)
const showDeleteModal = ref(false)
const deleting = ref<ExtensionItem | null>(null)

const isBundled = (ext: ExtensionItem) => ext.source === 'bundled'

const canUninstall = (ext: ExtensionItem) => {
  // Avoid removing first-party bundled extensions from DB via UI.
  // (They would re-appear on next sync anyway.)
  return ext.installed && !isBundled(ext)
}

const hasAnyExtensions = computed(() => (props.extensions?.length ?? 0) > 0)

const sync = () => {
  isSyncing.value = true
  router.post('/admin/system/extensions/sync', {}, {
    preserveScroll: true,
    onFinish: () => {
      isSyncing.value = false
    },
  })
}

const install = (code: string) => {
  router.post(`/admin/system/extensions/${encodeURIComponent(code)}/install`, {}, { preserveScroll: true })
}

const activate = (code: string) => {
  router.post(`/admin/system/extensions/${encodeURIComponent(code)}/activate`, {}, { preserveScroll: true })
}

const deactivate = (code: string) => {
  router.post(`/admin/system/extensions/${encodeURIComponent(code)}/deactivate`, {}, { preserveScroll: true })
}

const askUninstall = (ext: ExtensionItem) => {
  deleting.value = ext
  showDeleteModal.value = true
}

const confirmUninstall = () => {
  if (!deleting.value) return

  router.delete(`/admin/system/extensions/${encodeURIComponent(deleting.value.code)}`, {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false
      deleting.value = null
    },
  })
}
</script>

<template>
  <Head title="Extensions" />

  <AdminLayout title="Extensions">
    <div class="p-6 max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            Extensions
          </h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Manage and synchronize system extensions and plugins
          </p>
        </div>

        <button
          @click="sync"
          :disabled="isSyncing"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-xl text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <RefreshCcw class="w-4 h-4" :class="isSyncing ? 'animate-spin' : ''" />
          Sync Extensions
        </button>
      </div>

      <!-- Content -->
      <div class="overflow-auto rounded-xl">
        
        <!-- Info Banner -->
        <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 flex items-start gap-3">
             <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-full shrink-0">
                 <Box class="w-5 h-5 text-blue-600 dark:text-blue-400" />
             </div>
             <div>
                <p class="text-sm font-medium text-blue-900 dark:text-blue-100">Extension Synchronization</p>
                <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                    Use the "Sync" button to refresh extension manifests from the file system. Bundled extensions are automatically discovered.
                </p>
             </div>
        </div>

        <div v-if="!hasAnyExtensions" class="flex flex-col items-center justify-center p-12 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 text-center">
             <Puzzle class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" />
             <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No extensions found</h3>
             <p class="text-gray-500 dark:text-gray-400">Run sync to discover available extensions.</p>
        </div>

        <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Version</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Source</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="ext in props.extensions" :key="ext.code" class="group hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors">
                <td class="px-6 py-4">
                  <div class="font-medium text-gray-900 dark:text-white">{{ ext.name }}</div>
                  <div v-if="ext.description" class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ ext.description }}</div>
                </td>

                <td class="px-6 py-4">
                  <code class="text-xs font-mono text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded border border-gray-200 dark:border-gray-700">{{ ext.code }}</code>
                </td>

                <td class="px-6 py-4">
                  <div class="text-sm text-gray-700 dark:text-gray-300">{{ ext.version }}</div>
                </td>

                <td class="px-6 py-4">
                  <span
                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium border"
                    :class="isBundled(ext)
                      ? 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800'
                      : 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700'"
                  >
                    {{ isBundled(ext) ? 'Bundled' : 'Filesystem' }}
                  </span>
                </td>

                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <span
                      class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium border"
                      :class="ext.installed
                        ? 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800'
                        : 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800'"
                    >
                      {{ ext.installed ? 'Installed' : 'Not installed' }}
                    </span>

                    <span
                      v-if="ext.installed"
                      class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium border"
                      :class="ext.active
                        ? 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800'
                        : 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700'"
                    >
                      {{ ext.active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                  <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                    <button
                      v-if="!ext.installed"
                      @click="install(ext.code)"
                      class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors"
                      title="Install"
                    >
                      <Download class="w-4 h-4" />
                    </button>

                    <button
                      v-else-if="ext.active"
                      @click="deactivate(ext.code)"
                      class="p-2 text-gray-400 hover:text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-colors"
                      title="Deactivate"
                    >
                      <Pause class="w-4 h-4" />
                    </button>

                    <button
                      v-else
                      @click="activate(ext.code)"
                      class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                      title="Activate"
                    >
                      <Play class="w-4 h-4" />
                    </button>

                    <button
                      v-if="canUninstall(ext)"
                      @click="askUninstall(ext)"
                      class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                      title="Uninstall"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <ConfirmDeleteModal
            v-model:show="showDeleteModal"
            :title="deleting?.name || deleting?.code || 'Extension'"
            message="This will remove the extension from the database and run its uninstall hook (if implemented)."
            @confirm="confirmUninstall"
        />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue'
import { RefreshCcw } from 'lucide-vue-next'

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
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Extensions</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">System → Extensions</p>
        </div>

        <button
          @click="sync"
          :disabled="isSyncing"
          class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <RefreshCcw class="h-4 w-4" :class="isSyncing ? 'animate-spin' : ''" />
          Sync
        </button>
      </div>

      <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
        <p class="text-sm text-blue-800 dark:text-blue-200">
          <strong>Tip:</strong> “Sync” refreshes extension manifests into the database. Bundled extensions default to installed and active.
        </p>
      </div>

      <div v-if="!hasAnyExtensions" class="rounded-lg border border-gray-200 bg-white p-6 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
        No extensions discovered.
      </div>

      <div v-else class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Version</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Source</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
              <tr v-for="ext in props.extensions" :key="ext.code" class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                <td class="px-6 py-4">
                  <div class="font-medium text-gray-900 dark:text-white">{{ ext.name }}</div>
                  <div v-if="ext.description" class="mt-1 text-sm text-gray-600 dark:text-gray-300 line-clamp-2">{{ ext.description }}</div>
                </td>

                <td class="px-6 py-4">
                  <div class="text-sm font-mono text-gray-700 dark:text-gray-200">{{ ext.code }}</div>
                </td>

                <td class="px-6 py-4">
                  <div class="text-sm text-gray-700 dark:text-gray-200">{{ ext.version }}</div>
                </td>

                <td class="px-6 py-4">
                  <span
                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="isBundled(ext)
                      ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-200'
                      : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'"
                  >
                    {{ isBundled(ext) ? 'Bundled' : 'Filesystem' }}
                  </span>
                </td>

                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <span
                      class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                      :class="ext.installed
                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200'
                        : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200'"
                    >
                      {{ ext.installed ? 'Installed' : 'Not installed' }}
                    </span>

                    <span
                      v-if="ext.installed"
                      class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                      :class="ext.active
                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200'
                        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'"
                    >
                      {{ ext.active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                </td>

                <td class="px-6 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      v-if="!ext.installed"
                      @click="install(ext.code)"
                      class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700"
                    >
                      Install
                    </button>

                    <button
                      v-else-if="ext.active"
                      @click="deactivate(ext.code)"
                      class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                      Deactivate
                    </button>

                    <button
                      v-else
                      @click="activate(ext.code)"
                      class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700"
                    >
                      Activate
                    </button>

                    <button
                      v-if="canUninstall(ext)"
                      @click="askUninstall(ext)"
                      class="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700"
                    >
                      Uninstall
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <ConfirmDeleteModal
        v-model:show="showDeleteModal"
        :title="deleting?.name || deleting?.code || 'Extension'"
        message="This will remove the extension from the database and run its uninstall hook (if implemented)."
        @confirm="confirmUninstall"
      />
    </div>
  </AdminLayout>
</template>

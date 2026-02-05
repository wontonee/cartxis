<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Shield, Plus, Edit, Trash2 } from 'lucide-vue-next'

interface Permission {
  id: number
  name: string
  display_name: string
  group: string
  description: string
  created_at: string
}

interface Props {
  permissions: Record<string, Permission[]>
}

const props = defineProps<Props>()

const deletePermission = (permission: Permission) => {
  if (confirm(`Are you sure you want to delete permission "${permission.display_name}"?`)) {
    router.delete(`/admin/system/permissions/${permission.id}`, {
      preserveScroll: true,
    })
  }
}

const getGroupColor = (group: string) => {
  const colors: Record<string, string> = {
    'catalog': 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
    'sales': 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
    'customer': 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200',
    'marketing': 'bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-200',
    'content': 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200',
    'settings': 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
    'system': 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
    'reports': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200',
  }
  return colors[group.toLowerCase()] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200'
}
</script>

<template>
  <Head title="Permissions Management" />
  
  <AdminLayout title="Permissions Management">
    <div class="flex flex-col h-full">
      <!-- Header -->
      <div class="px-6 py-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between shadow-sm z-10">
        <div>
          <h1 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <Shield class="w-5 h-5 text-blue-600" />
            Permissions Management
          </h1>
        </div>
        <Link
          href="/admin/system/permissions/create"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center gap-2 transition-colors text-sm font-medium shadow-sm"
        >
          <Plus class="w-4 h-4" />
          Add Permission
        </Link>
      </div>

      <!-- Content -->
      <div class="flex-1 p-6 overflow-auto bg-gray-50 dark:bg-gray-900">
        <!-- Permissions by Group -->
        <div class="space-y-6">
          <div
            v-for="(groupPermissions, group) in props.permissions"
            :key="group"
            class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
          >
            <!-- Group Header -->
            <div class="bg-gray-50 dark:bg-gray-800/60 px-6 py-3 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center gap-2">
                <span
                  class="px-2.5 py-0.5 text-xs font-semibold rounded-full uppercase tracking-wide"
                  :class="getGroupColor(group)"
                >
                  {{ group }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                  {{ groupPermissions.length }} permission{{ groupPermissions.length !== 1 ? 's' : '' }}
                </span>
              </div>
            </div>

            <!-- Permissions Table -->
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Name
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Display Name
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Description
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Created
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="permission in groupPermissions" :key="permission.id" class="group hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <code class="text-xs font-mono text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded border border-gray-200 dark:border-gray-700">
                      {{ permission.name }}
                    </code>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ permission.display_name }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      {{ permission.description || 'No description' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ new Date(permission.created_at).toLocaleDateString() }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                      <Link
                        :href="`/admin/system/permissions/${permission.id}/edit`"
                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                        title="Edit"
                      >
                        <Edit class="w-4 h-4" />
                      </Link>
                      <button
                        @click="deletePermission(permission)"
                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                        title="Delete"
                      >
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-if="Object.keys(props.permissions).length === 0"
          class="flex flex-col items-center justify-center p-12 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 text-center"
        >
          <Shield class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" />
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No permissions found</h3>
          <p class="text-gray-500 dark:text-gray-400 mb-6">Get started by creating your first permission.</p>
          <Link
            href="/admin/system/permissions/create"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
          >
            <Plus class="w-4 h-4" />
            Add Permission
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

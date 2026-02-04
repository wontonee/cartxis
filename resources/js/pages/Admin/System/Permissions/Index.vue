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
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Permissions Management</h1>
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Manage system permissions and access control</p>
          </div>
          <Link
            href="/admin/system/permissions/create"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <Plus class="w-5 h-5 mr-2" />
            Add Permission
          </Link>
        </div>
      </div>

    <!-- Permissions by Group -->
    <div class="space-y-6">
          <div
            v-for="(groupPermissions, group) in props.permissions"
            :key="group"
            class="bg-white dark:bg-gray-900 rounded-lg shadow overflow-hidden"
          >
            <!-- Group Header -->
            <div class="bg-gray-50 dark:bg-gray-800/60 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center gap-2">
                <span
                  class="px-3 py-1 text-sm font-semibold rounded-full"
                  :class="getGroupColor(group)"
                >
                  {{ group }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-300">
                  {{ groupPermissions.length }} permission{{ groupPermissions.length !== 1 ? 's' : '' }}
                </span>
              </div>
            </div>

            <!-- Permissions Table -->
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
              <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Name
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Display Name
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Description
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Created
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                <tr v-for="permission in groupPermissions" :key="permission.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <code class="text-sm font-mono text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                      {{ permission.name }}
                    </code>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                      {{ permission.display_name }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-500 dark:text-gray-300">
                      {{ permission.description || 'No description' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                    {{ permission.created_at }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Link
                        :href="`/admin/system/permissions/${permission.id}/edit`"
                        class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                        title="Edit"
                      >
                        <Edit class="w-5 h-5" />
                      </Link>
                      <button
                        @click="deletePermission(permission)"
                        class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                        title="Delete"
                      >
                        <Trash2 class="w-5 h-5" />
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
        class="bg-white dark:bg-gray-900 rounded-lg shadow p-12 text-center"
      >
        <Shield class="w-16 h-16 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No permissions found</h3>
        <p class="text-gray-500 dark:text-gray-300 mb-6">Get started by creating your first permission.</p>
        <Link
          href="/admin/system/permissions/create"
          class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
        >
          <Plus class="w-5 h-5" />
          Add Permission
        </Link>
      </div>
    </div>
  </AdminLayout>
</template>

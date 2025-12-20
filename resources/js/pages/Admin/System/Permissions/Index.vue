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
    'catalog': 'bg-blue-100 text-blue-800',
    'sales': 'bg-green-100 text-green-800',
    'customer': 'bg-purple-100 text-purple-800',
    'marketing': 'bg-pink-100 text-pink-800',
    'content': 'bg-orange-100 text-orange-800',
    'settings': 'bg-gray-100 text-gray-800',
    'system': 'bg-red-100 text-red-800',
    'reports': 'bg-indigo-100 text-indigo-800',
  }
  return colors[group.toLowerCase()] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
  <AdminLayout>
    <Head title="Permissions Management" />

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div>
              <div class="flex items-center gap-3 mb-2">
                <Shield class="w-8 h-8 text-primary" />
                <h1 class="text-3xl font-bold text-gray-900">Permissions Management</h1>
              </div>
              <p class="text-gray-600">Manage system permissions and access control</p>
            </div>
            <Link
              href="/admin/system/permissions/create"
              class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
            >
              <Plus class="w-5 h-5" />
              Add Permission
            </Link>
          </div>
        </div>

        <!-- Permissions by Group -->
        <div class="space-y-6">
          <div
            v-for="(groupPermissions, group) in props.permissions"
            :key="group"
            class="bg-white rounded-lg shadow overflow-hidden"
          >
            <!-- Group Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
              <div class="flex items-center gap-2">
                <span
                  class="px-3 py-1 text-sm font-semibold rounded-full"
                  :class="getGroupColor(group)"
                >
                  {{ group }}
                </span>
                <span class="text-sm text-gray-500">
                  {{ groupPermissions.length }} permission{{ groupPermissions.length !== 1 ? 's' : '' }}
                </span>
              </div>
            </div>

            <!-- Permissions Table -->
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Name
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Display Name
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Description
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Created
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="permission in groupPermissions" :key="permission.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <code class="text-sm font-mono text-gray-900 bg-gray-100 px-2 py-1 rounded">
                      {{ permission.name }}
                    </code>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ permission.display_name }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-500">
                      {{ permission.description || 'No description' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ permission.created_at }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Link
                        :href="`/admin/system/permissions/${permission.id}/edit`"
                        class="text-blue-600 hover:text-blue-900"
                        title="Edit"
                      >
                        <Edit class="w-5 h-5" />
                      </Link>
                      <button
                        @click="deletePermission(permission)"
                        class="text-red-600 hover:text-red-900"
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
          class="bg-white rounded-lg shadow p-12 text-center"
        >
          <Shield class="w-16 h-16 text-gray-400 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">No permissions found</h3>
          <p class="text-gray-500 mb-6">Get started by creating your first permission.</p>
          <Link
            href="/admin/system/permissions/create"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
          >
            <Plus class="w-5 h-5" />
            Add Permission
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

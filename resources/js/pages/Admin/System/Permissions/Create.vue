<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Shield, ArrowLeft } from 'lucide-vue-next'


interface Props {
  existingGroups: string[]
}

const props = defineProps<Props>()

const form = useForm({
  name: '',
  display_name: '',
  group: '',
  description: '',
})

const customGroup = ref('')
const showCustomGroup = ref(false)

const toggleCustomGroup = () => {
  showCustomGroup.value = !showCustomGroup.value
  if (showCustomGroup.value) {
    form.group = customGroup.value
  } else {
    customGroup.value = form.group
    form.group = ''
  }
}

const updateGroup = (value: string) => {
  if (showCustomGroup.value) {
    customGroup.value = value
    form.group = value
  } else {
    form.group = value
  }
}

const submit = () => {
  form.post('/admin/system/permissions', {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Create Permission" />
  
  <AdminLayout title="Create Permission">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Create Permission</h1>
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Add a new permission to the system</p>
          </div>
          <Link
            href="/admin/system/permissions"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <ArrowLeft class="w-4 h-4 mr-2" />
            Back to Permissions
          </Link>
        </div>
      </div>

      <!-- Form -->
      <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6">
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Name -->
            <div class="space-y-2">
              <Label for="name" class="required">Permission Name</Label>
              <Input
                id="name"
                v-model="form.name"
                type="text"
                placeholder="e.g., catalog.products.view"
                class="font-mono"
                required
              />
              <p class="text-sm text-gray-500 dark:text-gray-300">
                Use dot notation for permission keys (e.g., module.resource.action)
              </p>
              <p v-if="form.errors.name" class="text-sm text-red-600">
                {{ form.errors.name }}
              </p>
            </div>

            <!-- Display Name -->
            <div class="space-y-2">
              <Label for="display_name" class="required">Display Name</Label>
              <Input
                id="display_name"
                v-model="form.display_name"
                type="text"
                placeholder="e.g., View Products"
                required
              />
              <p class="text-sm text-gray-500 dark:text-gray-300">
                A human-readable name for this permission
              </p>
              <p v-if="form.errors.display_name" class="text-sm text-red-600">
                {{ form.errors.display_name }}
              </p>
            </div>

            <!-- Group Selection -->
            <div class="space-y-2">
              <Label for="group" class="required">Permission Group</Label>
              <div class="flex gap-2">
                <select
                  v-if="!showCustomGroup"
                  id="group"
                  v-model="form.group"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                  required
                >
                  <option value="" disabled>Select a group</option>
                  <option value="catalog">Catalog</option>
                  <option value="sales">Sales</option>
                  <option value="customer">Customer</option>
                  <option value="marketing">Marketing</option>
                  <option value="content">Content</option>
                  <option value="settings">Settings</option>
                  <option value="system">System</option>
                  <option value="reports">Reports</option>
                  <option
                    v-for="group in props.existingGroups"
                    :key="group"
                    :value="group"
                  >
                    {{ group }}
                  </option>
                </select>
                <Input
                  v-else
                  id="custom_group"
                  v-model="customGroup"
                  type="text"
                  placeholder="Enter custom group name"
                  @input="updateGroup(customGroup)"
                  required
                />
                <Button type="button" variant="outline" @click="toggleCustomGroup">
                  {{ showCustomGroup ? 'Select Existing' : 'Custom Group' }}
                </Button>
              </div>
              <p class="text-sm text-gray-500 dark:text-gray-300">
                Group related permissions together
              </p>
              <p v-if="form.errors.group" class="text-sm text-red-600">
                {{ form.errors.group }}
              </p>
            </div>

            <!-- Description -->
            <div class="space-y-2">
              <Label for="description">Description</Label>
              <Textarea
                id="description"
                v-model="form.description"
                placeholder="Describe what this permission allows..."
                rows="3"
              />
              <p class="text-sm text-gray-500 dark:text-gray-300">
                Optional description to explain what this permission controls
              </p>
              <p v-if="form.errors.description" class="text-sm text-red-600">
                {{ form.errors.description }}
              </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t dark:border-gray-800">
              <Link
                href="/admin/system/permissions"
                class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Cancel
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ form.processing ? 'Creating...' : 'Create Permission' }}
              </button>
            </div>
          </form>
        </div>
      </div>
  </AdminLayout>
</template>

<style scoped>
.required::after {
  content: ' *';
  color: #ef4444;
}
</style>

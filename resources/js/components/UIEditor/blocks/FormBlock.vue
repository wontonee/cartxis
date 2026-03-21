<script setup lang="ts">
defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

type Field = { id: string; type: string; label: string; required?: boolean; placeholder?: string }
</script>

<template>
  <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
    <!-- Form title -->
    <h3 v-if="settings.title" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
      {{ settings.title }}
    </h3>

    <form class="space-y-4" @submit.prevent>
      <!-- Fields -->
      <div
        v-for="field in (settings.fields as Field[] ?? [])"
        :key="field.id"
        class="flex flex-col gap-1"
      >
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
          {{ field.label }}
          <span v-if="field.required" class="text-red-500 ml-0.5">*</span>
        </label>

        <textarea
          v-if="field.type === 'textarea'"
          :placeholder="field.placeholder ?? ''"
          rows="4"
          class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white resize-y focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          :disabled="editorMode"
        />
        <select
          v-else-if="field.type === 'select'"
          class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          :disabled="editorMode"
        >
          <option value="" disabled selected>{{ field.placeholder ?? `Select ${field.label}` }}</option>
        </select>
        <input
          v-else
          :type="field.type === 'email' ? 'email' : field.type === 'phone' ? 'tel' : 'text'"
          :placeholder="field.placeholder ?? ''"
          class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          :disabled="editorMode"
        />
      </div>

      <!-- Submit button -->
      <div class="pt-2">
        <button
          type="submit"
          class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors disabled:opacity-60"
          :disabled="editorMode"
        >
          {{ (settings.submit_label as string) || 'Send Message' }}
        </button>
      </div>

      <!-- Editor-mode overlay hint -->
      <div
        v-if="editorMode"
        class="absolute inset-0 cursor-pointer"
      />
    </form>
  </div>
</template>

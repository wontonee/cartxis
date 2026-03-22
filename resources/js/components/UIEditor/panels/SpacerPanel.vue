<script setup lang="ts">
const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()
function update(key: string, value: unknown) { emit('update:settings', { ...props.settings, [key]: value }) }
</script>

<template>
  <div class="p-4">
    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Height (px)</label>
    <input
      type="number"
      :value="settings.height as number"
      min="8"
      max="400"
      class="w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      @change="update('height', Number(($event.target as HTMLInputElement).value))"
    />
  </div>
</template>

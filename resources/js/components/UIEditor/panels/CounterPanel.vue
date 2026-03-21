<script setup lang="ts">
const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()
function update(key: string, value: unknown) { emit('update:settings', { ...props.settings, [key]: value }) }
</script>

<template>
  <div class="p-4 space-y-4">
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Number</label>
      <input
        type="number"
        :value="settings.number as number"
        class="uie-input"
        @input="update('number', Number(($event.target as HTMLInputElement).value))"
      />
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Prefix</label>
        <input type="text" :value="settings.prefix as string" placeholder="$" class="uie-input" @input="update('prefix', ($event.target as HTMLInputElement).value)" />
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Suffix</label>
        <input type="text" :value="settings.suffix as string" placeholder="+" class="uie-input" @input="update('suffix', ($event.target as HTMLInputElement).value)" />
      </div>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Label</label>
      <input type="text" :value="settings.label as string" class="uie-input" @input="update('label', ($event.target as HTMLInputElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Animation Duration (ms)</label>
      <input type="number" min="200" max="8000" step="100" :value="settings.duration as number" class="uie-input" @input="update('duration', Number(($event.target as HTMLInputElement).value))" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Color</label>
      <div class="flex gap-2 items-center">
        <input type="color" :value="settings.color as string" class="h-8 w-10 rounded cursor-pointer border border-gray-300" @input="update('color', ($event.target as HTMLInputElement).value)" />
        <input type="text" :value="settings.color as string" class="uie-input flex-1" @input="update('color', ($event.target as HTMLInputElement).value)" />
      </div>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Alignment</label>
      <select :value="settings.align as string" class="uie-input" @change="update('align', ($event.target as HTMLSelectElement).value)">
        <option value="left">Left</option>
        <option value="center">Center</option>
        <option value="right">Right</option>
      </select>
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.uie-input { @apply w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent; }
</style>

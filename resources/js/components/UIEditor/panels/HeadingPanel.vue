<script setup lang="ts">
const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()
function update(key: string, value: unknown) {
  emit('update:settings', { ...props.settings, [key]: value })
}
</script>

<template>
  <div class="p-4 space-y-4">
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Text</label>
      <input type="text" :value="settings.text as string" class="uie-input" @input="update('text', ($event.target as HTMLInputElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Level</label>
      <select :value="settings.level as string" class="uie-input" @change="update('level', ($event.target as HTMLSelectElement).value)">
        <option v-for="h in ['h1','h2','h3','h4','h5','h6']" :key="h" :value="h">{{ h.toUpperCase() }}</option>
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Align</label>
      <select :value="settings.align as string" class="uie-input" @change="update('align', ($event.target as HTMLSelectElement).value)">
        <option value="left">Left</option>
        <option value="center">Center</option>
        <option value="right">Right</option>
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Color</label>
      <div class="flex items-center gap-2">
        <input type="color" :value="(settings.color as string) || '#111827'" class="w-8 h-8 rounded border cursor-pointer" @input="update('color', ($event.target as HTMLInputElement).value)" />
        <input type="text" :value="settings.color as string" class="uie-input flex-1" @change="update('color', ($event.target as HTMLInputElement).value)" />
      </div>
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.uie-input { @apply w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent; }
</style>

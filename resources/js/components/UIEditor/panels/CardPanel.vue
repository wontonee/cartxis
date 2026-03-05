<script setup lang="ts">
const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()
function update(key: string, value: unknown) { emit('update:settings', { ...props.settings, [key]: value }) }
</script>

<template>
  <div class="p-4 space-y-4">
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Image URL</label>
      <input type="text" :value="settings.image as string" class="uie-input" placeholder="/storage/…" @input="update('image', ($event.target as HTMLInputElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Title</label>
      <input type="text" :value="settings.title as string" class="uie-input" @input="update('title', ($event.target as HTMLInputElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Body</label>
      <textarea :value="settings.body as string" rows="3" class="uie-input resize-none" @input="update('body', ($event.target as HTMLTextAreaElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Badge</label>
      <input type="text" :value="settings.badge as string" class="uie-input" placeholder="e.g. New" @input="update('badge', ($event.target as HTMLInputElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Footer Text</label>
      <input type="text" :value="settings.footer as string" class="uie-input" @input="update('footer', ($event.target as HTMLInputElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Link URL</label>
      <input type="text" :value="settings.link as string" class="uie-input" @input="update('link', ($event.target as HTMLInputElement).value)" />
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.uie-input { @apply w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent; }
</style>

<script setup lang="ts">
const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()
function update(key: string, value: unknown) { emit('update:settings', { ...props.settings, [key]: value }) }

const iconOptions = ['star', 'heart', 'zap', 'shield', 'award', 'rocket', 'globe', 'check', 'thumbs-up', 'smile', 'truck', 'refresh-cw', 'headphones', 'shopping-bag', 'gift', 'tag']
</script>

<template>
  <div class="p-4 space-y-4">
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Icon</label>
      <select :value="settings.icon as string" class="uie-input" @change="update('icon', ($event.target as HTMLSelectElement).value)">
        <option v-for="opt in iconOptions" :key="opt" :value="opt">{{ opt }}</option>
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Icon Color</label>
      <div class="flex gap-2 items-center">
        <input type="color" :value="settings.icon_color as string" class="h-8 w-10 rounded cursor-pointer border border-gray-300" @input="update('icon_color', ($event.target as HTMLInputElement).value)" />
        <input type="text" :value="settings.icon_color as string" class="uie-input flex-1" @input="update('icon_color', ($event.target as HTMLInputElement).value)" />
      </div>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Icon Size (px)</label>
      <input type="number" min="20" max="120" :value="settings.icon_size as number" class="uie-input" @input="update('icon_size', Number(($event.target as HTMLInputElement).value))" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Title</label>
      <input type="text" :value="settings.title as string" class="uie-input" @input="update('title', ($event.target as HTMLInputElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
      <textarea :value="settings.description as string" rows="3" class="uie-input resize-none" @input="update('description', ($event.target as HTMLTextAreaElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Link URL (optional)</label>
      <input type="text" :value="settings.link as string" class="uie-input" placeholder="https://..." @input="update('link', ($event.target as HTMLInputElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Alignment</label>
      <select :value="settings.align as string" class="uie-input" @change="update('align', ($event.target as HTMLSelectElement).value)">
        <option value="left">Left</option>
        <option value="center">Center</option>
        <option value="right">Right</option>
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Title Color (optional)</label>
      <div class="flex gap-2 items-center">
        <input type="color" :value="(settings.title_color as string) || '#111827'" class="h-8 w-10 rounded cursor-pointer border border-gray-300" @input="update('title_color', ($event.target as HTMLInputElement).value)" />
        <input type="text" :value="settings.title_color as string" placeholder="inherit" class="uie-input flex-1" @input="update('title_color', ($event.target as HTMLInputElement).value)" />
      </div>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description Color (optional)</label>
      <div class="flex gap-2 items-center">
        <input type="color" :value="(settings.desc_color as string) || '#6b7280'" class="h-8 w-10 rounded cursor-pointer border border-gray-300" @input="update('desc_color', ($event.target as HTMLInputElement).value)" />
        <input type="text" :value="settings.desc_color as string" placeholder="inherit" class="uie-input flex-1" @input="update('desc_color', ($event.target as HTMLInputElement).value)" />
      </div>
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.uie-input { @apply w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent; }
</style>

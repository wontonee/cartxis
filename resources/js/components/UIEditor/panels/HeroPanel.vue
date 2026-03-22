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
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Headline</label>
      <input type="text" :value="settings.headline as string" class="uie-input" @input="update('headline', ($event.target as HTMLInputElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Subheading</label>
      <input type="text" :value="settings.subheading as string" class="uie-input" @input="update('subheading', ($event.target as HTMLInputElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">CTA Label</label>
      <input type="text" :value="settings.cta_text as string" class="uie-input" @input="update('cta_text', ($event.target as HTMLInputElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">CTA URL</label>
      <input type="text" :value="settings.cta_url as string" class="uie-input" @input="update('cta_url', ($event.target as HTMLInputElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Image URL</label>
      <input type="text" :value="settings.image as string" class="uie-input" @input="update('image', ($event.target as HTMLInputElement).value)" placeholder="https://… or /storage/…" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Height (px)</label>
      <input type="number" :value="settings.height as number" min="200" max="1000" class="uie-input" @change="update('height', Number(($event.target as HTMLInputElement).value))" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Text Align</label>
      <select :value="settings.text_align as string" class="uie-input" @change="update('text_align', ($event.target as HTMLSelectElement).value)">
        <option value="left">Left</option>
        <option value="center">Center</option>
        <option value="right">Right</option>
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Overlay Color</label>
      <div class="flex items-center gap-2">
        <input type="color" :value="settings.overlay_color as string" class="w-8 h-8 rounded border cursor-pointer" @input="update('overlay_color', ($event.target as HTMLInputElement).value)" />
        <input type="text" :value="settings.overlay_color as string" class="uie-input flex-1" @change="update('overlay_color', ($event.target as HTMLInputElement).value)" />
      </div>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Overlay Opacity ({{ settings.overlay_opacity ?? 40 }}%)</label>
      <input type="range" :value="settings.overlay_opacity as number" min="0" max="100" class="w-full" @input="update('overlay_opacity', Number(($event.target as HTMLInputElement).value))" />
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.uie-input {
  @apply w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent;
}
</style>

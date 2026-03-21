<script setup lang="ts">
import { ref } from 'vue'
import MediaPickerModal from '../MediaPickerModal.vue'

const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()
function update(key: string, value: unknown) { emit('update:settings', { ...props.settings, [key]: value }) }

const showPicker = ref(false)

function onImageSelected(url: string, alt: string) {
  emit('update:settings', {
    ...props.settings,
    image: url,
    alt: props.settings.alt || alt,
  })
}
</script>

<template>
  <div class="p-4 space-y-4">

    <!-- Image preview + browse button -->
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Image</label>

      <!-- Preview -->
      <div
        v-if="settings.image"
        class="relative mb-2 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800"
      >
        <img :src="settings.image as string" class="w-full max-h-32 object-contain" />
        <button
          type="button"
          class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-red-500/90 text-white flex items-center justify-center hover:bg-red-600 text-sm leading-none shadow"
          title="Remove image"
          @click="update('image', '')"
        >×</button>
      </div>

      <!-- Media picker button -->
      <button
        type="button"
        class="w-full py-2 px-3 text-sm font-medium text-blue-600 dark:text-blue-400 border border-blue-300 dark:border-blue-700 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-colors flex items-center justify-center gap-2"
        @click="showPicker = true"
      >
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        {{ settings.image ? 'Change Image' : 'Browse Media Library' }}
      </button>
    </div>

    <!-- Manual URL fallback -->
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Or enter URL directly</label>
      <input
        type="text"
        :value="settings.image as string"
        class="uie-input"
        placeholder="/storage/image.jpg or https://…"
        @input="update('image', ($event.target as HTMLInputElement).value)"
      />
    </div>

    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Alt Text</label>
      <input type="text" :value="settings.alt as string" class="uie-input" @input="update('alt', ($event.target as HTMLInputElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Caption</label>
      <input type="text" :value="settings.caption as string" class="uie-input" @input="update('caption', ($event.target as HTMLInputElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Link URL</label>
      <input type="text" :value="settings.link as string" class="uie-input" @input="update('link', ($event.target as HTMLInputElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Border Radius (px)</label>
      <input type="number" :value="settings.border_radius as number" min="0" max="100" class="uie-input" @change="update('border_radius', Number(($event.target as HTMLInputElement).value))" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Width</label>
      <input type="text" :value="settings.width as string" placeholder="100% or 400px" class="uie-input" @input="update('width', ($event.target as HTMLInputElement).value)" />
    </div>

    <MediaPickerModal v-model="showPicker" @select="onImageSelected" />
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.uie-input { @apply w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent; }
</style>


<script setup lang="ts">
import { computed } from 'vue'
import { useUiEditorStore } from '@/stores/uiEditorStore'
import type { PreviewMode } from '@/stores/uiEditorStore'

const store = useUiEditorStore()

const modes: { value: PreviewMode; label: string; icon: string }[] = [
  { value: 'desktop', label: 'Desktop', icon: 'monitor' },
  { value: 'tablet',  label: 'Tablet',  icon: 'tablet' },
  { value: 'mobile',  label: 'Mobile',  icon: 'smartphone' },
]

function setMode(mode: PreviewMode) {
  store.setPreviewMode(mode)
}
</script>

<template>
  <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
    <button
      v-for="mode in modes"
      :key="mode.value"
      type="button"
      :title="mode.label"
      :class="[
        'flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-150',
        store.previewMode === mode.value
          ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
          : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300',
      ]"
      @click="setMode(mode.value)"
    >
      <!-- Monitor icon -->
      <svg v-if="mode.icon === 'monitor'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <rect x="2" y="3" width="20" height="14" rx="2" stroke-width="2" />
        <path stroke-linecap="round" stroke-width="2" d="M8 21h8M12 17v4" />
      </svg>
      <!-- Tablet icon -->
      <svg v-else-if="mode.icon === 'tablet'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <rect x="4" y="2" width="16" height="20" rx="2" stroke-width="2" />
        <circle cx="12" cy="19" r="1" fill="currentColor" />
      </svg>
      <!-- Smartphone icon -->
      <svg v-else-if="mode.icon === 'smartphone'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <rect x="5" y="2" width="14" height="20" rx="2" stroke-width="2" />
        <circle cx="12" cy="18" r="1" fill="currentColor" />
      </svg>
      <span class="hidden sm:inline">{{ mode.label }}</span>
    </button>
  </div>
</template>

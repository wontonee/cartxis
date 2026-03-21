<script setup lang="ts">
defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()
</script>

<template>
  <!-- Editor placeholder when content is empty -->
  <div
    v-if="editorMode && !settings.content"
    class="flex items-center justify-center gap-2 py-6 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg text-gray-400 dark:text-gray-600 text-sm select-none"
  >
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
    </svg>
    HTML Block — add content in the panel
  </div>

  <!-- Editor preview badge + rendered content -->
  <div v-else class="relative">
    <div
      v-if="editorMode"
      class="absolute top-1 right-1 z-10 px-1.5 py-0.5 text-[10px] font-bold bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-400 rounded select-none pointer-events-none"
    >
      HTML
    </div>
    <!-- eslint-disable-next-line vue/no-v-html -->
    <div class="uie-html-output" v-html="settings.content as string" />
  </div>
</template>

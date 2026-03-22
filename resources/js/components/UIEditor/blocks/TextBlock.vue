<script setup lang="ts">
defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()
</script>

<template>
  <div
    class="prose dark:prose-invert max-w-none px-1 py-1 text-sm leading-relaxed"
    v-html="(settings.content as string) || (editorMode ? '<p class=\'text-gray-400\'>Enter your text here…</p>' : '')"
  />
</template>

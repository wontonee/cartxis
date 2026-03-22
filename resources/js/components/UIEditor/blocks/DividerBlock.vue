<script setup lang="ts">
defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()
</script>

<template>
  <div class="py-2 flex justify-center">
    <hr
      class="border-0"
      :style="{
        width: (settings.width as string) || '100%',
        height: '1px',
        backgroundColor: (settings.color as string) || '#e5e7eb',
        borderStyle: (settings.style as string) || 'solid',
      }"
    />
  </div>
</template>

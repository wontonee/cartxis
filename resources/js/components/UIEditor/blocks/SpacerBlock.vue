<script setup lang="ts">
defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()
</script>

<template>
  <div :style="{ height: `${settings.height ?? 40}px` }" />
</template>

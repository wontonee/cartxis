<script setup lang="ts">
defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()
</script>

<template>
  <component
    :is="(settings.level as string) || 'h2'"
    class="font-bold leading-tight"
    :class="{
      'text-left':   settings.align === 'left',
      'text-center': settings.align === 'center',
      'text-right':  settings.align === 'right',
      'text-4xl': settings.level === 'h1',
      'text-3xl': settings.level === 'h2',
      'text-2xl': settings.level === 'h3',
      'text-xl':  settings.level === 'h4',
      'text-lg':  settings.level === 'h5',
      'text-base':settings.level === 'h6',
    }"
    :style="settings.color ? { color: settings.color as string } : {}"
  >
    {{ (settings.text as string) || (editorMode ? 'Your Heading' : '') }}
  </component>
</template>

<script setup lang="ts">
defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()
</script>

<template>
  <div
    class="py-1"
    :class="{
      'text-left':   settings.align === 'left',
      'text-center': settings.align === 'center',
      'text-right':  settings.align === 'right',
    }"
  >
    <a
      :href="(settings.url as string) || '#'"
      class="inline-block font-semibold rounded-lg transition-colors"
      :class="{
        'px-6 py-3 text-base': settings.size === 'lg',
        'px-4 py-2 text-sm':   !settings.size || settings.size === 'md',
        'px-3 py-1.5 text-xs': settings.size === 'sm',
        'bg-blue-600 hover:bg-blue-700 text-white':                     settings.variant === 'primary' || !settings.variant,
        'bg-gray-200 hover:bg-gray-300 text-gray-900 dark:bg-gray-700 dark:text-white': settings.variant === 'secondary',
        'border-2 border-blue-600 text-blue-600 hover:bg-blue-50':      settings.variant === 'outline',
      }"
    >
      {{ (settings.label as string) || 'Click Here' }}
    </a>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

const label = computed(() =>
  String(props.settings.label ?? props.settings.text ?? 'Click Here'),
)

const variant = computed(() => {
  const style = props.settings.style as string | undefined
  const explicit = props.settings.variant as string | undefined
  if (explicit) return explicit
  if (style === 'outline') return 'outline'
  if (style === 'secondary') return 'secondary'
  return 'primary'
})

const buttonStyle = computed(() => {
  const bg = props.settings.bg_color as string | undefined
  const color = props.settings.text_color as string | undefined
  if (!bg && !color) return undefined
  return {
    backgroundColor: variant.value === 'outline' ? 'transparent' : (bg ?? undefined),
    color: color ?? undefined,
    borderColor: variant.value === 'outline' ? (bg ?? color ?? '#2563eb') : undefined,
  }
})
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
        'bg-blue-600 hover:bg-blue-700 text-white': variant === 'primary' && !buttonStyle,
        'bg-gray-200 hover:bg-gray-300 text-gray-900 dark:bg-gray-700 dark:text-white': variant === 'secondary' && !buttonStyle,
        'border-2 border-blue-600 text-blue-600 hover:bg-blue-50': variant === 'outline' && !buttonStyle,
        'border-2': variant === 'outline' && !!buttonStyle,
      }"
      :style="buttonStyle"
    >
      {{ label }}
    </a>
  </div>
</template>

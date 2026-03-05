<script setup lang="ts">
import { ref, onMounted } from 'vue'

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

const displayValue = ref(0)

onMounted(() => {
  const target  = (props.settings.number as number) ?? 1000
  const duration = (props.settings.duration as number) ?? 2000

  if (props.editorMode) {
    // No animation in editor — just show the final number
    displayValue.value = target
    return
  }

  const steps     = Math.min(60, Math.ceil(duration / 16))
  const increment = target / steps
  let current     = 0
  let step        = 0

  const timer = setInterval(() => {
    step++
    current += increment
    displayValue.value = Math.min(Math.round(current), target)
    if (step >= steps) {
      displayValue.value = target
      clearInterval(timer)
    }
  }, duration / steps)
})
</script>

<template>
  <div
    class="py-6 px-4 flex flex-col"
    :class="{
      'items-start text-left':   settings.align === 'left',
      'items-center text-center': !settings.align || settings.align === 'center',
      'items-end text-right':    settings.align === 'right',
    }"
  >
    <div
      class="text-5xl font-bold tabular-nums leading-none"
      :style="{ color: (settings.color as string) || '#3b82f6' }"
    >
      {{ (settings.prefix as string) || '' }}{{ displayValue.toLocaleString() }}{{ (settings.suffix as string) || '' }}
    </div>
    <div class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">
      {{ (settings.label as string) || 'Stat Label' }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { PreviewMode } from '@/stores/uiEditorStore'

const props = defineProps<{
  mode: PreviewMode
}>()

const config = {
  desktop: { width: null,  label: 'Desktop' },
  tablet:  { width: 768,   label: 'Tablet (768px)' },
  mobile:  { width: 390,   label: 'Mobile (390px)' },
}

const current = computed(() => config[props.mode])
</script>

<template>
  <!-- Desktop: no frame wrapper needed, slot renders at full width -->
  <div v-if="mode === 'desktop'" class="w-full">
    <slot />
  </div>

  <!-- Tablet / Mobile: centered device frame -->
  <div v-else class="flex flex-col items-center w-full py-4">
    <!-- Device label -->
    <span class="mb-2 text-xs font-medium text-gray-400 dark:text-gray-500 tracking-wide uppercase">
      {{ current.label }}
    </span>

    <!-- Device frame shell -->
    <div
      class="relative bg-gray-900 dark:bg-gray-800 shadow-2xl transition-all duration-300 ease-in-out"
      :class="mode === 'mobile' ? 'rounded-[2.5rem] p-3' : 'rounded-2xl p-2.5'"
      :style="current.width ? { width: `${current.width + 24}px`, maxWidth: '100%' } : {}"
    >
      <!-- Top notch (mobile only) -->
      <div
        v-if="mode === 'mobile'"
        class="absolute top-0 left-1/2 -translate-x-1/2 w-24 h-5 bg-gray-900 dark:bg-gray-800 rounded-b-xl z-10"
      />

      <!-- Screen area -->
      <div
        class="relative overflow-hidden bg-white dark:bg-gray-950"
        :class="mode === 'mobile' ? 'rounded-[2rem] mt-2' : 'rounded-xl'"
        :style="current.width ? { width: `${current.width}px`, maxWidth: '100%' } : {}"
      >
        <slot />
      </div>

      <!-- Home indicator (mobile only) -->
      <div
        v-if="mode === 'mobile'"
        class="flex justify-center mt-2.5"
      >
        <div class="w-28 h-1 bg-gray-600 rounded-full" />
      </div>
    </div>
  </div>
</template>

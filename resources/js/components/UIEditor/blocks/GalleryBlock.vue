<script setup lang="ts">
defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()
</script>

<template>
  <div class="w-full">
    <!-- Grid layout -->
    <div
      v-if="settings.layout === 'grid' || settings.layout === 'masonry' || !settings.layout"
      class="grid gap-2"
      :style="{ gridTemplateColumns: `repeat(${settings.columns ?? 3}, 1fr)`, gap: `${settings.gap ?? 8}px` }"
    >
      <div
        v-for="(img, i) in (settings.images as string[])"
        :key="i"
        class="overflow-hidden rounded"
      >
        <img :src="img" :alt="`Gallery image ${i + 1}`" class="w-full h-40 object-cover" />
      </div>
      <!-- Empty state -->
      <template v-if="!(settings.images as string[])?.length && editorMode">
        <div
          v-for="n in 3"
          :key="n"
          class="h-40 bg-gray-100 dark:bg-gray-800 rounded border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center text-gray-400 text-xs"
        >
          Image {{ n }}
        </div>
      </template>
    </div>
    <!-- Simple carousel (scroll) -->
    <div
      v-else-if="settings.layout === 'carousel'"
      class="flex gap-3 overflow-x-auto pb-2 snap-x"
    >
      <div
        v-for="(img, i) in (settings.images as string[])"
        :key="i"
        class="flex-shrink-0 snap-start"
      >
        <img :src="img" :alt="`Gallery image ${i + 1}`" class="h-48 w-auto rounded object-cover" />
      </div>
    </div>
  </div>
</template>

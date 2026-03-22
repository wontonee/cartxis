<script setup lang="ts">
defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()
</script>

<template>
  <div class="group rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow bg-white dark:bg-gray-800">
    <!-- Card image -->
    <div v-if="settings.image || editorMode" class="w-full h-48 bg-gray-100 dark:bg-gray-700 overflow-hidden">
      <img
        v-if="settings.image"
        :src="settings.image as string"
        alt=""
        class="w-full h-full object-cover"
      />
      <div v-else class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600 text-sm">
        Card Image
      </div>
    </div>
    <div class="p-4">
      <span
        v-if="settings.badge"
        class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 mb-2"
      >
        {{ settings.badge }}
      </span>
      <h3 class="font-semibold text-gray-900 dark:text-white text-base mb-1">
        {{ (settings.title as string) || (editorMode ? 'Card Title' : '') }}
      </h3>
      <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
        {{ (settings.body as string) || (editorMode ? 'Card description goes here.' : '') }}
      </p>
      <div v-if="settings.footer || settings.link" class="mt-3 flex items-center justify-between">
        <span v-if="settings.footer" class="text-xs text-gray-400">{{ settings.footer }}</span>
        <a
          v-if="settings.link"
          :href="settings.link as string"
          class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline"
        >
          Learn more →
        </a>
      </div>
    </div>
  </div>
</template>

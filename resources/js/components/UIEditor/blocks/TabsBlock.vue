<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

type Tab = { label: string; content: string }

const active = ref((props.settings.active_tab as number) ?? 0)
</script>

<template>
  <div class="w-full">
    <!-- Tab buttons -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
      <button
        v-for="(tab, i) in (settings.tabs as Tab[])"
        :key="i"
        type="button"
        class="flex-shrink-0 px-4 py-2.5 text-sm font-medium transition-colors whitespace-nowrap"
        :class="active === i
          ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400 -mb-px bg-white dark:bg-gray-900'
          : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'"
        @click="active = i"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Tab content -->
    <div
      v-for="(tab, i) in (settings.tabs as Tab[])"
      :key="i"
      v-show="active === i"
      class="p-4 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-t-0 border-gray-200 dark:border-gray-700 rounded-b-lg"
      v-html="tab.content"
    />

    <div v-if="!(settings.tabs as unknown[])?.length" class="p-4 text-sm text-gray-400 text-center border border-gray-200 dark:border-gray-700 rounded-b-lg">
      No tabs yet
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()

type Tab = { label: string; content: string }
const tabs = computed(() => (props.settings.tabs as Tab[]) ?? [])

function update(key: string, value: unknown) {
  emit('update:settings', { ...props.settings, [key]: value })
}

function addTab() {
  update('tabs', [...tabs.value, { label: `Tab ${tabs.value.length + 1}`, content: '<p>Tab content here.</p>' }])
}

function removeTab(i: number) {
  const copy = [...tabs.value]
  copy.splice(i, 1)
  update('tabs', copy)
}

function updateTab(i: number, key: string, value: unknown) {
  const copy = tabs.value.map((t, idx) => idx === i ? { ...t, [key]: value } : t)
  update('tabs', copy)
}
</script>

<template>
  <div class="p-4 space-y-4">
    <div class="space-y-3">
      <div
        v-for="(tab, i) in tabs"
        :key="i"
        class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 space-y-2"
      >
        <div class="flex items-center gap-2">
          <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 flex-1">Tab {{ i + 1 }}</span>
          <button type="button" class="text-xs text-red-500 hover:text-red-700" @click="removeTab(i)">Remove</button>
        </div>
        <div>
          <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Label</label>
          <input
            type="text"
            :value="tab.label"
            class="uie-input"
            @input="updateTab(i, 'label', ($event.target as HTMLInputElement).value)"
          />
        </div>
        <div>
          <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Content (HTML)</label>
          <textarea
            :value="tab.content"
            rows="3"
            class="uie-input resize-none"
            @input="updateTab(i, 'content', ($event.target as HTMLTextAreaElement).value)"
          />
        </div>
      </div>
    </div>

    <button
      type="button"
      class="w-full py-2 text-sm text-blue-600 dark:text-blue-400 border border-dashed border-blue-300 dark:border-blue-600 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
      @click="addTab"
    >
      + Add Tab
    </button>
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.uie-input { @apply w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent; }
</style>

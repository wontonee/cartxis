<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()

type Item = { title: string; content: string; open: boolean }

const items = computed(() => (props.settings.items as Item[]) ?? [])

function update(key: string, value: unknown) {
  emit('update:settings', { ...props.settings, [key]: value })
}

function addItem() {
  update('items', [...items.value, { title: 'New Question', content: 'Answer goes here.', open: false }])
}

function removeItem(i: number) {
  const copy = [...items.value]
  copy.splice(i, 1)
  update('items', copy)
}

function updateItem(i: number, key: string, value: unknown) {
  const copy = items.value.map((item, idx) => idx === i ? { ...item, [key]: value } : item)
  update('items', copy)
}
</script>

<template>
  <div class="p-4 space-y-4">
    <!-- Allow multiple -->
    <div class="flex items-center gap-2">
      <input
        id="allow-multiple"
        type="checkbox"
        :checked="!!settings.allow_multiple"
        class="rounded border-gray-300 dark:border-gray-600 text-blue-600"
        @change="update('allow_multiple', ($event.target as HTMLInputElement).checked)"
      />
      <label for="allow-multiple" class="text-xs font-medium text-gray-700 dark:text-gray-300">Allow multiple open</label>
    </div>

    <!-- Items -->
    <div class="space-y-3">
      <div
        v-for="(item, i) in items"
        :key="i"
        class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 space-y-2"
      >
        <div class="flex items-center gap-2">
          <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 flex-1">Item {{ i + 1 }}</span>
          <button
            type="button"
            class="text-xs text-red-500 hover:text-red-700"
            @click="removeItem(i)"
          >Remove</button>
        </div>
        <div>
          <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Title</label>
          <input
            type="text"
            :value="item.title"
            class="uie-input"
            @input="updateItem(i, 'title', ($event.target as HTMLInputElement).value)"
          />
        </div>
        <div>
          <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Content</label>
          <textarea
            :value="item.content"
            rows="3"
            class="uie-input resize-none"
            @input="updateItem(i, 'content', ($event.target as HTMLTextAreaElement).value)"
          />
        </div>
        <div class="flex items-center gap-2">
          <input
            :id="`item-open-${i}`"
            type="checkbox"
            :checked="!!item.open"
            class="rounded border-gray-300 text-blue-600"
            @change="updateItem(i, 'open', ($event.target as HTMLInputElement).checked)"
          />
          <label :for="`item-open-${i}`" class="text-xs text-gray-500 dark:text-gray-400">Open by default</label>
        </div>
      </div>
    </div>

    <button
      type="button"
      class="w-full py-2 text-sm text-blue-600 dark:text-blue-400 border border-dashed border-blue-300 dark:border-blue-600 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
      @click="addItem"
    >
      + Add Item
    </button>
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.uie-input { @apply w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent; }
</style>

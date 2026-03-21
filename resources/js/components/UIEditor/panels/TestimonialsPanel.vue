<script setup lang="ts">
import { ref } from 'vue'
const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()

interface TestItem { author: string; avatar: string | null; text: string; rating: number }

function update(key: string, value: unknown) { emit('update:settings', { ...props.settings, [key]: value }) }
function updateItem(i: number, key: keyof TestItem, value: unknown) {
  const items = JSON.parse(JSON.stringify(props.settings.items ?? [])) as TestItem[]
  items[i] = { ...items[i], [key]: value }
  update('items', items)
}
function addItem() {
  const items = [...((props.settings.items as TestItem[]) ?? []), { author: 'Customer Name', avatar: null, text: 'Great experience!', rating: 5 }]
  update('items', items)
}
function removeItem(i: number) {
  const items = [...((props.settings.items as TestItem[]) ?? [])]
  items.splice(i, 1)
  update('items', items)
}
</script>

<template>
  <div class="p-4 space-y-4">
    <div v-for="(item, i) in ((settings.items as TestItem[]) ?? [])" :key="i" class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 space-y-2">
      <div class="flex justify-between items-center">
        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Testimonial {{ i + 1 }}</span>
        <button type="button" class="text-xs text-red-500 hover:text-red-700" @click="removeItem(i)">Remove</button>
      </div>
      <input type="text" :value="item.author" placeholder="Author Name" class="uie-input text-xs" @input="updateItem(i, 'author', ($event.target as HTMLInputElement).value)" />
      <textarea :value="item.text" placeholder="Review text…" rows="2" class="uie-input text-xs resize-none" @input="updateItem(i, 'text', ($event.target as HTMLTextAreaElement).value)" />
      <div class="flex items-center gap-2">
        <label class="text-xs text-gray-600 dark:text-gray-400">Rating:</label>
        <select :value="item.rating" class="uie-input w-20 text-xs" @change="updateItem(i, 'rating', Number(($event.target as HTMLSelectElement).value))">
          <option v-for="n in [1,2,3,4,5]" :key="n" :value="n">{{ n }} ★</option>
        </select>
      </div>
    </div>
    <button type="button" class="w-full py-2 text-sm border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-gray-500 hover:border-blue-400 hover:text-blue-600 transition-colors" @click="addItem">
      + Add Testimonial
    </button>
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.uie-input { @apply w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent; }
</style>

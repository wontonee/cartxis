<script setup lang="ts">
import { ref, computed } from 'vue'
import axios from 'axios'

const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()
function update(key: string, value: unknown) { emit('update:settings', { ...props.settings, [key]: value }) }

interface ProductOption { id: number; name: string }
const search    = ref('')
const options   = ref<ProductOption[]>([])
const searching = ref(false)

async function doSearch() {
  if (!search.value.trim()) { options.value = []; return }
  searching.value = true
  try {
    const res = await axios.get(route('admin.uieditor.products.search'), { params: { search: search.value, per_page: 10 } })
    options.value = res.data.data ?? []
  } finally {
    searching.value = false
  }
}

const selectedIds = computed(() => (props.settings.product_ids as number[]) ?? [])

function toggleProduct(id: number) {
  const ids = [...selectedIds.value]
  const i = ids.indexOf(id)
  if (i !== -1) ids.splice(i, 1)
  else ids.push(id)
  update('product_ids', ids)
}
</script>

<template>
  <div class="p-4 space-y-4">
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Search & Select Products</label>
      <div class="flex gap-2 mb-2">
        <input v-model="search" type="text" placeholder="Search products…" class="uie-input flex-1" @keydown.enter.prevent="doSearch" />
        <button type="button" :disabled="searching" class="px-2 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" @click="doSearch">Search</button>
      </div>
      <div v-if="options.length" class="max-h-40 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg divide-y divide-gray-100 dark:divide-gray-700">
        <label v-for="opt in options" :key="opt.id" class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer text-sm">
          <input type="checkbox" :checked="selectedIds.includes(opt.id)" class="rounded text-blue-600" @change="toggleProduct(opt.id)" />
          <span class="text-gray-700 dark:text-gray-300">{{ opt.name }}</span>
        </label>
      </div>
      <p v-if="selectedIds.length" class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ selectedIds.length }} product(s) selected</p>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Columns</label>
      <input type="number" :value="settings.columns as number" min="2" max="6" class="uie-input" @change="update('columns', Number(($event.target as HTMLInputElement).value))" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Max Products</label>
      <input type="number" :value="settings.limit as number" min="1" max="50" class="uie-input" @change="update('limit', Number(($event.target as HTMLInputElement).value))" />
    </div>
    <div class="flex items-center gap-2">
      <input id="pg_price" type="checkbox" :checked="settings.show_price as boolean" class="rounded text-blue-600" @change="update('show_price', ($event.target as HTMLInputElement).checked)" />
      <label for="pg_price" class="text-sm text-gray-700 dark:text-gray-300">Show Price</label>
    </div>
    <div class="flex items-center gap-2">
      <input id="pg_cart" type="checkbox" :checked="settings.show_cart as boolean" class="rounded text-blue-600" @change="update('show_cart', ($event.target as HTMLInputElement).checked)" />
      <label for="pg_cart" class="text-sm text-gray-700 dark:text-gray-300">Show Add to Cart</label>
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.uie-input { @apply w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent; }
</style>

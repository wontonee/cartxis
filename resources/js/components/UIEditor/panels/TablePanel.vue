<script setup lang="ts">
import { ref } from 'vue'
const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()
function update(key: string, value: unknown) { emit('update:settings', { ...props.settings, [key]: value }) }

function updateHeader(i: number, value: string) {
  const headers = [...((props.settings.headers as string[]) ?? [])]
  headers[i] = value
  update('headers', headers)
}
function addColumn() {
  const headers = [...((props.settings.headers as string[]) ?? []), 'Column']
  const rows = ((props.settings.rows as string[][]) ?? []).map(r => [...r, ''])
  emit('update:settings', { ...props.settings, headers, rows })
}
function addRow() {
  const cols = ((props.settings.headers as string[]) ?? []).length || 2
  const rows = [...((props.settings.rows as string[][]) ?? []), Array(cols).fill('')]
  update('rows', rows)
}
function updateCell(ri: number, ci: number, value: string) {
  const rows = JSON.parse(JSON.stringify(props.settings.rows ?? [])) as string[][]
  rows[ri][ci] = value
  update('rows', rows)
}
function removeRow(ri: number) {
  const rows = [...((props.settings.rows as string[][]) ?? [])]
  rows.splice(ri, 1)
  update('rows', rows)
}
</script>

<template>
  <div class="p-4 space-y-4">
    <div class="flex items-center gap-2">
      <input id="tb_striped" type="checkbox" :checked="settings.striped as boolean" class="rounded text-blue-600" @change="update('striped', ($event.target as HTMLInputElement).checked)" />
      <label for="tb_striped" class="text-sm text-gray-700 dark:text-gray-300">Striped rows</label>
    </div>
    <div class="flex items-center gap-2">
      <input id="tb_bordered" type="checkbox" :checked="settings.bordered as boolean" class="rounded text-blue-600" @change="update('bordered', ($event.target as HTMLInputElement).checked)" />
      <label for="tb_bordered" class="text-sm text-gray-700 dark:text-gray-300">Bordered</label>
    </div>
    <div>
      <div class="flex items-center justify-between mb-1">
        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Headers</label>
        <button type="button" class="text-xs text-blue-600 hover:text-blue-700" @click="addColumn">+ Column</button>
      </div>
      <div class="flex gap-1.5 flex-wrap">
        <input
          v-for="(h, i) in ((settings.headers as string[]) ?? [])"
          :key="i"
          type="text"
          :value="h"
          class="uie-input w-24 text-xs"
          @input="updateHeader(i, ($event.target as HTMLInputElement).value)"
        />
      </div>
    </div>
    <div>
      <div class="flex items-center justify-between mb-1">
        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Rows</label>
        <button type="button" class="text-xs text-blue-600 hover:text-blue-700" @click="addRow">+ Row</button>
      </div>
      <div v-for="(row, ri) in ((settings.rows as string[][]) ?? [])" :key="ri" class="flex items-center gap-1.5 mb-1">
        <input
          v-for="(cell, ci) in row"
          :key="ci"
          type="text"
          :value="cell"
          class="uie-input w-24 text-xs"
          @input="updateCell(ri, ci, ($event.target as HTMLInputElement).value)"
        />
        <button type="button" class="text-xs text-red-500 hover:text-red-700 flex-shrink-0" @click="removeRow(ri)">✕</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.uie-input { @apply w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent; }
</style>

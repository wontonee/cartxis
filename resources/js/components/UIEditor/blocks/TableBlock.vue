<script setup lang="ts">
defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()
</script>

<template>
  <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 my-1">
    <table class="min-w-full text-sm">
      <thead>
        <tr class="bg-gray-50 dark:bg-gray-800">
          <th
            v-for="(header, i) in ((settings.headers as string[]) ?? [])"
            :key="i"
            class="px-4 py-2.5 text-left font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700"
            :class="settings.bordered ? 'border-r border-gray-200 dark:border-gray-700 last:border-r-0' : ''"
          >
            {{ header }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(row, ri) in ((settings.rows as string[][]) ?? [])"
          :key="ri"
          :class="settings.striped && ri % 2 !== 0 ? 'bg-gray-50 dark:bg-gray-800/50' : ''"
        >
          <td
            v-for="(cell, ci) in row"
            :key="ci"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700"
            :class="settings.bordered ? 'border-r border-gray-100 dark:border-gray-700 last:border-r-0' : ''"
          >
            {{ cell }}
          </td>
        </tr>
        <!-- Empty state -->
        <tr v-if="editorMode && !(settings.rows as unknown[])?.length">
          <td
            :colspan="(settings.headers as string[])?.length ?? 2"
            class="text-center py-6 text-gray-400 text-sm"
          >
            Add rows in the settings panel
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

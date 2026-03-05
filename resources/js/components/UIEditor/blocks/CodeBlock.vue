<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

const lines = computed(() => {
  const raw = (props.settings.code as string) ?? ''
  return raw.split('\n')
})

const langLabel: Record<string, string> = {
  html: 'HTML', css: 'CSS', js: 'JavaScript', ts: 'TypeScript',
  php: 'PHP', python: 'Python', json: 'JSON', bash: 'Shell',
  sql: 'SQL', vue: 'Vue', jsx: 'JSX', tsx: 'TSX', xml: 'XML', yaml: 'YAML',
}
</script>

<template>
  <div class="rounded-lg overflow-hidden border border-gray-800 dark:border-gray-700 font-mono text-sm">
    <!-- Header bar -->
    <div class="flex items-center justify-between px-4 py-2 bg-gray-800 dark:bg-gray-900">
      <div class="flex items-center gap-2">
        <!-- Traffic-light dots -->
        <span class="w-2.5 h-2.5 rounded-full bg-red-500 opacity-70" />
        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 opacity-70" />
        <span class="w-2.5 h-2.5 rounded-full bg-green-500 opacity-70" />
        <span v-if="settings.filename" class="ml-2 text-xs text-gray-400">
          {{ settings.filename }}
        </span>
      </div>
      <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">
        {{ langLabel[settings.language as string] ?? settings.language }}
      </span>
    </div>

    <!-- Code content -->
    <div class="overflow-x-auto bg-gray-950 dark:bg-gray-950 p-4">
      <table class="w-full border-collapse text-xs leading-relaxed">
        <tbody>
          <tr v-for="(line, i) in lines" :key="i" class="hover:bg-white/5 transition-colors">
            <!-- Line number -->
            <td
              v-if="settings.show_line_numbers"
              class="select-none pr-4 text-right text-gray-600 dark:text-gray-600 w-px whitespace-nowrap"
              style="min-width: 2rem"
            >
              {{ i + 1 }}
            </td>
            <!-- Code -->
            <td class="text-gray-100 whitespace-pre pl-2">{{ line || ' ' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { LayoutTemplate, ExternalLink, CheckCircle, Clock } from 'lucide-vue-next'

const props = defineProps<{
  settings: Record<string, unknown>
}>()

// read-only panel — no settings emission needed
const regionId   = computed(() => props.settings.region_id   as number | null)
const regionName = computed(() => props.settings.region_name as string || 'Unknown Region')
const regionType = computed(() => props.settings.region_type as string || 'section')
const status     = computed(() => props.settings.region_status as string || 'draft')

const typeColors: Record<string, string> = {
  header:  'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
  footer:  'bg-gray-100 text-gray-600 dark:bg-gray-700',
  section: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
  banner:  'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
  sidebar: 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300',
}
</script>

<template>
  <div class="space-y-4 p-4">
    <!-- Info card -->
    <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-3">
      <div class="flex items-start gap-2">
        <LayoutTemplate class="w-4 h-4 text-indigo-500 mt-0.5 flex-shrink-0" />
        <div class="min-w-0">
          <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide mb-1">Linked Reusable Section</p>
          <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ regionName }}</p>
          <div class="flex items-center gap-2 mt-1.5">
            <span :class="['text-xs font-semibold px-2 py-0.5 rounded-full capitalize', typeColors[regionType] ?? typeColors.section]">
              {{ regionType }}
            </span>
            <span :class="['inline-flex items-center gap-1 text-xs', status === 'published' ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400']">
              <CheckCircle v-if="status === 'published'" class="w-3 h-3" />
              <Clock v-else class="w-3 h-3" />
              {{ status === 'published' ? 'Live' : 'Draft' }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Link to edit the region -->
    <a
      v-if="regionId"
      :href="`/admin/uieditor/regions/${regionId}/editor`"
      target="_blank"
      class="flex items-center justify-center gap-2 w-full px-4 py-2 text-sm font-medium text-indigo-600 border border-indigo-300 dark:border-indigo-700 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
    >
      <ExternalLink class="w-3.5 h-3.5" />
      Open Region Editor
    </a>

    <p class="text-xs text-gray-400 dark:text-gray-500 text-center leading-relaxed">
      Editing this region will update <strong>all pages</strong> that use it — changes are live-linked.
    </p>
  </div>
</template>

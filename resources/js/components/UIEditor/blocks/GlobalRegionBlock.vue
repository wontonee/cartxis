<script setup lang="ts">
/**
 * GlobalRegionBlock.vue
 *
 * In EDITOR MODE: renders a visual placeholder showing the region name, status,
 * and a link to open the region editor. The actual region layout is fetched and
 * previewed inline.
 *
 * In STOREFRONT MODE: the backend inlines the region's published_layout_data into
 * the page's layout JSON before serving, so `UIBlockRenderer` renders the region
 * content directly. This component is only used as a fallback / editor view.
 */
import { ref, computed, onMounted } from 'vue'
import { LayoutTemplate, ExternalLink, AlertCircle, CheckCircle, Clock } from 'lucide-vue-next'
import UIBlockRenderer from '../UIBlockRenderer.vue'
import axios from 'axios'

const props = defineProps<{
  settings: {
    region_id?: number
    region_name?: string
    region_type?: string
    region_status?: string
  }
  editorMode?: boolean
}>()

const regionName = computed(() => props.settings.region_name || 'Unknown Region')
const regionType = computed(() => props.settings.region_type || 'section')
const regionStatus = computed(() => props.settings.region_status || 'draft')
const regionId = computed(() => props.settings.region_id)

// In editor mode, fetch the region's current layout to preview it
const previewLayout = ref<any>(null)
const loadError = ref(false)

onMounted(async () => {
  if (!props.editorMode || !regionId.value) return
  try {
    const res = await axios.get(`/admin/uieditor/regions/${regionId.value}/preview-data`)
    previewLayout.value = res.data.layout_data
  } catch {
    loadError.value = true
  }
})

const typeColorMap: Record<string, string> = {
  header:  'border-purple-300 bg-purple-50 dark:bg-purple-900/10',
  footer:  'border-gray-300 bg-gray-50 dark:bg-gray-900/10',
  section: 'border-blue-300 bg-blue-50 dark:bg-blue-900/10',
  banner:  'border-orange-300 bg-orange-50 dark:bg-orange-900/10',
  sidebar: 'border-teal-300 bg-teal-50 dark:bg-teal-900/10',
}
const borderColor = computed(() => typeColorMap[regionType.value] ?? typeColorMap.section)
</script>

<template>
  <!-- ── EDITOR MODE ──────────────────────────────────────────────────────── -->
  <div v-if="editorMode" class="w-full">
    <!-- Region header badge -->
    <div
      :class="[
        'flex items-center justify-between px-4 py-2 border-2 rounded-t-lg text-xs font-semibold select-none',
        borderColor,
      ]"
    >
      <div class="flex items-center gap-2">
        <LayoutTemplate class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" />
        <span class="text-gray-700 dark:text-gray-200">Reusable Section:</span>
        <span class="font-bold text-gray-900 dark:text-white">{{ regionName }}</span>
        <span class="text-gray-400 capitalize">({{ regionType }})</span>
      </div>
      <div class="flex items-center gap-3">
        <span
          :class="[
            'inline-flex items-center gap-1',
            regionStatus === 'published' ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400',
          ]"
        >
          <CheckCircle v-if="regionStatus === 'published'" class="w-3 h-3" />
          <Clock v-else class="w-3 h-3" />
          {{ regionStatus === 'published' ? 'Live' : 'Draft' }}
        </span>
        <a
          v-if="regionId"
          :href="`/admin/uieditor/regions/${regionId}/editor`"
          target="_blank"
          class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline"
          title="Open region editor"
        >
          <ExternalLink class="w-3 h-3" />
          Edit Region
        </a>
      </div>
    </div>

    <!-- Region preview (fetched layout rendered inline) -->
    <div
      :class="[
        'border-2 border-t-0 rounded-b-lg overflow-hidden pointer-events-none',
        borderColor,
      ]"
    >
      <div v-if="loadError" class="flex items-center gap-2 px-4 py-6 text-sm text-red-500 dark:text-red-400">
        <AlertCircle class="w-4 h-4" />
        Could not load region preview.
      </div>
      <div v-else-if="previewLayout">
        <UIBlockRenderer :layout="previewLayout" :editor-mode="false" />
      </div>
      <div v-else class="flex items-center justify-center px-4 py-10 text-sm text-gray-400 dark:text-gray-500">
        <span v-if="!regionId">No region selected.</span>
        <span v-else>Loading preview…</span>
      </div>
    </div>
  </div>

  <!-- ── STOREFRONT MODE (should be hydrated by backend; this is a fallback) ── -->
  <div v-else class="w-full">
    <div v-if="previewLayout">
      <UIBlockRenderer :layout="previewLayout" :editor-mode="false" />
    </div>
    <!-- Silently empty if not hydrated -->
  </div>
</template>

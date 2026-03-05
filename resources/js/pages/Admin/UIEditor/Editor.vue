<script setup lang="ts">
import { onMounted, computed, ref, onBeforeUnmount } from 'vue'
import { useUiEditorStore } from '@/stores/uiEditorStore'
import type { PageSettingsData } from '@/stores/uiEditorStore'
import PreviewSwitcher from '@/components/UIEditor/PreviewSwitcher.vue'
import PublishControls from '@/components/UIEditor/PublishControls.vue'
import DeviceFrame from '@/components/UIEditor/DeviceFrame.vue'
import EditorCanvas from '@/components/UIEditor/EditorCanvas.vue'
import BlockPalette from '@/components/UIEditor/BlockPalette.vue'
import PropertiesPanel from '@/components/UIEditor/PropertiesPanel.vue'
import PageSettingsPanel from '@/components/UIEditor/PageSettingsPanel.vue'

interface PageInfo {
  id: number | null
  title: string
  url: string
  url_key: string
  status: string
  is_homepage: boolean
  meta_title: string | null
  meta_description: string | null
  meta_keywords: string | null
}
interface RegionMeta {
  id: number
  name: string
  slug: string
  description: string | null
  region_type: string
  status: string
}
interface BlockTypeDef { type: string; label: string; category: string; icon: string; defaults: Record<string, unknown> }

const props = defineProps<{
  page: PageInfo | null
  pageType: 'cms_page' | 'homepage' | 'global_region'
  regionMeta?: RegionMeta | null
  layoutData: Record<string, unknown> | null
  layoutStatus: 'draft' | 'published' | null
  publishedAt: string | null
  blockTypes: BlockTypeDef[]
  saveUrl: string
  publishUrl: string
  unpublishUrl: string
  previewUrl: string
}>()

const store = useUiEditorStore()

onMounted(() => {
  store.loadLayout(props.layoutData, props.layoutStatus ?? 'draft', props.publishedAt)
  store.setUrls({
    saveUrl: props.saveUrl,
    publishUrl: props.publishUrl,
    unpublishUrl: props.unpublishUrl,
    previewUrl: props.previewUrl,
  })
  store.setBlockTypes(props.blockTypes)
  if (props.page) {
    store.setPageSettings(props.page as unknown as PageSettingsData)
  }
})

const editorTitle = computed(() => {
  if (props.pageType === 'homepage') return 'Homepage Editor'
  if (props.pageType === 'global_region') {
    return props.regionMeta?.name ? `Region Editor — ${props.regionMeta.name}` : 'Region Editor'
  }
  const title = store.pageSettings?.title ?? props.page?.title
  return title ? `Block Editor — ${title}` : 'Block Editor'
})

const iframeStyle = computed(() => {
  const map = { desktop: '100%', tablet: '768px', mobile: '390px' }
  return { width: map[store.previewMode], height: 'calc(100vh - 56px)', border: 'none' }
})

function goBack() {
  if (props.pageType === 'global_region') {
    window.location.href = '/admin/uieditor/regions'
  } else {
    window.location.href = '/admin/content/pages'
  }
}

// ─── Floating panel drag ──────────────────────────────────────────────────────
// Offset from bottom-right corner (positive = move left/up)
const floatRight = ref(24)
const floatBottom = ref(24)
let dragStartX = 0
let dragStartY = 0
let dragStartRight = 0
let dragStartBottom = 0
let draggingFloat = false

function onFloatMouseDown(e: MouseEvent) {
  // Only drag when clicking the handle bar, not scrollable content
  draggingFloat = true
  dragStartX = e.clientX
  dragStartY = e.clientY
  dragStartRight = floatRight.value
  dragStartBottom = floatBottom.value
  window.addEventListener('mousemove', onFloatMouseMove)
  window.addEventListener('mouseup', onFloatMouseUp)
  e.preventDefault()
}

function onFloatMouseMove(e: MouseEvent) {
  if (!draggingFloat) return
  floatRight.value  = Math.max(0, dragStartRight  - (e.clientX - dragStartX))
  floatBottom.value = Math.max(0, dragStartBottom - (e.clientY - dragStartY))
}

function onFloatMouseUp() {
  draggingFloat = false
  window.removeEventListener('mousemove', onFloatMouseMove)
  window.removeEventListener('mouseup', onFloatMouseUp)
}

onBeforeUnmount(() => {
  window.removeEventListener('mousemove', onFloatMouseMove)
  window.removeEventListener('mouseup', onFloatMouseUp)
})

const hasSelection = computed(() => !!store.selectedId)
// Show floating popup only when properties sidebar is hidden and something is selected
const showFloatingPanel = computed(() => !store.showProperties && hasSelection.value)
</script>

<template>
  <!-- Full-screen editor — no AdminLayout chrome -->
  <div class="h-screen flex flex-col bg-gray-100 dark:bg-gray-950 overflow-hidden">
    <!-- Top Bar -->
    <header class="h-14 flex-shrink-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 flex items-center px-4 gap-3 z-50 shadow-sm">
      <!-- Back -->
      <button type="button" class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors" @click="goBack">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back
      </button>

      <!-- Toggle palette (left panel) -->
      <button
        type="button"
        :title="store.showPalette ? 'Hide block palette' : 'Show block palette'"
        :class="[
          'flex items-center justify-center w-8 h-8 rounded-lg transition-colors flex-shrink-0',
          store.showPalette
            ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
            : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800',
        ]"
        @click="store.togglePalette()"
      >
        <!-- panel-left icon -->
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2"/>
          <path stroke-width="2" d="M9 3v18"/>
        </svg>
      </button>

      <!-- Page Settings gear button (CMS pages + homepage) -->
      <button
        v-if="props.page !== null"
        type="button"
        title="Page settings"
        :class="[
          'flex items-center justify-center w-8 h-8 rounded-lg transition-colors flex-shrink-0',
          store.isPageSettingsOpen
            ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
            : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800',
        ]"
        @click="store.togglePageSettings()"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
      </button>

      <!-- Title -->
      <span class="flex-1 text-sm font-semibold text-gray-800 dark:text-white truncate">{{ editorTitle }}</span>

      <!-- Center: preview switcher -->
      <div class="absolute left-1/2 -translate-x-1/2">
        <PreviewSwitcher />
      </div>

      <!-- Right: publish controls + toggle properties (right panel) -->
      <div class="flex items-center gap-2 ml-auto">
        <PublishControls />

        <!-- Toggle properties (right panel) -->
        <button
          type="button"
          :title="store.showProperties ? 'Hide block settings' : 'Show block settings'"
          :class="[
            'flex items-center justify-center w-8 h-8 rounded-lg transition-colors flex-shrink-0',
            store.showProperties
              ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800',
          ]"
          @click="store.toggleProperties()"
        >
          <!-- panel-right icon -->
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2"/>
            <path stroke-width="2" d="M15 3v18"/>
          </svg>
        </button>
      </div>
    </header>

    <!-- Body -->
    <div class="flex flex-1 min-h-0 overflow-hidden">
      <!-- Left: Block Palette (toggleable) -->
      <transition
        enter-active-class="transition-all duration-200 ease-out"
        enter-from-class="opacity-0 -translate-x-4"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-x-0"
        leave-to-class="opacity-0 -translate-x-4"
      >
        <aside v-show="store.showPalette" class="w-64 flex-shrink-0 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 overflow-y-auto">
          <BlockPalette :block-types="blockTypes" />
        </aside>
      </transition>

      <!-- Center: Canvas or Preview iframe -->
      <main class="flex-1 overflow-auto bg-gray-100 dark:bg-gray-950 flex flex-col items-center">
        <template v-if="store.isPreviewIframe">
          <DeviceFrame :mode="store.previewMode" class="my-6">
            <iframe :src="store.previewUrl ?? ''" :style="iframeStyle" class="block bg-white" title="Page preview" />
          </DeviceFrame>
        </template>
        <template v-else>
          <div class="w-full" :style="store.previewMode !== 'desktop' ? { maxWidth: store.previewMode === 'tablet' ? '768px' : '390px', margin: '24px auto' } : {}">
            <DeviceFrame :mode="store.previewMode">
              <EditorCanvas />
            </DeviceFrame>
          </div>
        </template>
      </main>

      <!-- Right: Properties Panel (toggleable via sidebar) -->
      <transition
        enter-active-class="transition-all duration-200 ease-out"
        enter-from-class="opacity-0 translate-x-4"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-x-0"
        leave-to-class="opacity-0 translate-x-4"
      >
        <PropertiesPanel v-show="store.showProperties" />
      </transition>
    </div>

    <!-- ── Floating properties popup (shown when right sidebar is hidden & something is selected) ── -->
    <Teleport to="body">
      <transition
        enter-active-class="transition-all duration-200 ease-out"
        enter-from-class="opacity-0 scale-95 translate-y-2"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition-all duration-150 ease-in"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-95 translate-y-2"
      >
        <div
          v-if="showFloatingPanel"
          class="fixed z-[9990] w-72 flex flex-col bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
          :style="{ right: floatRight + 'px', bottom: floatBottom + 'px', maxHeight: 'min(72vh, 560px)' }"
        >
          <!-- Drag handle / header -->
          <div
            class="flex items-center justify-between px-4 py-2.5 border-b border-gray-100 dark:border-gray-800 cursor-grab select-none bg-gray-50 dark:bg-gray-800/60 flex-shrink-0"
            @mousedown="onFloatMouseDown"
          >
            <!-- Grip dots -->
            <div class="flex items-center gap-2">
              <svg class="w-3.5 h-3.5 text-gray-400" viewBox="0 0 16 16" fill="currentColor">
                <circle cx="5" cy="4" r="1.5"/><circle cx="11" cy="4" r="1.5"/>
                <circle cx="5" cy="8" r="1.5"/><circle cx="11" cy="8" r="1.5"/>
                <circle cx="5" cy="12" r="1.5"/><circle cx="11" cy="12" r="1.5"/>
              </svg>
              <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Block Settings</span>
            </div>

            <div class="flex items-center gap-1">
              <!-- Dock to sidebar button -->
              <button
                type="button"
                title="Dock to sidebar"
                class="w-6 h-6 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                @click="store.showProperties = true"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2"/>
                  <path stroke-width="2" d="M15 3v18"/>
                </svg>
              </button>
              <!-- Close / deselect button -->
              <button
                type="button"
                title="Close"
                class="w-6 h-6 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                @click="store.clearSelection()"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Panel content (reuse PropertiesPanel in floating mode) -->
          <div class="flex-1 overflow-y-auto min-h-0">
            <PropertiesPanel :floating="true" />
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- ── Page Settings slide-over ── -->
    <PageSettingsPanel />
  </div>
</template>


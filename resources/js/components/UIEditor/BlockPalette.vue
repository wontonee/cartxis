<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { useUiEditorStore } from '@/stores/uiEditorStore'
import type { SavedBlockItem } from '@/stores/uiEditorStore'
import axios from 'axios'

const props = defineProps<{
  blockTypes: Array<{
    type: string
    label: string
    icon: string
    category: string
    defaults: Record<string, unknown>
  }>
}>()

const store = useUiEditorStore()
const search = ref('')
const activeTab = ref<'blocks' | 'saved' | 'snippets' | 'regions'>('blocks')
const savedLoading = ref(false)
const deletingId = ref<number | null>(null)

const categoryLabels: Record<string, string> = {
  layout:   'Layout',
  content:  'Content',
  media:    'Media',
  commerce: 'Commerce',
}

const categoryOrder = ['layout', 'content', 'media', 'commerce']

const categoryIconBg: Record<string, string> = {
  layout:   'bg-blue-50 dark:bg-blue-900/30',
  content:  'bg-violet-50 dark:bg-violet-900/30',
  media:    'bg-emerald-50 dark:bg-emerald-900/30',
  commerce: 'bg-amber-50 dark:bg-amber-900/30',
}

const categoryIconColor: Record<string, string> = {
  layout:   'text-blue-500 dark:text-blue-400',
  content:  'text-violet-500 dark:text-violet-400',
  media:    'text-emerald-500 dark:text-emerald-400',
  commerce: 'text-amber-500 dark:text-amber-400',
}

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  return props.blockTypes.filter(b =>
    !q || b.label.toLowerCase().includes(q) || b.category.toLowerCase().includes(q)
  )
})

const grouped = computed(() => {
  const g: Record<string, typeof props.blockTypes> = {}
  for (const cat of categoryOrder) {
    const items = filtered.value.filter(b => b.category === cat)
    if (items.length > 0) g[cat] = items
  }
  return g
})

const categoryOpen = ref<Record<string, boolean>>(
  Object.fromEntries(categoryOrder.map(c => [c, true]))
)

function toggleCategory(cat: string) {
  categoryOpen.value[cat] = !categoryOpen.value[cat]
}

const icons: Record<string, string> = {
  'arrow-down-up':   'M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4',
  minus:             'M20 12H4',
  type:              'M9 12h6M12 9v6M4 6h16v2H4zM4 16h16v2H4z',
  'align-left':      'M3 6h18M3 12h12M3 18h15',
  image:             'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
  youtube:           'M15 10l-6 3.5V6.5L15 10z M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2s10 4.477 10 10z',
  'mouse-pointer':   'M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z',
  table:             'M3 5h18v14H3z M3 9h18 M3 13h18 M8 5v14 M13 5v14',
  'credit-card':     'M2 7h20v10a2 2 0 01-2 2H4a2 2 0 01-2-2V7zm0 4h20',
  'layout-grid':     'M3 3h8v8H3zm10 0h8v8h-8zM3 13h8v8H3zm10 0h8v8h-8z',
  monitor:           'M9 17H7a2 2 0 00-2 2h10a2 2 0 00-2-2h-2m0 0V11m0 6h2m-2 0h-2M5 3h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z',
  'shopping-bag':    'M6 2 3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0',
  'message-square':  'M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z',
  'clipboard-list':  'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
  'chevrons-down':   'M7 13l5 5 5-5M7 6l5 5 5-5',
  'book-open':       'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
  'mail':            'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
  'star':            'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z',
  'zap':             'M13 2L3 14h9l-1 8 10-12h-9l1-8z',
  'hash':            'M4 9h16M4 15h16M10 3L8 21M16 3l-2 18',
  'tag':             'M20 12V22H4V12M22 7H2v5h20V7zM12 22V7M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z',
  'share-2':         'M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11A2.99 2.99 0 0021 6a3 3 0 10-3 3c.17 0 .34-.02.5-.05L11.45 13.06c-.16-.03-.33-.06-.5-.06-.16 0-.33.02-.49.06L3.41 9.07A3 3 0 103 12c.76 0 1.44-.3 1.96-.77l7.13 4.15A2.97 2.97 0 0012 18a3 3 0 103-3h-.08z',
  'code':            'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
  'file-code':       'M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8zm-4 14l-4-4 4-4m4 0l4 4-4 4M14 2v6h6',
  'layout-template': 'M3 3h8v8H3zm10 0h8v8h-8zM3 13h8v8H3zm10 0h8v8h-8z',
}

function getIconPath(icon: string): string {
  return icons[icon] ?? 'M12 4v16m8-8H4'
}

// ── Native drag helpers ────────────────────────────────────────────────────
function startDrag(bt: { type: string; defaults?: Record<string, unknown> }, e: DragEvent) {
  store.setDraggingBlockType({ type: bt.type, defaults: bt.defaults ?? {} })
  if (e.dataTransfer) {
    e.dataTransfer.effectAllowed = 'copy'
    e.dataTransfer.setData('text/plain', bt.type) // required for Firefox
  }
}

function startDragSaved(item: SavedBlockItem, e: DragEvent) {
  const data = item.layout_data as { type?: string; settings?: Record<string, unknown> }
  store.setDraggingBlockType({ type: data.type ?? 'heading', defaults: data.settings ?? {} })
  if (e.dataTransfer) {
    e.dataTransfer.effectAllowed = 'copy'
    e.dataTransfer.setData('text/plain', data.type ?? 'heading')
  }
}

function endDrag() {
  store.setDraggingBlockType(null)
}

function switchTab(tab: 'blocks' | 'saved' | 'snippets' | 'regions') {
  activeTab.value = tab
  if (tab === 'snippets') loadSnippets()
  if (tab === 'saved') loadSaved()
  if (tab === 'regions') loadRegions()
}

// ── Built-in Snippets (loaded from DB via store) ─────────────────────────
const snippetsLoading = ref(false)

async function loadSnippets() {
  if (store.snippets.length > 0) return
  snippetsLoading.value = true
  await store.fetchSnippets()
  snippetsLoading.value = false
}


const snippetSearch = ref('')
const filteredSnippets = computed(() => {
  const q = snippetSearch.value.toLowerCase()
  if (!q) return store.snippets
  return store.snippets.filter(s =>
    s.name.toLowerCase().includes(q) || (s.description ?? '').toLowerCase().includes(q)
  )
})

function addSnippet(snippet: SavedBlockItem) {
  store.addSectionFromTemplate(snippet.layout_data)
}

// ── Saved tab ──────────────────────────────────────────────────────────────

const savedSearch = ref('')

const filteredSaved = computed(() => {
  const q = savedSearch.value.toLowerCase()
  if (!q) return store.savedBlocks
  return store.savedBlocks.filter(b =>
    b.name.toLowerCase().includes(q) ||
    (b.description ?? '').toLowerCase().includes(q)
  )
})

const savedSections = computed(() => filteredSaved.value.filter(b => b.type === 'section'))
const savedBlockItems = computed(() => filteredSaved.value.filter(b => b.type === 'block'))

async function loadSaved() {
  savedLoading.value = true
  await store.fetchSavedBlocks()
  savedLoading.value = false
}

async function deleteSaved(id: number) {
  deletingId.value = id
  await store.deleteFromLibrary(id)
  deletingId.value = null
}

function addSectionToCanvas(item: SavedBlockItem) {
  store.addSectionFromTemplate(item.layout_data as Record<string, unknown>)
}

// ── Global Regions ────────────────────────────────────────────────────────
interface GlobalRegionItem {
  id: number
  name: string
  slug: string
  region_type: string
  status: 'draft' | 'published'
}

const globalRegions = ref<GlobalRegionItem[]>([])
const regionsLoading = ref(false)
const regionSearch = ref('')

const filteredRegions = computed(() => {
  const q = regionSearch.value.toLowerCase()
  if (!q) return globalRegions.value
  return globalRegions.value.filter(r => r.name.toLowerCase().includes(q) || r.region_type.includes(q))
})

async function loadRegions() {
  regionsLoading.value = true
  try {
    const res = await axios.get('/admin/uieditor/regions/list')
    globalRegions.value = res.data
  } catch {
    // silently ignore
  } finally {
    regionsLoading.value = false
  }
}

function addRegionBlock(region: GlobalRegionItem) {
  const uid = () => Math.random().toString(36).slice(2, 9)
  // Build a full section containing the global_region block
  store.addSectionFromTemplate({
    id: `sec_${uid()}`,
    type: 'section',
    settings: { padding_top: 0, padding_bottom: 0, full_width: true },
    columns: [{
      id: `col_${uid()}`,
      width: 12,
      settings: {},
      blocks: [{
        id: `blk_${uid()}`,
        type: 'global_region',
        settings: {
          region_id: region.id,
          region_name: region.name,
          region_type: region.region_type,
          region_status: region.status,
        },
      }],
    }],
  })
}

const regionTypeColors: Record<string, string> = {
  header:  'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
  footer:  'bg-gray-100 text-gray-600 dark:bg-gray-700',
  section: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
  banner:  'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
  sidebar: 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300',
}

onMounted(() => loadSaved())
</script>

<template>
  <aside class="w-64 flex-shrink-0 flex flex-col h-full bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 overflow-hidden">

    <!-- Tab bar -->
    <div class="flex border-b border-gray-200 dark:border-gray-700">
      <button
        v-for="tab in (['blocks', 'snippets', 'saved', 'regions'] as const)"
        :key="tab"
        type="button"
        :class="[
          'flex-1 py-2 text-[10px] font-semibold uppercase transition-colors',
          activeTab === tab
            ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400 -mb-px'
            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300',
        ]"
        @click="switchTab(tab)"
      >
        {{ tab === 'blocks' ? 'Blocks' : tab === 'snippets' ? 'Snippets' : tab === 'saved' ? 'Saved' : 'Sections' }}
        <span
          v-if="tab === 'snippets'"
          class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-violet-100 dark:bg-violet-900/40 text-violet-600 dark:text-violet-400 text-[10px] font-bold"
        >{{ store.snippets.length || '…' }}</span>
        <span
          v-if="tab === 'saved' && store.savedBlocks.length > 0"
          class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 text-[10px] font-bold"
        >{{ store.savedBlocks.length }}</span>
        <span
          v-if="tab === 'regions' && globalRegions.length > 0"
          class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold"
        >{{ globalRegions.length }}</span>
      </button>
    </div>

    <!-- ── BLOCKS TAB ──────────────────────────────────────────────────── -->
    <template v-if="activeTab === 'blocks'">
      <div class="p-3 border-b border-gray-200 dark:border-gray-700">
        <div class="relative">
          <svg class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="search"
            type="text"
            placeholder="Search blocks…"
            class="w-full pl-8 pr-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
      </div>

      <div class="flex-1 overflow-y-auto py-2">
        <div v-for="(items, cat) in grouped" :key="cat" class="mb-1">
          <button
            type="button"
            class="w-full flex items-center justify-between px-3 py-1.5 text-xs font-semibold uppercase tracking-wider transition-colors"
            :class="[
              activeTab === 'blocks' && categoryOpen[cat as string]
                ? (categoryIconColor[cat as string] ?? 'text-gray-500 dark:text-gray-400')
                : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300',
            ]"
            @click="toggleCategory(cat as string)"
          >
            {{ categoryLabels[cat as string] ?? cat }}
            <svg
              class="w-3.5 h-3.5 transition-transform"
              :class="categoryOpen[cat as string] ? '' : '-rotate-90'"
              fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <div v-if="categoryOpen[cat as string]" class="px-2 pb-2">
            <div class="grid grid-cols-2 gap-1.5">
              <div
                v-for="element in items"
                :key="element.type"
                draggable="true"
                class="group flex flex-col items-center gap-1.5 p-2.5 rounded-xl cursor-grab select-none bg-gray-50 dark:bg-gray-800 hover:bg-white dark:hover:bg-gray-700 ring-1 ring-transparent hover:ring-gray-200 dark:hover:ring-gray-600 hover:shadow-sm transition-all"
                :title="element.label"
                @dragstart="startDrag(element, $event)"
                @dragend="endDrag"
              >
                <div
                  class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                  :class="categoryIconBg[cat as string] ?? 'bg-gray-100 dark:bg-gray-700'"
                >
                  <svg
                    class="w-4 h-4 transition-colors"
                    :class="categoryIconColor[cat as string] ?? 'text-gray-500 dark:text-gray-400'"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="getIconPath(element.icon)" />
                  </svg>
                </div>
                <span class="text-[10px] font-medium text-center text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-100 leading-tight line-clamp-2 transition-colors w-full">{{ element.label }}</span>
              </div>
            </div>
          </div>
        </div>

        <p v-if="Object.keys(grouped).length === 0" class="text-xs text-gray-400 dark:text-gray-500 text-center py-6">
          No blocks match "{{ search }}"
        </p>
      </div>
    </template>

    <!-- ── SNIPPETS TAB ──────────────────────────────────────────────────── -->
    <template v-else-if="activeTab === 'snippets'">
      <!-- Search -->
      <div class="p-3 border-b border-gray-200 dark:border-gray-700">
        <div class="relative">
          <svg class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="snippetSearch"
            type="text"
            placeholder="Search snippets…"
            class="w-full pl-8 pr-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
      </div>

        <div class="flex-1 overflow-y-auto">
        <div v-if="snippetsLoading" class="flex items-center justify-center py-12">
          <svg class="animate-spin w-5 h-5 text-violet-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
          </svg>
        </div>
        <template v-else>
        <p class="px-3 pt-3 pb-1 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Built-in</p>
        <div class="px-2 pb-2 space-y-1">
          <div
            v-for="snippet in filteredSnippets"
            :key="snippet.id"
            class="group flex items-center gap-2 px-2.5 py-2.5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-colors cursor-pointer"
            @click="addSnippet(snippet)"
          >
            <svg class="w-4 h-4 flex-shrink-0 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ snippet.name }}</p>
              <p class="text-[10px] text-gray-400 truncate">{{ snippet.description }}</p>
            </div>
            <svg class="w-3.5 h-3.5 text-violet-400 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </div>
          <p v-if="filteredSnippets.length === 0" class="text-xs text-gray-400 text-center py-4">No snippets match "{{ snippetSearch }}"</p>
        </div>

        <!-- User saved below built-ins -->
        <template v-if="store.savedBlocks.length > 0">
          <p class="px-3 pt-2 pb-1 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-t border-gray-200 dark:border-gray-700 mt-2">My Saved</p>
          <div class="px-2 pb-3 space-y-1">
            <div
              v-for="item in store.savedBlocks"
              :key="item.id"
              class="group flex items-center gap-2 px-2.5 py-2.5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors cursor-pointer"
              @click="item.type === 'section' ? addSectionToCanvas(item) : undefined"
            >
              <svg class="w-4 h-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
              </svg>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ item.name }}</p>
                <p v-if="item.description" class="text-[10px] text-gray-400 truncate">{{ item.description }}</p>
              </div>
            </div>
          </div>
        </template>
        </template>
      </div>
    </template>

    <!-- ── SAVED TAB ───────────────────────────────────────────────────── -->
    <template v-else-if="activeTab === 'saved'">
      <!-- Search + refresh -->
      <div class="p-3 border-b border-gray-200 dark:border-gray-700 flex gap-2">
        <div class="relative flex-1">
          <svg class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="savedSearch"
            type="text"
            placeholder="Search saved…"
            class="w-full pl-8 pr-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
        <button
          type="button"
          class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
          title="Refresh"
          @click="loadSaved"
        >
          <svg class="w-4 h-4" :class="savedLoading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </button>
      </div>

      <div class="flex-1 overflow-y-auto">
        <!-- Loading skeleton -->
        <div v-if="savedLoading" class="p-4 space-y-3">
          <div v-for="i in 3" :key="i" class="h-14 rounded-lg bg-gray-100 dark:bg-gray-800 animate-pulse" />
        </div>

        <template v-else>
          <!-- Saved sections -->
          <div v-if="savedSections.length > 0" class="mb-2">
            <p class="px-3 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
              Sections
            </p>
            <div class="px-2 space-y-1">
              <div
                v-for="item in savedSections"
                :key="item.id"
                class="group flex items-center gap-2 px-2.5 py-2.5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
              >
                <svg class="w-4 h-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16M4 12h16M4 19h16" />
                </svg>
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ item.name }}</p>
                  <p v-if="item.description" class="text-[10px] text-gray-400 truncate">{{ item.description }}</p>
                </div>
                <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button
                    type="button"
                    class="p-1 text-blue-500 hover:text-blue-700 dark:hover:text-blue-300"
                    title="Add to canvas"
                    @click="addSectionToCanvas(item)"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </button>
                  <button
                    type="button"
                    :disabled="deletingId === item.id"
                    class="p-1 text-gray-400 hover:text-red-500 disabled:opacity-40"
                    title="Delete"
                    @click="deleteSaved(item.id)"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Saved blocks (native drag) -->
          <div v-if="savedBlockItems.length > 0">
            <p class="px-3 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
              Blocks
            </p>
            <div class="px-2 pb-1 space-y-1">
              <div
                v-for="item in savedBlockItems"
                :key="item.id"
                draggable="true"
                class="group flex items-center gap-2 px-2.5 py-2.5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/20 cursor-grab transition-colors"
                @dragstart="startDragSaved(item, $event)"
                @dragend="endDrag"
              >
                <svg class="w-4 h-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ item.name }}</p>
                  <p v-if="item.description" class="text-[10px] text-gray-400 truncate">{{ item.description }}</p>
                </div>
                <button
                  type="button"
                  :disabled="deletingId === item.id"
                  class="p-1 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 disabled:opacity-40 transition-opacity"
                  title="Delete"
                  @click.stop="deleteSaved(item.id)"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Empty state -->
          <div
            v-if="store.savedBlocks.length === 0"
            class="flex flex-col items-center justify-center py-10 px-4 text-center"
          >
            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
            </svg>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">No saved components yet</p>
            <p class="text-xs text-gray-400 dark:text-gray-500">Use the ✦ button on any section toolbar to save it as a reusable template.</p>
          </div>
          <div
            v-else-if="filteredSaved.length === 0"
            class="text-xs text-gray-400 dark:text-gray-500 text-center py-6"
          >
            No saved items match "{{ savedSearch }}"
          </div>
        </template>
      </div>
    </template>

    <!-- ── REGIONS TAB ──────────────────────────────────────────────────── -->
    <template v-else-if="activeTab === 'regions'">
      <!-- Search -->
      <div class="p-3 border-b border-gray-200 dark:border-gray-700">
        <div class="relative">
          <svg class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="regionSearch"
            type="text"
            placeholder="Search sections…"
            class="w-full pl-7 pr-3 py-1.5 text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md text-gray-700 dark:text-gray-300 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
        </div>
      </div>

      <div class="flex-1 overflow-y-auto">
        <!-- Loading -->
        <div v-if="regionsLoading" class="flex items-center justify-center py-10">
          <svg class="animate-spin w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
        </div>

        <!-- Empty create prompt -->
        <div
          v-else-if="globalRegions.length === 0"
          class="flex flex-col items-center justify-center py-10 px-4 text-center"
        >
          <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h8v8H3zm10 0h8v8h-8zM3 13h8v8H3zm10 0h8v8h-8z" />
          </svg>
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">No sections yet</p>
          <p class="text-xs text-gray-400 dark:text-gray-500 mb-3">Create Reusable Sections in the Sections manager, then insert them into any page.</p>
          <a
            href="/admin/uieditor/regions"
            target="_blank"
            class="text-xs text-indigo-600 hover:underline dark:text-indigo-400"
          >Open Sections Manager →</a>
        </div>

        <!-- Region list -->
        <div v-else class="p-2 space-y-1">
          <!-- No search match -->
          <div v-if="filteredRegions.length === 0" class="text-xs text-gray-400 text-center py-6">
            No regions match "{{ regionSearch }}"
          </div>

          <button
            v-for="region in filteredRegions"
            :key="region.id"
            type="button"
            class="w-full flex items-center gap-2.5 px-2.5 py-2.5 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 text-left transition-colors group"
            @click="addRegionBlock(region)"
            :title="`Insert '${region.name}' into the page`"
          >
            <!-- Region icon -->
            <div class="w-6 h-6 rounded flex items-center justify-center bg-indigo-100 dark:bg-indigo-900/40 flex-shrink-0">
              <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h8v8H3zm10 0h8v8h-8zM3 13h8v8H3zm10 0h8v8h-8z" />
              </svg>
            </div>

            <div class="flex-1 min-w-0">
              <p class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ region.name }}</p>
              <div class="flex items-center gap-1.5 mt-0.5">
                <span :class="['text-[10px] font-semibold px-1.5 py-0.5 rounded-full capitalize', regionTypeColors[region.region_type] ?? regionTypeColors.section]">
                  {{ region.region_type }}
                </span>
                <span :class="['text-[10px]', region.status === 'published' ? 'text-green-500' : 'text-amber-500']">
                  {{ region.status === 'published' ? '● Live' : '○ Draft' }}
                </span>
              </div>
            </div>

            <!-- Add icon on hover -->
            <svg class="w-3.5 h-3.5 text-indigo-500 opacity-0 group-hover:opacity-100 flex-shrink-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </button>

          <!-- Manage link -->
          <div class="pt-2 pb-1 text-center">
            <a href="/admin/uieditor/regions" target="_blank" class="text-xs text-indigo-500 hover:underline dark:text-indigo-400">
              Manage all sections →
            </a>
          </div>
        </div>
      </div>
    </template>
  </aside>
</template>

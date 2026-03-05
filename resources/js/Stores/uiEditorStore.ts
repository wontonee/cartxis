import { defineStore } from 'pinia'
import { ref, watch, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

// ─── Types ────────────────────────────────────────────────────────────────────

export interface BlockSettings {
  [key: string]: unknown
}

export interface Block {
  id: string
  type: string
  settings: BlockSettings
}

export interface Column {
  id: string
  /** Width in 12-column grid units (1–12) */
  width: number
  settings: { padding?: number; align?: string }
  blocks: Block[]
}

export interface SectionSettings {
  background_color?: string
  background_image?: string | null
  padding_top?: number
  padding_bottom?: number
  full_width?: boolean
}

export interface Section {
  id: string
  type: 'section'
  settings: SectionSettings
  columns: Column[]
}

export interface Layout {
  version: string
  sections: Section[]
}

export type PreviewMode = 'desktop' | 'tablet' | 'mobile'
export type LayoutStatus = 'draft' | 'published' | null

export interface SavedBlockItem {
  id: number
  name: string
  description: string | null
  type: 'section' | 'block'
  layout_data: Record<string, unknown>
  created_at: string
}

export interface PageSettingsData {
  id: number
  title: string
  url_key: string
  status: string
  is_homepage: boolean
  meta_title: string | null
  meta_description: string | null
  meta_keywords: string | null
}

// ─── Defaults ─────────────────────────────────────────────────────────────────

const DEFAULT_SECTION_SETTINGS: SectionSettings = {
  background_color: '#ffffff',
  background_image: null,
  padding_top: 60,
  padding_bottom: 60,
  full_width: false,
}

const DEFAULT_COLUMN_SETTINGS = { padding: 16, align: 'left' }

function generateId(prefix: string): string {
  return `${prefix}_${Math.random().toString(36).slice(2, 9)}`
}

function makeColumn(width: number): Column {
  return { id: generateId('col'), width, settings: { ...DEFAULT_COLUMN_SETTINGS }, blocks: [] }
}

function makeSection(columnCount = 1): Section {
  const totalWidth = 12
  const colWidth = Math.floor(totalWidth / columnCount)
  const remainder = totalWidth - colWidth * columnCount

  const columns: Column[] = Array.from({ length: columnCount }, (_, i) =>
    makeColumn(i === columnCount - 1 ? colWidth + remainder : colWidth)
  )

  return {
    id: generateId('sec'),
    type: 'section',
    settings: { ...DEFAULT_SECTION_SETTINGS },
    columns,
  }
}

// ─── Store ────────────────────────────────────────────────────────────────────

export const useUiEditorStore = defineStore('uiEditor', () => {
  // ── Layout state
  const layout = ref<Layout>({ version: '1.0', sections: [] })
  const selectedId = ref<string | null>(null)
  const selectedType = ref<'section' | 'column' | 'block' | null>(null)
  const isDirty = ref(false)
  const isSaving = ref(false)
  const blockTypes = ref<Record<string, unknown>[]>([])

  // ── Status state
  const layoutStatus = ref<LayoutStatus>(null)
  const publishedAt = ref<string | null>(null)
  const savedAt = ref<string | null>(null)

  // ── Panel visibility state
  const showPalette = ref(true)
  const showProperties = ref(true)

  // ── Page settings panel
  const pageSettings = ref<PageSettingsData | null>(null)
  const isPageSettingsOpen = ref(false)
  const isSettingsSaving = ref(false)
  const settingsError = ref<string | null>(null)

  // ── Preview state
  const previewMode = ref<PreviewMode>('desktop')
  const isPreviewIframe = ref(false)   // true = show iframe instead of live canvas

  // ── URLs (set from props in Editor.vue)
  const saveUrl = ref('')
  const publishUrl = ref('')
  const unpublishUrl = ref('')
  const previewUrl = ref('')

  // ── Saved Blocks (Reusable components library)
  const savedBlocks = ref<SavedBlockItem[]>([])

  // ── Native drag from palette
  const draggingBlockType = ref<{ type: string; defaults: Record<string, unknown> } | null>(null)

  function setDraggingBlockType(bt: { type: string; defaults: Record<string, unknown> } | null) {
    draggingBlockType.value = bt
  }

  // ─── Auto-save (debounced 3 s) ─────────────────────────────────────────────
  let autoSaveTimer: ReturnType<typeof setTimeout> | null = null
  let isLoading = false

  watch(layout, () => {
    if (isLoading) return   // skip spurious trigger from loadLayout
    isDirty.value = true
    if (autoSaveTimer) clearTimeout(autoSaveTimer)
    autoSaveTimer = setTimeout(() => {
      if (isDirty.value && saveUrl.value) {
        triggerSave(true)
      }
    }, 3000)
  }, { deep: true })

  // ─── Actions ───────────────────────────────────────────────────────────────

  function loadLayout(data: Layout | null | Record<string, unknown>, status: LayoutStatus | null, pubAt: string | null) {
    isLoading = true
    layout.value = (data && (data as Layout).sections)
      ? (data as Layout)
      : { version: '1.0', sections: [] }
    layoutStatus.value = status ?? 'draft'
    publishedAt.value = pubAt
    isDirty.value = false
    // Reset after the watcher has fired (watcher runs before nextTick callback)
    nextTick(() => {
      isDirty.value = false
      isLoading = false
    })
  }

  function setUrls(urls: { saveUrl: string; publishUrl: string; unpublishUrl: string; previewUrl: string }) {
    saveUrl.value = urls.saveUrl
    publishUrl.value = urls.publishUrl
    unpublishUrl.value = urls.unpublishUrl
    previewUrl.value = urls.previewUrl
  }

  function setBlockTypes(types: Record<string, unknown>[]) {
    blockTypes.value = types
  }

  function setPreviewMode(mode: PreviewMode) {
    previewMode.value = mode
  }

  function togglePreviewIframe() {
    isPreviewIframe.value = !isPreviewIframe.value
  }

  function togglePalette() {
    showPalette.value = !showPalette.value
  }

  function toggleProperties() {
    showProperties.value = !showProperties.value
  }

  function setPageSettings(data: PageSettingsData) {
    pageSettings.value = { ...data }
  }

  function togglePageSettings() {
    isPageSettingsOpen.value = !isPageSettingsOpen.value
  }

  async function savePageSettings(data: Partial<PageSettingsData>): Promise<void> {
    if (!pageSettings.value) return
    isSettingsSaving.value = true
    settingsError.value = null
    try {
      const res = await axios.put(`/admin/uieditor/pages/${pageSettings.value.id}/settings`, data)
      pageSettings.value = { ...pageSettings.value, ...res.data.page }
    } catch (err: unknown) {
      const e = err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }
      const errors = e.response?.data?.errors
      if (errors) {
        const firstField = Object.keys(errors)[0]
        settingsError.value = errors[firstField]?.[0] ?? 'Validation error.'
      } else {
        settingsError.value = e.response?.data?.message ?? 'Failed to save settings.'
      }
      throw err
    } finally {
      isSettingsSaving.value = false
    }
  }

  // ── Selection
  function selectNode(id: string, type: 'section' | 'column' | 'block') {
    selectedId.value = id
    selectedType.value = type
  }

  function clearSelection() {
    selectedId.value = null
    selectedType.value = null
  }

  // ── Sections
  function addSection(columnCount = 1, afterIndex?: number) {
    const section = makeSection(columnCount)
    if (afterIndex !== undefined) {
      layout.value.sections.splice(afterIndex + 1, 0, section)
    } else {
      layout.value.sections.push(section)
    }
    selectNode(section.id, 'section')
  }

  function addSectionCustom(widths: number[], afterIndex?: number) {
    const section: Section = {
      id: generateId('sec'),
      type: 'section',
      settings: { ...DEFAULT_SECTION_SETTINGS },
      columns: widths.map(w => makeColumn(w)),
    }
    if (afterIndex !== undefined) {
      layout.value.sections.splice(afterIndex + 1, 0, section)
    } else {
      layout.value.sections.push(section)
    }
    selectNode(section.id, 'section')
  }

  function removeSection(sectionId: string) {
    const idx = layout.value.sections.findIndex(s => s.id === sectionId)
    if (idx !== -1) layout.value.sections.splice(idx, 1)
    if (selectedId.value === sectionId) clearSelection()
  }

  function updateSectionSettings(sectionId: string, settings: Partial<SectionSettings>) {
    const section = layout.value.sections.find(s => s.id === sectionId)
    if (section) Object.assign(section.settings, settings)
  }

  function setSectionColumns(sectionId: string, count: number) {
    const section = layout.value.sections.find(s => s.id === sectionId)
    if (!section) return
    const colWidth = Math.floor(12 / count)
    const remainder = 12 - colWidth * count
    section.columns = Array.from({ length: count }, (_, i) =>
      makeColumn(i === count - 1 ? colWidth + remainder : colWidth)
    )
  }

  // ── Blocks
  function addBlock(sectionId: string, columnId: string, blockType: string, defaults: BlockSettings, afterIndex?: number) {
    const section = layout.value.sections.find(s => s.id === sectionId)
    if (!section) return
    const column = section.columns.find(c => c.id === columnId)
    if (!column) return

    const block: Block = {
      id: generateId('blk'),
      type: blockType,
      settings: { ...defaults },
    }

    if (afterIndex !== undefined) {
      column.blocks.splice(afterIndex + 1, 0, block)
    } else {
      column.blocks.push(block)
    }
    selectNode(block.id, 'block')
  }

  function removeBlock(sectionId: string, columnId: string, blockId: string) {
    const section = layout.value.sections.find(s => s.id === sectionId)
    if (!section) return
    const column = section.columns.find(c => c.id === columnId)
    if (!column) return
    const idx = column.blocks.findIndex(b => b.id === blockId)
    if (idx !== -1) column.blocks.splice(idx, 1)
    if (selectedId.value === blockId) clearSelection()
  }

  function duplicateBlock(sectionId: string, columnId: string, blockId: string) {
    const section = layout.value.sections.find(s => s.id === sectionId)
    if (!section) return
    const column = section.columns.find(c => c.id === columnId)
    if (!column) return
    const idx = column.blocks.findIndex(b => b.id === blockId)
    if (idx === -1) return
    const original = column.blocks[idx]
    const copy: Block = {
      id: generateId('blk'),
      type: original.type,
      settings: JSON.parse(JSON.stringify(original.settings)),
    }
    column.blocks.splice(idx + 1, 0, copy)
    selectNode(copy.id, 'block')
  }

  function updateBlockSettings(sectionId: string, columnId: string, blockId: string, settings: Partial<BlockSettings>) {
    const section = layout.value.sections.find(s => s.id === sectionId)
    if (!section) return
    const column = section.columns.find(c => c.id === columnId)
    if (!column) return
    const block = column.blocks.find(b => b.id === blockId)
    if (block) Object.assign(block.settings, settings)
  }

  /**
   * Find a block anywhere in the tree by its ID.
   * Returns { section, column, block } or null.
   */
  function findBlock(blockId: string): { section: Section; column: Column; block: Block } | null {
    for (const section of layout.value.sections) {
      for (const column of section.columns) {
        const block = column.blocks.find(b => b.id === blockId)
        if (block) return { section, column, block }
      }
    }
    return null
  }

  function findSection(sectionId: string): Section | undefined {
    return layout.value.sections.find(s => s.id === sectionId)
  }

  // ─── API calls ─────────────────────────────────────────────────────────────

  async function triggerSave(silent = false) {
    if (!saveUrl.value) return
    if (!silent) isSaving.value = true
    try {
      const res = await axios.post(saveUrl.value, { layout_data: layout.value })
      layoutStatus.value = res.data.status
      savedAt.value = res.data.savedAt
      isDirty.value = false
    } finally {
      if (!silent) isSaving.value = false
    }
  }

  async function triggerPublish() {
    isSaving.value = true
    try {
      const res = await axios.post(publishUrl.value)
      layoutStatus.value = res.data.status
      publishedAt.value = res.data.publishedAt
    } finally {
      isSaving.value = false
    }
  }

  async function triggerUnpublish() {
    isSaving.value = true
    try {
      const res = await axios.post(unpublishUrl.value)
      layoutStatus.value = res.data.status
      publishedAt.value = null
    } finally {
      isSaving.value = false
    }
  }

  // ── Saved Blocks library ───────────────────────────────────────────────────

  async function fetchSavedBlocks() {
    try {
      const res = await axios.get('/admin/uieditor/saved-blocks')
      savedBlocks.value = res.data
    } catch {
      // silently ignore — non-critical
    }
  }

  async function saveToLibrary(
    name: string,
    type: 'section' | 'block',
    layoutData: Record<string, unknown>,
    description?: string,
  ): Promise<SavedBlockItem> {
    const res = await axios.post('/admin/uieditor/saved-blocks', {
      name,
      description: description ?? null,
      type,
      layout_data: layoutData,
    })
    const item = res.data as SavedBlockItem
    savedBlocks.value.push(item)
    return item
  }

  async function deleteFromLibrary(id: number) {
    await axios.delete(`/admin/uieditor/saved-blocks/${id}`)
    savedBlocks.value = savedBlocks.value.filter(b => b.id !== id)
  }

  /** Deep-clone a saved section onto the canvas with all new IDs. */
  function addSectionFromTemplate(sectionData: Record<string, unknown>, afterIndex?: number) {
    const uid = () => Math.random().toString(36).slice(2, 9)
    const newSection: Section = {
      id: `sec_${uid()}`,
      type: 'section',
      settings: { ...((sectionData.settings ?? {}) as SectionSettings) },
      columns: ((sectionData.columns ?? []) as Column[]).map(col => ({
        id: `col_${uid()}`,
        width: col.width,
        settings: { ...col.settings },
        blocks: (col.blocks ?? []).map(blk => ({
          id: `blk_${uid()}`,
          type: blk.type,
          settings: { ...blk.settings },
        })),
      })),
    }
    if (afterIndex !== undefined && afterIndex >= 0) {
      layout.value.sections.splice(afterIndex + 1, 0, newSection)
    } else {
      layout.value.sections.push(newSection)
    }
  }

  return {
    // state
    layout, selectedId, selectedType, isDirty, isSaving,
    layoutStatus, publishedAt, savedAt,
    previewMode, isPreviewIframe,
    showPalette, showProperties,
    pageSettings, isPageSettingsOpen, isSettingsSaving, settingsError,
    saveUrl, publishUrl, unpublishUrl, previewUrl,
    blockTypes,
    savedBlocks,
    draggingBlockType,
    // actions
    loadLayout, setUrls, setPreviewMode, togglePreviewIframe,
    togglePalette, toggleProperties,
    setPageSettings, togglePageSettings, savePageSettings,
    setDraggingBlockType,
    selectNode, clearSelection,
    addSection, addSectionCustom, removeSection, updateSectionSettings, setSectionColumns,
    addBlock, removeBlock, duplicateBlock, updateBlockSettings,
    findBlock, findSection,
    triggerSave, triggerPublish, triggerUnpublish,
    setBlockTypes,
    fetchSavedBlocks, saveToLibrary, deleteFromLibrary, addSectionFromTemplate,
  }
})

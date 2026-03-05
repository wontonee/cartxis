<script setup lang="ts">
import { computed, nextTick, ref } from 'vue'
import draggable from 'vuedraggable'
import { useUiEditorStore } from '@/stores/uiEditorStore'
import type { Section, Column, Block } from '@/stores/uiEditorStore'
import BlockWrapper from './BlockWrapper.vue'

const props = defineProps<{ section: Section; column: Column }>()
const store  = useUiEditorStore()
const hover  = ref(false)
const nativeDragOver = ref(false)

const isSelected = computed(() => store.selectedId === props.column.id)

const blocks = computed({
  get: () => props.column.blocks,
  set: (val) => { props.column.blocks = val },
})

function select(e: MouseEvent) {
  e.stopPropagation()
  store.selectNode(props.column.id, 'column')
}

/**
 * When a block is added from the palette, vuedraggable clones the raw
 * blockType definition object ({type, label, icon, category, defaults})
 * into our blocks array. We must replace it with a proper Block object.
 */
function onBlockChange(evt: any) {
  if (!evt.added) return
  const { newIndex, element } = evt.added
  // If element has no 'id', it's a raw palette item (blockType definition)
  if (!element.id) {
    const newBlock: Block = {
      id: `blk_${Math.random().toString(36).slice(2, 9)}`,
      type: element.type as string,
      settings: { ...(element.defaults as Record<string, unknown> ?? {}) },
    }
    props.column.blocks.splice(newIndex, 1, newBlock)
    nextTick(() => store.selectNode(newBlock.id, 'block'))
  } else {
    // Block moved from another column — just select it
    nextTick(() => store.selectNode(element.id, 'block'))
  }
}

// ── Native drop from palette ──────────────────────────────────────────────
function onNativeDragOver(e: DragEvent) {
  if (!store.draggingBlockType) return   // only react when palette drag is active
  e.preventDefault()
  e.stopPropagation()
  nativeDragOver.value = true
  if (e.dataTransfer) e.dataTransfer.dropEffect = 'copy'
}

function onNativeDragLeave(e: DragEvent) {
  // Only clear when truly leaving the column (not entering a child)
  if (!(e.currentTarget as Element).contains(e.relatedTarget as Node)) {
    nativeDragOver.value = false
  }
}

function onPaletteDrop(e: DragEvent) {
  e.preventDefault()
  e.stopPropagation()
  nativeDragOver.value = false
  const bt = store.draggingBlockType
  if (!bt) return
  store.setDraggingBlockType(null)
  const newBlock: Block = {
    id: `blk_${Math.random().toString(36).slice(2, 9)}`,
    type: bt.type,
    settings: { ...bt.defaults },
  }
  props.column.blocks.push(newBlock)
  nextTick(() => store.selectNode(newBlock.id, 'block'))
}
</script>

<template>
  <div
    class="relative min-h-[80px] transition-all rounded"
    :class="[
      nativeDragOver
        ? 'ring-2 ring-blue-400 ring-inset bg-blue-50/40 dark:bg-blue-900/20'
        : isSelected
          ? 'ring-2 ring-indigo-400 ring-inset'
          : 'hover:ring-1 hover:ring-indigo-200 hover:ring-inset',
    ]"
    @mouseenter="hover = true"
    @mouseleave="hover = false"
    @click="select"
    @dragover="onNativeDragOver"
    @dragleave="onNativeDragLeave"
    @drop="onPaletteDrop"
  >
    <!-- Block drop zone -->
    <draggable
      v-model="blocks"
      :group="{ name: 'blocks' }"
      item-key="id"
      handle=".blk-drag-handle"
      ghost-class="opacity-40 bg-blue-50"
      animation="180"
      class="flex flex-col gap-2 min-h-[80px] p-2"
      @change="onBlockChange"
    >
      <template #item="{ element: block }">
        <BlockWrapper
          :section="section"
          :column="column"
          :block="block"
        />
      </template>

      <!-- Empty state -->
      <template #footer>
        <div
          v-if="blocks.length === 0"
          class="flex flex-col items-center justify-center h-20 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg text-gray-400 dark:text-gray-600 text-sm select-none"
        >
          <svg class="w-6 h-6 mb-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Drop blocks here
        </div>
      </template>
    </draggable>
  </div>
</template>

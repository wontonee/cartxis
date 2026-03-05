<script setup lang="ts">
import { computed, defineAsyncComponent, markRaw, ref } from 'vue'
import { useUiEditorStore } from '@/stores/uiEditorStore'
import type { Section, Column, Block } from '@/stores/uiEditorStore'

const props = defineProps<{ section: Section; column: Column; block: Block }>()
const store  = useUiEditorStore()
const hover  = ref(false)

const isSelected = computed(() => store.selectedId === props.block.id)

function pascalCase(str: string): string {
  return str.replace(/(^|_)([a-z])/g, (_, __, c) => c.toUpperCase())
}

// Cache by block type — must NOT call defineAsyncComponent inside a computed
// or it gets re-created on every evaluation (causes component remount).
const blockComponentCache = new Map<string, ReturnType<typeof defineAsyncComponent>>()
function getBlockComponent(type: string) {
  if (!blockComponentCache.has(type)) {
    blockComponentCache.set(type, markRaw(defineAsyncComponent(() =>
      import(`./blocks/${pascalCase(type)}Block.vue`)
    )))
  }
  return blockComponentCache.get(type)!
}

const BlockComponent = computed(() => getBlockComponent(props.block.type))

function select(e: MouseEvent) {
  e.stopPropagation()
  store.selectNode(props.block.id, 'block')
}

function removeBlock() {
  store.removeBlock(props.section.id, props.column.id, props.block.id)
}

function duplicateBlock() {
  store.duplicateBlock(props.section.id, props.column.id, props.block.id)
}

function openSettings(e: MouseEvent) {
  e.stopPropagation()
  store.selectNode(props.block.id, 'block')
}
</script>

<template>
  <div
    class="relative group/block transition-all rounded"
    :class="[
      isSelected
        ? 'ring-2 ring-blue-500 ring-inset'
        : 'hover:ring-1 hover:ring-blue-300 hover:ring-inset',
    ]"
    @mouseenter="hover = true"
    @mouseleave="hover = false"
    @click="select"
  >
    <!-- Block action toolbar (hover) -->
    <div
      v-show="hover || isSelected"
      class="absolute top-0 right-0 z-50 flex items-center gap-0.5 bg-blue-600 rounded-bl-lg px-1 py-0.5"
    >
      <!-- Drag handle -->
      <div
        class="blk-drag-handle cursor-grab p-1 text-white/70 hover:text-white"
        title="Move"
      >
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
          <path d="M9 5a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm6-14a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
        </svg>
      </div>
      <!-- Settings -->
      <button type="button" class="p-1 text-white/70 hover:text-white" title="Settings" @click.stop="openSettings">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
          <circle cx="12" cy="12" r="3" stroke-width="2" />
        </svg>
      </button>
      <!-- Duplicate -->
      <button type="button" class="p-1 text-white/70 hover:text-white" title="Duplicate" @click.stop="duplicateBlock">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <rect x="9" y="9" width="13" height="13" rx="2" stroke-width="2" />
          <path stroke-linecap="round" stroke-width="2" d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
        </svg>
      </button>
      <!-- Delete -->
      <button type="button" class="p-1 text-white/70 hover:text-red-300" title="Delete" @click.stop="removeBlock">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- The actual block component -->
    <component
      :is="BlockComponent"
      :settings="block.settings"
      :editor-mode="true"
    />
  </div>
</template>

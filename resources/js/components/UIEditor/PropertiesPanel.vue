<script setup lang="ts">
import { computed, defineAsyncComponent, markRaw } from 'vue'
import { useUiEditorStore } from '@/Stores/uiEditorStore'

const props = defineProps<{
  floating?: boolean
}>()

const store = useUiEditorStore()

const selectedBlock = computed(() => {
  if (!store.selectedId || store.selectedType !== 'block') return null
  return store.findBlock(store.selectedId)
})

const selectedSection = computed(() => {
  if (!store.selectedId || store.selectedType !== 'section') return null
  return store.findSection(store.selectedId)
})

function pascalCase(str: string): string {
  return str.replace(/(^|_)([a-z])/g, (_, __, c) => c.toUpperCase())
}

// Cache async panel components by block type so defineAsyncComponent is only
// called ONCE per type. Calling it inside a computed re-creates the loader on
// every evaluation, causing Vue to unmount/remount and lose the open state.
const panelCache = new Map<string, ReturnType<typeof defineAsyncComponent>>()
function getPanelComponent(type: string) {
  if (!panelCache.has(type)) {
    panelCache.set(type, markRaw(defineAsyncComponent(() =>
      import(`./panels/${pascalCase(type)}Panel.vue`)
    )))
  }
  return panelCache.get(type)!
}

const PanelComponent = computed(() => {
  if (!selectedBlock.value) return null
  return getPanelComponent(selectedBlock.value.block.type)
})

function updateSettings(settings: Record<string, unknown>) {
  if (!selectedBlock.value) return
  const { section, column, block } = selectedBlock.value
  store.updateBlockSettings(section.id, column.id, block.id, settings)
}

function updateSectionSettings(settings: Record<string, unknown>) {
  if (!selectedSection.value) return
  store.updateSectionSettings(selectedSection.value.id, settings)
}
</script>

<template>
  <!--
    Sidebar mode  (floating=false) — renders as a fixed <aside> panel
    Floating mode (floating=true)  — renders as bare content inside the floating popup shell
  -->
  <component
    :is="floating ? 'div' : 'aside'"
    :class="floating
      ? 'flex flex-col min-h-0'
      : 'w-72 flex-shrink-0 flex flex-col h-full bg-white dark:bg-gray-900 border-l border-gray-200 dark:border-gray-700 overflow-hidden'"
  >
    <!-- Header — only in sidebar mode -->
    <div v-if="!floating" class="p-3 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
      <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
        <span v-if="selectedBlock">Block Settings</span>
        <span v-else-if="selectedSection">Section Settings</span>
        <span v-else>Properties</span>
      </h3>
    </div>

    <div class="flex-1 overflow-y-auto">
      <!-- Block panel -->
      <template v-if="selectedBlock && PanelComponent">
        <component
          :is="PanelComponent"
          :settings="selectedBlock.block.settings"
          @update:settings="updateSettings"
        />
      </template>

      <!-- Section panel (inline) -->
      <template v-else-if="selectedSection">
        <div class="p-4 space-y-4">
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Background Color</label>
            <div class="flex items-center gap-2">
              <input
                type="color"
                :value="selectedSection.settings.background_color ?? '#ffffff'"
                class="w-8 h-8 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                @input="updateSectionSettings({ background_color: ($event.target as HTMLInputElement).value })"
              />
              <input
                type="text"
                :value="selectedSection.settings.background_color ?? '#ffffff'"
                class="flex-1 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 dark:text-white"
                @change="updateSectionSettings({ background_color: ($event.target as HTMLInputElement).value })"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Padding Top (px)</label>
            <input
              type="number"
              :value="selectedSection.settings.padding_top ?? 60"
              min="0" max="200"
              class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 dark:text-white"
              @change="updateSectionSettings({ padding_top: Number(($event.target as HTMLInputElement).value) })"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Padding Bottom (px)</label>
            <input
              type="number"
              :value="selectedSection.settings.padding_bottom ?? 60"
              min="0" max="200"
              class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 dark:text-white"
              @change="updateSectionSettings({ padding_bottom: Number(($event.target as HTMLInputElement).value) })"
            />
          </div>

          <div class="flex items-center gap-2">
            <input
              id="full_width"
              type="checkbox"
              :checked="selectedSection.settings.full_width ?? false"
              class="rounded text-blue-600"
              @change="updateSectionSettings({ full_width: ($event.target as HTMLInputElement).checked })"
            />
            <label for="full_width" class="text-sm text-gray-700 dark:text-gray-300">Full width (no max-width)</label>
          </div>
        </div>
      </template>

      <!-- Empty state (only shown in sidebar mode — floating popup hides when nothing selected) -->
      <div v-else-if="!floating" class="flex flex-col items-center justify-center h-48 text-gray-400 dark:text-gray-600 text-sm px-6 text-center">
        <svg class="w-10 h-10 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l6 6-6-6zm-5 1a7 7 0 100-14 7 7 0 000 14z" />
        </svg>
        <p>Click any block or section to edit its settings here.</p>
      </div>
    </div>
  </component>
</template>

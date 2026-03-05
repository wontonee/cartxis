<script setup lang="ts">
import { computed, ref } from 'vue'
import draggable from 'vuedraggable'
import { useUiEditorStore } from '@/stores/uiEditorStore'
import SectionWrapper from './SectionWrapper.vue'

const store = useUiEditorStore()

const sections = computed({
  get: () => store.layout.sections,
  set: (val) => { store.layout.sections = val },
})

// ── Section structure picker ──────────────────────────────────────────────────
const showPicker  = ref(false)
const pickerAfter = ref<number | undefined>(undefined)

const columnLayouts = [
  { label: '1 Column',   visual: [1],       widths: [12] },
  { label: '2 Columns',  visual: [1, 1],    widths: [6, 6] },
  { label: '3 Columns',  visual: [1, 1, 1], widths: [4, 4, 4] },
  { label: '4 Columns',  visual: [1,1,1,1], widths: [3, 3, 3, 3] },
  { label: '2/3 + 1/3',  visual: [2, 1],    widths: [8, 4] },
  { label: '1/3 + 2/3',  visual: [1, 2],    widths: [4, 8] },
]

function openPicker(afterIndex?: number) {
  pickerAfter.value = afterIndex
  showPicker.value  = true
}

function closePicker() {
  showPicker.value = false
}

function selectLayout(layout: typeof columnLayouts[0]) {
  store.addSectionCustom(layout.widths, pickerAfter.value)
  closePicker()
}
</script>

<template>
  <div class="min-h-full pb-20">
    <!-- Sections list -->
    <draggable
      v-model="sections"
      item-key="id"
      handle=".sec-drag-handle"
      ghost-class="opacity-40"
      animation="180"
      class="flex flex-col"
    >
      <template #item="{ element: section, index }">
        <div class="group/insert relative">
          <SectionWrapper :section="section" :index="index" />

          <!-- Insert-after divider — appears on section hover -->
          <div class="flex items-center justify-center h-0 overflow-visible opacity-0 group-hover/insert:opacity-100 transition-opacity z-40 relative">
            <button
              type="button"
              class="flex items-center gap-1 px-3 py-0.5 rounded-full bg-blue-500 text-white text-xs font-medium shadow-md hover:bg-blue-600 transition-colors"
              title="Add section here"
              @click.stop="openPicker(index)"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
              </svg>
              Add section
            </button>
          </div>
        </div>
      </template>
    </draggable>

    <!-- Add Section at bottom -->
    <div class="flex justify-center mt-8">
      <button
        type="button"
        class="flex items-center gap-2 px-6 py-2.5 rounded-full border-2 border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:border-blue-400 hover:text-blue-600 dark:hover:text-blue-400 font-medium text-sm transition-all hover:shadow-sm"
        @click="openPicker()"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Section
      </button>
    </div>

    <!-- ── Section Structure Picker Modal ──────────────────────────────────── -->
    <Teleport to="body">
      <Transition name="picker">
        <div
          v-if="showPicker"
          class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
          @click.self="closePicker"
        >
          <!-- Backdrop -->
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closePicker" />

          <!-- Picker card -->
          <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-[480px] max-w-full overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
              <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Choose section structure</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Select a column layout for the new section</p>
              </div>
              <button
                type="button"
                class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                @click="closePicker"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Layout grid -->
            <div class="p-5 grid grid-cols-3 gap-3">
              <button
                v-for="layout in columnLayouts"
                :key="layout.label"
                type="button"
                class="group flex flex-col items-center gap-2.5 p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all focus:outline-none focus:ring-2 focus:ring-blue-500"
                @click="selectLayout(layout)"
              >
                <!-- Column preview bars -->
                <div class="flex gap-1.5 w-full h-10 items-stretch">
                  <div
                    v-for="(w, i) in layout.visual"
                    :key="i"
                    class="rounded-md bg-gray-200 dark:bg-gray-700 group-hover:bg-blue-300 dark:group-hover:bg-blue-700 transition-colors"
                    :style="{ flex: w }"
                  />
                </div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                  {{ layout.label }}
                </span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.picker-enter-active,
.picker-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.picker-enter-from,
.picker-leave-to {
  opacity: 0;
  transform: scale(0.96);
}
</style>

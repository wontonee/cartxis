<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()
function update(key: string, value: unknown) { emit('update:settings', { ...props.settings, [key]: value }) }

const expanded = ref(false)

function onKeyDown(e: KeyboardEvent) {
  // Allow Tab key inside textarea
  if (e.key === 'Tab') {
    e.preventDefault()
    const el = e.target as HTMLTextAreaElement
    const start = el.selectionStart
    const end   = el.selectionEnd
    const val   = el.value
    el.value = val.substring(0, start) + '  ' + val.substring(end)
    el.selectionStart = el.selectionEnd = start + 2
    update('content', el.value)
  }
  // Escape closes fullscreen
  if (e.key === 'Escape') expanded.value = false
}
</script>

<template>
  <div class="p-4 space-y-4">
    <div>
      <div class="flex items-center justify-between mb-1">
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">HTML Content</label>
        <button
          type="button"
          class="flex items-center gap-1 px-2 py-1 text-[11px] font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-md transition-colors"
          @click="expanded = true"
          title="Open fullscreen editor"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
          </svg>
          Expand
        </button>
      </div>
      <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-2">
        Raw HTML is rendered as-is. Use this for embeds, custom markup, iframes, etc.
      </p>
      <textarea
        :value="settings.content as string"
        rows="14"
        spellcheck="false"
        class="uie-code-textarea"
        placeholder="<p>Enter your HTML here...</p>"
        @input="update('content', ($event.target as HTMLTextAreaElement).value)"
        @keydown="onKeyDown"
      />
    </div>

    <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-3 flex gap-2">
      <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
      <p class="text-[11px] text-amber-700 dark:text-amber-400 leading-relaxed">
        Only use trusted HTML. Scripts and styles embedded here will execute in the page context.
      </p>
    </div>
  </div>

  <!-- Fullscreen Editor Modal -->
  <Teleport to="body">
    <div
      v-if="expanded"
      class="uie-html-fullscreen"
      @keydown.esc="expanded = false"
    >
      <!-- Header bar -->
      <div class="uie-html-fs-bar">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
          </svg>
          <span class="text-sm font-semibold text-white">HTML Editor</span>
          <span class="text-xs text-gray-400 ml-2">Press Esc or click × to close · Tab inserts 2 spaces</span>
        </div>
        <button
          type="button"
          class="flex items-center gap-1 px-3 py-1.5 text-xs text-white bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors"
          @click="expanded = false"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Close
        </button>
      </div>

      <!-- Textarea -->
      <textarea
        :value="settings.content as string"
        spellcheck="false"
        class="uie-html-fs-textarea"
        placeholder="<p>Enter your HTML here...</p>"
        autofocus
        @input="update('content', ($event.target as HTMLTextAreaElement).value)"
        @keydown="onKeyDown"
      />
    </div>
  </Teleport>
</template>

<style scoped>
@reference "tailwindcss";
.uie-code-textarea {
  @apply w-full px-3 py-2.5 text-xs font-mono border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-900 dark:bg-gray-950 text-green-400 dark:text-green-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-y;
  tab-size: 2;
  white-space: pre;
  overflow-x: auto;
  min-height: 200px;
}

.uie-html-fullscreen {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  background: #0d1117;
}

.uie-html-fs-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 16px;
  background: #161b22;
  border-bottom: 1px solid #30363d;
  flex-shrink: 0;
}

.uie-html-fs-textarea {
  flex: 1;
  width: 100%;
  padding: 16px 20px;
  font-family: 'Fira Code', 'Cascadia Code', 'JetBrains Mono', Consolas, monospace;
  font-size: 14px;
  line-height: 1.6;
  color: #7ee787;
  background: transparent;
  border: none;
  outline: none;
  resize: none;
  tab-size: 2;
  white-space: pre;
  overflow: auto;
}
</style>

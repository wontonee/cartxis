<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Image from '@tiptap/extension-image'
import { watch } from 'vue'

interface Props {
  modelValue: string
  placeholder?: string
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Write something...',
  disabled: false
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const editor = useEditor({
  extensions: [
    StarterKit.configure({
      Link: {
        openOnClick: false,
      },
    }),
    Image,
  ],
  content: props.modelValue,
  editorProps: {
    attributes: {
      class: 'prose max-w-none focus:outline-none min-h-[200px] px-4 py-3',
    },
  },
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getHTML())
  },
})

// Watch for external changes to modelValue
watch(() => props.modelValue, (value) => {
  const isSame = editor.value?.getHTML() === value
  if (!isSame && editor.value) {
    editor.value.commands.setContent(value, false)
  }
})
</script>

<template>
  <div class="border border-gray-300 rounded-md overflow-hidden">
    <!-- Toolbar -->
    <div v-if="editor" class="border-b border-gray-300 bg-gray-50 px-2 py-2 flex flex-wrap gap-1">
      <!-- Bold -->
      <button
        type="button"
        @click="editor.chain().focus().toggleBold().run()"
        :class="[
          'px-3 py-1.5 rounded text-sm font-medium transition-colors',
          editor.isActive('bold')
            ? 'bg-blue-600 text-white'
            : 'bg-white text-gray-700 hover:bg-gray-100'
        ]"
        title="Bold (Ctrl+B)"
      >
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path d="M11.49 3.17c-.38.02-.74.12-1.07.3-.33.18-.63.45-.89.76-.27.32-.48.7-.65 1.13-.17.43-.25.89-.25 1.38s.08.95.25 1.38c.17.43.38.81.65 1.13.26.31.56.58.89.76.33.18.69.28 1.07.3h.13v3.42c-.38.02-.74.12-1.07.3-.33.18-.63.45-.89.76-.27.32-.48.7-.65 1.13-.17.43-.25.89-.25 1.38s.08.95.25 1.38c.17.43.38.81.65 1.13.26.31.56.58.89.76.33.18.69.28 1.07.3h3.77c1.37 0 2.61-.35 3.73-1.06 1.12-.71 2.01-1.69 2.66-2.94.66-1.25.99-2.64.99-4.18s-.33-2.93-.99-4.18c-.65-1.25-1.54-2.23-2.66-2.94-1.12-.71-2.36-1.06-3.73-1.06H11.62c-.04 0-.09.01-.13.01zm.09 1.5h3.68c1.04 0 1.99.27 2.84.81.85.54 1.52 1.27 2.02 2.19.5.92.75 1.95.75 3.09s-.25 2.17-.75 3.09c-.5.92-1.17 1.65-2.02 2.19-.85.54-1.8.81-2.84.81h-3.68c-.49 0-.92-.08-1.29-.23-.37-.15-.68-.36-.93-.64-.25-.27-.44-.59-.57-.95-.13-.36-.19-.74-.19-1.14s.06-.78.19-1.14c.13-.36.32-.68.57-.95.25-.28.56-.49.93-.64.37-.15.8-.23 1.29-.23v-3.42c-.49 0-.92-.08-1.29-.23-.37-.15-.68-.36-.93-.64-.25-.27-.44-.59-.57-.95-.13-.36-.19-.74-.19-1.14s.06-.78.19-1.14c.13-.36.32-.68.57-.95.25-.28.56-.49.93-.64.37-.15.8-.23 1.29-.23z" />
        </svg>
      </button>

      <!-- Italic -->
      <button
        type="button"
        @click="editor.chain().focus().toggleItalic().run()"
        :class="[
          'px-3 py-1.5 rounded text-sm font-medium transition-colors',
          editor.isActive('italic')
            ? 'bg-blue-600 text-white'
            : 'bg-white text-gray-700 hover:bg-gray-100'
        ]"
        title="Italic (Ctrl+I)"
      >
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path d="M10 3c-.55 0-1 .45-1 1v1h1.5l-2 10H7v1c0 .55.45 1 1 1h4c.55 0 1-.45 1-1v-1h-1.5l2-10H15V4c0-.55-.45-1-1-1h-4z" />
        </svg>
      </button>

      <!-- Strikethrough -->
      <button
        type="button"
        @click="editor.chain().focus().toggleStrike().run()"
        :class="[
          'px-3 py-1.5 rounded text-sm font-medium transition-colors',
          editor.isActive('strike')
            ? 'bg-blue-600 text-white'
            : 'bg-white text-gray-700 hover:bg-gray-100'
        ]"
        title="Strikethrough"
      >
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path d="M10 4c-1.66 0-3 1.34-3 3 0 .55.45 1 1 1s1-.45 1-1c0-.55.45-1 1-1s1 .45 1 1c0 .55-.45 1-1 1H9c-.55 0-1 .45-1 1s.45 1 1 1h1c1.66 0 3-1.34 3-3s-1.34-3-3-3zM4 9c-.55 0-1 .45-1 1s.45 1 1 1h12c.55 0 1-.45 1-1s-.45-1-1-1H4zm6 3c-1.66 0-3 1.34-3 3 0 .55.45 1 1 1s1-.45 1-1c0-.55.45-1 1-1s1 .45 1 1c0 .55-.45 1-1 1H9c-.55 0-1 .45-1 1s.45 1 1 1h1c1.66 0 3-1.34 3-3s-1.34-3-3-3z" />
        </svg>
      </button>

      <div class="w-px h-6 bg-gray-300 mx-1"></div>

      <!-- Heading 1 -->
      <button
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
        :class="[
          'px-3 py-1.5 rounded text-sm font-medium transition-colors',
          editor.isActive('heading', { level: 1 })
            ? 'bg-blue-600 text-white'
            : 'bg-white text-gray-700 hover:bg-gray-100'
        ]"
        title="Heading 1"
      >
        H1
      </button>

      <!-- Heading 2 -->
      <button
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
        :class="[
          'px-3 py-1.5 rounded text-sm font-medium transition-colors',
          editor.isActive('heading', { level: 2 })
            ? 'bg-blue-600 text-white'
            : 'bg-white text-gray-700 hover:bg-gray-100'
        ]"
        title="Heading 2"
      >
        H2
      </button>

      <!-- Heading 3 -->
      <button
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
        :class="[
          'px-3 py-1.5 rounded text-sm font-medium transition-colors',
          editor.isActive('heading', { level: 3 })
            ? 'bg-blue-600 text-white'
            : 'bg-white text-gray-700 hover:bg-gray-100'
        ]"
        title="Heading 3"
      >
        H3
      </button>

      <div class="w-px h-6 bg-gray-300 mx-1"></div>

      <!-- Bullet List -->
      <button
        type="button"
        @click="editor.chain().focus().toggleBulletList().run()"
        :class="[
          'px-3 py-1.5 rounded text-sm font-medium transition-colors',
          editor.isActive('bulletList')
            ? 'bg-blue-600 text-white'
            : 'bg-white text-gray-700 hover:bg-gray-100'
        ]"
        title="Bullet List"
      >
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path d="M4 6c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm6-1c-.55 0-1 .45-1 1s.45 1 1 1h6c.55 0 1-.45 1-1s-.45-1-1-1h-6zM4 11c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm6-1c-.55 0-1 .45-1 1s.45 1 1 1h6c.55 0 1-.45 1-1s-.45-1-1-1h-6zM4 16c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm6-1c-.55 0-1 .45-1 1s.45 1 1 1h6c.55 0 1-.45 1-1s-.45-1-1-1h-6z" />
        </svg>
      </button>

      <!-- Numbered List -->
      <button
        type="button"
        @click="editor.chain().focus().toggleOrderedList().run()"
        :class="[
          'px-3 py-1.5 rounded text-sm font-medium transition-colors',
          editor.isActive('orderedList')
            ? 'bg-blue-600 text-white'
            : 'bg-white text-gray-700 hover:bg-gray-100'
        ]"
        title="Numbered List"
      >
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path d="M5 4h1v4H5V4zm0 6h1v1H5v-1zm0 2h1v4H5v-4zm2-9c-.55 0-1 .45-1 1v1c0 .55.45 1 1 1h9c.55 0 1-.45 1-1V4c0-.55-.45-1-1-1H7zm0 6c-.55 0-1 .45-1 1v1c0 .55.45 1 1 1h9c.55 0 1-.45 1-1v-1c0-.55-.45-1-1-1H7zm0 6c-.55 0-1 .45-1 1v1c0 .55.45 1 1 1h9c.55 0 1-.45 1-1v-1c0-.55-.45-1-1-1H7z" />
        </svg>
      </button>

      <!-- Blockquote -->
      <button
        type="button"
        @click="editor.chain().focus().toggleBlockquote().run()"
        :class="[
          'px-3 py-1.5 rounded text-sm font-medium transition-colors',
          editor.isActive('blockquote')
            ? 'bg-blue-600 text-white'
            : 'bg-white text-gray-700 hover:bg-gray-100'
        ]"
        title="Blockquote"
      >
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path d="M6 10c0-2.21 1.79-4 4-4s4 1.79 4 4-1.79 4-4 4-4-1.79-4-4zm-2 0c0 3.31 2.69 6 6 6s6-2.69 6-6-2.69-6-6-6-6 2.69-6 6z" />
        </svg>
      </button>

      <div class="w-px h-6 bg-gray-300 mx-1"></div>

      <!-- Undo -->
      <button
        type="button"
        @click="editor.chain().focus().undo().run()"
        :disabled="!editor.can().undo()"
        class="px-3 py-1.5 rounded text-sm font-medium bg-white text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        title="Undo (Ctrl+Z)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
        </svg>
      </button>

      <!-- Redo -->
      <button
        type="button"
        @click="editor.chain().focus().redo().run()"
        :disabled="!editor.can().redo()"
        class="px-3 py-1.5 rounded text-sm font-medium bg-white text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        title="Redo (Ctrl+Y)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6" />
        </svg>
      </button>
    </div>

    <!-- Editor Content -->
    <div class="bg-white">
      <EditorContent :editor="editor" />
    </div>
  </div>
</template>

<style>
/* TipTap Editor Styles */
.ProseMirror {
  outline: none;
}

.ProseMirror p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  color: #9ca3af;
  pointer-events: none;
  height: 0;
  float: left;
}

.ProseMirror ul,
.ProseMirror ol {
  padding-left: 1.5rem;
  margin: 0.5rem 0;
}

.ProseMirror ul {
  list-style-type: disc;
}

.ProseMirror ol {
  list-style-type: decimal;
}

.ProseMirror li {
  margin: 0.25rem 0;
}

.ProseMirror blockquote {
  border-left: 3px solid #d1d5db;
  padding-left: 1rem;
  margin: 1rem 0;
  color: #6b7280;
}

.ProseMirror h1 {
  font-size: 2em;
  font-weight: bold;
  margin: 0.5rem 0;
}

.ProseMirror h2 {
  font-size: 1.5em;
  font-weight: bold;
  margin: 0.5rem 0;
}

.ProseMirror h3 {
  font-size: 1.17em;
  font-weight: bold;
  margin: 0.5rem 0;
}

.ProseMirror p {
  margin: 0.5rem 0;
}

.ProseMirror strong {
  font-weight: bold;
}

.ProseMirror em {
  font-style: italic;
}

.ProseMirror s {
  text-decoration: line-through;
}

.ProseMirror code {
  background-color: #f3f4f6;
  padding: 0.125rem 0.25rem;
  border-radius: 0.25rem;
  font-family: monospace;
  font-size: 0.875em;
}

.ProseMirror pre {
  background-color: #1f2937;
  color: #f3f4f6;
  padding: 1rem;
  border-radius: 0.375rem;
  overflow-x: auto;
  margin: 1rem 0;
}

.ProseMirror pre code {
  background: none;
  padding: 0;
  color: inherit;
}
</style>

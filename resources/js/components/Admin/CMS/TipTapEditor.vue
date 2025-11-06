<template>
    <div class="tiptap-editor">
        <div v-if="editor" class="toolbar">
            <div class="toolbar-group">
                <button
                    type="button"
                    @click="editor.chain().focus().toggleBold().run()"
                    :class="{ 'is-active': editor.isActive('bold') }"
                    title="Bold (Ctrl+B)"
                >
                    <Icon name="bold" :size="16" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleItalic().run()"
                    :class="{ 'is-active': editor.isActive('italic') }"
                    title="Italic (Ctrl+I)"
                >
                    <Icon name="italic" :size="16" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleUnderline().run()"
                    :class="{ 'is-active': editor.isActive('underline') }"
                    title="Underline (Ctrl+U)"
                >
                    <Icon name="underline" :size="16" />
                </button>
            </div>

            <div class="toolbar-divider"></div>

            <div class="toolbar-group">
                <button
                    type="button"
                    @click="editor.chain().focus().setParagraph().run()"
                    :class="{ 'is-active': editor.isActive('paragraph') }"
                    title="Paragraph"
                >
                    <Icon name="pilcrow" :size="16" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                    :class="{ 'is-active': editor.isActive('heading', { level: 1 }) }"
                    title="Heading 1"
                >
                    H1
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                    :class="{ 'is-active': editor.isActive('heading', { level: 2 }) }"
                    title="Heading 2"
                >
                    H2
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                    :class="{ 'is-active': editor.isActive('heading', { level: 3 }) }"
                    title="Heading 3"
                >
                    H3
                </button>
            </div>

            <div class="toolbar-divider"></div>

            <div class="toolbar-group">
                <button
                    type="button"
                    @click="editor.chain().focus().toggleBulletList().run()"
                    :class="{ 'is-active': editor.isActive('bulletList') }"
                    title="Bullet List"
                >
                    <Icon name="list" :size="16" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleOrderedList().run()"
                    :class="{ 'is-active': editor.isActive('orderedList') }"
                    title="Numbered List"
                >
                    <Icon name="list-ordered" :size="16" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleBlockquote().run()"
                    :class="{ 'is-active': editor.isActive('blockquote') }"
                    title="Quote"
                >
                    <Icon name="quote" :size="16" />
                </button>
            </div>

            <div class="toolbar-divider"></div>

            <div class="toolbar-group">
                <button
                    type="button"
                    @click="setLink"
                    :class="{ 'is-active': editor.isActive('link') }"
                    title="Insert Link"
                >
                    <Icon name="link" :size="16" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().unsetLink().run()"
                    :disabled="!editor.isActive('link')"
                    title="Remove Link"
                >
                    <Icon name="unlink" :size="16" />
                </button>
            </div>

            <div class="toolbar-divider"></div>

            <div class="toolbar-group">
                <button
                    type="button"
                    @click="editor.chain().focus().setTextAlign('left').run()"
                    :class="{ 'is-active': editor.isActive({ textAlign: 'left' }) }"
                    title="Align Left"
                >
                    <Icon name="align-left" :size="16" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().setTextAlign('center').run()"
                    :class="{ 'is-active': editor.isActive({ textAlign: 'center' }) }"
                    title="Align Center"
                >
                    <Icon name="align-center" :size="16" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().setTextAlign('right').run()"
                    :class="{ 'is-active': editor.isActive({ textAlign: 'right' }) }"
                    title="Align Right"
                >
                    <Icon name="align-right" :size="16" />
                </button>
            </div>

            <div class="toolbar-divider"></div>

            <div class="toolbar-group">
                <button
                    type="button"
                    @click="editor.chain().focus().undo().run()"
                    :disabled="!editor.can().undo()"
                    title="Undo (Ctrl+Z)"
                >
                    <Icon name="undo" :size="16" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().redo().run()"
                    :disabled="!editor.can().redo()"
                    title="Redo (Ctrl+Shift+Z)"
                >
                    <Icon name="redo" :size="16" />
                </button>
            </div>
        </div>

        <EditorContent :editor="editor" class="editor-content" />

        <div v-if="showCharacterCount" class="character-count">
            {{ editor?.storage.characterCount.characters() ?? 0 }} characters
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Link from '@tiptap/extension-link';
import TextAlign from '@tiptap/extension-text-align';
import Image from '@tiptap/extension-image';
import CharacterCount from '@tiptap/extension-character-count';
import Icon from '@/components/Icon.vue';

interface Props {
    modelValue: string;
    placeholder?: string;
    showCharacterCount?: boolean;
    editable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Start typing...',
    showCharacterCount: false,
    editable: true,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const editor = useEditor({
    content: props.modelValue,
    editable: props.editable,
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3, 4, 5, 6],
            },
            // Exclude these since we're adding them separately with custom config
            link: false,
            underline: false,
        }),
        Underline,
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {
                class: 'text-blue-600 hover:text-blue-800 underline',
            },
        }),
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        Image.configure({
            HTMLAttributes: {
                class: 'max-w-full h-auto rounded-lg',
            },
        }),
        CharacterCount,
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm sm:prose lg:prose-lg xl:prose-xl focus:outline-none',
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

const setLink = () => {
    if (!editor.value) return;

    const previousUrl = editor.value.getAttributes('link').href;
    const url = window.prompt('Enter URL:', previousUrl);

    if (url === null) return;

    if (url === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }

    editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

watch(
    () => props.modelValue,
    (value) => {
        if (editor.value && editor.value.getHTML() !== value) {
            editor.value.commands.setContent(value);
        }
    }
);

watch(
    () => props.editable,
    (value) => {
        if (editor.value) {
            editor.value.setEditable(value);
        }
    }
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});
</script>

<style scoped>
.tiptap-editor {
    border: 1px solid rgb(209 213 219);
    border-radius: 0.5rem;
    overflow: hidden;
    background-color: white;
}

.dark .tiptap-editor {
    background-color: rgb(31 41 55);
    border-color: rgb(75 85 99);
}

.toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem;
    border-bottom: 1px solid rgb(209 213 219);
    background-color: rgb(249 250 251);
}

.dark .toolbar {
    background-color: rgb(55 65 81);
    border-bottom-color: rgb(75 85 99);
}

.toolbar-group {
    display: flex;
    gap: 0.25rem;
}

.toolbar-divider {
    width: 1px;
    height: 1.5rem;
    background-color: rgb(209 213 219);
}

.dark .toolbar-divider {
    background-color: rgb(75 85 99);
}

.toolbar button {
    padding: 0.375rem 0.5rem;
    border-radius: 0.25rem;
    color: rgb(55 65 81);
    transition: background-color 0.15s;
    min-width: 32px;
    font-weight: 600;
}

.toolbar button:hover {
    background-color: rgb(229 231 235);
}

.toolbar button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.dark .toolbar button {
    color: rgb(229 231 235);
}

.dark .toolbar button:hover {
    background-color: rgb(75 85 99);
}

.toolbar button.is-active {
    background-color: rgb(209 213 219);
    color: rgb(17 24 39);
}

.dark .toolbar button.is-active {
    background-color: rgb(75 85 99);
    color: white;
}

.editor-content {
    padding: 1rem;
    min-height: 300px;
    max-height: 600px;
    overflow-y: auto;
}

.editor-content :deep(.ProseMirror) {
    outline: none;
}

.editor-content :deep(.ProseMirror p.is-editor-empty:first-child::before) {
    content: attr(data-placeholder);
    color: rgb(156 163 175);
    float: left;
    height: 0;
    pointer-events: none;
}

.character-count {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    color: rgb(107 114 128);
    border-top: 1px solid rgb(209 213 219);
    background-color: rgb(249 250 251);
}

.dark .character-count {
    background-color: rgb(55 65 81);
    border-top-color: rgb(75 85 99);
    color: rgb(156 163 175);
}
</style>

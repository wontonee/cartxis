<script setup lang="ts">
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps<{
  modelValue: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [v: boolean]
  'select': [url: string, alt: string]
}>()

interface MediaFile {
  id: number
  url: string
  thumbnail_url: string | null
  filename: string
  original_filename: string
  alt_text: string | null
  formatted_size: string
  width: number | null
  height: number | null
}

const tab = ref<'browse' | 'upload'>('browse')
const search = ref('')
const loading = ref(false)
const files = ref<MediaFile[]>([])
const currentPage = ref(1)
const lastPage = ref(1)
const selected = ref<MediaFile | null>(null)

// Upload tab
const uploading = ref(false)
const uploadError = ref('')
const dragOver = ref(false)
const fileInputRef = ref<HTMLInputElement | null>(null)

async function fetchMedia(page = 1) {
  loading.value = true
  try {
    const res = await axios.get(route('admin.content.media.picker'), {
      params: { type: 'images', search: search.value, per_page: 20, page },
    })
    const paged = res.data.media
    const items: MediaFile[] = paged.data ?? []
    if (page === 1) {
      files.value = items
    } else {
      files.value.push(...items)
    }
    currentPage.value = paged.current_page ?? 1
    lastPage.value = paged.last_page ?? 1
  } catch (e) {
    console.error('Failed to load media', e)
  } finally {
    loading.value = false
  }
}

watch(
  () => props.modelValue,
  (v) => {
    if (v) {
      tab.value = 'browse'
      search.value = ''
      selected.value = null
      files.value = []
      currentPage.value = 1
      fetchMedia(1)
    }
  },
)

let searchTimer: ReturnType<typeof setTimeout>
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    currentPage.value = 1
    fetchMedia(1)
  }, 300)
}

function loadMore() {
  if (currentPage.value < lastPage.value && !loading.value) {
    fetchMedia(currentPage.value + 1)
  }
}

function selectFile(file: MediaFile) {
  selected.value = file
}

function confirmSelection() {
  if (selected.value) {
    emit('select', selected.value.url, selected.value.alt_text ?? selected.value.original_filename ?? '')
    emit('update:modelValue', false)
  }
}

function close() {
  emit('update:modelValue', false)
}

async function uploadFile(file: File) {
  uploading.value = true
  uploadError.value = ''
  try {
    const formData = new FormData()
    formData.append('file', file)
    const csrfMeta = document.querySelector('meta[name="csrf-token"]')
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') ?? '' : ''
    const res = await axios.post(route('admin.content.media.upload-json'), formData, {
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Content-Type': 'multipart/form-data',
      },
    })
    if (res.data.success) {
      const uploaded: MediaFile = res.data.file
      emit('select', uploaded.url, uploaded.alt_text ?? uploaded.original_filename ?? '')
      emit('update:modelValue', false)
    } else {
      uploadError.value = res.data.message ?? 'Upload failed.'
    }
  } catch (e: any) {
    uploadError.value = e?.response?.data?.message ?? 'Upload failed. Please try again.'
  } finally {
    uploading.value = false
  }
}

function onFileInput(e: Event) {
  const input = e.target as HTMLInputElement
  if (input.files?.[0]) {
    uploadFile(input.files[0])
    input.value = ''
  }
}

function onDrop(e: DragEvent) {
  dragOver.value = false
  const file = e.dataTransfer?.files[0]
  if (file && file.type.startsWith('image/')) {
    uploadFile(file)
  }
}
</script>

<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
      aria-modal="true"
      role="dialog"
    >
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="close" />

      <!-- Dialog -->
      <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Media Library</h2>
          <button
            type="button"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
            @click="close"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-gray-200 dark:border-gray-700 px-6 flex-shrink-0">
          <button
            type="button"
            :class="[
              'py-3 px-1 mr-6 text-sm font-medium border-b-2 transition-colors',
              tab === 'browse'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300',
            ]"
            @click="tab = 'browse'"
          >Browse Library</button>
          <button
            type="button"
            :class="[
              'py-3 px-1 text-sm font-medium border-b-2 transition-colors',
              tab === 'upload'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300',
            ]"
            @click="tab = 'upload'"
          >Upload New</button>
        </div>

        <!-- ── BROWSE TAB ────────────────────────────────────────────────── -->
        <template v-if="tab === 'browse'">
          <!-- Search bar -->
          <div class="px-6 py-3 border-b border-gray-100 dark:border-gray-800 flex-shrink-0">
            <input
              v-model="search"
              type="search"
              placeholder="Search images…"
              class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @input="onSearch"
            />
          </div>

          <!-- Image grid -->
          <div class="flex-1 overflow-y-auto p-6 min-h-0">
            <!-- Spinner -->
            <div v-if="loading && files.length === 0" class="flex items-center justify-center h-40 text-gray-400">
              <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
              </svg>
            </div>

            <!-- Empty state -->
            <div v-else-if="!loading && files.length === 0" class="flex flex-col items-center justify-center h-40 text-gray-400">
              <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <p class="text-sm">No images found</p>
              <button type="button" class="mt-2 text-xs text-blue-500 hover:text-blue-600" @click="tab = 'upload'">Upload an image</button>
            </div>

            <!-- Grid -->
            <div v-else class="grid grid-cols-5 gap-3">
              <button
                v-for="file in files"
                :key="file.id"
                type="button"
                :class="[
                  'relative aspect-square rounded-xl overflow-hidden border-2 transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500',
                  selected?.id === file.id
                    ? 'border-blue-600 ring-2 ring-blue-400 ring-offset-1'
                    : 'border-gray-200 dark:border-gray-700 hover:border-blue-400',
                ]"
                @click="selectFile(file)"
                @dblclick="confirmSelection"
              >
                <img
                  :src="file.thumbnail_url ?? file.url"
                  :alt="file.alt_text ?? file.filename"
                  class="w-full h-full object-cover"
                  loading="lazy"
                />
                <!-- Selected checkmark overlay -->
                <div
                  v-if="selected?.id === file.id"
                  class="absolute inset-0 bg-blue-600/20 flex items-center justify-center"
                >
                  <div class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center shadow-lg">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                </div>
              </button>
            </div>

            <!-- Load more -->
            <div v-if="currentPage < lastPage" class="mt-5 flex justify-center">
              <button
                type="button"
                class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium disabled:opacity-50"
                :disabled="loading"
                @click="loadMore"
              >
                <span v-if="loading" class="flex items-center gap-2">
                  <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                  Loading…
                </span>
                <span v-else>Load more</span>
              </button>
            </div>
          </div>

          <!-- Footer: selected info + confirm -->
          <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50 flex-shrink-0">
            <div v-if="selected" class="flex items-center gap-3 min-w-0 flex-1">
              <img
                :src="selected.thumbnail_url ?? selected.url"
                :alt="selected.alt_text ?? selected.filename"
                class="w-10 h-10 rounded-lg object-cover flex-shrink-0 border border-gray-200 dark:border-gray-700"
              />
              <div class="min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ selected.original_filename ?? selected.filename }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ selected.formatted_size }}<template v-if="selected.width"> · {{ selected.width }}×{{ selected.height }}px</template>
                </p>
              </div>
            </div>
            <p v-else class="text-sm text-gray-400 flex-1">Select an image to insert</p>
            <button
              type="button"
              :disabled="!selected"
              class="ml-4 px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors flex-shrink-0"
              @click="confirmSelection"
            >Insert Image</button>
          </div>
        </template>

        <!-- ── UPLOAD TAB ────────────────────────────────────────────────── -->
        <template v-if="tab === 'upload'">
          <div class="flex-1 flex flex-col items-center justify-center p-8">
            <!-- Drop zone -->
            <div
              :class="[
                'w-full max-w-sm rounded-2xl border-2 border-dashed transition-colors p-10 flex flex-col items-center gap-4 cursor-pointer select-none',
                dragOver
                  ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/20'
                  : 'border-gray-300 dark:border-gray-600 hover:border-blue-400 hover:bg-gray-50 dark:hover:bg-gray-800/40',
              ]"
              @dragover.prevent="dragOver = true"
              @dragleave.prevent="dragOver = false"
              @drop.prevent="onDrop"
              @click="fileInputRef?.click()"
            >
              <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
              </div>
              <div class="text-center">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                  Drop an image here or <span class="text-blue-600 dark:text-blue-400">click to browse</span>
                </p>
                <p class="text-xs text-gray-400 mt-1">PNG, JPG, GIF, WebP — up to 10 MB</p>
              </div>
              <input
                ref="fileInputRef"
                type="file"
                accept="image/*"
                class="hidden"
                @change="onFileInput"
              />
            </div>

            <!-- Uploading indicator -->
            <div v-if="uploading" class="mt-6 flex items-center gap-2 text-blue-600 dark:text-blue-400">
              <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
              </svg>
              <span class="text-sm font-medium">Uploading…</span>
            </div>

            <!-- Error message -->
            <div
              v-if="uploadError"
              class="mt-4 w-full max-w-sm px-4 py-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 rounded-xl"
            >
              <p class="text-sm text-red-600 dark:text-red-400">{{ uploadError }}</p>
            </div>

            <!-- Or switch to browse -->
            <p class="mt-6 text-xs text-gray-400">
              Already uploaded?
              <button type="button" class="text-blue-500 hover:text-blue-600 font-medium" @click="tab = 'browse'">Browse library</button>
            </p>
          </div>
        </template>

      </div>
    </div>
  </Teleport>
</template>

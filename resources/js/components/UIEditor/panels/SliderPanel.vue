<script setup lang="ts">
import { ref, computed } from 'vue'
import MediaPickerModal from '../MediaPickerModal.vue'

interface Slide {
  id:         string
  image:      string
  grad_from:  string
  grad_to:    string
  badge:      string
  headline:   string
  subheading: string
  cta_text:   string
  cta_url:    string
  text_align: string
}

const props  = defineProps<{ settings: Record<string, unknown> }>()
const emit   = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()

const showPicker    = ref(false)
const pickerSlideIdx = ref(-1)

function openPicker(idx: number) {
  pickerSlideIdx.value = idx
  showPicker.value = true
}

function onImageSelected(url: string) {
  if (pickerSlideIdx.value >= 0)
    updateSlide(pickerSlideIdx.value, 'image', url)
}

function update(key: string, value: unknown) {
  emit('update:settings', { ...props.settings, [key]: value })
}

const slides = computed<Slide[]>(() => (props.settings.slides as Slide[]) ?? [])

function updateSlide(idx: number, key: keyof Slide, value: string) {
  const copy = slides.value.map((s, i) => i === idx ? { ...s, [key]: value } : s)
  update('slides', copy)
}

function addSlide() {
  const newSlide: Slide = {
    id: `s${Date.now()}`,
    image: '',
    grad_from: '#1e3a5f',
    grad_to: '#0ea5e9',
    badge: '',
    headline: 'New Slide Headline',
    subheading: 'Add your subheading here.',
    cta_text: 'Shop Now',
    cta_url: '/products',
    text_align: 'center',
  }
  update('slides', [...slides.value, newSlide])
}

function removeSlide(idx: number) {
  if (slides.value.length <= 1) return
  update('slides', slides.value.filter((_, i) => i !== idx))
}
</script>

<template>
  <div class="p-4 space-y-4">
    <!-- Global Settings -->
    <div class="space-y-3 pb-3 border-b border-gray-200 dark:border-gray-700">
      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Height (px)</label>
        <input
          type="number" min="300" max="900" step="10"
          :value="settings.height as number ?? 540"
          class="uie-input"
          @change="update('height', Number(($event.target as HTMLInputElement).value))"
        />
      </div>
      <div class="flex items-center justify-between">
        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Auto-Play</label>
        <button
          class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
          :class="settings.autoplay !== false ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'"
          @click="update('autoplay', settings.autoplay === false)"
        >
          <span
            class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"
            :class="settings.autoplay !== false ? 'translate-x-4' : 'translate-x-1'"
          />
        </button>
      </div>
      <div v-if="settings.autoplay !== false">
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Interval (ms)</label>
        <input
          type="number" min="1000" max="10000" step="500"
          :value="settings.interval as number ?? 5000"
          class="uie-input"
          @change="update('interval', Number(($event.target as HTMLInputElement).value))"
        />
      </div>
    </div>

    <!-- Slides -->
    <div class="space-y-4">
      <div v-for="(slide, idx) in slides" :key="slide.id ?? idx" class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-gray-800">
          <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Slide {{ idx + 1 }}</span>
          <button
            v-if="slides.length > 1"
            @click="removeSlide(idx)"
            class="text-xs text-red-500 hover:text-red-700"
          >Remove</button>
        </div>
        <div class="p-3 space-y-3">
          <!-- Image field with media picker -->
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Slide Image</label>
            <!-- Thumbnail preview -->
            <div v-if="slide.image" class="relative mb-2 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
              <img :src="slide.image" class="w-full max-h-28 object-cover" />
              <button
                type="button"
                class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-red-500/90 text-white flex items-center justify-center hover:bg-red-600 text-sm leading-none shadow"
                title="Remove image"
                @click="updateSlide(idx, 'image', '')"
              >×</button>
            </div>
            <!-- Browse button -->
            <button
              type="button"
              class="w-full py-2 px-3 text-xs font-medium text-blue-600 dark:text-blue-400 border border-blue-300 dark:border-blue-700 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-colors flex items-center justify-center gap-1.5"
              @click="openPicker(idx)"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              {{ slide.image ? 'Change Image' : 'Browse / Upload Image' }}
            </button>
            <!-- Manual URL fallback -->
            <details class="mt-1.5">
              <summary class="text-[10px] text-gray-400 cursor-pointer select-none">Or paste URL</summary>
              <input type="text" :value="slide.image" placeholder="https://… or /storage/…" class="uie-input mt-1 text-xs" @input="updateSlide(idx, 'image', ($event.target as HTMLInputElement).value)" />
            </details>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Gradient From</label>
              <div class="flex gap-1 items-center">
                <input type="color" :value="slide.grad_from || '#1e3a5f'" class="h-7 w-8 cursor-pointer rounded border" @input="updateSlide(idx, 'grad_from', ($event.target as HTMLInputElement).value)" />
                <input type="text" :value="slide.grad_from" class="uie-input flex-1 text-xs" @input="updateSlide(idx, 'grad_from', ($event.target as HTMLInputElement).value)" />
              </div>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Gradient To</label>
              <div class="flex gap-1 items-center">
                <input type="color" :value="slide.grad_to || '#0ea5e9'" class="h-7 w-8 cursor-pointer rounded border" @input="updateSlide(idx, 'grad_to', ($event.target as HTMLInputElement).value)" />
                <input type="text" :value="slide.grad_to" class="uie-input flex-1 text-xs" @input="updateSlide(idx, 'grad_to', ($event.target as HTMLInputElement).value)" />
              </div>
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Badge Text</label>
            <input type="text" :value="slide.badge" placeholder="New Arrivals" class="uie-input" @input="updateSlide(idx, 'badge', ($event.target as HTMLInputElement).value)" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Headline</label>
            <input type="text" :value="slide.headline" class="uie-input" @input="updateSlide(idx, 'headline', ($event.target as HTMLInputElement).value)" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Subheading</label>
            <textarea :value="slide.subheading" rows="2" class="uie-input resize-none" @input="updateSlide(idx, 'subheading', ($event.target as HTMLTextAreaElement).value)" />
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">CTA Label</label>
              <input type="text" :value="slide.cta_text" class="uie-input" @input="updateSlide(idx, 'cta_text', ($event.target as HTMLInputElement).value)" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">CTA URL</label>
              <input type="text" :value="slide.cta_url" class="uie-input" @input="updateSlide(idx, 'cta_url', ($event.target as HTMLInputElement).value)" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Text Align</label>
            <select :value="slide.text_align" class="uie-input" @change="updateSlide(idx, 'text_align', ($event.target as HTMLSelectElement).value)">
              <option value="left">Left</option>
              <option value="center">Center</option>
              <option value="right">Right</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <button @click="addSlide" class="w-full py-2 text-sm font-medium text-blue-600 dark:text-blue-400 border-2 border-dashed border-blue-300 dark:border-blue-700 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
      + Add Slide
    </button>
  </div>

  <MediaPickerModal v-model="showPicker" @select="onImageSelected" />
</template>

<style scoped>
@reference "tailwindcss";
.uie-input {
  @apply w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent;
}
</style>

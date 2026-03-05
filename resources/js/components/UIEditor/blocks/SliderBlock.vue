<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

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

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

const current  = ref(0)
const sliding  = ref(false)
const direction = ref<'next' | 'prev'>('next')

const slides = computed<Slide[]>(() => {
  const raw = props.settings.slides as Slide[] | undefined
  return raw?.length
    ? raw
    : [
        {
          id: 's1',
          image: '',
          grad_from: '#1e3a5f',
          grad_to: '#0ea5e9',
          badge: 'New Arrivals',
          headline: 'Summer Collection 2026',
          subheading: 'Discover amazing products at unbeatable prices.',
          cta_text: 'Shop Now',
          cta_url: '/products',
          text_align: 'center',
        },
      ]
}
)

const height = computed(() => Number(props.settings.height ?? 540))
const autoplay = computed(() => props.settings.autoplay !== false)
const interval = computed(() => Number(props.settings.interval ?? 5000))

let timer: ReturnType<typeof setInterval> | null = null

function go(idx: number) {
  if (sliding.value || idx === current.value) return
  direction.value = idx > current.value ? 'next' : 'prev'
  sliding.value = true
  setTimeout(() => {
    current.value = idx
    sliding.value = false
  }, 0)
}

function next() {
  go((current.value + 1) % slides.value.length)
}
function prev() {
  go((current.value - 1 + slides.value.length) % slides.value.length)
}

function startAutoplay() {
  if (!autoplay.value || slides.value.length <= 1) return
  timer = setInterval(next, interval.value)
}
function stopAutoplay() {
  if (timer) { clearInterval(timer); timer = null }
}

onMounted(startAutoplay)
onUnmounted(stopAutoplay)
watch(autoplay, (v) => v ? startAutoplay() : stopAutoplay())
watch(interval, () => { stopAutoplay(); startAutoplay() })

function alignClass(align: string) {
  if (align === 'left')  return 'items-start text-left'
  if (align === 'right') return 'items-end text-right'
  return 'items-center text-center'
}
</script>

<template>
  <div
    class="relative overflow-hidden select-none group"
    :style="{ height: height + 'px' }"
    @mouseenter="stopAutoplay"
    @mouseleave="startAutoplay"
  >
    <!-- Slides -->
    <transition-group name="slide-fade" tag="div" class="absolute inset-0">
      <div
        v-for="(slide, idx) in slides"
        v-show="idx === current"
        :key="slide.id ?? idx"
        class="absolute inset-0 flex flex-col justify-center px-6 md:px-20"
        :class="alignClass(slide.text_align)"
        :style="{
          background: slide.image
            ? 'transparent'
            : `linear-gradient(135deg, ${slide.grad_from ?? '#1e3a5f'} 0%, ${slide.grad_to ?? '#0ea5e9'} 100%)`
        }"
      >
        <!-- Background image -->
        <img
          v-if="slide.image"
          :src="slide.image"
          alt=""
          class="absolute inset-0 w-full h-full object-cover"
        />
        <!-- Overlay (only when image is set) -->
        <div
          v-if="slide.image"
          class="absolute inset-0"
          :style="{ background: 'linear-gradient(90deg, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0.2) 100%)' }"
        />

        <!-- Content -->
        <div class="relative z-10 max-w-2xl">
          <span
            v-if="slide.badge"
            class="inline-block mb-3 px-3 py-1 text-xs font-bold uppercase tracking-widest rounded-full text-white"
            style="background: rgba(255,255,255,0.2); backdrop-filter: blur(4px)"
          >{{ slide.badge }}</span>

          <h2
            v-if="slide.headline"
            class="text-3xl md:text-5xl font-extrabold text-white leading-tight mb-4"
          >{{ slide.headline }}</h2>

          <p
            v-if="slide.subheading"
            class="text-base md:text-xl text-white/80 mb-8 max-w-lg"
            :class="slide.text_align === 'center' ? 'mx-auto' : ''"
          >{{ slide.subheading }}</p>

          <a
            v-if="slide.cta_text"
            :href="slide.cta_url || '#'"
            class="inline-flex items-center gap-2 px-7 py-3 rounded-lg font-semibold text-sm md:text-base shadow-lg transition-all"
            style="background: #fff; color: #111;"
            onmouseenter="this.style.background='#f3f4f6'"
            onmouseleave="this.style.background='#fff'"
          >
            {{ slide.cta_text }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </a>
        </div>
      </div>
    </transition-group>

    <!-- Arrows (only if > 1 slide) -->
    <template v-if="slides.length > 1">
      <button
        @click.stop="prev"
        class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 flex items-center justify-center rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
        style="background: rgba(255,255,255,0.2); backdrop-filter: blur(4px)"
      >
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <button
        @click.stop="next"
        class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 flex items-center justify-center rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
        style="background: rgba(255,255,255,0.2); backdrop-filter: blur(4px)"
      >
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <!-- Dots -->
      <div class="absolute bottom-5 left-1/2 -translate-x-1/2 z-20 flex gap-2">
        <button
          v-for="(_, idx) in slides"
          :key="idx"
          @click="go(idx)"
          class="rounded-full transition-all"
          :style="{
            width:   idx === current ? '28px' : '8px',
            height:  '8px',
            background: idx === current ? '#fff' : 'rgba(255,255,255,0.5)',
          }"
        />
      </div>
    </template>

    <!-- Editor placeholder -->
    <div
      v-if="!slides[0]?.image && !slides[0]?.grad_from && editorMode"
      class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-gray-700 to-gray-900"
    >
      <span class="text-white/50 text-sm">Slider — click to configure</span>
    </div>
  </div>
</template>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: opacity 0.6s ease;
  position: absolute;
  inset: 0;
}
.slide-fade-enter-from { opacity: 0; }
.slide-fade-leave-to  { opacity: 0; }
</style>

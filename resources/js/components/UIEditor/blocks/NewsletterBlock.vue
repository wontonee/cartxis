<script setup lang="ts">
import { ref, computed } from 'vue'

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

const submitted = ref(false)
const email     = ref('')
const error     = ref('')

const bgColor    = computed(() => (props.settings.bg_color    as string)  || '#0f172a')
const layout     = computed(() => (props.settings.layout      as string)  || 'split')
const title      = computed(() => (props.settings.title       as string)  || 'Join Thousands of Happy Shoppers')
const subtitle   = computed(() => (props.settings.subtitle    as string)  || 'Subscribe and get <strong>10% off</strong> your first order, plus exclusive deals and new arrivals straight to your inbox.')
const ctaText    = computed(() => (props.settings.cta_text    as string)  || 'Subscribe Free')
const successMsg = computed(() => (props.settings.success_msg as string)  || "You're in! Check your inbox for your discount code.")

function submit() {
  error.value = ''
  if (!email.value || !email.value.includes('@')) {
    error.value = 'Please enter a valid email address.'
    return
  }
  submitted.value = true
}
</script>

<template>
  <div class="w-full" :style="{ background: bgColor }">
    <div
      class="max-w-6xl mx-auto px-6 py-16"
      :class="layout === 'split'
        ? 'flex flex-col md:flex-row items-center gap-12'
        : 'flex flex-col items-center text-center'"
    >

      <!-- Text -->
      <div :class="layout === 'split' ? 'flex-1 min-w-0' : 'max-w-xl'">
        <span
          class="inline-block mb-3 px-3 py-1 text-xs font-bold uppercase tracking-widest rounded-full text-white"
          style="background: rgba(255,255,255,0.12)"
        >Newsletter</span>
        <h2 class="text-2xl md:text-4xl font-extrabold text-white leading-tight mb-4">
          {{ title }}
        </h2>
        <!-- eslint-disable-next-line vue/no-v-html -->
        <p class="text-base md:text-lg text-white/70 leading-relaxed" v-html="subtitle" />

        <!-- Trust badges -->
        <div
          class="flex flex-wrap gap-4 mt-6 text-white/50 text-xs"
          :class="layout === 'center' ? 'justify-center' : ''"
        >
          <span class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            No spam, ever
          </span>
          <span class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            Unsubscribe any time
          </span>
          <span class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            Exclusive deals
          </span>
        </div>
      </div>

      <!-- Form -->
      <div :class="layout === 'split' ? 'w-full md:w-[440px] shrink-0' : 'w-full max-w-md mt-8'">

        <!-- Success -->
        <div
          v-if="submitted"
          class="rounded-2xl p-8 text-center"
          style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12)"
        >
          <div class="text-5xl mb-4">🎉</div>
          <p class="text-white font-bold text-xl mb-2">You're subscribed!</p>
          <p class="text-white/60 text-sm">{{ successMsg }}</p>
        </div>

        <!-- Form card -->
        <div
          v-else
          class="rounded-2xl p-6"
          style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1)"
        >
          <h3 class="text-white font-bold text-lg mb-1">Get your 10% off code</h3>
          <p class="text-white/50 text-sm mb-5">Enter your email and we'll send it instantly.</p>

          <form @submit.prevent="submit" class="space-y-3">
            <div>
              <input
                v-model="email"
                type="email"
                placeholder="your@email.com"
                autocomplete="email"
                class="w-full px-4 py-3 rounded-xl text-sm outline-none bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-400 border-0"
              />
              <p v-if="error" class="mt-1.5 text-xs text-red-400">{{ error }}</p>
            </div>
            <button
              type="submit"
              class="w-full py-3 rounded-xl font-bold text-sm text-white transition-all"
              style="background: #2563eb"
              onmouseenter="this.style.background='#1d4ed8'"
              onmouseleave="this.style.background='#2563eb'"
            >
              {{ ctaText }}
            </button>
          </form>

          <p class="text-white/30 text-xs text-center mt-4">
            🔒 Your data is safe with us. Privacy guaranteed.
          </p>
        </div>
      </div>

    </div>
  </div>
</template>

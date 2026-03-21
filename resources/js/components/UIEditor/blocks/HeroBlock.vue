<script setup lang="ts">
defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()
</script>

<template>
  <div
    class="relative flex items-center justify-center overflow-hidden bg-gray-900"
    :style="{ minHeight: `${settings.height ?? 500}px` }"
  >
    <!-- Background image -->
    <img
      v-if="settings.image"
      :src="settings.image as string"
      alt=""
      class="absolute inset-0 w-full h-full object-cover"
    />
    <!-- Overlay -->
    <div
      v-if="settings.overlay_opacity"
      class="absolute inset-0"
      :style="{ backgroundColor: settings.overlay_color as string, opacity: (settings.overlay_opacity as number) / 100 }"
    />
    <!-- Content -->
    <div
      class="relative z-10 px-6 max-w-3xl w-full"
      :class="{
        'text-center': settings.text_align === 'center',
        'text-left':   settings.text_align === 'left',
        'text-right':  settings.text_align === 'right',
      }"
    >
      <h1 v-if="settings.headline" class="text-4xl md:text-5xl font-bold text-white mb-4">
        {{ settings.headline }}
      </h1>
      <p v-if="settings.subheading" class="text-xl text-white/80 mb-8">
        {{ settings.subheading }}
      </p>
      <a
        v-if="settings.cta_text"
        :href="(settings.cta_url as string) ?? '#'"
        class="inline-block px-8 py-3 bg-white text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition-colors"
      >
        {{ settings.cta_text }}
      </a>
    </div>
    <!-- Editor placeholder when no image -->
    <div v-if="!settings.image && editorMode" class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-gray-700 to-gray-900">
      <span class="text-white/50 text-sm">Hero — click to configure</span>
    </div>
  </div>
</template>

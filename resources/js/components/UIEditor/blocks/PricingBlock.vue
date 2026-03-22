<script setup lang="ts">
defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()
</script>

<template>
  <div
    class="relative flex flex-col rounded-2xl border-2 overflow-hidden"
    :class="settings.highlight
      ? 'border-blue-500 shadow-xl ring-2 ring-blue-500/20'
      : 'border-gray-200 dark:border-gray-700 shadow'"
  >
    <!-- Badge -->
    <div
      v-if="settings.badge"
      class="absolute top-4 right-4 px-2.5 py-0.5 text-xs font-bold rounded-full"
      :class="settings.highlight ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
    >
      {{ settings.badge }}
    </div>

    <!-- Header -->
    <div
      class="px-6 pt-8 pb-6"
      :class="settings.highlight ? 'bg-blue-600 text-white' : 'bg-gray-50 dark:bg-gray-800'"
    >
      <h3
        class="text-lg font-bold mb-4"
        :class="settings.highlight ? 'text-white' : 'text-gray-900 dark:text-white'"
      >
        {{ (settings.title as string) || 'Plan Name' }}
      </h3>
      <div class="flex items-baseline gap-1">
        <span
          class="text-3xl font-bold"
          :class="settings.highlight ? 'text-white' : 'text-gray-900 dark:text-white'"
        >{{ settings.currency }}{{ settings.price }}</span>
        <span
          class="text-sm"
          :class="settings.highlight ? 'text-blue-100' : 'text-gray-500 dark:text-gray-400'"
        >{{ settings.period }}</span>
      </div>
      <p
        v-if="settings.description"
        class="mt-2 text-sm"
        :class="settings.highlight ? 'text-blue-100' : 'text-gray-600 dark:text-gray-400'"
      >{{ settings.description }}</p>
    </div>

    <!-- Features -->
    <div class="px-6 py-5 flex-1 bg-white dark:bg-gray-900">
      <ul class="space-y-3">
        <li
          v-for="(feat, i) in (settings.features as string[])"
          :key="i"
          class="flex items-start gap-2.5 text-sm text-gray-700 dark:text-gray-300"
        >
          <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
          {{ feat }}
        </li>
      </ul>
    </div>

    <!-- CTA -->
    <div class="px-6 pb-7 pt-4 bg-white dark:bg-gray-900">
      <a
        :href="(settings.cta_url as string) || '#'"
        class="block w-full text-center py-2.5 px-4 rounded-xl font-semibold text-sm transition-colors"
        :class="settings.highlight
          ? 'bg-blue-600 hover:bg-blue-700 text-white'
          : 'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white'"
      >
        {{ (settings.cta_text as string) || 'Get Started' }}
      </a>
    </div>
  </div>
</template>

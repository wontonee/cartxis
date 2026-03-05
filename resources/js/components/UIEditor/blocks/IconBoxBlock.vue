<script setup lang="ts">
defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

const iconPaths: Record<string, string> = {
  star:          'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z',
  heart:         'M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z',
  zap:           'M13 2L3 14h9l-1 8 10-12h-9l1-8z',
  shield:        'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z',
  award:         'M12 15a6 6 0 100-12 6 6 0 000 12zm0 0v7m-4-3h8',
  rocket:        'M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 00-2.91-.09zM12 15l-3-3a22 22 0 012-3.95A12.88 12.88 0 0122 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 01-4 2z',
  globe:         'M12 2a10 10 0 100 20 10 10 0 000-20zm0 0c2.76 0 5 4.48 5 10s-2.24 10-5 10-5-4.48-5-10 2.24-10 5-10zm-10 10h20M2 9.5h20M2 14.5h20',
  check:         'M22 11.08V12a10 10 0 11-5.93-9.14M22 4L12 14.01l-3-3',
  'thumbs-up':   'M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3H14zm-7 11H5a2 2 0 01-2-2v-7a2 2 0 012-2h2',
  smile:         'M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01M22 12a10 10 0 11-20 0 10 10 0 0120 0z',
  truck:         'M1 3h15v13H1zm15 5h4l3 3v5h-7V8zm0 8a2 2 0 100 4 2 2 0 000-4zm-12 0a2 2 0 100 4 2 2 0 000-4z',
  'refresh-cw':  'M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15',
  headphones:    'M3 18v-6a9 9 0 0118 0v6M3 18a3 3 0 006 0V15a3 3 0 00-6 0v3zm18 0a3 3 0 01-6 0v-3a3 3 0 016 0v3z',
  'shopping-bag':'M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zm3 9a3 3 0 006 0',
  gift:          'M20 12v10H4V12M22 7H2v5h20V7zM12 22V7m0-5a3 3 0 00-3 3h6a3 3 0 00-3-3z',
  tag:           'M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82zM7 7h.01',
}

function getPath(icon: string) {
  return iconPaths[icon as string] ?? iconPaths.star
}
</script>

<template>
  <div
    class="flex flex-col w-full py-4 px-3"
    :class="{
      'items-start text-left':   settings.align === 'left',
      'items-center text-center': !settings.align || settings.align === 'center',
      'items-end text-right':    settings.align === 'right',
    }"
  >
    <!-- Icon circle -->
    <div
      class="flex items-center justify-center rounded-full mb-3 flex-shrink-0"
      :style="{ width: `${(settings.icon_size as number) ?? 48}px`, height: `${(settings.icon_size as number) ?? 48}px`, backgroundColor: `${settings.icon_color ?? '#3b82f6'}20` }"
    >
      <svg
        :width="((settings.icon_size as number) ?? 48) * 0.5"
        :height="((settings.icon_size as number) ?? 48) * 0.5"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
        :style="{ color: settings.icon_color as string ?? '#3b82f6' }"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getPath(settings.icon as string)" />
      </svg>
    </div>

    <!-- Title -->
    <h3
      class="font-semibold text-base mb-1"
      :style="{ color: (settings.title_color as string) || undefined }"
      :class="!(settings.title_color) ? 'text-gray-900 dark:text-white' : ''"
    >
      {{ (settings.title as string) || 'Feature Title' }}
    </h3>

    <!-- Description -->
    <p
      class="text-sm leading-relaxed"
      :style="{ color: (settings.desc_color as string) || undefined }"
      :class="!(settings.desc_color) ? 'text-gray-600 dark:text-gray-400' : ''"
    >
      {{ (settings.description as string) || 'A short description of this feature.' }}
    </p>

    <!-- Link -->
    <a
      v-if="settings.link"
      :href="(settings.link as string)"
      class="mt-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline"
    >
      Learn more →
    </a>
  </div>
</template>

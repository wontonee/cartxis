<script setup lang="ts">
const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()

function update(key: string, value: unknown) {
  emit('update:settings', { ...props.settings, [key]: value })
}
</script>

<template>
  <div class="p-4 space-y-4">

    <!-- Layout toggle -->
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Layout</label>
      <div class="grid grid-cols-2 gap-2">
        <button
          v-for="opt in [{ val: 'split', label: 'Split' }, { val: 'center', label: 'Centered' }]"
          :key="opt.val"
          type="button"
          class="py-1.5 text-xs font-medium rounded-lg border transition-colors"
          :class="(settings.layout ?? 'split') === opt.val
            ? 'bg-blue-600 text-white border-blue-600'
            : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-300 dark:border-gray-600 hover:border-blue-400'"
          @click="update('layout', opt.val)"
        >{{ opt.label }}</button>
      </div>
    </div>

    <!-- Background Color -->
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Background Color</label>
      <div class="flex items-center gap-2">
        <input
          type="color"
          :value="(settings.bg_color as string) || '#0f172a'"
          class="w-8 h-8 rounded border cursor-pointer flex-shrink-0"
          @input="update('bg_color', ($event.target as HTMLInputElement).value)"
        />
        <input
          type="text"
          :value="(settings.bg_color as string) || '#0f172a'"
          class="uie-input flex-1"
          @change="update('bg_color', ($event.target as HTMLInputElement).value)"
        />
      </div>
    </div>

    <!-- Title -->
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Headline</label>
      <input
        type="text"
        :value="settings.title as string"
        class="uie-input"
        placeholder="Join Thousands of Happy Shoppers"
        @input="update('title', ($event.target as HTMLInputElement).value)"
      />
    </div>

    <!-- Subtitle -->
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Subtitle <span class="text-gray-400 font-normal">(HTML allowed)</span></label>
      <textarea
        :value="settings.subtitle as string"
        rows="3"
        class="uie-input resize-none"
        placeholder="Subscribe and get <strong>10% off</strong>…"
        @input="update('subtitle', ($event.target as HTMLTextAreaElement).value)"
      />
    </div>

    <!-- CTA Button Text -->
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Button Label</label>
      <input
        type="text"
        :value="settings.cta_text as string"
        class="uie-input"
        placeholder="Subscribe Free"
        @input="update('cta_text', ($event.target as HTMLInputElement).value)"
      />
    </div>

    <!-- Success Message -->
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Success Message</label>
      <input
        type="text"
        :value="settings.success_msg as string"
        class="uie-input"
        placeholder="You're in! Check your inbox…"
        @input="update('success_msg', ($event.target as HTMLInputElement).value)"
      />
    </div>

  </div>
</template>

<style scoped>
@reference "tailwindcss";
.uie-input {
  @apply w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent;
}
</style>

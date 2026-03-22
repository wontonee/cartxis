<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()

function update(key: string, value: unknown) { emit('update:settings', { ...props.settings, [key]: value }) }

const features = computed(() => (props.settings.features as string[]) ?? [])

function addFeature() { update('features', [...features.value, 'New feature']) }
function removeFeature(i: number) { const c = [...features.value]; c.splice(i, 1); update('features', c) }
function updateFeature(i: number, val: string) { const c = [...features.value]; c[i] = val; update('features', c) }
</script>

<template>
  <div class="p-4 space-y-4">
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Plan Name</label>
      <input type="text" :value="settings.title as string" class="uie-input" @input="update('title', ($event.target as HTMLInputElement).value)" />
    </div>
    <div class="grid grid-cols-3 gap-2">
      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Currency</label>
        <input type="text" :value="settings.currency as string" class="uie-input" placeholder="$" @input="update('currency', ($event.target as HTMLInputElement).value)" />
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Price</label>
        <input type="text" :value="settings.price as string" class="uie-input" placeholder="29" @input="update('price', ($event.target as HTMLInputElement).value)" />
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Period</label>
        <input type="text" :value="settings.period as string" class="uie-input" placeholder="/mo" @input="update('period', ($event.target as HTMLInputElement).value)" />
      </div>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
      <textarea :value="settings.description as string" rows="2" class="uie-input resize-none" @input="update('description', ($event.target as HTMLTextAreaElement).value)" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Badge (optional)</label>
      <input type="text" :value="settings.badge as string" class="uie-input" placeholder="Popular" @input="update('badge', ($event.target as HTMLInputElement).value)" />
    </div>
    <div class="flex items-center gap-2">
      <input id="pricing-highlight" type="checkbox" :checked="!!settings.highlight" class="rounded text-blue-600" @change="update('highlight', ($event.target as HTMLInputElement).checked)" />
      <label for="pricing-highlight" class="text-xs font-medium text-gray-700 dark:text-gray-300">Highlight (featured plan)</label>
    </div>

    <!-- Features -->
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Features</label>
      <div class="space-y-2">
        <div v-for="(feat, i) in features" :key="i" class="flex gap-2">
          <input
            type="text"
            :value="feat"
            class="uie-input flex-1"
            @input="updateFeature(i, ($event.target as HTMLInputElement).value)"
          />
          <button type="button" class="text-red-500 hover:text-red-700 flex-shrink-0" @click="removeFeature(i)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
      </div>
      <button type="button" class="mt-2 w-full py-1.5 text-sm text-blue-600 dark:text-blue-400 border border-dashed border-blue-300 dark:border-blue-600 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" @click="addFeature">
        + Add Feature
      </button>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Button Text</label>
        <input type="text" :value="settings.cta_text as string" class="uie-input" @input="update('cta_text', ($event.target as HTMLInputElement).value)" />
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Button URL</label>
        <input type="text" :value="settings.cta_url as string" class="uie-input" placeholder="#" @input="update('cta_url', ($event.target as HTMLInputElement).value)" />
      </div>
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.uie-input { @apply w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent; }
</style>

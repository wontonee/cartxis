<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()

type SocialLink = { platform: string; url: string; visible: boolean }

function update(key: string, value: unknown) { emit('update:settings', { ...props.settings, [key]: value }) }

const links = computed(() => (props.settings.links as SocialLink[]) ?? [])

const platformOptions = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'tiktok', 'pinterest', 'github', 'whatsapp']

function addLink() {
  update('links', [...links.value, { platform: 'facebook', url: '#', visible: true }])
}

function removeLink(i: number) {
  const c = [...links.value]; c.splice(i, 1); update('links', c)
}

function updateLink(i: number, key: string, value: unknown) {
  const c = links.value.map((l, idx) => idx === i ? { ...l, [key]: value } : l)
  update('links', c)
}
</script>

<template>
  <div class="p-4 space-y-4">
    <!-- Style -->
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Style</label>
        <select :value="settings.style as string" class="uie-input" @change="update('style', ($event.target as HTMLSelectElement).value)">
          <option value="circle">Circle</option>
          <option value="square">Square</option>
          <option value="flat">Flat (no bg)</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Size</label>
        <select :value="settings.size as string" class="uie-input" @change="update('size', ($event.target as HTMLSelectElement).value)">
          <option value="sm">Small</option>
          <option value="md">Medium</option>
          <option value="lg">Large</option>
        </select>
      </div>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Icon Color</label>
      <div class="flex gap-2 items-center">
        <input type="color" :value="settings.color as string" class="h-8 w-10 rounded cursor-pointer border border-gray-300" @input="update('color', ($event.target as HTMLInputElement).value)" />
        <input type="text" :value="settings.color as string" class="uie-input flex-1" @input="update('color', ($event.target as HTMLInputElement).value)" />
      </div>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Alignment</label>
      <select :value="settings.align as string" class="uie-input" @change="update('align', ($event.target as HTMLSelectElement).value)">
        <option value="left">Left</option>
        <option value="center">Center</option>
        <option value="right">Right</option>
      </select>
    </div>

    <!-- Links -->
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Links</label>
      <div class="space-y-3">
        <div
          v-for="(link, i) in links"
          :key="i"
          class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 space-y-2"
        >
          <div class="flex items-center gap-2">
            <input
              :id="`sl-vis-${i}`"
              type="checkbox"
              :checked="link.visible !== false"
              class="rounded text-blue-600"
              @change="updateLink(i, 'visible', ($event.target as HTMLInputElement).checked)"
            />
            <label :for="`sl-vis-${i}`" class="text-xs font-semibold text-gray-600 dark:text-gray-400 flex-1 capitalize">{{ link.platform }}</label>
            <button type="button" class="text-xs text-red-500 hover:text-red-700" @click="removeLink(i)">Remove</button>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Platform</label>
              <select :value="link.platform" class="uie-input" @change="updateLink(i, 'platform', ($event.target as HTMLSelectElement).value)">
                <option v-for="p in platformOptions" :key="p" :value="p">{{ p }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">URL</label>
              <input type="text" :value="link.url" class="uie-input" placeholder="https://..." @input="updateLink(i, 'url', ($event.target as HTMLInputElement).value)" />
            </div>
          </div>
        </div>
      </div>
      <button type="button" class="mt-2 w-full py-1.5 text-sm text-blue-600 dark:text-blue-400 border border-dashed border-blue-300 dark:border-blue-600 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" @click="addLink">
        + Add Link
      </button>
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.uie-input { @apply w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent; }
</style>

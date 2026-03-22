<script setup lang="ts">
const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()
function update(key: string, value: unknown) { emit('update:settings', { ...props.settings, [key]: value }) }
</script>

<template>
  <div class="p-4 space-y-4">
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Section Title</label>
      <input
        type="text"
        :value="settings.title as string"
        class="uie-input"
        placeholder="Latest Posts"
        @input="update('title', ($event.target as HTMLInputElement).value)"
      />
    </div>

    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Number of Posts</label>
      <input
        type="number"
        :value="(settings.count as number) ?? 6"
        class="uie-input"
        min="2"
        max="20"
        @change="update('count', Number(($event.target as HTMLInputElement).value))"
      />
    </div>

    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Category ID (optional)</label>
      <input
        type="number"
        :value="settings.category_id as number ?? ''"
        class="uie-input"
        placeholder="Leave blank for all categories"
        min="1"
        @input="update('category_id', ($event.target as HTMLInputElement).value ? Number(($event.target as HTMLInputElement).value) : null)"
      />
    </div>

    <div class="space-y-2 pt-1 border-t border-gray-100 dark:border-gray-700">
      <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Display Options</p>

      <div class="flex items-center gap-2">
        <input id="pc-date" type="checkbox"
          :checked="(settings.show_date as boolean) ?? true"
          class="rounded text-blue-600"
          @change="update('show_date', ($event.target as HTMLInputElement).checked)" />
        <label for="pc-date" class="text-sm text-gray-700 dark:text-gray-300">Show date</label>
      </div>

      <div class="flex items-center gap-2">
        <input id="pc-category" type="checkbox"
          :checked="(settings.show_category as boolean) ?? true"
          class="rounded text-blue-600"
          @change="update('show_category', ($event.target as HTMLInputElement).checked)" />
        <label for="pc-category" class="text-sm text-gray-700 dark:text-gray-300">Show category</label>
      </div>
    </div>
  </div>
</template>

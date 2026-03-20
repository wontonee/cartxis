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
        min="1"
        max="24"
        @change="update('count', Number(($event.target as HTMLInputElement).value))"
      />
    </div>

    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Columns</label>
      <select
        :value="(settings.columns as number) ?? 3"
        class="uie-input"
        @change="update('columns', Number(($event.target as HTMLSelectElement).value))"
      >
        <option :value="1">1</option>
        <option :value="2">2</option>
        <option :value="3">3</option>
        <option :value="4">4</option>
      </select>
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
        <input id="bpg-image" type="checkbox"
          :checked="(settings.show_image as boolean) ?? true"
          class="rounded text-blue-600"
          @change="update('show_image', ($event.target as HTMLInputElement).checked)" />
        <label for="bpg-image" class="text-sm text-gray-700 dark:text-gray-300">Show image</label>
      </div>

      <div class="flex items-center gap-2">
        <input id="bpg-excerpt" type="checkbox"
          :checked="(settings.show_excerpt as boolean) ?? true"
          class="rounded text-blue-600"
          @change="update('show_excerpt', ($event.target as HTMLInputElement).checked)" />
        <label for="bpg-excerpt" class="text-sm text-gray-700 dark:text-gray-300">Show excerpt</label>
      </div>

      <div class="flex items-center gap-2">
        <input id="bpg-author" type="checkbox"
          :checked="(settings.show_author as boolean) ?? true"
          class="rounded text-blue-600"
          @change="update('show_author', ($event.target as HTMLInputElement).checked)" />
        <label for="bpg-author" class="text-sm text-gray-700 dark:text-gray-300">Show author</label>
      </div>

      <div class="flex items-center gap-2">
        <input id="bpg-date" type="checkbox"
          :checked="(settings.show_date as boolean) ?? true"
          class="rounded text-blue-600"
          @change="update('show_date', ($event.target as HTMLInputElement).checked)" />
        <label for="bpg-date" class="text-sm text-gray-700 dark:text-gray-300">Show date</label>
      </div>

      <div class="flex items-center gap-2">
        <input id="bpg-category" type="checkbox"
          :checked="(settings.show_category as boolean) ?? true"
          class="rounded text-blue-600"
          @change="update('show_category', ($event.target as HTMLInputElement).checked)" />
        <label for="bpg-category" class="text-sm text-gray-700 dark:text-gray-300">Show category</label>
      </div>
    </div>
  </div>
</template>

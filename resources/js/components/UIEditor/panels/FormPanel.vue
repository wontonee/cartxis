<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()

type Field = { id: string; type: string; label: string; required: boolean; placeholder: string }

const fields = computed<Field[]>(() => (props.settings.fields as Field[]) ?? [])

function update(key: string, value: unknown) {
  emit('update:settings', { ...props.settings, [key]: value })
}

function updateField(index: number, key: keyof Field, value: unknown) {
  const updated = fields.value.map((f, i) =>
    i === index ? { ...f, [key]: value } : f
  )
  update('fields', updated)
}

function addField() {
  const newField: Field = {
    id:          `f${Date.now()}`,
    type:        'text',
    label:       'New Field',
    required:    false,
    placeholder: '',
  }
  update('fields', [...fields.value, newField])
}

function removeField(index: number) {
  update('fields', fields.value.filter((_, i) => i !== index))
}

function moveField(index: number, dir: -1 | 1) {
  const arr = [...fields.value]
  const target = index + dir
  if (target < 0 || target >= arr.length) return
  ;[arr[index], arr[target]] = [arr[target], arr[index]]
  update('fields', arr)
}
</script>

<template>
  <div class="p-4 space-y-5 text-sm">
    <!-- Form title -->
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Form Title</label>
      <input
        type="text"
        :value="settings.title as string"
        class="uie-input"
        @input="update('title', ($event.target as HTMLInputElement).value)"
      />
    </div>

    <!-- Fields list -->
    <div>
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Fields</span>
        <button
          type="button"
          class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium"
          @click="addField"
        >
          + Add field
        </button>
      </div>

      <div class="space-y-3">
        <div
          v-for="(field, i) in fields"
          :key="field.id"
          class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 space-y-2 bg-gray-50 dark:bg-gray-800/50"
        >
          <!-- Field header -->
          <div class="flex items-center justify-between gap-1">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Field {{ i + 1 }}</span>
            <div class="flex items-center gap-1">
              <!-- Move up -->
              <button
                v-if="i > 0"
                type="button"
                class="p-0.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                title="Move up"
                @click="moveField(i, -1)"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
              </button>
              <!-- Move down -->
              <button
                v-if="i < fields.length - 1"
                type="button"
                class="p-0.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                title="Move down"
                @click="moveField(i, 1)"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <!-- Remove -->
              <button
                type="button"
                class="p-0.5 text-gray-400 hover:text-red-500"
                title="Remove field"
                @click="removeField(i)"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Field type -->
          <div>
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">Type</label>
            <select
              :value="field.type"
              class="uie-input text-xs"
              @change="updateField(i, 'type', ($event.target as HTMLSelectElement).value)"
            >
              <option value="text">Text</option>
              <option value="email">Email</option>
              <option value="phone">Phone</option>
              <option value="number">Number</option>
              <option value="textarea">Textarea</option>
              <option value="select">Select (dropdown)</option>
            </select>
          </div>

          <!-- Label -->
          <div>
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">Label</label>
            <input
              type="text"
              :value="field.label"
              class="uie-input text-xs"
              @input="updateField(i, 'label', ($event.target as HTMLInputElement).value)"
            />
          </div>

          <!-- Placeholder -->
          <div>
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">Placeholder</label>
            <input
              type="text"
              :value="field.placeholder"
              class="uie-input text-xs"
              @input="updateField(i, 'placeholder', ($event.target as HTMLInputElement).value)"
            />
          </div>

          <!-- Required -->
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input
              type="checkbox"
              :checked="field.required"
              class="w-3.5 h-3.5 rounded text-blue-600"
              @change="updateField(i, 'required', ($event.target as HTMLInputElement).checked)"
            />
            <span class="text-xs text-gray-600 dark:text-gray-400">Required</span>
          </label>
        </div>

        <p v-if="fields.length === 0" class="text-xs text-gray-400 dark:text-gray-500 text-center py-2">
          No fields yet. Click "+ Add field" to start.
        </p>
      </div>
    </div>

    <!-- Submit label -->
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Submit Button Label</label>
      <input
        type="text"
        :value="settings.submit_label as string"
        class="uie-input"
        @input="update('submit_label', ($event.target as HTMLInputElement).value)"
      />
    </div>

    <!-- Success message -->
    <div>
      <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Success Message</label>
      <textarea
        :value="settings.success_message as string"
        rows="2"
        class="uie-input resize-none"
        @input="update('success_message', ($event.target as HTMLTextAreaElement).value)"
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

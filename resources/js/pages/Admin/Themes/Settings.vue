<script setup lang="ts">
import { ref, reactive } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Theme {
  id: number;
  name: string;
  slug: string;
  description: string;
  version: string;
  is_active: boolean;
}

interface Field {
  id: string;
  type: 'color' | 'select' | 'number' | 'boolean' | 'text';
  label: string;
  description?: string;
  default: any;
  options?: Record<string, string>;
  min?: number;
  max?: number;
  step?: number;
}

interface Section {
  id: string;
  title: string;
  description: string;
  fields: Field[];
}

interface Schema {
  sections: Section[];
}

interface Props {
  theme: Theme;
  schema: Schema;
  settings: Record<string, any>;
}

const props = defineProps<Props>();
const page = usePage();
const processing = ref(false);

// Initialize form with current settings or defaults
const form = reactive<Record<string, any>>({});

// Populate form with existing settings or defaults
props.schema.sections.forEach((section) => {
  section.fields.forEach((field) => {
    const settingKey = `${section.id}.${field.id}`;
    form[settingKey] = props.settings[settingKey] ?? field.default;
  });
});

const saveSettings = () => {
  processing.value = true;
  
  router.put(
    `/admin/appearance/themes/${props.theme.slug}/settings`,
    { settings: form },
    {
      preserveScroll: true,
      onFinish: () => {
        processing.value = false;
      },
    }
  );
};

const goBack = () => {
  router.get('/admin/appearance/themes');
};
</script>

<template>
  <AdminLayout>
    <Head :title="`${theme.name} Settings`" />

    <div class="py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <button
            @click="goBack"
            class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4"
          >
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Themes
          </button>
          
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">{{ theme.name }} Settings</h1>
              <p class="mt-2 text-sm text-gray-600">{{ theme.description }}</p>
              <div class="mt-1 flex items-center text-sm text-gray-500">
                <span>Version {{ theme.version }}</span>
                <span v-if="theme.is_active" class="ml-3 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                  Active
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Success/Error Messages -->
        <div v-if="page.props.flash.success" class="mb-6 rounded-md bg-green-50 p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <p class="ml-3 text-sm font-medium text-green-800">
              {{ page.props.flash.success }}
            </p>
          </div>
        </div>

        <!-- Settings Form -->
        <form @submit.prevent="saveSettings" class="space-y-8">
          <!-- Sections -->
          <div
            v-for="section in schema.sections"
            :key="section.id"
            class="bg-white shadow rounded-lg overflow-hidden"
          >
            <div class="px-6 py-5 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">{{ section.title }}</h3>
              <p class="mt-1 text-sm text-gray-500">{{ section.description }}</p>
            </div>

            <div class="px-6 py-5 space-y-6">
              <div
                v-for="field in section.fields"
                :key="field.id"
                class="grid grid-cols-3 gap-6"
              >
                <div class="col-span-1">
                  <label
                    :for="`${section.id}-${field.id}`"
                    class="block text-sm font-medium text-gray-700"
                  >
                    {{ field.label }}
                  </label>
                  <p v-if="field.description" class="mt-1 text-sm text-gray-500">
                    {{ field.description }}
                  </p>
                </div>

                <div class="col-span-2">
                  <!-- Color Field -->
                  <div v-if="field.type === 'color'" class="flex items-center space-x-3">
                    <input
                      :id="`${section.id}-${field.id}`"
                      v-model="form[`${section.id}.${field.id}`]"
                      type="color"
                      class="h-10 w-20 border border-gray-300 rounded-md cursor-pointer"
                    />
                    <input
                      v-model="form[`${section.id}.${field.id}`]"
                      type="text"
                      class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      placeholder="#000000"
                    />
                  </div>

                  <!-- Select Field -->
                  <select
                    v-else-if="field.type === 'select'"
                    :id="`${section.id}-${field.id}`"
                    v-model="form[`${section.id}.${field.id}`]"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                    <option
                      v-for="(label, value) in field.options"
                      :key="value"
                      :value="value"
                    >
                      {{ label }}
                    </option>
                  </select>

                  <!-- Number Field -->
                  <input
                    v-else-if="field.type === 'number'"
                    :id="`${section.id}-${field.id}`"
                    v-model.number="form[`${section.id}.${field.id}`]"
                    type="number"
                    :min="field.min"
                    :max="field.max"
                    :step="field.step"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  />

                  <!-- Boolean Field -->
                  <div v-else-if="field.type === 'boolean'" class="flex items-center">
                    <button
                      type="button"
                      @click="form[`${section.id}.${field.id}`] = !form[`${section.id}.${field.id}`]"
                      :class="[
                        form[`${section.id}.${field.id}`] ? 'bg-blue-600' : 'bg-gray-200',
                        'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2'
                      ]"
                    >
                      <span
                        :class="[
                          form[`${section.id}.${field.id}`] ? 'translate-x-5' : 'translate-x-0',
                          'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                        ]"
                      />
                    </button>
                  </div>

                  <!-- Text Field -->
                  <input
                    v-else
                    :id="`${section.id}-${field.id}`"
                    v-model="form[`${section.id}.${field.id}`]"
                    type="text"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="goBack"
              class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="processing"
              class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

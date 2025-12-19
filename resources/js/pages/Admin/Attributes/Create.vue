<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref, computed, watch } from 'vue';
import * as attributeRoutes from '@/routes/admin/catalog/attributes';

interface AttributeOption {
  label: string;
  value: string;
  swatch_value: string | null;
  sort_order: number;
}

const form = useForm({
  name: '',
  code: '',
  type: 'text' as 'text' | 'textarea' | 'select' | 'multiselect' | 'boolean' | 'date' | 'price',
  is_required: false,
  is_filterable: true,
  is_configurable: false,
  sort_order: 0,
  options: [] as AttributeOption[],
});

// Auto-generate code from name
const generateCode = () => {
  form.code = form.name
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_+|_+$/g, '');
};

// Check if attribute type supports options
const supportsOptions = computed(() => {
  return ['select', 'multiselect'].includes(form.type);
});

// Watch type changes to reset options if needed
watch(() => form.type, (newType) => {
  if (!['select', 'multiselect'].includes(newType)) {
    form.options = [];
  } else if (form.options.length === 0) {
    // Add first empty option
    addOption();
  }
});

// Option management
const addOption = () => {
  form.options.push({
    label: '',
    value: '',
    swatch_value: null,
    sort_order: form.options.length + 1,
  });
};

const removeOption = (index: number) => {
  form.options.splice(index, 1);
  // Re-sort
  form.options.forEach((opt, idx) => {
    opt.sort_order = idx + 1;
  });
};

const moveOptionUp = (index: number) => {
  if (index > 0) {
    const temp = form.options[index];
    form.options[index] = form.options[index - 1];
    form.options[index - 1] = temp;
    // Update sort orders
    form.options.forEach((opt, idx) => {
      opt.sort_order = idx + 1;
    });
  }
};

const moveOptionDown = (index: number) => {
  if (index < form.options.length - 1) {
    const temp = form.options[index];
    form.options[index] = form.options[index + 1];
    form.options[index + 1] = temp;
    // Update sort orders
    form.options.forEach((opt, idx) => {
      opt.sort_order = idx + 1;
    });
  }
};

// Auto-generate option value from label
const generateOptionValue = (index: number) => {
  const option = form.options[index];
  option.value = option.label
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_+|_+$/g, '');
};

const submit = () => {
  form.post(attributeRoutes.store().url, {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
    },
    onError: (errors) => {
      console.error('Validation errors:', errors);
      // Scroll to first error
      const firstErrorField = Object.keys(errors)[0];
      const element = document.querySelector(`[name="${firstErrorField}"]`);
      if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    },
  });
};
</script>

<template>
  <AdminLayout title="Create Attribute">
    <Head title="Create Attribute" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-semibold text-gray-900">Create New Attribute</h1>
              <p class="mt-1 text-sm text-gray-600">Add a new product attribute</p>
            </div>
            <Link
              :href="attributeRoutes.index().url"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Back to Attributes
            </Link>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Main Information Card -->
          <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Basic Information</h3>
              
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Name -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">
                    Attribute Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="name"
                    v-model="form.name"
                    @input="generateCode"
                    type="text"
                    name="name"
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="e.g., Color, Size, Material"
                  />
                  <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <!-- Code -->
                <div>
                  <label for="code" class="block text-sm font-medium text-gray-700">
                    Attribute Code <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="code"
                    v-model="form.code"
                    type="text"
                    name="code"
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="e.g., color, size, material"
                  />
                  <p class="mt-1 text-xs text-gray-500">Auto-generated from name. Use lowercase with underscores.</p>
                  <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
                </div>

                <!-- Type -->
                <div>
                  <label for="type" class="block text-sm font-medium text-gray-700">
                    Input Type <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="type"
                    v-model="form.type"
                    name="type"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                    <option value="text">Text</option>
                    <option value="textarea">Textarea</option>
                    <option value="select">Select (Dropdown)</option>
                    <option value="multiselect">Multi-select</option>
                    <option value="boolean">Boolean (Yes/No)</option>
                    <option value="date">Date</option>
                    <option value="price">Price</option>
                  </select>
                  <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
                </div>

                <!-- Sort Order -->
                <div>
                  <label for="sort_order" class="block text-sm font-medium text-gray-700">
                    Sort Order
                  </label>
                  <input
                    id="sort_order"
                    v-model.number="form.sort_order"
                    type="number"
                    name="sort_order"
                    min="0"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="0"
                  />
                  <p class="mt-1 text-xs text-gray-500">Controls display order. Lower numbers appear first.</p>
                  <p v-if="form.errors.sort_order" class="mt-1 text-sm text-red-600">{{ form.errors.sort_order }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Settings Card -->
          <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Attribute Settings</h3>
              
              <div class="space-y-4">
                <!-- Is Required -->
                <div class="flex items-center">
                  <input
                    id="is_required"
                    v-model="form.is_required"
                    type="checkbox"
                    name="is_required"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="is_required" class="ml-3 block text-sm font-medium text-gray-700">
                    Required
                  </label>
                </div>
                <p class="ml-7 text-xs text-gray-500">Customers must provide a value for this attribute</p>

                <!-- Is Filterable -->
                <div class="flex items-center">
                  <input
                    id="is_filterable"
                    v-model="form.is_filterable"
                    type="checkbox"
                    name="is_filterable"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="is_filterable" class="ml-3 block text-sm font-medium text-gray-700">
                    Use in Filters
                  </label>
                </div>
                <p class="ml-7 text-xs text-gray-500">Show this attribute in product listing filters</p>

                <!-- Is Configurable -->
                <div class="flex items-center">
                  <input
                    id="is_configurable"
                    v-model="form.is_configurable"
                    type="checkbox"
                    name="is_configurable"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="is_configurable" class="ml-3 block text-sm font-medium text-gray-700">
                    Use for Product Variants
                  </label>
                </div>
                <p class="ml-7 text-xs text-gray-500">Use this attribute to create product variants (e.g., Size, Color)</p>
              </div>
            </div>
          </div>

          <!-- Options Card (Only for Select/Multiselect) -->
          <div v-if="supportsOptions" class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Attribute Options</h3>
                <button
                  type="button"
                  @click="addOption"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Add Option
                </button>
              </div>

              <div v-if="form.options.length === 0" class="text-center py-6 text-gray-500">
                No options added yet. Click "Add Option" to create options.
              </div>

              <div v-else class="space-y-3">
                <div
                  v-for="(option, index) in form.options"
                  :key="index"
                  class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors"
                >
                  <!-- Sort Order Buttons -->
                  <div class="flex flex-col gap-1">
                    <button
                      type="button"
                      @click="moveOptionUp(index)"
                      :disabled="index === 0"
                      class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed"
                      title="Move up"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                      </svg>
                    </button>
                    <button
                      type="button"
                      @click="moveOptionDown(index)"
                      :disabled="index === form.options.length - 1"
                      class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed"
                      title="Move down"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </button>
                  </div>

                  <!-- Option Fields -->
                  <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <!-- Label -->
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Label</label>
                      <input
                        v-model="option.label"
                        @input="generateOptionValue(index)"
                        type="text"
                        class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., Red"
                      />
                    </div>

                    <!-- Value -->
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Value</label>
                      <input
                        v-model="option.value"
                        type="text"
                        class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., red"
                      />
                    </div>

                    <!-- Swatch Value (for colors) -->
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Swatch (optional)</label>
                      <input
                        v-model="option.swatch_value"
                        type="text"
                        class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., #FF0000"
                      />
                    </div>
                  </div>

                  <!-- Remove Button -->
                  <button
                    type="button"
                    @click="removeOption(index)"
                    class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded"
                    title="Remove option"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>

              <p v-if="form.errors.options" class="mt-2 text-sm text-red-600">{{ form.errors.options }}</p>
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="flex justify-end gap-3">
            <Link
              :href="attributeRoutes.index().url"
              class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ form.processing ? 'Creating...' : 'Create Attribute' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import TiptapEditor from '@/components/Admin/TiptapEditor.vue';
import ImageUploader from '@/components/Admin/ImageUploader.vue';
import * as productRoutes from '@/routes/admin/catalog/products';
import { useCurrency } from '@/composables/useCurrency';

interface Category {
  id: number;
  name: string;
  parent_id: number | null;
  children?: Category[];
}

interface Brand {
  id: number;
  name: string;
}

interface TaxClass {
  id: number;
  name: string;
  code: string;
}

interface AttributeOption {
  id: number;
  attribute_id: number;
  label: string;
  value: string;
  swatch_value?: string;
  sort_order: number;
}

interface Attribute {
  id: number;
  name: string;
  code: string;
  type: 'text' | 'textarea' | 'select' | 'multiselect' | 'boolean' | 'date' | 'price';
  is_required: boolean;
  options?: AttributeOption[];
}

interface Props {
  categories: Category[];
  brands: Brand[];
  taxClasses: TaxClass[];
  attributes: Attribute[];
  errors?: Record<string, string>;
}

const props = defineProps<Props>();

// Active tab
const activeTab = ref<'general' | 'images' | 'attributes' | 'inventory' | 'seo'>('general');

// Images
const images = ref<File[]>([]);

// Attribute values - Initialize multiselect attributes as arrays
const attributeValues = ref<Record<string, any>>({});

// Currency symbol
const { getSymbol } = useCurrency();
const currencySymbol = computed(() => getSymbol());

// Selected attributes to display
const selectedAttributeIds = ref<number[]>([]);

// Initialize multiselect attributes when they are selected
const addAttribute = (attributeId: number) => {
  if (!selectedAttributeIds.value.includes(attributeId)) {
    selectedAttributeIds.value.push(attributeId);
    
    // Initialize multiselect as array
    const attribute = props.attributes.find(attr => attr.id === attributeId);
    if (attribute && attribute.type === 'multiselect') {
      attributeValues.value[attribute.code] = [];
    }
  }
};

const removeAttribute = (attributeId: number) => {
  const index = selectedAttributeIds.value.indexOf(attributeId);
  if (index > -1) {
    selectedAttributeIds.value.splice(index, 1);
    
    // Remove value
    const attribute = props.attributes.find(attr => attr.id === attributeId);
    if (attribute) {
      delete attributeValues.value[attribute.code];
    }
  }
};

const getSelectedAttributes = computed(() => {
  return props.attributes.filter(attr => selectedAttributeIds.value.includes(attr.id));
});

const getAvailableAttributes = computed(() => {
  return props.attributes.filter(attr => !selectedAttributeIds.value.includes(attr.id));
});

// Form data
const form = ref({
  name: '',
  slug: '',
  sku: '',
  description: '',
  short_description: '',
  price: '',
  tax_class_id: null as number | null,
  special_price: '',
  special_price_from: '',
  special_price_to: '',
  cost: '',
  quantity: '0',
  weight: '',
  status: 'enabled',
  visibility: 'both',
  featured: false,
  new: false,
  stock_status: 'in_stock',
  manage_stock: true,
  type: 'simple',
  brand_id: null as number | null,
  category_ids: [] as number[],
  meta_title: '',
  meta_description: '',
  meta_keywords: '',
});

// Generate slug from name
const generateSlug = () => {
  form.value.slug = form.value.name
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
};

// Flatten categories for multi-select
const flatCategories = computed(() => {
  const flatten = (cats: Category[], prefix = ''): Array<{ id: number; name: string }> => {
    return cats.reduce((acc: Array<{ id: number; name: string }>, cat) => {
      acc.push({ id: cat.id, name: prefix + cat.name });
      if (cat.children && cat.children.length > 0) {
        acc.push(...flatten(cat.children, prefix + cat.name + ' > '));
      }
      return acc;
    }, []);
  };
  return flatten(props.categories);
});

// Toggle category selection
const toggleCategory = (categoryId: number) => {
  const index = form.value.category_ids.indexOf(categoryId);
  if (index > -1) {
    form.value.category_ids.splice(index, 1);
  } else {
    form.value.category_ids.push(categoryId);
  }
};

// Submit form
const submit = () => {
  const formData = {
    ...form.value,
    attribute_values: attributeValues.value
  };
  
  router.post(productRoutes.store().url, formData, {
    onSuccess: () => {
      // Success message will be shown via toast
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

// Save as draft
const saveDraft = () => {
  form.value.status = 'disabled';
  submit();
};
</script>

<template>
  <Head title="Create Product" />
  
  <AdminLayout title="Create Product">
    <div class="p-6 max-w-7xl mx-auto space-y-6">
      
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Product</h1>
          <div class="flex items-center gap-2 mt-1 text-sm text-gray-500 dark:text-gray-400">
            <Link :href="productRoutes.index().url" class="hover:text-blue-600 transition-colors">Products</Link>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <span>New Product</span>
          </div>
        </div>
        
        <div class="flex items-center gap-3">
          <Link 
            :href="productRoutes.index().url" 
            class="hidden sm:inline-flex px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
          >
            Cancel
          </Link>
          <button 
            type="button" 
            @click="saveDraft"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
          >
            Save Draft
          </button>
          <button 
            type="button" 
            @click="submit"
            class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            Create Product
          </button>
        </div>
      </div>

      <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Content (2/3 width) -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700">
              <nav class="-mb-px flex space-x-8 overflow-x-auto">
                <button type="button" @click="activeTab = 'general'" :class="[activeTab === 'general' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                  General
                </button>
                <button type="button" @click="activeTab = 'images'" :class="[activeTab === 'images' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                  Images
                </button>
                <button type="button" @click="activeTab = 'attributes'" :class="[activeTab === 'attributes' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                  Attributes
                </button>
                <button type="button" @click="activeTab = 'inventory'" :class="[activeTab === 'inventory' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                  Inventory & Pricing
                </button>
                <button v-if="form.type === 'downloadable'" type="button" @click="activeTab = 'downloads'" :class="[activeTab === 'downloads' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                  Downloads
                </button>
                <button type="button" @click="activeTab = 'seo'" :class="[activeTab === 'seo' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                  SEO
                </button>
              </nav>
            </div>

            <!-- General Tab -->
            <div v-show="activeTab === 'general'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">General Information</h3>

              <div class="space-y-6">
                <!-- Product Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Product Name <span class="text-red-500">*</span>
                  </label>
                  <input v-model="form.name" type="text" @input="generateSlug" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" :class="{ 'border-red-500': errors?.name }" placeholder="Enter product name" />
                  <p v-if="errors?.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                </div>

                <!-- Slug -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Slug <span class="text-red-500">*</span>
                  </label>
                  <input v-model="form.slug" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" :class="{ 'border-red-500': errors?.slug }" placeholder="product-slug" />
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">URL-friendly version of the product name</p>
                  <p v-if="errors?.slug" class="mt-1 text-sm text-red-600">{{ errors.slug }}</p>
                </div>

                <!-- SKU -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    SKU <span class="text-red-500">*</span>
                  </label>
                  <input v-model="form.sku" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" :class="{ 'border-red-500': errors?.sku }" placeholder="PROD-001" />
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Stock Keeping Unit - Unique product identifier</p>
                  <p v-if="errors?.sku" class="mt-1 text-sm text-red-600">{{ errors.sku }}</p>
                </div>

                <!-- Product Type -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Product Type <span class="text-red-500">*</span>
                  </label>
                  <select v-model="form.type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" :class="{ 'border-red-500': errors?.type }">
                    <option value="simple">Simple Product</option>
                    <option value="configurable">Configurable Product (Variants)</option>
                    <option value="virtual">Virtual Product (No Shipping)</option>
                    <option value="downloadable">Downloadable Product (Digital)</option>
                  </select>
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <span v-if="form.type === 'simple'">Physical product with no variants</span>
                    <span v-else-if="form.type === 'configurable'">Product with options like size, color, etc.</span>
                    <span v-else-if="form.type === 'virtual'">Non-physical product (no shipping required)</span>
                    <span v-else-if="form.type === 'downloadable'">Digital file product with download links</span>
                  </p>
                  <p v-if="errors?.type" class="mt-1 text-sm text-red-600">{{ errors.type }}</p>
                </div>

                <!-- Brand -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Brand</label>
                  <select v-model="form.brand_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option :value="null">Select Brand</option>
                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                      {{ brand.name }}
                    </option>
                  </select>
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Associate this product with a brand</p>
                </div>

                <!-- Short Description -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Short Description</label>
                  <TiptapEditor v-model="form.short_description" placeholder="Brief product description" />
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Displayed in product listings</p>
                </div>

                <!-- Full Description -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Description</label>
                  <TiptapEditor v-model="form.description" placeholder="Detailed product description with formatting..." />
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Complete product details and specifications</p>
                </div>
              </div>
            </div>

            <!-- Images Tab -->
            <div v-show="activeTab === 'images'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Product Images</h3>
              <ImageUploader v-model="images" :maxFiles="10" :maxSize="5" accept="image/*" />
              <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">Note: Backend integration for image upload will be implemented in the next phase</p>
            </div>

            <!-- Attributes Tab -->
            <div v-show="activeTab === 'attributes'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
              <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Product Attributes</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Add and specify product characteristics and specifications</p>
              </div>
              
              <!-- Attribute Selector -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Add Attribute
                </label>
                <select 
                  @change="(e) => { addAttribute(Number((e.target as HTMLSelectElement).value)); (e.target as HTMLSelectElement).value = ''; }"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="">Select an attribute to add...</option>
                  <option 
                    v-for="attribute in getAvailableAttributes" 
                    :key="attribute.id" 
                    :value="attribute.id"
                  >
                    {{ attribute.name }}
                    <template v-if="attribute.is_required"> (Required)</template>
                  </option>
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Choose attributes that apply to this product</p>
              </div>

              <!-- No Attributes Selected State -->
              <div v-if="selectedAttributeIds.length === 0" class="text-center py-12 bg-gray-50 dark:bg-gray-800/50 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No attributes added yet</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Select attributes from the dropdown above to add product specifications</p>
              </div>

              <!-- Selected Attributes -->
              <div v-else class="space-y-4">
                <div 
                  v-for="attribute in getSelectedAttributes" 
                  :key="attribute.id" 
                  class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-300 dark:hover:border-blue-700 transition-colors bg-white dark:bg-gray-800"
                >
                  <!-- Attribute Header -->
                  <div class="flex items-start justify-between mb-3">
                    <div>
                      <label class="block text-sm font-medium text-gray-900 dark:text-white">
                        {{ attribute.name }}
                        <span v-if="attribute.is_required" class="text-red-500 ml-1">*</span>
                      </label>
                      <div class="flex items-center gap-2 mt-1">
                        <span v-if="attribute.is_configurable" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300">
                          Configurable
                        </span>
                        <span v-if="attribute.is_filterable" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                          Filterable
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ attribute.type }}</span>
                      </div>
                    </div>
                    <button
                      v-if="!attribute.is_required"
                      type="button"
                      @click="removeAttribute(attribute.id)"
                      class="text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                      title="Remove attribute"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>

                  <!-- Attribute Input Fields -->
                  <div class="mt-3">
                    <!-- Text Input -->
                    <input
                      v-if="attribute.type === 'text'"
                      v-model="attributeValues[attribute.code]"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      :placeholder="`Enter ${attribute.name.toLowerCase()}`"
                    />

                    <!-- Textarea -->
                    <textarea
                      v-if="attribute.type === 'textarea'"
                      v-model="attributeValues[attribute.code]"
                      rows="3"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      :placeholder="`Enter ${attribute.name.toLowerCase()}`"
                    />

                    <!-- Select -->
                    <select
                      v-if="attribute.type === 'select'"
                      v-model="attributeValues[attribute.code]"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="">Select {{ attribute.name }}</option>
                      <option v-for="option in attribute.options" :key="option.id" :value="option.value">
                        {{ option.label }}
                      </option>
                    </select>

                    <!-- Multiselect with better UI -->
                    <div v-if="attribute.type === 'multiselect'" class="grid grid-cols-2 md:grid-cols-3 gap-3">
                      <label 
                        v-for="option in attribute.options" 
                        :key="option.id" 
                        class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20 cursor-pointer transition-colors"
                        :class="{ 'bg-blue-50 dark:bg-blue-900/30 border-blue-300 dark:border-blue-700': attributeValues[attribute.code]?.includes(option.value) }"
                      >
                        <input
                          :id="`${attribute.code}-${option.id}`"
                          type="checkbox"
                          :value="option.value"
                          v-model="attributeValues[attribute.code]"
                          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700"
                        />
                        <span class="ml-3 text-sm text-gray-700 dark:text-gray-300 flex items-center">
                          <span 
                            v-if="option.swatch_value" 
                            class="w-5 h-5 rounded-full mr-2 border border-gray-300 dark:border-gray-600" 
                            :style="{ backgroundColor: option.swatch_value }"
                          ></span>
                          {{ option.label }}
                        </span>
                      </label>
                    </div>

                    <!-- Boolean -->
                    <div v-if="attribute.type === 'boolean'" class="flex items-center p-3 bg-gray-50 dark:bg-gray-800/50 rounded-md border border-gray-200 dark:border-gray-700">
                      <input
                        :id="attribute.code"
                        type="checkbox"
                        v-model="attributeValues[attribute.code]"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700"
                      />
                      <label :for="attribute.code" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                        Enable {{ attribute.name }}
                      </label>
                    </div>

                    <!-- Date -->
                    <input
                      v-if="attribute.type === 'date'"
                      v-model="attributeValues[attribute.code]"
                      type="date"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />

                    <!-- Price -->
                    <div v-if="attribute.type === 'price'" class="relative">
                      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">{{ currencySymbol }}</span>
                      <input
                        v-model="attributeValues[attribute.code]"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :placeholder="`Enter ${attribute.name.toLowerCase()}`"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Inventory & Pricing Tab -->
            <div v-show="activeTab === 'inventory'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Pricing</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Price -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Regular Price <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500 dark:text-gray-400">{{ currencySymbol }}</span>
                    <input v-model="form.price" type="number" step="0.01" class="w-full pl-7 pr-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" :class="{ 'border-red-500': errors?.price }" placeholder="0.00" />
                  </div>
                  <p v-if="errors?.price" class="mt-1 text-sm text-red-600">{{ errors.price }}</p>
                </div>

                <!-- Cost -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cost</label>
                  <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500 dark:text-gray-400">{{ currencySymbol }}</span>
                    <input v-model="form.cost" type="number" step="0.01" class="w-full pl-7 pr-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0.00" />
                  </div>
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cost per unit (for profit calculation)</p>
                </div>

                <!-- Tax Class -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tax Class</label>
                  <select
                    v-model="form.tax_class_id"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option :value="null">No Tax</option>
                    <option v-for="taxClass in props.taxClasses" :key="taxClass.id" :value="taxClass.id">
                      {{ taxClass.name }}
                    </option>
                  </select>
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Select applicable tax class for this product</p>
                </div>
              </div>

              <div class="border-t border-gray-200 dark:border-gray-700 pt-8 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Special Pricing</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <!-- Special Price -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Special Price</label>
                    <div class="relative">
                      <span class="absolute left-3 top-2 text-gray-500 dark:text-gray-400">{{ currencySymbol }}</span>
                      <input v-model="form.special_price" type="number" step="0.01" class="w-full pl-7 pr-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0.00" />
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Sale price (optional)</p>
                  </div>

                  <!-- From Date -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
                    <input v-model="form.special_price_from" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                  </div>

                  <!-- To Date -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Date</label>
                    <input v-model="form.special_price_to" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                  </div>
                </div>
              </div>

              <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Inventory</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Quantity -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Quantity <span class="text-red-500">*</span>
                    </label>
                    <input v-model="form.quantity" type="number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" :class="{ 'border-red-500': errors?.quantity }" placeholder="0" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Available stock quantity</p>
                    <p v-if="errors?.quantity" class="mt-1 text-sm text-red-600">{{ errors.quantity }}</p>
                  </div>

                  <!-- Weight - Only for physical products -->
                  <div v-if="form.type === 'simple' || form.type === 'configurable'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Weight (kg)</label>
                    <input 
                      v-model="form.weight" 
                      type="text" 
                      class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white dark:border-gray-600"
                      :class="props.errors?.weight ? 'border-red-500' : 'border-gray-300'"
                      placeholder="0.00" 
                    />
                    <p v-if="props.errors?.weight" class="mt-1 text-sm text-red-600">{{ props.errors.weight }}</p>
                    <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">Product weight for shipping</p>
                  </div>

                  <!-- Digital Product Notice -->
                  <div v-if="form.type === 'virtual' || form.type === 'downloadable'" class="col-span-2">
                    <div class="bg-blue-50 dark:bg-blue-900/40 border border-blue-200 dark:border-blue-800 rounded-md p-4">
                      <div class="flex">
                        <svg class="h-5 w-5 text-blue-400 dark:text-blue-300 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                          <p class="text-sm text-blue-800 dark:text-blue-200 font-medium">{{ form.type === 'virtual' ? 'Virtual Product' : 'Downloadable Product' }}</p>
                          <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            {{ form.type === 'virtual' ? 'This product does not require shipping.' : 'This product will be available for download after purchase.' }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Downloadable Files Tab -->
            <div v-show="activeTab === 'downloads'" v-if="form.type === 'downloadable'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Downloadable Files</h3>
              
              <div class="space-y-6">
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center hover:border-blue-400 dark:hover:border-blue-500 transition-colors">
                  <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    <label for="file-upload" class="relative cursor-pointer rounded-md font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300">
                      <span>Upload files</span>
                      <input id="file-upload" name="file-upload" type="file" class="sr-only" multiple />
                    </label>
                    or drag and drop
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Any file type up to 50MB</p>
                </div>

                <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-md p-4">
                  <div class="flex">
                    <svg class="h-5 w-5 text-yellow-400 dark:text-yellow-500 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                      <p class="text-sm text-yellow-800 dark:text-yellow-200 font-medium">File Management Coming Soon</p>
                      <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                        Downloadable file upload and management will be available in the next update. For now, you can create the product and add files later.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- SEO Tab -->
            <div v-show="activeTab === 'seo'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Search Engine Optimization</h3>

              <div class="space-y-6">
                <!-- Meta Title -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta Title</label>
                  <input v-model="form.meta_title" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Product Name | Your Store" />
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommended: 50-60 characters</p>
                </div>

                <!-- Meta Description -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta Description</label>
                  <textarea v-model="form.meta_description" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Brief description for search engines"></textarea>
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommended: 150-160 characters</p>
                </div>

                <!-- Meta Keywords -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta Keywords</label>
                  <input v-model="form.meta_keywords" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="keyword1, keyword2, keyword3" />
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Comma-separated keywords</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar (1/3 width) -->
          <div class="lg:col-span-1 space-y-6">
            <!-- Publish Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Publish</h3>

              <div class="space-y-4">
                <!-- Status -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                  <select v-model="form.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="enabled">Enabled</option>
                    <option value="disabled">Disabled</option>
                  </select>
                </div>

                <!-- Visibility -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Visibility</label>
                  <select v-model="form.visibility" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="both">Catalog & Search</option>
                    <option value="catalog">Catalog Only</option>
                    <option value="search">Search Only</option>
                    <option value="none">Not Visible</option>
                  </select>
                </div>

                <!-- Featured -->
                <div class="flex items-center">
                  <input v-model="form.featured" type="checkbox" id="featured" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700" />
                  <label for="featured" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Featured Product</label>
                </div>

                <!-- New -->
                <div class="flex items-center">
                  <input v-model="form.new" type="checkbox" id="new" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700" />
                  <label for="new" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Mark as New</label>
                </div>
              </div>

              <!-- Buttons moved to Header -->
            </div>

            <!-- Categories Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Categories</h3>

              <div v-if="errors?.category_ids" class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md">
                <p class="text-sm text-red-600 dark:text-red-400">{{ errors.category_ids }}</p>
              </div>

              <div class="max-h-80 overflow-y-auto space-y-2">
                <div v-for="category in flatCategories" :key="category.id" class="flex items-center">
                  <input :id="`category_${category.id}`" type="checkbox" :checked="form.category_ids.includes(category.id)" @change="toggleCategory(category.id)" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700" />
                  <label :for="`category_${category.id}`" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">{{ category.name }}</label>
                </div>
              </div>

              <div v-if="flatCategories.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-2 text-sm">No categories available</p>
              </div>
            </div>
          </div>
      </form>
    </div>
  </AdminLayout>
</template>

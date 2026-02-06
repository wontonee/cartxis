<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import TiptapEditor from '@/components/Admin/TiptapEditor.vue';
import ImageUploader from '@/components/Admin/ImageUploader.vue';
import InventoryTracker from '@/components/Admin/InventoryTracker.vue';
import * as productRoutes from '@/routes/admin/catalog/products';
import axios from '@/lib/axios';
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
  is_filterable: boolean;
  is_configurable: boolean;
  options?: AttributeOption[];
}

interface Product {
  id: number;
  name: string;
  slug: string;
  sku: string;
  description: string | null;
  short_description: string | null;
  price: string;
  special_price: string | null;
  special_price_from: string | null;
  special_price_to: string | null;
  cost: string | null;
  quantity: number;
  weight: string | null;
  status: string;
  visibility: string;
  featured: boolean;
  new: boolean;
  stock_status: string;
  manage_stock: boolean;
  type: string;
  brand_id: number | null;
  meta_title: string | null;
  meta_description: string | null;
  meta_keywords: string | null;
  tax_class_id?: number | null;
  categories: Array<{ id: number; name: string }>;
  main_image_id: number | null;
  images: Array<{
    id: number;
    path: string;
    url: string; // Added url property
    thumbnail_path: string;
    alt_text: string | null;
    position: number;
  }>;
  attribute_values?: Array<{
    id: number;
    attribute_id: number;
    value: any;
    attribute?: {
      id: number;
      code: string;
      name: string;
      type: string;
    };
  }>;
}

interface Props {
  product: Product;
  categories: Category[];
  brands: Brand[];
  taxClasses: TaxClass[];
  attributes: Attribute[];
  ai?: {
    enabled?: boolean;
    agents?: Array<{ name: string }>;
    product_description_agent?: string;
  };
  currency?: {
    code: string;
    symbol: string;
    symbol_position: string;
    decimal_places: number;
  };
  adjustmentHistory?: Array<{
    id: number;
    type: 'addition' | 'subtraction' | 'correction';
    quantity: number;
    quantity_adjusted: number;
    reason: string;
    notes: string | null;
    created_at: string;
    user?: { id: number; name: string };
  }>;
  errors?: Record<string, string>;
}

const props = defineProps<Props>();

const aiEnabled = computed(() => props.ai?.enabled ?? false);

// Currency symbol - use from props if available, otherwise use composable
const { getSymbol } = useCurrency();
const currencySymbol = computed(() => props.currency?.symbol || getSymbol());

// Active tab
const activeTab = ref<'general' | 'images' | 'attributes' | 'inventory' | 'seo' | 'downloads'>('general');

// Images for upload
const images = ref<File[]>([]);
const isUploading = ref(false);

// Attribute values - Initialize multiselect attributes as arrays
const attributeValues = ref<Record<string, any>>({});

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

// Delete image modal
const showDeleteImageModal = ref(false);
const imageToDelete = ref<number | null>(null);
const isDeletingImage = ref(false);

// Upload images to backend
const uploadImages = async () => {
  if (images.value.length === 0) {
    alert('Please select images to upload');
    return;
  }

  isUploading.value = true;
  const formData = new FormData();
  
  images.value.forEach((image) => {
    formData.append('images[]', image);
  });

  try {
    const response = await axios.post(
      `/admin/catalog/products/${props.product.id}/images/upload`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      }
    );

    images.value = []; // Clear selected images
    
    // Reload the page to show new images
    router.reload({ 
      only: ['product'],
      preserveScroll: true,
    });
  } catch (error: any) {
    console.error('Upload error:', error);
    const message = error.response?.data?.message || error.message || 'Failed to upload images';
    alert(`Upload failed: ${message}`);
  } finally {
    isUploading.value = false;
  }
};

// Delete an image
const openDeleteImageModal = (imageId: number) => {
  imageToDelete.value = imageId;
  showDeleteImageModal.value = true;
};

const deleteImage = async () => {
  if (!imageToDelete.value) return;

  isDeletingImage.value = true;
  
  try {
    await axios.delete(`/admin/catalog/products/${props.product.id}/images/${imageToDelete.value}`);
    
    showDeleteImageModal.value = false;
    imageToDelete.value = null;
    
    // Reload the page to update images
    router.reload({ 
      only: ['product'],
      preserveScroll: true,
    });
  } catch (error: any) {
    console.error('Delete error:', error);
    const message = error.response?.data?.message || 'Failed to delete image';
    alert(message);
  } finally {
    isDeletingImage.value = false;
  }
};

// Set main image
const setMainImage = async (imageId: number) => {
  try {
    await axios.post(`/admin/catalog/products/${props.product.id}/images/${imageId}/set-main`);
    
    // Reload the page to update main image
    router.reload({ 
      only: ['product'],
      preserveScroll: true,
    });
  } catch (error: any) {
    console.error('Set main image error:', error);
    const message = error.response?.data?.message || 'Failed to set main image';
    alert(message);
  }
};

// Delete modal
const showDeleteModal = ref(false);
const isDeleting = ref(false);

// Form data
const form = ref({
  name: props.product.name,
  slug: props.product.slug,
  sku: props.product.sku,
  description: props.product.description || '',
  short_description: props.product.short_description || '',
  price: props.product.price,
  tax_class_id: props.product.tax_class_id || null,
  special_price: props.product.special_price || '',
  special_price_from: props.product.special_price_from || '',
  special_price_to: props.product.special_price_to || '',
  cost: props.product.cost || '',
  quantity: props.product.quantity.toString(),
  weight: props.product.weight || '',
  status: props.product.status,
  visibility: props.product.visibility,
  featured: props.product.featured,
  new: props.product.new,
  stock_status: props.product.stock_status,
  manage_stock: props.product.manage_stock,
  type: props.product.type,
  brand_id: props.product.brand_id || null,
  category_ids: props.product.categories.map(cat => cat.id),
  meta_title: props.product.meta_title || '',
  meta_description: props.product.meta_description || '',
  meta_keywords: props.product.meta_keywords || '',
});

// AI Description Generator
const aiTone = ref<'professional' | 'casual' | 'luxury' | 'minimalist'>('professional');
const aiLanguage = ref('en');
const aiTargetAudience = ref('');
const aiKeyFeatures = ref('');
const aiIsGenerating = ref(false);
const aiResult = ref<any | null>(null);
const aiError = ref<string | null>(null);
const resolveAgents = (): Array<{ name: string }> => {
  const raw = props.ai?.agents || [];
  if (Array.isArray(raw)) {
    return raw as Array<{ name: string }>;
  }
  if (typeof raw === 'string') {
    try {
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : [];
    } catch {
      return [];
    }
  }
  return [];
};

const aiAgentOptions = computed(() => {
  return resolveAgents().map(agent => agent.name).filter(Boolean);
});

const aiAgent = ref(props.ai?.product_description_agent || '');

if (!aiAgent.value && aiAgentOptions.value.length > 0) {
  aiAgent.value = aiAgentOptions.value[0];
}

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

const selectedCategoryPath = computed(() => {
  const names = flatCategories.value
    .filter(cat => form.value.category_ids.includes(cat.id))
    .map(cat => cat.name);
  return names.join(' > ');
});

const selectedBrandName = computed(() => {
  return props.brands.find(brand => brand.id === form.value.brand_id)?.name || '';
});

const keyFeaturesList = computed(() => {
  return aiKeyFeatures.value
    .split(',')
    .map(item => item.trim())
    .filter(Boolean);
});

const generateAiDescription = async () => {
  aiIsGenerating.value = true;
  aiError.value = null;
  try {
    const response = await axios.post(
      `/admin/catalog/products/${props.product.id}/generate-description`,
      {
        product_title: form.value.name,
        category: selectedCategoryPath.value || undefined,
        attributes: attributeValues.value,
        brand: selectedBrandName.value || undefined,
        key_features: keyFeaturesList.value,
        target_audience: aiTargetAudience.value || undefined,
        tone_preference: aiTone.value,
        language: aiLanguage.value,
        agent: aiAgent.value || undefined,
      }
    );

    if (response.data?.success) {
      aiResult.value = response.data.data?.data || response.data.data;
    } else {
      aiError.value = response.data?.message || 'AI generation failed.';
    }
  } catch (error: any) {
    aiError.value = error.response?.data?.message || error.message || 'AI generation failed.';
  } finally {
    aiIsGenerating.value = false;
  }
};

const applyAiDescription = () => {
  if (!aiResult.value) {
    return;
  }

  if (aiResult.value.short_description) {
    form.value.short_description = aiResult.value.short_description;
  }

  if (aiResult.value.long_description) {
    form.value.description = aiResult.value.long_description;
  }

  if (aiResult.value.meta_description) {
    form.value.meta_description = aiResult.value.meta_description;
  }

  if (Array.isArray(aiResult.value.keywords)) {
    form.value.meta_keywords = aiResult.value.keywords.join(', ');
  }
};

// Toggle category selection
const toggleCategory = (categoryId: number) => {
  const index = form.value.category_ids.indexOf(categoryId);
  if (index > -1) {
    form.value.category_ids.splice(index, 1);
  } else {
    form.value.category_ids.push(categoryId);
  }
};

// Initialize existing attribute values on component mount
onMounted(() => {
  // Load existing attribute values from the product
  if (props.product.attribute_values && props.product.attribute_values.length > 0) {
    // Group attribute values by attribute_id for multiselect handling
    const groupedByAttribute = props.product.attribute_values.reduce((acc: any, attrValue: any) => {
      if (!acc[attrValue.attribute_id]) {
        acc[attrValue.attribute_id] = [];
      }
      acc[attrValue.attribute_id].push(attrValue);
      return acc;
    }, {});

    // Process each attribute
    Object.keys(groupedByAttribute).forEach((attributeIdStr) => {
      const attributeId = Number(attributeIdStr);
      const attribute = props.attributes.find(attr => attr.id === attributeId);
      
      if (attribute) {
        // Add attribute to selected list
        if (!selectedAttributeIds.value.includes(attribute.id)) {
          selectedAttributeIds.value.push(attribute.id);
        }

        const attrValues = groupedByAttribute[attributeId];

        // Set the value based on attribute type
        if (attribute.type === 'multiselect' || attribute.type === 'select') {
          // For multiselect/select, collect all option values
          const optionValues = attrValues
            .filter((av: any) => av.attribute_option_id)
            .map((av: any) => av.option?.value || String(av.attribute_option_id));
          
          if (attribute.type === 'multiselect') {
            attributeValues.value[attribute.code] = optionValues;
          } else {
            attributeValues.value[attribute.code] = optionValues[0] || '';
          }
        } else if (attribute.type === 'boolean') {
          // Convert to boolean
          const boolValue = attrValues[0]?.boolean_value;
          attributeValues.value[attribute.code] = boolValue === 1 || boolValue === '1' || boolValue === true;
        } else if (attribute.type === 'date') {
          // Date value
          attributeValues.value[attribute.code] = attrValues[0]?.date_value || '';
        } else {
          // For text/textarea types, use text_value
          attributeValues.value[attribute.code] = attrValues[0]?.text_value || '';
        }
      }
    });
  }
});

const submitForm = () => {
  const formData = {
    ...form.value,
    attribute_values: attributeValues.value
  };
  
  router.put(productRoutes.update({ product: props.product.id }), formData);
};

// Handle stock adjustment (save immediately)
const handleStockAdjustment = (data: { quantity: number; adjustment: any }) => {
  // Update form quantity
  form.value.quantity = data.quantity.toString();
  
  // Save to backend using Inertia router (preserves all form fields)
  router.put(
    productRoutes.update({ product: props.product.id }).url,
    form.value,
    {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        // Success toast will be shown via flash message from backend
      },
      onError: (errors) => {
        console.error('Failed to adjust stock:', errors);
        // Error details logged to console for debugging
      }
    }
  );
};

// Delete product
const deleteProduct = () => {
  isDeleting.value = true;
  router.delete(productRoutes.destroy({ product: props.product.id }), {
    onFinish: () => {
      isDeleting.value = false;
      showDeleteModal.value = false;
    }
  });
};
</script>

<template>
  <Head title="Edit Product" />
  
  <AdminLayout title="Edit Product">
    <div class="p-6 max-w-7xl mx-auto space-y-6">
      
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Product</h1>
          <div class="flex items-center gap-2 mt-1 text-sm text-gray-500 dark:text-gray-400">
            <Link :href="productRoutes.index().url" class="hover:text-blue-600 transition-colors">Products</Link>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <span>{{ product.name }}</span>
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
            @click="showDeleteModal = true"
            class="px-4 py-2 border border-red-300 dark:border-red-700 rounded-lg text-sm font-medium text-red-700 dark:text-red-400 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
          >
            Delete
          </button>
          <button 
            type="button" 
            @click="submitForm"
            class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            Update Product
          </button>
        </div>
      </div>

      <!-- Main Content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Column (2/3) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Tabs Navigation -->
          <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
              <button
                @click="activeTab = 'general'"
                :class="[
                  activeTab === 'general'
                   ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                   : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                General
              </button>
              <button
                @click="activeTab = 'images'"
                :class="[
                  activeTab === 'images'
                   ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                   : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Images
              </button>
              <button
                @click="activeTab = 'attributes'"
                :class="[
                  activeTab === 'attributes'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Attributes
              </button>
              <button
                @click="activeTab = 'inventory'"
                :class="[
                  activeTab === 'inventory'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Inventory & Pricing
              </button>
              <button
                v-if="form.type === 'downloadable'"
                @click="activeTab = 'downloads'"
                :class="[
                  activeTab === 'downloads'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Downloads
              </button>
              <button
                @click="activeTab = 'seo'"
                :class="[
                  activeTab === 'seo'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                SEO
              </button>
            </nav>
          </div>

          <!-- General Tab -->
          <div v-show="activeTab === 'general'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                <!-- Product Name -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Product Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="name"
                    v-model="form.name"
                    @input="generateSlug"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors?.name }"
                    placeholder="Enter product name"
                  />
                  <p v-if="errors?.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                </div>

                <!-- Slug -->
                <div>
                  <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Slug <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="slug"
                    v-model="form.slug"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors?.slug }"
                    placeholder="auto-generated-from-name"
                  />
                  <p v-if="errors?.slug" class="mt-1 text-sm text-red-600">{{ errors.slug }}</p>
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Auto-generated from product name</p>
                </div>

                <!-- SKU -->
                <div>
                  <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    SKU <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="sku"
                    v-model="form.sku"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors?.sku }"
                    placeholder="Enter SKU"
                  />
                  <p v-if="errors?.sku" class="mt-1 text-sm text-red-600">{{ errors.sku }}</p>
                </div>

                <!-- Product Type -->
                <div>
                  <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Product Type <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="type"
                    v-model="form.type"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors?.type }"
                  >
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
                  <label for="brand" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Brand</label>
                  <select
                    id="brand"
                    v-model="form.brand_id"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option :value="null">Select Brand</option>
                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                      {{ brand.name }}
                    </option>
                  </select>
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Associate this product with a brand</p>
                </div>

                <!-- AI Description Generator -->
                <div v-if="aiEnabled" class="border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800/50 p-4 space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-sm font-semibold text-gray-900 dark:text-white">AI Product Description</h3>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Generate SEO-optimized descriptions from product data.</p>
                    </div>
                    <button
                      type="button"
                      @click="generateAiDescription"
                      :disabled="aiIsGenerating"
                      class="inline-flex items-center px-3 py-2 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                      {{ aiIsGenerating ? 'Generating...' : 'Generate Description' }}
                    </button>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tone</label>
                      <select v-model="aiTone" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md text-sm">
                        <option value="professional">Professional</option>
                        <option value="casual">Casual</option>
                        <option value="luxury">Luxury</option>
                        <option value="minimalist">Minimalist</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Language</label>
                      <select v-model="aiLanguage" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md text-sm">
                        <option value="en">English</option>
                        <option value="es">Spanish</option>
                        <option value="fr">French</option>
                        <option value="de">German</option>
                        <option value="zh">Chinese</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">AI Agent</label>
                      <select v-model="aiAgent" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md text-sm">
                        <option value="">Select agent</option>
                        <option v-for="agent in aiAgentOptions" :key="agent" :value="agent">
                          {{ agent }}
                        </option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Target Audience</label>
                      <input v-model="aiTargetAudience" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md text-sm" placeholder="e.g., gym enthusiasts" />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Key Features (comma separated)</label>
                      <input v-model="aiKeyFeatures" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md text-sm" placeholder="Active noise cancellation, 30-hour battery" />
                    </div>
                  </div>

                  <p v-if="aiError" class="text-xs text-red-600">{{ aiError }}</p>

                  <div v-if="aiResult" class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-3">
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Short Description</label>
                      <p class="text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md p-3" v-text="aiResult.short_description"></p>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Long Description</label>
                      <p class="text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md p-3 whitespace-pre-line" v-text="aiResult.long_description"></p>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Description</label>
                      <p class="text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md p-3" v-text="aiResult.meta_description"></p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Bullet Points</label>
                        <ul class="text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md p-3 list-disc pl-5" v-if="Array.isArray(aiResult.bullet_points)">
                          <li v-for="(item, idx) in aiResult.bullet_points" :key="idx">{{ item }}</li>
                        </ul>
                      </div>
                      <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Keywords</label>
                        <div class="text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md p-3">
                          {{ Array.isArray(aiResult.keywords) ? aiResult.keywords.join(', ') : '' }}
                        </div>
                      </div>
                    </div>
                    <div class="flex justify-end">
                      <button type="button" class="px-3 py-2 text-xs font-medium text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-700 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20" @click="applyAiDescription">
                        Apply to Product Fields
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Short Description -->
                <div>
                  <label for="short_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Short Description
                  </label>
                  <TiptapEditor v-model="form.short_description" placeholder="Brief product description" />
                </div>

                <!-- Description -->
                <div>
                  <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Description
                  </label>
                  <TiptapEditor v-model="form.description" placeholder="Detailed product description with formatting..." />
                </div>
              </div>

              <!-- Images Tab -->
              <div v-show="activeTab === 'images'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                <!-- Existing Images -->
                <div v-if="product.images && product.images.length > 0">
                  <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Current Images</h3>
                  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
                    <div
                      v-for="image in product.images"
                      :key="image.id"
                      class="space-y-2"
                    >
                      <!-- Image Container -->
                      <div class="aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600"
                           :class="{ 'border-blue-500': product.main_image_id === image.id }">
                        <div class="w-full h-full group relative">
                          <img
                            :src="image.url"
                            :alt="image.alt_text || product.name"
                            class="w-full h-full object-cover block"
                          />
                          
                          <!-- Main Image Badge -->
                          <div v-if="product.main_image_id === image.id" class="absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded z-20">
                            Main
                          </div>

                          <!-- Actions Overlay -->
                          <div class="absolute inset-0 bg-black/0 group-hover:bg-black/50 transition-all duration-200 flex items-center justify-center gap-2">
                            <button
                              v-if="product.main_image_id !== image.id"
                              type="button"
                              @click="setMainImage(image.id)"
                              class="cursor-pointer opacity-0 group-hover:opacity-100 transform scale-90 group-hover:scale-100 transition-all duration-200 bg-blue-600 text-white rounded-lg px-3 py-1.5 text-xs hover:bg-blue-700"
                              title="Set as main image"
                            >
                              Set Main
                            </button>
                            <button
                              type="button"
                              @click="openDeleteImageModal(image.id)"
                              class="cursor-pointer opacity-0 group-hover:opacity-100 transform scale-90 group-hover:scale-100 transition-all duration-200 bg-red-600 text-white rounded-lg px-3 py-1.5 text-xs hover:bg-red-700"
                              title="Delete image"
                            >
                              Delete
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Upload New Images -->
                <div>
                  <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Upload New Images</h3>
                  <ImageUploader v-model="images" :maxFiles="10" :maxSize="5" accept="image/*" />
                  <button
                    v-if="images.length > 0"
                    @click="uploadImages"
                    :disabled="isUploading"
                    type="button"
                    class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <svg v-if="isUploading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ isUploading ? 'Uploading...' : `Upload ${images.length} Image${images.length > 1 ? 's' : ''}` }}
                  </button>
                </div>
              </div>

              <!-- Inventory Tab -->
              <div v-show="activeTab === 'inventory'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <InventoryTracker
                  :product-id="product.id"
                  :current-stock="product.quantity"
                  :min-quantity="1"
                  :notify-stock-qty="5"
                  :manage-stock="product.manage_stock"
                  :warehouses="[]"
                  :adjustment-history="adjustmentHistory || []"
                  @update:stock="handleStockAdjustment"
                />
                
                <!-- Pricing Section (moved from old inventory tab) -->
                <div class="mt-6 space-y-6">
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pricing</h3>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Price <span class="text-red-500">*</span>
                      </label>
                      <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 dark:text-gray-400">{{ currencySymbol }}</span>
                        <input
                          id="price"
                          v-model="form.price"
                          type="number"
                          step="0.01"
                          class="w-full pl-7 pr-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          :class="{ 'border-red-500': errors?.price }"
                        />
                      </div>
                      <p v-if="errors?.price" class="mt-1 text-sm text-red-600">{{ errors.price }}</p>
                    </div>

                    <div>
                      <label for="cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Cost
                      </label>
                      <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 dark:text-gray-400">{{ currencySymbol }}</span>
                        <input
                          id="cost"
                          v-model="form.cost"
                          type="number"
                          step="0.01"
                          class="w-full pl-7 pr-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                    </div>

                    <div>
                      <label for="tax_class_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Tax Class
                      </label>
                      <select
                        id="tax_class_id"
                        v-model="form.tax_class_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      >
                        <option :value="null">No Tax</option>
                        <option v-for="taxClass in props.taxClasses" :key="taxClass.id" :value="taxClass.id">
                          {{ taxClass.name }}
                        </option>
                      </select>
                      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Select applicable tax class for this product</p>
                    </div>
                  </div>

                  <!-- Special Pricing -->
                  <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Special Pricing</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                      <div>
                        <label for="special_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                          Special Price
                        </label>
                        <div class="relative">
                          <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 dark:text-gray-400">{{ currencySymbol }}</span>
                          <input
                            id="special_price"
                            v-model="form.special_price"
                            type="number"
                            step="0.01"
                            class="w-full pl-7 pr-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          />
                        </div>
                      </div>

                      <div>
                        <label for="special_price_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                          From Date
                        </label>
                        <input
                          id="special_price_from"
                          v-model="form.special_price_from"
                          type="date"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>

                      <div>
                        <label for="special_price_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                          To Date
                        </label>
                        <input
                          id="special_price_to"
                          v-model="form.special_price_to"
                          type="date"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Weight - Only for physical products -->
                  <div v-if="form.type === 'simple' || form.type === 'configurable'" class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Physical Attributes</h4>
                    <div>
                      <label for="weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Weight (kg)
                      </label>
                      <input
                        id="weight"
                        v-model="form.weight"
                        type="number"
                        step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                  </div>

                  <!-- Digital Product Notice -->
                  <div v-if="form.type === 'virtual' || form.type === 'downloadable'" class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4">
                      <div class="flex">
                        <svg class="h-5 w-5 text-blue-400 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                          <p class="text-sm text-blue-800 dark:text-blue-300 font-medium">{{ form.type === 'virtual' ? 'Virtual Product' : 'Downloadable Product' }}</p>
                          <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">
                            {{ form.type === 'virtual' ? 'This product does not require shipping.' : 'This product will be available for download after purchase.' }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Downloadable Files Tab -->
              <div v-show="activeTab === 'downloads'" v-if="form.type === 'downloadable'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                <div>
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Downloadable Files</h3>
                  
                  <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center hover:border-blue-400 dark:hover:border-blue-500 transition-colors">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                      <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                      <label for="file-upload-edit" class="relative cursor-pointer rounded-md font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500">
                        <span>Upload files</span>
                        <input id="file-upload-edit" name="file-upload" type="file" class="sr-only" multiple />
                      </label>
                      or drag and drop
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Any file type up to 50MB</p>
                  </div>

                  <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700/50 rounded-md p-4">
                    <div class="flex">
                      <svg class="h-5 w-5 text-yellow-400 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                      </svg>
                      <div>
                        <p class="text-sm text-yellow-800 dark:text-yellow-300 font-medium">File Management Coming Soon</p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                          Downloadable file upload and management will be available in the next update.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- SEO Tab -->
              <div v-show="activeTab === 'seo'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                <div>
                  <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Meta Title
                  </label>
                  <input
                    id="meta_title"
                    v-model="form.meta_title"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="SEO title for search engines"
                  />
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommended: 50-60 characters</p>
                </div>

                <div>
                  <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Meta Description
                  </label>
                  <textarea
                    id="meta_description"
                    v-model="form.meta_description"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="SEO description for search engines"
                  />
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommended: 150-160 characters</p>
                </div>

                <div>
                  <label for="meta_keywords" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Meta Keywords
                  </label>
                  <input
                    id="meta_keywords"
                    v-model="form.meta_keywords"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="keyword1, keyword2, keyword3"
                  />
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Separate keywords with commas</p>
                </div>
              </div>

              <!-- Attributes Tab -->
              <div v-show="activeTab === 'attributes'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
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
                <div v-if="selectedAttributeIds.length === 0" class="text-center py-12 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
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
                    class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-300 dark:hover:border-blue-500 transition-colors bg-white dark:bg-gray-800"
                  >
                    <!-- Attribute Header -->
                    <div class="flex items-start justify-between mb-3">
                      <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">
                          {{ attribute.name }}
                          <span v-if="attribute.is_required" class="text-red-500 ml-1">*</span>
                        </label>
                        <div class="flex items-center gap-2 mt-1">
                          <span v-if="attribute.is_configurable" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200">
                            Configurable
                          </span>
                          <span v-if="attribute.is_filterable" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
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
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :placeholder="`Enter ${attribute.name.toLowerCase()}`"
                      />

                      <!-- Textarea -->
                      <textarea
                        v-if="attribute.type === 'textarea'"
                        v-model="attributeValues[attribute.code]"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                          :class="{ 'bg-blue-50 dark:bg-blue-900/30 border-blue-300 dark:border-blue-600': attributeValues[attribute.code]?.includes(option.value) }"
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
                      <div v-if="attribute.type === 'boolean'" class="flex items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-md border border-gray-200 dark:border-gray-700">
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
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      />

                      <!-- Price -->
                      <div v-if="attribute.type === 'price'" class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">{{ currencySymbol }}</span>
                        <input
                          v-model="attributeValues[attribute.code]"
                          type="number"
                          step="0.01"
                          min="0"
                          class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          :placeholder="`Enter ${attribute.name.toLowerCase()}`"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

        <!-- Sidebar (1/3) -->
        <div class="space-y-6">
          <!-- Publish Card -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Publish</h3>
            
            <!-- Status -->
            <div class="mb-4">
              <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Status
              </label>
              <select
                id="status"
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="enabled">Enabled</option>
                <option value="disabled">Disabled</option>
              </select>
            </div>

            <!-- Visibility -->
            <div class="mb-4">
              <label for="visibility" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Visibility
              </label>
              <select
                id="visibility"
                v-model="form.visibility"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="both">Catalog and Search</option>
                <option value="catalog">Catalog Only</option>
                <option value="search">Search Only</option>
                <option value="none">Not Visible</option>
              </select>
            </div>

            <!-- Featured & New -->
            <div class="space-y-3 mb-6">
              <label class="flex items-center">
                <input
                  v-model="form.featured"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700"
                />
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Featured Product</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="form.new"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700"
                />
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Mark as New</span>
              </label>
            </div>

            <!-- Action Buttons (Moved to Header) -->
            <div class="hidden space-y-2">
            </div>
          </div>

          <!-- Categories Card -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Categories</h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
              <label
                v-for="category in flatCategories"
                :key="category.id"
                class="flex items-start"
              >
                <input
                  type="checkbox"
                  :value="category.id"
                  :checked="form.category_ids.includes(category.id)"
                  @change="toggleCategory(category.id)"
                  class="mt-0.5 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700"
                />
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ category.name }}</span>
              </label>
            </div>
            <p v-if="errors?.category_ids" class="mt-2 text-sm text-red-600">{{ errors.category_ids }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmDeleteModal
      v-model:show="showDeleteModal"
      :title="product.name"
      :message="`Are you sure you want to delete '${product.name}'? This action cannot be undone.`"
      @confirm="deleteProduct"
    />

    <!-- Delete Image Modal -->
    <div
      v-if="showDeleteImageModal"
      class="relative z-50"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity"></div>

      <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div
            class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
            @click.stop
          >
            <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                  <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                  </svg>
                </div>
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                  <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Delete Image</h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      Are you sure you want to delete this image? This action cannot be undone.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
              <button
                @click="deleteImage"
                :disabled="isDeletingImage"
                type="button"
                class="cursor-pointer inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="isDeletingImage">Deleting...</span>
                <span v-else>Delete</span>
              </button>
              <button
                @click="showDeleteImageModal = false"
                type="button"
                class="cursor-pointer mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>


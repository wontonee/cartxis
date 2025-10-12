<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TiptapEditor from '@/Components/Admin/TiptapEditor.vue';
import ImageUploader from '@/Components/Admin/ImageUploader.vue';
import * as productRoutes from '@/routes/admin/catalog/products';

interface Category {
  id: number;
  name: string;
  parent_id: number | null;
  children?: Category[];
}

interface Props {
  categories: Category[];
  errors?: Record<string, string>;
}

const props = defineProps<Props>();

// Active tab
const activeTab = ref<'general' | 'images' | 'inventory' | 'seo'>('general');

// Images
const images = ref<File[]>([]);

// Form data
const form = ref({
  name: '',
  slug: '',
  sku: '',
  description: '',
  short_description: '',
  price: '',
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
  router.post(productRoutes.store().url, form.value, {
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
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl md:text-3xl text-gray-800 font-bold">Create Product</h1>
            <p class="text-sm text-gray-600 mt-1">Add a new product to your catalog</p>
          </div>
          <Link 
            :href="productRoutes.index().url" 
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Products
          </Link>
        </div>
      </div>

      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Content (2/3 width) -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Tabs -->
            <div class="border-b border-gray-200">
              <nav class="-mb-px flex space-x-8">
                <button type="button" @click="activeTab = 'general'" :class="[activeTab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                  General
                </button>
                <button type="button" @click="activeTab = 'images'" :class="[activeTab === 'images' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                  Images
                </button>
                <button type="button" @click="activeTab = 'inventory'" :class="[activeTab === 'inventory' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                  Inventory & Pricing
                </button>
                <button type="button" @click="activeTab = 'seo'" :class="[activeTab === 'seo' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                  SEO
                </button>
              </nav>
            </div>

            <!-- General Tab -->
            <div v-show="activeTab === 'general'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-6">General Information</h3>

              <div class="space-y-6">
                <!-- Product Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Product Name <span class="text-red-500">*</span>
                  </label>
                  <input v-model="form.name" type="text" @input="generateSlug" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" :class="{ 'border-red-500': errors?.name }" placeholder="Enter product name" />
                  <p v-if="errors?.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                </div>

                <!-- Slug -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Slug <span class="text-red-500">*</span>
                  </label>
                  <input v-model="form.slug" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" :class="{ 'border-red-500': errors?.slug }" placeholder="product-slug" />
                  <p class="mt-1 text-xs text-gray-500">URL-friendly version of the product name</p>
                  <p v-if="errors?.slug" class="mt-1 text-sm text-red-600">{{ errors.slug }}</p>
                </div>

                <!-- SKU -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    SKU <span class="text-red-500">*</span>
                  </label>
                  <input v-model="form.sku" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" :class="{ 'border-red-500': errors?.sku }" placeholder="PROD-001" />
                  <p class="mt-1 text-xs text-gray-500">Stock Keeping Unit - Unique product identifier</p>
                  <p v-if="errors?.sku" class="mt-1 text-sm text-red-600">{{ errors.sku }}</p>
                </div>

                <!-- Short Description -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                  <TiptapEditor v-model="form.short_description" placeholder="Brief product description" />
                  <p class="mt-1 text-xs text-gray-500">Displayed in product listings</p>
                </div>

                <!-- Full Description -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Full Description</label>
                  <TiptapEditor v-model="form.description" placeholder="Detailed product description with formatting..." />
                  <p class="mt-1 text-xs text-gray-500">Complete product details and specifications</p>
                </div>
              </div>
            </div>

            <!-- Images Tab -->
            <div v-show="activeTab === 'images'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-6">Product Images</h3>
              <ImageUploader v-model="images" :maxFiles="10" :maxSize="5" accept="image/*" />
              <p class="mt-4 text-xs text-gray-500">Note: Backend integration for image upload will be implemented in the next phase</p>
            </div>

            <!-- Inventory & Pricing Tab -->
            <div v-show="activeTab === 'inventory'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-6">Pricing</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Price -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Regular Price <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                    <input v-model="form.price" type="number" step="0.01" class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" :class="{ 'border-red-500': errors?.price }" placeholder="0.00" />
                  </div>
                  <p v-if="errors?.price" class="mt-1 text-sm text-red-600">{{ errors.price }}</p>
                </div>

                <!-- Cost -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Cost</label>
                  <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                    <input v-model="form.cost" type="number" step="0.01" class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0.00" />
                  </div>
                  <p class="mt-1 text-xs text-gray-500">Cost per unit (for profit calculation)</p>
                </div>
              </div>

              <div class="border-t border-gray-200 pt-8 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Special Pricing</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <!-- Special Price -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Special Price</label>
                    <div class="relative">
                      <span class="absolute left-3 top-2 text-gray-500">$</span>
                      <input v-model="form.special_price" type="number" step="0.01" class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0.00" />
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Sale price (optional)</p>
                  </div>

                  <!-- From Date -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                    <input v-model="form.special_price_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                  </div>

                  <!-- To Date -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                    <input v-model="form.special_price_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                  </div>
                </div>
              </div>

              <div class="border-t border-gray-200 pt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Inventory</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Quantity -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Quantity <span class="text-red-500">*</span>
                    </label>
                    <input v-model="form.quantity" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" :class="{ 'border-red-500': errors?.quantity }" placeholder="0" />
                    <p class="mt-1 text-xs text-gray-500">Available stock quantity</p>
                    <p v-if="errors?.quantity" class="mt-1 text-sm text-red-600">{{ errors.quantity }}</p>
                  </div>

                  <!-- Weight -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Weight (kg)</label>
                    <input 
                      v-model="form.weight" 
                      type="text" 
                      class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      :class="props.errors?.weight ? 'border-red-500' : 'border-gray-300'"
                      placeholder="0.00" 
                    />
                    <p v-if="props.errors?.weight" class="mt-1 text-sm text-red-600">{{ props.errors.weight }}</p>
                    <p v-else class="mt-1 text-xs text-gray-500">Product weight for shipping</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- SEO Tab -->
            <div v-show="activeTab === 'seo'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-6">Search Engine Optimization</h3>

              <div class="space-y-6">
                <!-- Meta Title -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                  <input v-model="form.meta_title" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Product Name | Your Store" />
                  <p class="mt-1 text-xs text-gray-500">Recommended: 50-60 characters</p>
                </div>

                <!-- Meta Description -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                  <textarea v-model="form.meta_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Brief description for search engines"></textarea>
                  <p class="mt-1 text-xs text-gray-500">Recommended: 150-160 characters</p>
                </div>

                <!-- Meta Keywords -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Meta Keywords</label>
                  <input v-model="form.meta_keywords" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="keyword1, keyword2, keyword3" />
                  <p class="mt-1 text-xs text-gray-500">Comma-separated keywords</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar (1/3 width) -->
          <div class="lg:col-span-1 space-y-6">
            <!-- Publish Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-6">Publish</h3>

              <div class="space-y-4">
                <!-- Status -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                  <select v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="enabled">Enabled</option>
                    <option value="disabled">Disabled</option>
                  </select>
                </div>

                <!-- Visibility -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Visibility</label>
                  <select v-model="form.visibility" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="both">Catalog & Search</option>
                    <option value="catalog">Catalog Only</option>
                    <option value="search">Search Only</option>
                    <option value="none">Not Visible</option>
                  </select>
                </div>

                <!-- Featured -->
                <div class="flex items-center">
                  <input v-model="form.featured" type="checkbox" id="featured" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                  <label for="featured" class="ml-2 block text-sm text-gray-900">Featured Product</label>
                </div>

                <!-- New -->
                <div class="flex items-center">
                  <input v-model="form.new" type="checkbox" id="new" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                  <label for="new" class="ml-2 block text-sm text-gray-900">Mark as New</label>
                </div>
              </div>

              <div class="border-t border-gray-200 mt-6 pt-6 space-y-3">
                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Publish Product
                </button>
                <button type="button" @click="saveDraft" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                  </svg>
                  Save as Draft
                </button>
              </div>
            </div>

            <!-- Categories Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Categories</h3>

              <div v-if="errors?.category_ids" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                <p class="text-sm text-red-600">{{ errors.category_ids }}</p>
              </div>

              <div class="max-h-80 overflow-y-auto space-y-2">
                <div v-for="category in flatCategories" :key="category.id" class="flex items-center">
                  <input :id="`category_${category.id}`" type="checkbox" :checked="form.category_ids.includes(category.id)" @change="toggleCategory(category.id)" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                  <label :for="`category_${category.id}`" class="ml-2 block text-sm text-gray-900">{{ category.name }}</label>
                </div>
              </div>

              <div v-if="flatCategories.length === 0" class="text-center py-8 text-gray-500">
                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-2 text-sm">No categories available</p>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

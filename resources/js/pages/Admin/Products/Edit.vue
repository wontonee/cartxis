<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TiptapEditor from '@/Components/Admin/TiptapEditor.vue';
import ImageUploader from '@/Components/Admin/ImageUploader.vue';
import ProductVariantEditor from '@/Components/Admin/ProductVariantEditor.vue';
import InventoryTracker from '@/Components/Admin/InventoryTracker.vue';
import * as productRoutes from '@/routes/admin/catalog/products';
import axios from '@/lib/axios';

interface Category {
  id: number;
  name: string;
  parent_id: number | null;
  children?: Category[];
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
  meta_title: string | null;
  meta_description: string | null;
  meta_keywords: string | null;
  categories: Array<{ id: number; name: string }>;
  main_image_id: number | null;
  images: Array<{
    id: number;
    path: string;
    thumbnail_path: string;
    alt_text: string | null;
    position: number;
  }>;
}

interface Props {
  product: Product;
  categories: Category[];
  errors?: Record<string, string>;
}

const props = defineProps<Props>();

// Active tab
const activeTab = ref<'general' | 'images' | 'variants' | 'inventory' | 'seo'>('general');

// Images for upload
const images = ref<File[]>([]);
const isUploading = ref(false);

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
  category_ids: props.product.categories.map(cat => cat.id),
  meta_title: props.product.meta_title || '',
  meta_description: props.product.meta_description || '',
  meta_keywords: props.product.meta_keywords || '',
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
const submitForm = () => {
  router.put(productRoutes.update({ product: props.product.id }), form.value);
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
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl md:text-3xl text-gray-800 font-bold">Edit Product</h1>
            <p class="text-sm text-gray-600 mt-1">Update product information</p>
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

      <!-- Main Content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Column (2/3) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Tabs Navigation -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6 border-b border-gray-200" aria-label="Tabs">
              <button
                @click="activeTab = 'general'"
                :class="[
                  activeTab === 'general'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                General
              </button>
              <button
                @click="activeTab = 'images'"
                :class="[
                  activeTab === 'images'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Images
              </button>
              <button
                @click="activeTab = 'variants'"
                :class="[
                  activeTab === 'variants'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Variants
              </button>
              <button
                @click="activeTab = 'inventory'"
                :class="[
                  activeTab === 'inventory'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Inventory
              </button>
              <button
                @click="activeTab = 'seo'"
                :class="[
                  activeTab === 'seo'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                SEO
              </button>
            </nav>

            <!-- Tab Content -->
            <div class="p-6">
              <!-- General Tab -->
              <div v-show="activeTab === 'general'" class="space-y-6">
                <!-- Product Name -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Product Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="name"
                    v-model="form.name"
                    @input="generateSlug"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors?.name }"
                    placeholder="Enter product name"
                  />
                  <p v-if="errors?.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                </div>

                <!-- Slug -->
                <div>
                  <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">
                    Slug <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="slug"
                    v-model="form.slug"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors?.slug }"
                    placeholder="auto-generated-from-name"
                  />
                  <p v-if="errors?.slug" class="mt-1 text-sm text-red-600">{{ errors.slug }}</p>
                  <p class="mt-1 text-xs text-gray-500">Auto-generated from product name</p>
                </div>

                <!-- SKU -->
                <div>
                  <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">
                    SKU <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="sku"
                    v-model="form.sku"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors?.sku }"
                    placeholder="Enter SKU"
                  />
                  <p v-if="errors?.sku" class="mt-1 text-sm text-red-600">{{ errors.sku }}</p>
                </div>

                <!-- Short Description -->
                <div>
                  <label for="short_description" class="block text-sm font-medium text-gray-700 mb-1">
                    Short Description
                  </label>
                  <TiptapEditor v-model="form.short_description" placeholder="Brief product description" />
                </div>

                <!-- Description -->
                <div>
                  <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description
                  </label>
                  <TiptapEditor v-model="form.description" placeholder="Detailed product description with formatting..." />
                </div>
              </div>

              <!-- Images Tab -->
              <div v-show="activeTab === 'images'" class="space-y-6">
                <!-- Existing Images -->
                <div v-if="product.images && product.images.length > 0">
                  <h3 class="text-sm font-medium text-gray-700 mb-3">Current Images</h3>
                  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
                    <div
                      v-for="image in product.images"
                      :key="image.id"
                      class="space-y-2"
                    >
                      <!-- Image Container -->
                      <div class="aspect-square rounded-lg overflow-hidden bg-gray-100 border-2 border-gray-200"
                           :class="{ 'border-blue-500': product.main_image_id === image.id }">
                        <div class="w-full h-full group relative">
                          <img
                            :src="`/storage/${image.path}`"
                            :alt="image.alt_text || product.name"
                            class="w-full h-full object-cover block"
                          />
                          
                          <!-- Main Image Badge -->
                          <div v-if="product.main_image_id === image.id" class="absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded z-20">
                            Main
                          </div>

                          <!-- Actions Overlay -->
                          <div class="absolute inset-0 bg-opacity-0 group-hover:bg-black group-hover:bg-opacity-50 transition-all duration-200 flex items-center justify-center gap-2">
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
                  <h3 class="text-sm font-medium text-gray-700 mb-3">Upload New Images</h3>
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

              <!-- Variants Tab -->
              <div v-show="activeTab === 'variants'">
                <ProductVariantEditor
                  :product-id="product.id"
                  :base-price="product.price"
                  :base-sku="product.sku"
                  :configurable_attributes="[]"
                  :existing_variants="[]"
                />
              </div>

              <!-- Inventory Tab -->
              <div v-show="activeTab === 'inventory'">
                <InventoryTracker
                  :product-id="product.id"
                  :current-stock="product.quantity"
                  :min-quantity="1"
                  :notify-stock-qty="5"
                  :manage-stock="product.manage_stock"
                  :warehouses="[]"
                  :adjustment-history="[]"
                  @update:stock="(data) => form.quantity = data.quantity.toString()"
                />
                
                <!-- Pricing Section (moved from old inventory tab) -->
                <div class="mt-6 bg-white rounded-lg shadow p-6 space-y-6">
                  <h3 class="text-lg font-semibold text-gray-900">Pricing</h3>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                        Price <span class="text-red-500">*</span>
                      </label>
                      <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                        <input
                          id="price"
                          v-model="form.price"
                          type="number"
                          step="0.01"
                          class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          :class="{ 'border-red-500': errors?.price }"
                        />
                      </div>
                      <p v-if="errors?.price" class="mt-1 text-sm text-red-600">{{ errors.price }}</p>
                    </div>

                    <div>
                      <label for="cost" class="block text-sm font-medium text-gray-700 mb-1">
                        Cost
                      </label>
                      <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                        <input
                          id="cost"
                          v-model="form.cost"
                          type="number"
                          step="0.01"
                          class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Special Pricing -->
                  <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Special Pricing</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                      <div>
                        <label for="special_price" class="block text-sm font-medium text-gray-700 mb-1">
                          Special Price
                        </label>
                        <div class="relative">
                          <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                          <input
                            id="special_price"
                            v-model="form.special_price"
                            type="number"
                            step="0.01"
                            class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          />
                        </div>
                      </div>

                      <div>
                        <label for="special_price_from" class="block text-sm font-medium text-gray-700 mb-1">
                          From Date
                        </label>
                        <input
                          id="special_price_from"
                          v-model="form.special_price_from"
                          type="date"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>

                      <div>
                        <label for="special_price_to" class="block text-sm font-medium text-gray-700 mb-1">
                          To Date
                        </label>
                        <input
                          id="special_price_to"
                          v-model="form.special_price_to"
                          type="date"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Weight -->
                  <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Physical Attributes</h4>
                    <div>
                      <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">
                        Weight (kg)
                      </label>
                      <input
                        id="weight"
                        v-model="form.weight"
                        type="number"
                        step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- SEO Tab -->
              <div v-show="activeTab === 'seo'" class="space-y-6">
                <div>
                  <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-1">
                    Meta Title
                  </label>
                  <input
                    id="meta_title"
                    v-model="form.meta_title"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="SEO title for search engines"
                  />
                  <p class="mt-1 text-xs text-gray-500">Recommended: 50-60 characters</p>
                </div>

                <div>
                  <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">
                    Meta Description
                  </label>
                  <textarea
                    id="meta_description"
                    v-model="form.meta_description"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="SEO description for search engines"
                  />
                  <p class="mt-1 text-xs text-gray-500">Recommended: 150-160 characters</p>
                </div>

                <div>
                  <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-1">
                    Meta Keywords
                  </label>
                  <input
                    id="meta_keywords"
                    v-model="form.meta_keywords"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="keyword1, keyword2, keyword3"
                  />
                  <p class="mt-1 text-xs text-gray-500">Separate keywords with commas</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar (1/3) -->
        <div class="space-y-6">
          <!-- Publish Card -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Publish</h3>
            
            <!-- Status -->
            <div class="mb-4">
              <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                Status
              </label>
              <select
                id="status"
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="enabled">Enabled</option>
                <option value="disabled">Disabled</option>
              </select>
            </div>

            <!-- Visibility -->
            <div class="mb-4">
              <label for="visibility" class="block text-sm font-medium text-gray-700 mb-2">
                Visibility
              </label>
              <select
                id="visibility"
                v-model="form.visibility"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <span class="ml-2 text-sm text-gray-700">Featured Product</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="form.new"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <span class="ml-2 text-sm text-gray-700">Mark as New</span>
              </label>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2">
              <button
                @click="submitForm"
                type="button"
                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Update Product
              </button>
              <button
                @click="showDeleteModal = true"
                type="button"
                class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              >
                Delete Product
              </button>
            </div>
          </div>

          <!-- Categories Card -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Categories</h3>
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
                  class="mt-0.5 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <span class="ml-2 text-sm text-gray-700">{{ category.name }}</span>
              </label>
            </div>
            <p v-if="errors?.category_ids" class="mt-2 text-sm text-red-600">{{ errors.category_ids }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50"
      @click="showDeleteModal = false"
    >
      <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div
            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
            @click.stop
          >
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                  <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                  </svg>
                </div>
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                  <h3 class="text-base font-semibold leading-6 text-gray-900">Delete Product</h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to delete "{{ product.name }}"? This action cannot be undone.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
              <button
                @click="deleteProduct"
                :disabled="isDeleting"
                type="button"
                class="cursor-pointer inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="isDeleting">Deleting...</span>
                <span v-else>Delete</span>
              </button>
              <button
                @click="showDeleteModal = false"
                type="button"
                class="cursor-pointer mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Image Modal -->
    <div
      v-if="showDeleteImageModal"
      class="relative z-50"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

      <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div
            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
            @click.stop
          >
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                  <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                  </svg>
                </div>
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                  <h3 class="text-base font-semibold leading-6 text-gray-900">Delete Image</h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to delete this image? This action cannot be undone.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
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
                class="cursor-pointer mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
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


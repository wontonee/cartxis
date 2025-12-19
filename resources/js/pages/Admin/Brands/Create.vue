<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ImageUploader from '@/Components/Admin/ImageUploader.vue';
import { ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import * as brandRoutes from '@/routes/admin/catalog/brands';

const activeTab = ref<'general' | 'seo'>('general');

// Image upload handling (separate from form data)
const images = ref<File[]>([]);

const form = useForm({
  name: '',
  slug: '',
  description: '',
  website: '',
  status: true,
  is_featured: false,
  meta_title: '',
  meta_description: '',
  meta_keywords: '',
});

// Auto-generate slug from name
const generateSlug = () => {
  form.slug = form.name
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
};

const submit = () => {
  // If there's an image to upload, use FormData with axios
  if (images.value.length > 0) {
    const formData = new FormData();
    
    // Add all form fields
    formData.append('name', form.name);
    formData.append('slug', form.slug);
    formData.append('description', form.description);
    formData.append('website', form.website);
    formData.append('status', form.status ? '1' : '0');
    formData.append('is_featured', form.is_featured ? '1' : '0');
    formData.append('meta_title', form.meta_title);
    formData.append('meta_description', form.meta_description);
    formData.append('meta_keywords', form.meta_keywords);
    formData.append('logo', images.value[0]);
    
    // Use axios for file upload
    form.processing = true;
    form.clearErrors();
    
    axios.post(brandRoutes.store().url, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    .then(() => {
      form.processing = false;
      form.reset();
      images.value = [];
      // Redirect to index with flash message
      router.visit(brandRoutes.index().url, {
        onSuccess: () => {
          // Flash message will be shown automatically by Toast component
        }
      });
    })
    .catch(error => {
      form.processing = false;
      if (error.response?.data?.errors) {
        form.setError(error.response.data.errors);
        console.error('Validation errors:', error.response.data.errors);
      } else {
        console.error('Error response:', error.response?.data);
      }
    });
  } else {
    // No image, use normal Inertia form submit
    form.post(brandRoutes.store().url);
  }
};
</script>

<template>
  <Head title="Create Brand" />
  <AdminLayout title="Create Brand">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl md:text-3xl text-gray-800 font-bold">Create Brand</h1>
            <p class="text-sm text-gray-600 mt-1">Add a new brand to your catalog</p>
          </div>
          <Link 
            :href="brandRoutes.index().url"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Brands
          </Link>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Content (2/3) -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
              <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                  <button
                    type="button"
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
                    type="button"
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
              </div>

              <!-- Tab Content -->
              <div class="p-6">
                <!-- General Tab -->
                <div v-show="activeTab === 'general'" class="space-y-6">
                  <!-- Name -->
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                      Brand Name <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="name"
                      v-model="form.name"
                      @input="generateSlug"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      :class="{ 'border-red-500': form.errors?.name }"
                      placeholder="Enter brand name"
                    />
                    <p v-if="form.errors?.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                  </div>

                  <!-- Slug -->
                  <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">
                      Slug
                    </label>
                    <input
                      id="slug"
                      v-model="form.slug"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      :class="{ 'border-red-500': form.errors?.slug }"
                      placeholder="auto-generated-from-name"
                    />
                    <p v-if="form.errors?.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                    <p class="mt-1 text-xs text-gray-500">URL-friendly version of the name (auto-generated if left empty)</p>
                  </div>

                  <!-- Description -->
                  <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                      Description
                    </label>
                    <textarea
                      id="description"
                      v-model="form.description"
                      rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      :class="{ 'border-red-500': form.errors?.description }"
                      placeholder="Brand description..."
                    />
                    <p v-if="form.errors?.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                  </div>

                  <!-- Website -->
                  <div>
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-1">
                      Website URL
                    </label>
                    <input
                      id="website"
                      v-model="form.website"
                      type="url"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      :class="{ 'border-red-500': form.errors?.website }"
                      placeholder="https://example.com"
                    />
                    <p v-if="form.errors?.website" class="mt-1 text-sm text-red-600">{{ form.errors.website }}</p>
                    <p class="mt-1 text-xs text-gray-500">Full URL including https://</p>
                  </div>

                  <!-- Logo Upload with Dropzone -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Brand Logo
                    </label>
                    <ImageUploader 
                      v-model="images" 
                      :maxFiles="1" 
                      :maxSize="5" 
                      accept="image/*"
                    />
                    <p v-if="images.length > 0" class="mt-2 text-xs text-green-600">
                      ✓ {{ images.length }} logo selected (will be uploaded on save)
                    </p>
                    <p v-if="form.errors?.logo" class="mt-2 text-sm text-red-600">{{ form.errors.logo }}</p>
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
                      maxlength="255"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      :class="{ 'border-red-500': form.errors?.meta_title }"
                      placeholder="SEO title for search engines"
                    />
                    <p v-if="form.errors?.meta_title" class="mt-1 text-sm text-red-600">{{ form.errors.meta_title }}</p>
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
                      :class="{ 'border-red-500': form.errors?.meta_description }"
                      placeholder="SEO description for search engines"
                    />
                    <p v-if="form.errors?.meta_description" class="mt-1 text-sm text-red-600">{{ form.errors.meta_description }}</p>
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
                      :class="{ 'border-red-500': form.errors?.meta_keywords }"
                      placeholder="keyword1, keyword2, keyword3"
                    />
                    <p v-if="form.errors?.meta_keywords" class="mt-1 text-sm text-red-600">{{ form.errors.meta_keywords }}</p>
                    <p class="mt-1 text-xs text-gray-500">Separate keywords with commas</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar (1/3) -->
          <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-sm font-medium text-gray-900 mb-4">Status</h3>
              
              <div class="space-y-4">
                <!-- Active Status -->
                <div class="flex items-center">
                  <input
                    type="checkbox"
                    id="status"
                    v-model="form.status"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="status" class="ml-2 block text-sm text-gray-900">Active</label>
                </div>

                <!-- Featured -->
                <div class="flex items-center">
                  <input
                    type="checkbox"
                    id="is_featured"
                    v-model="form.is_featured"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="is_featured" class="ml-2 block text-sm text-gray-900">Featured Brand</label>
                </div>
              </div>
            </div>

            <!-- Actions Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-sm font-medium text-gray-900 mb-4">Actions</h3>
              
              <div class="space-y-3">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="w-full inline-flex justify-center items-center bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ form.processing ? 'Creating...' : 'Create Brand' }}
                </button>
                
                <Link
                  :href="brandRoutes.index().url"
                  class="w-full inline-block text-center border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
                >
                  Cancel
                </Link>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

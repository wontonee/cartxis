<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';
import * as categoryRoutes from '@/routes/admin/catalog/categories';

interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
  image?: string;
  status: boolean;
  show_in_menu: boolean;
  sort_order: number;
  parent_id?: number;
  meta_title?: string;
  meta_description?: string;
  meta_keywords?: string;
}

interface ParentCategory {
  id: number;
  name: string;
}

interface Props {
  category: Category;
  parentCategories: ParentCategory[];
}

const props = defineProps<Props>();

const activeTab = ref<'general' | 'seo'>('general');
const showDeleteModal = ref(false);

const form = useForm({
  name: props.category.name,
  slug: props.category.slug,
  description: props.category.description || '',
  parent_id: props.category.parent_id || null,
  status: props.category.status,
  show_in_menu: props.category.show_in_menu,
  sort_order: props.category.sort_order,
  image: props.category.image || '',
  meta_title: props.category.meta_title || '',
  meta_description: props.category.meta_description || '',
  meta_keywords: props.category.meta_keywords || '',
});

const originalSlug = props.category.slug;

// Auto-generate slug from name (only if slug was auto-generated before)
watch(() => form.name, (newName) => {
  if (newName && form.slug === originalSlug) {
    form.slug = newName
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }
});

const submit = () => {
  form.put(categoryRoutes.update(props.category.id).url, {
    preserveScroll: true,
    onSuccess: () => {
      // Success toast will be shown via flash message from backend
    },
    onError: (errors) => {
      console.error('Validation errors:', errors);
    },
  });
};

const deleteCategory = () => {
  form.delete(categoryRoutes.destroy(props.category.id).url, {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      // Success toast will be shown via flash message from backend
    },
    onError: (errors) => {
      console.error('Delete error:', errors);
    },
  });
};
</script>

<template>
  <AdminLayout title="Edit Category">
    <Head :title="`Edit ${category.name}`" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-semibold text-gray-900">Edit Category</h1>
              <p class="mt-1 text-sm text-gray-600">Update category information and settings</p>
            </div>
            <div class="flex items-center space-x-3">
              <button
                type="button"
                @click="showDeleteModal = true"
                class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete Category
              </button>
              <Link
                :href="categoryRoutes.index().url"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Categories
              </Link>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit">
          <div class="bg-white rounded-lg shadow">
            <!-- Tabs -->
            <div class="border-b border-gray-200">
              <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button
                  type="button"
                  @click="activeTab = 'general'"
                  :class="[
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                    activeTab === 'general'
                      ? 'border-blue-500 text-blue-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]"
                >
                  General Information
                </button>
                <button
                  type="button"
                  @click="activeTab = 'seo'"
                  :class="[
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                    activeTab === 'seo'
                      ? 'border-blue-500 text-blue-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]"
                >
                  SEO Settings
                </button>
              </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
              <!-- General Tab -->
              <div v-show="activeTab === 'general'" class="space-y-6">
                <!-- Name -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">
                    Category Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': form.errors.name }"
                    placeholder="e.g., Electronics, Clothing, Books"
                    required
                  />
                  <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <!-- Slug -->
                <div>
                  <label for="slug" class="block text-sm font-medium text-gray-700">
                    URL Slug
                  </label>
                  <input
                    id="slug"
                    v-model="form.slug"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': form.errors.slug }"
                    placeholder="auto-generated-from-name"
                  />
                  <p class="mt-1 text-sm text-gray-500">URL-friendly version of the category name</p>
                  <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                </div>

                <!-- Description -->
                <div>
                  <label for="description" class="block text-sm font-medium text-gray-700">
                    Description
                  </label>
                  <textarea
                    id="description"
                    v-model="form.description"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': form.errors.description }"
                    placeholder="Brief description of this category"
                  ></textarea>
                  <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                </div>

                <!-- Parent Category -->
                <div>
                  <label for="parent_id" class="block text-sm font-medium text-gray-700">
                    Parent Category
                  </label>
                  <select
                    id="parent_id"
                    v-model="form.parent_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': form.errors.parent_id }"
                  >
                    <option :value="null">None (Root Category)</option>
                    <option v-for="parent in parentCategories" :key="parent.id" :value="parent.id">
                      {{ parent.name }}
                    </option>
                  </select>
                  <p class="mt-1 text-sm text-gray-500">Select a parent to make this a sub-category</p>
                  <p v-if="form.errors.parent_id" class="mt-1 text-sm text-red-600">{{ form.errors.parent_id }}</p>
                </div>

                <!-- Image Upload Placeholder -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Category Image</label>
                  <div v-if="category.image" class="mt-2 mb-4">
                    <img :src="category.image" alt="Category" class="h-32 w-32 object-cover rounded-md" />
                  </div>
                  <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                    <div class="space-y-1 text-center">
                      <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      <div class="flex text-sm text-gray-600">
                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                          <span>Upload a file</span>
                          <input type="file" class="sr-only" accept="image/*" disabled />
                        </label>
                        <p class="pl-1">or drag and drop</p>
                      </div>
                      <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                      <p class="text-xs text-gray-500 italic">(Image upload coming soon)</p>
                    </div>
                  </div>
                  <p v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</p>
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
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': form.errors.sort_order }"
                    placeholder="0"
                    min="0"
                  />
                  <p class="mt-1 text-sm text-gray-500">Lower numbers appear first</p>
                  <p v-if="form.errors.sort_order" class="mt-1 text-sm text-red-600">{{ form.errors.sort_order }}</p>
                </div>

                <!-- Status Toggle -->
                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input
                      id="status"
                      v-model="form.status"
                      type="checkbox"
                      class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      :class="{ 'border-red-500': form.errors.status }"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="status" class="font-medium text-gray-700">Active Status</label>
                    <p class="text-gray-500">This category is enabled and visible on the storefront</p>
                    <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
                  </div>
                </div>

                <!-- Show in Menu Toggle -->
                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input
                      id="show_in_menu"
                      v-model="form.show_in_menu"
                      type="checkbox"
                      class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      :class="{ 'border-red-500': form.errors.show_in_menu }"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="show_in_menu" class="font-medium text-gray-700">Show in Navigation Menu</label>
                    <p class="text-gray-500">Display this category in the main navigation menu</p>
                    <p v-if="form.errors.show_in_menu" class="mt-1 text-sm text-red-600">{{ form.errors.show_in_menu }}</p>
                  </div>
                </div>
              </div>

              <!-- SEO Tab -->
              <div v-show="activeTab === 'seo'" class="space-y-6">
                <!-- Meta Title -->
                <div>
                  <label for="meta_title" class="block text-sm font-medium text-gray-700">
                    Meta Title
                  </label>
                  <input
                    id="meta_title"
                    v-model="form.meta_title"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': form.errors.meta_title }"
                    placeholder="SEO-optimized title for search engines"
                    maxlength="60"
                  />
                  <p class="mt-1 text-sm text-gray-500">
                    Recommended: 50-60 characters. {{ form.meta_title.length }}/60
                  </p>
                  <p v-if="form.errors.meta_title" class="mt-1 text-sm text-red-600">{{ form.errors.meta_title }}</p>
                </div>

                <!-- Meta Description -->
                <div>
                  <label for="meta_description" class="block text-sm font-medium text-gray-700">
                    Meta Description
                  </label>
                  <textarea
                    id="meta_description"
                    v-model="form.meta_description"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': form.errors.meta_description }"
                    placeholder="Brief description for search engine results"
                    maxlength="160"
                  ></textarea>
                  <p class="mt-1 text-sm text-gray-500">
                    Recommended: 150-160 characters. {{ form.meta_description.length }}/160
                  </p>
                  <p v-if="form.errors.meta_description" class="mt-1 text-sm text-red-600">{{ form.errors.meta_description }}</p>
                </div>

                <!-- Meta Keywords -->
                <div>
                  <label for="meta_keywords" class="block text-sm font-medium text-gray-700">
                    Meta Keywords
                  </label>
                  <input
                    id="meta_keywords"
                    v-model="form.meta_keywords"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': form.errors.meta_keywords }"
                    placeholder="keyword1, keyword2, keyword3"
                  />
                  <p class="mt-1 text-sm text-gray-500">Comma-separated keywords (less important for modern SEO)</p>
                  <p v-if="form.errors.meta_keywords" class="mt-1 text-sm text-red-600">{{ form.errors.meta_keywords }}</p>
                </div>

                <!-- SEO Preview -->
                <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                  <h4 class="text-sm font-medium text-gray-900 mb-2">Search Engine Preview</h4>
                  <div class="space-y-1">
                    <div class="text-blue-600 text-lg">{{ form.meta_title || form.name || 'Category Title' }}</div>
                    <div class="text-green-700 text-sm">yourstore.com/category/{{ form.slug || 'category-slug' }}</div>
                    <div class="text-gray-600 text-sm">{{ form.meta_description || form.description || 'Category description will appear here...' }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 border-t border-gray-200">
              <Link
                :href="categoryRoutes.index().url"
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
                {{ form.processing ? 'Updating...' : 'Update Category' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeleteModal = false"></div>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
              <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Category</h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">
                  Are you sure you want to delete "{{ category.name }}"? This action cannot be undone.
                </p>
                <p v-if="form.errors.error" class="mt-2 text-sm text-red-600">{{ form.errors.error }}</p>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
            <button
              type="button"
              @click="deleteCategory"
              :disabled="form.processing"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
            >
              {{ form.processing ? 'Deleting...' : 'Delete' }}
            </button>
            <button
              type="button"
              @click="showDeleteModal = false"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import * as categoryRoutes from '@/routes/admin/catalog/categories';
import * as productRoutes from '@/routes/admin/catalog/products';
import { useCurrency } from '@/composables/useCurrency';

const { formatPrice } = useCurrency();

interface Product {
  id: number;
  name: string;
  sku: string;
  price: string;
  status: string;
}

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
  parent?: {
    id: number;
    name: string;
  };
  children?: Category[];
  products?: Product[];
  meta_title?: string;
  meta_description?: string;
  meta_keywords?: string;
  created_at: string;
  updated_at: string;
}

interface Props {
  category: Category;
}

const props = defineProps<Props>();
</script>

<template>
  <AdminLayout title="Category Details">
    <Head :title="category.name" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-semibold text-gray-900">{{ category.name }}</h1>
              <p class="mt-1 text-sm text-gray-600">Category details and relationships</p>
            </div>
            <div class="flex items-center space-x-3">
              <Link
                :href="categoryRoutes.edit(category.id).url"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Category
              </Link>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Info -->
          <div class="lg:col-span-2 space-y-6">
            <!-- General Information -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">General Information</h2>
              </div>
              <div class="px-6 py-4">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ category.name }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Slug</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ category.slug }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                      <span :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        category.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                      ]">
                        {{ category.status ? 'Active' : 'Inactive' }}
                      </span>
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Show in Menu</dt>
                    <dd class="mt-1">
                      <span :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        category.show_in_menu ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'
                      ]">
                        {{ category.show_in_menu ? 'Yes' : 'No' }}
                      </span>
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Sort Order</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ category.sort_order }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Parent Category</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      <Link
                        v-if="category.parent"
                        :href="categoryRoutes.show(category.parent.id).url"
                        class="text-blue-600 hover:text-blue-800"
                      >
                        {{ category.parent.name }}
                      </Link>
                      <span v-else class="text-gray-400">None (Root Category)</span>
                    </dd>
                  </div>
                  <div class="sm:col-span-2" v-if="category.description">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ category.description }}</dd>
                  </div>
                  <div class="sm:col-span-2" v-if="category.image">
                    <dt class="text-sm font-medium text-gray-500 mb-2">Image</dt>
                    <dd>
                      <img :src="category.image" :alt="category.name" class="h-48 w-48 object-cover rounded-lg" />
                    </dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- SEO Information -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">SEO Settings</h2>
              </div>
              <div class="px-6 py-4">
                <dl class="space-y-4">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Meta Title</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ category.meta_title || '-' }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Meta Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ category.meta_description || '-' }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Meta Keywords</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ category.meta_keywords || '-' }}</dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- Subcategories -->
            <div v-if="category.children && category.children.length > 0" class="bg-white shadow rounded-lg overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Subcategories ({{ category.children.length }})</h2>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort Order</th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="child in category.children" :key="child.id">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ child.name }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="[
                          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                          child.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        ]">
                          {{ child.status ? 'Active' : 'Inactive' }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ child.sort_order }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <Link
                          :href="categoryRoutes.show(child.id).url"
                          class="text-blue-600 hover:text-blue-900 mr-4"
                        >
                          View
                        </Link>
                        <Link
                          :href="categoryRoutes.edit(child.id).url"
                          class="text-indigo-600 hover:text-indigo-900"
                        >
                          Edit
                        </Link>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Products -->
            <div v-if="category.products && category.products.length > 0" class="bg-white shadow rounded-lg overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Products in this Category ({{ category.products.length }})</h2>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="product in category.products" :key="product.id">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ product.name }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ product.sku }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ formatPrice(product.price) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="[
                          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                          product.status === 'enabled' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        ]">
                          {{ product.status }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <Link
                          :href="productRoutes.edit(product.id).url"
                          class="text-indigo-600 hover:text-indigo-900"
                        >
                          Edit
                        </Link>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Metadata -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Metadata</h2>
              </div>
              <div class="px-6 py-4">
                <dl class="space-y-4">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">#{{ category.id }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ new Date(category.created_at).toLocaleDateString() }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ new Date(category.updated_at).toLocaleDateString() }}</dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Quick Stats</h2>
              </div>
              <div class="px-6 py-4">
                <dl class="space-y-4">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Subcategories</dt>
                    <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ category.children?.length || 0 }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Products</dt>
                    <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ category.products?.length || 0 }}</dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Quick Actions</h2>
              </div>
              <div class="px-6 py-4 space-y-3">
                <Link
                  :href="categoryRoutes.create({ query: { parent_id: category.id } }).url"
                  class="block w-full text-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Add Subcategory
                </Link>
                <Link
                  :href="productRoutes.create({ query: { category_id: category.id } }).url"
                  class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Add Product
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

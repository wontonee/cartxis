<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';
import Pagination from '@/components/Admin/Pagination.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import * as brandRoutes from '@/routes/admin/catalog/brands';

interface Brand {
  id: number;
  name: string;
  slug: string;
  description?: string;
  logo?: string;
  website?: string;
  status: boolean;
  is_featured: boolean;
  sort_order: number;
  products_count?: number;
  created_at: string;
  updated_at: string;
}

interface Props {
  brands: {
    data: Brand[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
  };
  filters: {
    search?: string;
    status?: string;
    is_featured?: string;
    sort_by?: string;
    sort_order?: string;
  };
}

const props = defineProps<Props>();

const selectedBrands = ref<number[]>([]);
const showDeleteModal = ref(false);
const deleteBrandId = ref<number | null>(null);
const deletingBrand = ref<{ id: number; name: string } | null>(null);
const showBulkDeleteModal = ref(false);

// Local filter state
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const featuredFilter = ref(props.filters.is_featured || '');
const sortBy = ref(props.filters.sort_by || 'name');
const sortOrder = ref(props.filters.sort_order || 'asc');

// Computed
const allSelected = computed(() => {
  return props.brands.data.length > 0 && 
         selectedBrands.value.length === props.brands.data.length;
});

const someSelected = computed(() => {
  return selectedBrands.value.length > 0 && 
         selectedBrands.value.length < props.brands.data.length;
});

// Debounced search
const performSearch = debounce(() => {
  applyFilters();
}, 300);

// Methods
function toggleSelectAll() {
  if (allSelected.value) {
    selectedBrands.value = [];
  } else {
    selectedBrands.value = props.brands.data.map(b => b.id);
  }
}

function applyFilters() {
  router.get(brandRoutes.index().url, {
    search: searchQuery.value || undefined,
    status: statusFilter.value || undefined,
    is_featured: featuredFilter.value || undefined,
    sort_by: sortBy.value,
    sort_order: sortOrder.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
}

function clearFilters() {
  search.value = '';
  statusFilter.value = '';
  featuredFilter.value = '';
  sortBy.value = 'name';
  sortOrder.value = 'asc';
  applyFilters();
}

function sortTable(column: string) {
  if (sortBy.value === column) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortOrder.value = 'asc';
  }
  applyFilters();
}

function confirmDelete(brandId: number) {
  const brand = props.brands.data.find(b => b.id === brandId);
  if (brand) {
    deletingBrand.value = { id: brand.id, name: brand.name };
    showDeleteModal.value = true;
  }
}

function deleteBrand() {
  if (deletingBrand.value) {
    router.delete(brandRoutes.destroy(deletingBrand.value.id).url, {
      onSuccess: () => {
        showDeleteModal.value = false;
        deletingBrand.value = null;
      },
    });
  }
}

function confirmBulkDelete() {
  if (selectedBrands.value.length > 0) {
    showBulkDeleteModal.value = true;
  }
}

function bulkDelete() {
  router.post(brandRoutes.bulkDestroy().url, {
    ids: selectedBrands.value,
  }, {
    onSuccess: () => {
      showBulkDeleteModal.value = false;
      selectedBrands.value = [];
    },
  });
}

function bulkUpdateStatus(status: boolean) {
  if (selectedBrands.value.length > 0) {
    router.post(brandRoutes.bulkStatus().url, {
      ids: selectedBrands.value,
      status: status,
    }, {
      onSuccess: () => {
        selectedBrands.value = [];
      },
    });
  }
}

function changePage(page: number) {
  router.get(brandRoutes.index().url, {
    ...props.filters,
    page,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
}
</script>

<template>
  <Head title="Brands" />

  <AdminLayout title="Brands">
    <div class="p-6 space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Brands</h1>
          <p class="mt-1 text-sm text-gray-600">Manage your product brands</p>
        </div>
        <Link
          :href="brandRoutes.create().url"
          class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Brand
        </Link>
      </div>

      <!-- Filters Card -->
      <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="search"
              @input="performSearch"
              type="text"
              placeholder="Search brands..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="statusFilter"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Status</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>

          <!-- Featured Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Featured</label>
            <select
              v-model="featuredFilter"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Brands</option>
              <option value="1">Featured</option>
              <option value="0">Not Featured</option>
            </select>
          </div>

          <!-- Clear Filters -->
          <div class="flex items-end">
            <button
              @click="clearFilters"
              class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Bulk Actions -->
      <div v-if="selectedBrands.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-blue-900">
            {{ selectedBrands.length }} brand(s) selected
          </span>
          <div class="flex gap-2">
            <button
              @click="bulkUpdateStatus(true)"
              class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded hover:bg-green-700"
            >
              Activate
            </button>
            <button
              @click="bulkUpdateStatus(false)"
              class="px-3 py-1.5 text-sm font-medium text-white bg-gray-600 rounded hover:bg-gray-700"
            >
              Deactivate
            </button>
            <button
              @click="confirmBulkDelete"
              class="px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded hover:bg-red-700"
            >
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="w-12 px-6 py-3">
                  <input
                    type="checkbox"
                    :checked="allSelected"
                    :indeterminate="someSelected"
                    @change="toggleSelectAll"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600"
                  />
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortTable('name')">
                  <div class="flex items-center gap-1">
                    Name
                    <svg v-if="sortBy === 'name'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path v-if="sortOrder === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                      <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Website
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortTable('status')">
                  <div class="flex items-center gap-1">
                    Status
                    <svg v-if="sortBy === 'status'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path v-if="sortOrder === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                      <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Featured
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Products
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="brand in brands.data" :key="brand.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    :value="brand.id"
                    v-model="selectedBrands"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div v-if="brand.logo" class="h-10 w-10 flex-shrink-0">
                      <img class="h-10 w-10 rounded object-cover" :src="`/storage/${brand.logo}`" :alt="brand.name" />
                    </div>
                    <div :class="brand.logo ? 'ml-4' : ''">
                      <div class="text-sm font-medium text-gray-900">{{ brand.name }}</div>
                      <div class="text-sm text-gray-500">{{ brand.slug }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <a v-if="brand.website" :href="brand.website" target="_blank" class="text-sm text-blue-600 hover:text-blue-800">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Visit
                  </a>
                  <span v-else class="text-sm text-gray-500">—</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                      brand.status 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-gray-100 text-gray-800'
                    ]"
                  >
                    {{ brand.status ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span v-if="brand.is_featured" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800">
                    ⭐ Featured
                  </span>
                  <span v-else class="text-gray-400 text-sm">—</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                  {{ brand.products_count || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="brandRoutes.edit(brand.id).url"
                      class="text-blue-600 hover:text-blue-900"
                      title="Edit"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </Link>
                    <button
                      @click="confirmDelete(brand.id)"
                      class="text-red-600 hover:text-red-900"
                      title="Delete"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="brands.data.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                  <div class="flex flex-col items-center">
                    <svg class="h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <p class="font-medium">No brands found</p>
                    <p class="text-gray-400 mt-1">Get started by creating a new brand.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <Pagination :data="brands" resource-name="brands" />
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmDeleteModal
      v-model:show="showDeleteModal"
      :title="deletingBrand?.name ?? ''"
      :message="`Are you sure you want to delete '${deletingBrand?.name}'? This action cannot be undone.`"
      @confirm="deleteBrand"
    />

    <!-- Bulk Delete Modal -->
    <ConfirmDeleteModal
      v-model:show="showBulkDeleteModal"
      title="Multiple Brands"
      :message="`Are you sure you want to delete ${selectedBrands.length} brand(s)? This action cannot be undone.`"
      @confirm="bulkDelete"
    />
  </AdminLayout>
</template>

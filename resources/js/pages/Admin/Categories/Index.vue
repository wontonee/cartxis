<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';
import * as categoryRoutes from '@/routes/admin/catalog/categories';

interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
  image?: string;
  status: 'enabled' | 'disabled';
  sort_order: number;
  show_in_menu: boolean;
  parent_id?: number;
  parent?: {
    id: number;
    name: string;
  };
  created_at: string;
  updated_at: string;
}

interface Props {
  categories: {
    data: Category[];
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
    parent_id?: string;
    sort_by?: string;
    sort_order?: string;
  };
  parentCategories: Array<{ id: number; name: string }>;
}

const props = defineProps<Props>();

const selectedCategories = ref<number[]>([]);
const showDeleteModal = ref(false);
const deleteCategoryId = ref<number | null>(null);
const deletingCategory = ref<{ id: number; name: string } | null>(null);
const showBulkDeleteModal = ref(false);

// Local filter state
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const parentFilter = ref(props.filters.parent_id || '');
const sortBy = ref(props.filters.sort_by || 'sort_order');
const sortOrder = ref(props.filters.sort_order || 'asc');

// Computed
const allSelected = computed(() => {
  return props.categories.data.length > 0 && 
         selectedCategories.value.length === props.categories.data.length;
});

const someSelected = computed(() => {
  return selectedCategories.value.length > 0 && 
         selectedCategories.value.length < props.categories.data.length;
});

// Debounced search
const performSearch = debounce(() => {
  applyFilters();
}, 300);

// Methods
function toggleSelectAll() {
  if (allSelected.value) {
    selectedCategories.value = [];
  } else {
    selectedCategories.value = props.categories.data.map(c => c.id);
  }
}

function applyFilters() {
  router.get(categoryRoutes.index().url, {
    search: search.value || undefined,
    status: statusFilter.value || undefined,
    parent_id: parentFilter.value || undefined,
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
  parentFilter.value = '';
  sortBy.value = 'sort_order';
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

function confirmDelete(categoryId: number) {
  const category = props.categories.data.find(c => c.id === categoryId);
  if (category) {
    deletingCategory.value = { id: category.id, name: category.name };
    showDeleteModal.value = true;
  }
}

function deleteCategory() {
  if (deletingCategory.value) {
    router.delete(categoryRoutes.destroy(deletingCategory.value.id).url, {
      onSuccess: () => {
        showDeleteModal.value = false;
        deletingCategory.value = null;
      },
    });
  }
}

function confirmBulkDelete() {
  if (selectedCategories.value.length > 0) {
    showBulkDeleteModal.value = true;
  }
}

function bulkDelete() {
  router.post(categoryRoutes.bulkDestroy().url, {
    ids: selectedCategories.value,
  }, {
    onSuccess: () => {
      showBulkDeleteModal.value = false;
      selectedCategories.value = [];
    },
  });
}

function bulkUpdateStatus(status: 'enabled' | 'disabled') {
  if (selectedCategories.value.length > 0) {
    router.post(categoryRoutes.bulkStatus().url, {
      ids: selectedCategories.value,
      status: status,
    }, {
      onSuccess: () => {
        selectedCategories.value = [];
      },
    });
  }
}

function changePage(page: number) {
  router.get(categoryRoutes.index().url, {
    ...props.filters,
    page,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
}
</script>

<template>
  <Head title="Categories" />

  <AdminLayout title="Categories">
    <div class="p-6 space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
          <p class="mt-1 text-sm text-gray-600">Manage your product categories</p>
        </div>
        <Link
          :href="categoryRoutes.create().url"
          class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Category
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
              placeholder="Search by name or slug..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="statusFilter"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Status</option>
              <option value="enabled">Enabled</option>
              <option value="disabled">Disabled</option>
            </select>
          </div>

          <!-- Parent Category Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
            <select
              v-model="parentFilter"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Categories</option>
              <option value="null">Root Categories</option>
              <option v-for="parent in parentCategories" :key="parent.id" :value="parent.id">
                {{ parent.name }}
              </option>
            </select>
          </div>

          <!-- Clear Filters Button -->
          <div class="flex items-end">
            <button
              v-if="search || statusFilter || parentFilter"
              @click="clearFilters"
              class="w-full px-3 py-2 text-sm text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Bulk Actions -->
      <div v-if="selectedCategories.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-blue-900">
            {{ selectedCategories.length }} {{ selectedCategories.length === 1 ? 'category' : 'categories' }} selected
          </span>
          <div class="flex gap-2">
            <button
              @click="bulkUpdateStatus('enabled')"
              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700"
            >
              Enable
            </button>
            <button
              @click="bulkUpdateStatus('disabled')"
              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-gray-600 hover:bg-gray-700"
            >
              Disable
            </button>
            <button
              @click="confirmBulkDelete"
              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700"
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
                    Parent
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
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortTable('sort_order')">
                    <div class="flex items-center gap-1">
                      Order
                      <svg v-if="sortBy === 'sort_order'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path v-if="sortOrder === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </div>
                  </th>
                  <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Show in Menu
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="category in categories.data" :key="category.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input
                      type="checkbox"
                      :value="category.id"
                      v-model="selectedCategories"
                      class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div v-if="category.image" class="h-10 w-10 flex-shrink-0">
                        <img class="h-10 w-10 rounded object-cover" :src="`/storage/${category.image}`" :alt="category.name" />
                      </div>
                      <div :class="category.image ? 'ml-4' : ''">
                        <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
                        <div class="text-sm text-gray-500">{{ category.slug }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-if="category.parent" class="text-sm text-gray-900">{{ category.parent.name }}</span>
                    <span v-else class="text-sm text-gray-500">—</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                        category.status === 'enabled' 
                          ? 'bg-green-100 text-green-800' 
                          : 'bg-gray-100 text-gray-800'
                      ]"
                    >
                      {{ category.status === 'enabled' ? 'Enabled' : 'Disabled' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ category.sort_order }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span v-if="category.show_in_menu" class="text-green-600">
                      <svg class="h-5 w-5 inline" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                    </span>
                    <span v-else class="text-gray-400">
                      <svg class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Link
                        :href="categoryRoutes.edit(category.id).url"
                        class="text-blue-600 hover:text-blue-900"
                        title="Edit"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </Link>
                      <button
                        @click="confirmDelete(category.id)"
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
                <tr v-if="categories.data.length === 0">
                  <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                    <div class="flex flex-col items-center">
                      <svg class="h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                      </svg>
                      <p class="font-medium">No categories found</p>
                      <p class="text-gray-400 mt-1">Get started by creating a new category.</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="categories.last_page > 1" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="flex-1 flex justify-between sm:hidden">
                <button
                  @click="changePage(categories.current_page - 1)"
                  :disabled="categories.current_page === 1"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                >
                  Previous
                </button>
                <button
                  @click="changePage(categories.current_page + 1)"
                  :disabled="categories.current_page === categories.last_page"
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                >
                  Next
                </button>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    Showing
                    <span class="font-medium">{{ categories.from }}</span>
                    to
                    <span class="font-medium">{{ categories.to }}</span>
                    of
                    <span class="font-medium">{{ categories.total }}</span>
                    results
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <button
                      @click="changePage(categories.current_page - 1)"
                      :disabled="categories.current_page === 1"
                      class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                    >
                      <span class="sr-only">Previous</span>
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                      </svg>
                    </button>
                    <button
                      v-for="page in categories.last_page"
                      :key="page"
                      @click="changePage(page)"
                      :class="[
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                        page === categories.current_page
                          ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                      ]"
                    >
                      {{ page }}
                    </button>
                    <button
                      @click="changePage(categories.current_page + 1)"
                      :disabled="categories.current_page === categories.last_page"
                      class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                    >
                      <span class="sr-only">Next</span>
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </button>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmDeleteModal
      v-model:show="showDeleteModal"
      :title="deletingCategory?.name ?? ''"
      :message="`Are you sure you want to delete '${deletingCategory?.name}'? This action cannot be undone.`"
      @confirm="deleteCategory"
    />

    <!-- Bulk Delete Modal -->
    <ConfirmDeleteModal
      v-model:show="showBulkDeleteModal"
      title="Multiple Categories"
      :message="`Are you sure you want to delete ${selectedCategories.length} ${selectedCategories.length === 1 ? 'category' : 'categories'}? This action cannot be undone.`"
      @confirm="bulkDelete"
    />
  </AdminLayout>
</template>

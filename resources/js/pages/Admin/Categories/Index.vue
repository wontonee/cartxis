<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';
import * as categoryRoutes from '@/routes/admin/catalog/categories';
import { 
  Plus, 
  Search, 
  Filter, 
  X, 
  Trash2, 
  CheckCircle, 
  XCircle, 
  Edit, 
  Package, 
  Layers,
  ChevronDown,
  ArrowUpDown,
  ArrowUp,
  ArrowDown,
  Image as ImageIcon,
  PlusCircle,
  MinusCircle
} from 'lucide-vue-next';

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
const expandedRows = ref<number[]>([]);

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

function toggleRow(id: number) {
  if (expandedRows.value.includes(id)) {
    expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
  } else {
    expandedRows.value.push(id);
  }
}
</script>

<template>
  <Head title="Categories" />

  <AdminLayout title="Categories">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Categories</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your product categories structure.</p>
        </div>
        <Link
          :href="categoryRoutes.create().url"
          class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 dark:focus:ring-offset-gray-900 transition-all shadow-sm hover:shadow-md"
        >
          <Plus class="w-5 h-5 mr-2" />
          Add Category
        </Link>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="flex items-center gap-2 mb-4 text-gray-900 dark:text-white font-medium">
          <Filter class="w-4 h-4 text-gray-500" />
          Filters & Search
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="md:col-span-1 relative group">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
              <Search class="h-4 w-4 text-gray-400 group-focus-within:text-blue-500 transition-colors" />
            </div>
            <input
              v-model="search"
              @input="performSearch"
              type="text"
              placeholder="Search by name or slug..."
              class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 dark:border-gray-700 rounded-lg leading-5 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all sm:text-sm"
            />
          </div>

          <!-- Status Filter -->
          <div class="md:col-span-1">
            <div class="relative">
              <select
                v-model="statusFilter"
                @change="applyFilters"
                class="appearance-none block w-full pl-3 pr-10 py-2.5 border border-gray-200 dark:border-gray-700 rounded-lg leading-5 bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all sm:text-sm cursor-pointer"
              >
                <option value="">All Status</option>
                <option value="enabled">Enabled</option>
                <option value="disabled">Disabled</option>
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                <ChevronDown class="h-4 w-4" />
              </div>
            </div>
          </div>

          <!-- Parent Category Filter -->
          <div class="md:col-span-1">
            <div class="relative">
              <select
                v-model="parentFilter"
                @change="applyFilters"
                class="appearance-none block w-full pl-3 pr-10 py-2.5 border border-gray-200 dark:border-gray-700 rounded-lg leading-5 bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all sm:text-sm cursor-pointer"
              >
                <option value="">All Categories</option>
                <option value="null">Root Categories</option>
                <option v-for="parent in parentCategories" :key="parent.id" :value="parent.id">
                  {{ parent.name }}
                </option>
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                <ChevronDown class="h-4 w-4" />
              </div>
            </div>
          </div>

          <!-- Clear Filters (keeping structure similar but aligned) -->
          <div class="flex items-end md:col-span-1">
            <!-- This will be handled by the Active Filters & Clear section below -->
          </div>
        </div>

        <!-- Active Filters & Clear -->
        <div v-if="search || statusFilter || parentFilter" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
          <div class="flex flex-wrap gap-2">
            <span v-if="search" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-100 dark:border-blue-800">
              Search: {{ search }}
              <button @click="search = ''; performSearch()" class="ml-1.5 hover:text-blue-900 dark:hover:text-blue-100"><X class="w-3 h-3" /></button>
            </span>
            <span v-if="statusFilter" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
              Status: {{ statusFilter }}
              <button @click="statusFilter = ''; applyFilters()" class="ml-1.5 hover:text-gray-900 dark:hover:text-white"><X class="w-3 h-3" /></button>
            </span>
             <span v-if="parentFilter" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
              Parent: {{ parentFilter === 'null' ? 'Root' : parentCategories.find(p => p.id == parentFilter)?.name || parentFilter }}
              <button @click="parentFilter = ''; applyFilters()" class="ml-1.5 hover:text-gray-900 dark:hover:text-white"><X class="w-3 h-3" /></button>
            </span>
          </div>
          <button
            @click="clearFilters"
            class="text-sm text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 font-medium flex items-center transition-colors"
          >
            <X class="w-4 h-4 mr-1" />
            Clear All
          </button>
        </div>
      </div>

      <!-- Bulk Actions -->
      <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="selectedCategories.length > 0" class="bg-blue-600 rounded-xl shadow-lg p-3 text-white flex items-center justify-between sticky top-4 z-10 px-6">
          <span class="text-sm font-semibold flex items-center">
            <CheckCircle class="w-4 h-4 mr-2" />
            {{ selectedCategories.length }} {{ selectedCategories.length === 1 ? 'category' : 'categories' }} selected
          </span>
          <div class="flex gap-2">
            <button
              @click="bulkUpdateStatus('enabled')"
              class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-white rounded-lg hover:bg-blue-50 transition-colors uppercase tracking-wide"
            >
              Enable
            </button>
            <button
              @click="bulkUpdateStatus('disabled')"
              class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-white rounded-lg hover:bg-blue-50 transition-colors uppercase tracking-wide"
            >
              Disable
            </button>
            <div class="w-px h-6 bg-blue-400 mx-1"></div>
            <button
              @click="confirmBulkDelete"
              class="px-3 py-1.5 text-xs font-bold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors flex items-center uppercase tracking-wide"
            >
              <Trash2 class="w-3 h-3 mr-1.5" />
              Delete
            </button>
          </div>
        </div>
      </transition>

      <!-- Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
            <thead class="bg-gray-50/80 dark:bg-gray-700/50">
              <tr>
                <th class="w-12 px-6 py-4">
                  <input
                    type="checkbox"
                    :checked="allSelected"
                    :indeterminate.prop="someSelected"
                    @change="toggleSelectAll"
                    class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 transition-all"
                  />
                </th>
                <th class="w-20 px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Image
                </th>
                <th
                  @click="sortTable('name')"
                  class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Name & Slug
                    <span v-if="sortBy === 'name'" class="text-blue-600 dark:text-blue-400">
                       <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                       <ArrowDown v-else class="w-3 h-3" />
                    </span>
                    <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                  </div>
                </th>
                <th class="hidden md:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Parent
                </th>
                <th
                  @click="sortTable('status')"
                  class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Status
                    <span v-if="sortBy === 'status'" class="text-blue-600 dark:text-blue-400">
                       <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                       <ArrowDown v-else class="w-3 h-3" />
                    </span>
                    <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                  </div>
                </th>
                <th
                  @click="sortTable('sort_order')"
                  class="hidden lg:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Order
                    <span v-if="sortBy === 'sort_order'" class="text-blue-600 dark:text-blue-400">
                       <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                       <ArrowDown v-else class="w-3 h-3" />
                    </span>
                    <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                  </div>
                </th>
                <th class="hidden lg:table-cell px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Menu
                </th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-if="categories.data.length === 0">
                <td colspan="8" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                      <Layers class="w-8 h-8" />
                    </div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">No categories found</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm">No categories matched your search criteria. Try adjusting your filters or add a new category.</p>
                     <button @click="clearFilters" v-if="search || statusFilter || parentFilter" class="mt-4 text-blue-600 hover:text-blue-700 font-medium text-sm">Clear all filters</button>
                  </div>
                </td>
              </tr>
              <template v-for="category in categories.data" :key="category.id">
              <tr class="group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                <td class="px-6 py-4 relative">
                  <div class="flex items-center">
                    <button 
                      @click="toggleRow(category.id)" 
                      class="lg:hidden absolute left-2 p-1 text-blue-600 hover:text-blue-800 focus:outline-none"
                    >
                      <MinusCircle v-if="expandedRows.includes(category.id)" class="w-5 h-5 fill-blue-100 dark:fill-blue-900/30" />
                      <PlusCircle v-else class="w-5 h-5 fill-blue-50 dark:fill-blue-900/20" />
                    </button>
                    <input
                      v-model="selectedCategories"
                      type="checkbox"
                      :value="category.id"
                      class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer transition-all ml-4 lg:ml-0"
                    />
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div v-if="category.image" class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 shadow-sm">
                    <img :src="`/storage/${category.image}`" :alt="category.name" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" />
                  </div>
                  <div v-else class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 flex items-center justify-center text-gray-400 dark:text-gray-500">
                    <ImageIcon class="w-6 h-6" />
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ category.name }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">{{ category.slug }}</span>
                  </div>
                </td>
                <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                   <span v-if="category.parent" class="inline-flex items-center text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 px-2 py-1 rounded-md border border-gray-100 dark:border-gray-700">
                      <Layers class="w-3 h-3 mr-1.5 opacity-70" />
                      {{ category.parent.name }}
                   </span>
                   <span v-else class="text-xs text-gray-400 dark:text-gray-500 italic">Root Category</span>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border shadow-sm" :class="{
                    'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800': category.status === 'enabled',
                    'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600': category.status === 'disabled',
                  }">
                    <CheckCircle v-if="category.status === 'enabled'" class="w-3 h-3 mr-1" />
                    <XCircle v-else class="w-3 h-3 mr-1" />
                    {{ category.status.charAt(0).toUpperCase() + category.status.slice(1) }}
                  </span>
                </td>
                <td class="hidden lg:table-cell px-6 py-4 text-sm text-gray-500 dark:text-gray-400 font-mono">
                  {{ category.sort_order }}
                </td>
                <td class="hidden lg:table-cell px-6 py-4 text-center">
                  <div v-if="category.show_in_menu" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 ring-1 ring-inset ring-green-600/20">
                     <CheckCircle class="w-4 h-4" />
                  </div>
                  <div v-else class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 ring-1 ring-inset ring-gray-300/20">
                     <X class="w-4 h-4" />
                  </div>
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium align-middle">
                  <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                    <Link
                      :href="categoryRoutes.edit(category.id).url"
                      class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                      title="Edit"
                    >
                      <Edit class="w-4 h-4" />
                    </Link>
                    <button
                      @click="confirmDelete(category.id)"
                      class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                      title="Delete"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <!-- Expanded Mobile Details Row -->
              <tr v-if="expandedRows.includes(category.id)" class="lg:hidden bg-blue-50/30 dark:bg-blue-900/10 border-t border-gray-100 dark:border-gray-700">
                <td colspan="100%" class="px-6 py-4">
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-sm">
                    <div class="md:hidden">
                      <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-1.5">Parent</span>
                       <span v-if="category.parent" class="inline-flex items-center text-gray-700 dark:text-gray-300">
                          <Layers class="w-3.5 h-3.5 mr-1.5 opacity-70" />
                          {{ category.parent.name }}
                       </span>
                       <span v-else class="text-gray-400 dark:text-gray-500 italic">Root Category</span>
                    </div>

                    <div>
                      <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-1.5">Order</span>
                      <span class="font-mono text-gray-700 dark:text-gray-300">{{ category.sort_order }}</span>
                    </div>
                    
                    <div>
                      <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-1.5">Show in Menu</span>
                      <span v-if="category.show_in_menu" class="inline-flex items-center text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-md text-xs font-medium border border-green-200 dark:border-green-800">
                        <CheckCircle class="w-3 h-3 mr-1" /> Yes
                      </span>
                      <span v-else class="inline-flex items-center text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-md text-xs font-medium border border-gray-200 dark:border-gray-700">
                        <X class="w-3 h-3 mr-1" /> No
                      </span>
                    </div>
                  </div>
                </td>
              </tr>
              </template>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <Pagination :data="categories" resource-name="categories" />
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
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';
import Pagination from '@/components/Admin/Pagination.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import * as brandRoutes from '@/routes/admin/catalog/brands';
import { 
  Plus, 
  Search, 
  Filter, 
  X, 
  Trash2, 
  CheckCircle, 
  XCircle, 
  Edit, 
  ArrowUp, 
  ArrowDown, 
  AlertCircle,
  PlusCircle,
  MinusCircle,
  ImageIcon,
  ArrowUpDown,
  Tag
} from 'lucide-vue-next';

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
const expandedRows = ref<number[]>([]);

// Local filter state
const search = ref(props.filters.search || '');
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

const toggleRow = (id: number) => {
  const index = expandedRows.value.indexOf(id);
  if (index === -1) {
    expandedRows.value.push(id);
  } else {
    expandedRows.value.splice(index, 1);
  }
};

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
    search: search.value || undefined,
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
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="relative">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="search"
                @input="performSearch"
                type="text"
                placeholder="Search brands..."
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
              />
            </div>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
            <div class="relative">
              <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <select
                v-model="statusFilter"
                @change="applyFilters"
                class="w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
              >
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
               <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
              </div>
            </div>
          </div>

          <!-- Featured Filter -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Featured</label>
            <div class="relative">
              <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <select
                v-model="featuredFilter"
                @change="applyFilters"
                class="w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
              >
                <option value="">All Brands</option>
                <option value="1">Featured</option>
                <option value="0">Not Featured</option>
              </select>
               <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
              </div>
            </div>
          </div>

          <!-- Clear Filters -->
           <div class="flex items-end">
            <button
              @click="clearFilters"
              class="w-full py-2.5 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors flex items-center justify-center gap-2"
            >
              <X class="w-4 h-4" />
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Bulk Actions -->
      <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="selectedBrands.length > 0" class="bg-blue-600 rounded-xl shadow-lg p-3 text-white flex items-center justify-between sticky top-4 z-10 px-6">
          <span class="text-sm font-semibold flex items-center">
            <CheckCircle class="w-4 h-4 mr-2" />
            {{ selectedBrands.length }} {{ selectedBrands.length === 1 ? 'brand' : 'brands' }} selected
          </span>
          <div class="flex gap-2">
            <button
              @click="bulkUpdateStatus(true)"
              class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-white rounded-lg hover:bg-blue-50 transition-colors uppercase tracking-wide"
            >
              Activate
            </button>
            <button
              @click="bulkUpdateStatus(false)"
              class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-white rounded-lg hover:bg-blue-50 transition-colors uppercase tracking-wide"
            >
              Deactivate
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
                <th scope="col" class="w-12 px-6 py-4">
                  <input
                    type="checkbox"
                    :checked="allSelected"
                    :indeterminate.prop="someSelected"
                    @change="toggleSelectAll"
                    class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 transition-all"
                  />
                </th>
                <th
                  scope="col"
                  class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                  @click="sortTable('name')"
                >
                  <div class="flex items-center gap-1">
                    Name
                    <span v-if="sortBy === 'name'" class="text-blue-600 dark:text-blue-400">
                       <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                       <ArrowDown v-else class="w-3 h-3" />
                    </span>
                    <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                  </div>
                </th>
                <th scope="col" class="hidden md:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Website
                </th>
                <th
                  scope="col"
                  class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                  @click="sortTable('status')"
                >
                  <div class="flex items-center justify-center gap-1">
                    Status
                    <span v-if="sortBy === 'status'" class="text-blue-600 dark:text-blue-400">
                       <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                       <ArrowDown v-else class="w-3 h-3" />
                    </span>
                    <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                  </div>
                </th>
                <th scope="col" class="hidden sm:table-cell px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Featured
                </th>
                 <th scope="col" class="hidden lg:table-cell px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Products
                </th>
                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
              <template v-for="brand in brands.data" :key="brand.id">
              <tr class="group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                <td class="px-6 py-4 relative">
                  <div class="flex items-center">
                    <button 
                      @click="toggleRow(brand.id)" 
                      class="lg:hidden absolute left-2 p-1 text-blue-600 hover:text-blue-800 focus:outline-none"
                    >
                      <MinusCircle v-if="expandedRows.includes(brand.id)" class="w-5 h-5 fill-blue-100 dark:fill-blue-900/30" />
                      <PlusCircle v-else class="w-5 h-5 fill-blue-50 dark:fill-blue-900/20" />
                    </button>
                    <input
                      type="checkbox"
                      :value="brand.id"
                      v-model="selectedBrands"
                      class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer transition-all ml-4 lg:ml-0"
                    />
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                   <div class="flex items-center">
                    <div class="h-10 w-10 flex-shrink-0">
                      <img v-if="brand.logo" class="h-10 w-10 rounded-full object-cover border border-gray-100" :src="`/storage/${brand.logo}`" :alt="brand.name" />
                      <div v-else class="h-10 w-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400">
                        <ImageIcon class="w-5 h-5" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ brand.name }}</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ brand.slug }}</div>
                    </div>
                  </div>
                </td>
                <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                  <a v-if="brand.website" :href="brand.website" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
                    <ExternalLink class="w-3 h-3" />
                    Visit
                  </a>
                  <span v-else class="text-sm text-gray-400 dark:text-gray-600">-</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border shadow-sm',
                      brand.status ? 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800' : 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'
                    ]"
                  >
                    <CheckCircle v-if="brand.status" class="w-3 h-3 mr-1" />
                    <XCircle v-else class="w-3 h-3 mr-1" />
                    {{ brand.status ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap text-center">
                   <span v-if="brand.is_featured" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 ring-1 ring-inset ring-yellow-600/20">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                  </span>
                  <span v-else class="text-gray-300 dark:text-gray-600">
                    -
                  </span>
                </td>
                <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                  <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
                    {{ brand.products_count || 0 }} products
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                    <Link
                      :href="brandRoutes.edit(brand.id).url"
                      class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                      title="Edit"
                    >
                      <Edit class="w-4 h-4" />
                    </Link>
                    <button
                      @click="confirmDelete(brand.id)"
                      class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                      title="Delete"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <!-- Expanded Mobile Row -->
              <tr v-if="expandedRows.includes(brand.id)" class="bg-gray-50/50 dark:bg-gray-900/50 lg:hidden">
                 <td colspan="7" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                       <div class="flex flex-col gap-1 col-span-2 sm:col-span-1" v-if="brand.website">
                          <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Website</span>
                          <a :href="brand.website" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1">
                             <ExternalLink class="w-3 h-3" /> {{ brand.website }}
                          </a>
                       </div>
                       <div class="flex flex-col gap-1 sm:hidden">
                          <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Featured</span>
                           <span v-if="brand.is_featured" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 w-fit">Featured</span>
                           <span v-else class="text-gray-500">No</span>
                       </div>
                        <div class="flex flex-col gap-1 lg:hidden">
                          <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Products</span>
                          <span class="text-gray-700 dark:text-gray-300">{{ brand.products_count || 0 }} products</span>
                       </div>
                        <div class="flex flex-col gap-1">
                          <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Created</span>
                          <span class="text-gray-700 dark:text-gray-300">{{ new Date(brand.created_at).toLocaleDateString() }}</span>
                       </div>
                    </div>
                 </td>
              </tr>
              </template>
              <tr v-if="brands.data.length === 0">
                <td colspan="7" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                      <Tag class="w-8 h-8" />
                    </div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">No brands found</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm">Get started by creating a new brand.</p>
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

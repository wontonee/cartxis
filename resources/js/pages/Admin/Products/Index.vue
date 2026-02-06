<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import ProductQuickView from '@/components/Admin/ProductQuickView.vue';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';
import * as productRoutes from '@/routes/admin/catalog/products';
import { useCurrency } from '@/composables/useCurrency';
import { 
  Plus, 
  Search, 
  Filter, 
  X, 
  Trash2, 
  CheckCircle, 
  XCircle, 
  MoreHorizontal, 
  Eye, 
  Edit, 
  Archive, 
  Package, 
  Tag, 
  DollarSign, 
  Layers,
  ChevronDown,
  ArrowUpDown,
  ArrowUp,
  ArrowDown,
  PlusCircle,
  MinusCircle
} from 'lucide-vue-next';

interface Product {
  id: number;
  sku: string;
  name: string;
  slug: string;
  price: number;
  special_price?: number;
  quantity: number;
  status: 'enabled' | 'disabled';
  stock_status: 'in_stock' | 'out_of_stock' | 'on_backorder';
  type: 'simple' | 'configurable' | 'virtual' | 'downloadable';
  featured: boolean;
  new: boolean;
  created_at: string;
  main_image?: {
    id: number;
    path: string;
    url: string;
    thumbnail_path: string;
  };
  categories: Array<{ id: number; name: string }>;
  images_count: number;
}

interface Props {
  products: {
    data: Product[];
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
    category_id?: number;
    stock_status?: string;
    sort_by?: string;
    sort_order?: string;
  };
  categories: Array<{ id: number; name: string }>;
}

const props = defineProps<Props>();

const selectedProducts = ref<number[]>([]);
const showDeleteModal = ref(false);
const deleteProductId = ref<number | null>(null);
const deletingProduct = ref<{ id: number; name: string } | null>(null);
const showBulkDeleteModal = ref(false);
const expandedRows = ref<number[]>([]);

// Quick View
const showQuickView = ref(false);
const selectedProduct = ref<Product | null>(null);

// Local filter state
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const categoryFilter = ref(props.filters.category_id || '');
const stockStatusFilter = ref(props.filters.stock_status || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortOrder = ref(props.filters.sort_order || 'desc');

// Computed
const allSelected = computed(() => {
  return props.products.data.length > 0 && 
         selectedProducts.value.length === props.products.data.length;
});

const someSelected = computed(() => {
  return selectedProducts.value.length > 0 && 
         selectedProducts.value.length < props.products.data.length;
});

// Debounced search
const performSearch = debounce(() => {
  applyFilters();
}, 300);

// Methods
const toggleRow = (id: number) => {
  const index = expandedRows.value.indexOf(id);
  if (index === -1) {
    expandedRows.value.push(id);
  } else {
    expandedRows.value.splice(index, 1);
  }
};

function toggleSelectAll() {
  if (allSelected.value) {
    selectedProducts.value = [];
  } else {
    selectedProducts.value = props.products.data.map(p => p.id);
  }
}

function applyFilters() {
  router.get(productRoutes.index().url, {
    search: search.value || undefined,
    status: statusFilter.value || undefined,
    category_id: categoryFilter.value || undefined,
    stock_status: stockStatusFilter.value || undefined,
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
  categoryFilter.value = '';
  stockStatusFilter.value = '';
  sortBy.value = 'created_at';
  sortOrder.value = 'desc';
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

function confirmDelete(productId: number) {
  const product = props.products.data.find((p) => p.id === productId);
  if (product) {
    deletingProduct.value = { id: product.id, name: product.name };
    showDeleteModal.value = true;
  }
}

function deleteProduct() {
  if (deletingProduct.value) {
    router.delete(productRoutes.destroy({ product: deletingProduct.value.id }).url, {
      onSuccess: () => {
        showDeleteModal.value = false;
        deletingProduct.value = null;
      },
    });
  }
}

function confirmBulkDelete() {
  if (selectedProducts.value.length > 0) {
    showBulkDeleteModal.value = true;
  }
}

function bulkDelete() {
  router.post(productRoutes.bulkDestroy().url, {
    ids: selectedProducts.value,
  }, {
    onSuccess: () => {
      selectedProducts.value = [];
      showBulkDeleteModal.value = false;
    },
  });
}

function openQuickView(product: Product) {
  selectedProduct.value = product;
  showQuickView.value = true;
}

function editProductFromQuickView(product: Product) {
  router.visit(productRoutes.edit({ product: product.id }).url);
}

function deleteProductFromQuickView(product: Product) {
  showQuickView.value = false;
  confirmDelete(product.id);
}

function bulkUpdateStatus(status: 'enabled' | 'disabled') {
  if (selectedProducts.value.length > 0) {
    router.post(productRoutes.bulkStatus().url, {
      ids: selectedProducts.value,
      status: status,
    }, {
      onSuccess: () => {
        selectedProducts.value = [];
      },
    });
  }
}

const { formatPrice } = useCurrency();

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}
</script>

<template>
  <Head title="Products" />

  <AdminLayout title="Products">
    <div class="space-y-5">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Products</h1>
          <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Manage your entire product catalog from one place.</p>
        </div>
        <Link
          :href="productRoutes.create().url"
          class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 dark:focus:ring-offset-gray-900 transition-all shadow-sm"
        >
          <Plus class="w-4 h-4 mr-1.5" />
          Add Product
        </Link>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-gray-800/80 rounded-xl border border-gray-100 dark:border-gray-700/60 p-5">
        <div class="flex items-center gap-2 mb-3.5 text-gray-700 dark:text-gray-300 text-sm font-medium">
          <Filter class="w-3.5 h-3.5 text-gray-400" />
          Filters & Search
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
          <!-- Search -->
          <div class="md:col-span-4 relative group">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
              <Search class="h-4 w-4 text-gray-400 group-focus-within:text-blue-500 transition-colors" />
            </div>
            <input
              v-model="search"
              @input="performSearch"
              type="text"
              placeholder="Search by name, SKU..."
              class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 dark:border-gray-700 rounded-lg leading-5 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all sm:text-sm"
            />
          </div>

          <!-- Status Filter -->
          <div class="md:col-span-2">
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

          <!-- Category Filter -->
          <div class="md:col-span-3">
            <div class="relative">
              <select
                v-model="categoryFilter"
                @change="applyFilters"
                class="appearance-none block w-full pl-3 pr-10 py-2.5 border border-gray-200 dark:border-gray-700 rounded-lg leading-5 bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all sm:text-sm cursor-pointer"
              >
                <option value="">All Categories</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                <ChevronDown class="h-4 w-4" />
              </div>
            </div>
          </div>

          <!-- Stock Status Filter -->
          <div class="md:col-span-3">
            <div class="relative">
              <select
                v-model="stockStatusFilter"
                @change="applyFilters"
                class="appearance-none block w-full pl-3 pr-10 py-2.5 border border-gray-200 dark:border-gray-700 rounded-lg leading-5 bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all sm:text-sm cursor-pointer"
              >
                <option value="">All Stock Status</option>
                <option value="in_stock">In Stock</option>
                <option value="out_of_stock">Out of Stock</option>
                <option value="on_backorder">On Backorder</option>
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                <ChevronDown class="h-4 w-4" />
              </div>
            </div>
          </div>
        </div>

        <!-- Active Filters & Clear -->
        <div v-if="search || statusFilter || categoryFilter || stockStatusFilter" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
          <div class="flex flex-wrap gap-2">
            <span v-if="search" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-100 dark:border-blue-800">
              Search: {{ search }}
              <button @click="search = ''; performSearch()" class="ml-1.5 hover:text-blue-900 dark:hover:text-blue-100"><X class="w-3 h-3" /></button>
            </span>
            <span v-if="statusFilter" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
              Status: {{ statusFilter }}
              <button @click="statusFilter = ''; applyFilters()" class="ml-1.5 hover:text-gray-900 dark:hover:text-white"><X class="w-3 h-3" /></button>
            </span>
            <!-- Add more badges as needed -->
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
        <div v-if="selectedProducts.length > 0" class="bg-blue-600 rounded-xl p-3 text-white flex items-center justify-between sticky top-4 z-10 px-5 shadow-lg shadow-blue-600/20">
          <span class="text-xs font-semibold flex items-center">
            <CheckCircle class="w-3.5 h-3.5 mr-1.5" />
            {{ selectedProducts.length }} product{{ selectedProducts.length > 1 ? 's' : '' }} selected
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

      <!-- Products Table -->
      <div class="bg-white dark:bg-gray-800/80 rounded-xl border border-gray-100 dark:border-gray-700/60 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/60">
            <thead class="bg-gray-50/80 dark:bg-gray-800/50">
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
                    Product Details
                    <span v-if="sortBy === 'name'" class="text-blue-600 dark:text-blue-400">
                       <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                       <ArrowDown v-else class="w-3 h-3" />
                    </span>
                    <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                  </div>
                </th>
                <th
                  @click="sortTable('sku')"
                  class="hidden md:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    SKU & Type
                    <span v-if="sortBy === 'sku'" class="text-blue-600 dark:text-blue-400">
                       <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                       <ArrowDown v-else class="w-3 h-3" />
                    </span>
                    <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                  </div>
                </th>
                <th
                  @click="sortTable('price')"
                  class="hidden md:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Price
                    <span v-if="sortBy === 'price'" class="text-blue-600 dark:text-blue-400">
                       <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                       <ArrowDown v-else class="w-3 h-3" />
                    </span>
                    <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                  </div>
                </th>
                <th
                  @click="sortTable('quantity')"
                  class="hidden lg:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Stock
                    <span v-if="sortBy === 'quantity'" class="text-blue-600 dark:text-blue-400">
                       <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                       <ArrowDown v-else class="w-3 h-3" />
                    </span>
                    <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                  </div>
                </th>
                <th class="hidden lg:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800/80 divide-y divide-gray-50 dark:divide-gray-700/40">
              <tr v-if="products.data.length === 0">
                <td colspan="8" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                      <Package class="w-8 h-8" />
                    </div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">No products found</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm">No products matched your search criteria. Try adjusting your filters or add a new product.</p>
                    <button @click="clearFilters" v-if="search || statusFilter || categoryFilter" class="mt-4 text-blue-600 hover:text-blue-700 font-medium text-sm">Clear all filters</button>
                  </div>
                </td>
              </tr>
              <template v-for="product in products.data" :key="product.id">
              <tr class="group hover:bg-gray-50/80 dark:hover:bg-gray-700/20 transition-colors">
                <td class="px-6 py-4 relative">
                  <div class="flex items-center">
                    <button 
                      @click="toggleRow(product.id)" 
                      class="md:hidden absolute left-2 p-1 text-blue-600 hover:text-blue-800 focus:outline-none"
                    >
                      <MinusCircle v-if="expandedRows.includes(product.id)" class="w-5 h-5 fill-blue-100 dark:fill-blue-900/30" />
                      <PlusCircle v-else class="w-5 h-5 fill-blue-50 dark:fill-blue-900/20" />
                    </button>
                    <input
                      v-model="selectedProducts"
                      type="checkbox"
                      :value="product.id"
                      class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer transition-all ml-4 md:ml-0"
                    />
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div v-if="product.main_image" class="relative group/image">
                    <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 shadow-sm">
                      <img :src="product.main_image.url" :alt="product.name" class="w-full h-full object-cover group-hover/image:scale-110 transition-transform duration-300" />
                    </div>
                    <button @click="openQuickView(product)" class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover/image:opacity-100 transition-opacity rounded-lg">
                      <Eye class="w-5 h-5 text-white drop-shadow-md" />
                    </button>
                  </div>
                  <div v-else class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 flex items-center justify-center text-gray-400 dark:text-gray-500">
                    <Package class="w-6 h-6" />
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-start gap-2 max-w-xs">
                    <div>
                      <div class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2 leading-snug">{{ product.name }}</div>
                      <div v-if="product.categories.length > 0" class="flex flex-wrap gap-1 mt-1.5 ">
                        <span class="inline-flex items-center text-[10px] text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 px-1.5 py-0.5 rounded">
                           <Layers class="w-3 h-3 mr-1 opacity-70" />
                           {{ product.categories[0].name }}
                           <span v-if="product.categories.length > 1" class="ml-0.5">+{{ product.categories.length - 1 }}</span>
                        </span>
                      </div>
                      <div class="flex gap-1.5 mt-2" v-if="product.featured || product.new">
                        <span v-if="product.featured" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-gradient-to-r from-yellow-100 to-yellow-200 dark:from-yellow-900/40 dark:to-yellow-800/40 text-yellow-800 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800/50 shadow-sm">
                          Featured
                        </span>
                        <span v-if="product.new" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-gradient-to-r from-green-100 to-green-200 dark:from-green-900/40 dark:to-green-800/40 text-green-800 dark:text-green-300 border border-green-200 dark:border-green-800/50 shadow-sm">
                          New
                        </span>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="hidden md:table-cell px-6 py-4 text-sm align-top pt-5">
                  <div class="flex flex-col gap-1.5">
                    <span class="text-xs font-mono text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 px-1.5 py-0.5 rounded border border-gray-200 dark:border-gray-700 w-fit">{{ product.sku || 'NO-SKU' }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium w-fit border shadow-sm" :class="{
                      'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700': product.type === 'simple',
                      'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800': product.type === 'configurable',
                      'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800': product.type === 'virtual',
                      'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-900/20 dark:text-cyan-300 dark:border-cyan-800': product.type === 'downloadable',
                    }">
                      {{ product.type.charAt(0).toUpperCase() + product.type.slice(1) }}
                    </span>
                  </div>
                </td>
                <td class="hidden md:table-cell px-6 py-4 text-sm text-gray-900 dark:text-white align-top pt-5">
                  <div v-if="product.special_price" class="flex flex-col">
                    <span class="font-bold text-red-600 dark:text-red-400">{{ formatPrice(product.special_price) }}</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500 line-through decoration-gray-400">{{ formatPrice(product.price) }}</span>
                  </div>
                  <span v-else class="font-bold">{{ formatPrice(product.price) }}</span>
                </td>
                <td class="hidden lg:table-cell px-6 py-4 text-sm align-top pt-5">
                  <div class="flex flex-col gap-1">
                     <div class="flex items-center text-xs font-medium" :class="{
                      'text-green-600 dark:text-green-400': product.stock_status === 'in_stock',
                      'text-red-600 dark:text-red-400': product.stock_status === 'out_of_stock',
                      'text-yellow-600 dark:text-yellow-400': product.stock_status === 'on_backorder',
                    }">
                        <div class="w-1.5 h-1.5 rounded-full mr-1.5" :class="{
                          'bg-green-500': product.stock_status === 'in_stock',
                          'bg-red-500': product.stock_status === 'out_of_stock',
                          'bg-yellow-500': product.stock_status === 'on_backorder',
                        }"></div>
                        {{ product.quantity }} units
                     </div>
                    <span class="text-[10px] uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400">
                      {{ product.stock_status.replace('_', ' ') }}
                    </span>
                  </div>
                </td>
                <td class="hidden lg:table-cell px-6 py-4 align-top pt-5">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border shadow-sm" :class="{
                    'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800': product.status === 'enabled',
                    'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600': product.status === 'disabled',
                  }">
                    <CheckCircle v-if="product.status === 'enabled'" class="w-3 h-3 mr-1" />
                    <XCircle v-else class="w-3 h-3 mr-1" />
                    {{ product.status.charAt(0).toUpperCase() + product.status.slice(1) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium align-middle">
                  <div class="flex items-center justify-end gap-1 opacity-50 group-hover:opacity-100 transition-opacity">
                    <button
                      @click="openQuickView(product)"
                      class="p-1.5 text-gray-400 hover:text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-500/10 rounded-lg transition-colors"
                      title="Quick View"
                    >
                      <Eye class="w-4 h-4" />
                    </button>
                    <Link
                      :href="productRoutes.edit({ product: product.id }).url"
                      class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition-colors"
                      title="Edit"
                    >
                      <Edit class="w-4 h-4" />
                    </Link>
                    <button
                      @click="confirmDelete(product.id)"
                      class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors"
                      title="Delete"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <!-- Expanded Row for Mobile/Tablet -->
              <tr v-if="expandedRows.includes(product.id)" class="bg-gray-50/50 dark:bg-gray-900/50 md:hidden">
                 <td colspan="8" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                       <div class="flex flex-col gap-1">
                          <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Price</span>
                          <div v-if="product.special_price">
                             <span class="font-bold text-red-600">{{ formatPrice(product.special_price) }}</span>
                             <span class="text-xs line-through text-gray-400 ml-1">{{ formatPrice(product.price) }}</span>
                          </div>
                          <span v-else class="font-bold text-gray-900 dark:text-gray-100">{{ formatPrice(product.price) }}</span>
                       </div>
                       <div class="flex flex-col gap-1">
                          <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Stock</span>
                          <div class="flex items-center gap-2">
                             <span class="font-semibold text-gray-900 dark:text-white">{{ product.quantity }} units</span>
                             <span class="px-1.5 py-0.5 text-[10px] rounded border" :class="{
                                'bg-green-50 text-green-700 border-green-200': product.stock_status === 'in_stock',
                                'bg-red-50 text-red-700 border-red-200': product.stock_status === 'out_of_stock',
                                'bg-yellow-50 text-yellow-700 border-yellow-200': product.stock_status === 'on_backorder'
                             }">{{ product.stock_status.replace('_', ' ') }}</span>
                          </div>
                       </div>
                       <div class="flex flex-col gap-1">
                          <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">SKU & Type</span>
                          <div class="flex flex-col gap-1">
                             <span class="font-mono text-xs text-gray-600">{{ product.sku || 'N/A' }}</span>
                             <span class="text-xs px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded w-fit capitalize">{{ product.type }}</span>
                          </div>
                       </div>
                       <div class="flex flex-col gap-1">
                          <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Status</span>
                          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium w-fit border" :class="{
                           'bg-green-50 text-green-700 border-green-200': product.status === 'enabled',
                           'bg-gray-100 text-gray-600 border-gray-200': product.status === 'disabled',
                          }">
                             {{ product.status.charAt(0).toUpperCase() + product.status.slice(1) }}
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
        <Pagination :data="products" resource-name="products" />
      </div>

      <!-- Delete Confirmation Modal -->
      <ConfirmDeleteModal
        v-model:show="showDeleteModal"
        :title="deletingProduct?.name ?? ''"
        :message="`Are you sure you want to delete '${deletingProduct?.name}'? This action cannot be undone.`"
        @confirm="deleteProduct"
      />

      <!-- Bulk Delete Confirmation Modal -->
      <ConfirmDeleteModal
        v-model:show="showBulkDeleteModal"
        title="Delete Multiple Products"
        :message="`Are you sure you want to delete ${selectedProducts.length} product${selectedProducts.length > 1 ? 's' : ''}? This action cannot be undone.`"
        @confirm="bulkDelete"
      />

      <!-- Product Quick View Modal -->
      <ProductQuickView
        :product="selectedProduct"
        :show="showQuickView"
        @close="showQuickView = false"
        @edit="editProductFromQuickView"
        @delete="deleteProductFromQuickView"
      />
    </div>
  </AdminLayout>
</template>

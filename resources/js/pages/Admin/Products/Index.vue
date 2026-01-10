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
  featured: boolean;
  new: boolean;
  created_at: string;
  main_image?: {
    id: number;
    path: string;
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
    <div class="p-6 space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Products</h1>
          <p class="mt-1 text-sm text-gray-600">Manage your product catalog</p>
      </div>
      <Link
        :href="productRoutes.create().url"
        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Product
      </Link>
    </div>      <!-- Filters Card -->
      <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="search"
              @input="performSearch"
              type="text"
              placeholder="Search by name or SKU..."
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

          <!-- Category Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select
              v-model="categoryFilter"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Categories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <!-- Stock Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
            <select
              v-model="stockStatusFilter"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Stock</option>
              <option value="in_stock">In Stock</option>
              <option value="out_of_stock">Out of Stock</option>
              <option value="on_backorder">On Backorder</option>
            </select>
          </div>
        </div>

        <!-- Clear Filters -->
        <div v-if="search || statusFilter || categoryFilter || stockStatusFilter" class="mt-3 flex justify-end">
          <button
            @click="clearFilters"
            class="text-sm text-gray-600 hover:text-gray-900"
          >
            Clear Filters
          </button>
        </div>
      </div>

      <!-- Bulk Actions -->
      <div v-if="selectedProducts.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-blue-900">
            {{ selectedProducts.length }} product{{ selectedProducts.length > 1 ? 's' : '' }} selected
          </span>
          <div class="flex gap-2">
            <button
              @click="bulkUpdateStatus('enabled')"
              class="px-3 py-1.5 text-sm font-medium text-green-700 bg-green-100 rounded hover:bg-green-200 transition-colors"
            >
              Enable
            </button>
            <button
              @click="bulkUpdateStatus('disabled')"
              class="px-3 py-1.5 text-sm font-medium text-yellow-700 bg-yellow-100 rounded hover:bg-yellow-200 transition-colors"
            >
              Disable
            </button>
            <button
              @click="confirmBulkDelete"
              class="px-3 py-1.5 text-sm font-medium text-red-700 bg-red-100 rounded hover:bg-red-200 transition-colors"
            >
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Products Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="w-12 px-6 py-3">
                  <input
                    type="checkbox"
                    :checked="allSelected"
                    :indeterminate.prop="someSelected"
                    @change="toggleSelectAll"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                </th>
                <th class="w-20 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Image
                </th>
                <th
                  @click="sortTable('name')"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                >
                  <div class="flex items-center gap-1">
                    Name
                    <svg v-if="sortBy === 'name'" class="w-4 h-4" :class="sortOrder === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>
                <th
                  @click="sortTable('sku')"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                >
                  <div class="flex items-center gap-1">
                    SKU
                    <svg v-if="sortBy === 'sku'" class="w-4 h-4" :class="sortOrder === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>
                <th
                  @click="sortTable('price')"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                >
                  <div class="flex items-center gap-1">
                    Price
                    <svg v-if="sortBy === 'price'" class="w-4 h-4" :class="sortOrder === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>
                <th
                  @click="sortTable('quantity')"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                >
                  <div class="flex items-center gap-1">
                    Stock
                    <svg v-if="sortBy === 'quantity'" class="w-4 h-4" :class="sortOrder === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="products.data.length === 0">
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                  <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p class="text-lg font-medium">No products found</p>
                    <p class="text-sm text-gray-400 mt-1">Get started by creating your first product</p>
                  </div>
                </td>
              </tr>
              <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <input
                    v-model="selectedProducts"
                    type="checkbox"
                    :value="product.id"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                </td>
                <td class="px-6 py-4">
                  <div v-if="product.main_image" class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100">
                    <img :src="product.main_image.url" :alt="product.name" class="w-full h-full object-cover" />
                  </div>
                  <div v-else class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-start gap-2">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                      <div v-if="product.categories.length > 0" class="text-xs text-gray-500 mt-0.5">
                        {{ product.categories.map(c => c.name).join(', ') }}
                      </div>
                      <div class="flex gap-2 mt-1">
                        <span v-if="product.featured" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                          Featured
                        </span>
                        <span v-if="product.new" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                          New
                        </span>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm">
                  <div class="flex flex-col gap-1">
                    <span class="text-gray-900">{{ product.sku || '-' }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium w-fit" :class="{
                      'bg-gray-100 text-gray-800': product.type === 'simple',
                      'bg-purple-100 text-purple-800': product.type === 'configurable',
                      'bg-blue-100 text-blue-800': product.type === 'virtual',
                      'bg-cyan-100 text-cyan-800': product.type === 'downloadable',
                    }">
                      <svg v-if="product.type === 'virtual' || product.type === 'downloadable'" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z" />
                        <path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z" />
                        <path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z" />
                      </svg>
                      {{ product.type.charAt(0).toUpperCase() + product.type.slice(1) }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  <div v-if="product.special_price" class="flex flex-col">
                    <span class="font-semibold text-red-600">{{ formatPrice(product.special_price) }}</span>
                    <span class="text-xs text-gray-500 line-through">{{ formatPrice(product.price) }}</span>
                  </div>
                  <span v-else class="font-semibold">{{ formatPrice(product.price) }}</span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <div class="flex items-center gap-2">
                    <span class="font-medium" :class="{
                      'text-green-600': product.stock_status === 'in_stock',
                      'text-red-600': product.stock_status === 'out_of_stock',
                      'text-yellow-600': product.stock_status === 'on_backorder',
                    }">
                      {{ product.quantity }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="{
                      'bg-green-100 text-green-800': product.stock_status === 'in_stock',
                      'bg-red-100 text-red-800': product.stock_status === 'out_of_stock',
                      'bg-yellow-100 text-yellow-800': product.stock_status === 'on_backorder',
                    }">
                      {{ product.stock_status.replace('_', ' ') }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                    'bg-green-100 text-green-800': product.status === 'enabled',
                    'bg-gray-100 text-gray-800': product.status === 'disabled',
                  }">
                    {{ product.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      @click="openQuickView(product)"
                      class="text-purple-600 hover:text-purple-900 cursor-pointer"
                      title="Quick View"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <Link
                      :href="productRoutes.edit({ product: product.id }).url"
                      class="text-blue-600 hover:text-blue-900"
                      title="Edit"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </Link>
                    <button
                      @click="confirmDelete(product.id)"
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

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Input } from '@/components/ui/input';
import { Plus, Search, ToggleLeft, ToggleRight } from 'lucide-vue-next';
import { useCurrency } from '@/composables/useCurrency';

interface Coupon {
  id: number;
  code: string;
  name: string;
  type: string;
  value: number;
  is_active: boolean;
  is_public: boolean;
  usage_count: number;
  usage_limit_total: number | null;
  start_date: string;
  end_date: string | null;
  created_at: string;
}

interface Stats {
  total: number;
  active: number;
  used_today: number;
  total_discount: number;
}

interface Props {
  coupons: {
    data: Coupon[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  stats: Stats;
  filters: {
    search?: string;
    status?: string;
    type?: string;
  };
}

const props = defineProps<Props>();

const selectedCoupons = ref<number[]>([]);
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const typeFilter = ref(props.filters.type || 'all');

const { formatPrice } = useCurrency();

const allSelected = computed(() => {
  return props.coupons.data.length > 0 && selectedCoupons.value.length === props.coupons.data.length;
});

const someSelected = computed(() => {
  return selectedCoupons.value.length > 0 && selectedCoupons.value.length < props.coupons.data.length;
});

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedCoupons.value = [];
  } else {
    selectedCoupons.value = props.coupons.data.map(c => c.id);
  }
};

const toggleSelect = (id: number) => {
  const index = selectedCoupons.value.indexOf(id);
  if (index > -1) {
    selectedCoupons.value.splice(index, 1);
  } else {
    selectedCoupons.value.push(id);
  }
};

const applyFilters = () => {
  router.get('/admin/marketing/coupons', {
    search: searchQuery.value || undefined,
    status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    type: typeFilter.value !== 'all' ? typeFilter.value : undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const createCoupon = () => {
  router.visit('/admin/marketing/coupons/create');
};

const deleteCoupon = (id: number) => {
  if (confirm('Are you sure you want to delete this coupon?')) {
    router.delete(`/admin/marketing/coupons/${id}`, {
      preserveScroll: true,
      onSuccess: () => {
        selectedCoupons.value = selectedCoupons.value.filter(cid => cid !== id);
      },
    });
  }
};

const bulkActivate = () => {
  if (selectedCoupons.value.length === 0) return;
  
  router.post('/admin/marketing/coupons/bulk-activate', {
    coupon_ids: selectedCoupons.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedCoupons.value = [];
    },
  });
};

const bulkDeactivate = () => {
  if (selectedCoupons.value.length === 0) return;
  
  router.post('/admin/marketing/coupons/bulk-deactivate', {
    coupon_ids: selectedCoupons.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedCoupons.value = [];
    },
  });
};

const bulkDelete = () => {
  if (selectedCoupons.value.length === 0) return;
  
  if (confirm(`Are you sure you want to delete ${selectedCoupons.value.length} coupon(s)?`)) {
    router.post('/admin/marketing/coupons/bulk-delete', {
      coupon_ids: selectedCoupons.value,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        selectedCoupons.value = [];
      },
    });
  }
};

const getTypeLabel = (type: string) => {
  const types: Record<string, string> = {
    percentage: 'Percentage',
    fixed_amount: 'Fixed Amount',
    free_shipping: 'Free Shipping',
    buy_x_get_y: 'Buy X Get Y',
    fixed_price: 'Fixed Price',
  };
  return types[type] || type;
};

const getTypeColor = (type: string) => {
  const colors: Record<string, string> = {
    percentage: 'bg-blue-100 text-blue-800',
    fixed_amount: 'bg-green-100 text-green-800',
    free_shipping: 'bg-purple-100 text-purple-800',
    buy_x_get_y: 'bg-orange-100 text-orange-800',
    fixed_price: 'bg-pink-100 text-pink-800',
  };
  return colors[type] || 'bg-gray-100 text-gray-800';
};

const formatDiscount = (coupon: Coupon) => {
  switch (coupon.type) {
    case 'percentage':
      return `${coupon.value}%`;
    case 'fixed_amount':
      return `$${coupon.value}`;
    case 'free_shipping':
      return 'Free';
    case 'fixed_price':
      return `$${coupon.value}`;
    default:
      return coupon.value;
  }
};

const formatDate = (date: string | null) => {
  if (!date) return 'No expiry';
  return new Date(date).toLocaleDateString();
};

const clearFilters = () => {
  searchQuery.value = '';
  statusFilter.value = 'all';
  typeFilter.value = 'all';
  applyFilters();
};

const isExpired = (endDate: string | null) => {
  if (!endDate) return false;
  return new Date(endDate) < new Date();
};
</script>

<template>
  <AdminLayout title="Coupons">
    <div class="p-6 space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Coupons</h1>
          <p class="mt-1 text-sm text-gray-600">Manage discount coupons and promotional codes</p>
        </div>
        <button
          @click="createCoupon"
          class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
        >
          <Plus class="h-5 w-5" />
          Create Coupon
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="grid gap-4 md:grid-cols-4">
        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="text-sm font-medium text-gray-600 mb-2">Total Coupons</div>
          <div class="text-2xl font-bold text-gray-900">{{ stats.total }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="text-sm font-medium text-gray-600 mb-2">Active</div>
          <div class="text-2xl font-bold text-green-600">{{ stats.active }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="text-sm font-medium text-gray-600 mb-2">Used Today</div>
          <div class="text-2xl font-bold text-gray-900">{{ stats.used_today }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="text-sm font-medium text-gray-600 mb-2">Total Discounts</div>
          <div class="text-2xl font-bold text-gray-900">{{ formatPrice(stats.total_discount) }}</div>
        </div>
      </div>

      <!-- Filters and Search -->
      <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <div class="relative">
              <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-400" />
              <Input
                v-model="searchQuery"
                placeholder="Search coupons..."
                class="pl-8"
                @keyup.enter="applyFilters"
              />
            </div>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select 
              v-model="statusFilter" 
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="all">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="expired">Expired</option>
            </select>
          </div>

          <!-- Type Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select 
              v-model="typeFilter" 
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="all">All Types</option>
              <option value="percentage">Percentage</option>
              <option value="fixed_amount">Fixed Amount</option>
              <option value="free_shipping">Free Shipping</option>
              <option value="buy_x_get_y">Buy X Get Y</option>
              <option value="fixed_price">Fixed Price</option>
            </select>
          </div>
        </div>

        <!-- Clear Filters -->
        <div v-if="searchQuery || statusFilter !== 'all' || typeFilter !== 'all'" class="mt-3 flex justify-end">
          <button
            @click="clearFilters"
            class="text-sm text-gray-600 hover:text-gray-900"
          >
            Clear Filters
          </button>
        </div>
      </div>

      <!-- Bulk Actions -->
      <div v-if="selectedCoupons.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-blue-900">
            {{ selectedCoupons.length }} {{ selectedCoupons.length === 1 ? 'coupon' : 'coupons' }} selected
          </span>
          <div class="flex gap-2">
            <button
              @click="bulkActivate"
              class="flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-green-700 bg-white border border-green-300 rounded-lg hover:bg-green-50"
            >
              <ToggleRight class="h-4 w-4" />
              Activate
            </button>
            <button
              @click="bulkDeactivate"
              class="flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              <ToggleLeft class="h-4 w-4" />
              Deactivate
            </button>
            <button
              @click="bulkDelete"
              class="flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-red-700 bg-white border border-red-300 rounded-lg hover:bg-red-50"
            >
              <Trash2 class="h-4 w-4" />
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="overflow-x-auto">
          <div class="rounded-md border">
            <table class="w-full caption-bottom text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left">
                    <input
                      type="checkbox"
                      :checked="allSelected"
                      @change="toggleSelectAll"
                      class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valid Until</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="coupons.data.length === 0">
                  <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                    No coupons found
                  </td>
                </tr>
                <tr v-for="coupon in coupons.data" :key="coupon.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input
                      type="checkbox"
                      :value="coupon.id"
                      v-model="selectedCoupons"
                      class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center space-x-2">
                      <code class="font-mono font-semibold text-sm">{{ coupon.code }}</code>
                      <span v-if="coupon.is_public" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Public</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ coupon.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getTypeColor(coupon.type)]">
                      {{ getTypeLabel(coupon.type) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatDiscount(coupon) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span v-if="coupon.usage_limit_total">
                      {{ coupon.usage_count }} / {{ coupon.usage_limit_total }}
                    </span>
                    <span v-else>{{ coupon.usage_count }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-if="isExpired(coupon.end_date)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Expired</span>
                    <span v-else-if="coupon.is_active" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm" :class="{ 'text-red-600': isExpired(coupon.end_date), 'text-gray-500': !isExpired(coupon.end_date) }">
                    {{ formatDate(coupon.end_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Link
                        :href="`/admin/marketing/coupons/${coupon.id}/edit`"
                        class="text-blue-600 hover:text-blue-900"
                        title="Edit"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </Link>
                      <button
                        @click="deleteCoupon(coupon.id)"
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
          <div v-if="coupons.last_page > 1" class="flex items-center justify-between mt-4">
            <div class="text-sm text-muted-foreground">
              Showing {{ ((coupons.current_page - 1) * coupons.per_page) + 1 }} 
              to {{ Math.min(coupons.current_page * coupons.per_page, coupons.total) }} 
              of {{ coupons.total }} coupons
            </div>
            <div class="flex space-x-2">
              <Button
                variant="outline"
                size="sm"
                :disabled="coupons.current_page === 1"
                @click="router.get(`/admin/marketing/coupons?page=${coupons.current_page - 1}`)"
              >
                Previous
              </Button>
              <Button
                variant="outline"
                size="sm"
                :disabled="coupons.current_page === coupons.last_page"
                @click="router.get(`/admin/marketing/coupons?page=${coupons.current_page + 1}`)"
              >
                Next
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

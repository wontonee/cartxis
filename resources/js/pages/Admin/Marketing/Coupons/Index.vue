<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Input } from '@/components/ui/input';
import {
  PlusCircle,
  Search,
  Filter,
  Trash2,
  Edit,
  CheckCircle,
  X,
  ArrowUpDown,
  Ticket,
  Percent,
  DollarSign,
  Truck,
  ShoppingBag,
  Calendar,
  ChevronLeft,
  ChevronRight,
  MinusCircle,
  Eye
} from 'lucide-vue-next';
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
    from: number;
    to: number;
    prev_page_url: string | null;
    next_page_url: string | null;
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
const expandedRows = ref<number[]>([]);

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

const toggleRow = (id: number) => {
  if (expandedRows.value.includes(id)) {
    expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
  } else {
    expandedRows.value.push(id);
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
    percentage: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
    fixed_amount: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
    free_shipping: 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300',
    buy_x_get_y: 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300',
    fixed_price: 'bg-pink-100 dark:bg-pink-900/30 text-pink-800 dark:text-pink-300',
  };
  return colors[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
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
  <Head title="Coupons" />

  <AdminLayout title="Coupons">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Coupons</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage discount coupons and promotional codes</p>
        </div>
        <Link
          href="/admin/marketing/coupons/create"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <PlusCircle class="w-4 h-4 mr-2" />
          Create Coupon
        </Link>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Coupons</p>
              <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg group-hover:bg-blue-100 dark:group-hover:bg-blue-900/40 transition-colors">
              <Ticket class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-green-200 dark:hover:border-green-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active</p>
              <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.active }}</p>
            </div>
            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg group-hover:bg-green-100 dark:group-hover:bg-green-900/40 transition-colors">
              <CheckCircle class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-purple-200 dark:hover:border-purple-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Used Today</p>
              <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.used_today }}</p>
            </div>
            <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg group-hover:bg-purple-100 dark:group-hover:bg-purple-900/40 transition-colors">
              <ShoppingBag class="w-6 h-6 text-purple-600 dark:text-purple-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-orange-200 dark:hover:border-orange-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Discounts</p>
              <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ formatPrice(stats.total_discount) }}</p>
            </div>
            <div class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg group-hover:bg-orange-100 dark:group-hover:bg-orange-900/40 transition-colors">
              <DollarSign class="w-6 h-6 text-orange-600 dark:text-orange-400" />
            </div>
          </div>
        </div>
      </div>

      <!-- Filters & Actions -->
      <div class="flex flex-col gap-4">
        <!-- Search & Filter Grid -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- Search -->
            <div class="md:col-span-6 lg:col-span-4">
              <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
              <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                  type="text"
                  v-model="searchQuery"
                  placeholder="Code, name..."
                  class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
                  @keyup.enter="applyFilters"
                />
              </div>
            </div>

            <!-- Status Filter -->
            <div class="md:col-span-3 lg:col-span-2">
              <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
              <div class="relative">
                <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <select
                  v-model="statusFilter"
                  @change="applyFilters"
                  class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
                >
                  <option value="all">All Status</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                  <ArrowUpDown class="w-3 h-3 text-gray-400" />
                </div>
              </div>
            </div>

            <!-- Type Filter -->
            <div class="md:col-span-3 lg:col-span-2">
              <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Type</label>
              <div class="relative">
                <Ticket class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <select
                  v-model="typeFilter"
                  @change="applyFilters"
                  class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
                >
                  <option value="all">All Types</option>
                  <option value="percentage">Percentage</option>
                  <option value="fixed_amount">Fixed Amount</option>
                  <option value="free_shipping">Free Shipping</option>
                  <option value="buy_x_get_y">Buy X Get Y</option>
                </select>
                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                  <ArrowUpDown class="w-3 h-3 text-gray-400" />
                </div>
              </div>
            </div>
          </div>
          
          <!-- Filter Actions -->
          <div v-if="searchQuery || statusFilter !== 'all' || typeFilter !== 'all'" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
            <button
              @click="clearFilters"
              class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors flex items-center gap-2"
            >
              <X class="w-4 h-4" />
              Clear Filters
            </button>
          </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div
          v-if="someSelected || allSelected"
          class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 flex flex-wrap items-center justify-between gap-4 transition-all duration-300"
        >
          <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-blue-700 dark:text-blue-300">
              {{ selectedCoupons.length }} selected
            </span>
          </div>
          <div class="flex items-center gap-2">
             <button
              @click="bulkActivate"
              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60"
            >
              <CheckCircle class="w-4 h-4 mr-1.5" />
              Activate
            </button>
            <button
              @click="bulkDeactivate"
              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-orange-700 bg-orange-100 hover:bg-orange-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:bg-orange-900/40 dark:text-orange-300 dark:hover:bg-orange-900/60"
            >
              <X class="w-4 h-4 mr-1.5" />
              Deactivate
            </button>
            <button
              @click="bulkDelete"
              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60"
            >
              <Trash2 class="w-4 h-4 mr-1.5" />
              Delete Selected
            </button>
          </div>
        </div>
      </div>

      <!-- Content Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left">
                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      :checked="allSelected"
                      :indeterminate="someSelected"
                      @change="toggleSelectAll"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600"
                    />
                  </div>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Details
                </th>
                <th scope="col" class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Type
                </th>
                <th scope="col" class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Discount
                </th>
                <th scope="col" class="hidden lg:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Usage
                </th>
                 <th scope="col" class="hidden lg:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Valid Until
                </th>
                <th scope="col" class="hidden sm:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <template v-for="coupon in coupons.data" :key="coupon.id">
                <tr :class="['group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors', {'bg-blue-50 dark:bg-blue-900/10': selectedCoupons.includes(coupon.id)}]">
                  <td class="px-6 py-4 whitespace-nowrap relative">
                    <div class="flex items-center">
                      <button
                        @click="toggleRow(coupon.id)"
                        class="md:hidden absolute left-2 p-1 text-blue-600 hover:text-blue-800 focus:outline-none"
                      >
                        <MinusCircle v-if="expandedRows.includes(coupon.id)" class="w-5 h-5 fill-blue-100 dark:fill-blue-900/30" />
                        <PlusCircle v-else class="w-5 h-5 fill-blue-50 dark:fill-blue-900/20" />
                      </button>
                      <input
                        type="checkbox"
                        :checked="selectedCoupons.includes(coupon.id)"
                        @change="toggleSelect(coupon.id)"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600 ml-4 md:ml-0"
                      />
                    </div>
                  </td>
                  
                  <!-- Details -->
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ coupon.code }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ coupon.name }}</div>
                      </div>
                    </div>
                  </td>

                  <!-- Type -->
                  <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                    <span 
                      class="inline-flex px-2 py-1 text-xs font-medium rounded-md"
                      :class="getTypeColor(coupon.type)"
                    >
                      {{ getTypeLabel(coupon.type) }}
                    </span>
                  </td>

                  <!-- Discount -->
                  <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">
                    {{ formatDiscount(coupon) }}
                  </td>

                  <!-- Usage -->
                  <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    <div class="flex flex-col">
                      <span>{{ coupon.usage_count }} used</span>
                      <span class="text-xs text-gray-400" v-if="coupon.usage_limit_total">
                        of {{ coupon.usage_limit_total }} limit
                      </span>
                    </div>
                  </td>

                   <!-- Dates -->
                  <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    <div :class="{'text-red-500 font-medium': isExpired(coupon.end_date)}">
                       {{ formatDate(coupon.end_date) }}
                       <span v-if="isExpired(coupon.end_date)" class="block text-xs text-red-500">Expired</span>
                    </div>
                  </td>

                  <!-- Status -->
                  <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
                    <span
                      class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                      :class="coupon.is_active
                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                    >
                      {{ coupon.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>

                  <!-- Actions -->
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                      <Link 
                        :href="`/admin/marketing/coupons/${coupon.id}/edit`"
                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                      >
                        <Edit class="w-4 h-4" />
                      </Link>
                      <button 
                        @click="deleteCoupon(coupon.id)"
                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                      >
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>

                 <!-- Mobile Expanded Row -->
                  <tr v-if="expandedRows.includes(coupon.id)" class="md:hidden bg-gray-50 dark:bg-gray-700/30">
                    <td colspan="7" class="px-6 py-4 space-y-3">
                       <div class="grid grid-cols-2 gap-4 text-sm">
                          <div>
                            <span class="block text-xs font-medium text-gray-500 uppercase">Type</span>
                            <span class="inline-flex mt-1 px-2 py-1 text-xs font-medium rounded-md" :class="getTypeColor(coupon.type)">
                              {{ getTypeLabel(coupon.type) }}
                            </span>
                          </div>
                          <div>
                            <span class="block text-xs font-medium text-gray-500 uppercase">Discount</span>
                            <span class="block mt-1 text-gray-900 dark:text-white font-medium">{{ formatDiscount(coupon) }}</span>
                          </div>
                          <div>
                            <span class="block text-xs font-medium text-gray-500 uppercase">Usage</span>
                             <div class="mt-1 text-gray-500 dark:text-gray-400">
                                <span>{{ coupon.usage_count }} used</span>
                                <span class="text-xs text-gray-400" v-if="coupon.usage_limit_total">
                                  of {{ coupon.usage_limit_total }} limit
                                </span>
                             </div>
                          </div>
                          <div>
                             <span class="block text-xs font-medium text-gray-500 uppercase">Status</span>
                             <span
                                class="inline-flex mt-1 px-2 py-1 text-xs font-medium rounded-full"
                                :class="coupon.is_active
                                  ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                                  : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                              >
                                {{ coupon.is_active ? 'Active' : 'Inactive' }}
                              </span>
                          </div>
                           <div class="col-span-2">
                             <span class="block text-xs font-medium text-gray-500 uppercase">Valid Until</span>
                              <div class="mt-1" :class="{'text-red-500 font-medium': isExpired(coupon.end_date)}">
                                 {{ formatDate(coupon.end_date) }}
                              </div>
                          </div>
                       </div>
                    </td>
                  </tr>
              </template>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
          <div class="flex items-center justify-between">
             <div class="text-sm text-gray-500 dark:text-gray-400">
               Showing {{ coupons.from }} to {{ coupons.to }} of {{ coupons.total }} results
             </div>
             <div class="flex gap-2">
                <Link
                  v-if="coupons.prev_page_url"
                  :href="coupons.prev_page_url"
                  class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300"
                >
                  Previous
                </Link>
                <Link
                  v-if="coupons.next_page_url"
                  :href="coupons.next_page_url"
                  class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300"
                >
                  Next
                </Link>
             </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';
import { useCurrency } from '@/composables/useCurrency';
import { 
  Search, Filter, X, CheckCircle, Download, Plus, 
  PlusCircle, MinusCircle, ArrowUp, ArrowDown, ArrowUpDown,
  CreditCard, Calendar, Truck, User, ShoppingBag, Eye 
} from 'lucide-vue-next';

interface Order {
  id: number;
  order_number: string;
  user?: {
    id: number;
    name: string;
    email: string;
  };
  customer_email: string;
  status: string;
  payment_status: string;
  payment_method: string;
  shipping_method: string;
  total: number;
  created_at: string;
  items_count?: number;
}

interface Props {
  orders: {
    data: Order[];
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
    payment_status?: string;
    payment_method?: string;
    date_from?: string;
    date_to?: string;
    sort_by?: string;
    sort_order?: string;
  };
  statuses: Array<{ value: string; label: string }>;
  paymentStatuses: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const { formatPrice } = useCurrency();

const selectedOrders = ref<number[]>([]);
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const paymentStatusFilter = ref(props.filters.payment_status || '');
const paymentMethodFilter = ref(props.filters.payment_method || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortOrder = ref(props.filters.sort_order || 'desc');

const showBulkModal = ref(false);
const bulkStatus = ref('');
const bulkNotifyCustomer = ref(false);
const expandedRows = ref<number[]>([]);

const selectAll = computed({
  get: () => selectedOrders.value.length === props.orders.data.length && props.orders.data.length > 0,
  set: (value: boolean) => {
    selectedOrders.value = value ? props.orders.data.map(o => o.id) : [];
  }
});

const someSelected = computed(() => {
  return selectedOrders.value.length > 0 && selectedOrders.value.length < props.orders.data.length;
});

function toggleRow(id: number) {
  if (expandedRows.value.includes(id)) {
    expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
  } else {
    expandedRows.value.push(id);
  }
}

const performSearch = debounce(() => {
  applyFilters();
}, 300);

function applyFilters() {
  router.get('/admin/sales/orders', {
    search: search.value || undefined,
    status: statusFilter.value || undefined,
    payment_status: paymentStatusFilter.value || undefined,
    payment_method: paymentMethodFilter.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
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
  paymentStatusFilter.value = '';
  paymentMethodFilter.value = '';
  dateFrom.value = '';
  dateTo.value = '';
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

function openBulkModal() {
  if (selectedOrders.value.length === 0) return;
  showBulkModal.value = true;
}

function bulkUpdateStatus() {
  if (!bulkStatus.value || selectedOrders.value.length === 0) return;

  router.post('/admin/sales/orders/bulk/status', {
    order_ids: selectedOrders.value,
    status: bulkStatus.value,
    notify_customer: bulkNotifyCustomer.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedOrders.value = [];
      showBulkModal.value = false;
      bulkStatus.value = '';
      bulkNotifyCustomer.value = false;
    },
  });
}

function exportOrders() {
  window.location.href = `/admin/sales/orders/export/csv?${new URLSearchParams({
    search: search.value || '',
    status: statusFilter.value || '',
    payment_status: paymentStatusFilter.value || '',
    date_from: dateFrom.value || '',
    date_to: dateTo.value || '',
  }).toString()}`;
}

function getStatusBadge(status: string): string {
  const badges: Record<string, string> = {
    pending: 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
    processing: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
    completed: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800',
    cancelled: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
    refunded: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800',
    failed: 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
  };
  return badges[status] || badges.pending;
}

function getPaymentStatusBadge(status: string): string {
  const badges: Record<string, string> = {
    pending: 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
    paid: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800',
    failed: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
    refunded: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800',
  };
  return badges[status] || badges.pending;
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}
</script>

<template>
  <Head title="Orders" />

  <AdminLayout title="Orders">
    <div class="p-6 space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Orders</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage customer orders and shipments</p>
        </div>
        <div class="flex gap-2">
          <button
            @click="exportOrders"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            <Download class="w-4 h-4" />
            Export
          </button>
          <Link
            href="/admin/sales/orders/create"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
          >
            <Plus class="w-4 h-4" />
            Create Order
          </Link>
        </div>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
          <!-- Search -->
          <div class="lg:col-span-2">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="search"
                @input="performSearch"
                type="text"
                placeholder="Order #, email, customer..."
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
              />
            </div>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Filter class="h-4 w-4 text-gray-400" />
              </div>
              <select
                v-model="statusFilter"
                @change="applyFilters"
                class="w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
              >
                <option value="">All Status</option>
                <option v-for="status in statuses" :key="status.value" :value="status.value">
                  {{ status.label }}
                </option>
              </select>
               <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <ArrowUpDown class="w-3 h-3 text-gray-400" />
              </div>
            </div>
          </div>

          <!-- Payment Status Filter -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Payment</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <CreditCard class="h-4 w-4 text-gray-400" />
              </div>
              <select
                v-model="paymentStatusFilter"
                @change="applyFilters"
                class="w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
              >
                <option value="">All Payments</option>
                <option v-for="status in paymentStatuses" :key="status.value" :value="status.value">
                  {{ status.label }}
                </option>
              </select>
              <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <ArrowUpDown class="w-3 h-3 text-gray-400" />
              </div>
            </div>
          </div>

          <!-- Date From -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">From Date</label>
            <div class="relative">
              <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="dateFrom"
                @change="applyFilters"
                type="date"
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
              />
            </div>
          </div>

          <!-- Date To -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">To Date</label>
            <div class="relative">
               <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="dateTo"
                @change="applyFilters"
                type="date"
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
              />
            </div>
          </div>
        </div>

        <!-- Clear Filters -->
        <div v-if="search || statusFilter || paymentStatusFilter || dateFrom || dateTo" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
          <button
            @click="clearFilters"
             class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors flex items-center gap-2"
          >
            <X class="w-4 h-4" />
            Clear Filters
          </button>
        </div>
      </div>

      <!-- Bulk Actions -->
      <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="selectedOrders.length > 0" class="bg-blue-600 rounded-xl shadow-lg p-3 text-white flex items-center justify-between sticky top-4 z-10 px-6">
          <span class="text-sm font-semibold flex items-center">
            <CheckCircle class="w-4 h-4 mr-2" />
             {{ selectedOrders.length }} {{ selectedOrders.length === 1 ? 'order' : 'orders' }} selected
          </span>
          <div class="flex gap-2">
            <button
              @click="openBulkModal"
              class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-white rounded-lg hover:bg-blue-50 transition-colors uppercase tracking-wide"
            >
              Update Status
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
                    v-model="selectAll"
                    :indeterminate.prop="someSelected"
                     class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 transition-all"
                  />
                </th>
                <th
                  scope="col"
                  class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                  @click="sortTable('order_number')"
                >
                  <div class="flex items-center gap-1">
                    Order #
                     <span v-if="sortBy === 'order_number'" class="text-blue-600 dark:text-blue-400">
                       <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                       <ArrowDown v-else class="w-3 h-3" />
                    </span>
                    <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                  </div>
                </th>
                <th scope="col" class="hidden md:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Customer
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="hidden lg:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Payment
                </th>
                <th
                  scope="col"
                  class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                  @click="sortTable('total')"
                >
                  <div class="flex items-center gap-1">
                    Total
                     <span v-if="sortBy === 'total'" class="text-blue-600 dark:text-blue-400">
                       <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                       <ArrowDown v-else class="w-3 h-3" />
                    </span>
                    <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                  </div>
                </th>
                <th
                  scope="col"
                  class="hidden sm:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                  @click="sortTable('created_at')"
                >
                  <div class="flex items-center gap-1">
                    Date
                     <span v-if="sortBy === 'created_at'" class="text-blue-600 dark:text-blue-400">
                       <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                       <ArrowDown v-else class="w-3 h-3" />
                    </span>
                    <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                  </div>
                </th>
                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
              <template v-for="order in orders.data" :key="order.id">
              <tr class="group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                <td class="px-6 py-4 relative">
                   <div class="flex items-center">
                    <button 
                      @click="toggleRow(order.id)" 
                      class="lg:hidden absolute left-2 p-1 text-blue-600 hover:text-blue-800 focus:outline-none"
                    >
                      <MinusCircle v-if="expandedRows.includes(order.id)" class="w-5 h-5 fill-blue-100 dark:fill-blue-900/30" />
                      <PlusCircle v-else class="w-5 h-5 fill-blue-50 dark:fill-blue-900/20" />
                    </button>
                    <input
                      type="checkbox"
                      :value="order.id"
                      v-model="selectedOrders"
                      class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer transition-all ml-4 lg:ml-0"
                    />
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col">
                    <Link
                      :href="`/admin/sales/orders/${order.id}`"
                      class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline"
                    >
                      {{ order.order_number }}
                    </Link>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ order.items_count || 1 }} items</span>
                  </div>
                </td>
                <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 mr-3">
                      <User class="w-4 h-4" />
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">{{ order.user?.name || 'Guest' }}</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">{{ order.customer_email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm', getStatusBadge(order.status)]">
                    {{ order.status }}
                  </span>
                </td>
                <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap">
                   <div class="flex flex-col gap-1">
                    <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium w-fit', getPaymentStatusBadge(order.payment_status)]">
                      {{ order.payment_status }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ order.payment_method }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                  {{ formatPrice(order.total) }}
                </td>
                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                   <div class="flex items-center gap-1.5">
                    <Calendar class="w-3.5 h-3.5 text-gray-400" />
                    {{ formatDate(order.created_at) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                    <Link
                      :href="`/admin/sales/orders/${order.id}`"
                      class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                      title="View Order"
                    >
                      <Eye class="w-4 h-4" />
                    </Link>
                  </div>
                </td>
              </tr>
              <!-- Expanded Mobile Row -->
              <tr v-if="expandedRows.includes(order.id)" class="bg-gray-50/50 dark:bg-gray-900/50 lg:hidden">
                 <td colspan="8" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                       <div class="md:hidden flex flex-col gap-2">
                          <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Customer</span>
                          <div class="flex items-center">
                            <div class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 mr-3">
                              <User class="w-4 h-4" />
                            </div>
                            <div>
                              <div class="text-sm font-medium text-gray-900 dark:text-white">{{ order.user?.name || 'Guest' }}</div>
                              <div class="text-xs text-gray-500 dark:text-gray-400">{{ order.customer_email }}</div>
                            </div>
                          </div>
                       </div>
                       <div class="lg:hidden flex flex-col gap-2">
                          <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Payment</span>
                           <div class="flex items-center gap-3">
                             <div class="flex flex-col">
                                <span class="text-xs text-gray-500 mb-0.5">Status</span>
                                <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium w-fit', getPaymentStatusBadge(order.payment_status)]">
                                  {{ order.payment_status }}
                                </span>
                             </div>
                             <div class="flex flex-col">
                                <span class="text-xs text-gray-500 mb-0.5">Method</span>
                                <span class="text-gray-700 dark:text-gray-300 font-medium">{{ order.payment_method }}</span>
                             </div>
                           </div>
                       </div>
                       <div class="sm:hidden flex flex-col gap-1">
                          <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Date</span>
                          <div class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300">
                            <Calendar class="w-3.5 h-3.5 text-gray-400" />
                            {{ formatDate(order.created_at) }}
                          </div>
                       </div>
                    </div>
                 </td>
              </tr>
              </template>
              <tr v-if="orders.data.length === 0">
                <td colspan="8" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                      <ShoppingBag class="w-8 h-8" />
                    </div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">No orders found</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm">Try adjusting your filters or create a new order.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <Pagination :data="orders" resource-name="orders" />
      </div>
    </div>

    <!-- Bulk Update Modal -->
    <div v-if="showBulkModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500/75 dark:bg-gray-900/75" @click="showBulkModal = false"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Bulk Update Status</h3>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Status</label>
                <select
                  v-model="bulkStatus"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Select status...</option>
                  <option v-for="status in statuses" :key="status.value" :value="status.value">
                    {{ status.label }}
                  </option>
                </select>
              </div>
              <div class="flex items-center">
                <input
                  type="checkbox"
                  v-model="bulkNotifyCustomer"
                  class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700"
                />
                <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Notify customers</label>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-700">
            <button
              @click="bulkUpdateStatus"
              :disabled="!bulkStatus"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Update Orders
            </button>
            <button
              @click="showBulkModal = false"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

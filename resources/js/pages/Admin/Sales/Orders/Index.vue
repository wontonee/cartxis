<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';
import { useCurrency } from '@/composables/useCurrency';

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

const selectAll = computed({
  get: () => selectedOrders.value.length === props.orders.data.length && props.orders.data.length > 0,
  set: (value: boolean) => {
    selectedOrders.value = value ? props.orders.data.map(o => o.id) : [];
  }
});

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
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    refunded: 'bg-purple-100 text-purple-800',
    failed: 'bg-gray-100 text-gray-800',
  };
  return badges[status] || badges.pending;
}

function getPaymentStatusBadge(status: string): string {
  const badges: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    refunded: 'bg-purple-100 text-purple-800',
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
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Orders</h1>
          <p class="mt-1 text-sm text-gray-600">Manage customer orders</p>
        </div>
        <div class="flex gap-2">
          <button
            @click="exportOrders"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export CSV
          </button>
          <Link
            href="/admin/sales/orders/create"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Order
          </Link>
        </div>
      </div>

      <!-- Filters Card -->
      <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="search"
              @input="performSearch"
              type="text"
              placeholder="Order #, customer email, name..."
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
              <option v-for="status in statuses" :key="status.value" :value="status.value">
                {{ status.label }}
              </option>
            </select>
          </div>

          <!-- Payment Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment</label>
            <select
              v-model="paymentStatusFilter"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Payments</option>
              <option v-for="status in paymentStatuses" :key="status.value" :value="status.value">
                {{ status.label }}
              </option>
            </select>
          </div>

          <!-- Date From -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
            <input
              v-model="dateFrom"
              @change="applyFilters"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Date To -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
            <input
              v-model="dateTo"
              @change="applyFilters"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>

        <!-- Clear Filters -->
        <div v-if="search || statusFilter || paymentStatusFilter || dateFrom || dateTo" class="mt-3 flex justify-end">
          <button
            @click="clearFilters"
            class="text-sm text-gray-600 hover:text-gray-900"
          >
            Clear Filters
          </button>
        </div>
      </div>

      <!-- Bulk Actions -->
      <div v-if="selectedOrders.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-blue-900">
            {{ selectedOrders.length }} {{ selectedOrders.length === 1 ? 'order' : 'orders' }} selected
          </span>
          <div class="flex gap-2">
            <button
              @click="openBulkModal"
              class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
            >
              Update Status
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left">
                  <input
                    type="checkbox"
                    v-model="selectAll"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortTable('order_number')">
                  <div class="flex items-center gap-1">
                    Order #
                    <svg v-if="sortBy === 'order_number'" class="w-4 h-4" :class="sortOrder === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Customer
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Payment
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortTable('total')">
                  <div class="flex items-center gap-1">
                    Total
                    <svg v-if="sortBy === 'total'" class="w-4 h-4" :class="sortOrder === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortTable('created_at')">
                  <div class="flex items-center gap-1">
                    Date
                    <svg v-if="sortBy === 'created_at'" class="w-4 h-4" :class="sortOrder === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    :value="order.id"
                    v-model="selectedOrders"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <Link
                    :href="`/admin/sales/orders/${order.id}`"
                    class="text-sm font-medium text-blue-600 hover:text-blue-900"
                  >
                    {{ order.order_number }}
                  </Link>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ order.user?.name || 'Guest' }}</div>
                  <div class="text-sm text-gray-500">{{ order.customer_email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getStatusBadge(order.status)]">
                    {{ order.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getPaymentStatusBadge(order.payment_status)]">
                    {{ order.payment_status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatPrice(order.total) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(order.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="`/admin/sales/orders/${order.id}`"
                      class="text-blue-600 hover:text-blue-900 cursor-pointer"
                      title="View Order"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </Link>
                  </div>
                </td>
              </tr>
              <tr v-if="orders.data.length === 0">
                <td colspan="8" class="px-6 py-12 text-center">
                  <div class="text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="mt-2 text-sm">No orders found</p>
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
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Update Status</h3>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Status</label>
                <select
                  v-model="bulkStatus"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
                  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <label class="ml-2 text-sm text-gray-700">Notify customers</label>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="bulkUpdateStatus"
              :disabled="!bulkStatus"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Update Orders
            </button>
            <button
              @click="showBulkModal = false"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

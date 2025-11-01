<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';
import { debounce } from 'lodash';

interface Shipment {
  id: number;
  shipment_number: string;
  status: string;
  carrier: string | null;
  tracking_number: string | null;
  shipped_at: string | null;
  delivered_at: string | null;
  created_at: string;
  order: {
    id: number;
    order_number: string;
    customer_email: string;
    user?: {
      name: string;
    };
  };
}

interface Props {
  shipments: {
    data: Shipment[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  filters: {
    search?: string;
    status?: string;
    date_from?: string;
    date_to?: string;
  };
  statistics: {
    total: number;
    pending: number;
    shipped: number;
    in_transit: number;
    out_for_delivery: number;
    delivered: number;
    failed: number;
    cancelled: number;
  };
  statuses: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const dateFromFilter = ref(props.filters.date_from || '');
const dateToFilter = ref(props.filters.date_to || '');

const performSearch = debounce(() => {
  applyFilters();
}, 300);

const applyFilters = () => {
  router.get('/admin/sales/shipments', {
    search: searchQuery.value,
    status: statusFilter.value,
    date_from: dateFromFilter.value,
    date_to: dateToFilter.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  searchQuery.value = '';
  statusFilter.value = '';
  dateFromFilter.value = '';
  dateToFilter.value = '';
  router.get('/admin/sales/shipments');
};

const getStatusBadge = (status: string) => {
  const badges: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
    shipped: 'bg-blue-100 text-blue-800 border border-blue-200',
    in_transit: 'bg-indigo-100 text-indigo-800 border border-indigo-200',
    out_for_delivery: 'bg-purple-100 text-purple-800 border border-purple-200',
    delivered: 'bg-green-100 text-green-800 border border-green-200',
    failed: 'bg-red-100 text-red-800 border border-red-200',
    cancelled: 'bg-gray-100 text-gray-800 border border-gray-200',
  };
  return badges[status] || badges.pending;
};

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const viewShipment = (id: number) => {
  router.visit(`/admin/sales/shipments/${id}`);
};
</script>

<template>
  <Head title="Shipments - Sales" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Shipments</h1>
        <p class="text-gray-600 mt-1">Manage order shipments and tracking</p>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Total</p>
              <p class="text-2xl font-bold text-gray-900">{{ statistics.total }}</p>
            </div>
            <div class="p-3 bg-gray-100 rounded-lg">
              <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Pending</p>
              <p class="text-2xl font-bold text-yellow-600">{{ statistics.pending }}</p>
            </div>
            <div class="p-3 bg-yellow-100 rounded-lg">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">In Transit</p>
              <p class="text-2xl font-bold text-indigo-600">{{ statistics.in_transit }}</p>
            </div>
            <div class="p-3 bg-indigo-100 rounded-lg">
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Delivered</p>
              <p class="text-2xl font-bold text-green-600">{{ statistics.delivered }}</p>
            </div>
            <div class="p-3 bg-green-100 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="searchQuery"
              @input="performSearch"
              type="text"
              placeholder="Shipment #, Order #, Tracking..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="statusFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @change="applyFilters"
            >
              <option value="">All Statuses</option>
              <option v-for="status in statuses" :key="status.value" :value="status.value">
                {{ status.label }}
              </option>
            </select>
          </div>

          <!-- Date From -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
            <input
              v-model="dateFromFilter"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @change="applyFilters"
            />
          </div>

          <!-- Date To -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
            <input
              v-model="dateToFilter"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @change="applyFilters"
            />
          </div>
        </div>

        <!-- Clear Filters -->
        <div v-if="searchQuery || statusFilter || dateFromFilter || dateToFilter" class="mt-3 flex justify-end">
          <button
            @click="clearFilters"
            class="text-sm text-gray-600 hover:text-gray-900"
          >
            Clear Filters
          </button>
        </div>
      </div>

      <!-- Shipments Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Shipment #
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Order #
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Customer
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Carrier
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tracking #
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
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
              <tr
                v-for="shipment in shipments.data"
                :key="shipment.id"
                class="hover:bg-gray-50 cursor-pointer"
                @click="viewShipment(shipment.id)"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-blue-600">{{ shipment.shipment_number }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ shipment.order.order_number }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">
                    {{ shipment.order.user?.name || 'Guest' }}
                  </div>
                  <div class="text-sm text-gray-500">{{ shipment.order.customer_email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ shipment.carrier || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ shipment.tracking_number || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ shipment.shipped_at ? formatDate(shipment.shipped_at) : formatDate(shipment.created_at) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['px-3 py-1 inline-flex text-sm font-semibold rounded-full', getStatusBadge(shipment.status)]">
                    {{ shipment.status.replace('_', ' ') }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click.stop="viewShipment(shipment.id)"
                    class="text-blue-600 hover:text-blue-900"
                    title="View Shipment"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="shipments.data.length === 0" class="text-center py-12">
          <svg
            class="mx-auto h-12 w-12 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
            />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No shipments found</h3>
          <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or create a new shipment from an order.</p>
        </div>

        <!-- Pagination -->
        <div v-if="shipments.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing <span class="font-medium">{{ ((shipments.current_page - 1) * shipments.per_page) + 1 }}</span>
              to <span class="font-medium">{{ Math.min(shipments.current_page * shipments.per_page, shipments.total) }}</span>
              of <span class="font-medium">{{ shipments.total }}</span> results
            </div>
            <div class="flex space-x-2">
              <button
                v-if="shipments.current_page > 1"
                @click="router.get('/admin/sales/shipments', { ...filters, page: shipments.current_page - 1 })"
                class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Previous
              </button>
              <button
                v-if="shipments.current_page < shipments.last_page"
                @click="router.get('/admin/sales/shipments', { ...filters, page: shipments.current_page + 1 })"
                class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

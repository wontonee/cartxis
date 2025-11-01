<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import type { Transaction, TransactionFilters, TransactionStatistics, StatusOption, PaginatedResponse } from '@/types/sales';
import { useCurrency } from '@/composables/useCurrency';

const props = defineProps<{
  transactions: PaginatedResponse<Transaction>;
  filters: TransactionFilters;
  statistics: TransactionStatistics;
  types: StatusOption[];
  statuses: StatusOption[];
  gateways: StatusOption[];
}>();

const { formatPrice } = useCurrency();

const filters = ref<TransactionFilters>({
  search: props.filters.search || '',
  type: props.filters.type || '',
  status: props.filters.status || '',
  gateway: props.filters.gateway || '',
  payment_method: props.filters.payment_method || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
  amount_min: props.filters.amount_min,
  amount_max: props.filters.amount_max,
  sort_by: props.filters.sort_by || 'created_at',
  sort_order: props.filters.sort_order || 'desc',
});

const showFilters = ref(false);

const applyFilters = () => {
  router.get('/admin/sales/transactions', filters.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  filters.value = {
    search: '',
    type: '',
    status: '',
    gateway: '',
    payment_method: '',
    date_from: '',
    date_to: '',
    amount_min: undefined,
    amount_max: undefined,
    sort_by: 'created_at',
    sort_order: 'desc',
  };
  applyFilters();
};

const handleSearch = () => {
  applyFilters();
};

const viewTransaction = (id: number) => {
  router.visit(`/admin/sales/transactions/${id}`);
};

const exportTransactions = () => {
  window.location.href = `/admin/sales/transactions/export?${new URLSearchParams(filters.value as any).toString()}`;
};

const getStatusBadgeClass = (status: string) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    completed: 'bg-green-100 text-green-800 border-green-200',
    failed: 'bg-red-100 text-red-800 border-red-200',
    cancelled: 'bg-gray-100 text-gray-800 border-gray-200',
  };
  return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const getTypeBadgeClass = (type: string) => {
  const classes = {
    payment: 'bg-blue-100 text-blue-800 border-blue-200',
    refund: 'bg-purple-100 text-purple-800 border-purple-200',
    authorization: 'bg-indigo-100 text-indigo-800 border-indigo-200',
    capture: 'bg-teal-100 text-teal-800 border-teal-200',
  };
  return classes[type as keyof typeof classes] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

<template>
  <AdminLayout>
    <div class="container-fluid">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Transactions</h1>
        <p class="text-gray-600 mt-1">View and manage payment transactions</p>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Total Transactions</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">{{ statistics.total }}</p>
            </div>
            <div class="p-3 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Completed</p>
              <p class="text-2xl font-bold text-green-600 mt-1">{{ statistics.completed }}</p>
            </div>
            <div class="p-3 bg-green-100 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Pending</p>
              <p class="text-2xl font-bold text-yellow-600 mt-1">{{ statistics.pending }}</p>
            </div>
            <div class="p-3 bg-yellow-100 rounded-lg">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Failed</p>
              <p class="text-2xl font-bold text-red-600 mt-1">{{ statistics.failed }}</p>
            </div>
            <div class="p-3 bg-red-100 rounded-lg">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment/Refund Summary -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-sm font-medium text-gray-900 mb-4">Payments</h3>
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Count:</span>
              <span class="text-sm font-medium text-gray-900">{{ statistics.payment_count }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Total Amount:</span>
              <span class="text-sm font-medium text-green-600">{{ formatPrice(statistics.payment_amount) }}</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-sm font-medium text-gray-900 mb-4">Refunds</h3>
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Count:</span>
              <span class="text-sm font-medium text-gray-900">{{ statistics.refund_count }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Total Amount:</span>
              <span class="text-sm font-medium text-purple-600">{{ formatPrice(statistics.refund_amount) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters and Actions -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-4 border-b border-gray-200">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Search -->
            <div class="flex-1">
              <div class="relative">
                <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                  v-model="filters.search"
                  @input="handleSearch"
                  type="text"
                  placeholder="Search by transaction #, order #, gateway transaction ID..."
                  class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>

            <!-- Quick Filters -->
            <div class="flex items-center gap-2">
              <select
                v-model="filters.type"
                @change="applyFilters"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">All Types</option>
                <option v-for="type in types" :key="type.value" :value="type.value">
                  {{ type.label }}
                </option>
              </select>

              <select
                v-model="filters.status"
                @change="applyFilters"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">All Statuses</option>
                <option v-for="status in statuses" :key="status.value" :value="status.value">
                  {{ status.label }}
                </option>
              </select>

              <select
                v-model="filters.gateway"
                @change="applyFilters"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">All Gateways</option>
                <option v-for="gateway in gateways" :key="gateway.value" :value="gateway.value">
                  {{ gateway.label }}
                </option>
              </select>

              <button
                @click="showFilters = !showFilters"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
              </button>

              <button
                @click="resetFilters"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700"
              >
                Reset
              </button>

              <button
                @click="exportTransactions"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export
              </button>
            </div>
          </div>

          <!-- Advanced Filters -->
          <div v-if="showFilters" class="mt-4 pt-4 border-t border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input
                  v-model="filters.date_from"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input
                  v-model="filters.date_to"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Min Amount</label>
                <input
                  v-model.number="filters.amount_min"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Amount</label>
                <input
                  v-model.number="filters.amount_max"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                />
              </div>
            </div>
            <div class="mt-4">
              <button
                @click="applyFilters"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                Apply Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Transactions Table -->
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Transaction #
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Order #
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Type
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Gateway
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Amount
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
                v-for="transaction in transactions.data"
                :key="transaction.id"
                class="hover:bg-gray-50 cursor-pointer"
                @click="viewTransaction(transaction.id)"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-medium text-gray-900">{{ transaction.transaction_number }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-blue-600 hover:text-blue-800">
                    {{ transaction.order?.order_number || 'N/A' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-900">{{ formatDate(transaction.created_at) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="getTypeBadgeClass(transaction.type)"
                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border"
                  >
                    {{ transaction.type }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-900 capitalize">{{ transaction.gateway }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-medium text-gray-900">{{ formatPrice(transaction.amount) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="getStatusBadgeClass(transaction.status)"
                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border"
                  >
                    {{ transaction.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click.stop="viewTransaction(transaction.id)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </td>
              </tr>
              <tr v-if="transactions.data.length === 0">
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                  No transactions found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <Pagination :data="transactions" resource-name="transactions" />
      </div>
    </div>
  </AdminLayout>
</template>

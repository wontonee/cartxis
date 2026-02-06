<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Transaction, TransactionFilters, TransactionStatistics, StatusOption, PaginatedResponse } from '@/types/sales';
import { useCurrency } from '@/composables/useCurrency';
import {
  Search,
  Filter,
  Download,
  Calendar,
  ArrowUpDown,
  ChevronLeft,
  ChevronRight,
  Eye,
  CheckCircle,
  X,
  FileText,
  User,
  PlusCircle,
  MinusCircle,
  CreditCard,
  DollarSign,
  Clock,
  CheckSquare,
  AlertCircle,
  Ban
} from 'lucide-vue-next';

const props = defineProps<{
  transactions: PaginatedResponse<Transaction>;
  filters: TransactionFilters;
  statistics: TransactionStatistics;
  types: StatusOption[];
  statuses: StatusOption[];
  gateways: StatusOption[];
}>();

const { formatPrice } = useCurrency();

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');
const statusFilter = ref(props.filters.status || '');
const gatewayFilter = ref(props.filters.gateway || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const amountMin = ref(props.filters.amount_min || '');
const amountMax = ref(props.filters.amount_max || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortOrder = ref(props.filters.sort_order || 'desc');

// Bulk actions state
const selectAll = ref(false);
const selectedIds = ref<number[]>([]);
const expandedRows = ref<number[]>([]);

const applyFilters = () => {
  router.get('/admin/sales/transactions', {
    search: search.value,
    type: typeFilter.value,
    status: statusFilter.value,
    gateway: gatewayFilter.value,
    date_from: dateFrom.value,
    date_to: dateTo.value,
    amount_min: amountMin.value || undefined,
    amount_max: amountMax.value || undefined,
    sort_by: sortBy.value,
    sort_order: sortOrder.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  search.value = '';
  typeFilter.value = '';
  statusFilter.value = '';
  gatewayFilter.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  amountMin.value = '';
  amountMax.value = '';
  applyFilters();
};

const toggleRow = (id: number) => {
  const index = expandedRows.value.indexOf(id);
  if (index === -1) {
    expandedRows.value.push(id);
  } else {
    expandedRows.value.splice(index, 1);
  }
};

const someSelected = computed(() => {
  return selectedIds.value.length > 0 && selectedIds.value.length < props.transactions.data.length;
});

watch(selectAll, (value) => {
  if (value) {
    selectedIds.value = props.transactions.data.map(t => t.id);
  } else {
    selectedIds.value = [];
  }
});

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const getStatusBadge = (status: string) => {
  const badges: Record<string, string> = {
    pending: 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
    completed: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800',
    failed: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
    cancelled: 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-900/20 dark:text-gray-300 dark:border-gray-800',
  };
  return badges[status] || 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600';
};

const getTypeBadge = (type: string) => {
  const badges: Record<string, string> = {
    payment: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
    refund: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800',
    authorization: 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/20 dark:text-indigo-300 dark:border-indigo-800',
    capture: 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-900/20 dark:text-teal-300 dark:border-teal-800',
  };
  return badges[type] || 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600';
};

const viewTransaction = (id: number) => {
  router.visit(`/admin/sales/transactions/${id}`);
};

const exportTransactions = () => {
    // Construct query params manually or use a helper
    const params = new URLSearchParams({
        search: search.value,
        type: typeFilter.value,
        status: statusFilter.value,
        gateway: gatewayFilter.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
        amount_min: amountMin.value?.toString() || '',
        amount_max: amountMax.value?.toString() || '',
        sort_by: sortBy.value,
        sort_order: sortOrder.value,
    });
  window.location.href = `/admin/sales/transactions/export?${params.toString()}`;
};
</script>

<template>
  <Head title="Transactions" />

  <AdminLayout title="Transactions">
    <div class="p-6 space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Transactions</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">View and manage payment transactions</p>
        </div>
        <button
          @click="exportTransactions"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <Download class="w-4 h-4 mr-2" />
          Export
        </button>
      </div>

     <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Transactions</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.total }}</p>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg group-hover:bg-blue-100 dark:group-hover:bg-blue-900/40 transition-colors">
              <FileText class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-green-200 dark:hover:border-green-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Completed</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.completed }}</p>
            </div>
            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
              <CheckSquare class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-yellow-200 dark:hover:border-yellow-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.pending }}</p>
            </div>
            <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
              <Clock class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-red-200 dark:hover:border-red-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Failed</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.failed }}</p>
            </div>
            <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
              <AlertCircle class="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
          </div>
        </div>
      </div>

      <!-- Payment/Refund Summary -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
           <div class="flex items-center justify-between mb-4">
              <h3 class="text-sm font-medium text-gray-900 dark:text-white">Payments</h3>
               <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                 <DollarSign class="w-4 h-4 text-blue-600 dark:text-blue-400" />
               </div>
           </div>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Count</span>
              <span class="text-sm font-bold text-gray-900 dark:text-white">{{ statistics.payment_count }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Total Amount</span>
              <span class="text-sm font-bold text-green-600 dark:text-green-400">{{ formatPrice(statistics.payment_amount) }}</span>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <div class="flex items-center justify-between mb-4">
              <h3 class="text-sm font-medium text-gray-900 dark:text-white">Refunds</h3>
               <div class="p-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                 <RefreshCw class="w-4 h-4 text-purple-600 dark:text-purple-400" />
               </div>
           </div>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Count</span>
              <span class="text-sm font-bold text-gray-900 dark:text-white">{{ statistics.refund_count }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-400">Total Amount</span>
              <span class="text-sm font-bold text-purple-600 dark:text-purple-400">{{ formatPrice(statistics.refund_amount) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="lg:col-span-1">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="search"
                @input="applyFilters"
                type="text"
                placeholder="Transaction #, Order #..."
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
              />
            </div>
          </div>

          <!-- Type Filter -->
          <div>
             <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Type</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Filter class="h-4 w-4 text-gray-400" />
              </div>
              <select
                v-model="typeFilter"
                @change="applyFilters"
                class="w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
              >
                <option value="">All Types</option>
                <option v-for="type in types" :key="type.value" :value="type.value">
                  {{ type.label }}
                </option>
              </select>
               <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <ArrowUpDown class="w-3 h-3 text-gray-400" />
              </div>
            </div>
          </div>

          <!-- Status Filter -->
          <div>
             <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <CheckCircle class="h-4 w-4 text-gray-400" />
              </div>
              <select
                v-model="statusFilter"
                @change="applyFilters"
                class="w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
              >
                <option value="">All Statuses</option>
                <option v-for="status in statuses" :key="status.value" :value="status.value">
                  {{ status.label }}
                </option>
              </select>
               <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <ArrowUpDown class="w-3 h-3 text-gray-400" />
              </div>
            </div>
          </div>

          <!-- Gateway Filter -->
          <div>
             <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Gateway</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <CreditCard class="h-4 w-4 text-gray-400" />
              </div>
               <select
                v-model="gatewayFilter"
                @change="applyFilters"
                class="w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
              >
                <option value="">All Gateways</option>
                <option v-for="gateway in gateways" :key="gateway.value" :value="gateway.value">
                  {{ gateway.label }}
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
          
           <!-- Min Amount -->
          <div>
             <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Min Amount</label>
            <div class="relative">
              <DollarSign class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="amountMin"
                @change="applyFilters"
                type="number"
                step="0.01"
                placeholder="0.00"
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
              />
            </div>
          </div>

          <!-- Max Amount -->
          <div>
             <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Max Amount</label>
            <div class="relative">
              <DollarSign class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="amountMax"
                @change="applyFilters"
                type="number"
                step="0.01"
                placeholder="0.00"
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
              />
            </div>
          </div>
        </div>

        <!-- Clear filters -->
         <div v-if="search || typeFilter || statusFilter || gatewayFilter || dateFrom || dateTo || amountMin || amountMax" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
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
        <div v-if="selectedIds.length > 0" class="bg-blue-600 rounded-xl shadow-lg p-3 text-white flex items-center justify-between sticky top-4 z-10 px-6 mb-6">
          <span class="text-sm font-semibold flex items-center">
            <CheckCircle class="w-4 h-4 mr-2" />
             {{ selectedIds.length }} selected
          </span>
          <div class="flex gap-2">
            <!-- Add bulk actions here if needed -->
          </div>
        </div>
      </transition>

      <!-- Transactions Table -->
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
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Transaction #
                </th>
                <th scope="col" class="hidden sm:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Order #
                </th>
                <th scope="col" class="hidden xl:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Date
                </th>
                <th scope="col" class="hidden md:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Type
                </th>
                <th scope="col" class="hidden lg:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Gateway
                </th>
                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Amount
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
              <template v-for="transaction in transactions.data" :key="transaction.id">
              <tr class="group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                <td class="px-6 py-4 relative">
                   <div class="flex items-center">
                    <button
                      @click="toggleRow(transaction.id)"
                      class="lg:hidden absolute left-2 p-1 text-blue-600 hover:text-blue-800 focus:outline-none"
                    >
                      <MinusCircle v-if="expandedRows.includes(transaction.id)" class="w-5 h-5 fill-blue-100 dark:fill-blue-900/30" />
                      <PlusCircle v-else class="w-5 h-5 fill-blue-50 dark:fill-blue-900/20" />
                    </button>
                    <input
                      type="checkbox"
                      :value="transaction.id"
                      v-model="selectedIds"
                      class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer transition-all ml-4 lg:ml-0"
                    />
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-blue-600 dark:text-blue-400">
                    {{ transaction.transaction_number }}
                  </div>
                </td>
                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
                  <Link 
                    v-if="transaction.order" 
                    :href="`/admin/sales/orders/${transaction.order.id}`"
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:underline"
                  >
                    {{ transaction.order.order_number }}
                  </Link>
                  <span v-else class="text-sm text-gray-500 dark:text-gray-400">N/A</span>
                </td>
                <td class="hidden xl:table-cell px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(transaction.created_at) }}</span>
                </td>
                <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                  <span
                    :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm', getTypeBadge(transaction.type)]"
                  >
                    {{ transaction.type }}
                  </span>
                </td>
                <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-900 dark:text-white capitalize">{{ transaction.gateway }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <span class="text-sm font-bold text-gray-900 dark:text-white">{{ formatPrice(transaction.amount) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm', getStatusBadge(transaction.status)]"
                  >
                    {{ transaction.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                   <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                      <button
                        @click="viewTransaction(transaction.id)"
                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                        title="View Details"
                      >
                         <Eye class="w-4 h-4" />
                      </button>
                   </div>
                </td>
              </tr>
              <!-- Expanded Mobile Row -->
              <tr v-if="expandedRows.includes(transaction.id)" class="bg-gray-50/50 dark:bg-gray-900/50 lg:hidden">
                 <td colspan="9" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="sm:hidden flex flex-col gap-2">
                           <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Order Reference</span>
                           <Link v-if="transaction.order" :href="`/admin/sales/orders/${transaction.order.id}`" class="text-sm text-blue-600 dark:text-blue-400 font-medium">
                              {{ transaction.order.order_number }}
                           </Link>
                           <span v-else class="text-sm text-gray-500">N/A</span>
                       </div>
                       <div class="md:hidden flex flex-col gap-2">
                           <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Type</span>
                            <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm w-fit', getTypeBadge(transaction.type)]">
                                {{ transaction.type }}
                            </span>
                        </div>
                        <div class="lg:hidden flex flex-col gap-2">
                            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Gateway</span>
                            <span class="text-sm text-gray-900 dark:text-white capitalize">{{ transaction.gateway }}</span>
                        </div>
                         <div class="xl:hidden flex flex-col gap-2">
                            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Date</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(transaction.created_at) }}</span>
                        </div>
                    </div>
                 </td>
              </tr>
              </template>
              <tr v-if="transactions.data.length === 0">
                <td colspan="9" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                   <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                      <CreditCard class="w-8 h-8" />
                    </div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">No transactions found</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm">Try adjusting your filters.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50/50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
          <div class="text-xs text-gray-500 dark:text-gray-400">
            Showing <span class="font-medium">{{ transactions.from || 0 }}</span> to <span class="font-medium">{{ transactions.to || 0 }}</span> of <span class="font-medium">{{ transactions.total }}</span> results
          </div>
           <div class="flex gap-2">
            <Link
              v-if="transactions.prev_page_url"
              :href="transactions.prev_page_url"
              class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-1"
            >
              <ChevronLeft class="w-3 h-3" />
              Previous
            </Link>
             <button
              v-else
              disabled
              class="px-3 py-1.5 text-xs font-medium text-gray-400 dark:text-gray-600 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg cursor-not-allowed flex items-center gap-1 opacity-50"
            >
              <ChevronLeft class="w-3 h-3" />
              Previous
            </button>
            
            <Link
              v-if="transactions.next_page_url"
              :href="transactions.next_page_url"
              class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-1"
            >
              Next
              <ChevronRight class="w-3 h-3" />
            </Link>
             <button
              v-else
              disabled
              class="px-3 py-1.5 text-xs font-medium text-gray-400 dark:text-gray-600 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg cursor-not-allowed flex items-center gap-1 opacity-50"
            >
              Next
              <ChevronRight class="w-3 h-3" />
            </button>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>

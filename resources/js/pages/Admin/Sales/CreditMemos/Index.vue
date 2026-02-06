<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref, computed, watch } from 'vue';
import { debounce } from 'lodash';
import { useCurrency } from '@/composables/useCurrency';
import type { CreditMemo, CreditMemoFilters, CreditMemoStatistics, PaginatedResponse, StatusOption } from '@/types/sales';
import {
  Search,
  Filter,
  Download,
  Calendar,
  ArrowUpDown,
  ArrowUp,
  ArrowDown,
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
  AlertCircle,
  CheckSquare,
  Clock,
  Printer,
  Mail,
  RefreshCw,
  Ban
} from 'lucide-vue-next';

interface Props {
  creditMemos: PaginatedResponse<CreditMemo>;
  filters: CreditMemoFilters;
  statistics: CreditMemoStatistics;
  statuses: StatusOption[];
}

const props = defineProps<Props>();
const { formatPrice } = useCurrency();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const refundMethodFilter = ref(props.filters.refund_method || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const minAmount = ref(props.filters.min_amount || '');
const maxAmount = ref(props.filters.max_amount || '');
const sortBy = ref('created_at'); // Assuming default sort, update if props.filters has it
const sortOrder = ref('desc');

// Bulk actions state
const selectAll = ref(false);
const selectedIds = ref<number[]>([]);
const expandedRows = ref<number[]>([]);

const performSearch = debounce(() => {
  applyFilters();
}, 300);

const applyFilters = () => {
  router.get('/admin/sales/credit-memos', {
    search: search.value,
    status: statusFilter.value,
    refund_method: refundMethodFilter.value || undefined,
    date_from: dateFrom.value,
    date_to: dateTo.value,
    min_amount: minAmount.value || undefined,
    max_amount: maxAmount.value || undefined,
    // Add sorting params if backend supports it
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  search.value = '';
  statusFilter.value = '';
  refundMethodFilter.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  minAmount.value = '';
  maxAmount.value = '';
  applyFilters();
};

const sortTable = (field: string) => {
  // If your backend supports sorting, implement this
  console.log('Sort by', field);
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
  return selectedIds.value.length > 0 && selectedIds.value.length < props.creditMemos.data.length;
});

watch(selectAll, (value) => {
  if (value) {
    selectedIds.value = props.creditMemos.data.map(cm => cm.id);
  } else {
    selectedIds.value = [];
  }
});

const getStatusBadge = (status: string) => {
  const badges: Record<string, string> = {
    pending: 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
    refunded: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800',
    cancelled: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
  };
  return badges[status] || 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600';
};

const getRefundMethodBadge = (method: string) => {
  return method === 'online' 
    ? 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800' 
    : 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800';
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const downloadPdf = (id: number) => {
  window.open(`/admin/sales/credit-memos/${id}/download-pdf`, '_blank');
};

const sendEmail = (creditMemoId: number) => {
  router.post(`/admin/sales/credit-memos/${creditMemoId}/send-email`, {}, {
    preserveScroll: true,
  });
};

const processRefund = (id: number) => {
  router.post(`/admin/sales/credit-memos/${id}/process-refund`, {}, {
    preserveScroll: true,
  });
};

const cancelCreditMemo = (id: number) => {
  router.post(`/admin/sales/credit-memos/${id}/cancel`, {}, {
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="Credit Memos" />

  <AdminLayout title="Credit Memos">
    <div class="p-6 space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Credit Memos</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage refunds and credit memos</p>
        </div>
        <Link
          href="/admin/sales/orders"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <PlusCircle class="w-4 h-4 mr-2" />
          Create Credit Memo
        </Link>
      </div>

     <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.total }}</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg group-hover:bg-blue-50 dark:group-hover:bg-blue-900/20 transition-colors">
              <FileText class="w-6 h-6 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" />
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

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-green-200 dark:hover:border-green-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Refunded</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.refunded }}</p>
            </div>
            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
              <CheckSquare class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-purple-200 dark:hover:border-purple-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Amount</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ formatPrice(statistics.total_amount) }}</p>
            </div>
            <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
              <DollarSign class="w-6 h-6 text-purple-600 dark:text-purple-400" />
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
                @input="performSearch"
                type="text"
                placeholder="Credit Memo #, Order #..."
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

          <!-- Refund Method -->
          <div>
             <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Refund Method</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <CreditCard class="h-4 w-4 text-gray-400" />
              </div>
              <select
                v-model="refundMethodFilter"
                @change="applyFilters"
                class="w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
              >
                <option value="">All Methods</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
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
        </div>
        
        <!-- Advanced / clear filters -->
         <div v-if="search || statusFilter || refundMethodFilter || dateFrom || dateTo || minAmount || maxAmount" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
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
        <div v-if="selectedIds.length > 0" class="bg-blue-600 rounded-xl shadow-lg p-3 text-white flex items-center justify-between sticky top-4 z-10 px-6">
          <span class="text-sm font-semibold flex items-center">
            <CheckCircle class="w-4 h-4 mr-2" />
             {{ selectedIds.length }} selected
          </span>
          <div class="flex gap-2">
            <!-- Add bulk actions here -->
          </div>
        </div>
      </transition>

      <!-- Credit Memos Table -->
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
                  Credit Memo
                </th>
                <th scope="col" class="hidden sm:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Order
                </th>
                <th scope="col" class="hidden md:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Customer
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="hidden lg:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Method
                </th>
                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Amount
                </th>
                <th scope="col" class="hidden xl:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Date
                </th>
                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
              <template v-for="creditMemo in creditMemos.data" :key="creditMemo.id">
              <tr class="group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                 <td class="px-6 py-4 relative">
                   <div class="flex items-center">
                    <button
                      @click="toggleRow(creditMemo.id)"
                      class="lg:hidden absolute left-2 p-1 text-blue-600 hover:text-blue-800 focus:outline-none"
                    >
                      <MinusCircle v-if="expandedRows.includes(creditMemo.id)" class="w-5 h-5 fill-blue-100 dark:fill-blue-900/30" />
                      <PlusCircle v-else class="w-5 h-5 fill-blue-50 dark:fill-blue-900/20" />
                    </button>
                    <input
                      type="checkbox"
                      :value="creditMemo.id"
                      v-model="selectedIds"
                      class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer transition-all ml-4 lg:ml-0"
                    />
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-blue-600 dark:text-blue-400">
                    {{ creditMemo.credit_memo_number }}
                  </div>
                </td>
                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
                   <div class="text-sm text-gray-600 dark:text-gray-300">
                      {{ creditMemo.order?.order_number || '-' }}
                   </div>
                </td>
                <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                   <div class="flex items-center">
                     <div class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 mr-3">
                      <User class="w-4 h-4" />
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ creditMemo.order?.user?.name || 'Guest' }}
                      </div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">{{ creditMemo.order?.customer_email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm', getStatusBadge(creditMemo.status)]">
                    {{ creditMemo.status }}
                  </span>
                </td>
                 <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap">
                  <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border shadow-sm', getRefundMethodBadge(creditMemo.refund_method)]">
                    {{ creditMemo.refund_method }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="text-sm font-bold text-gray-900 dark:text-white">{{ formatPrice(creditMemo.grand_total) }}</div>
                </td>
                <td class="hidden xl:table-cell px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(creditMemo.created_at) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                   <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                      <button
                        v-if="creditMemo.status === 'pending'"
                        @click="processRefund(creditMemo.id)"
                        class="p-2 text-green-600 hover:text-green-800 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors"
                        title="Process Refund"
                      >
                         <RefreshCw class="w-4 h-4" />
                      </button>
                      <button
                        @click="downloadPdf(creditMemo.id)"
                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                        title="Download PDF"
                      >
                        <Download class="w-4 h-4" />
                      </button>
                      <button
                        @click="sendEmail(creditMemo.id)"
                        class="p-2 text-gray-400 hover:text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors"
                        title="Send Email"
                      >
                         <Mail class="w-4 h-4" />
                      </button>
                      <Link
                        :href="`/admin/sales/credit-memos/${creditMemo.id}`"
                         class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                        title="View"
                      >
                         <Eye class="w-4 h-4" />
                      </Link>
                   </div>
                </td>
              </tr>
               <!-- Expanded Mobile Row -->
              <tr v-if="expandedRows.includes(creditMemo.id)" class="bg-gray-50/50 dark:bg-gray-900/50 lg:hidden">
                 <td colspan="9" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="sm:hidden flex flex-col gap-2">
                          <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Order Reference</span>
                           <span class="text-sm text-gray-600 dark:text-gray-300">
                              {{ creditMemo.order?.order_number || '-' }}
                            </span>
                       </div>
                       <div class="md:hidden flex flex-col gap-2">
                           <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Customer</span>
                           <div class="flex items-center">
                               <div class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 mr-3">
                                <User class="w-4 h-4" />
                              </div>
                              <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                  {{ creditMemo.order?.user?.name || 'Guest' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ creditMemo.order?.customer_email }}</div>
                              </div>
                           </div>
                        </div>
                        <div class="lg:hidden flex flex-col gap-2">
                            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Details</span>
                            <div class="grid grid-cols-2 gap-4">
                               <div>
                                  <span class="text-xs text-gray-500 block">Method</span>
                                  <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border shadow-sm mt-1', getRefundMethodBadge(creditMemo.refund_method)]">
                                    {{ creditMemo.refund_method }}
                                  </span>
                               </div>
                               <div>
                                  <span class="text-xs text-gray-500 block">Date</span>
                                  <span class="text-gray-700 dark:text-gray-300 mt-1 block">{{ formatDate(creditMemo.created_at) }}</span>
                               </div>
                            </div>
                        </div>
                    </div>
                 </td>
              </tr>
              </template>

              <tr v-if="creditMemos.data.length === 0">
                 <td colspan="9" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                      <FileText class="w-8 h-8" />
                    </div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">No credit memos found</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm">Try adjusting your filters or create a new one.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50/50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
          <div class="text-xs text-gray-500 dark:text-gray-400">
            Showing <span class="font-medium">{{ creditMemos.from || 0 }}</span> to <span class="font-medium">{{ creditMemos.to || 0 }}</span> of <span class="font-medium">{{ creditMemos.total }}</span> results
          </div>
          <div class="flex gap-2">
            <Link
              v-if="creditMemos.prev_page_url"
              :href="creditMemos.prev_page_url"
              class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-1"
            >
              <ChevronLeft class="w-3 h-3" />
              Previous
            </Link>
            <Link
              v-if="creditMemos.next_page_url"
              :href="creditMemos.next_page_url"
              class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-1"
            >
              Next
              <ChevronRight class="w-3 h-3" />
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

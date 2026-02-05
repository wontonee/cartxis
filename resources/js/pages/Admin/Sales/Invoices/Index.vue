<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref, computed, watch } from 'vue';
import { debounce } from 'lodash';
import { useCurrency } from '@/composables/useCurrency';
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
  Printer
} from 'lucide-vue-next';

interface Invoice {
  id: number;
  invoice_number: string;
  status: string;
  issue_date: string;
  due_date: string | null;
  total: number;
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
  invoices: {
    data: Invoice[];
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
    min_amount?: number;
    max_amount?: number;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
  };
  statuses: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();
const { formatPrice } = useCurrency();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const sortBy = ref(props.filters.sort_by || 'issue_date');
const sortOrder = ref(props.filters.sort_order || 'desc');

// Bulk actions state
const selectAll = ref(false);
const selectedIds = ref<number[]>([]);
const expandedRows = ref<number[]>([]);

const performSearch = debounce(() => {
  applyFilters();
}, 300);

const applyFilters = () => {
  router.get('/admin/sales/invoices', {
    search: search.value,
    status: statusFilter.value,
    date_from: dateFrom.value,
    date_to: dateTo.value,
    sort_by: sortBy.value,
    sort_order: sortOrder.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  search.value = '';
  statusFilter.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  applyFilters();
};

const sortTable = (field: string) => {
  if (sortBy.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = field;
    sortOrder.value = 'asc';
  }
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
  return selectedIds.value.length > 0 && selectedIds.value.length < props.invoices.data.length;
});

watch(selectAll, (value) => {
  if (value) {
    selectedIds.value = props.invoices.data.map(i => i.id);
  } else {
    selectedIds.value = [];
  }
});

const exportInvoices = () => {
  // Implementation for export
  console.log('Exporting invoices...');
};

const getStatusBadge = (status: string) => {
  const badges: Record<string, string> = {
    pending: 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
    sent: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
    paid: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800',
    cancelled: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
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
</script>

<template>
  <Head title="Invoices" />

  <AdminLayout title="Invoices">
    <div class="p-6 space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Invoices</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage customer invoices and payments</p>
        </div>
        <div class="flex gap-2">
           <button
            @click="exportInvoices"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            <Download class="w-4 h-4" />
            Export
          </button>
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
                placeholder="Invoice #, Order #..."
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
        <div v-if="search || statusFilter || dateFrom || dateTo" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
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
             <button
              class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-white rounded-lg hover:bg-blue-50 transition-colors uppercase tracking-wide flex items-center gap-2"
            >
              <Printer class="w-3 h-3" />
              Print
            </button>
             <button
              class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-white rounded-lg hover:bg-blue-50 transition-colors uppercase tracking-wide flex items-center gap-2"
            >
              <Download class="w-3 h-3" />
              Download PDF
            </button>
          </div>
        </div>
      </transition>

      <!-- Invoices Table -->
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
                  @click="sortTable('invoice_number')"
                >
                   <div class="flex items-center gap-1">
                    Invoice #
                     <span v-if="sortBy === 'invoice_number'" class="text-blue-600 dark:text-blue-400">
                       <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                       <ArrowDown v-else class="w-3 h-3" />
                    </span>
                    <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                  </div>
                </th>
                <th scope="col" class="hidden sm:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Order #
                </th>
                <th scope="col" class="hidden md:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Customer
                </th>
                <th
                  scope="col"
                  class="hidden lg:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                  @click="sortTable('issue_date')"
                >
                  <div class="flex items-center gap-1">
                    Issue Date
                     <span v-if="sortBy === 'issue_date'" class="text-blue-600 dark:text-blue-400">
                       <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                       <ArrowDown v-else class="w-3 h-3" />
                    </span>
                    <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                  </div>
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th
                  scope="col"
                  class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                   @click="sortTable('total')"
                >
                   <div class="flex items-center justify-end gap-1">
                    Total
                     <span v-if="sortBy === 'total'" class="text-blue-600 dark:text-blue-400">
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
              <template v-for="invoice in invoices.data" :key="invoice.id">
              <tr class="group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                 <td class="px-6 py-4 relative">
                   <div class="flex items-center">
                    <button
                      @click="toggleRow(invoice.id)"
                      class="lg:hidden absolute left-2 p-1 text-blue-600 hover:text-blue-800 focus:outline-none"
                    >
                      <MinusCircle v-if="expandedRows.includes(invoice.id)" class="w-5 h-5 fill-blue-100 dark:fill-blue-900/30" />
                      <PlusCircle v-else class="w-5 h-5 fill-blue-50 dark:fill-blue-900/20" />
                    </button>
                    <input
                      type="checkbox"
                      :value="invoice.id"
                      v-model="selectedIds"
                      class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer transition-all ml-4 lg:ml-0"
                    />
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col">
                    <Link
                      :href="`/admin/sales/invoices/${invoice.id}`"
                      class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline"
                    >
                      {{ invoice.invoice_number }}
                    </Link>
                    <span v-if="invoice.due_date" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Due: {{ formatDate(invoice.due_date) }}</span>
                  </div>
                </td>
                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
                   <Link
                      :href="`/admin/sales/orders/${invoice.order.id}`"
                      class="text-sm text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:underline"
                    >
                      {{ invoice.order.order_number }}
                    </Link>
                </td>
                <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                     <div class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 mr-3">
                      <User class="w-4 h-4" />
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ invoice.order.user?.name || 'Guest' }}
                      </div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">{{ invoice.order.customer_email }}</div>
                    </div>
                  </div>
                </td>
                <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(invoice.issue_date) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm', getStatusBadge(invoice.status)]">
                    {{ invoice.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="text-sm font-bold text-gray-900 dark:text-white">{{ formatPrice(invoice.total) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                   <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                      <Link
                        :href="`/admin/sales/invoices/${invoice.id}`"
                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                        title="View Invoice"
                      >
                       <Eye class="w-4 h-4" />
                    </Link>
                   </div>
                </td>
              </tr>
              <!-- Expanded Mobile Row -->
              <tr v-if="expandedRows.includes(invoice.id)" class="bg-gray-50/50 dark:bg-gray-900/50 lg:hidden">
                 <td colspan="8" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                       <div class="sm:hidden flex flex-col gap-2">
                          <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Order Reference</span>
                           <Link
                              :href="`/admin/sales/orders/${invoice.order.id}`"
                              class="text-sm text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:underline flex items-center gap-2"
                            >
                              <FileText class="w-4 h-4" />
                              {{ invoice.order.order_number }}
                            </Link>
                       </div>
                        <div class="md:hidden flex flex-col gap-2">
                           <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Customer</span>
                           <div class="flex items-center">
                               <div class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 mr-3">
                                <User class="w-4 h-4" />
                              </div>
                              <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                  {{ invoice.order.user?.name || 'Guest' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ invoice.order.customer_email }}</div>
                              </div>
                           </div>
                        </div>
                         <div class="lg:hidden flex flex-col gap-2">
                            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Dates</span>
                            <div class="grid grid-cols-2 gap-2">
                               <div>
                                  <span class="text-xs text-gray-500 block">Issued</span>
                                  <span class="text-gray-700 dark:text-gray-300">{{ formatDate(invoice.issue_date) }}</span>
                               </div>
                               <div v-if="invoice.due_date">
                                  <span class="text-xs text-gray-500 block">Due</span>
                                  <span class="text-gray-700 dark:text-gray-300">{{ formatDate(invoice.due_date) }}</span>
                               </div>
                            </div>
                         </div>
                    </div>
                 </td>
              </tr>
              </template>

              <tr v-if="invoices.data.length === 0">
                 <td colspan="8" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                      <FileText class="w-8 h-8" />
                    </div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">No invoices found</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm">Try adjusting your filters.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="invoices.data.length === 0" class="text-center py-12">
          <svg
            class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No invoices found</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters or create a new invoice from an order.</p>
        </div>

        <!-- Pagination -->
        <div v-if="invoices.data.length > 0" class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
              Showing <span class="font-medium">{{ ((invoices.current_page - 1) * invoices.per_page) + 1 }}</span>
              to <span class="font-medium">{{ Math.min(invoices.current_page * invoices.per_page, invoices.total) }}</span>
              of <span class="font-medium">{{ invoices.total }}</span> results
            </div>
            <div class="flex space-x-2">
              <button
                v-if="invoices.current_page > 1"
                @click="router.get('/admin/sales/invoices', { ...filters, page: invoices.current_page - 1 })"
                class="px-3 py-1 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600"
              >
                Previous
              </button>
              <button
                v-if="invoices.current_page < invoices.last_page"
                @click="router.get('/admin/sales/invoices', { ...filters, page: invoices.current_page + 1 })"
                class="px-3 py-1 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600"
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

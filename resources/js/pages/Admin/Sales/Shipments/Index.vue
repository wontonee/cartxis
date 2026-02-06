<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref, computed, watch } from 'vue';
import { debounce } from 'lodash';
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
  Truck,
  Package,
  Clock,
  CheckSquare,
  AlertCircle,
  Printer
} from 'lucide-vue-next';

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
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
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

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortOrder = ref(props.filters.sort_order || 'desc');

// Bulk actions state
const selectAll = ref(false);
const selectedIds = ref<number[]>([]);
const expandedRows = ref<number[]>([]);

const performSearch = debounce(() => {
  applyFilters();
}, 300);

const applyFilters = () => {
  router.get('/admin/sales/shipments', {
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
  return selectedIds.value.length > 0 && selectedIds.value.length < props.shipments.data.length;
});

watch(selectAll, (value) => {
  if (value) {
    selectedIds.value = props.shipments.data.map(s => s.id);
  } else {
    selectedIds.value = [];
  }
});

const getStatusBadge = (status: string) => {
  const badges: Record<string, string> = {
    pending: 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
    shipped: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
    in_transit: 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/20 dark:text-indigo-300 dark:border-indigo-800',
    out_for_delivery: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800',
    delivered: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800',
    failed: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
    cancelled: 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
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
  <Head title="Shipments" />

  <AdminLayout title="Shipments">
    <div class="p-6 space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Shipments</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage order shipments and tracking</p>
        </div>
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
              <Package class="w-6 h-6 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" />
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

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-indigo-200 dark:hover:border-indigo-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">In Transit</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.in_transit }}</p>
            </div>
            <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
              <Truck class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-green-200 dark:hover:border-green-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Delivered</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.delivered }}</p>
            </div>
            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
              <CheckSquare class="w-6 h-6 text-green-600 dark:text-green-400" />
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
                placeholder="Shipment #, Order #..."
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
              Print Labels
            </button>
          </div>
        </div>
      </transition>

      <!-- Shipments Table -->
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
                  @click="sortTable('shipment_number')"
                >
                   <div class="flex items-center gap-1">
                    Shipment #
                     <span v-if="sortBy === 'shipment_number'" class="text-blue-600 dark:text-blue-400">
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
                <th scope="col" class="hidden lg:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Carrier
                </th>
                 <th scope="col" class="hidden xl:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Tracking #
                </th>
                <th
                  scope="col"
                  class="hidden lg:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                  @click="sortTable('created_at')"
                >
                  <div class="flex items-center gap-1">
                    Date Created
                     <span v-if="sortBy === 'created_at'" class="text-blue-600 dark:text-blue-400">
                       <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                       <ArrowDown v-else class="w-3 h-3" />
                    </span>
                    <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                  </div>
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
              <template v-for="shipment in shipments.data" :key="shipment.id">
              <tr class="group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                 <td class="px-6 py-4 relative">
                   <div class="flex items-center">
                    <button
                      @click="toggleRow(shipment.id)"
                      class="lg:hidden absolute left-2 p-1 text-blue-600 hover:text-blue-800 focus:outline-none"
                    >
                      <MinusCircle v-if="expandedRows.includes(shipment.id)" class="w-5 h-5 fill-blue-100 dark:fill-blue-900/30" />
                      <PlusCircle v-else class="w-5 h-5 fill-blue-50 dark:fill-blue-900/20" />
                    </button>
                    <input
                      type="checkbox"
                      :value="shipment.id"
                      v-model="selectedIds"
                      class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer transition-all ml-4 lg:ml-0"
                    />
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                   <div class="flex flex-col">
                    <Link
                      :href="`/admin/sales/shipments/${shipment.id}`"
                      class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline"
                    >
                      {{ shipment.shipment_number }}
                    </Link>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                       {{ shipment.carrier || 'No Carrier' }}
                    </span>
                   </div>
                </td>
                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
                   <Link
                      :href="`/admin/sales/orders/${shipment.order.id}`"
                      class="text-sm text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:underline"
                    >
                      {{ shipment.order.order_number }}
                    </Link>
                </td>
                <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                   <div class="flex items-center">
                     <div class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 mr-3">
                      <User class="w-4 h-4" />
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ shipment.order.user?.name || 'Guest' }}
                      </div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">{{ shipment.order.customer_email }}</div>
                    </div>
                  </div>
                </td>
                <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap">
                   <div class="text-sm text-gray-900 dark:text-white flex items-center gap-2">
                      <Truck class="w-4 h-4 text-gray-400" />
                      {{ shipment.carrier || '-' }}
                   </div>
                </td>
                <td class="hidden xl:table-cell px-6 py-4 whitespace-nowrap">
                   <div class="text-sm font-mono text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 px-2 py-1 rounded w-fit">
                    {{ shipment.tracking_number || 'N/A' }}
                  </div>
                </td>
                <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(shipment.created_at) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm', getStatusBadge(shipment.status)]">
                    {{ shipment.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                   <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                      <Link
                        :href="`/admin/sales/shipments/${shipment.id}`"
                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                        title="View Shipment"
                      >
                       <Eye class="w-4 h-4" />
                    </Link>
                   </div>
                </td>
              </tr>
               <!-- Expanded Mobile Row -->
              <tr v-if="expandedRows.includes(shipment.id)" class="bg-gray-50/50 dark:bg-gray-900/50 lg:hidden">
                 <td colspan="9" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="sm:hidden flex flex-col gap-2">
                          <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Order Reference</span>
                           <Link
                              :href="`/admin/sales/orders/${shipment.order.id}`"
                              class="text-sm text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:underline flex items-center gap-2"
                            >
                              <FileText class="w-4 h-4" />
                              {{ shipment.order.order_number }}
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
                                  {{ shipment.order.user?.name || 'Guest' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ shipment.order.customer_email }}</div>
                              </div>
                           </div>
                        </div>
                        <div class="lg:hidden flex flex-col gap-2">
                            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Shipping Details</span>
                            <div class="grid grid-cols-2 gap-4">
                               <div>
                                  <span class="text-xs text-gray-500 block">Carrier</span>
                                  <span class="text-gray-700 dark:text-gray-300 flex items-center gap-1.5 mt-0.5">
                                    <Truck class="w-3.5 h-3.5 text-gray-400" />
                                    {{ shipment.carrier || '-' }}
                                  </span>
                               </div>
                               <div>
                                  <span class="text-xs text-gray-500 block">Tracking</span>
                                  <span class="text-gray-700 dark:text-gray-300 font-mono text-xs mt-0.5">{{ shipment.tracking_number || 'N/A' }}</span>
                               </div>
                            </div>
                        </div>
                         <div class="lg:hidden flex flex-col gap-2">
                           <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Date</span>
                           <div class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300">
                             <Calendar class="w-3.5 h-3.5 text-gray-400" />
                             {{ formatDate(shipment.created_at) }}
                           </div>
                         </div>
                    </div>
                 </td>
              </tr>
              </template>

              <tr v-if="shipments.data.length === 0">
                 <td colspan="9" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                      <Truck class="w-8 h-8" />
                    </div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">No shipments found</p>
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
            Showing <span class="font-medium">{{ shipments.from || 0 }}</span> to <span class="font-medium">{{ shipments.to || 0 }}</span> of <span class="font-medium">{{ shipments.total }}</span> results
          </div>
          <div class="flex gap-2">
            <Link
              v-if="shipments.prev_page_url"
              :href="shipments.prev_page_url"
              class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-1"
            >
              <ChevronLeft class="w-3 h-3" />
              Previous
            </Link>
            <Link
              v-if="shipments.next_page_url"
              :href="shipments.next_page_url"
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

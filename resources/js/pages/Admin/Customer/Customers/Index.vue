<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';
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
  Users,
  Mail,
  Edit,
  Trash2,
  Shield,
  UserCheck,
  Building
} from 'lucide-vue-next';

interface CustomerGroup {
  id: number;
  name: string;
  color: string;
}

interface Customer {
  id: number;
  first_name: string;
  last_name: string;
  full_name: string;
  email: string;
  phone?: string;
  customer_group: {
    id: number;
    name: string;
    color: string;
  };
  company_name?: string;
  is_active: boolean;
  is_verified: boolean;
  is_guest: boolean;
  newsletter_subscribed: boolean;
  total_orders: number;
  total_spent: number;
  created_at: string;
}

interface Props {
  customers: {
    data: Customer[];
    links: any;
    meta: any;
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    prev_page_url: string | null;
    next_page_url: string | null;
  };
  customerGroups: CustomerGroup[];
  filters: {
    search?: string;
    customer_group_id?: number;
    status?: string;
    customer_type?: string;
    is_verified?: boolean;
    newsletter_subscribed?: boolean;
    date_from?: string;
    date_to?: string;
  };
  statistics: {
    total_customers: number;
    active_customers: number;
    verified_customers: number;
    newsletter_subscribers: number;
  };
}

const props = defineProps<Props>();

const selectedCustomers = ref<number[]>([]);
const expandedRows = ref<number[]>([]);
const showDeleteModal = ref(false);
const deleteCustomerId = ref<number | null>(null);
const deletingCustomer = ref<{ id: number; name: string } | null>(null);
const showBulkDeleteModal = ref(false);

// Local filter state
const search = ref(props.filters.search || '');
const customerGroupFilter = ref(props.filters.customer_group_id || '');
const statusFilter = ref(props.filters.status || '');
const customerTypeFilter = ref(props.filters.customer_type || '');
const verifiedFilter = ref(props.filters.is_verified?.toString() || '');
const newsletterFilter = ref(props.filters.newsletter_subscribed?.toString() || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

// Computed
const allSelected = computed(() => {
  return props.customers.data.length > 0 && 
         selectedCustomers.value.length === props.customers.data.length;
});

const someSelected = computed(() => {
  return selectedCustomers.value.length > 0 && 
         selectedCustomers.value.length < props.customers.data.length;
});

// Debounced search
const performSearch = debounce(() => {
  applyFilters();
}, 300);

// Methods
function toggleSelectAll() {
  if (allSelected.value) {
    selectedCustomers.value = [];
  } else {
    selectedCustomers.value = props.customers.data.map(c => c.id);
  }
}

const toggleRow = (id: number) => {
  const index = expandedRows.value.indexOf(id);
  if (index === -1) {
    expandedRows.value.push(id);
  } else {
    expandedRows.value.splice(index, 1);
  }
};

function applyFilters() {
  router.get('/admin/customers', {
    search: search.value || undefined,
    customer_group_id: customerGroupFilter.value || undefined,
    status: statusFilter.value || undefined,
    customer_type: customerTypeFilter.value || undefined,
    is_verified: verifiedFilter.value || undefined,
    newsletter_subscribed: newsletterFilter.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
}

function clearFilters() {
  search.value = '';
  customerGroupFilter.value = '';
  statusFilter.value = '';
  customerTypeFilter.value = '';
  verifiedFilter.value = '';
  newsletterFilter.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  applyFilters();
}

function confirmDelete(customerId: number) {
  const customer = props.customers.data.find((c) => c.id === customerId);
  if (customer) {
    deletingCustomer.value = { id: customer.id, name: `${customer.first_name} ${customer.last_name}` };
    showDeleteModal.value = true;
  }
}

function deleteCustomer() {
  if (deletingCustomer.value) {
    router.delete(`/admin/customers/${deletingCustomer.value.id}`, {
      onSuccess: () => {
        showDeleteModal.value = false;
        deletingCustomer.value = null;
      },
    });
  }
}

function confirmBulkDelete() {
  if (selectedCustomers.value.length > 0) {
    showBulkDeleteModal.value = true;
  }
}

function bulkDelete() {
  router.post('/admin/customers/bulk-delete', {
    customer_ids: selectedCustomers.value,
  }, {
    onSuccess: () => {
      selectedCustomers.value = [];
      showBulkDeleteModal.value = false;
    },
  });
}

function bulkUpdateStatus(isActive: boolean) {
  if (selectedCustomers.value.length > 0) {
    router.post('/admin/customers/bulk-update-status', {
      customer_ids: selectedCustomers.value,
      is_active: isActive,
    }, {
      onSuccess: () => {
        selectedCustomers.value = [];
      },
    });
  }
}

function exportCustomers() {
  const params = new URLSearchParams({
    search: search.value || '',
    customer_group_id: customerGroupFilter.value?.toString() || '',
    status: statusFilter.value || '',
  });
  window.location.href = `/admin/customers/export/csv?${params.toString()}`;
}

const { formatPrice } = useCurrency();

function formatCurrency(amount: number): string {
  return formatPrice(amount);
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}
</script>

<template>
  <Head title="Customers" />

  <AdminLayout title="Customers">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Customers</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage your customer database</p>
        </div>
        <Link
          :href="'/admin/customers/create'"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <PlusCircle class="w-4 h-4 mr-2" />
          Add Customer
        </Link>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Customers</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.total_customers }}</p>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg group-hover:bg-blue-100 dark:group-hover:bg-blue-900/40 transition-colors">
              <Users class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-green-200 dark:hover:border-green-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.active_customers }}</p>
            </div>
            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
              <UserCheck class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Verified</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.verified_customers }}</p>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
              <Shield class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-purple-200 dark:hover:border-purple-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subscribers</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.newsletter_subscribers }}</p>
            </div>
            <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
              <Mail class="w-6 h-6 text-purple-600 dark:text-purple-400" />
            </div>
          </div>
        </div>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="search"
                @input="applyFilters"
                type="text"
                placeholder="Name, email, phone..."
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
              />
            </div>
          </div>

          <!-- Customer Group Filter -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Group</label>
            <div class="relative">
              <Users class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <select
                v-model="customerGroupFilter"
                @change="applyFilters"
                class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
              >
                <option value="">All Groups</option>
                <option v-for="group in customerGroups" :key="group.id" :value="group.id">
                  {{ group.name }}
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
              <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <select
                v-model="statusFilter"
                @change="applyFilters"
                class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
              >
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
               <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <ArrowUpDown class="w-3 h-3 text-gray-400" />
              </div>
            </div>
          </div>

          <!-- Customer Type Filter -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Type</label>
             <div class="relative">
              <User class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <select
                v-model="customerTypeFilter"
                @change="applyFilters"
                class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
              >
                <option value="">All Types</option>
                <option value="registered">Registered</option>
                <option value="guest">Guest</option>
              </select>
               <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <ArrowUpDown class="w-3 h-3 text-gray-400" />
              </div>
            </div>
          </div>

          <!-- Verified Filter -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Verified</label>
             <div class="relative">
              <Shield class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <select
                v-model="verifiedFilter"
                @change="applyFilters"
                class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
              >
                <option value="">All</option>
                <option value="true">Verified</option>
                <option value="false">Not Verified</option>
              </select>
               <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <ArrowUpDown class="w-3 h-3 text-gray-400" />
              </div>
            </div>
          </div>
        </div>

        <!-- Date Range Filters -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
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

        <!-- Filter Actions -->
        <div v-if="search || customerGroupFilter || statusFilter || customerTypeFilter || verifiedFilter || newsletterFilter || dateFrom || dateTo" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
            
          <button
            @click="clearFilters"
             class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors flex items-center gap-2"
          >
            <X class="w-4 h-4" />
            Clear Filters
          </button>
          
           <button
             @click="exportCustomers"
             class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium transition-colors"
           >
             <Download class="w-4 h-4 mr-2" />
             Export CSV
           </button>
        </div>
        <div v-else class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
           <button
             @click="exportCustomers"
             class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium transition-colors"
           >
             <Download class="w-4 h-4 mr-2" />
             Export CSV
           </button>
        </div>
      </div>

     <!-- Bulk Actions -->
     <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
      <div v-if="selectedCustomers.length > 0" class="bg-blue-600 rounded-xl shadow-lg p-3 text-white flex items-center justify-between sticky top-4 z-10 px-6 mb-6">
        <div class="flex items-center">
          <CheckCircle class="w-4 h-4 mr-2" />
          <span class="text-sm font-semibold">
            {{ selectedCustomers.length }} {{ selectedCustomers.length === 1 ? 'customer' : 'customers' }} selected
          </span>
        </div>
        <div class="flex gap-2">
          <button
            @click="confirmBulkDelete"
            class="px-3 py-1.5 text-xs font-semibold bg-white/20 hover:bg-white/30 rounded-lg transition-colors flex items-center"
          >
            <Trash2 class="w-3 h-3 mr-1.5" />
            Delete Selected
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
            <th class="w-12 px-6 py-4">
              <input
                type="checkbox"
                :checked="allSelected"
                :indeterminate="someSelected"
                @change="toggleSelectAll"
                 class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 transition-all"
              />
            </th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Customer
            </th>
            <th class="hidden md:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Contact
            </th>
            <th class="hidden lg:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Group
            </th>
            <th class="hidden xl:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Orders
            </th>
            <th class="hidden lg:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Total Spent
            </th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Status
            </th>
            <th class="hidden xl:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Created
            </th>
            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
          <!-- Empty State -->
          <tr v-if="customers.data.length === 0">
            <td colspan="9" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
              <div class="flex flex-col items-center justify-center">
                <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                    <User class="w-8 h-8" />
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No customers found</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 max-w-sm">Get started by creating your first customer.</p>
                <Link
                  href="/admin/customers/create"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  <PlusCircle class="w-4 h-4 mr-2" />
                  Add Customer
                </Link>
              </div>
            </td>
          </tr>
          
          <!-- Customer Rows -->
          <template v-for="customer in customers.data" :key="customer.id">
          <tr class="group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
            <td class="px-6 py-4 relative">
               <div class="flex items-center">
                    <button
                      @click="toggleRow(customer.id)"
                      class="lg:hidden absolute left-2 p-1 text-blue-600 hover:text-blue-800 focus:outline-none"
                    >
                      <MinusCircle v-if="expandedRows.includes(customer.id)" class="w-5 h-5 fill-blue-100 dark:fill-blue-900/30" />
                      <PlusCircle v-else class="w-5 h-5 fill-blue-50 dark:fill-blue-900/20" />
                    </button>
                    <input
                        type="checkbox"
                        :value="customer.id"
                        v-model="selectedCustomers"
                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer transition-all ml-4 lg:ml-0"
                    />
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="w-10 h-10 flex-shrink-0 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center border border-gray-200 dark:border-gray-600">
                  <span class="text-sm font-bold text-gray-600 dark:text-gray-300">
                    {{ customer.first_name.charAt(0) }}{{ customer.last_name.charAt(0) }}
                  </span>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-bold text-gray-900 dark:text-white">{{ customer.full_name }}</div>
                  <div v-if="customer.company_name" class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                      <Building class="w-3 h-3" />
                      {{ customer.company_name }}
                  </div>
                </div>
              </div>
            </td>
            <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-600 dark:text-gray-300">{{ customer.email }}</div>
              <div v-if="customer.phone" class="text-xs text-gray-500 dark:text-gray-400">{{ customer.phone }}</div>
            </td>
            <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm"
                :style="{ 
                    backgroundColor: customer.customer_group.color + '20', 
                    color: customer.customer_group.color,
                    borderColor: customer.customer_group.color + '40'
                }"
              >
                {{ customer.customer_group.name }}
              </span>
            </td>
            <td class="hidden xl:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
              {{ customer.total_orders }}
            </td>
            <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
              {{ formatCurrency(customer.total_spent) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex flex-col gap-1.5 items-start">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm',
                    customer.is_active 
                        ? 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800' 
                        : 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800'
                  ]"
                >
                  {{ customer.is_active ? 'Active' : 'Inactive' }}
                </span>
                 <div class="flex gap-1">
                     <span
                      v-if="customer.is_guest"
                      class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600"
                    >
                      Guest
                    </span>
                     <span
                      v-if="customer.is_verified"
                      class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-600 border border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800"
                    >
                      Verified
                    </span>
                 </div>
              </div>
            </td>
            <td class="hidden xl:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ formatDate(customer.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                <Link
                  :href="`/admin/customers/${customer.id}`"
                   class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                  title="View Customer"
                >
                  <Eye class="w-4 h-4" />
                </Link>
                <Link
                  :href="`/admin/customers/${customer.id}/edit`"
                   class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors"
                  title="Edit Customer"
                >
                   <Edit class="w-4 h-4" />
                </Link>
                <button
                  @click="confirmDelete(customer.id)"
                   class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                  title="Delete Customer"
                >
                   <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
           <!-- Expanded Mobile Row -->
            <tr v-if="expandedRows.includes(customer.id)" class="bg-gray-50/50 dark:bg-gray-900/50 lg:hidden">
                <td colspan="9" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="md:hidden flex flex-col gap-2">
                             <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Contact</span>
                             <div class="text-sm font-medium text-gray-900 dark:text-white">{{ customer.email }}</div>
                             <div v-if="customer.phone" class="text-xs text-gray-500">{{ customer.phone }}</div>
                        </div>
                        <div class="lg:hidden flex flex-col gap-2">
                            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Group</span>
                             <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm w-fit"
                                :style="{ 
                                    backgroundColor: customer.customer_group.color + '20', 
                                    color: customer.customer_group.color,
                                    borderColor: customer.customer_group.color + '40'
                                }"
                            >
                                {{ customer.customer_group.name }}
                            </span>
                        </div>
                         <div class="lg:hidden flex flex-col gap-2">
                             <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Financials</span>
                             <div class="grid grid-cols-2 gap-4">
                                 <div>
                                     <span class="text-xs text-gray-500 block">Orders</span>
                                     <span class="font-medium text-gray-900 dark:text-white">{{ customer.total_orders }}</span>
                                 </div>
                                  <div>
                                     <span class="text-xs text-gray-500 block">Spent</span>
                                     <span class="font-bold text-gray-900 dark:text-white">{{ formatCurrency(customer.total_spent) }}</span>
                                 </div>
                             </div>
                        </div>
                        <div class="xl:hidden flex flex-col gap-2">
                            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Joined</span>
                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ formatDate(customer.created_at) }}</span>
                        </div>
                    </div>
                </td>
            </tr>
          </template>
        </tbody>
      </table>
        </div>

        <!-- Pagination -->
       <div class="bg-gray-50/50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
          <div class="text-xs text-gray-500 dark:text-gray-400">
            Showing <span class="font-medium">{{ customers.from || 0 }}</span> to <span class="font-medium">{{ customers.to || 0 }}</span> of <span class="font-medium">{{ customers.total }}</span> results
          </div>
          <div class="flex gap-2">
            <Link
              v-if="customers.prev_page_url"
              :href="customers.prev_page_url"
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
              v-if="customers.next_page_url"
              :href="customers.next_page_url"
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

      <!-- Delete Modal -->
      <ConfirmDeleteModal
        v-model:show="showDeleteModal"
        :title="deletingCustomer?.name ?? ''"
        :message="`Are you sure you want to delete '${deletingCustomer?.name}'? This action cannot be undone.`"
        @confirm="deleteCustomer"
      />

      <!-- Bulk Delete Modal -->
      <ConfirmDeleteModal
        v-model:show="showBulkDeleteModal"
        title="Delete Multiple Customers"
        :message="`Are you sure you want to delete ${selectedCustomers.length} customer(s)? This action cannot be undone.`"
        @confirm="bulkDelete"
      />
    </div>
  </AdminLayout>
</template>

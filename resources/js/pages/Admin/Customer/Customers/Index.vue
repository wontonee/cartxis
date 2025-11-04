<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';
import { useCurrency } from '@/composables/useCurrency';

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
const showDeleteModal = ref(false);
const deleteCustomerId = ref<number | null>(null);
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
  deleteCustomerId.value = customerId;
  showDeleteModal.value = true;
}

function deleteCustomer() {
  if (deleteCustomerId.value) {
    router.delete(`/admin/customers/${deleteCustomerId.value}`, {
      onSuccess: () => {
        showDeleteModal.value = false;
        deleteCustomerId.value = null;
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

  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Customers</h1>
            <p class="mt-1 text-sm text-gray-600">Manage your customer database</p>
          </div>
          <Link
            :href="'/admin/customers/create'"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Customer
          </Link>
        </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm font-medium text-gray-600">Total Customers</div>
          <div class="mt-2 text-3xl font-semibold text-gray-900">{{ statistics.total_customers }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm font-medium text-gray-600">Active Customers</div>
          <div class="mt-2 text-3xl font-semibold text-green-600">{{ statistics.active_customers }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm font-medium text-gray-600">Verified</div>
          <div class="mt-2 text-3xl font-semibold text-blue-600">{{ statistics.verified_customers }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm font-medium text-gray-600">Newsletter Subscribers</div>
          <div class="mt-2 text-3xl font-semibold text-purple-600">{{ statistics.newsletter_subscribers }}</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="search"
              @input="performSearch"
              type="text"
              placeholder="Name, email, phone..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Customer Group Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Group</label>
            <select
              v-model="customerGroupFilter"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Groups</option>
              <option v-for="group in customerGroups" :key="group.id" :value="group.id">
                {{ group.name }}
              </option>
            </select>
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
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <!-- Customer Type Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Type</label>
            <select
              v-model="customerTypeFilter"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Types</option>
              <option value="registered">Registered</option>
              <option value="guest">Guest</option>
            </select>
          </div>

          <!-- Verified Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Verified</label>
            <select
              v-model="verifiedFilter"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All</option>
              <option value="true">Verified</option>
              <option value="false">Not Verified</option>
            </select>
          </div>

          <!-- Newsletter Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Newsletter</label>
            <select
              v-model="newsletterFilter"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All</option>
              <option value="true">Subscribed</option>
              <option value="false">Not Subscribed</option>
            </select>
          </div>
        </div>

        <!-- Date Range Filters -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
            <input
              v-model="dateFrom"
              @change="applyFilters"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
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

        <!-- Filter Actions -->
        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200">
          <button
            v-if="search || customerGroupFilter || statusFilter || customerTypeFilter || verifiedFilter || newsletterFilter || dateFrom || dateTo"
            @click="clearFilters"
            class="text-sm text-gray-600 hover:text-gray-900"
          >
            Clear Filters
          </button>
          <div class="flex gap-2 ml-auto">
            <button
              @click="exportCustomers"
              class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Export CSV
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Actions -->
      <!-- Bulk Actions -->
      <div v-if="selectedCustomers.length > 0" class="mb-4 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-blue-900">
            {{ selectedCustomers.length }} {{ selectedCustomers.length === 1 ? 'customer' : 'customers' }} selected
          </span>
          <div class="flex gap-2">
            <button
              @click="confirmBulkDelete"
              class="px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700"
            >
              Delete Selected
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
            <th class="w-12 px-6 py-3">
              <input
                type="checkbox"
                :checked="allSelected"
                :indeterminate="someSelected"
                @change="toggleSelectAll"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Customer
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Contact
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Group
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Orders
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Total Spent
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Created
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <!-- Empty State -->
          <tr v-if="customers.data.length === 0">
            <td colspan="9" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center justify-center">
                <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No customers found</h3>
                <p class="text-gray-500 mb-4">Get started by creating your first customer.</p>
                <Link
                  href="/admin/customers/create"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  Add Customer
                </Link>
              </div>
            </td>
          </tr>
          
          <!-- Customer Rows -->
          <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <input
                type="checkbox"
                :value="customer.id"
                v-model="selectedCustomers"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center">
                <div class="w-10 h-10 flex-shrink-0 bg-gray-200 rounded-full flex items-center justify-center">
                  <span class="text-sm font-medium text-gray-600">
                    {{ customer.first_name.charAt(0) }}{{ customer.last_name.charAt(0) }}
                  </span>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">{{ customer.full_name }}</div>
                  <div v-if="customer.company_name" class="text-sm text-gray-500">{{ customer.company_name }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-gray-900">{{ customer.email }}</div>
              <div v-if="customer.phone" class="text-sm text-gray-500">{{ customer.phone }}</div>
            </td>
            <td class="px-6 py-4">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :style="{ backgroundColor: customer.customer_group.color + '20', color: customer.customer_group.color }"
              >
                {{ customer.customer_group.name }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">
              {{ customer.total_orders }}
            </td>
            <td class="px-6 py-4 text-sm font-medium text-gray-900">
              {{ formatCurrency(customer.total_spent) }}
            </td>
            <td class="px-6 py-4">
              <div class="flex flex-col gap-1">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    customer.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ customer.is_active ? 'Active' : 'Inactive' }}
                </span>
                <span
                  v-if="customer.is_guest"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                >
                  Guest
                </span>
                <span
                  v-if="customer.is_verified"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                >
                  Verified
                </span>
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
              {{ formatDate(customer.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex items-center justify-end gap-2">
                <Link
                  :href="`/admin/customers/${customer.id}`"
                  class="text-blue-600 hover:text-blue-900 cursor-pointer"
                  title="View Customer"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </Link>
                <Link
                  :href="`/admin/customers/${customer.id}/edit`"
                  class="text-indigo-600 hover:text-indigo-900 cursor-pointer"
                  title="Edit Customer"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </Link>
                <button
                  @click="confirmDelete(customer.id)"
                  class="text-red-600 hover:text-red-900 cursor-pointer"
                  title="Delete Customer"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
        </div>

        <!-- Pagination -->
        <Pagination :data="customers" resource-name="customers" />
      </div>

      <!-- Delete Modal -->
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeleteModal = false"></div>
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full z-10">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Customer</h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete this customer? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="deleteCustomer"
              type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Delete
            </button>
            <button
              @click="showDeleteModal = false"
              type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
      </div>

      <!-- Bulk Delete Modal -->
      <div
        v-if="showBulkDeleteModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showBulkDeleteModal = false"></div>
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full z-10">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Multiple Customers</h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete {{ selectedCustomers.length }} customer(s)? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="bulkDelete"
              type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Delete All
            </button>
            <button
              @click="showBulkDeleteModal = false"
              type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
      </div>
    </div>
  </AdminLayout>
</template>

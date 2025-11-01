<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';

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

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount);
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
    <!-- Header -->
    <div class="mb-6">
      <div class="flex justify-between items-center mb-4">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Customers</h1>
          <p class="mt-1 text-sm text-gray-600">Manage your customer database</p>
        </div>
        <Link
          :href="'/admin/customers/create'"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
      <div class="bg-white rounded-lg shadow p-4 space-y-4">
        <!-- Search -->
        <div class="flex gap-4">
          <div class="flex-1">
            <input
              v-model="search"
              @input="performSearch"
              type="text"
              placeholder="Search by name, email, phone..."
              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
          <button
            @click="clearFilters"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Reset
          </button>
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

        <!-- Additional Filters -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <select
            v-model="customerGroupFilter"
            @change="applyFilters"
            class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Groups</option>
            <option v-for="group in customerGroups" :key="group.id" :value="group.id">
              {{ group.name }}
            </option>
          </select>

          <select
            v-model="statusFilter"
            @change="applyFilters"
            class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>

          <select
            v-model="verifiedFilter"
            @change="applyFilters"
            class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Verified</option>
            <option value="true">Verified</option>
            <option value="false">Not Verified</option>
          </select>

          <input
            v-model="dateFrom"
            @change="applyFilters"
            type="date"
            placeholder="From Date"
            class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />

          <input
            v-model="dateTo"
            @change="applyFilters"
            type="date"
            placeholder="To Date"
            class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
      </div>
    </div>

    <!-- Bulk Actions -->
    <div v-if="selectedCustomers.length > 0" class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-blue-900">
          {{ selectedCustomers.length }} customer(s) selected
        </span>
        <div class="flex gap-2">
          <button
            @click="bulkUpdateStatus(true)"
            class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700"
          >
            Activate
          </button>
          <button
            @click="bulkUpdateStatus(false)"
            class="px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700"
          >
            Deactivate
          </button>
          <button
            @click="confirmBulkDelete"
            class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
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
            <td class="px-6 py-4 text-right text-sm font-medium">
              <Link
                :href="`/admin/customers/${customer.id}`"
                class="text-blue-600 hover:text-blue-900 mr-3"
              >
                View
              </Link>
              <Link
                :href="`/admin/customers/${customer.id}/edit`"
                class="text-indigo-600 hover:text-indigo-900 mr-3"
              >
                Edit
              </Link>
              <button
                @click="confirmDelete(customer.id)"
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="px-6 py-4 border-t border-gray-200">
        <Pagination :data="customers" resource-name="customers" />
      </div>
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
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
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
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
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
  </AdminLayout>
</template>

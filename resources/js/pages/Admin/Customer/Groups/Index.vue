<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';

interface Customer {
  id: number;
  first_name: string;
  last_name: string;
  full_name: string;
  email: string;
}

interface CustomerGroup {
  id: number;
  name: string;
  code: string;
  description?: string;
  color: string;
  discount_percentage: number;
  order: number;
  is_default: boolean;
  auto_assignment_rules: {
    min_orders?: number;
    min_spent?: number;
    min_aov?: number;
  };
  status: boolean;
  customers_count: number;
  active_customers_count: number;
  customers?: Customer[];
  created_at: string;
  updated_at: string;
}

interface Props {
  groups: {
    data: CustomerGroup[];
    links: any;
    meta: any;
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
  };
  statistics: {
    total: number;
    active: number;
    inactive: number;
    with_customers: number;
  };
  filters: {
    search?: string;
    status?: string;
    sort_by?: string;
    sort_order?: string;
    per_page?: number;
  };
}

const props = defineProps<Props>();

const selectedGroups = ref<number[]>([]);
const showDeleteModal = ref(false);
const deleteGroupId = ref<number | null>(null);
const showBulkDeleteModal = ref(false);
const showAutoAssignModal = ref(false);
const autoAssignGroupId = ref<number | null>(null);
const isDragging = ref(false);
const draggedGroup = ref<CustomerGroup | null>(null);

// Local filter state
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const sortBy = ref(props.filters.sort_by || 'order');
const sortOrder = ref(props.filters.sort_order || 'asc');
const perPage = ref(props.filters.per_page || 15);

// Computed
const allSelected = computed(() => {
  return props.groups.data.length > 0 && 
         selectedGroups.value.length === props.groups.data.length;
});

const someSelected = computed(() => {
  return selectedGroups.value.length > 0 && 
         selectedGroups.value.length < props.groups.data.length;
});

const hasActiveFilters = computed(() => {
  return search.value || statusFilter.value;
});

// Debounced search
const performSearch = debounce(() => {
  applyFilters();
}, 300);

// Methods
function toggleSelectAll() {
  if (allSelected.value) {
    selectedGroups.value = [];
  } else {
    selectedGroups.value = props.groups.data.map(g => g.id);
  }
}

function applyFilters() {
  router.get('/admin/customers/groups', {
    search: search.value || undefined,
    status: statusFilter.value || undefined,
    sort_by: sortBy.value,
    sort_order: sortOrder.value,
    per_page: perPage.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
}

function clearFilters() {
  search.value = '';
  statusFilter.value = '';
  applyFilters();
}

function confirmDelete(groupId: number) {
  deleteGroupId.value = groupId;
  showDeleteModal.value = true;
}

function deleteGroup() {
  if (deleteGroupId.value) {
    router.delete(`/admin/customers/groups/${deleteGroupId.value}`, {
      onSuccess: () => {
        showDeleteModal.value = false;
        deleteGroupId.value = null;
      },
    });
  }
}

function confirmBulkDelete() {
  if (selectedGroups.value.length > 0) {
    showBulkDeleteModal.value = true;
  }
}

function bulkDelete() {
  router.post('/admin/customers/groups/bulk-delete', {
    ids: selectedGroups.value,
  }, {
    onSuccess: () => {
      selectedGroups.value = [];
      showBulkDeleteModal.value = false;
    },
  });
}

function bulkUpdateStatus(status: boolean) {
  if (selectedGroups.value.length > 0) {
    router.post('/admin/customers/groups/bulk-update-status', {
      ids: selectedGroups.value,
      status: status,
    }, {
      onSuccess: () => {
        selectedGroups.value = [];
      },
    });
  }
}

function applyAutoAssignment(groupId: number) {
  autoAssignGroupId.value = groupId;
  showAutoAssignModal.value = true;
}

function confirmAutoAssignment() {
  if (autoAssignGroupId.value) {
    router.post(`/admin/customers/groups/${autoAssignGroupId.value}/apply-auto-assignment`, {}, {
      preserveScroll: true,
      onFinish: () => {
        showAutoAssignModal.value = false;
        autoAssignGroupId.value = null;
      }
    });
  }
}

// Drag and drop methods
function handleDragStart(event: DragEvent, group: CustomerGroup) {
  isDragging.value = true;
  draggedGroup.value = group;
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/html', '');
  }
}

function handleDragOver(event: DragEvent) {
  event.preventDefault();
  if (event.dataTransfer) {
    event.dataTransfer.dropEffect = 'move';
  }
}

function handleDrop(event: DragEvent, targetGroup: CustomerGroup) {
  event.preventDefault();
  isDragging.value = false;
  
  if (!draggedGroup.value || draggedGroup.value.id === targetGroup.id) {
    return;
  }
  
  // Reorder groups
  const groups = [...props.groups.data];
  const draggedIndex = groups.findIndex(g => g.id === draggedGroup.value!.id);
  const targetIndex = groups.findIndex(g => g.id === targetGroup.id);
  
  // Remove dragged group
  const [removed] = groups.splice(draggedIndex, 1);
  
  // Insert at new position
  groups.splice(targetIndex, 0, removed);
  
  // Update order property for all groups
  const reorderedGroups = groups.map((group, index) => ({
    id: group.id,
    order: index,
  }));
  
  // Send update to server
  router.post('/admin/customers/groups/reorder', {
    groups: reorderedGroups,
  }, {
    preserveScroll: true,
  });
  
  draggedGroup.value = null;
}

function handleDragEnd() {
  isDragging.value = false;
  draggedGroup.value = null;
}
</script>

<template>
  <AdminLayout>
    <Head title="Customer Groups" />

    <!-- Page Header -->
    <div class="mb-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Customer Groups</h1>
          <p class="mt-1 text-sm text-gray-600">Manage customer segmentation and group-based pricing</p>
        </div>
        <div class="mt-4 md:mt-0">
          <Link
            :href="'/admin/customers/groups/create'"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Group
          </Link>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Total Groups</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.total }}</p>
          </div>
          <div class="p-3 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Active Groups</p>
            <p class="text-2xl font-bold text-green-600">{{ statistics.active }}</p>
          </div>
          <div class="p-3 bg-green-100 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Inactive Groups</p>
            <p class="text-2xl font-bold text-red-600">{{ statistics.inactive }}</p>
          </div>
          <div class="p-3 bg-red-100 rounded-lg">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">With Customers</p>
            <p class="text-2xl font-bold text-purple-600">{{ statistics.with_customers }}</p>
          </div>
          <div class="p-3 bg-purple-100 rounded-lg">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            v-model="search"
            @input="performSearch"
            type="text"
            placeholder="Name, code, description..."
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
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <!-- Per Page -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Per Page</label>
          <select
            v-model="perPage"
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option :value="15">15</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </div>
      </div>

      <div v-if="hasActiveFilters" class="mt-4">
        <button
          @click="clearFilters"
          class="text-sm text-blue-600 hover:text-blue-700"
        >
          Clear all filters
        </button>
      </div>
    </div>

    <!-- Bulk Actions -->
    <div v-if="selectedGroups.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
      <div class="flex items-center justify-between">
        <span class="text-sm text-blue-800">
          {{ selectedGroups.length }} group(s) selected
        </span>
        <div class="flex items-center space-x-2">
          <button
            @click="bulkUpdateStatus(true)"
            class="px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors"
          >
            Activate
          </button>
          <button
            @click="bulkUpdateStatus(false)"
            class="px-3 py-1.5 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700 transition-colors"
          >
            Deactivate
          </button>
          <button
            @click="confirmBulkDelete"
            class="px-3 py-1.5 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Groups Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="w-12 px-6 py-3 text-left">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  :indeterminate="someSelected"
                  @change="toggleSelectAll"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
              </th>
              <th class="w-12 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Order
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Group
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Discount
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Customers
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Auto-Assignment
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
              v-for="group in groups.data"
              :key="group.id"
              draggable="true"
              @dragstart="handleDragStart($event, group)"
              @dragover="handleDragOver"
              @drop="handleDrop($event, group)"
              @dragend="handleDragEnd"
              :class="[
                'hover:bg-gray-50 transition-colors cursor-move',
                draggedGroup?.id === group.id ? 'opacity-50' : ''
              ]"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <input
                  type="checkbox"
                  :value="group.id"
                  v-model="selectedGroups"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                  </svg>
                  <span>{{ group.order }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center space-x-3">
                  <div
                    class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-semibold"
                    :style="{ backgroundColor: group.color }"
                  >
                    {{ group.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <div class="flex items-center space-x-2">
                      <span class="text-sm font-medium text-gray-900">{{ group.name }}</span>
                      <span
                        v-if="group.is_default"
                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800"
                      >
                        Default
                      </span>
                    </div>
                    <p class="text-sm text-gray-500">{{ group.code }}</p>
                    <p v-if="group.description" class="text-xs text-gray-400 mt-1 max-w-md truncate">
                      {{ group.description }}
                    </p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm font-medium text-gray-900">{{ group.discount_percentage }}%</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ group.customers_count }} total
                </div>
                <div class="text-xs text-gray-500">
                  {{ group.active_customers_count }} active
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div v-if="group.auto_assignment_rules && Object.keys(group.auto_assignment_rules).length > 0" class="text-xs">
                  <div v-if="group.auto_assignment_rules.min_orders" class="text-gray-600">
                    Min Orders: {{ group.auto_assignment_rules.min_orders }}
                  </div>
                  <div v-if="group.auto_assignment_rules.min_spent" class="text-gray-600">
                    Min Spent: ${{ group.auto_assignment_rules.min_spent }}
                  </div>
                  <div v-if="group.auto_assignment_rules.min_aov" class="text-gray-600">
                    Min AOV: ${{ group.auto_assignment_rules.min_aov }}
                  </div>
                  <button
                    @click="applyAutoAssignment(group.id)"
                    class="mt-1 text-blue-600 hover:text-blue-700 font-medium"
                  >
                    Apply Now
                  </button>
                </div>
                <span v-else class="text-xs text-gray-400">Not configured</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    group.status
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ group.status ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right text-sm font-medium">
                <div class="flex items-center justify-end gap-2">
                  <Link
                    :href="`/admin/customers/groups/${group.id}/edit`"
                    class="text-blue-600 hover:text-blue-900"
                    title="Edit"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </Link>
                  <button
                    @click="confirmDelete(group.id)"
                    class="text-red-600 hover:text-red-900"
                    :disabled="group.is_default"
                    :class="{ 'opacity-50 cursor-not-allowed': group.is_default }"
                    title="Delete"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="groups.data.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No customer groups</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new customer group.</p>
        <div class="mt-6">
          <Link
            :href="'/admin/customers/groups/create'"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Group
          </Link>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="groups.data.length > 0" class="px-6 py-4 border-t border-gray-200">
        <Pagination :data="groups" resource-name="groups" />
      </div>
    </div>

    <!-- Delete Modal -->
    <Teleport to="body">
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity -z-10" @click="showDeleteModal = false"></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
          <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  Delete Customer Group
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete this customer group? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
              <button
                type="button"
                @click="deleteGroup"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Delete
              </button>
              <button
                type="button"
                @click="showDeleteModal = false"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm"
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
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity -z-10" @click="showBulkDeleteModal = false"></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
          <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  Delete Customer Groups
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete {{ selectedGroups.length }} customer group(s)? Groups with customers or set as default will be skipped.
                  </p>
                </div>
              </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
              <button
                type="button"
                @click="bulkDelete"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Delete
              </button>
              <button
                type="button"
                @click="showBulkDeleteModal = false"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Auto Assignment Confirmation Modal -->
      <div
        v-if="showAutoAssignModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity -z-10" @click="showAutoAssignModal = false"></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
          <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  Apply Auto-Assignment Rules
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    This will assign customers matching the auto-assignment rules to this group. Continue?
                  </p>
                </div>
              </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
              <button
                type="button"
                @click="confirmAutoAssignment"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Continue
              </button>
              <button
                type="button"
                @click="showAutoAssignModal = false"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>

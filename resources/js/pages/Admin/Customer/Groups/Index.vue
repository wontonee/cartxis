<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';
import {
  Users,
  UserCheck,
  UserX,
  PlusCircle,
  Search,
  Filter,
  Trash2,
  Edit,
  CheckCircle,
  X,
  ArrowUpDown,
  Tag,
  Layers,
  GripVertical,
  ChevronLeft,
  ChevronRight
} from 'lucide-vue-next';

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
    prev_page_url: string | null;
    next_page_url: string | null;
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
const deletingGroup = ref<{ id: number; name: string } | null>(null);
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
  const group = props.groups.data.find(g => g.id === groupId);
  if (group) {
    deletingGroup.value = { id: group.id, name: group.name };
    showDeleteModal.value = true;
  }
}

function deleteGroup() {
  if (deletingGroup.value) {
    router.delete(`/admin/customers/groups/${deletingGroup.value.id}`, {
      onSuccess: () => {
        showDeleteModal.value = false;
        deletingGroup.value = null;
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
  <Head title="Customer Groups" />

  <AdminLayout title="Customer Groups">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Customer Groups</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage customer segmentation and group-based pricing</p>
        </div>
        <Link
          :href="'/admin/customers/groups/create'"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <PlusCircle class="w-4 h-4 mr-2" />
          New Group
        </Link>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Groups</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.total }}</p>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg group-hover:bg-blue-100 dark:group-hover:bg-blue-900/40 transition-colors">
              <Layers class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-green-200 dark:hover:border-green-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.active }}</p>
            </div>
            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
              <CheckCircle class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-red-200 dark:hover:border-red-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Inactive</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.inactive }}</p>
            </div>
            <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
              <X class="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-purple-200 dark:hover:border-purple-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">With Customers</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.with_customers }}</p>
            </div>
            <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
              <Users class="w-6 h-6 text-purple-600 dark:text-purple-400" />
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
                placeholder="Name, code, description..."
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
              />
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
          
           <!-- Per Page -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Per Page</label>
             <div class="relative">
              <Layers class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <select
                v-model="perPage"
                @change="applyFilters"
                class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
              >
                <option :value="15">15</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
               <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <ArrowUpDown class="w-3 h-3 text-gray-400" />
              </div>
            </div>
          </div>
        </div>

        <div v-if="hasActiveFilters" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
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
        <div v-if="selectedGroups.length > 0" class="bg-blue-600 rounded-xl shadow-lg p-3 text-white flex items-center justify-between sticky top-4 z-10 px-6 mb-6">
          <div class="flex items-center">
             <CheckCircle class="w-4 h-4 mr-2" />
            <span class="text-sm font-semibold">
              {{ selectedGroups.length }} group(s) selected
            </span>
          </div>
          <div class="flex gap-2">
            <button
              @click="bulkUpdateStatus(true)"
              class="px-3 py-1.5 text-xs font-semibold bg-white/20 hover:bg-white/30 rounded-lg transition-colors"
            >
              Activate
            </button>
            <button
              @click="bulkUpdateStatus(false)"
              class="px-3 py-1.5 text-xs font-semibold bg-white/20 hover:bg-white/30 rounded-lg transition-colors"
            >
              Deactivate
            </button>
            <button
              @click="confirmBulkDelete"
              class="px-3 py-1.5 text-xs font-semibold bg-white/20 hover:bg-white/30 rounded-lg transition-colors flex items-center"
            >
              <Trash2 class="w-3 h-3 mr-1.5" />
              Delete
            </button>
          </div>
        </div>
      </transition>

      <!-- Groups Table -->
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
                <th class="w-12 px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Order
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Group
                </th>
                <th class="hidden sm:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Discount
                </th>
                <th class="hidden md:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Customers
                </th>
                <th class="hidden lg:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Auto-Assignment
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
              <!-- Empty State -->
              <tr v-if="groups.data.length === 0">
                <td colspan="8" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                            <Layers class="w-8 h-8" />
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No customer groups found</h3>
                         <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 max-w-sm">Get started by creating a new customer group.</p>
                        <Link
                            :href="'/admin/customers/groups/create'"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <PlusCircle class="w-4 h-4 mr-2" />
                            New Group
                        </Link>
                    </div>
                </td>
              </tr>
              
              <template v-for="group in groups.data" :key="group.id">
              <tr
                draggable="true"
                @dragstart="handleDragStart($event, group)"
                @dragover="handleDragOver"
                @drop="handleDrop($event, group)"
                @dragend="handleDragEnd"
                :class="[
                  'group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors',
                  draggedGroup?.id === group.id ? 'opacity-50' : ''
                ]"
              >
                <td class="px-6 py-4">
                  <input
                    type="checkbox"
                    :value="group.id"
                    v-model="selectedGroups"
                    class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer transition-all"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  <div class="flex items-center space-x-2 cursor-move">
                    <GripVertical class="w-4 h-4 text-gray-400" />
                    <span>{{ group.order }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center space-x-3">
                    <div
                      class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold shadow-sm"
                      :style="{ backgroundColor: group.color }"
                    >
                      {{ group.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <div class="flex items-center space-x-2">
                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ group.name }}</span>
                         <span
                          v-if="group.is_default"
                          class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800"
                        >
                          Default
                        </span>
                      </div>
                      <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5">
                          <Tag class="w-3 h-3" />
                          {{ group.code }}
                      </div>
                       <p v-if="group.description" class="text-xs text-gray-400 dark:text-gray-500 mt-1 max-w-xs truncate hidden sm:block">
                        {{ group.description }}
                      </p>
                    </div>
                  </div>
                </td>
                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                      {{ group.discount_percentage }}%
                  </span>
                </td>
                <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ group.customers_count }} total
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ group.active_customers_count }} active
                  </div>
                </td>
                <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap">
                  <div v-if="group.auto_assignment_rules && Object.keys(group.auto_assignment_rules).length > 0" class="text-xs space-y-1">
                    <div v-if="group.auto_assignment_rules.min_orders" class="text-gray-600 dark:text-gray-400">
                      Min Orders: <span class="font-medium text-gray-900 dark:text-white">{{ group.auto_assignment_rules.min_orders }}</span>
                    </div>
                    <div v-if="group.auto_assignment_rules.min_spent" class="text-gray-600 dark:text-gray-400">
                      Min Spent: <span class="font-medium text-gray-900 dark:text-white">${{ group.auto_assignment_rules.min_spent }}</span>
                    </div>
                    <div v-if="group.auto_assignment_rules.min_aov" class="text-gray-600 dark:text-gray-400">
                      Min AOV: <span class="font-medium text-gray-900 dark:text-white">${{ group.auto_assignment_rules.min_aov }}</span>
                    </div>
                    <button
                      @click="applyAutoAssignment(group.id)"
                      class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium hover:underline"
                    >
                      Apply Now
                    </button>
                  </div>
                  <span v-else class="text-xs text-gray-400 dark:text-gray-500 italic">Not configured</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border shadow-sm',
                      group.status
                        ? 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800' 
                        : 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800'
                    ]"
                  >
                    {{ group.status ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                    <Link
                      :href="`/admin/customers/groups/${group.id}/edit`"
                     class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors"
                      title="Edit"
                    >
                      <Edit class="w-4 h-4" />
                    </Link>
                    <button
                      @click="confirmDelete(group.id)"
                       class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                      :disabled="group.is_default"
                      :class="{ 'opacity-50 cursor-not-allowed': group.is_default }"
                      title="Delete"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
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
            Showing <span class="font-medium">{{ groups.from || 0 }}</span> to <span class="font-medium">{{ groups.to || 0 }}</span> of <span class="font-medium">{{ groups.total }}</span> results
          </div>
          <div class="flex gap-2">
            <Link
              v-if="groups.prev_page_url"
              :href="groups.prev_page_url"
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
              v-if="groups.next_page_url"
              :href="groups.next_page_url"
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
      :title="deletingGroup?.name ?? ''"
      :message="`Are you sure you want to delete '${deletingGroup?.name}'? This action cannot be undone.`"
      @confirm="deleteGroup"
    />

    <!-- Bulk Delete Modal -->
    <ConfirmDeleteModal
      v-model:show="showBulkDeleteModal"
      title="Multiple Customer Groups"
      :message="`Are you sure you want to delete ${selectedGroups.length} customer group(s)? Groups with customers or set as default will be skipped.`"
      @confirm="bulkDelete"
    />

    <!-- Auto Assignment Confirmation Modal -->
    <Teleport to="body">
      <div
        v-if="showAutoAssignModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="showAutoAssignModal = false"></div>
          
          <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/30 sm:mx-0 sm:h-10 sm:w-10">
                <Users class="h-6 w-6 text-blue-600 dark:text-blue-400" />
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                  Apply Auto-Assignment Rules
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    This will assign customers matching the auto-assignment rules to this group. This operation might take a while depending on the number of customers.
                  </p>
                </div>
              </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
              <button
                type="button"
                @click="confirmAutoAssignment"
                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm"
              >
                Continue
              </button>
              <button
                type="button"
                @click="showAutoAssignModal = false"
                class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
    </div>
  </AdminLayout>
</template>

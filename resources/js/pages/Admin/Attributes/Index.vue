<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';
import * as attributeRoutes from '@/routes/admin/catalog/attributes';
import {
  Plus,
  Search,
  Filter,
  X,
  Trash2,
  CheckCircle,
  XCircle,
  Edit,
  ArrowUp,
  ArrowDown,
  AlertCircle,
  PlusCircle,
  MinusCircle,
  List,
  ArrowUpDown
} from 'lucide-vue-next';

interface Attribute {
  id: number;
  code: string;
  name: string;
  type: 'text' | 'textarea' | 'select' | 'multiselect' | 'boolean' | 'date' | 'price' | 'number';
  is_required: boolean;
  is_filterable: boolean;
  is_configurable: boolean;
  sort_order: number;
  options_count: number;
  created_at: string;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface Props {
  attributes: {
    data: Attribute[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
  };
  filters: {
    search?: string;
    type?: string;
    sort_by?: string;
    sort_order?: string;
  };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');
const sortBy = ref(props.filters.sort_by || 'sort_order');
const sortOrder = ref(props.filters.sort_order || 'asc');

const selectedAttributes = ref<number[]>([]);
const showDeleteModal = ref(false);
const attributeToDelete = ref<number | null>(null);
const showBulkDeleteModal = ref(false);
const expandedRows = ref<number[]>([]);

// Select all checkbox
const selectAll = computed({
  get: () => selectedAttributes.value.length === props.attributes.data.length && props.attributes.data.length > 0,
  set: (value: boolean) => {
    selectedAttributes.value = value ? props.attributes.data.map(attr => attr.id) : [];
  }
});

// Row expansion
const toggleRow = (id: number) => {
  const index = expandedRows.value.indexOf(id);
  if (index === -1) {
    expandedRows.value.push(id);
  } else {
    expandedRows.value.splice(index, 1);
  }
};

// Debounced search
const performSearch = debounce(() => {
  applyFilters();
}, 300);

// Apply filters
const applyFilters = () => {
  router.get(attributeRoutes.index().url, {
    search: search.value || undefined,
    type: typeFilter.value || undefined,
    sort_by: sortBy.value,
    sort_order: sortOrder.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

// Sort table
const sortTable = (column: string) => {
  if (sortBy.value === column) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortOrder.value = 'asc';
  }
  applyFilters();
};

// Clear filters
const clearFilters = () => {
  search.value = '';
  typeFilter.value = '';
  sortBy.value = 'sort_order';
  sortOrder.value = 'asc';
  applyFilters();
};

// Delete attribute
const confirmDelete = (id: number) => {
  attributeToDelete.value = id;
  showDeleteModal.value = true;
};

const deleteAttribute = () => {
  if (attributeToDelete.value) {
    router.delete(attributeRoutes.destroy(attributeToDelete.value).url, {
      onSuccess: () => {
        showDeleteModal.value = false;
        attributeToDelete.value = null;
      },
    });
  }
};

// Bulk delete
const confirmBulkDelete = () => {
  if (selectedAttributes.value.length > 0) {
    showBulkDeleteModal.value = true;
  }
};

const bulkDelete = () => {
  if (selectedAttributes.value.length > 0) {
    router.post(attributeRoutes.bulkDestroy().url, {
      ids: selectedAttributes.value,
    }, {
      onSuccess: () => {
        selectedAttributes.value = [];
        showBulkDeleteModal.value = false;
      },
    });
  }
};

// Get type label
const getTypeLabel = (type: string): string => {
  const labels: Record<string, string> = {
    text: 'Text',
    textarea: 'Textarea',
    select: 'Select',
    multiselect: 'Multi-select',
    boolean: 'Boolean',
    date: 'Date',
    price: 'Price',
    number: 'Number',
  };
  return labels[type] || type;
};

// Get type badge color
const getTypeBadgeColor = (type: string): string => {
  const colors: Record<string, string> = {
    text: 'bg-blue-100 text-blue-800',
    textarea: 'bg-indigo-100 text-indigo-800',
    select: 'bg-green-100 text-green-800',
    multiselect: 'bg-emerald-100 text-emerald-800',
    boolean: 'bg-purple-100 text-purple-800',
    date: 'bg-pink-100 text-pink-800',
    price: 'bg-yellow-100 text-yellow-800',
    number: 'bg-orange-100 text-orange-800',
  };
  return colors[type] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
  <Head title="Attributes" />

  <AdminLayout title="Attributes">
    <div class="p-6 space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Attributes</h1>
          <p class="mt-1 text-sm text-gray-600">Manage product attributes and their options</p>
        </div>
        <Link
          :href="attributeRoutes.create().url"
          class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Attribute
        </Link>
      </div>

      <!-- Filters Card -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search -->
          <div class="relative">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="search"
                @input="performSearch"
                type="text"
                placeholder="Search by name or code..."
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
              />
            </div>
          </div>

          <!-- Type Filter -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Type</label>
            <div class="relative">
              <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <select
                v-model="typeFilter"
                @change="applyFilters"
                class="w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
              >
                <option value="">All Types</option>
                <option value="text">Text</option>
                <option value="textarea">Textarea</option>
                <option value="select">Select</option>
                <option value="multiselect">Multi-select</option>
                <option value="boolean">Boolean</option>
                <option value="date">Date</option>
                <option value="price">Price</option>
              </select>
              <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
              </div>
            </div>
          </div>

          <!-- Clear Filters Button -->
          <div class="flex items-end">
            <button
              v-if="search || typeFilter"
              @click="clearFilters"
              class="w-full py-2.5 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors flex items-center justify-center gap-2"
            >
              <X class="w-4 h-4" />
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Bulk Actions -->
      <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="selectedAttributes.length > 0" class="bg-blue-600 rounded-xl shadow-lg p-3 text-white flex items-center justify-between sticky top-4 z-10 px-6">
          <span class="text-sm font-semibold flex items-center">
            <CheckCircle class="w-4 h-4 mr-2" />
            {{ selectedAttributes.length }} {{ selectedAttributes.length === 1 ? 'attribute' : 'attributes' }} selected
          </span>
          <div class="flex gap-2">
            <button
              @click="confirmBulkDelete"
              class="px-3 py-1.5 text-xs font-bold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors flex items-center uppercase tracking-wide"
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
              <th scope="col" class="w-12 px-6 py-4">
                <input
                  type="checkbox"
                  v-model="selectAll"
                  class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 transition-all"
                />
              </th>
              <th
                scope="col"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                @click="sortTable('name')"
              >
                <div class="flex items-center gap-1">
                  Name
                  <span v-if="sortBy === 'name'" class="text-blue-600 dark:text-blue-400">
                     <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                     <ArrowDown v-else class="w-3 h-3" />
                  </span>
                  <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                </div>
              </th>
              <th
                scope="col"
                class="hidden md:table-cell px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer group hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                @click="sortTable('code')"
              >
                <div class="flex items-center gap-1">
                  Code
                  <span v-if="sortBy === 'code'" class="text-blue-600 dark:text-blue-400">
                     <ArrowUp v-if="sortOrder === 'asc'" class="w-3 h-3" />
                     <ArrowDown v-else class="w-3 h-3" />
                  </span>
                  <ArrowUpDown v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                </div>
              </th>
              <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Type
              </th>
              <th scope="col" class="hidden lg:table-cell px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Options
              </th>
              <th scope="col" class="hidden lg:table-cell px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Required
              </th>
              <th scope="col" class="hidden xl:table-cell px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Filterable
              </th>
              <th scope="col" class="hidden xl:table-cell px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Configurable
              </th>
              <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
            <template v-for="attribute in attributes.data" :key="attribute.id">
            <tr class="group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
              <td class="px-6 py-4 relative">
                <div class="flex items-center">
                  <button 
                    @click="toggleRow(attribute.id)" 
                    class="lg:hidden absolute left-2 p-1 text-blue-600 hover:text-blue-800 focus:outline-none"
                  >
                    <MinusCircle v-if="expandedRows.includes(attribute.id)" class="w-5 h-5 fill-blue-100 dark:fill-blue-900/30" />
                    <PlusCircle v-else class="w-5 h-5 fill-blue-50 dark:fill-blue-900/20" />
                  </button>
                  <input
                    type="checkbox"
                    :value="attribute.id"
                    v-model="selectedAttributes"
                    class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700 w-4 h-4 cursor-pointer transition-all ml-4 lg:ml-0"
                  />
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ attribute.name }}</div>
              </td>
              <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                <code class="text-xs bg-gray-100 dark:bg-gray-700/50 px-2 py-1 rounded text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-600 font-mono">{{ attribute.code }}</code>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium border shadow-sm', getTypeBadgeColor(attribute.type)]"
                >
                  {{ getTypeLabel(attribute.type) }}
                </span>
              </td>
              <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                <span v-if="['select', 'multiselect'].includes(attribute.type)" class="inline-flex items-center px-2 py-0.5 rounded bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-mono">
                  {{ attribute.options_count }}
                </span>
                <span v-else class="text-gray-300 dark:text-gray-600">-</span>
              </td>
              <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap text-center">
                <span v-if="attribute.is_required" class="text-green-600 dark:text-green-400 inline-flex items-center justify-center p-1 bg-green-50 dark:bg-green-900/20 rounded-full">
                  <CheckCircle class="w-4 h-4" />
                </span>
                <span v-else class="text-gray-300 dark:text-gray-600">
                   <MinusCircle class="w-4 h-4" />
                </span>
              </td>
              <td class="hidden xl:table-cell px-6 py-4 whitespace-nowrap text-center">
                <span v-if="attribute.is_filterable" class="text-green-600 dark:text-green-400 inline-flex items-center justify-center p-1 bg-green-50 dark:bg-green-900/20 rounded-full">
                   <CheckCircle class="w-4 h-4" />
                </span>
                <span v-else class="text-gray-300 dark:text-gray-600">
                   <MinusCircle class="w-4 h-4" />
                </span>
              </td>
              <td class="hidden xl:table-cell px-6 py-4 whitespace-nowrap text-center">
                <span v-if="attribute.is_configurable" class="text-green-600 dark:text-green-400 inline-flex items-center justify-center p-1 bg-green-50 dark:bg-green-900/20 rounded-full">
                   <CheckCircle class="w-4 h-4" />
                </span>
                <span v-else class="text-gray-300 dark:text-gray-600">
                   <MinusCircle class="w-4 h-4" />
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                  <Link
                    :href="attributeRoutes.edit(attribute.id).url"
                    class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                    title="Edit"
                  >
                    <Edit class="w-4 h-4" />
                  </Link>
                  <button
                    @click="confirmDelete(attribute.id)"
                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                    title="Delete"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <!-- Expanded Mobile Row -->
            <tr v-if="expandedRows.includes(attribute.id)" class="bg-gray-50/50 dark:bg-gray-900/50 lg:hidden">
               <td colspan="9" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                  <div class="grid grid-cols-2 gap-4 text-sm">
                     <div class="flex flex-col gap-1 md:hidden">
                        <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Code</span>
                         <code class="text-xs bg-gray-100 dark:bg-gray-700/50 px-2 py-1 rounded text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-600 font-mono w-fit">{{ attribute.code }}</code>
                     </div>
                     <div class="flex flex-col gap-1">
                        <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Options</span>
                        <span v-if="['select', 'multiselect'].includes(attribute.type)" class="text-gray-900 dark:text-gray-100 font-medium">
                           {{ attribute.options_count }} options
                        </span>
                        <span v-else class="text-gray-400 italic">N/A</span>
                     </div>
                     <div class="flex flex-col gap-1">
                        <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Settings</span>
                        <div class="flex flex-wrap gap-2">
                           <span v-if="attribute.is_required" class="inline-flex items-center text-[10px] text-green-700 bg-green-50 border border-green-100 px-1.5 py-0.5 rounded">Required</span>
                           <span v-if="attribute.is_filterable" class="inline-flex items-center text-[10px] text-blue-700 bg-blue-50 border border-blue-100 px-1.5 py-0.5 rounded">Filterable</span>
                           <span v-if="attribute.is_configurable" class="inline-flex items-center text-[10px] text-purple-700 bg-purple-50 border border-purple-100 px-1.5 py-0.5 rounded">Configurable</span>
                        </div>
                     </div>
                  </div>
               </td>
            </tr>
            </template>
            <tr v-if="attributes.data.length === 0">
              <td colspan="9" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                <div class="flex flex-col items-center">
                  <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                    <List class="w-8 h-8" />
                  </div>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">No attributes found</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm">Get started by creating your first attribute.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        </div>

        <!-- Pagination -->
        <div v-if="attributes.total > attributes.per_page" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
          <div class="flex-1 flex justify-between sm:hidden">
            <Link
              v-if="attributes.current_page > 1"
              :href="attributes.links[0].url || '#'"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Previous
            </Link>
            <Link
              v-if="attributes.current_page < attributes.last_page"
              :href="attributes.links[attributes.links.length - 1].url || '#'"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Next
            </Link>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ (attributes.current_page - 1) * attributes.per_page + 1 }}</span>
                to
                <span class="font-medium">{{ Math.min(attributes.current_page * attributes.per_page, attributes.total) }}</span>
                of
                <span class="font-medium">{{ attributes.total }}</span>
                results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <Link
                  v-for="(link, index) in attributes.links"
                  :key="index"
                  :href="link.url || '#'"
                  :class="[
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                    link.active
                      ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                    index === 0 ? 'rounded-l-md' : '',
                    index === attributes.links.length - 1 ? 'rounded-r-md' : ''
                  ]"
                >
                  <span v-html="link.label" />
                </Link>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity z-50"
      @click="showDeleteModal = false"
    >
      <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div
            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
            @click.stop
          >
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                  <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                  </svg>
                </div>
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                  <h3 class="text-base font-semibold leading-6 text-gray-900">Delete Attribute</h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to delete this attribute? This action cannot be undone.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
              <button
                @click="deleteAttribute"
                type="button"
                class="cursor-pointer inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
              >
                Delete
              </button>
              <button
                @click="showDeleteModal = false"
                type="button"
                class="cursor-pointer mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <div
      v-if="showBulkDeleteModal"
      class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity z-50"
      @click="showBulkDeleteModal = false"
    >
      <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div
            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
            @click.stop
          >
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                  <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                  </svg>
                </div>
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                  <h3 class="text-base font-semibold leading-6 text-gray-900">Delete Multiple Attributes</h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to delete {{ selectedAttributes.length }} selected {{ selectedAttributes.length === 1 ? 'attribute' : 'attributes' }}? This action cannot be undone.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
              <button
                @click="bulkDelete"
                type="button"
                class="cursor-pointer inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
              >
                Delete All
              </button>
              <button
                @click="showBulkDeleteModal = false"
                type="button"
                class="cursor-pointer mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
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

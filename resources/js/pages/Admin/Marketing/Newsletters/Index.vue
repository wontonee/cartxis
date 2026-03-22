<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {
  Search,
  Filter,
  Download,
  Mail,
  ArrowUpDown,
  ChevronLeft,
  ChevronRight,
  Trash2,
  CheckSquare,
  Users,
  UserCheck,
  UserX,
  RefreshCw,
  X,
} from 'lucide-vue-next';

interface Subscriber {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  is_guest: boolean;
  updated_at: string;
}

interface PaginatedSubscribers {
  data: Subscriber[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
  links: { url: string | null; label: string; active: boolean }[];
}

interface Stats {
  total: number;
  registered: number;
  guest: number;
}

const props = defineProps<{
  subscribers: PaginatedSubscribers;
  filters: { search?: string; type?: string; sort_by?: string; sort_order?: string };
  stats: Stats;
}>();

const search    = ref(props.filters.search    || '');
const typeFilter = ref(props.filters.type     || '');
const sortBy    = ref(props.filters.sort_by   || 'updated_at');
const sortOrder = ref(props.filters.sort_order || 'desc');

const showFilters  = ref(false);
const selectAll    = ref(false);
const selectedIds  = ref<number[]>([]);

const applyFilters = () => {
  router.get('/admin/marketing/newsletters', {
    search: search.value || undefined,
    type: typeFilter.value || undefined,
    sort_by: sortBy.value,
    sort_order: sortOrder.value,
  }, { preserveState: true, preserveScroll: true });
};

const clearFilters = () => {
  search.value = '';
  typeFilter.value = '';
  sortBy.value = 'updated_at';
  sortOrder.value = 'desc';
  applyFilters();
};

watch(search, (val, old) => {
  if (val === '' && old !== '') applyFilters();
});

const hasActiveFilters = computed(() => !!(search.value || typeFilter.value));

const toggleSort = (field: string) => {
  if (sortBy.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = field;
    sortOrder.value = 'asc';
  }
  applyFilters();
};

// Selection
const toggleSelectAll = () => {
  selectAll.value = !selectAll.value;
  selectedIds.value = selectAll.value ? props.subscribers.data.map(s => s.id) : [];
};
const toggleSelect = (id: number) => {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter(i => i !== id);
  } else {
    selectedIds.value = [...selectedIds.value, id];
  }
  selectAll.value = selectedIds.value.length === props.subscribers.data.length;
};

// Unsubscribe single
const unsubscribe = (id: number) => {
  if (!confirm('Remove this subscriber from the newsletter list?')) return;
  router.delete(`/admin/marketing/newsletters/${id}`, {
    preserveScroll: true,
    onSuccess: () => {
      selectedIds.value = selectedIds.value.filter(i => i !== id);
    },
  });
};

// Bulk unsubscribe
const bulkUnsubscribe = () => {
  if (!selectedIds.value.length) return;
  if (!confirm(`Remove ${selectedIds.value.length} subscriber(s) from the newsletter list?`)) return;
  router.post('/admin/marketing/newsletters/bulk-unsubscribe', { ids: selectedIds.value }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedIds.value = [];
      selectAll.value = false;
    },
  });
};

// CSV export (passes current filters)
const exportUrl = computed(() => {
  const params = new URLSearchParams();
  if (search.value)    params.set('search', search.value);
  if (typeFilter.value) params.set('type', typeFilter.value);
  return '/admin/marketing/newsletters/export?' + params.toString();
});

const formatDate = (iso: string) =>
  new Date(iso).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
</script>

<template>
  <AdminLayout>
    <Head title="Newsletter Subscribers" />

    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Newsletter Subscribers</h1>
          <p class="mt-1 text-sm text-gray-500">Manage all email newsletter subscribers</p>
        </div>
        <div class="flex items-center gap-3">
          <a
            :href="exportUrl"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <Download class="w-4 h-4" />
            Export CSV
          </a>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
          <div class="p-3 rounded-lg bg-blue-50">
            <Mail class="w-5 h-5 text-blue-600" />
          </div>
          <div>
            <p class="text-sm text-gray-500">Total Subscribers</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.total.toLocaleString() }}</p>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
          <div class="p-3 rounded-lg bg-green-50">
            <UserCheck class="w-5 h-5 text-green-600" />
          </div>
          <div>
            <p class="text-sm text-gray-500">Registered</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.registered.toLocaleString() }}</p>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
          <div class="p-3 rounded-lg bg-orange-50">
            <Users class="w-5 h-5 text-orange-600" />
          </div>
          <div>
            <p class="text-sm text-gray-500">Guest</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.guest.toLocaleString() }}</p>
          </div>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-4 flex flex-col sm:flex-row gap-3">
          <!-- Search -->
          <div class="relative flex-1">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="search"
              type="text"
              placeholder="Search by name or email…"
              class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              @keyup.enter="applyFilters"
            />
          </div>

          <div class="flex items-center gap-2">
            <button
              class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium border rounded-lg transition-colors"
              :class="showFilters || hasActiveFilters
                ? 'bg-blue-50 border-blue-300 text-blue-700'
                : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'"
              @click="showFilters = !showFilters"
            >
              <Filter class="w-4 h-4" />
              Filters
              <span v-if="hasActiveFilters" class="w-2 h-2 rounded-full bg-blue-500" />
            </button>

            <button
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors"
              @click="applyFilters"
            >
              Search
            </button>
          </div>
        </div>

        <!-- Expanded Filters -->
        <div v-if="showFilters" class="px-4 pb-4 flex flex-wrap gap-4 border-t border-gray-100 pt-4">
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Type</label>
            <select
              v-model="typeFilter"
              class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="applyFilters"
            >
              <option value="">All</option>
              <option value="registered">Registered</option>
              <option value="guest">Guest</option>
            </select>
          </div>

          <div v-if="hasActiveFilters" class="flex items-end">
            <button
              class="inline-flex items-center gap-1 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
              @click="clearFilters"
            >
              <X class="w-3 h-3" />
              Clear
            </button>
          </div>
        </div>

        <!-- Bulk Action Bar -->
        <div
          v-if="selectedIds.length"
          class="px-4 py-3 bg-blue-50 border-t border-blue-100 flex items-center justify-between"
        >
          <span class="text-sm text-blue-700 font-medium">{{ selectedIds.length }} selected</span>
          <button
            class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-red-700 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition-colors"
            @click="bulkUnsubscribe"
          >
            <UserX class="w-4 h-4" />
            Unsubscribe Selected
          </button>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-t border-b border-gray-200">
              <tr>
                <th class="px-4 py-3 text-left w-10">
                  <input
                    type="checkbox"
                    :checked="selectAll"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    @change="toggleSelectAll"
                  />
                </th>
                <th class="px-4 py-3 text-left font-medium text-gray-700">
                  <button class="inline-flex items-center gap-1 hover:text-gray-900" @click="toggleSort('first_name')">
                    Name
                    <ArrowUpDown class="w-3 h-3" />
                  </button>
                </th>
                <th class="px-4 py-3 text-left font-medium text-gray-700">
                  <button class="inline-flex items-center gap-1 hover:text-gray-900" @click="toggleSort('email')">
                    Email
                    <ArrowUpDown class="w-3 h-3" />
                  </button>
                </th>
                <th class="px-4 py-3 text-left font-medium text-gray-700">Type</th>
                <th class="px-4 py-3 text-left font-medium text-gray-700">
                  <button class="inline-flex items-center gap-1 hover:text-gray-900" @click="toggleSort('updated_at')">
                    Subscribed
                    <ArrowUpDown class="w-3 h-3" />
                  </button>
                </th>
                <th class="px-4 py-3 text-right font-medium text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-if="!subscribers.data.length">
                <td colspan="6" class="px-4 py-16 text-center text-gray-400">
                  <Mail class="w-10 h-10 mx-auto mb-3 text-gray-300" />
                  <p class="font-medium">No subscribers found</p>
                  <p class="text-sm mt-1">Try adjusting your search or filters.</p>
                </td>
              </tr>
              <tr
                v-for="sub in subscribers.data"
                :key="sub.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-4 py-3">
                  <input
                    type="checkbox"
                    :checked="selectedIds.includes(sub.id)"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    @change="toggleSelect(sub.id)"
                  />
                </td>
                <td class="px-4 py-3 font-medium text-gray-900">
                  {{ sub.first_name }} {{ sub.last_name }}
                </td>
                <td class="px-4 py-3 text-gray-600">{{ sub.email }}</td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                    :class="sub.is_guest
                      ? 'bg-orange-100 text-orange-700'
                      : 'bg-green-100 text-green-700'"
                  >
                    {{ sub.is_guest ? 'Guest' : 'Registered' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-gray-500">{{ formatDate(sub.updated_at) }}</td>
                <td class="px-4 py-3 text-right">
                  <button
                    class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-600 hover:text-red-800 border border-red-200 hover:border-red-300 rounded-lg hover:bg-red-50 transition-colors"
                    @click="unsubscribe(sub.id)"
                  >
                    <Trash2 class="w-3 h-3" />
                    Unsubscribe
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div
          v-if="subscribers.last_page > 1"
          class="px-4 py-4 border-t border-gray-200 flex items-center justify-between"
        >
          <p class="text-sm text-gray-500">
            Showing {{ subscribers.from }}–{{ subscribers.to }} of {{ subscribers.total }} subscribers
          </p>
          <div class="flex items-center gap-1">
            <button
              v-for="link in subscribers.links"
              :key="link.label"
              :disabled="!link.url"
              class="px-3 py-1 text-sm rounded-lg border transition-colors"
              :class="link.active
                ? 'bg-blue-600 text-white border-blue-600'
                : link.url
                  ? 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                  : 'bg-white text-gray-300 border-gray-200 cursor-not-allowed'"
              @click="link.url && router.get(link.url, {}, { preserveScroll: true })"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

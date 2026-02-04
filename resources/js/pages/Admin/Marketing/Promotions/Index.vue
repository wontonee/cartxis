<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useCurrency } from '@/composables/useCurrency';

interface Promotion {
  id: number;
  name: string;
  description: string | null;
  type: string;
  discount_type: string;
  discount_value: number;
  is_active: boolean;
  show_badge: boolean;
  badge_text: string | null;
  badge_color: string;
  badge_bg_color: string;
  start_date: string | null;
  end_date: string | null;
  usage_count: number;
  usage_limit: number | null;
  total_revenue_generated: number;
  priority: number;
  created_at: string;
}

interface Filters {
  search?: string;
  status?: string;
  type?: string;
  sort_by?: string;
  sort_order?: string;
}

interface Stats {
  total: number;
  active: number;
  usage_count: number;
  revenue_generated: number;
}

const props = defineProps<{
  promotions: {
    data: Promotion[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: any[];
  };
  filters: Filters;
  stats: Stats;
  promotionTypes: Record<string, string>;
}>();

const filters = ref({
  search: props.filters.search || '',
  status: props.filters.status || '',
  type: props.filters.type || '',
});

const selectedIds = ref<number[]>([]);
const selectAll = ref(false);

const { formatPrice } = useCurrency();

const search = () => {
  router.get('/admin/marketing/promotions', filters.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedIds.value = props.promotions.data.map(p => p.id);
  } else {
    selectedIds.value = [];
  }
};

const toggleSelect = (id: number) => {
  const index = selectedIds.value.indexOf(id);
  if (index > -1) {
    selectedIds.value.splice(index, 1);
  } else {
    selectedIds.value.push(id);
  }
  selectAll.value = selectedIds.value.length === props.promotions.data.length;
};

const deletePromotion = (id: number) => {
  if (confirm('Are you sure you want to delete this promotion?')) {
    router.delete(`/admin/marketing/promotions/${id}`, {
      preserveScroll: true,
    });
  }
};

const bulkActivate = () => {
  if (selectedIds.value.length === 0) return;
  
  router.post('/admin/marketing/promotions/bulk-activate', {
    ids: selectedIds.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedIds.value = [];
      selectAll.value = false;
    },
  });
};

const bulkDeactivate = () => {
  if (selectedIds.value.length === 0) return;
  
  router.post('/admin/marketing/promotions/bulk-deactivate', {
    ids: selectedIds.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedIds.value = [];
      selectAll.value = false;
    },
  });
};

const bulkDelete = () => {
  if (selectedIds.value.length === 0) return;
  
  if (confirm(`Are you sure you want to delete ${selectedIds.value.length} promotions?`)) {
    router.post('/admin/marketing/promotions/bulk-delete', {
      ids: selectedIds.value,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        selectedIds.value = [];
        selectAll.value = false;
      },
    });
  }
};

const getTypeLabel = (type: string) => {
  return props.promotionTypes[type] || type;
};

const getTypeBadgeColor = (type: string) => {
  const colors: Record<string, string> = {
    'catalog_rule': 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
    'cart_rule': 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
    'bundle': 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300',
    'flash_sale': 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
    'tiered_pricing': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
  };
  return colors[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
};

const formatDiscount = (promotion: Promotion) => {
  if (promotion.discount_type === 'percentage') {
    return `${promotion.discount_value}%`;
  }
  return formatPrice(promotion.discount_value);
};

const formatDate = (date: string | null) => {
  if (!date) return 'No expiry';
  return new Intl.DateTimeFormat('en-US', { 
    month: '2-digit', 
    day: '2-digit', 
    year: 'numeric' 
  }).format(new Date(date));
};

const isExpired = (promotion: Promotion) => {
  if (!promotion.end_date) return false;
  return new Date(promotion.end_date) < new Date();
};

const isScheduled = (promotion: Promotion) => {
  if (!promotion.start_date) return false;
  return new Date(promotion.start_date) > new Date();
};
</script>

<template>
  <AdminLayout>
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Promotions</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage automatic discounts and promotional offers</p>
        </div>
        <Link
          href="/admin/marketing/promotions/create"
          class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Create Promotion
        </Link>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Promotions</p>
            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
          </div>
          <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active</p>
            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.active }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Applications</p>
            <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
          </div>
          <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.usage_count }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Revenue Impact</p>
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">{{ formatPrice(stats.revenue_generated) }}</p>
        </div>
      </div>

      <!-- Filters & Actions -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <label for="search" class="sr-only">Search</label>
            <div class="relative">
              <input
                id="search"
                v-model="filters.search"
                type="text"
                placeholder="Search promotions..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                @keyup.enter="search"
              />
              <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </div>
          </div>

          <!-- Status Filter -->
          <div>
            <label for="status" class="sr-only">Status</label>
            <select
              id="status"
              v-model="filters.status"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              @change="search"
            >
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <!-- Type Filter -->
          <div>
            <label for="type" class="sr-only">Type</label>
            <select
              id="type"
              v-model="filters.type"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              @change="search"
            >
              <option value="">All Types</option>
              <option v-for="(label, value) in promotionTypes" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
          </div>
        </div>

        <!-- Bulk Actions -->
        <div v-if="selectedIds.length > 0" class="mt-4 flex items-center gap-3">
          <span class="text-sm text-gray-600 dark:text-gray-400">{{ selectedIds.length }} selected</span>
          <button
            @click="bulkActivate"
            class="px-3 py-1.5 text-sm text-green-700 bg-green-50 rounded-lg hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/30"
          >
            Activate
          </button>
          <button
            @click="bulkDeactivate"
            class="px-3 py-1.5 text-sm text-yellow-700 bg-yellow-50 rounded-lg hover:bg-yellow-100 dark:bg-yellow-900/20 dark:text-yellow-400 dark:hover:bg-yellow-900/30"
          >
            Deactivate
          </button>
          <button
            @click="bulkDelete"
            class="px-3 py-1.5 text-sm text-red-700 bg-red-50 rounded-lg hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30"
          >
            Delete
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col" class="w-12 px-6 py-3">
                  <input
                    type="checkbox"
                    v-model="selectAll"
                    @change="toggleSelectAll"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600"
                  />
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Name & Type
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Discount
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Badge
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Usage
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Schedule
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="promotion in promotions.data" :key="promotion.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    :checked="selectedIds.includes(promotion.id)"
                    @change="toggleSelect(promotion.id)"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600"
                  />
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ promotion.name }}</span>
                    <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mt-1 w-fit', getTypeBadgeColor(promotion.type)]">
                      {{ getTypeLabel(promotion.type) }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDiscount(promotion) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div v-if="promotion.show_badge" class="flex items-center gap-2">
                    <div 
                      class="px-2 py-1 rounded text-xs font-medium"
                      :style="{ 
                        backgroundColor: promotion.badge_bg_color, 
                        color: promotion.badge_color 
                      }"
                    >
                      {{ promotion.badge_text || 'SALE' }}
                    </div>
                  </div>
                  <span v-else class="text-sm text-gray-400">No badge</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">
                    {{ promotion.usage_count }}
                    <span v-if="promotion.usage_limit" class="text-gray-500 dark:text-gray-400">/ {{ promotion.usage_limit }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    v-if="isExpired(promotion)"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300"
                  >
                    Expired
                  </span>
                  <span
                    v-else-if="isScheduled(promotion)"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300"
                  >
                    Scheduled
                  </span>
                  <span
                    v-else-if="promotion.is_active"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300"
                  >
                    Active
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300"
                  >
                    Inactive
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  <div class="flex flex-col">
                    <span>{{ formatDate(promotion.start_date) }}</span>
                    <span class="text-xs">to {{ formatDate(promotion.end_date) }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="`/admin/marketing/promotions/${promotion.id}/edit`"
                      class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                      title="Edit"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </Link>
                    <button
                      @click="deletePromotion(promotion.id)"
                      class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                      title="Delete"
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
        <div v-if="promotions.last_page > 1" class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
              Showing
              <span class="font-medium">{{ promotions.from }}</span>
              to
              <span class="font-medium">{{ promotions.to }}</span>
              of
              <span class="font-medium">{{ promotions.total }}</span>
              results
            </div>
            <div class="flex gap-2">
              <Link
                v-for="link in promotions.links"
                :key="link.label"
                :href="link.url"
                :class="[
                  'px-3 py-2 text-sm rounded-lg',
                  link.active
                    ? 'bg-blue-600 text-white'
                    : link.url
                    ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600'
                ]"
              >
                <span v-html="link.label" />
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import {
  PlusCircle,
  Search,
  Filter,
  Trash2,
  Edit,
  CheckCircle,
  X,
  ArrowUpDown,
  Tag,
  Percent,
  TrendingUp,
  CreditCard,
  Calendar,
  ChevronLeft,
  ChevronRight,
  MinusCircle,
  ShoppingBag
} from 'lucide-vue-next';

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
    prev_page_url: string | null;
    next_page_url: string | null;
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
const expandedRows = ref<number[]>([]);

const { formatPrice } = useCurrency();

const someSelected = computed(() => {
  return selectedIds.value.length > 0 && selectedIds.value.length < props.promotions.data.length;
});

const allSelected = computed(() => {
  return props.promotions.data.length > 0 && selectedIds.value.length === props.promotions.data.length;
});

const search = () => {
  router.get('/admin/marketing/promotions', {
    search: filters.value.search || undefined,
    status: filters.value.status || undefined,
    type: filters.value.type || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
    filters.value.search = '';
    filters.value.status = '';
    filters.value.type = '';
    search();
};

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedIds.value = [];
  } else {
    selectedIds.value = props.promotions.data.map(p => p.id);
  }
};

const toggleSelect = (id: number) => {
  const index = selectedIds.value.indexOf(id);
  if (index > -1) {
    selectedIds.value.splice(index, 1);
  } else {
    selectedIds.value.push(id);
  }
};

const toggleRow = (id: number) => {
  if (expandedRows.value.includes(id)) {
    expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
  } else {
    expandedRows.value.push(id);
  }
};

const deletePromotion = (id: number) => {
  if (confirm('Are you sure you want to delete this promotion?')) {
    router.delete(`/admin/marketing/promotions/${id}`, {
      preserveScroll: true,
      onSuccess: () => {
          selectedIds.value = selectedIds.value.filter(sid => sid !== id);
      }
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
  <AdminLayout title="Promotions">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Promotions</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage automatic discounts and promotional offers</p>
        </div>
        <Link
          href="/admin/marketing/promotions/create"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <PlusCircle class="w-4 h-4 mr-2" />
          Create Promotion
        </Link>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-blue-200 dark:hover:border-blue-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Promotions</p>
              <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg group-hover:bg-blue-100 dark:group-hover:bg-blue-900/40 transition-colors">
              <Tag class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-green-200 dark:hover:border-green-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active</p>
              <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.active }}</p>
            </div>
            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg group-hover:bg-green-100 dark:group-hover:bg-green-900/40 transition-colors">
              <CheckCircle class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-purple-200 dark:hover:border-purple-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Applications</p>
              <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.usage_count }}</p>
            </div>
            <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg group-hover:bg-purple-100 dark:group-hover:bg-purple-900/40 transition-colors">
              <ShoppingBag class="w-6 h-6 text-purple-600 dark:text-purple-400" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 group hover:border-red-200 dark:hover:border-red-800 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Revenue Impact</p>
              <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ formatPrice(stats.revenue_generated) }}</p>
            </div>
            <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg group-hover:bg-red-100 dark:group-hover:bg-red-900/40 transition-colors">
              <TrendingUp class="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
          </div>
        </div>
      </div>

      <!-- Filters & Actions -->
      <div class="flex flex-col gap-4">
         <!-- Search & Filter Grid -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
             <!-- Search -->
            <div class="md:col-span-6 lg:col-span-4">
               <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
               <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                  type="text"
                  v-model="filters.search"
                  placeholder="Search promotions..."
                  class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-gray-400"
                  @keyup.enter="search"
                />
              </div>
            </div>

            <!-- Status Filter -->
             <div class="md:col-span-3 lg:col-span-2">
              <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
              <div class="relative">
                <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <select
                  v-model="filters.status"
                  @change="search"
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

            <!-- Type Filter -->
             <div class="md:col-span-3 lg:col-span-2">
              <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Type</label>
              <div class="relative">
                <Tag class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <select
                  v-model="filters.type"
                   @change="search"
                  class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer"
                >
                  <option value="">All Types</option>
                  <option v-for="(label, value) in promotionTypes" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                  <ArrowUpDown class="w-3 h-3 text-gray-400" />
                </div>
              </div>
            </div>
            
            <!-- Filter Actions -->
            <div v-if="filters.search || filters.status || filters.type" class="md:col-span-12 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
              <button
                @click="clearFilters"
                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors flex items-center gap-2"
              >
                <X class="w-4 h-4" />
                Clear Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div
          v-if="someSelected || allSelected"
          class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 flex flex-wrap items-center justify-between gap-4 transition-all duration-300"
        >
          <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-blue-700 dark:text-blue-300">
              {{ selectedIds.length }} selected
            </span>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="bulkActivate"
              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60"
            >
              <CheckCircle class="w-4 h-4 mr-1.5" />
              Activate
            </button>
            <button
              @click="bulkDeactivate"
              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-orange-700 bg-orange-100 hover:bg-orange-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:bg-orange-900/40 dark:text-orange-300 dark:hover:bg-orange-900/60"
            >
              <X class="w-4 h-4 mr-1.5" />
              Deactivate
            </button>
            <button
              @click="bulkDelete"
              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60"
            >
              <Trash2 class="w-4 h-4 mr-1.5" />
              Delete Selected
            </button>
          </div>
        </div>
      </div>

      <!-- Content Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left">
                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      :checked="allSelected"
                      :indeterminate="someSelected"
                      @change="toggleSelectAll"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600"
                    />
                  </div>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Promotion
                </th>
                <th scope="col" class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Discount
                </th>
                <th scope="col" class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Badge
                </th>
                <th scope="col" class="hidden lg:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Usage
                </th>
                 <th scope="col" class="hidden lg:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Schedule
                </th>
                <th scope="col" class="hidden sm:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <template v-for="promotion in promotions.data" :key="promotion.id">
                 <tr :class="['group hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors', {'bg-blue-50 dark:bg-blue-900/10': selectedIds.includes(promotion.id)}]">
                  <td class="px-6 py-4 whitespace-nowrap relative">
                    <div class="flex items-center">
                      <button
                        @click="toggleRow(promotion.id)"
                        class="md:hidden absolute left-2 p-1 text-blue-600 hover:text-blue-800 focus:outline-none"
                      >
                        <MinusCircle v-if="expandedRows.includes(promotion.id)" class="w-5 h-5 fill-blue-100 dark:fill-blue-900/30" />
                        <PlusCircle v-else class="w-5 h-5 fill-blue-50 dark:fill-blue-900/20" />
                      </button>
                      <input
                        type="checkbox"
                        :checked="selectedIds.includes(promotion.id)"
                        @change="toggleSelect(promotion.id)"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600 ml-4 md:ml-0"
                      />
                    </div>
                  </td>

                   <!-- Promotion Details -->
                  <td class="px-6 py-4">
                     <div class="flex flex-col">
                        <span class="font-medium text-gray-900 dark:text-white">{{ promotion.name }}</span>
                        <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mt-1 w-fit', getTypeBadgeColor(promotion.type)]">
                        {{ getTypeLabel(promotion.type) }}
                        </span>
                     </div>
                  </td>

                  <!-- Discount -->
                   <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                      {{ formatDiscount(promotion) }}
                   </td>

                   <!-- Badge -->
                   <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
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

                   <!-- Usage -->
                   <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      <div class="text-sm text-gray-900 dark:text-white">
                        {{ promotion.usage_count }}
                        <span v-if="promotion.usage_limit" class="text-gray-500 dark:text-gray-400">/ {{ promotion.usage_limit }}</span>
                      </div>
                   </td>

                   <!-- Schedule -->
                   <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      <div class="flex flex-col">
                        <span>{{ formatDate(promotion.start_date) }}</span>
                        <span class="text-xs">to {{ formatDate(promotion.end_date) }}</span>
                      </div>
                   </td>

                   <!-- Status -->
                   <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
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

                   <!-- Actions -->
                   <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                        <Link
                          :href="`/admin/marketing/promotions/${promotion.id}/edit`"
                          class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                          title="Edit"
                        >
                           <Edit class="w-4 h-4" />
                        </Link>
                        <button
                          @click="deletePromotion(promotion.id)"
                          class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                          title="Delete"
                        >
                           <Trash2 class="w-4 h-4" />
                        </button>
                      </div>
                   </td>
                 </tr>

                 <!-- Mobile Expanded Rows -->
                 <tr v-if="expandedRows.includes(promotion.id)" class="md:hidden bg-gray-50 dark:bg-gray-700/30">
                    <td colspan="8" class="px-6 py-4 space-y-3">
                       <div class="grid grid-cols-2 gap-4 text-sm">
                           <div>
                            <span class="block text-xs font-medium text-gray-500 uppercase">Discount</span>
                            <span class="block mt-1 text-gray-900 dark:text-white font-medium">{{ formatDiscount(promotion) }}</span>
                          </div>
                          <div>
                            <span class="block text-xs font-medium text-gray-500 uppercase">Status</span>
                             <div class="mt-1">
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
                             </div>
                          </div>
                          <div class="col-span-2">
                             <span class="block text-xs font-medium text-gray-500 uppercase">Schedule</span>
                             <span class="block mt-1 text-gray-900 dark:text-white">{{ formatDate(promotion.start_date) }} - {{ formatDate(promotion.end_date) }}</span>
                          </div>
                       </div>
                    </td>
                 </tr>
              </template>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
           <div class="flex items-center justify-between">
             <div class="text-sm text-gray-500 dark:text-gray-400">
               Showing {{ promotions.from }} to {{ promotions.to }} of {{ promotions.total }} results
             </div>
             <div class="flex gap-2">
                <Link
                  v-if="promotions.prev_page_url"
                  :href="promotions.prev_page_url"
                  class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300"
                >
                  Previous
                </Link>
                <Link
                  v-if="promotions.next_page_url"
                  :href="promotions.next_page_url"
                  class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300"
                >
                  Next
                </Link>
             </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

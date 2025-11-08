<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useCurrency } from '@/composables/useCurrency';

interface Coupon {
  id: number;
  code: string;
  name: string;
  description: string | null;
  type: string;
  value: number;
  max_discount: number | null;
  min_order_amount: number | null;
  is_active: boolean;
  is_public: boolean;
  stackable: boolean;
  auto_apply: boolean;
  first_order_only: boolean;
  usage_limit_total: number | null;
  usage_limit_per_customer: number | null;
  usage_count: number;
  start_date: string;
  end_date: string | null;
  days_of_week: string[] | null;
  time_restrictions: { start: string; end: string } | null;
  customer_groups: number[] | null;
  min_account_age_days: number | null;
  applicable_products: number[] | null;
  excluded_products: number[] | null;
  applicable_categories: number[] | null;
  excluded_categories: number[] | null;
  buy_quantity: number | null;
  get_quantity: number | null;
  priority: number;
  created_at: string;
  updated_at: string;
}

interface Analytics {
  total_uses: number;
  total_discount: number;
  total_revenue: number;
  unique_customers: number;
  avg_order_value: number;
  recent_uses: Array<{
    id: number;
    customer_name: string;
    order_id: number;
    discount_amount: number;
    order_total: number;
    used_at: string;
  }>;
}

const props = defineProps<{
  coupon: Coupon;
  analytics: Analytics;
}>();

const activeTab = ref('general');

const { formatPrice } = useCurrency();

const form = useForm({
  code: props.coupon.code,
  name: props.coupon.name,
  description: props.coupon.description || '',
  type: props.coupon.type,
  value: props.coupon.value,
  max_discount: props.coupon.max_discount,
  min_order_amount: props.coupon.min_order_amount,
  is_active: props.coupon.is_active,
  is_public: props.coupon.is_public,
  stackable: props.coupon.stackable,
  auto_apply: props.coupon.auto_apply,
  first_order_only: props.coupon.first_order_only,
  usage_limit_total: props.coupon.usage_limit_total,
  usage_limit_per_customer: props.coupon.usage_limit_per_customer,
  start_date: props.coupon.start_date,
  end_date: props.coupon.end_date || '',
  days_of_week: props.coupon.days_of_week || [],
  time_restrictions: props.coupon.time_restrictions || { start: '', end: '' },
  customer_groups: props.coupon.customer_groups || [],
  min_account_age_days: props.coupon.min_account_age_days,
  applicable_products: props.coupon.applicable_products || [],
  excluded_products: props.coupon.excluded_products || [],
  applicable_categories: props.coupon.applicable_categories || [],
  excluded_categories: props.coupon.excluded_categories || [],
  buy_quantity: props.coupon.buy_quantity,
  get_quantity: props.coupon.get_quantity,
  priority: props.coupon.priority,
});

const daysOfWeek = [
  { value: 'monday', label: 'Monday' },
  { value: 'tuesday', label: 'Tuesday' },
  { value: 'wednesday', label: 'Wednesday' },
  { value: 'thursday', label: 'Thursday' },
  { value: 'friday', label: 'Friday' },
  { value: 'saturday', label: 'Saturday' },
  { value: 'sunday', label: 'Sunday' },
];

const showBuyXGetY = computed(() => form.type === 'buy_x_get_y');
const showMaxDiscount = computed(() => form.type === 'percentage');
const showValue = computed(() => form.type !== 'free_shipping');

const isExpired = computed(() => {
  if (!props.coupon.end_date) return false;
  return new Date(props.coupon.end_date) < new Date();
});

const toggleDay = (day: string) => {
  const index = form.days_of_week.indexOf(day);
  if (index > -1) {
    form.days_of_week.splice(index, 1);
  } else {
    form.days_of_week.push(day);
  }
};

const submit = () => {
  form.put(`/admin/marketing/coupons/${props.coupon.id}`, {
    onSuccess: () => {
      router.visit('/admin/marketing/coupons');
    },
  });
};

const formatDate = (date: string) => {
  return new Intl.DateTimeFormat('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date));
};
</script>

<template>
  <AdminLayout>
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Edit Coupon</h1>
          <p class="mt-1 text-sm text-gray-600">Update coupon "{{ coupon.code }}"</p>
        </div>
        <div class="flex gap-3">
          <Link
            href="/admin/marketing/coupons"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </Link>
          <button
            @click="submit"
            :disabled="form.processing"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Save Changes
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600">Total Uses</p>
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
          </div>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ analytics.total_uses }}</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600">Unique Customers</p>
            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
          </div>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ analytics.unique_customers }}</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600">Total Discount</p>
            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatPrice(analytics.total_discount) }}</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600">Avg Order Value</p>
            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
          </div>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatPrice(analytics.avg_order_value) }}</p>
        </div>
      </div>

      <!-- Tabs & Form -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow-sm">
            <div class="border-b border-gray-200">
              <nav class="flex -mb-px">
                <button
                  @click="activeTab = 'general'"
                  :class="[
                    'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                    activeTab === 'general'
                      ? 'border-blue-600 text-blue-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]"
                >
                  General
                </button>
                <button
                  @click="activeTab = 'conditions'"
                  :class="[
                    'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                    activeTab === 'conditions'
                      ? 'border-blue-600 text-blue-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]"
                >
                  Conditions
                </button>
                <button
                  @click="activeTab = 'limits'"
                  :class="[
                    'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                    activeTab === 'limits'
                      ? 'border-blue-600 text-blue-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]"
                >
                  Usage Limits
                </button>
                <button
                  @click="activeTab = 'restrictions'"
                  :class="[
                    'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                    activeTab === 'restrictions'
                      ? 'border-blue-600 text-blue-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]"
                >
                  Restrictions
                </button>
              </nav>
            </div>

            <form @submit.prevent="submit" class="p-6">
              <!-- General Tab -->
              <div v-show="activeTab === 'general'" class="space-y-6">
                <div>
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                  <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                          Coupon Code <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="code"
                          v-model="form.code"
                          type="text"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase"
                          :class="{ 'border-red-500': form.errors.code }"
                        />
                        <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
                      </div>

                      <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                          Display Name <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="name"
                          v-model="form.name"
                          type="text"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          :class="{ 'border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                      </div>
                    </div>

                    <div>
                      <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                      <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      ></textarea>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                      <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                          Discount Type <span class="text-red-500">*</span>
                        </label>
                        <select
                          id="type"
                          v-model="form.type"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          :class="{ 'border-red-500': form.errors.type }"
                        >
                          <option value="percentage">Percentage Discount</option>
                          <option value="fixed_amount">Fixed Amount</option>
                          <option value="free_shipping">Free Shipping</option>
                          <option value="buy_x_get_y">Buy X Get Y</option>
                          <option value="fixed_price">Fixed Price</option>
                        </select>
                        <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
                      </div>

                      <div v-if="showValue">
                        <label for="value" class="block text-sm font-medium text-gray-700 mb-1">
                          {{ form.type === 'percentage' ? 'Percentage (%)' : 'Amount ($)' }} <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="value"
                          v-model.number="form.value"
                          type="number"
                          step="0.01"
                          min="0"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          :class="{ 'border-red-500': form.errors.value }"
                        />
                        <p v-if="form.errors.value" class="mt-1 text-sm text-red-600">{{ form.errors.value }}</p>
                      </div>

                      <div v-if="showMaxDiscount">
                        <label for="max_discount" class="block text-sm font-medium text-gray-700 mb-1">Max Discount ($)</label>
                        <input
                          id="max_discount"
                          v-model.number="form.max_discount"
                          type="number"
                          step="0.01"
                          min="0"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                    </div>

                    <div v-if="showBuyXGetY" class="grid grid-cols-2 gap-4">
                      <div>
                        <label for="buy_quantity" class="block text-sm font-medium text-gray-700 mb-1">
                          Buy Quantity <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="buy_quantity"
                          v-model.number="form.buy_quantity"
                          type="number"
                          min="1"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                      <div>
                        <label for="get_quantity" class="block text-sm font-medium text-gray-700 mb-1">
                          Get Quantity <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="get_quantity"
                          v-model.number="form.get_quantity"
                          type="number"
                          min="1"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                          Start Date <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="start_date"
                          v-model="form.start_date"
                          type="datetime-local"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          :class="{ 'border-red-500': form.errors.start_date }"
                        />
                        <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p>
                      </div>
                      <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input
                          id="end_date"
                          v-model="form.end_date"
                          type="datetime-local"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p class="mt-1 text-xs text-gray-500">Leave empty for no expiration</p>
                      </div>
                    </div>

                    <div class="flex items-center gap-6">
                      <label class="flex items-center">
                        <input
                          type="checkbox"
                          v-model="form.is_active"
                          class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                      </label>
                      <label class="flex items-center">
                        <input
                          type="checkbox"
                          v-model="form.is_public"
                          class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <span class="ml-2 text-sm text-gray-700">Public</span>
                      </label>
                      <label class="flex items-center">
                        <input
                          type="checkbox"
                          v-model="form.auto_apply"
                          class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <span class="ml-2 text-sm text-gray-700">Auto Apply</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Conditions Tab -->
              <div v-show="activeTab === 'conditions'" class="space-y-6">
                <div>
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Order Conditions</h3>
                  <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label for="min_order_amount" class="block text-sm font-medium text-gray-700 mb-1">
                          Minimum Order Amount ($)
                        </label>
                        <input
                          id="min_order_amount"
                          v-model.number="form.min_order_amount"
                          type="number"
                          step="0.01"
                          min="0"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                      <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <input
                          id="priority"
                          v-model.number="form.priority"
                          type="number"
                          min="0"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                    </div>

                    <div class="flex items-center gap-6">
                      <label class="flex items-center">
                        <input
                          type="checkbox"
                          v-model="form.first_order_only"
                          class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <span class="ml-2 text-sm text-gray-700">First Order Only</span>
                      </label>
                      <label class="flex items-center">
                        <input
                          type="checkbox"
                          v-model="form.stackable"
                          class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <span class="ml-2 text-sm text-gray-700">Can Stack with Other Coupons</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Usage Limits Tab -->
              <div v-show="activeTab === 'limits'" class="space-y-6">
                <div>
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Usage Limits</h3>
                  <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label for="usage_limit_total" class="block text-sm font-medium text-gray-700 mb-1">
                          Total Usage Limit
                        </label>
                        <input
                          id="usage_limit_total"
                          v-model.number="form.usage_limit_total"
                          type="number"
                          min="1"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                      <div>
                        <label for="usage_limit_per_customer" class="block text-sm font-medium text-gray-700 mb-1">
                          Usage Limit Per Customer
                        </label>
                        <input
                          id="usage_limit_per_customer"
                          v-model.number="form.usage_limit_per_customer"
                          type="number"
                          min="1"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Restrictions Tab -->
              <div v-show="activeTab === 'restrictions'" class="space-y-6">
                <div>
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Time Restrictions</h3>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Days of Week</label>
                      <div class="grid grid-cols-4 gap-3">
                        <label
                          v-for="day in daysOfWeek"
                          :key="day.value"
                          class="flex items-center"
                        >
                          <input
                            type="checkbox"
                            :checked="form.days_of_week.includes(day.value)"
                            @change="toggleDay(day.value)"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                          />
                          <span class="ml-2 text-sm text-gray-700">{{ day.label }}</span>
                        </label>
                      </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label for="time_start" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                        <input
                          id="time_start"
                          v-model="form.time_restrictions.start"
                          type="time"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                      <div>
                        <label for="time_end" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                        <input
                          id="time_end"
                          v-model="form.time_restrictions.end"
                          type="time"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                      </div>
                    </div>
                  </div>
                </div>

                <div>
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Restrictions</h3>
                  <div>
                    <label for="min_account_age_days" class="block text-sm font-medium text-gray-700 mb-1">
                      Minimum Account Age (Days)
                    </label>
                    <input
                      id="min_account_age_days"
                      v-model.number="form.min_account_age_days"
                      type="number"
                      min="0"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Sidebar: Recent Usage -->
        <div>
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Recent Usage</h3>
            <p class="text-sm text-gray-600 mb-4">Last 10 uses of this coupon</p>

            <div v-if="analytics.recent_uses.length === 0" class="text-center py-8 text-gray-500">
              No usage yet
            </div>
            
            <div v-else class="space-y-4">
              <div
                v-for="use in analytics.recent_uses"
                :key="use.id"
                class="pb-4 border-b border-gray-200 last:border-0"
              >
                <div class="flex items-center justify-between mb-1">
                  <span class="font-medium text-sm text-gray-900">{{ use.customer_name }}</span>
                  <span class="text-xs text-gray-500">{{ formatDate(use.used_at) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm mb-1">
                  <span class="text-gray-600">Order #{{ use.order_id }}</span>
                  <span class="font-medium text-green-600">-{{ formatPrice(use.discount_amount) }}</span>
                </div>
                <div class="text-xs text-gray-500">
                  Order Total: {{ formatPrice(use.order_total) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

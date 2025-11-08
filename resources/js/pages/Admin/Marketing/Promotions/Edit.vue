<script setup lang="ts">
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Promotion {
  id: number;
  name: string;
  description: string | null;
  type: string;
  discount_type: string;
  discount_value: number;
  max_discount: number | null;
  is_active: boolean;
  stop_rules_processing: boolean;
  priority: number;
  stackable: boolean;
  stackable_with_coupons: boolean;
  show_badge: boolean;
  badge_text: string | null;
  badge_color: string;
  badge_bg_color: string;
  badge_position: string;
  show_countdown: boolean;
  start_date: string | null;
  end_date: string | null;
  usage_limit: number | null;
  usage_per_customer: number | null;
  internal_notes: string | null;
}

interface Props {
  promotion: Promotion;
  promotionTypes: Record<string, string>;
  badgePositions: Record<string, string>;
}

const props = defineProps<Props>();

const activeTab = ref('general');

const form = useForm({
  name: props.promotion.name,
  description: props.promotion.description || '',
  type: props.promotion.type,
  discount_type: props.promotion.discount_type,
  discount_value: props.promotion.discount_value,
  max_discount: props.promotion.max_discount,
  is_active: props.promotion.is_active,
  stop_rules_processing: props.promotion.stop_rules_processing,
  priority: props.promotion.priority,
  stackable: props.promotion.stackable,
  stackable_with_coupons: props.promotion.stackable_with_coupons,
  show_badge: props.promotion.show_badge,
  badge_text: props.promotion.badge_text || '',
  badge_color: props.promotion.badge_color,
  badge_bg_color: props.promotion.badge_bg_color,
  badge_position: props.promotion.badge_position,
  show_countdown: props.promotion.show_countdown,
  start_date: props.promotion.start_date ? props.promotion.start_date.slice(0, 16) : '',
  end_date: props.promotion.end_date ? props.promotion.end_date.slice(0, 16) : '',
  usage_limit: props.promotion.usage_limit,
  usage_per_customer: props.promotion.usage_per_customer,
  conditions: null,
  actions: null,
  bundle_products: null,
  price_tiers: null,
  internal_notes: props.promotion.internal_notes || '',
});

const submit = () => {
  form.put(`/admin/marketing/promotions/${props.promotion.id}`, {
    onSuccess: () => {
      // Redirect handled by controller
    },
  });
};
</script>

<template>
  <AdminLayout title="Edit Promotion">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Edit Promotion</h1>
          <p class="mt-1 text-sm text-gray-600">Update promotional campaign settings</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Tab Navigation -->
          <div class="bg-white shadow-sm rounded-lg">
            <div class="border-b border-gray-200">
              <nav class="flex -mb-px">
                <button
                  type="button"
                  @click="activeTab = 'general'"
                  :class="[
                    'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
                    activeTab === 'general'
                      ? 'border-blue-500 text-blue-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]"
                >
                  General Information
                </button>
                <button
                  type="button"
                  @click="activeTab = 'badge'"
                  :class="[
                    'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
                    activeTab === 'badge'
                      ? 'border-blue-500 text-blue-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]"
                >
                  Badge Configuration
                </button>
                <button
                  type="button"
                  @click="activeTab = 'conditions'"
                  :class="[
                    'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
                    activeTab === 'conditions'
                      ? 'border-blue-500 text-blue-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]"
                >
                  Conditions & Limits
                </button>
                <button
                  type="button"
                  @click="activeTab = 'schedule'"
                  :class="[
                    'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
                    activeTab === 'schedule'
                      ? 'border-blue-500 text-blue-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]"
                >
                  Schedule & Settings
                </button>
              </nav>
            </div>

            <div class="p-6">
              <!-- General Information Tab -->
              <div v-show="activeTab === 'general'" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Name -->
                  <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                      Promotion Name <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="name"
                      v-model="form.name"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="e.g., Summer Sale 2025"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                  </div>

                  <!-- Type -->
                  <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                      Promotion Type <span class="text-red-500">*</span>
                    </label>
                    <select
                      id="type"
                      v-model="form.type"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                      <option v-for="(label, value) in promotionTypes" :key="value" :value="value">
                        {{ label }}
                      </option>
                    </select>
                    <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
                  </div>

                  <!-- Discount Type -->
                  <div>
                    <label for="discount_type" class="block text-sm font-medium text-gray-700 mb-2">
                      Discount Type <span class="text-red-500">*</span>
                    </label>
                    <select
                      id="discount_type"
                      v-model="form.discount_type"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                      <option value="percentage">Percentage (%)</option>
                      <option value="fixed_amount">Fixed Amount ($)</option>
                    </select>
                    <p v-if="form.errors.discount_type" class="mt-1 text-sm text-red-600">{{ form.errors.discount_type }}</p>
                  </div>

                  <!-- Discount Value -->
                  <div>
                    <label for="discount_value" class="block text-sm font-medium text-gray-700 mb-2">
                      Discount Value <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="discount_value"
                      v-model.number="form.discount_value"
                      type="number"
                      step="0.01"
                      min="0"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="0.00"
                    />
                    <p v-if="form.errors.discount_value" class="mt-1 text-sm text-red-600">{{ form.errors.discount_value }}</p>
                  </div>

                  <!-- Max Discount -->
                  <div>
                    <label for="max_discount" class="block text-sm font-medium text-gray-700 mb-2">
                      Maximum Discount Amount
                    </label>
                    <input
                      id="max_discount"
                      v-model.number="form.max_discount"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Optional"
                    />
                    <p class="mt-1 text-xs text-gray-500">Leave empty for no limit</p>
                    <p v-if="form.errors.max_discount" class="mt-1 text-sm text-red-600">{{ form.errors.max_discount }}</p>
                  </div>

                  <!-- Description -->
                  <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                      Description
                    </label>
                    <textarea
                      id="description"
                      v-model="form.description"
                      rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Describe this promotion..."
                    ></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                  </div>

                  <!-- Priority -->
                  <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                      Priority
                    </label>
                    <input
                      id="priority"
                      v-model.number="form.priority"
                      type="number"
                      min="0"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="0"
                    />
                    <p class="mt-1 text-xs text-gray-500">Higher number = higher priority</p>
                    <p v-if="form.errors.priority" class="mt-1 text-sm text-red-600">{{ form.errors.priority }}</p>
                  </div>

                  <!-- Internal Notes -->
                  <div class="md:col-span-2">
                    <label for="internal_notes" class="block text-sm font-medium text-gray-700 mb-2">
                      Internal Notes
                    </label>
                    <textarea
                      id="internal_notes"
                      v-model="form.internal_notes"
                      rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Notes for internal use only..."
                    ></textarea>
                    <p v-if="form.errors.internal_notes" class="mt-1 text-sm text-red-600">{{ form.errors.internal_notes }}</p>
                  </div>
                </div>
              </div>

              <!-- Badge Configuration Tab -->
              <div v-show="activeTab === 'badge'" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Show Badge -->
                  <div class="md:col-span-2">
                    <label class="flex items-center">
                      <input
                        v-model="form.show_badge"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                      />
                      <span class="ml-2 text-sm font-medium text-gray-700">Show Badge on Products</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Display a promotional badge on qualifying products</p>
                  </div>

                  <template v-if="form.show_badge">
                    <!-- Badge Text -->
                    <div>
                      <label for="badge_text" class="block text-sm font-medium text-gray-700 mb-2">
                        Badge Text
                      </label>
                      <input
                        id="badge_text"
                        v-model="form.badge_text"
                        type="text"
                        maxlength="50"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 25% OFF"
                      />
                      <p v-if="form.errors.badge_text" class="mt-1 text-sm text-red-600">{{ form.errors.badge_text }}</p>
                    </div>

                    <!-- Badge Position -->
                    <div>
                      <label for="badge_position" class="block text-sm font-medium text-gray-700 mb-2">
                        Badge Position
                      </label>
                      <select
                        id="badge_position"
                        v-model="form.badge_position"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      >
                        <option v-for="(label, value) in badgePositions" :key="value" :value="value">
                          {{ label }}
                        </option>
                      </select>
                      <p v-if="form.errors.badge_position" class="mt-1 text-sm text-red-600">{{ form.errors.badge_position }}</p>
                    </div>

                    <!-- Badge Text Color -->
                    <div>
                      <label for="badge_color" class="block text-sm font-medium text-gray-700 mb-2">
                        Badge Text Color
                      </label>
                      <div class="flex gap-2">
                        <input
                          id="badge_color"
                          v-model="form.badge_color"
                          type="color"
                          class="h-10 w-20 border border-gray-300 rounded cursor-pointer"
                        />
                        <input
                          v-model="form.badge_color"
                          type="text"
                          pattern="^#[0-9A-Fa-f]{6}$"
                          class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="#000000"
                        />
                      </div>
                      <p v-if="form.errors.badge_color" class="mt-1 text-sm text-red-600">{{ form.errors.badge_color }}</p>
                    </div>

                    <!-- Badge Background Color -->
                    <div>
                      <label for="badge_bg_color" class="block text-sm font-medium text-gray-700 mb-2">
                        Badge Background Color
                      </label>
                      <div class="flex gap-2">
                        <input
                          id="badge_bg_color"
                          v-model="form.badge_bg_color"
                          type="color"
                          class="h-10 w-20 border border-gray-300 rounded cursor-pointer"
                        />
                        <input
                          v-model="form.badge_bg_color"
                          type="text"
                          pattern="^#[0-9A-Fa-f]{6}$"
                          class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="#FFFFFF"
                        />
                      </div>
                      <p v-if="form.errors.badge_bg_color" class="mt-1 text-sm text-red-600">{{ form.errors.badge_bg_color }}</p>
                    </div>

                    <!-- Badge Preview -->
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 mb-2">Badge Preview</label>
                      <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <div class="relative inline-block">
                          <div
                            :style="{
                              color: form.badge_color,
                              backgroundColor: form.badge_bg_color,
                            }"
                            class="px-3 py-1 rounded text-sm font-bold shadow-md"
                          >
                            {{ form.badge_text || 'Preview' }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </template>

                  <!-- Show Countdown -->
                  <div class="md:col-span-2">
                    <label class="flex items-center">
                      <input
                        v-model="form.show_countdown"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                      />
                      <span class="ml-2 text-sm font-medium text-gray-700">Show Countdown Timer</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Display a countdown timer for flash sales</p>
                  </div>
                </div>
              </div>

              <!-- Conditions & Limits Tab -->
              <div v-show="activeTab === 'conditions'" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Usage Limit -->
                  <div>
                    <label for="usage_limit" class="block text-sm font-medium text-gray-700 mb-2">
                      Total Usage Limit
                    </label>
                    <input
                      id="usage_limit"
                      v-model.number="form.usage_limit"
                      type="number"
                      min="1"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Unlimited"
                    />
                    <p class="mt-1 text-xs text-gray-500">Leave empty for unlimited uses</p>
                    <p v-if="form.errors.usage_limit" class="mt-1 text-sm text-red-600">{{ form.errors.usage_limit }}</p>
                  </div>

                  <!-- Usage Per Customer -->
                  <div>
                    <label for="usage_per_customer" class="block text-sm font-medium text-gray-700 mb-2">
                      Per Customer Usage Limit
                    </label>
                    <input
                      id="usage_per_customer"
                      v-model.number="form.usage_per_customer"
                      type="number"
                      min="1"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="Unlimited"
                    />
                    <p class="mt-1 text-xs text-gray-500">Leave empty for unlimited per customer</p>
                    <p v-if="form.errors.usage_per_customer" class="mt-1 text-sm text-red-600">{{ form.errors.usage_per_customer }}</p>
                  </div>

                  <!-- Stackable -->
                  <div class="md:col-span-2">
                    <label class="flex items-center">
                      <input
                        v-model="form.stackable"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                      />
                      <span class="ml-2 text-sm font-medium text-gray-700">Stackable with Other Promotions</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Allow this promotion to be combined with other promotions</p>
                  </div>

                  <!-- Stackable with Coupons -->
                  <div class="md:col-span-2">
                    <label class="flex items-center">
                      <input
                        v-model="form.stackable_with_coupons"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                      />
                      <span class="ml-2 text-sm font-medium text-gray-700">Stackable with Coupons</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Allow customers to use coupons alongside this promotion</p>
                  </div>

                  <!-- Stop Rules Processing -->
                  <div class="md:col-span-2">
                    <label class="flex items-center">
                      <input
                        v-model="form.stop_rules_processing"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                      />
                      <span class="ml-2 text-sm font-medium text-gray-700">Stop Further Rules Processing</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Don't apply other promotions after this one</p>
                  </div>
                </div>
              </div>

              <!-- Schedule & Settings Tab -->
              <div v-show="activeTab === 'schedule'" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Start Date -->
                  <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                      Start Date & Time
                    </label>
                    <input
                      id="start_date"
                      v-model="form.start_date"
                      type="datetime-local"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p>
                  </div>

                  <!-- End Date -->
                  <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                      End Date & Time
                    </label>
                    <input
                      id="end_date"
                      v-model="form.end_date"
                      type="datetime-local"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">{{ form.errors.end_date }}</p>
                  </div>

                  <!-- Is Active -->
                  <div class="md:col-span-2">
                    <label class="flex items-center">
                      <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                      />
                      <span class="ml-2 text-sm font-medium text-gray-700">Active</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Enable or disable this promotion immediately</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex items-center justify-end gap-4">
            <button
              type="button"
              @click="router.visit('/admin/marketing/promotions')"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ form.processing ? 'Updating...' : 'Update Promotion' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>


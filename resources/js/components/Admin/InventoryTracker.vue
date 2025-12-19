<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

interface InventoryAdjustment {
  id?: number;
  type: 'addition' | 'subtraction' | 'correction';
  quantity: number;
  reason: string;
  notes: string | null;
  created_at?: string;
  user?: { name: string };
}

interface Warehouse {
  id: number;
  name: string;
  code: string;
  quantity: number;
}

interface Props {
  productId: number;
  currentStock: number;
  minQuantity?: number;
  notifyStockQty?: number;
  manageStock?: boolean;
  warehouses?: Warehouse[];
  adjustmentHistory?: InventoryAdjustment[];
}

const props = withDefaults(defineProps<Props>(), {
  minQuantity: 1,
  notifyStockQty: 5,
  manageStock: true,
  warehouses: () => [],
  adjustmentHistory: () => [],
});

const emit = defineEmits(['update:stock']);

// State
const showAdjustmentForm = ref(false);
const newAdjustment = ref<InventoryAdjustment>({
  type: 'addition',
  quantity: 0,
  reason: '',
  notes: null,
});

const isSubmitting = ref(false);

// Computed
const stockStatus = computed(() => {
  if (!props.manageStock) return 'unmanaged';
  if (props.currentStock === 0) return 'out_of_stock';
  if (props.currentStock <= props.notifyStockQty) return 'low_stock';
  return 'in_stock';
});

const stockStatusColor = computed(() => {
  switch (stockStatus.value) {
    case 'out_of_stock': return 'bg-red-100 text-red-800';
    case 'low_stock': return 'bg-yellow-100 text-yellow-800';
    case 'in_stock': return 'bg-green-100 text-green-800';
    default: return 'bg-gray-100 text-gray-800';
  }
});

const stockStatusText = computed(() => {
  switch (stockStatus.value) {
    case 'out_of_stock': return 'Out of Stock';
    case 'low_stock': return 'Low Stock';
    case 'in_stock': return 'In Stock';
    default: return 'Not Managed';
  }
});

const totalWarehouseStock = computed(() => {
  return props.warehouses.reduce((sum, wh) => sum + wh.quantity, 0);
});

// Methods
const openAdjustmentForm = () => {
  showAdjustmentForm.value = true;
  newAdjustment.value = {
    type: 'addition',
    quantity: 0,
    reason: '',
    notes: null,
  };
};

const submitAdjustment = async () => {
  if (newAdjustment.value.quantity <= 0) {
    alert('Please enter a valid quantity');
    return;
  }

  if (!newAdjustment.value.reason.trim()) {
    alert('Please provide a reason for the adjustment');
    return;
  }

  isSubmitting.value = true;

  // Calculate new stock
  let newStock = props.currentStock;
  if (newAdjustment.value.type === 'addition') {
    newStock += newAdjustment.value.quantity;
  } else if (newAdjustment.value.type === 'subtraction') {
    newStock = Math.max(0, newStock - newAdjustment.value.quantity);
  } else if (newAdjustment.value.type === 'correction') {
    newStock = newAdjustment.value.quantity;
  }

  // Emit update
  emit('update:stock', {
    quantity: newStock,
    adjustment: newAdjustment.value,
  });

  // Close form
  showAdjustmentForm.value = false;
  isSubmitting.value = false;
};

const getAdjustmentTypeLabel = (type: string) => {
  switch (type) {
    case 'addition': return 'Stock Added';
    case 'subtraction': return 'Stock Removed';
    case 'correction': return 'Stock Correction';
    default: return type;
  }
};

const getAdjustmentTypeColor = (type: string) => {
  switch (type) {
    case 'addition': return 'text-green-600';
    case 'subtraction': return 'text-red-600';
    case 'correction': return 'text-blue-600';
    default: return 'text-gray-600';
  }
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

<template>
  <div class="bg-white rounded-lg shadow">
    <!-- Inventory Overview -->
    <div class="p-6 border-b border-gray-200">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Inventory Management</h3>
        <button
          @click="openAdjustmentForm"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors cursor-pointer"
        >
          Adjust Stock
        </button>
      </div>

      <!-- Stock Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Current Stock -->
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 mb-1">Current Stock</p>
              <p class="text-2xl font-bold text-gray-900">{{ currentStock }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
          </div>
          <div class="mt-2">
            <span :class="`px-2 py-1 text-xs font-medium rounded-full ${stockStatusColor}`">
              {{ stockStatusText }}
            </span>
          </div>
        </div>

        <!-- Low Stock Threshold -->
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 mb-1">Low Stock Alert</p>
              <p class="text-2xl font-bold text-gray-900">{{ notifyStockQty }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-2">Alert when stock falls below this level</p>
        </div>

        <!-- Minimum Quantity -->
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 mb-1">Min Quantity</p>
              <p class="text-2xl font-bold text-gray-900">{{ minQuantity }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
              </svg>
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-2">Minimum purchase quantity</p>
        </div>

        <!-- Stock Status -->
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 mb-1">Management</p>
              <p class="text-lg font-semibold text-gray-900">
                {{ manageStock ? 'Enabled' : 'Disabled' }}
              </p>
            </div>
            <div :class="`w-12 h-12 rounded-full flex items-center justify-center ${manageStock ? 'bg-green-100' : 'bg-gray-200'}`">
              <svg class="w-6 h-6" :class="manageStock ? 'text-green-600' : 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-2">
            {{ manageStock ? 'Stock tracking active' : 'No stock tracking' }}
          </p>
        </div>
      </div>
    </div>

    <!-- Warehouses (if multi-warehouse enabled) -->
    <div v-if="warehouses.length > 0" class="p-6 border-b border-gray-200">
      <h4 class="text-sm font-semibold text-gray-900 mb-4">Stock by Warehouse</h4>
      <div class="space-y-3">
        <div
          v-for="warehouse in warehouses"
          :key="warehouse.id"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <span class="text-sm font-semibold text-blue-600">{{ warehouse.code }}</span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">{{ warehouse.name }}</p>
              <p class="text-xs text-gray-500">Code: {{ warehouse.code }}</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-lg font-semibold text-gray-900">{{ warehouse.quantity }}</p>
            <p class="text-xs text-gray-500">units</p>
          </div>
        </div>
        
        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border-2 border-blue-200">
          <p class="text-sm font-semibold text-blue-900">Total Stock</p>
          <p class="text-lg font-bold text-blue-900">{{ totalWarehouseStock }} units</p>
        </div>
      </div>
    </div>

    <!-- Adjustment History -->
    <div class="p-6">
      <h4 class="text-sm font-semibold text-gray-900 mb-4">Stock Adjustment History</h4>
      
      <div v-if="adjustmentHistory.length > 0" class="space-y-3 max-h-96 overflow-y-auto">
        <div
          v-for="adjustment in adjustmentHistory"
          :key="adjustment.id"
          class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
        >
          <div class="flex-shrink-0">
            <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${
              adjustment.type === 'addition' ? 'bg-green-100' : 
              adjustment.type === 'subtraction' ? 'bg-red-100' : 'bg-blue-100'
            }`">
              <svg v-if="adjustment.type === 'addition'" class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <svg v-else-if="adjustment.type === 'subtraction'" class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" />
              </svg>
              <svg v-else class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </div>
          </div>
          
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between">
              <div>
                <p :class="`text-sm font-medium ${getAdjustmentTypeColor(adjustment.type)}`">
                  {{ getAdjustmentTypeLabel(adjustment.type) }}
                </p>
                <p class="text-sm text-gray-900 font-semibold">
                  {{ adjustment.type === 'addition' ? '+' : adjustment.type === 'subtraction' ? '-' : '' }}{{ adjustment.quantity }} units
                </p>
              </div>
              <span class="text-xs text-gray-500">
                {{ adjustment.created_at ? formatDate(adjustment.created_at) : 'Just now' }}
              </span>
            </div>
            
            <p class="text-sm text-gray-700 mt-1">{{ adjustment.reason }}</p>
            <p v-if="adjustment.notes" class="text-xs text-gray-500 mt-1">{{ adjustment.notes }}</p>
            <p v-if="adjustment.user" class="text-xs text-gray-500 mt-1">By: {{ adjustment.user.name }}</p>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-8 bg-gray-50 rounded-lg">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <p class="mt-2 text-sm text-gray-600">No stock adjustments recorded yet</p>
      </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <div
      v-if="showAdjustmentForm"
      class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Adjust Inventory</h3>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment Type</label>
              <select
                v-model="newAdjustment.type"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="addition">Add Stock</option>
                <option value="subtraction">Remove Stock</option>
                <option value="correction">Stock Correction</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ newAdjustment.type === 'correction' ? 'New Stock Level' : 'Quantity' }}
              </label>
              <input
                v-model.number="newAdjustment.quantity"
                type="number"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <p v-if="newAdjustment.type !== 'correction'" class="text-xs text-gray-500 mt-1">
                Current stock: {{ currentStock }} units
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
              <input
                v-model="newAdjustment.reason"
                type="text"
                placeholder="e.g., New shipment, Damaged goods, Stock count"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
              <textarea
                v-model="newAdjustment.notes"
                rows="3"
                placeholder="Additional details..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              ></textarea>
            </div>
          </div>

          <div class="flex gap-3 mt-6">
            <button
              @click="submitAdjustment"
              :disabled="isSubmitting"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer"
            >
              {{ isSubmitting ? 'Adjusting...' : 'Adjust Stock' }}
            </button>
            <button
              @click="showAdjustmentForm = false"
              :disabled="isSubmitting"
              class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

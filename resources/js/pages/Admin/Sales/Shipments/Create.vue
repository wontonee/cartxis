<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref, computed } from 'vue';

interface OrderItem {
  id: number;
  product_name: string;
  quantity: number;
  product: {
    id: number;
    name: string;
    sku: string;
  } | null;
}

interface Order {
  id: number;
  order_number: string;
  customer_email: string;
  status: string;
  user: {
    id: number;
    name: string;
    email: string;
  } | null;
  items: OrderItem[];
  shipments: Array<{
    id: number;
    status: string;
    shipment_items: Array<{
      order_item_id: number;
      quantity: number;
    }>;
  }>;
}

interface Props {
  order: Order | null;
  shiprocket_available: boolean;
  statuses: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const form = useForm({
  order_id: props.order?.id || null,
  shipment_mode: 'manual' as 'manual' | 'shiprocket',
  carrier: '',
  tracking_number: '',
  tracking_url: '',
  notes: '',
  items: [] as Array<{ order_item_id: number; quantity: number }>,
});

// Initialize items with remaining quantities if order is provided
if (props.order) {
  form.items = props.order.items.map(item => ({
    order_item_id: item.id,
    quantity: getRemainingQuantity(item.id),
  }));
}

function getRemainingQuantity(orderItemId: number): number {
  const orderItem = props.order?.items.find(i => i.id === orderItemId);
  if (!orderItem) return 0;

  const orderedQty = orderItem.quantity;
  
  // Calculate shipped quantity from all valid shipments
  let shippedQty = 0;
  props.order?.shipments.forEach(shipment => {
    if (!['cancelled', 'failed'].includes(shipment.status)) {
      const shipmentItem = shipment.shipment_items.find(si => si.order_item_id === orderItemId);
      if (shipmentItem) {
        shippedQty += shipmentItem.quantity;
      }
    }
  });

  return Math.max(0, orderedQty - shippedQty);
}

const selectedItems = computed(() => {
  return form.items.filter(item => item.quantity > 0);
});

const canSubmit = computed(() => {
  return form.order_id && selectedItems.value.length > 0;
});

function submit() {
  if (!canSubmit.value) return;

  // Filter out items with 0 quantity
  form.items = selectedItems.value;

  form.post('/admin/sales/shipments', {
    preserveScroll: true,
  });
}

function cancel() {
  if (props.order) {
    router.visit(`/admin/sales/orders/${props.order.id}`);
  } else {
    router.visit('/admin/sales/shipments');
  }
}
</script>

<template>
  <Head title="Create Shipment" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Create Shipment</h1>
        <p class="text-gray-600 mt-1">Create a new shipment for an order</p>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Shipment Method</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="border rounded-lg p-4 cursor-pointer" :class="form.shipment_mode === 'manual' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
              <div class="flex items-start gap-3">
                <input v-model="form.shipment_mode" type="radio" value="manual" class="mt-1" />
                <div>
                  <p class="font-medium text-gray-900">Manual Shipment</p>
                  <p class="text-sm text-gray-600 mt-1">You enter carrier/tracking details manually.</p>
                </div>
              </div>
            </label>

            <label
              class="border rounded-lg p-4"
              :class="[props.shiprocket_available ? 'cursor-pointer' : 'opacity-60 cursor-not-allowed', form.shipment_mode === 'shiprocket' ? 'border-cyan-500 bg-cyan-50' : 'border-gray-200']"
            >
              <div class="flex items-start gap-3">
                <input
                  v-model="form.shipment_mode"
                  type="radio"
                  value="shiprocket"
                  class="mt-1"
                  :disabled="!props.shiprocket_available"
                />
                <div>
                  <p class="font-medium text-gray-900">Shiprocket Shipment</p>
                  <p class="text-sm text-gray-600 mt-1">System creates shipment and sends it to Shiprocket automatically.</p>
                  <p v-if="!props.shiprocket_available" class="text-xs text-red-600 mt-2">Enable/configure Shiprocket in Settings to use this option.</p>
                </div>
              </div>
            </label>
          </div>
        </div>

        <!-- Order Information (if provided) -->
        <div v-if="order" class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Information</h3>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <div class="text-sm text-gray-600">Order Number</div>
              <div class="mt-1 font-medium text-gray-900">{{ order.order_number }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Customer</div>
              <div class="mt-1 font-medium text-gray-900">{{ order.user?.name || 'Guest' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Email</div>
              <div class="mt-1 font-medium text-gray-900">{{ order.customer_email }}</div>
            </div>
          </div>
        </div>

        <!-- Tracking Information -->
        <div v-if="form.shipment_mode === 'manual'" class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Tracking Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Carrier</label>
              <input
                v-model="form.carrier"
                type="text"
                placeholder="e.g., FedEx, UPS, DHL"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-500': form.errors.carrier }"
              />
              <p v-if="form.errors.carrier" class="mt-1 text-sm text-red-600">{{ form.errors.carrier }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tracking Number</label>
              <input
                v-model="form.tracking_number"
                type="text"
                placeholder="Enter tracking number"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-500': form.errors.tracking_number }"
              />
              <p v-if="form.errors.tracking_number" class="mt-1 text-sm text-red-600">{{ form.errors.tracking_number }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Tracking URL</label>
              <input
                v-model="form.tracking_url"
                type="url"
                placeholder="https://..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-500': form.errors.tracking_url }"
              />
              <p v-if="form.errors.tracking_url" class="mt-1 text-sm text-red-600">{{ form.errors.tracking_url }}</p>
            </div>
          </div>
        </div>

        <div v-else class="bg-cyan-50 border border-cyan-200 rounded-lg p-6">
          <h3 class="text-lg font-semibold text-cyan-900 mb-2">Shiprocket Flow Selected</h3>
          <p class="text-sm text-cyan-800">After clicking create, this shipment will be sent to Shiprocket automatically and AWB/tracking will be filled when available.</p>
        </div>

        <!-- Items to Ship -->
        <div v-if="order" class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Items to Ship</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Product
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    SKU
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ordered
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Remaining
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ship Qty
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(item, index) in order.items" :key="item.id">
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ item.product?.name || item.product_name || `Item #${item.id}` }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-500">{{ item.product?.sku || 'N/A' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <div class="text-sm text-gray-900">{{ item.quantity }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <div class="text-sm text-gray-900">{{ getRemainingQuantity(item.id) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <input
                      v-model.number="form.items[index].quantity"
                      type="number"
                      min="0"
                      :max="getRemainingQuantity(item.id)"
                      class="w-20 px-3 py-1 text-right border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-if="form.errors.items" class="mt-2 text-sm text-red-600">{{ form.errors.items }}</p>
        </div>

        <!-- Notes -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes (Optional)</h3>
          <textarea
            v-model="form.notes"
            rows="4"
            placeholder="Add any additional notes about this shipment..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :class="{ 'border-red-500': form.errors.notes }"
          ></textarea>
          <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</p>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-600">
            {{ form.shipment_mode === 'shiprocket'
              ? 'Shipment will be created and pushed to Shiprocket in one step.'
              : 'Shipment will be created using manual flow.' }}
          </p>
          <div class="flex space-x-3">
            <button
              type="button"
              @click="cancel"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="!canSubmit || form.processing"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ form.processing ? 'Creating...' : form.shipment_mode === 'shiprocket' ? 'Create & Send to Shiprocket' : 'Create Manual Shipment' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

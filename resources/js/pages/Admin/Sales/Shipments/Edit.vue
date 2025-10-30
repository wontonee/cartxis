<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { computed } from 'vue';

interface ShipmentItem {
  id: number;
  order_item_id: number;
  quantity: number;
  order_item: {
    id: number;
    product_name: string;
    quantity: number;
    product: {
      id: number;
      name: string;
      sku: string;
    };
  };
}

interface Shipment {
  id: number;
  shipment_number: string;
  status: string;
  carrier: string | null;
  tracking_number: string | null;
  tracking_url: string | null;
  notes: string | null;
  order: {
    id: number;
    order_number: string;
    customer_email: string;
    user: {
      id: number;
      name: string;
      email: string;
    } | null;
    items: Array<{
      id: number;
      quantity: number;
      product: {
        id: number;
        name: string;
        sku: string;
      };
    }>;
    shipments: Array<{
      id: number;
      status: string;
      shipment_items: Array<{
        order_item_id: number;
        quantity: number;
      }>;
    }>;
  };
  shipment_items: ShipmentItem[];
}

interface Props {
  shipment: Shipment;
  statuses: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const form = useForm({
  carrier: props.shipment.carrier || '',
  tracking_number: props.shipment.tracking_number || '',
  tracking_url: props.shipment.tracking_url || '',
  notes: props.shipment.notes || '',
  items: props.shipment.shipment_items.map(si => ({
    order_item_id: si.order_item_id,
    quantity: si.quantity,
  })),
});

function getRemainingQuantity(orderItemId: number): number {
  const orderItem = props.shipment.order.items.find(i => i.id === orderItemId);
  if (!orderItem) return 0;

  const orderedQty = orderItem.quantity;
  
  // Calculate shipped quantity from all valid shipments EXCEPT current one
  let shippedQty = 0;
  props.shipment.order.shipments.forEach(shipment => {
    if (shipment.id !== props.shipment.id && !['cancelled', 'failed'].includes(shipment.status)) {
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
  return selectedItems.value.length > 0;
});

function submit() {
  if (!canSubmit.value) return;

  // Filter out items with 0 quantity
  form.items = selectedItems.value;

  form.transform((data) => ({
    ...data,
    _method: 'put',
  })).post(`/admin/sales/shipments/${props.shipment.id}`, {
    preserveScroll: true,
  });
}

function cancel() {
  router.visit(`/admin/sales/shipments/${props.shipment.id}`);
}
</script>

<template>
  <Head :title="`Edit Shipment ${shipment.shipment_number}`" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Shipment {{ shipment.shipment_number }}</h1>
        <p class="text-gray-600 mt-1">Update shipment details and items</p>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Order Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Information</h3>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <div class="text-sm text-gray-600">Order Number</div>
              <div class="mt-1 font-medium text-gray-900">{{ shipment.order.order_number }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Customer</div>
              <div class="mt-1 font-medium text-gray-900">{{ shipment.order.user?.name || 'Guest' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Email</div>
              <div class="mt-1 font-medium text-gray-900">{{ shipment.order.customer_email }}</div>
            </div>
          </div>
        </div>

        <!-- Tracking Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
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

        <!-- Items to Ship -->
        <div class="bg-white rounded-lg shadow-sm p-6">
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
                    Available
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ship Qty
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(item, index) in shipment.order.items" :key="item.id">
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ item.product.name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-500">{{ item.product.sku }}</div>
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
        <div class="flex justify-end space-x-3">
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
            {{ form.processing ? 'Updating...' : 'Update Shipment' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

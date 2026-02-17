<script setup lang="ts">
import { router, Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';

interface ShipmentItem {
  id: number;
  quantity: number;
  order_item: {
    id: number;
    product_name: string;
    sku: string;
    quantity: number;
    price: number;
    product: {
      id: number;
      name: string;
      sku: string;
    } | null;
  } | null;
}

interface Shipment {
  id: number;
  shipment_number: string;
  status: string;
  carrier: string | null;
  tracking_number: string | null;
  tracking_url: string | null;
  shiprocket_order_id: string | null;
  shiprocket_shipment_id: string | null;
  shiprocket_awb_code: string | null;
  shiprocket_courier_name: string | null;
  shiprocket_status: string | null;
  shiprocket_synced_at: string | null;
  shipped_at: string | null;
  delivered_at: string | null;
  notes: string | null;
  created_at: string;
  order: {
    id: number;
    order_number: string;
    customer_email: string;
    status: string;
    user: {
      id: number;
      name: string;
      email: string;
    } | null;
  };
  shipment_items: ShipmentItem[];
}

interface Props {
  shipment: Shipment;
  statuses: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

// Modal states
const showUpdateTrackingModal = ref(false);
const showUpdateStatusModal = ref(false);
const showCancelModal = ref(false);

// Form data
const trackingForm = ref({
  carrier: props.shipment.carrier || '',
  tracking_number: props.shipment.tracking_number || '',
  tracking_url: props.shipment.tracking_url || '',
});

const statusForm = ref({
  status: props.shipment.status,
});

const cancelForm = ref({
  reason: '',
});

function getStatusBadge(status: string): string {
  const badges: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
    shipped: 'bg-blue-100 text-blue-800 border border-blue-200',
    in_transit: 'bg-indigo-100 text-indigo-800 border border-indigo-200',
    out_for_delivery: 'bg-purple-100 text-purple-800 border border-purple-200',
    delivered: 'bg-green-100 text-green-800 border border-green-200',
    failed: 'bg-red-100 text-red-800 border border-red-200',
    cancelled: 'bg-gray-100 text-gray-800 border border-gray-200',
  };
  return badges[status] || badges.pending;
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function markAsShipped() {
  router.post(`/admin/sales/shipments/${props.shipment.id}/mark-as-shipped`, {}, {
    preserveScroll: true,
  });
}

function updateTracking() {
  router.post(`/admin/sales/shipments/${props.shipment.id}/update-tracking`, trackingForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      showUpdateTrackingModal.value = false;
    },
  });
}

function updateStatus() {
  router.post(`/admin/sales/shipments/${props.shipment.id}/update-status`, statusForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      showUpdateStatusModal.value = false;
    },
  });
}

function cancelShipment() {
  router.post(`/admin/sales/shipments/${props.shipment.id}/cancel`, cancelForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      showCancelModal.value = false;
    },
  });
}

function editShipment() {
  router.visit(`/admin/sales/shipments/${props.shipment.id}/edit`);
}

function goToOrder() {
  router.visit(`/admin/sales/orders/${props.shipment.order.id}`);
}

function trackPackage() {
  if (props.shipment.tracking_url) {
    window.open(props.shipment.tracking_url, '_blank');
  }
}

function createInShiprocket() {
  router.post(`/admin/sales/shipments/${props.shipment.id}/shiprocket/create`, {}, {
    preserveScroll: true,
  });
}

function syncShiprocketStatus() {
  router.post(`/admin/sales/shipments/${props.shipment.id}/shiprocket/sync`, {}, {
    preserveScroll: true,
  });
}

const canEdit = ['pending', 'shipped'].includes(props.shipment.status);
const canCancel = !['delivered', 'cancelled'].includes(props.shipment.status);
</script>

<template>
  <Head :title="`Shipment ${shipment.shipment_number}`" />

  <AdminLayout :title="`Shipment ${shipment.shipment_number}`">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-start">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Shipment {{ shipment.shipment_number }}</h1>
          <p class="text-sm text-gray-500 mt-1">Created: {{ formatDate(shipment.created_at) }}</p>
        </div>
        <div class="flex gap-2">
          <button
            v-if="shipment.status === 'pending'"
            @click="markAsShipped"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Mark as Shipped
          </button>
          <button
            v-if="canEdit"
            @click="editShipment"
            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
          >
            Edit
          </button>
          <button
            @click="showUpdateTrackingModal = true"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
          >
            Update Tracking
          </button>
          <button
            v-if="!shipment.shiprocket_order_id"
            @click="createInShiprocket"
            class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700"
          >
            Send to Shiprocket
          </button>
          <button
            v-else
            @click="syncShiprocketStatus"
            class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700"
          >
            Sync Shiprocket Status
          </button>
          <button
            v-if="shipment.status !== 'delivered' && shipment.status !== 'cancelled'"
            @click="showUpdateStatusModal = true"
            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
          >
            Update Status
          </button>
          <button
            v-if="canCancel"
            @click="showCancelModal = true"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
          >
            Cancel
          </button>
        </div>
      </div>

      <div
        v-if="!shipment.shiprocket_order_id"
        class="bg-cyan-50 border border-cyan-200 rounded-lg p-4 flex items-center justify-between"
      >
        <div>
          <p class="text-sm font-semibold text-cyan-900">Next step: send this shipment to Shiprocket</p>
          <p class="text-sm text-cyan-800 mt-1">This will create Shiprocket order/shipment and fetch AWB if available.</p>
        </div>
        <button
          @click="createInShiprocket"
          class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700"
        >
          Send to Shiprocket
        </button>
      </div>

      <!-- Status & Info Cards -->
      <div class="grid grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-lg shadow-sm">
          <div class="text-sm text-gray-600">Status</div>
          <div class="mt-2">
            <span :class="['px-3 py-1 inline-flex text-sm font-semibold rounded-full', getStatusBadge(shipment.status)]">
              {{ shipment.status.replace('_', ' ') }}
            </span>
          </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm">
          <div class="text-sm text-gray-600">Order Number</div>
          <div class="mt-2">
            <button
              @click="goToOrder"
              class="text-blue-600 hover:text-blue-800 font-medium"
            >
              {{ shipment.order.order_number }}
            </button>
          </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm">
          <div class="text-sm text-gray-600">Carrier</div>
          <div class="mt-2 font-medium text-gray-900">{{ shipment.carrier || 'Not specified' }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm">
          <div class="text-sm text-gray-600">Items Count</div>
          <div class="mt-2 text-2xl font-bold text-gray-900">{{ shipment.shipment_items.length }}</div>
        </div>
      </div>

      <!-- Shipment Details -->
      <div class="grid grid-cols-2 gap-6">
        <!-- Tracking Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Tracking Information</h3>
          <div class="space-y-3">
            <div>
              <div class="text-sm text-gray-600">Carrier</div>
              <div class="mt-1 font-medium text-gray-900">{{ shipment.carrier || 'Not specified' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Tracking Number</div>
              <div class="mt-1 font-medium text-gray-900">{{ shipment.tracking_number || 'Not available' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Shiprocket Status</div>
              <div class="mt-1 font-medium text-gray-900">{{ shipment.shiprocket_status || 'Not synced' }}</div>
            </div>
            <div v-if="shipment.shiprocket_synced_at">
              <div class="text-sm text-gray-600">Last Synced</div>
              <div class="mt-1 font-medium text-gray-900">{{ formatDate(shipment.shiprocket_synced_at) }}</div>
            </div>
            <div v-if="shipment.tracking_url">
              <button
                @click="trackPackage"
                class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm"
              >
                Track Package
              </button>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Shiprocket Reference</h3>
          <div class="space-y-3">
            <div>
              <div class="text-sm text-gray-600">Shiprocket Order ID</div>
              <div class="mt-1 font-medium text-gray-900">{{ shipment.shiprocket_order_id || 'Not available' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Shiprocket Shipment ID</div>
              <div class="mt-1 font-medium text-gray-900">{{ shipment.shiprocket_shipment_id || 'Not available' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-600">AWB Code</div>
              <div class="mt-1 font-medium text-gray-900">{{ shipment.shiprocket_awb_code || 'Not available' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Shiprocket Courier</div>
              <div class="mt-1 font-medium text-gray-900">{{ shipment.shiprocket_courier_name || 'Not available' }}</div>
            </div>
          </div>
        </div>

        <!-- Customer Information -->
        <div class="bg-white rounded-lg shadow-sm p-6 col-span-2">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h3>
          <div class="space-y-3">
            <div>
              <div class="text-sm text-gray-600">Name</div>
              <div class="mt-1 font-medium text-gray-900">{{ shipment.order.user?.name || 'Guest' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Email</div>
              <div class="mt-1 font-medium text-gray-900">{{ shipment.order.customer_email }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Timeline -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Shipment Timeline</h3>
        <div class="space-y-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">Shipment Created</div>
              <div class="text-sm text-gray-500">{{ formatDate(shipment.created_at) }}</div>
            </div>
          </div>

          <div v-if="shipment.shipped_at" class="flex items-start">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">Shipped</div>
              <div class="text-sm text-gray-500">{{ formatDate(shipment.shipped_at) }}</div>
            </div>
          </div>

          <div v-if="shipment.delivered_at" class="flex items-start">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">Delivered</div>
              <div class="text-sm text-gray-500">{{ formatDate(shipment.delivered_at) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Shipped Items -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Shipped Items</h3>
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
                    Quantity Shipped
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in shipment.shipment_items" :key="item.id">
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ item.order_item?.product?.name || item.order_item?.product_name || `Item #${item.id}` }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-500">{{ item.order_item?.product?.sku || item.order_item?.sku || 'N/A' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <div class="text-sm text-gray-900">{{ item.quantity }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="shipment.notes" class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Notes</h3>
        <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ shipment.notes }}</p>
      </div>
    </div>

    <!-- Update Tracking Modal -->
    <div v-if="showUpdateTrackingModal" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Tracking Information</h3>
        <form @submit.prevent="updateTracking" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Carrier</label>
            <input
              v-model="trackingForm.carrier"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="e.g., FedEx, UPS, DHL"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tracking Number</label>
            <input
              v-model="trackingForm.tracking_number"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter tracking number"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tracking URL</label>
            <input
              v-model="trackingForm.tracking_url"
              type="url"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="https://..."
            />
          </div>
          <div class="flex justify-end space-x-2">
            <button
              type="button"
              @click="showUpdateTrackingModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
            >
              Update
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Update Status Modal -->
    <div v-if="showUpdateStatusModal" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Shipment Status</h3>
        <form @submit.prevent="updateStatus" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="statusForm.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option v-for="status in statuses" :key="status.value" :value="status.value">
                {{ status.label }}
              </option>
            </select>
          </div>
          <div class="flex justify-end space-x-2">
            <button
              type="button"
              @click="showUpdateStatusModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700"
            >
              Update
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Cancel Shipment Modal -->
    <div v-if="showCancelModal" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Cancel Shipment</h3>
        <form @submit.prevent="cancelShipment" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reason (Optional)</label>
            <textarea
              v-model="cancelForm.reason"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter reason for cancellation..."
            ></textarea>
          </div>
          <div class="bg-red-50 border border-red-200 rounded-lg p-3">
            <p class="text-sm text-red-800">This action cannot be undone.</p>
          </div>
          <div class="flex justify-end space-x-2">
            <button
              type="button"
              @click="showCancelModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              No, Keep It
            </button>
            <button
              type="submit"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700"
            >
              Yes, Cancel Shipment
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

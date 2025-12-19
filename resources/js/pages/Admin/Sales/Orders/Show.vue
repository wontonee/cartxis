<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';
import { useCurrency } from '@/composables/useCurrency';

interface Order {
  id: number;
  order_number: string;
  user?: {
    id: number;
    name: string;
    email: string;
  };
  customer_email: string;
  customer_phone: string;
  status: string;
  payment_status: string;
  payment_method: string;
  shipping_method: string;
  tracking_number?: string;
  subtotal: number;
  tax: number;
  shipping_cost: number;
  discount: number;
  total: number;
  notes?: string;
  created_at: string;
  items: Array<{
    id: number;
    product_id: number;
    product_name: string;
    quantity: number;
    price: number;
    total: number;
  }>;
  addresses: Array<{
    id: number;
    type: string;
    full_name: string;
    phone: string;
    address_line1: string;
    address_line2?: string;
    city: string;
    state: string;
    postal_code: string;
    country: string;
  }>;
  histories: Array<{
    id: number;
    status_from?: string;
    status_to?: string;
    payment_status_from?: string;
    payment_status_to?: string;
    comment?: string;
    customer_notified: boolean;
    admin_user?: {
      id: number;
      name: string;
    };
    created_at: string;
  }>;
  shipments: Array<{
    id: number;
    shipment_number: string;
    carrier: string;
    tracking_number?: string;
    tracking_url?: string;
    status: string;
    shipped_at?: string;
    delivered_at?: string;
    created_at: string;
    shipment_items: Array<{
      id: number;
      quantity: number;
      order_item: {
        id: number;
        product_name: string;
      };
    }>;
  }>;
}

interface Props {
  order: Order;
  statuses: Array<{ value: string; label: string }>;
  paymentStatuses: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const { formatPrice } = useCurrency();

const activeTab = ref('overview');
const showStatusModal = ref(false);
const showPaymentModal = ref(false);
const showCancelModal = ref(false);

const newStatus = ref('');
const statusComment = ref('');
const notifyCustomer = ref(false);

const newPaymentStatus = ref('');
const paymentComment = ref('');

const cancelReason = ref('');
const restoreStock = ref(true);

function createCreditMemo() {
  router.visit(`/admin/sales/credit-memos/create/${props.order.id}`);
}

function updateStatus() {
  if (!newStatus.value) return;

  router.post(`/admin/sales/orders/${props.order.id}/status`, {
    status: newStatus.value,
    comment: statusComment.value,
    notify_customer: notifyCustomer.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showStatusModal.value = false;
      newStatus.value = '';
      statusComment.value = '';
      notifyCustomer.value = false;
    },
  });
}

function updatePaymentStatus() {
  if (!newPaymentStatus.value) return;

  router.post(`/admin/sales/orders/${props.order.id}/payment-status`, {
    payment_status: newPaymentStatus.value,
    comment: paymentComment.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showPaymentModal.value = false;
      newPaymentStatus.value = '';
      paymentComment.value = '';
    },
  });
}

function cancelOrder() {
  if (!cancelReason.value) return;

  router.post(`/admin/sales/orders/${props.order.id}/cancel`, {
    reason: cancelReason.value,
    restore_stock: restoreStock.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showCancelModal.value = false;
      cancelReason.value = '';
      restoreStock.value = true;
    },
  });
}

function getStatusBadge(status: string): string {
  const badges: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    refunded: 'bg-purple-100 text-purple-800',
    failed: 'bg-gray-100 text-gray-800',
  };
  return badges[status] || badges.pending;
}

function formatDate(date: string): string {
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

const shippingAddress = props.order.addresses.find(a => a.type === 'shipping');
const billingAddress = props.order.addresses.find(a => a.type === 'billing');
</script>

<template>
  <Head :title="`Order #${order.order_number}`" />

  <AdminLayout :title="`Order #${order.order_number}`">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Order #{{ order.order_number }}</h1>
          <p class="mt-1 text-sm text-gray-600">{{ formatDate(order.created_at) }}</p>
        </div>
        <div class="flex gap-2">
          <button
            @click="createCreditMemo"
            v-if="order.payment_status === 'paid'"
            class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700"
          >
            Create Credit Memo
          </button>
          <button
            @click="showStatusModal = true"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Update Status
          </button>
          <button
            @click="showPaymentModal = true"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Update Payment
          </button>
          <button
            @click="showCancelModal = true"
            v-if="order.status !== 'cancelled'"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700"
          >
            Cancel Order
          </button>
        </div>
      </div>

      <!-- Status Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-lg shadow-sm">
          <div class="text-sm text-gray-600">Order Status</div>
          <div class="mt-2">
            <span :class="['px-3 py-1 inline-flex text-sm font-semibold rounded-full', getStatusBadge(order.status)]">
              {{ order.status }}
            </span>
          </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm">
          <div class="text-sm text-gray-600">Payment Status</div>
          <div class="mt-2">
            <span :class="['px-3 py-1 inline-flex text-sm font-semibold rounded-full', getStatusBadge(order.payment_status)]">
              {{ order.payment_status }}
            </span>
          </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm">
          <div class="text-sm text-gray-600">Order Total</div>
          <div class="mt-2 text-2xl font-bold text-gray-900">{{ formatPrice(order.total) }}</div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 px-6">
            <button
              v-for="tab in ['overview', 'items', 'addresses', 'shipments', 'history']"
              :key="tab"
              @click="activeTab = tab"
              :class="[
                'py-4 px-1 border-b-2 font-medium text-sm',
                activeTab === tab
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              {{ tab.charAt(0).toUpperCase() + tab.slice(1) }}
            </button>
          </nav>
        </div>

        <div class="p-6">
          <!-- Overview Tab -->
          <div v-if="activeTab === 'overview'" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <div class="text-sm font-medium text-gray-500">Customer</div>
                <div class="mt-1 text-sm text-gray-900">{{ order.user?.name || 'Guest' }}</div>
                <div class="text-sm text-gray-500">{{ order.customer_email }}</div>
                <div class="text-sm text-gray-500">{{ order.customer_phone }}</div>
              </div>
              <div>
                <div class="text-sm font-medium text-gray-500">Payment Method</div>
                <div class="mt-1 text-sm text-gray-900">{{ order.payment_method }}</div>
                <div class="text-sm font-medium text-gray-500 mt-3">Shipping Method</div>
                <div class="mt-1 text-sm text-gray-900">{{ order.shipping_method }}</div>
                <div v-if="order.tracking_number" class="text-sm font-medium text-gray-500 mt-3">Tracking Number</div>
                <div v-if="order.tracking_number" class="mt-1 text-sm text-gray-900">{{ order.tracking_number }}</div>
              </div>
            </div>
            <div v-if="order.notes">
              <div class="text-sm font-medium text-gray-500">Notes</div>
              <div class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ order.notes }}</div>
            </div>
          </div>

          <!-- Items Tab -->
          <div v-if="activeTab === 'items'">
            <table class="min-w-full divide-y divide-gray-200">
              <thead>
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="item in order.items" :key="item.id">
                  <td class="px-4 py-3 text-sm text-gray-900">{{ item.product_name }}</td>
                  <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ formatPrice(item.price) }}</td>
                  <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ item.quantity }}</td>
                  <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ formatPrice(item.total) }}</td>
                </tr>
              </tbody>
              <tfoot class="bg-gray-50">
                <tr>
                  <td colspan="3" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Subtotal:</td>
                  <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ formatPrice(order.subtotal) }}</td>
                </tr>
                <tr>
                  <td colspan="3" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Tax:</td>
                  <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ formatPrice(order.tax) }}</td>
                </tr>
                <tr>
                  <td colspan="3" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Shipping:</td>
                  <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ formatPrice(order.shipping_cost) }}</td>
                </tr>
                <tr v-if="order.discount > 0">
                  <td colspan="3" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Discount:</td>
                  <td class="px-4 py-3 text-sm font-medium text-red-600 text-right">-{{ formatPrice(order.discount) }}</td>
                </tr>
                <tr class="border-t-2 border-gray-300">
                  <td colspan="3" class="px-4 py-3 text-base font-bold text-gray-900 text-right">Total:</td>
                  <td class="px-4 py-3 text-base font-bold text-gray-900 text-right">{{ formatPrice(order.total) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Addresses Tab -->
          <div v-if="activeTab === 'addresses'" class="grid grid-cols-2 gap-6">
            <div v-if="shippingAddress">
              <h3 class="text-sm font-medium text-gray-900 mb-3">Shipping Address</h3>
              <div class="text-sm text-gray-600 space-y-1">
                <div>{{ shippingAddress.full_name }}</div>
                <div>{{ shippingAddress.phone }}</div>
                <div>{{ shippingAddress.address_line1 }}</div>
                <div v-if="shippingAddress.address_line2">{{ shippingAddress.address_line2 }}</div>
                <div>{{ shippingAddress.city }}, {{ shippingAddress.state }} {{ shippingAddress.postal_code }}</div>
                <div>{{ shippingAddress.country }}</div>
              </div>
            </div>
            <div v-if="billingAddress">
              <h3 class="text-sm font-medium text-gray-900 mb-3">Billing Address</h3>
              <div class="text-sm text-gray-600 space-y-1">
                <div>{{ billingAddress.full_name }}</div>
                <div>{{ billingAddress.phone }}</div>
                <div>{{ billingAddress.address_line1 }}</div>
                <div v-if="billingAddress.address_line2">{{ billingAddress.address_line2 }}</div>
                <div>{{ billingAddress.city }}, {{ billingAddress.state }} {{ billingAddress.postal_code }}</div>
                <div>{{ billingAddress.country }}</div>
              </div>
            </div>
          </div>

          <!-- Shipments Tab -->
          <div v-if="activeTab === 'shipments'">
            <div class="space-y-4">
              <div v-if="order.shipments && order.shipments.length > 0">
                <div v-for="shipment in order.shipments" :key="shipment.id" class="bg-white border border-gray-200 rounded-lg p-4">
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <div class="flex items-center gap-3 mb-2">
                        <a 
                          :href="`/admin/sales/shipments/${shipment.id}`"
                          class="text-lg font-semibold text-blue-600 hover:text-blue-800"
                        >
                          {{ shipment.shipment_number }}
                        </a>
                        <span :class="[
                          'px-2 py-1 text-xs font-medium rounded',
                          shipment.status === 'delivered' ? 'bg-green-100 text-green-800' :
                          shipment.status === 'shipped' ? 'bg-blue-100 text-blue-800' :
                          shipment.status === 'in_transit' ? 'bg-purple-100 text-purple-800' :
                          'bg-gray-100 text-gray-800'
                        ]">
                          {{ shipment.status.replace('_', ' ').toUpperCase() }}
                        </span>
                      </div>
                      
                      <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                          <span class="font-medium text-gray-700">Carrier:</span>
                          <span class="ml-2 text-gray-900">{{ shipment.carrier }}</span>
                        </div>
                        <div v-if="shipment.tracking_number">
                          <span class="font-medium text-gray-700">Tracking:</span>
                          <a 
                            v-if="shipment.tracking_url"
                            :href="shipment.tracking_url"
                            target="_blank"
                            class="ml-2 text-blue-600 hover:text-blue-800"
                          >
                            {{ shipment.tracking_number }}
                          </a>
                          <span v-else class="ml-2 text-gray-900">{{ shipment.tracking_number }}</span>
                        </div>
                        <div v-if="shipment.shipped_at">
                          <span class="font-medium text-gray-700">Shipped:</span>
                          <span class="ml-2 text-gray-900">{{ formatDate(shipment.shipped_at) }}</span>
                        </div>
                        <div v-if="shipment.delivered_at">
                          <span class="font-medium text-gray-700">Delivered:</span>
                          <span class="ml-2 text-gray-900">{{ formatDate(shipment.delivered_at) }}</span>
                        </div>
                      </div>

                      <div v-if="shipment.shipment_items && shipment.shipment_items.length > 0" class="mt-3">
                        <div class="text-xs font-medium text-gray-700 mb-1">Items:</div>
                        <div class="text-sm text-gray-600">
                          <span v-for="(item, index) in shipment.shipment_items" :key="item.id">
                            {{ item.order_item.product_name }} (×{{ item.quantity }})<span v-if="index < shipment.shipment_items.length - 1">, </span>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div v-else class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                  <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No shipments yet</h3>
                <p class="text-gray-600 mb-4">Create a shipment to start tracking this order's delivery.</p>
                <a 
                  :href="`/admin/sales/shipments/create?order_id=${order.id}`"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
                >
                  Create Shipment
                </a>
              </div>
            </div>
          </div>

          <!-- History Tab -->
          <div v-if="activeTab === 'history'">
            <div class="space-y-4">
              <div v-for="history in order.histories" :key="history.id" class="border-l-4 border-blue-500 pl-4 py-2">
                <div class="flex items-start justify-between">
                  <div>
                    <div v-if="history.status_from && history.status_to" class="text-sm font-medium text-gray-900">
                      Status changed: {{ history.status_from }} → {{ history.status_to }}
                    </div>
                    <div v-if="history.payment_status_from && history.payment_status_to" class="text-sm font-medium text-gray-900">
                      Payment: {{ history.payment_status_from }} → {{ history.payment_status_to }}
                    </div>
                    <div v-if="history.comment" class="mt-1 text-sm text-gray-600">{{ history.comment }}</div>
                    <div class="mt-1 text-xs text-gray-500">
                      {{ history.admin_user?.name || 'System' }} • {{ formatDate(history.created_at) }}
                      <span v-if="history.customer_notified" class="ml-2 text-blue-600">✓ Customer notified</span>
                    </div>
                  </div>
                </div>
              </div>
              <div v-if="order.histories.length === 0" class="text-center py-8 text-gray-500">
                No history available
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Status Modal -->
    <div v-if="showStatusModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75" @click="showStatusModal = false"></div>
        <div class="relative bg-white rounded-lg max-w-lg w-full p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Update Order Status</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">New Status</label>
              <select v-model="newStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">Select status...</option>
                <option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
              <textarea v-model="statusComment" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
            <div class="flex items-center">
              <input type="checkbox" v-model="notifyCustomer" class="h-4 w-4 rounded border-gray-300 text-blue-600" />
              <label class="ml-2 text-sm text-gray-700">Notify customer</label>
            </div>
          </div>
          <div class="mt-6 flex justify-end gap-2">
            <button @click="showStatusModal = false" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg">Cancel</button>
            <button @click="updateStatus" :disabled="!newStatus" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg disabled:opacity-50">Update</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Cancel Modal -->
    <div v-if="showCancelModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75" @click="showCancelModal = false"></div>
        <div class="relative bg-white rounded-lg max-w-lg w-full p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Cancel Order</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cancellation Reason</label>
              <textarea v-model="cancelReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Enter reason for cancellation..."></textarea>
            </div>
            <div class="flex items-center">
              <input type="checkbox" v-model="restoreStock" class="h-4 w-4 rounded border-gray-300 text-blue-600" />
              <label class="ml-2 text-sm text-gray-700">Restore product stock</label>
            </div>
          </div>
          <div class="mt-6 flex justify-end gap-2">
            <button @click="showCancelModal = false" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg">Cancel</button>
            <button @click="cancelOrder" :disabled="!cancelReason" class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg disabled:opacity-50">Cancel Order</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Status Update Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75" @click="showPaymentModal = false"></div>
        <div class="relative bg-white rounded-lg max-w-lg w-full p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Update Payment Status</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Current Payment Status</label>
              <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                <span :class="['px-3 py-1 inline-flex text-sm font-semibold rounded-full', getStatusBadge(order.payment_status)]">
                  {{ order.payment_status }}
                </span>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">New Payment Status</label>
              <select v-model="newPaymentStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">Select payment status...</option>
                <option v-for="status in paymentStatuses" :key="status.value" :value="status.value">{{ status.label }}</option>
              </select>
            </div>
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
              <div class="flex">
                <svg class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-blue-700">Updating payment status will be recorded in order history.</p>
              </div>
            </div>
          </div>
          <div class="mt-6 flex justify-end gap-2">
            <button @click="showPaymentModal = false" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg">Cancel</button>
            <button @click="updatePaymentStatus" :disabled="!newPaymentStatus" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg disabled:opacity-50">Update Payment Status</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

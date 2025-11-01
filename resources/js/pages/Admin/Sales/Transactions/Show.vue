<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Transaction } from '@/types/sales';
import { useCurrency } from '@/composables/useCurrency';

const props = defineProps<{
  transaction: Transaction;
  relatedTransactions: Transaction[];
}>();

const { formatPrice } = useCurrency();

const showRefundModal = ref(false);
const refundAmount = ref(props.transaction.amount);
const refundNotes = ref('');

const openRefundModal = () => {
  showRefundModal.value = true;
  refundAmount.value = props.transaction.amount;
};

const processRefund = () => {
  router.post(`/admin/sales/transactions/${props.transaction.id}/refund`, {
    amount: refundAmount.value,
    notes: refundNotes.value,
  }, {
    onSuccess: () => {
      showRefundModal.value = false;
      refundAmount.value = props.transaction.amount;
      refundNotes.value = '';
    },
  });
};

const retryTransaction = () => {
  if (confirm('Are you sure you want to retry this transaction?')) {
    router.post(`/admin/sales/transactions/${props.transaction.id}/retry`);
  }
};

const cancelTransaction = () => {
  if (confirm('Are you sure you want to cancel this transaction?')) {
    router.post(`/admin/sales/transactions/${props.transaction.id}/cancel`);
  }
};

const viewOrder = () => {
  router.visit(`/admin/sales/orders/${props.transaction.order_id}`);
};

const getStatusBadgeClass = (status: string) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    completed: 'bg-green-100 text-green-800 border-green-200',
    failed: 'bg-red-100 text-red-800 border-red-200',
    cancelled: 'bg-gray-100 text-gray-800 border-gray-200',
  };
  return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const getTypeBadgeClass = (type: string) => {
  const classes = {
    payment: 'bg-blue-100 text-blue-800 border-blue-200',
    refund: 'bg-purple-100 text-purple-800 border-purple-200',
    authorization: 'bg-indigo-100 text-indigo-800 border-indigo-200',
    capture: 'bg-teal-100 text-teal-800 border-teal-200',
  };
  return classes[type as keyof typeof classes] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const formatDate = (date?: string) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const formatJson = (data: any) => {
  return JSON.stringify(data, null, 2);
};
</script>

<template>
  <AdminLayout>
    <div class="container-fluid">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <div class="flex items-center gap-2 mb-2">
            <button
              @click="router.visit('/admin/sales/transactions')"
              class="text-gray-600 hover:text-gray-900"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <h1 class="text-2xl font-bold text-gray-900">Transaction Details</h1>
          </div>
          <p class="text-gray-600">{{ transaction.transaction_number }}</p>
        </div>

        <div class="flex items-center gap-2">
          <button
            v-if="transaction.status === 'completed' && transaction.type === 'payment'"
            @click="openRefundModal"
            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
            </svg>
            Process Refund
          </button>

          <button
            v-if="transaction.status === 'failed' && (transaction.type === 'payment' || transaction.type === 'capture')"
            @click="retryTransaction"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Retry
          </button>

          <button
            v-if="transaction.status === 'pending'"
            @click="cancelTransaction"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Cancel
          </button>

          <button
            @click="viewOrder"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center gap-2"
          >
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            View Order
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Transaction Information -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Transaction Information</h2>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm text-gray-600">Transaction Number</label>
                <p class="font-medium text-gray-900">{{ transaction.transaction_number }}</p>
              </div>

              <div>
                <label class="text-sm text-gray-600">Order Number</label>
                <p class="font-medium text-blue-600 hover:text-blue-800 cursor-pointer" @click="viewOrder">
                  {{ transaction.order?.order_number || 'N/A' }}
                </p>
              </div>

              <div>
                <label class="text-sm text-gray-600">Type</label>
                <p>
                  <span
                    :class="getTypeBadgeClass(transaction.type)"
                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border"
                  >
                    {{ transaction.type }}
                  </span>
                </p>
              </div>

              <div>
                <label class="text-sm text-gray-600">Status</label>
                <p>
                  <span
                    :class="getStatusBadgeClass(transaction.status)"
                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border"
                  >
                    {{ transaction.status }}
                  </span>
                </p>
              </div>

              <div>
                <label class="text-sm text-gray-600">Amount</label>
                <p class="font-medium text-gray-900 text-lg">{{ formatPrice(transaction.amount) }}</p>
              </div>

              <div>
                <label class="text-sm text-gray-600">Payment Method</label>
                <p class="font-medium text-gray-900">{{ transaction.payment_method }}</p>
              </div>

              <div>
                <label class="text-sm text-gray-600">Gateway</label>
                <p class="font-medium text-gray-900 capitalize">{{ transaction.gateway }}</p>
              </div>

              <div>
                <label class="text-sm text-gray-600">Gateway Transaction ID</label>
                <p class="font-mono text-sm text-gray-900">{{ transaction.gateway_transaction_id || 'N/A' }}</p>
              </div>

              <div>
                <label class="text-sm text-gray-600">Created At</label>
                <p class="font-medium text-gray-900">{{ formatDate(transaction.created_at) }}</p>
              </div>

              <div>
                <label class="text-sm text-gray-600">Processed At</label>
                <p class="font-medium text-gray-900">{{ formatDate(transaction.processed_at) }}</p>
              </div>
            </div>

            <div v-if="transaction.notes" class="mt-4 pt-4 border-t border-gray-200">
              <label class="text-sm text-gray-600">Notes</label>
              <p class="font-medium text-gray-900 mt-1">{{ transaction.notes }}</p>
            </div>
          </div>

          <!-- Gateway Response Data -->
          <div v-if="transaction.response_data" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Gateway Response</h2>
            <pre class="bg-gray-50 p-4 rounded-lg overflow-x-auto text-sm">{{ formatJson(transaction.response_data) }}</pre>
          </div>

          <!-- Related Transactions -->
          <div v-if="relatedTransactions.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Related Transactions</h2>
            <div class="space-y-3">
              <div
                v-for="related in relatedTransactions"
                :key="related.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer"
                @click="router.visit(`/admin/sales/transactions/${related.id}`)"
              >
                <div class="flex-1">
                  <p class="font-medium text-gray-900">{{ related.transaction_number }}</p>
                  <p class="text-sm text-gray-600">{{ formatDate(related.created_at) }}</p>
                </div>
                <div class="flex items-center gap-3">
                  <span
                    :class="getTypeBadgeClass(related.type)"
                    class="px-2 py-1 text-xs font-semibold rounded-full border"
                  >
                    {{ related.type }}
                  </span>
                  <span class="font-medium text-gray-900">{{ formatPrice(related.amount) }}</span>
                  <span
                    :class="getStatusBadgeClass(related.status)"
                    class="px-2 py-1 text-xs font-semibold rounded-full border"
                  >
                    {{ related.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Order Summary -->
          <div v-if="transaction.order" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Order Number:</span>
                <span class="text-sm font-medium text-blue-600 hover:text-blue-800 cursor-pointer" @click="viewOrder">
                  {{ transaction.order.order_number }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Customer:</span>
                <span class="text-sm font-medium text-gray-900">{{ transaction.order.customer_email }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Order Total:</span>
                <span class="text-sm font-medium text-gray-900">{{ formatPrice(transaction.order.total) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Order Status:</span>
                <span class="text-sm font-medium text-gray-900 capitalize">{{ transaction.order.status }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Payment Status:</span>
                <span class="text-sm font-medium text-gray-900 capitalize">{{ transaction.order.payment_status }}</span>
              </div>
            </div>
          </div>

          <!-- Invoice Info -->
          <div v-if="transaction.invoice" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Invoice</h2>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Invoice Number:</span>
                <span class="text-sm font-medium text-gray-900">{{ transaction.invoice.invoice_number }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Status:</span>
                <span class="text-sm font-medium text-gray-900 capitalize">{{ transaction.invoice.status }}</span>
              </div>
            </div>
          </div>

          <!-- Credit Memo Info -->
          <div v-if="transaction.credit_memo" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Credit Memo</h2>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Credit Memo #:</span>
                <span class="text-sm font-medium text-gray-900">{{ transaction.credit_memo.credit_memo_number }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Status:</span>
                <span class="text-sm font-medium text-gray-900 capitalize">{{ transaction.credit_memo.status }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Refund Modal -->
    <div v-if="showRefundModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showRefundModal = false"></div>
        
        <div class="relative bg-white rounded-lg max-w-md w-full p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Process Refund</h3>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Refund Amount</label>
              <input
                v-model.number="refundAmount"
                type="number"
                step="0.01"
                :max="transaction.amount"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              />
              <p class="text-xs text-gray-500 mt-1">Maximum: {{ formatPrice(transaction.amount) }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea
                v-model="refundNotes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                placeholder="Optional refund notes..."
              ></textarea>
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button
              @click="showRefundModal = false"
              class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="processRefund"
              class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
            >
              Process Refund
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

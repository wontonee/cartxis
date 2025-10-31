<script setup lang="ts">
import { router, Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';
import { useCurrency } from '@/composables/useCurrency';
import type { CreditMemo } from '@/types/sales';

interface Props {
  creditMemo: CreditMemo;
}

const props = defineProps<Props>();
const { formatPrice } = useCurrency();

const activeTab = ref('overview');

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const getStatusBadge = (status: string) => {
  const badges: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    refunded: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return badges[status] || 'bg-gray-100 text-gray-800';
};

const downloadPdf = () => {
  window.open(`/admin/sales/credit-memos/${props.creditMemo.id}/download-pdf`, '_blank');
};

const sendEmail = () => {
  router.post(`/admin/sales/credit-memos/${props.creditMemo.id}/send-email`, {}, {
    preserveScroll: true,
  });
};

const processRefund = () => {
  router.post(`/admin/sales/credit-memos/${props.creditMemo.id}/process-refund`, {}, {
    preserveScroll: true,
  });
};

const cancelCreditMemo = () => {
  router.post(`/admin/sales/credit-memos/${props.creditMemo.id}/cancel`, {}, {
    preserveScroll: true,
  });
};

const viewOrder = () => {
  router.visit(`/admin/sales/orders/${props.creditMemo.order_id}`);
};

const viewInvoice = () => {
  if (props.creditMemo.invoice_id) {
    router.visit(`/admin/sales/invoices/${props.creditMemo.invoice_id}`);
  }
};
</script>

<template>
  <Head :title="`Credit Memo ${creditMemo.credit_memo_number}`" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ creditMemo.credit_memo_number }}</h1>
          <p class="text-gray-600 mt-1">Credit memo for order {{ creditMemo.order?.order_number }}</p>
        </div>

        <div class="flex items-center space-x-3">
          <button
            v-if="creditMemo.status === 'pending'"
            @click="processRefund"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
          >
            Process Refund
          </button>
          <button
            @click="downloadPdf"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            Download PDF
          </button>
          <button
            @click="sendEmail"
            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2"
          >
            Send Email
          </button>
          <button
            v-if="creditMemo.status === 'pending'"
            @click="cancelCreditMemo"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
          >
            Cancel
          </button>
        </div>
      </div>

      <!-- Status Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="text-sm text-gray-600">Status</div>
          <div class="mt-2">
            <span :class="['px-3 py-1 inline-flex text-sm font-semibold rounded-full', getStatusBadge(creditMemo.status)]">
              {{ creditMemo.status }}
            </span>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="text-sm text-gray-600">Refund Method</div>
          <div class="mt-2 text-lg font-semibold text-gray-900 capitalize">{{ creditMemo.refund_method }}</div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="text-sm text-gray-600">Total Amount</div>
          <div class="mt-2 text-2xl font-bold text-gray-900">{{ formatPrice(creditMemo.grand_total) }}</div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="text-sm text-gray-600">Created</div>
          <div class="mt-2 text-sm text-gray-900">{{ formatDate(creditMemo.created_at) }}</div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 px-6">
            <button
              v-for="tab in ['overview', 'items', 'refund-details']"
              :key="tab"
              @click="activeTab = tab"
              :class="[
                'py-4 px-1 border-b-2 font-medium text-sm',
                activeTab === tab
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              {{ tab.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ') }}
            </button>
          </nav>
        </div>

        <div class="p-6">
          <!-- Overview Tab -->
          <div v-if="activeTab === 'overview'" class="space-y-6">
            <!-- Credit Memo Information -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Credit Memo Information</h3>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-sm text-gray-600">Credit Memo Number</div>
                  <div class="mt-1 font-medium text-gray-900">{{ creditMemo.credit_memo_number }}</div>
                </div>
                <div>
                  <div class="text-sm text-gray-600">Status</div>
                  <div class="mt-1">
                    <span :class="['px-3 py-1 inline-flex text-sm font-semibold rounded-full', getStatusBadge(creditMemo.status)]">
                      {{ creditMemo.status }}
                    </span>
                  </div>
                </div>
                <div>
                  <div class="text-sm text-gray-600">Order Number</div>
                  <div class="mt-1">
                    <button
                      @click="viewOrder"
                      class="font-medium text-blue-600 hover:text-blue-800"
                    >
                      {{ creditMemo.order?.order_number }}
                    </button>
                  </div>
                </div>
                <div v-if="creditMemo.invoice">
                  <div class="text-sm text-gray-600">Invoice Number</div>
                  <div class="mt-1">
                    <button
                      @click="viewInvoice"
                      class="font-medium text-blue-600 hover:text-blue-800"
                    >
                      {{ creditMemo.invoice.invoice_number }}
                    </button>
                  </div>
                </div>
                <div>
                  <div class="text-sm text-gray-600">Created At</div>
                  <div class="mt-1 text-gray-900">{{ formatDate(creditMemo.created_at) }}</div>
                </div>
                <div v-if="creditMemo.refunded_at">
                  <div class="text-sm text-gray-600">Refunded At</div>
                  <div class="mt-1 text-gray-900">{{ formatDate(creditMemo.refunded_at) }}</div>
                </div>
              </div>
            </div>

            <!-- Customer Information -->
            <div v-if="creditMemo.order">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h3>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-sm text-gray-600">Name</div>
                  <div class="mt-1 font-medium text-gray-900">{{ creditMemo.order.user?.name || 'Guest' }}</div>
                </div>
                <div>
                  <div class="text-sm text-gray-600">Email</div>
                  <div class="mt-1 text-gray-900">{{ creditMemo.order.customer_email }}</div>
                </div>
                <div v-if="creditMemo.order.customer_phone">
                  <div class="text-sm text-gray-600">Phone</div>
                  <div class="mt-1 text-gray-900">{{ creditMemo.order.customer_phone }}</div>
                </div>
              </div>
            </div>

            <!-- Reason & Notes -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Reason & Notes</h3>
              <div class="space-y-4">
                <div v-if="creditMemo.notes">
                  <div class="text-sm text-gray-600">Notes</div>
                  <div class="mt-1 text-gray-900">{{ creditMemo.notes }}</div>
                </div>
                <div v-if="creditMemo.admin_notes">
                  <div class="text-sm text-gray-600">Admin Notes</div>
                  <div class="mt-1 text-gray-900">{{ creditMemo.admin_notes }}</div>
                </div>
                <div v-if="!creditMemo.notes && !creditMemo.admin_notes">
                  <p class="text-sm text-gray-500 italic">No reason or notes provided</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Items Tab -->
          <div v-if="activeTab === 'items'">
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
                      Qty
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Price
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Tax
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Discount
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Row Total
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="item in creditMemo.items" :key="item.id">
                    <td class="px-6 py-4">
                      <div class="text-sm font-medium text-gray-900">{{ item.product_name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-500">{{ item.sku || '-' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                      <div class="text-sm text-gray-900">{{ item.qty }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                      <div class="text-sm text-gray-900">{{ formatPrice(item.price) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                      <div class="text-sm text-gray-900">{{ formatPrice(item.tax_amount) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                      <div class="text-sm text-gray-900">{{ formatPrice(item.discount_amount) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                      <div class="text-sm font-medium text-gray-900">{{ formatPrice(item.row_total) }}</div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Totals -->
            <div class="mt-6 flex justify-end">
              <div class="w-full max-w-xs space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Subtotal:</span>
                  <span class="font-medium text-gray-900">{{ formatPrice(creditMemo.subtotal) }}</span>
                </div>
                <div v-if="creditMemo.discount_amount > 0" class="flex justify-between text-sm">
                  <span class="text-gray-600">Discount:</span>
                  <span class="font-medium text-gray-900">-{{ formatPrice(creditMemo.discount_amount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Tax:</span>
                  <span class="font-medium text-gray-900">{{ formatPrice(creditMemo.tax_amount) }}</span>
                </div>
                                <div v-if="creditMemo.shipping_amount > 0" class="flex justify-between text-sm">
                  <span class="text-gray-600">Shipping Refund</span>
                  <span class="font-medium text-gray-900">{{ formatPrice(creditMemo.shipping_amount) }}</span>
                </div>
                <div v-if="creditMemo.adjustment_positive > 0" class="flex justify-between text-sm">
                  <span class="text-gray-600">Adjustment (+)</span>
                  <span class="font-medium text-green-600">
                    +{{ formatPrice(creditMemo.adjustment_positive) }}
                  </span>
                </div>
                <div v-if="creditMemo.adjustment_negative > 0" class="flex justify-between text-sm">
                  <span class="text-gray-600">Adjustment (-)</span>
                  <span class="font-medium text-red-600">
                    -{{ formatPrice(creditMemo.adjustment_negative) }}
                  </span>
                </div>
                <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-3 mt-3">
                  <span class="text-gray-900">Total Refund</span>
                  <span class="text-gray-900">{{ formatPrice(creditMemo.grand_total) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Refund Details Tab -->
          <div v-if="activeTab === 'refund-details'" class="space-y-6">
            <!-- Refund Information -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Refund Information</h3>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-sm text-gray-600">Refund Method</div>
                  <div class="mt-1 font-medium text-gray-900 capitalize">{{ creditMemo.refund_method }}</div>
                </div>
                <div>
                  <div class="text-sm text-gray-600">Status</div>
                  <div class="mt-1">
                    <span :class="['px-3 py-1 inline-flex text-sm font-semibold rounded-full', getStatusBadge(creditMemo.status)]">
                      {{ creditMemo.status }}
                    </span>
                  </div>
                </div>
                <div v-if="creditMemo.notes">
                  <div class="text-sm text-gray-600">Transaction Notes</div>
                  <div class="mt-1 text-sm text-gray-900">{{ creditMemo.notes }}</div>
                </div>
                <div v-if="creditMemo.refunded_at">
                  <div class="text-sm text-gray-600">Refunded At</div>
                  <div class="mt-1 text-gray-900">{{ formatDate(creditMemo.refunded_at) }}</div>
                </div>
                <div>
                  <div class="text-sm text-gray-600">Refund Amount</div>
                  <div class="mt-1 text-2xl font-bold text-gray-900">{{ formatPrice(creditMemo.grand_total) }}</div>
                </div>
              </div>
            </div>

            <!-- Inventory Information -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Inventory Information</h3>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-sm text-gray-600">Restore Inventory</div>
                  <div class="mt-1">
                    <span :class="[
                      'px-3 py-1 inline-flex text-sm font-semibold rounded-full',
                      creditMemo.restore_inventory ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                    ]">
                      {{ creditMemo.restore_inventory ? 'Yes' : 'No' }}
                    </span>
                  </div>
                </div>
                <div v-if="creditMemo.restore_inventory">
                  <div class="text-sm text-gray-600">Inventory Restored</div>
                  <div class="mt-1">
                    <span :class="[
                      'px-3 py-1 inline-flex text-sm font-semibold rounded-full',
                      creditMemo.inventory_restored_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                    ]">
                      {{ creditMemo.inventory_restored_at ? 'Yes' : 'Pending' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Payment Information -->
            <div v-if="creditMemo.order">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Original Order Payment</h3>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-sm text-gray-600">Payment Method</div>
                  <div class="mt-1 font-medium text-gray-900">{{ creditMemo.order.payment_method || '-' }}</div>
                </div>
                <div>
                  <div class="text-sm text-gray-600">Payment Status</div>
                  <div class="mt-1 font-medium text-gray-900 capitalize">{{ creditMemo.order.payment_status }}</div>
                </div>
                <div>
                  <div class="text-sm text-gray-600">Order Total</div>
                  <div class="mt-1 font-medium text-gray-900">{{ formatPrice(creditMemo.order.total) }}</div>
                </div>
                <div v-if="creditMemo.order.total_refunded">
                  <div class="text-sm text-gray-600">Total Refunded</div>
                  <div class="mt-1 font-medium text-gray-900">{{ formatPrice(creditMemo.order.total_refunded) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

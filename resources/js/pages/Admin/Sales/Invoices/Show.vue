<script setup lang="ts">
import { router, Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { ref } from 'vue';

interface Invoice {
  id: number;
  invoice_number: string;
  status: string;
  issue_date: string;
  due_date: string | null;
  subtotal: number;
  tax: number;
  total: number;
  notes: string | null;
  order: {
    id: number;
    order_number: string;
    created_at: string;
  };
}

interface InvoiceData {
  invoice: {
    number: string;
    issue_date: string;
    due_date: string | null;
    status: string;
    notes: string | null;
  };
  order: {
    number: string;
    date: string;
  };
  customer: {
    name: string;
    email: string;
    phone: string | null;
  };
  billing_address: {
    full_name: string;
    address_line1: string;
    address_line2: string | null;
    city: string;
    state: string;
    postal_code: string;
    country: string;
    phone: string;
  } | null;
  shipping_address: {
    full_name: string;
    address_line1: string;
    address_line2: string | null;
    city: string;
    state: string;
    postal_code: string;
    country: string;
    phone: string;
  } | null;
  items: Array<{
    product_name: string;
    sku: string;
    quantity: number;
    price: number;
    total: number;
  }>;
  totals: {
    subtotal: number;
    tax: number;
    shipping: number;
    discount: number;
    total: number;
  };
}

interface Props {
  invoice: Invoice;
  invoiceData: InvoiceData;
  statuses: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const { formatPrice } = useCurrency();

// Modal states
const showMarkAsSentModal = ref(false);
const showMarkAsPaidModal = ref(false);
const showCancelModal = ref(false);
const showSendEmailModal = ref(false);

function getStatusBadge(status: string): string {
  const badges: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
    sent: 'bg-blue-100 text-blue-800 border border-blue-200',
    paid: 'bg-green-100 text-green-800 border border-green-200',
    cancelled: 'bg-red-100 text-red-800 border border-red-200',
  };
  return badges[status] || badges.pending;
}

function confirmMarkAsSent() {
  router.post(`/admin/sales/invoices/${props.invoice.id}/mark-as-sent`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      showMarkAsSentModal.value = false;
    },
  });
}

function confirmMarkAsPaid() {
  router.post(`/admin/sales/invoices/${props.invoice.id}/mark-as-paid`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      showMarkAsPaidModal.value = false;
    },
  });
}

function confirmCancelInvoice() {
  router.post(`/admin/sales/invoices/${props.invoice.id}/cancel`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      showCancelModal.value = false;
    },
  });
}

function confirmSendEmail() {
  router.post(`/admin/sales/invoices/${props.invoice.id}/send-email`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      showSendEmailModal.value = false;
    },
  });
}

function downloadPdf() {
  window.open(`/admin/sales/invoices/${props.invoice.id}/download-pdf`, '_blank');
}

function goToOrder() {
  window.location.href = `/admin/sales/orders/${props.invoice.order.id}`;
}
</script>

<template>
  <Head :title="`Invoice ${invoice.invoice_number}`" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-start">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Invoice {{ invoice.invoice_number }}</h1>
          <p class="text-sm text-gray-500 mt-1">Issue Date: {{ invoiceData.invoice.issue_date }}</p>
        </div>
        <div class="flex gap-2">
          <button
            v-if="invoice.status === 'pending'"
            @click="showMarkAsSentModal = true"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Mark as Sent
          </button>
          <button
            v-if="invoice.status !== 'paid' && invoice.status !== 'cancelled'"
            @click="showMarkAsPaidModal = true"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
          >
            Mark as Paid
          </button>
          <button
            @click="downloadPdf"
            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
          >
            Download PDF
          </button>
          <button
            @click="showSendEmailModal = true"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
          >
            Send Email
          </button>
          <button
            v-if="invoice.status === 'pending'"
            @click="showCancelModal = true"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
          >
            Cancel
          </button>
        </div>
      </div>

      <!-- Invoice Status & Info Cards -->
      <div class="grid grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-lg shadow-sm">
          <div class="text-sm text-gray-600">Status</div>
          <div class="mt-2">
            <span :class="['px-3 py-1 inline-flex text-sm font-semibold rounded-full', getStatusBadge(invoice.status)]">
              {{ invoice.status }}
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
              {{ invoiceData.order.number }}
            </button>
          </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm">
          <div class="text-sm text-gray-600">Total Amount</div>
          <div class="mt-2 text-2xl font-bold text-gray-900">{{ formatPrice(invoice.total) }}</div>
        </div>
      </div>

      <!-- Invoice Details -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 space-y-8">
          <!-- Company & Customer Info -->
          <div class="grid grid-cols-2 gap-8">
            <!-- Bill To -->
            <div>
              <h3 class="text-sm font-semibold text-gray-900 mb-3">Bill To</h3>
              <div class="text-sm text-gray-600 space-y-1">
                <div class="font-medium text-gray-900">{{ invoiceData.customer.name }}</div>
                <div>{{ invoiceData.customer.email }}</div>
                <div v-if="invoiceData.customer.phone">{{ invoiceData.customer.phone }}</div>
                <template v-if="invoiceData.billing_address">
                  <div class="mt-2">
                    <div>{{ invoiceData.billing_address.address_line1 }}</div>
                    <div v-if="invoiceData.billing_address.address_line2">{{ invoiceData.billing_address.address_line2 }}</div>
                    <div>{{ invoiceData.billing_address.city }}, {{ invoiceData.billing_address.state }} {{ invoiceData.billing_address.postal_code }}</div>
                    <div>{{ invoiceData.billing_address.country }}</div>
                  </div>
                </template>
              </div>
            </div>

            <!-- Invoice Info -->
            <div>
              <h3 class="text-sm font-semibold text-gray-900 mb-3">Invoice Details</h3>
              <div class="text-sm space-y-2">
                <div class="flex justify-between">
                  <span class="text-gray-600">Invoice Number:</span>
                  <span class="font-medium text-gray-900">{{ invoiceData.invoice.number }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Issue Date:</span>
                  <span class="font-medium text-gray-900">{{ invoiceData.invoice.issue_date }}</span>
                </div>
                <div v-if="invoiceData.invoice.due_date" class="flex justify-between">
                  <span class="text-gray-600">Due Date:</span>
                  <span class="font-medium text-gray-900">{{ invoiceData.invoice.due_date }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Order Number:</span>
                  <button @click="goToOrder" class="font-medium text-blue-600 hover:text-blue-800">
                    {{ invoiceData.order.number }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Items Table -->
          <div>
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Items</h3>
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="(item, index) in invoiceData.items" :key="index">
                  <td class="px-4 py-3 text-sm text-gray-900">{{ item.product_name }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ item.sku }}</td>
                  <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ formatPrice(item.price) }}</td>
                  <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ item.quantity }}</td>
                  <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ formatPrice(item.total) }}</td>
                </tr>
              </tbody>
              <tfoot class="bg-gray-50">
                <tr>
                  <td colspan="4" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Subtotal:</td>
                  <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ formatPrice(invoiceData.totals.subtotal) }}</td>
                </tr>
                <tr>
                  <td colspan="4" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Tax:</td>
                  <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ formatPrice(invoiceData.totals.tax) }}</td>
                </tr>
                <tr>
                  <td colspan="4" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Shipping:</td>
                  <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ formatPrice(invoiceData.totals.shipping) }}</td>
                </tr>
                <tr v-if="invoiceData.totals.discount > 0">
                  <td colspan="4" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Discount:</td>
                  <td class="px-4 py-3 text-sm font-medium text-red-600 text-right">-{{ formatPrice(invoiceData.totals.discount) }}</td>
                </tr>
                <tr class="border-t-2 border-gray-300">
                  <td colspan="4" class="px-4 py-3 text-base font-bold text-gray-900 text-right">Total:</td>
                  <td class="px-4 py-3 text-base font-bold text-gray-900 text-right">{{ formatPrice(invoiceData.totals.total) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Notes -->
          <div v-if="invoiceData.invoice.notes">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Notes</h3>
            <p class="text-sm text-gray-600">{{ invoiceData.invoice.notes }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modals -->
    <!-- Mark as Sent Modal -->
    <div v-if="showMarkAsSentModal" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Mark as Sent</h3>
        <p class="text-sm text-gray-500 mb-6">
          Are you sure you want to mark this invoice as sent?
        </p>
        <div class="flex gap-3 justify-end">
          <button
            @click="showMarkAsSentModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="confirmMarkAsSent"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
          >
            Mark as Sent
          </button>
        </div>
      </div>
    </div>

    <!-- Mark as Paid Modal -->
    <div v-if="showMarkAsPaidModal" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Mark as Paid</h3>
        <p class="text-sm text-gray-500 mb-6">
          Are you sure you want to mark this invoice as paid?
        </p>
        <div class="flex gap-3 justify-end">
          <button
            @click="showMarkAsPaidModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="confirmMarkAsPaid"
            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700"
          >
            Mark as Paid
          </button>
        </div>
      </div>
    </div>

    <!-- Cancel Invoice Modal -->
    <div v-if="showCancelModal" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Cancel Invoice</h3>
        <p class="text-sm text-gray-500 mb-6">
          Are you sure you want to cancel this invoice? This action cannot be undone.
        </p>
        <div class="flex gap-3 justify-end">
          <button
            @click="showCancelModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="confirmCancelInvoice"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
          >
            Cancel Invoice
          </button>
        </div>
      </div>
    </div>

    <!-- Send Email Modal -->
    <div v-if="showSendEmailModal" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Send Invoice Email</h3>
        <p class="text-sm text-gray-500 mb-6">
          Send invoice email to {{ invoiceData.customer.email }}?
        </p>
        <div class="flex gap-3 justify-end">
          <button
            @click="showSendEmailModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="confirmSendEmail"
            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
          >
            Send Email
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import ConfirmDeleteModal from '@/components/Admin/ConfirmDeleteModal.vue';
import { ref } from 'vue';

interface Address {
  id: number;
  type: string;
  first_name: string;
  last_name: string;
  full_name: string;
  company?: string;
  address_line_1: string;
  address_line_2?: string;
  city: string;
  state: string;
  postal_code: string;
  country: string;
  phone?: string;
  is_default_shipping: boolean;
  is_default_billing: boolean;
  formatted_address: string;
}

interface Props {
  customer: any;
  addresses: {
    data: Address[];
    links: any;
    meta: any;
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
  };
}

const props = defineProps<Props>();

const showDeleteModal = ref(false);
const deleteAddressId = ref<number | null>(null);

function confirmDelete(id: number) {
  deleteAddressId.value = id;
  showDeleteModal.value = true;
}

function deleteAddress() {
  if (deleteAddressId.value) {
    router.delete(`/admin/customers/${props.customer.id}/addresses/${deleteAddressId.value}`, {
      onSuccess: () => {
        showDeleteModal.value = false;
        deleteAddressId.value = null;
      }
    });
  }
}

function setDefaultShipping(id: number) {
  router.post(`/admin/customers/${props.customer.id}/addresses/${id}/set-default-shipping`, {}, { preserveScroll: true });
}

function setDefaultBilling(id: number) {
  router.post(`/admin/customers/${props.customer.id}/addresses/${id}/set-default-billing`, {}, { preserveScroll: true });
}
</script>

<template>
  <AdminLayout>
    <Head title="Addresses" />

    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Customer Addresses</h1>
            <p class="mt-1 text-sm text-gray-600">
              Manage addresses for {{ props.customer.first_name }} {{ props.customer.last_name }}
            </p>
          </div>
          <div class="flex items-center gap-3">
            <Link
              :href="`/admin/customers/${props.customer.id}`"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Back to Customer
            </Link>
            <Link
              :href="`/admin/customers/${props.customer.id}/addresses/create`"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              New Address
            </Link>
          </div>
        </div>
      </div>

      <!-- Addresses Table -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default For</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="addr in props.addresses.data" :key="addr.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="addr.type === 'shipping' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'">
                  {{ addr.type }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ addr.full_name }}</div>
                <div v-if="addr.company" class="text-sm text-gray-500">{{ addr.company }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ addr.formatted_address }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ addr.phone || '-' }}
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-col gap-2">
                  <div v-if="addr.is_default_shipping" class="inline-flex items-center text-xs text-green-600">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Shipping
                  </div>
                  <button v-else @click="setDefaultShipping(addr.id)" class="text-xs text-gray-600 hover:text-blue-600 text-left">
                    Set as default shipping
                  </button>
                  
                  <div v-if="addr.is_default_billing" class="inline-flex items-center text-xs text-green-600">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Billing
                  </div>
                  <button v-else @click="setDefaultBilling(addr.id)" class="text-xs text-gray-600 hover:text-blue-600 text-left">
                    Set as default billing
                  </button>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end gap-2">
                  <Link
                    :href="`/admin/customers/${props.customer.id}/addresses/${addr.id}/edit`"
                    class="text-indigo-600 hover:text-indigo-900 cursor-pointer"
                    title="Edit Address"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </Link>
                  <button
                    @click="confirmDelete(addr.id)"
                    class="text-red-600 hover:text-red-900 cursor-pointer"
                    title="Delete Address"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="props.addresses.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200">
          <Pagination :data="props.addresses" />
        </div>

        <!-- Empty State -->
        <div v-if="props.addresses.data.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No addresses</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by adding a new address.</p>
          <div class="mt-6">
            <Link
              :href="`/admin/customers/${props.customer.id}/addresses/create`"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              New Address
            </Link>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <ConfirmDeleteModal
        v-model:show="showDeleteModal"
        title="Address"
        message="Are you sure you want to delete this address? This action cannot be undone."
        @confirm="deleteAddress"
      />

    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface CustomerGroup {
    id: number;
    name: string;
    color: string;
}

interface CustomerAddress {
    id: number;
    address_type: string;
    first_name: string;
    last_name: string;
    company: string | null;
    street_address_1: string;
    street_address_2: string | null;
    city: string;
    state: string;
    postal_code: string;
    country: string;
    phone: string | null;
    is_default: boolean;
    formatted_address: string;
}

interface CustomerNote {
    id: number;
    note: string;
    created_at: string;
    user: {
        name: string;
    };
}

interface Customer {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    email: string;
    phone: string | null;
    date_of_birth: string | null;
    gender: string | null;
    customer_group_id: number;
    company_name: string | null;
    tax_id: string | null;
    is_active: boolean;
    is_verified: boolean;
    is_guest: boolean;
    newsletter_subscribed: boolean;
    total_orders: number;
    total_spent: number;
    notes: string | null;
    created_at: string;
    updated_at: string;
    customer_group: CustomerGroup;
    addresses: CustomerAddress[];
    customer_notes: CustomerNote[];
}

interface Props {
    customer: Customer;
}

const props = defineProps<Props>();

const activeTab = ref('info');
const showDeleteModal = ref(false);

const deleteCustomer = () => {
    router.delete(`/admin/customers/${props.customer.id}`, {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="`Customer: ${customer.full_name}`" />

    <AdminLayout :title="customer.full_name">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-semibold text-gray-900">{{ customer.full_name }}</h1>
                            <span
                                :class="[
                                    'px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    customer.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                ]"
                            >
                                {{ customer.is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span
                                v-if="customer.is_guest"
                                class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                            >
                                Guest
                            </span>
                            <span
                                v-if="customer.is_verified"
                                class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                            >
                                Verified
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">Customer ID: #{{ customer.id }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="router.visit('/admin/customers')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Back to List
                        </button>
                        <a
                            :href="`/admin/customers/${customer.id}/edit`"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                        >
                            Edit Customer
                        </a>
                        <button
                            @click="showDeleteModal = true"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="text-sm font-medium text-gray-600">Total Orders</div>
                        <div class="mt-2 text-3xl font-semibold text-gray-900">
                            {{ customer.total_orders }}
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="text-sm font-medium text-gray-600">Total Spent</div>
                        <div class="mt-2 text-3xl font-semibold text-gray-900">
                            {{ formatCurrency(customer.total_spent) }}
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="text-sm font-medium text-gray-600">Customer Group</div>
                        <div class="mt-2">
                            <span
                                v-if="customer.customer_group && customer.customer_group.id"
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                :style="{ backgroundColor: customer.customer_group.color + '20', color: customer.customer_group.color }"
                            >
                                {{ customer.customer_group.name }}
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600"
                            >
                                No Group
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="border-b border-gray-200">
                    <nav class="flex gap-6 px-6" aria-label="Tabs">
                        <button
                            @click="activeTab = 'info'"
                            :class="[
                                'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                                activeTab === 'info'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]"
                        >
                            Customer Information
                        </button>
                        <button
                            @click="activeTab = 'addresses'"
                            :class="[
                                'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                                activeTab === 'addresses'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]"
                        >
                            Addresses ({{ customer.addresses?.length || 0 }})
                        </button>
                        <button
                            @click="activeTab = 'notes'"
                            :class="[
                                'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                                activeTab === 'notes'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]"
                        >
                            Notes ({{ customer.customer_notes?.length || 0 }})
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <!-- Customer Information Tab -->
                    <div v-if="activeTab === 'info'">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Contact Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ customer.email }}</dd>
                                </div>
                                <div v-if="customer.phone">
                                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ customer.phone }}</dd>
                                </div>
                                <div v-if="customer.company_name">
                                    <dt class="text-sm font-medium text-gray-500">Company</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ customer.company_name }}</dd>
                                </div>
                                <div v-if="customer.tax_id">
                                    <dt class="text-sm font-medium text-gray-500">Tax ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ customer.tax_id }}</dd>
                                </div>
                            </div>

                            <!-- Personal Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                                <div v-if="customer.date_of_birth">
                                    <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatDate(customer.date_of_birth) }}</dd>
                                </div>
                                <div v-if="customer.gender">
                                    <dt class="text-sm font-medium text-gray-500">Gender</dt>
                                    <dd class="mt-1 text-sm text-gray-900 capitalize">{{ customer.gender }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Newsletter</dt>
                                    <dd class="mt-1 text-sm">
                                        <span
                                            :class="[
                                                'px-2 py-1 rounded text-xs font-medium',
                                                customer.newsletter_subscribed ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                            ]"
                                        >
                                            {{ customer.newsletter_subscribed ? 'Subscribed' : 'Not Subscribed' }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatDate(customer.created_at) }}</dd>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div v-if="customer.notes" class="mt-6 pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ customer.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Addresses Tab -->
                    <div v-if="activeTab === 'addresses'">
                        <div v-if="customer.addresses && customer.addresses.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                v-for="address in customer.addresses"
                                :key="address.id"
                                class="border border-gray-200 rounded-lg p-4"
                            >
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-medium text-gray-900 capitalize">{{ address.address_type }}</span>
                                    <span
                                        v-if="address.is_default"
                                        class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded"
                                    >
                                        Default
                                    </span>
                                </div>
                                <div class="text-sm text-gray-700 space-y-1">
                                    <p class="font-medium">{{ address.first_name }} {{ address.last_name }}</p>
                                    <p v-if="address.company">{{ address.company }}</p>
                                    <p>{{ address.street_address_1 }}</p>
                                    <p v-if="address.street_address_2">{{ address.street_address_2 }}</p>
                                    <p>{{ address.city }}, {{ address.state }} {{ address.postal_code }}</p>
                                    <p>{{ address.country }}</p>
                                    <p v-if="address.phone" class="pt-2">{{ address.phone }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No addresses</h3>
                            <p class="mt-1 text-sm text-gray-500">This customer hasn't added any addresses yet.</p>
                        </div>
                    </div>

                    <!-- Notes Tab -->
                    <div v-if="activeTab === 'notes'">
                        <div v-if="customer.customer_notes && customer.customer_notes.length > 0" class="space-y-4">
                            <div
                                v-for="note in customer.customer_notes"
                                :key="note.id"
                                class="border border-gray-200 rounded-lg p-4"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <div class="text-sm font-medium text-gray-900">{{ note.user.name }}</div>
                                    <div class="text-xs text-gray-500">{{ formatDateTime(note.created_at) }}</div>
                                </div>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ note.note }}</p>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No notes</h3>
                            <p class="mt-1 text-sm text-gray-500">No notes have been added for this customer.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true"
                    @click="showDeleteModal = false"
                ></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full z-10">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Delete Customer
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to delete <strong>{{ customer.full_name }}</strong>? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="deleteCustomer"
                        >
                            Delete
                        </button>
                        <button
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="showDeleteModal = false"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import * as customerRoutes from '@/routes/admin/customers';

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
    router.delete(customerRoutes.destroy({ customer: props.customer.id }).url, {
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

    <AdminLayout>
        <div class="container-fixed">
            <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
                <div class="flex flex-col justify-center gap-2">
                    <h1 class="text-xl font-semibold leading-none text-gray-900">
                        {{ customer.full_name }}
                    </h1>
                    <div class="flex items-center gap-2 text-sm font-medium text-gray-600">
                        <span>Customer Details</span>
                        <span class="badge badge-sm" :class="{
                            'badge-success': customer.is_active,
                            'badge-danger': !customer.is_active
                        }">
                            {{ customer.is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <span v-if="customer.is_verified" class="badge badge-sm badge-primary">
                            Verified
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2.5">
                    <a
                        :href="customerRoutes.edit({ customer: customer.id }).url"
                        class="btn btn-primary"
                    >
                        Edit Customer
                    </a>
                    <button
                        @click="showDeleteModal = true"
                        class="btn btn-danger"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <div class="container-fixed">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                <div class="card">
                    <div class="card-body">
                        <div class="text-sm font-medium text-gray-600">Total Orders</div>
                        <div class="mt-2 text-3xl font-semibold text-gray-900">
                            {{ customer.total_orders }}
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="text-sm font-medium text-gray-600">Total Spent</div>
                        <div class="mt-2 text-3xl font-semibold text-gray-900">
                            {{ formatCurrency(customer.total_spent) }}
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="text-sm font-medium text-gray-600">Customer Group</div>
                        <div class="mt-2">
                            <span
                                class="badge badge-lg"
                                :style="{ backgroundColor: customer.customer_group.color }"
                            >
                                {{ customer.customer_group.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="card">
                <div class="card-header">
                    <div class="flex gap-5 border-b border-gray-200">
                        <button
                            @click="activeTab = 'info'"
                            class="pb-3 px-1 font-medium text-sm transition-colors"
                            :class="{
                                'text-primary border-b-2 border-primary': activeTab === 'info',
                                'text-gray-600 hover:text-gray-900': activeTab !== 'info'
                            }"
                        >
                            Customer Information
                        </button>
                        <button
                            @click="activeTab = 'addresses'"
                            class="pb-3 px-1 font-medium text-sm transition-colors"
                            :class="{
                                'text-primary border-b-2 border-primary': activeTab === 'addresses',
                                'text-gray-600 hover:text-gray-900': activeTab !== 'addresses'
                            }"
                        >
                            Addresses ({{ customer.addresses.length }})
                        </button>
                        <button
                            @click="activeTab = 'notes'"
                            class="pb-3 px-1 font-medium text-sm transition-colors"
                            :class="{
                                'text-primary border-b-2 border-primary': activeTab === 'notes',
                                'text-gray-600 hover:text-gray-900': activeTab !== 'notes'
                            }"
                        >
                            Notes ({{ customer.customer_notes.length }})
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Customer Information Tab -->
                    <div v-if="activeTab === 'info'" class="grid gap-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Contact Information</h3>
                                <dl class="grid gap-3">
                                    <div>
                                        <dt class="text-xs font-medium text-gray-600">Email</dt>
                                        <dd class="text-sm text-gray-900">{{ customer.email }}</dd>
                                    </div>
                                    <div v-if="customer.phone">
                                        <dt class="text-xs font-medium text-gray-600">Phone</dt>
                                        <dd class="text-sm text-gray-900">{{ customer.phone }}</dd>
                                    </div>
                                    <div v-if="customer.company_name">
                                        <dt class="text-xs font-medium text-gray-600">Company</dt>
                                        <dd class="text-sm text-gray-900">{{ customer.company_name }}</dd>
                                    </div>
                                    <div v-if="customer.tax_id">
                                        <dt class="text-xs font-medium text-gray-600">Tax ID</dt>
                                        <dd class="text-sm text-gray-900">{{ customer.tax_id }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Personal Information</h3>
                                <dl class="grid gap-3">
                                    <div v-if="customer.date_of_birth">
                                        <dt class="text-xs font-medium text-gray-600">Date of Birth</dt>
                                        <dd class="text-sm text-gray-900">{{ formatDate(customer.date_of_birth) }}</dd>
                                    </div>
                                    <div v-if="customer.gender">
                                        <dt class="text-xs font-medium text-gray-600">Gender</dt>
                                        <dd class="text-sm text-gray-900 capitalize">{{ customer.gender }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-600">Newsletter</dt>
                                        <dd class="text-sm">
                                            <span :class="{
                                                'text-success': customer.newsletter_subscribed,
                                                'text-gray-600': !customer.newsletter_subscribed
                                            }">
                                                {{ customer.newsletter_subscribed ? 'Subscribed' : 'Not Subscribed' }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-600">Member Since</dt>
                                        <dd class="text-sm text-gray-900">{{ formatDate(customer.created_at) }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div v-if="customer.notes">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3">Notes</h3>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ customer.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Addresses Tab -->
                    <div v-if="activeTab === 'addresses'">
                        <div v-if="customer.addresses.length === 0" class="text-center py-8">
                            <p class="text-gray-600">No addresses added yet.</p>
                        </div>
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div
                                v-for="address in customer.addresses"
                                :key="address.id"
                                class="p-4 border border-gray-200 rounded-lg relative"
                            >
                                <div v-if="address.is_default" class="absolute top-3 right-3">
                                    <span class="badge badge-sm badge-primary">Default</span>
                                </div>
                                <div class="mb-2">
                                    <span class="text-xs font-semibold text-gray-600 uppercase">
                                        {{ address.address_type }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-900">
                                    <p class="font-medium">{{ address.first_name }} {{ address.last_name }}</p>
                                    <p v-if="address.company">{{ address.company }}</p>
                                    <p>{{ address.street_address_1 }}</p>
                                    <p v-if="address.street_address_2">{{ address.street_address_2 }}</p>
                                    <p>{{ address.city }}, {{ address.state }} {{ address.postal_code }}</p>
                                    <p>{{ address.country }}</p>
                                    <p v-if="address.phone" class="mt-2">{{ address.phone }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Tab -->
                    <div v-if="activeTab === 'notes'">
                        <div v-if="customer.customer_notes.length === 0" class="text-center py-8">
                            <p class="text-gray-600">No notes added yet.</p>
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="note in customer.customer_notes"
                                :key="note.id"
                                class="p-4 border border-gray-200 rounded-lg"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ note.user.name }}
                                    </div>
                                    <div class="text-xs text-gray-600">
                                        {{ formatDateTime(note.created_at) }}
                                    </div>
                                </div>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ note.note }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
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

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-danger-light sm:mx-0 sm:h-10 sm:w-10"
                            >
                                <svg
                                    class="h-6 w-6 text-danger"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                    />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Delete Customer
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600">
                                        Are you sure you want to delete this customer? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            class="btn btn-danger w-full sm:w-auto sm:ml-3"
                            @click="deleteCustomer"
                        >
                            Delete
                        </button>
                        <button
                            type="button"
                            class="btn btn-light w-full sm:w-auto mt-3 sm:mt-0"
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

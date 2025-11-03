<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import * as customerRoutes from '@/routes/admin/customers';

interface CustomerGroup {
    id: number;
    name: string;
    color: string;
}

interface Customer {
    id: number;
    first_name: string;
    last_name: string;
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
    notes: string | null;
}

interface Props {
    customer: Customer;
    customerGroups: CustomerGroup[];
}

const props = defineProps<Props>();

const form = useForm({
    first_name: props.customer?.first_name || '',
    last_name: props.customer?.last_name || '',
    email: props.customer?.email || '',
    phone: props.customer?.phone || '',
    date_of_birth: props.customer?.date_of_birth || '',
    gender: props.customer?.gender || '',
    customer_group_id: props.customer?.customer_group_id || 0,
    company_name: props.customer?.company_name || '',
    tax_id: props.customer?.tax_id || '',
    is_active: props.customer?.is_active ?? true,
    is_verified: props.customer?.is_verified ?? false,
    newsletter_subscribed: props.customer?.newsletter_subscribed ?? false,
    notes: props.customer?.notes || '',
});

const submit = () => {
    if (!props.customer?.id) {
        console.error('Cannot submit: customer ID is undefined');
        return;
    }
    
    form.put(customerRoutes.update({ customer: props.customer.id }).url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Customer" />

    <AdminLayout title="Edit Customer">
        <div v-if="!props.customer" class="p-6">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-yellow-800">Loading customer data...</p>
            </div>
        </div>

        <div v-else class="p-6">
            <!-- Back Button -->
            <div class="mb-4">
                <button
                    type="button"
                    @click="router.visit('/admin/customers')"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Customers
                </button>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Customer Information -->
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h2>
                        <div class="space-y-4">
                            <!-- Basic Information -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                                    <input
                                        v-model="form.first_name"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.first_name }"
                                        placeholder="Enter first name"
                                    />
                                    <div v-if="form.errors.first_name" class="text-red-600 text-sm mt-1">{{ form.errors.first_name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                                    <input
                                        v-model="form.last_name"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.last_name }"
                                        placeholder="Enter last name"
                                    />
                                    <div v-if="form.errors.last_name" class="text-red-600 text-sm mt-1">{{ form.errors.last_name }}</div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.email }"
                                        placeholder="customer@example.com"
                                    />
                                    <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <input
                                        v-model="form.phone"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.phone }"
                                        placeholder="+1 (555) 000-0000"
                                    />
                                    <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">{{ form.errors.phone }}</div>
                                </div>
                            </div>

                            <!-- Personal Information -->
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                                    <input
                                        v-model="form.date_of_birth"
                                        type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.date_of_birth }"
                                    />
                                    <div v-if="form.errors.date_of_birth" class="text-red-600 text-sm mt-1">{{ form.errors.date_of_birth }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                                    <select
                                        v-model="form.gender"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.gender }"
                                    >
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <div v-if="form.errors.gender" class="text-red-600 text-sm mt-1">{{ form.errors.gender }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer Group *</label>
                                    <select
                                        v-model="form.customer_group_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.customer_group_id }"
                                    >
                                        <option value="">Select Group</option>
                                        <option v-for="group in customerGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                                    </select>
                                    <div v-if="form.errors.customer_group_id" class="text-red-600 text-sm mt-1">{{ form.errors.customer_group_id }}</div>
                                </div>
                            </div>

                            <!-- Company Information -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                                    <input
                                        v-model="form.company_name"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.company_name }"
                                        placeholder="Enter company name"
                                    />
                                    <div v-if="form.errors.company_name" class="text-red-600 text-sm mt-1">{{ form.errors.company_name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax ID</label>
                                    <input
                                        v-model="form.tax_id"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.tax_id }"
                                        placeholder="Enter tax ID"
                                    />
                                    <div v-if="form.errors.tax_id" class="text-red-600 text-sm mt-1">{{ form.errors.tax_id }}</div>
                                </div>
                            </div>

                            <!-- Status Toggles -->
                            <div class="grid grid-cols-3 gap-4">
                                <div class="flex items-center">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        id="is_active"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    />
                                    <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">Active</label>
                                </div>
                                <div class="flex items-center">
                                    <input
                                        v-model="form.is_verified"
                                        type="checkbox"
                                        id="is_verified"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    />
                                    <label for="is_verified" class="ml-2 text-sm font-medium text-gray-700">Verified</label>
                                </div>
                                <div class="flex items-center">
                                    <input
                                        v-model="form.newsletter_subscribed"
                                        type="checkbox"
                                        id="newsletter"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    />
                                    <label for="newsletter" class="ml-2 text-sm font-medium text-gray-700">Newsletter</label>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea
                                    v-model="form.notes"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.notes }"
                                    rows="4"
                                    placeholder="Add any additional notes"
                                ></textarea>
                                <div v-if="form.errors.notes" class="text-red-600 text-sm mt-1">{{ form.errors.notes }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                        <button
                            type="button"
                            @click="router.visit('/admin/customers')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

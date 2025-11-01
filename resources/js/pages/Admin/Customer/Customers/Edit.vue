<script setup lang="ts">
import { ref } from 'vue';
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
    first_name: props.customer.first_name,
    last_name: props.customer.last_name,
    email: props.customer.email,
    phone: props.customer.phone || '',
    date_of_birth: props.customer.date_of_birth || '',
    gender: props.customer.gender || '',
    customer_group_id: props.customer.customer_group_id,
    company_name: props.customer.company_name || '',
    tax_id: props.customer.tax_id || '',
    is_active: props.customer.is_active,
    is_verified: props.customer.is_verified,
    newsletter_subscribed: props.customer.newsletter_subscribed,
    notes: props.customer.notes || '',
});

const submit = () => {
    form.put(customerRoutes.update({ customer: props.customer.id }).url, {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};
</script>

<template>
    <Head title="Edit Customer" />

    <AdminLayout>
        <div class="container-fixed">
            <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
                <div class="flex flex-col justify-center gap-2">
                    <h1 class="text-xl font-semibold leading-none text-gray-900">
                        Edit Customer
                    </h1>
                    <div class="flex items-center gap-2 text-sm font-medium text-gray-600">
                        Update customer information
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fixed">
            <div class="grid gap-5 lg:gap-7.5">
                <form @submit.prevent="submit">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Customer Information</h3>
                        </div>
                        <div class="card-body grid gap-5">
                            <!-- Basic Information -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-1">
                                    <label class="form-label text-gray-900">
                                        First Name
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        v-model="form.first_name"
                                        type="text"
                                        class="input"
                                        :class="{ 'border-danger': form.errors.first_name }"
                                        placeholder="Enter first name"
                                    />
                                    <span v-if="form.errors.first_name" class="text-danger text-xs">
                                        {{ form.errors.first_name }}
                                    </span>
                                </div>

                                <div class="flex flex-col gap-1">
                                    <label class="form-label text-gray-900">
                                        Last Name
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        v-model="form.last_name"
                                        type="text"
                                        class="input"
                                        :class="{ 'border-danger': form.errors.last_name }"
                                        placeholder="Enter last name"
                                    />
                                    <span v-if="form.errors.last_name" class="text-danger text-xs">
                                        {{ form.errors.last_name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-1">
                                    <label class="form-label text-gray-900">
                                        Email
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        class="input"
                                        :class="{ 'border-danger': form.errors.email }"
                                        placeholder="customer@example.com"
                                    />
                                    <span v-if="form.errors.email" class="text-danger text-xs">
                                        {{ form.errors.email }}
                                    </span>
                                </div>

                                <div class="flex flex-col gap-1">
                                    <label class="form-label text-gray-900">Phone</label>
                                    <input
                                        v-model="form.phone"
                                        type="text"
                                        class="input"
                                        :class="{ 'border-danger': form.errors.phone }"
                                        placeholder="+1 (555) 000-0000"
                                    />
                                    <span v-if="form.errors.phone" class="text-danger text-xs">
                                        {{ form.errors.phone }}
                                    </span>
                                </div>
                            </div>

                            <!-- Personal Information -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                                <div class="flex flex-col gap-1">
                                    <label class="form-label text-gray-900">Date of Birth</label>
                                    <input
                                        v-model="form.date_of_birth"
                                        type="date"
                                        class="input"
                                        :class="{ 'border-danger': form.errors.date_of_birth }"
                                    />
                                    <span v-if="form.errors.date_of_birth" class="text-danger text-xs">
                                        {{ form.errors.date_of_birth }}
                                    </span>
                                </div>

                                <div class="flex flex-col gap-1">
                                    <label class="form-label text-gray-900">Gender</label>
                                    <select
                                        v-model="form.gender"
                                        class="select"
                                        :class="{ 'border-danger': form.errors.gender }"
                                    >
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <span v-if="form.errors.gender" class="text-danger text-xs">
                                        {{ form.errors.gender }}
                                    </span>
                                </div>

                                <div class="flex flex-col gap-1">
                                    <label class="form-label text-gray-900">
                                        Customer Group
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select
                                        v-model="form.customer_group_id"
                                        class="select"
                                        :class="{ 'border-danger': form.errors.customer_group_id }"
                                    >
                                        <option
                                            v-for="group in customerGroups"
                                            :key="group.id"
                                            :value="group.id"
                                        >
                                            {{ group.name }}
                                        </option>
                                    </select>
                                    <span v-if="form.errors.customer_group_id" class="text-danger text-xs">
                                        {{ form.errors.customer_group_id }}
                                    </span>
                                </div>
                            </div>

                            <!-- Company Information -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-1">
                                    <label class="form-label text-gray-900">Company Name</label>
                                    <input
                                        v-model="form.company_name"
                                        type="text"
                                        class="input"
                                        :class="{ 'border-danger': form.errors.company_name }"
                                        placeholder="Enter company name"
                                    />
                                    <span v-if="form.errors.company_name" class="text-danger text-xs">
                                        {{ form.errors.company_name }}
                                    </span>
                                </div>

                                <div class="flex flex-col gap-1">
                                    <label class="form-label text-gray-900">Tax ID</label>
                                    <input
                                        v-model="form.tax_id"
                                        type="text"
                                        class="input"
                                        :class="{ 'border-danger': form.errors.tax_id }"
                                        placeholder="Enter tax ID"
                                    />
                                    <span v-if="form.errors.tax_id" class="text-danger text-xs">
                                        {{ form.errors.tax_id }}
                                    </span>
                                </div>
                            </div>

                            <!-- Status Toggles -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                                <label class="switch">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                    />
                                    <span class="switch-label">Active</span>
                                </label>

                                <label class="switch">
                                    <input
                                        v-model="form.is_verified"
                                        type="checkbox"
                                    />
                                    <span class="switch-label">Verified</span>
                                </label>

                                <label class="switch">
                                    <input
                                        v-model="form.newsletter_subscribed"
                                        type="checkbox"
                                    />
                                    <span class="switch-label">Newsletter Subscription</span>
                                </label>
                            </div>

                            <!-- Notes -->
                            <div class="flex flex-col gap-1">
                                <label class="form-label text-gray-900">Notes</label>
                                <textarea
                                    v-model="form.notes"
                                    class="textarea"
                                    :class="{ 'border-danger': form.errors.notes }"
                                    rows="4"
                                    placeholder="Add any additional notes about this customer"
                                ></textarea>
                                <span v-if="form.errors.notes" class="text-danger text-xs">
                                    {{ form.errors.notes }}
                                </span>
                            </div>
                        </div>

                        <div class="card-footer justify-between">
                            <a
                                :href="customerRoutes.show({ customer: customer.id }).url"
                                class="btn btn-light"
                            >
                                Cancel
                            </a>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">Updating...</span>
                                <span v-else>Update Customer</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

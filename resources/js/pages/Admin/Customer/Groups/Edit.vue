<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';

interface Customer {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    email: string;
}

interface CustomerGroup {
    id: number;
    name: string;
    code: string;
    description: string;
    color: string;
    discount_percentage: number;
    order: number;
    is_default: boolean;
    auto_assignment_rules: {
        min_orders?: number;
        min_spent?: number;
        min_aov?: number;
    };
    status: boolean;
    customers_count: number;
    customers?: Customer[];
    created_at: string;
    updated_at: string;
}

interface Props {
    group: CustomerGroup;
    customersCount: number;
}

const props = defineProps<Props>();

const showDeleteModal = ref(false);

const form = useForm({
    name: props.group?.name || '',
    code: props.group?.code || '',
    description: props.group?.description || '',
    color: props.group?.color || '#3B82F6',
    discount_percentage: props.group?.discount_percentage || 0,
    is_default: props.group?.is_default || false,
    auto_assignment_rules: {
        min_orders: props.group?.auto_assignment_rules?.min_orders || null,
        min_spent: props.group?.auto_assignment_rules?.min_spent || null,
        min_aov: props.group?.auto_assignment_rules?.min_aov || null,
    },
    status: props.group?.status !== undefined ? props.group.status : true,
});

const submit = () => {
    form.put(`/admin/customers/groups/${props.group.id}`, {
        preserveScroll: true,
    });
};

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteGroup = () => {
    router.delete(`/admin/customers/groups/${props.group.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

const generateCode = () => {
    if (form.name) {
        form.code = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    }
};

const applyAutoAssignment = () => {
    if (confirm('This will assign customers matching the auto-assignment rules to this group. Continue?')) {
        router.post(`/admin/customers/groups/${props.group.id}/apply-auto-assignment`, {}, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Edit ${group.name}`" />

        <div class="p-6">
            <!-- Back Button -->
            <div class="mb-4">
                <button
                    type="button"
                    @click="router.visit('/admin/customers/groups')"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Customer Groups
                </button>
            </div>

            <!-- Page Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Customer Group</h1>
                        <p class="mt-1 text-sm text-gray-600">Update group settings and auto-assignment rules.</p>
                    </div>
                    <button
                        v-if="!group.is_default && customersCount === 0"
                        @click="confirmDelete"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                    >
                        Delete Group
                    </button>
                    <div v-else-if="group.is_default" class="text-sm text-gray-500">
                        Cannot delete default group
                    </div>
                    <div v-else class="text-sm text-gray-500">
                        Cannot delete (has {{ customersCount }} customers)
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="text-sm text-gray-600">Total Customers</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">{{ customersCount }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="text-sm text-gray-600">Discount Percentage</div>
                    <div class="text-2xl font-bold text-blue-600 mt-1">{{ group.discount_percentage }}%</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="text-sm text-gray-600">Display Order</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">#{{ group.order }}</div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h2>
                        <div class="space-y-4">
                            <!-- Name and Code -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Group Name *</label>
                                    <input
                                        v-model="form.name"
                                        @blur="generateCode"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.name }"
                                        placeholder="e.g., VIP Members, Wholesale"
                                    />
                                    <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Group Code
                                        <span class="text-gray-500 font-normal">(auto-generated)</span>
                                    </label>
                                    <input
                                        v-model="form.code"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.code }"
                                        placeholder="e.g., vip-members"
                                    />
                                    <div v-if="form.errors.code" class="text-red-600 text-sm mt-1">{{ form.errors.code }}</div>
                                    <p class="text-xs text-gray-500 mt-1">Lowercase letters, numbers, and dashes only</p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.description }"
                                    placeholder="Brief description of this customer group..."
                                ></textarea>
                                <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">{{ form.errors.description }}</div>
                            </div>

                            <!-- Color and Discount -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Group Color</label>
                                    <div class="flex items-center space-x-3">
                                        <input
                                            v-model="form.color"
                                            type="color"
                                            class="h-10 w-20 border border-gray-300 rounded cursor-pointer"
                                        />
                                        <input
                                            v-model="form.color"
                                            type="text"
                                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            :class="{ 'border-red-500': form.errors.color }"
                                            placeholder="#3B82F6"
                                        />
                                    </div>
                                    <div v-if="form.errors.color" class="text-red-600 text-sm mt-1">{{ form.errors.color }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount Percentage</label>
                                    <div class="relative">
                                        <input
                                            v-model.number="form.discount_percentage"
                                            type="number"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 pr-8"
                                            :class="{ 'border-red-500': form.errors.discount_percentage }"
                                            placeholder="0.00"
                                        />
                                        <span class="absolute right-3 top-2 text-gray-500">%</span>
                                    </div>
                                    <div v-if="form.errors.discount_percentage" class="text-red-600 text-sm mt-1">{{ form.errors.discount_percentage }}</div>
                                    <p class="text-xs text-gray-500 mt-1">Group-based discount (0-100%)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Auto-Assignment Rules -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-lg font-medium text-gray-900">Auto-Assignment Rules</h2>
                                <p class="text-sm text-gray-600">Automatically assign customers to this group based on their purchase behavior.</p>
                            </div>
                            <button
                                type="button"
                                @click="applyAutoAssignment"
                                v-if="form.auto_assignment_rules.min_orders || form.auto_assignment_rules.min_spent || form.auto_assignment_rules.min_aov"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm"
                            >
                                Apply Rules Now
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Orders</label>
                                    <input
                                        v-model.number="form.auto_assignment_rules.min_orders"
                                        type="number"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        placeholder="e.g., 10"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">Total orders placed</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Spent ($)</label>
                                    <input
                                        v-model.number="form.auto_assignment_rules.min_spent"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        placeholder="e.g., 1000.00"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">Total amount spent</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum AOV ($)</label>
                                    <input
                                        v-model.number="form.auto_assignment_rules.min_aov"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        placeholder="e.g., 100.00"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">Average order value</p>
                                </div>
                            </div>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <p class="text-sm text-blue-800">
                                    <strong>Note:</strong> Customers must meet ALL configured rules to be auto-assigned. Leave fields empty to ignore that criteria. Click "Apply Rules Now" to manually trigger the auto-assignment.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Settings</h2>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input
                                    v-model="form.is_default"
                                    type="checkbox"
                                    id="is_default"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                />
                                <label for="is_default" class="ml-2 text-sm font-medium text-gray-700">
                                    Set as Default Group
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 ml-6">New customers will be automatically assigned to this group</p>

                            <div class="flex items-center mt-4">
                                <input
                                    v-model="form.status"
                                    type="checkbox"
                                    id="status"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                />
                                <label for="status" class="ml-2 text-sm font-medium text-gray-700">
                                    Active
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 ml-6">Inactive groups won't be available for selection</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button
                            type="button"
                            @click="router.visit('/admin/customers/groups')"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Recent Customers in Group -->
            <div v-if="group.customers && group.customers.length > 0" class="mt-6 bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Customers ({{ customersCount }} total)</h2>
                <div class="space-y-2">
                    <div v-for="customer in group.customers" :key="customer.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <div class="font-medium text-gray-900">{{ customer.full_name }}</div>
                            <div class="text-sm text-gray-600">{{ customer.email }}</div>
                        </div>
                        <a
                            :href="`/admin/customers/${customer.id}`"
                            class="text-blue-600 hover:text-blue-700 text-sm font-medium"
                        >
                            View →
                        </a>
                    </div>
                </div>
                <div v-if="customersCount > 10" class="mt-4 text-center">
                    <a
                        :href="`/admin/customers?customer_group_id=${group.id}`"
                        class="text-blue-600 hover:text-blue-700 text-sm font-medium"
                    >
                        View all {{ customersCount }} customers →
                    </a>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <Teleport to="body">
            <div
                v-if="showDeleteModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title"
                role="dialog"
                aria-modal="true"
            >
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity -z-10" @click="showDeleteModal = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Delete Customer Group
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to delete "{{ group.name }}"? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button
                                type="button"
                                @click="deleteGroup"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Delete
                            </button>
                            <button
                                type="button"
                                @click="showDeleteModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>

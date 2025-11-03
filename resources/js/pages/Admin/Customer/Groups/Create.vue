<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';

const form = useForm({
    name: '',
    code: '',
    description: '',
    color: '#3B82F6',
    discount_percentage: 0,
    is_default: false,
    auto_assignment_rules: {
        min_orders: null as number | null,
        min_spent: null as number | null,
        min_aov: null as number | null,
    },
    status: true,
});

const submit = () => {
    form.post('/admin/customers/groups', {
        preserveScroll: true,
    });
};

const generateCode = () => {
    if (form.name) {
        form.code = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Create Customer Group" />

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
                <h1 class="text-2xl font-bold text-gray-900">Create Customer Group</h1>
                <p class="mt-1 text-sm text-gray-600">Create a new customer group for segmentation and group-based pricing.</p>
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
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Auto-Assignment Rules</h2>
                        <p class="text-sm text-gray-600 mb-4">Automatically assign customers to this group based on their purchase behavior.</p>
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
                                    <strong>Note:</strong> Customers must meet ALL configured rules to be auto-assigned. Leave fields empty to ignore that criteria.
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
                            <span v-if="form.processing">Creating...</span>
                            <span v-else>Create Group</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

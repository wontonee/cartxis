<template>
    <Head title="Business Settings" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
        <div class="max-w-3xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white">
                <h1 class="text-3xl font-bold text-center">Business Settings</h1>
                <p class="text-center text-blue-100 mt-2">Configure your store's basic information</p>
            </div>

            <!-- Progress Bar -->
            <div class="px-8 pt-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Step 2 of 4</span>
                    <span class="text-sm font-medium text-gray-700">50%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 50%"></div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <form @submit.prevent="submitSettings" class="space-y-6">
                    <!-- Store Name -->
                    <div>
                        <label for="store_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Store Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="store_name"
                            v-model="form.store_name"
                            type="text"
                            required
                            placeholder="e.g., My Awesome Store"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': errors.store_name }"
                        />
                        <p v-if="errors.store_name" class="mt-1 text-sm text-red-600">{{ errors.store_name }}</p>
                    </div>

                    <!-- Admin Email -->
                    <div>
                        <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Admin Email <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="admin_email"
                            v-model="form.admin_email"
                            type="email"
                            required
                            placeholder="admin@example.com"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': errors.admin_email }"
                        />
                        <p v-if="errors.admin_email" class="mt-1 text-sm text-red-600">{{ errors.admin_email }}</p>
                    </div>

                    <!-- Admin Password (Optional) -->
                    <div>
                        <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Admin Password
                        </label>
                        <input
                            id="admin_password"
                            v-model="form.admin_password"
                            type="password"
                            placeholder="Leave blank to keep default admin"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': errors.admin_password }"
                        />
                        <p v-if="errors.admin_password" class="mt-1 text-sm text-red-600">{{ errors.admin_password }}</p>
                    </div>

                    <!-- Admin Password Confirmation -->
                    <div>
                        <label for="admin_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm Admin Password
                        </label>
                        <input
                            id="admin_password_confirmation"
                            v-model="form.admin_password_confirmation"
                            type="password"
                            placeholder="Re-enter admin password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                            :class="{ 'border-red-500': errors.admin_password_confirmation }"
                        />
                        <p v-if="errors.admin_password_confirmation" class="mt-1 text-sm text-red-600">{{ errors.admin_password_confirmation }}</p>
                    </div>

                    <!-- Contact Phone -->
                    <div>
                        <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Phone
                        </label>
                        <input
                            id="contact_phone"
                            v-model="form.contact_phone"
                            type="tel"
                            placeholder="+1 (555) 123-4567"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                        />
                    </div>

                    <!-- Store Address -->
                    <div>
                        <label for="store_address" class="block text-sm font-medium text-gray-700 mb-2">
                            Store Address
                        </label>
                        <textarea
                            id="store_address"
                            v-model="form.store_address"
                            rows="3"
                            placeholder="123 Main Street, City, State, ZIP"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                        ></textarea>
                    </div>

                    <!-- Currency -->
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                            Currency <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="currency"
                            v-model="form.currency"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                        >
                            <option value="USD">USD - US Dollar ($)</option>
                            <option value="EUR">EUR - Euro (€)</option>
                            <option value="GBP">GBP - British Pound (£)</option>
                            <option value="INR">INR - Indian Rupee (₹)</option>
                            <option value="AUD">AUD - Australian Dollar (A$)</option>
                            <option value="CAD">CAD - Canadian Dollar (C$)</option>
                        </select>
                    </div>

                    <!-- Timezone -->
                    <div>
                        <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">
                            Timezone <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="timezone"
                            v-model="form.timezone"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                        >
                            <option value="UTC">UTC</option>
                            <option value="America/New_York">America/New York (EST)</option>
                            <option value="America/Chicago">America/Chicago (CST)</option>
                            <option value="America/Los_Angeles">America/Los Angeles (PST)</option>
                            <option value="Europe/London">Europe/London (GMT)</option>
                            <option value="Europe/Paris">Europe/Paris (CET)</option>
                            <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                            <option value="Asia/Dubai">Asia/Dubai (GST)</option>
                            <option value="Asia/Tokyo">Asia/Tokyo (JST)</option>
                            <option value="Australia/Sydney">Australia/Sydney (AEDT)</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-8">
                <button
                    type="button"
                    @click="goBack"
                    class="inline-flex items-center px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </button>

                <button
                    type="button"
                    @click="submitSettings"
                    :disabled="!isValid || processing"
                    class="inline-flex items-center px-6 py-2 font-medium rounded-lg transition-all duration-200"
                    :class="isValid && !processing 
                        ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:shadow-lg' 
                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                >
                    <span v-if="processing">Processing...</span>
                    <span v-else>Continue</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    businessType: String,
});

const form = ref({
    store_name: '',
    admin_email: '',
    admin_password: '',
    admin_password_confirmation: '',
    contact_phone: '',
    store_address: '',
    currency: 'USD',
    timezone: 'UTC',
});

const errors = ref({});
const processing = ref(false);

const isValid = computed(() => {
    return form.value.store_name && 
           form.value.admin_email && 
           form.value.currency && 
           form.value.timezone;
});

const goBack = () => {
    router.visit('/setup/business-type');
};

const submitSettings = async () => {
    if (!isValid.value || processing.value) return;

    errors.value = {};
    processing.value = true;

    try {
        const response = await axios.post('/setup/save-business-settings', {
            ...form.value,
            business_type: props.businessType
        });

        if (response.data.success) {
            router.visit(`/setup/demo-data?type=${props.businessType}`);
        } else {
            errors.value = { general: response.data.message };
            processing.value = false;
        }
    } catch (error) {
        console.error('Error saving settings:', error);
        errors.value = { general: error.response?.data?.message || 'Failed to save settings' };
        processing.value = false;
    }
};
</script>

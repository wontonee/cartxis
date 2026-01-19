<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import ThemeLayout from '@/../../themes/cartxis-default/resources/views/layouts/ThemeLayout.vue';
import { ref } from 'vue';

interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
}

interface Props {
  user: User;
}

const props = defineProps<Props>();

// Profile Information Form
const profileForm = useForm({
  name: props.user.name,
  email: props.user.email,
});

const updateProfile = () => {
  profileForm.put('/account/profile', {
    preserveScroll: true,
  });
};

// Password Form
const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const updatePassword = () => {
  passwordForm.put('/account/profile/password', {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset();
    },
  });
};

// Email Preferences Form
const preferencesForm = useForm({
  newsletter_subscribed: true,
  order_notifications: true,
  promotional_emails: false,
});

const updatePreferences = () => {
  preferencesForm.put('/account/profile/preferences', {
    preserveScroll: true,
  });
};

// Delete Account
// const showDeleteModal = ref(false);
const deleteForm = useForm({
  password: '',
});

const deleteAccount = () => {
  if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
    deleteForm.delete('/account/profile', {
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <ThemeLayout>
    <Head title="My Profile" />

    <div class="container mx-auto px-4 py-8">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold mb-2">My Profile</h1>
          <p class="text-gray-600">Manage your account settings and preferences</p>
        </div>

        <div class="space-y-6">
          <!-- Personal Information -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold mb-6">Personal Information</h2>
            
            <form @submit.prevent="updateProfile" class="space-y-4">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                  Full Name <span class="text-red-500">*</span>
                </label>
                <input
                  id="name"
                  v-model="profileForm.name"
                  type="text"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-500': profileForm.errors.name }"
                />
                <p v-if="profileForm.errors.name" class="mt-1 text-sm text-red-600">
                  {{ profileForm.errors.name }}
                </p>
              </div>

              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                  Email Address <span class="text-red-500">*</span>
                </label>
                <input
                  id="email"
                  v-model="profileForm.email"
                  type="email"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-500': profileForm.errors.email }"
                />
                <p v-if="profileForm.errors.email" class="mt-1 text-sm text-red-600">
                  {{ profileForm.errors.email }}
                </p>
                <p v-if="!user.email_verified_at" class="mt-1 text-sm text-yellow-600">
                  Your email address is not verified. Please check your inbox for a verification link.
                </p>
              </div>

              <div class="flex items-center gap-3 pt-2">
                <button
                  type="submit"
                  :disabled="profileForm.processing"
                  class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
                </button>
                <span v-if="profileForm.recentlySuccessful" class="text-sm text-green-600">
                  ✓ Saved successfully
                </span>
              </div>
            </form>
          </div>

          <!-- Change Password -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold mb-6">Change Password</h2>
            
            <form @submit.prevent="updatePassword" class="space-y-4">
              <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                  Current Password <span class="text-red-500">*</span>
                </label>
                <input
                  id="current_password"
                  v-model="passwordForm.current_password"
                  type="password"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-500': passwordForm.errors.current_password }"
                />
                <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-600">
                  {{ passwordForm.errors.current_password }}
                </p>
              </div>

              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                  New Password <span class="text-red-500">*</span>
                </label>
                <input
                  id="password"
                  v-model="passwordForm.password"
                  type="password"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-500': passwordForm.errors.password }"
                />
                <p v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-600">
                  {{ passwordForm.errors.password }}
                </p>
                <p class="mt-1 text-xs text-gray-600">
                  Minimum 8 characters
                </p>
              </div>

              <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                  Confirm New Password <span class="text-red-500">*</span>
                </label>
                <input
                  id="password_confirmation"
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>

              <div class="flex items-center gap-3 pt-2">
                <button
                  type="submit"
                  :disabled="passwordForm.processing"
                  class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                </button>
                <span v-if="passwordForm.recentlySuccessful" class="text-sm text-green-600">
                  ✓ Password updated
                </span>
              </div>
            </form>
          </div>

          <!-- Email Preferences -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold mb-6">Email Preferences</h2>
            
            <form @submit.prevent="updatePreferences" class="space-y-4">
              <div class="space-y-3">
                <label class="flex items-start gap-3 cursor-pointer">
                  <input
                    v-model="preferencesForm.order_notifications"
                    type="checkbox"
                    class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                  />
                  <div>
                    <div class="font-medium">Order Status Updates</div>
                    <div class="text-sm text-gray-600">Get notified about your order status changes</div>
                  </div>
                </label>

                <label class="flex items-start gap-3 cursor-pointer">
                  <input
                    v-model="preferencesForm.newsletter_subscribed"
                    type="checkbox"
                    class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                  />
                  <div>
                    <div class="font-medium">Newsletter Subscription</div>
                    <div class="text-sm text-gray-600">Receive our weekly newsletter with product updates</div>
                  </div>
                </label>

                <label class="flex items-start gap-3 cursor-pointer">
                  <input
                    v-model="preferencesForm.promotional_emails"
                    type="checkbox"
                    class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                  />
                  <div>
                    <div class="font-medium">Promotional Offers</div>
                    <div class="text-sm text-gray-600">Get exclusive deals and special promotions</div>
                  </div>
                </label>
              </div>

              <div class="flex items-center gap-3 pt-2">
                <button
                  type="submit"
                  :disabled="preferencesForm.processing"
                  class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  {{ preferencesForm.processing ? 'Saving...' : 'Save Preferences' }}
                </button>
                <span v-if="preferencesForm.recentlySuccessful" class="text-sm text-green-600">
                  ✓ Preferences saved
                </span>
              </div>
            </form>
          </div>

          <!-- Danger Zone -->
          <div class="bg-red-50 rounded-lg border border-red-200 p-6">
            <h2 class="text-xl font-semibold text-red-900 mb-4">Danger Zone</h2>
            <p class="text-sm text-red-800 mb-4">
              Once you delete your account, there is no going back. Please be certain.
            </p>
            
            <button
              @click="deleteAccount"
              class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
              Delete Account
            </button>
          </div>
        </div>
      </div>
    </div>
  </ThemeLayout>
</template>

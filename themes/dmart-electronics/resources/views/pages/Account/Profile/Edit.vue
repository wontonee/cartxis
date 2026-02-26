<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import ThemeLayout from '../../../layouts/ThemeLayout.vue';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import { User, Mail, Lock, Check, TriangleAlert } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    auth: { user: { id: number; name: string; email: string; phone?: string } };
    theme: { name: string; slug: string };
}

const props = defineProps<Props>();
const saved = ref(false);

const profileForm = useForm({
    name: props.auth.user.name,
    email: props.auth.user.email,
    phone: props.auth.user.phone || '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updateProfile = () => {
    profileForm.put('/account/profile', {
        onSuccess: () => { saved.value = true; setTimeout(() => saved.value = false, 3000); },
    });
};

const updatePassword = () => {
    passwordForm.put('/account/password', {
        onSuccess: () => passwordForm.reset(),
        onFinish: () => passwordForm.reset('current_password'),
    });
};

// Delete Account
const showDeleteModal = ref(false);
const deleteForm = useForm({ password: '' });

const deleteAccount = () => { showDeleteModal.value = true; };
const cancelDelete = () => { showDeleteModal.value = false; deleteForm.reset(); };
const confirmDelete = () => {
    deleteForm.delete('/account/profile', {
        onError: () => { /* keep modal open on wrong password */ },
    });
};

const breadcrumbs = [
    { label: 'My Account', url: '/account' },
    { label: 'Profile' },
];
</script>

<template>
    <ThemeLayout>
        <Head title="Edit Profile" />
        <Breadcrumb :items="breadcrumbs" />

        <section class="py-8 lg:py-12">
            <div class="dmart-container max-w-2xl">
                <h1 class="text-2xl font-extrabold mb-8 text-title font-title">Edit Profile</h1>

                <!-- Profile Info -->
                <div class="border rounded-2xl p-6 mb-6">
                    <h3 class="font-bold mb-5 flex items-center gap-2 font-title">
                        <User class="w-5 h-5 text-theme-1" /> Personal Information
                    </h3>

                    <Transition name="fade">
                        <div v-if="saved" class="mb-4 text-sm text-green-600 bg-green-50 rounded-xl p-3 flex items-center gap-2">
                            <Check class="w-4 h-4" /> Profile updated successfully.
                        </div>
                    </Transition>

                    <form @submit.prevent="updateProfile" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Full Name</label>
                            <div class="relative">
                                <User class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input v-model="profileForm.name" type="text" required class="w-full pl-11 pr-4 py-3 border rounded-xl text-sm focus:border-theme-1 focus:ring-0" />
                            </div>
                            <p v-if="profileForm.errors.name" class="text-red-500 text-xs mt-1">{{ profileForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Email Address</label>
                            <div class="relative">
                                <Mail class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input v-model="profileForm.email" type="email" required class="w-full pl-11 pr-4 py-3 border rounded-xl text-sm focus:border-theme-1 focus:ring-0" />
                            </div>
                            <p v-if="profileForm.errors.email" class="text-red-500 text-xs mt-1">{{ profileForm.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Phone Number</label>
                            <input v-model="profileForm.phone" type="tel" class="w-full px-4 py-3 border rounded-xl text-sm focus:border-theme-1 focus:ring-0" placeholder="Optional" />
                        </div>
                        <button type="submit" :disabled="profileForm.processing" class="dmart-btn dmart-btn-primary py-3">
                            {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="border rounded-2xl p-6 mb-6">
                    <h3 class="font-bold mb-5 flex items-center gap-2 font-title">
                        <Lock class="w-5 h-5 text-theme-1" /> Change Password
                    </h3>
                    <form @submit.prevent="updatePassword" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Current Password</label>
                            <input v-model="passwordForm.current_password" type="password" required class="w-full px-4 py-3 border rounded-xl text-sm focus:border-theme-1 focus:ring-0" />
                            <p v-if="passwordForm.errors.current_password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.current_password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">New Password</label>
                            <input v-model="passwordForm.password" type="password" required class="w-full px-4 py-3 border rounded-xl text-sm focus:border-theme-1 focus:ring-0" />
                            <p v-if="passwordForm.errors.password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Confirm New Password</label>
                            <input v-model="passwordForm.password_confirmation" type="password" required class="w-full px-4 py-3 border rounded-xl text-sm focus:border-theme-1 focus:ring-0" />
                        </div>
                        <button type="submit" :disabled="passwordForm.processing" class="dmart-btn dmart-btn-primary py-3">
                            {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                        </button>
                    </form>
                </div>

                <!-- Danger Zone -->
                <div class="border border-red-200 bg-red-50 rounded-2xl p-6">
                    <h3 class="font-bold mb-2 flex items-center gap-2 font-title text-red-700">
                        <TriangleAlert class="w-5 h-5" /> Danger Zone
                    </h3>
                    <p class="text-sm text-red-700 mb-4">
                        Once you delete your account, all personal data is permanently removed.
                        Orders will be anonymized but retained for our records.
                    </p>
                    <button @click="deleteAccount" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        Delete My Account
                    </button>
                </div>
            </div>
        </section>

        <!-- Delete Account Confirmation Modal -->
        <Teleport to="body">
            <div
                v-if="showDeleteModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                @click.self="cancelDelete"
            >
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
                    <h3 class="text-lg font-extrabold text-red-700 font-title mb-2">Delete Your Account</h3>
                    <p class="text-sm text-gray-600 mb-5">
                        This is <strong>permanent and irreversible</strong>. Your cart, addresses,
                        and wishlist will be deleted. Orders will be anonymized.
                    </p>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold mb-1.5">Confirm your password</label>
                        <input
                            v-model="deleteForm.password"
                            type="password"
                            autocomplete="current-password"
                            class="w-full px-4 py-3 border rounded-xl text-sm focus:border-red-400 focus:ring-0"
                            :class="{ 'border-red-500': deleteForm.errors.password }"
                            @keyup.enter="confirmDelete"
                        />
                        <p v-if="deleteForm.errors.password" class="text-red-500 text-xs mt-1">{{ deleteForm.errors.password }}</p>
                    </div>

                    <div class="flex gap-3 justify-end">
                        <button
                            type="button"
                            @click="cancelDelete"
                            class="px-5 py-2.5 border rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            :disabled="deleteForm.processing || !deleteForm.password"
                            @click="confirmDelete"
                            class="px-5 py-2.5 bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-colors"
                        >
                            {{ deleteForm.processing ? 'Deleting...' : 'Yes, Delete My Account' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </ThemeLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

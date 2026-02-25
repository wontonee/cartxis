<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import ThemeLayout from '../../../layouts/ThemeLayout.vue';
import Breadcrumb from '../../../components/Breadcrumb.vue';
import { User, Mail, Lock, Check } from 'lucide-vue-next';
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
                <div class="border rounded-2xl p-6">
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
            </div>
        </section>
    </ThemeLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

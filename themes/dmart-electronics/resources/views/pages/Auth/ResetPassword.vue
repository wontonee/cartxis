<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import { Lock, Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    email: string;
    token: string;
    theme: { name: string; slug: string };
}

const props = defineProps<Props>();
const showPassword = ref(false);

const form = useForm({
    token: props.token, email: props.email,
    password: '', password_confirmation: '',
});

const submit = () => {
    form.post('/reset-password', { onFinish: () => form.reset('password', 'password_confirmation') });
};
</script>

<template>
    <ThemeLayout>
        <Head title="Reset Password" />
        <section class="py-12 lg:py-20">
            <div class="max-w-md mx-auto px-4">
                <div class="bg-white rounded-2xl border p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-2xl font-extrabold text-title font-title">Reset Password</h1>
                        <p class="text-gray-500 text-sm mt-2">Enter your new password below.</p>
                    </div>
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Email</label>
                            <input v-model="form.email" type="email" required readonly class="w-full px-4 py-3 border rounded-xl text-sm bg-gray-50" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">New Password</label>
                            <div class="relative">
                                <Lock class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required autofocus class="w-full pl-11 pr-11 py-3 border rounded-xl text-sm focus:border-theme-1 focus:ring-0" />
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                                    <component :is="showPassword ? EyeOff : Eye" class="w-4 h-4" />
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Confirm Password</label>
                            <div class="relative">
                                <Lock class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" required class="w-full pl-11 pr-4 py-3 border rounded-xl text-sm focus:border-theme-1 focus:ring-0" />
                            </div>
                        </div>
                        <button type="submit" :disabled="form.processing" class="dmart-btn dmart-btn-primary w-full py-3.5 text-base justify-center">
                            {{ form.processing ? 'Resetting...' : 'Reset Password' }}
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </ThemeLayout>
</template>

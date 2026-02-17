<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import { Mail, Lock, Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    canResetPassword: boolean;
    status?: string;
    theme: { name: string; slug: string };
}

defineProps<Props>();
const showPassword = ref(false);
const form = useForm({ email: '', password: '', remember: false });

const submit = () => {
    form.post('/login', { onFinish: () => form.reset('password') });
};
</script>

<template>
    <ThemeLayout>
        <Head title="Login" />

        <section class="py-12 lg:py-20">
            <div class="max-w-md mx-auto px-4">
                <div class="bg-white rounded-2xl border p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-2xl font-extrabold text-title font-title">Welcome Back</h1>
                        <p class="text-gray-500 text-sm mt-2">Sign in to your account</p>
                    </div>

                    <div v-if="status" class="mb-4 text-sm text-green-600 bg-green-50 rounded-xl p-3 text-center">{{ status }}</div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Email Address</label>
                            <div class="relative">
                                <Mail class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input v-model="form.email" type="email" required autofocus class="w-full pl-11 pr-4 py-3 border rounded-xl text-sm focus:border-theme-1 focus:ring-0" placeholder="your@email.com" />
                            </div>
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Password</label>
                            <div class="relative">
                                <Lock class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required class="w-full pl-11 pr-11 py-3 border rounded-xl text-sm focus:border-theme-1 focus:ring-0" placeholder="••••••••" />
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                                    <component :is="showPassword ? EyeOff : Eye" class="w-4 h-4" />
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <input v-model="form.remember" type="checkbox" class="rounded accent-theme-1" />
                                Remember me
                            </label>
                            <Link v-if="canResetPassword" href="/forgot-password" class="text-sm font-semibold text-theme-1">
                                Forgot Password?
                            </Link>
                        </div>

                        <button type="submit" :disabled="form.processing" class="dmart-btn dmart-btn-primary w-full py-3.5 text-base justify-center">
                            {{ form.processing ? 'Signing in...' : 'Sign In' }}
                        </button>
                    </form>

                    <div class="text-center mt-6 text-sm text-gray-500">
                        Don't have an account?
                        <Link href="/register" class="font-semibold text-theme-1">Create Account</Link>
                    </div>
                </div>
            </div>
        </section>
    </ThemeLayout>
</template>

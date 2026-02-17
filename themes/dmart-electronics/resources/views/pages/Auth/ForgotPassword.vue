<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import { Mail, ArrowLeft } from 'lucide-vue-next';

interface Props {
    status?: string;
    theme: { name: string; slug: string };
}

defineProps<Props>();
const form = useForm({ email: '' });
const submit = () => { form.post('/forgot-password'); };
</script>

<template>
    <ThemeLayout>
        <Head title="Forgot Password" />
        <section class="py-12 lg:py-20">
            <div class="max-w-md mx-auto px-4">
                <div class="bg-white rounded-2xl border p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-2xl font-extrabold text-title font-title">Forgot Password?</h1>
                        <p class="text-gray-500 text-sm mt-2">Enter your email and we'll send you a reset link.</p>
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
                        <button type="submit" :disabled="form.processing" class="dmart-btn dmart-btn-primary w-full py-3.5 text-base justify-center">
                            {{ form.processing ? 'Sending...' : 'Send Reset Link' }}
                        </button>
                    </form>
                    <div class="text-center mt-6">
                        <Link href="/login" class="inline-flex items-center gap-1 text-sm font-semibold text-theme-1">
                            <ArrowLeft class="w-4 h-4" /> Back to Login
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </ThemeLayout>
</template>

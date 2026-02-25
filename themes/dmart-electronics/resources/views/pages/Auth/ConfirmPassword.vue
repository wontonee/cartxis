<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import { Lock } from 'lucide-vue-next';

interface Props { theme: { name: string; slug: string }; }
defineProps<Props>();

const form = useForm({ password: '' });
const submit = () => { form.post('/user/confirm-password', { onFinish: () => form.reset() }); };
</script>

<template>
    <ThemeLayout>
        <Head title="Confirm Password" />
        <section class="py-12 lg:py-20">
            <div class="max-w-md mx-auto px-4">
                <div class="bg-white rounded-2xl border p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-2xl font-extrabold text-title font-title">Confirm Password</h1>
                        <p class="text-gray-500 text-sm mt-2">Please confirm your password before continuing.</p>
                    </div>
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Password</label>
                            <div class="relative">
                                <Lock class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input v-model="form.password" type="password" required autofocus class="w-full pl-11 pr-4 py-3 border rounded-xl text-sm focus:border-theme-1 focus:ring-0" />
                            </div>
                            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                        </div>
                        <button type="submit" :disabled="form.processing" class="dmart-btn dmart-btn-primary w-full py-3.5 text-base justify-center">
                            {{ form.processing ? 'Confirming...' : 'Confirm' }}
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </ThemeLayout>
</template>

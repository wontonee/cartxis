<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import { ShieldCheck } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props { theme: { name: string; slug: string }; }
defineProps<Props>();

const useRecovery = ref(false);
const form = useForm({ code: '', recovery_code: '' });

const submit = () => {
    form.post('/two-factor-challenge', { onFinish: () => form.reset() });
};
</script>

<template>
    <ThemeLayout>
        <Head title="Two-Factor Challenge" />
        <section class="py-12 lg:py-20">
            <div class="max-w-md mx-auto px-4">
                <div class="bg-white rounded-2xl border p-8">
                    <div class="text-center mb-8">
                        <ShieldCheck class="w-12 h-12 mx-auto mb-3 text-theme-1" />
                        <h1 class="text-2xl font-extrabold text-title font-title">Two-Factor Authentication</h1>
                        <p class="text-gray-500 text-sm mt-2">
                            {{ useRecovery ? 'Enter one of your emergency recovery codes.' : 'Enter the authentication code from your app.' }}
                        </p>
                    </div>
                    <form @submit.prevent="submit" class="space-y-5">
                        <div v-if="!useRecovery">
                            <label class="block text-sm font-semibold mb-1.5">Authentication Code</label>
                            <input v-model="form.code" type="text" inputmode="numeric" autofocus autocomplete="one-time-code" class="w-full text-center tracking-[0.5em] text-lg px-4 py-3 border rounded-xl focus:border-theme-1 focus:ring-0" placeholder="000000" />
                            <p v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</p>
                        </div>
                        <div v-else>
                            <label class="block text-sm font-semibold mb-1.5">Recovery Code</label>
                            <input v-model="form.recovery_code" type="text" autofocus class="w-full px-4 py-3 border rounded-xl text-sm focus:border-theme-1 focus:ring-0" />
                            <p v-if="form.errors.recovery_code" class="text-red-500 text-xs mt-1">{{ form.errors.recovery_code }}</p>
                        </div>
                        <button type="submit" :disabled="form.processing" class="dmart-btn dmart-btn-primary w-full py-3.5 text-base justify-center">
                            {{ form.processing ? 'Verifying...' : 'Verify' }}
                        </button>
                    </form>
                    <button @click="useRecovery = !useRecovery" class="block mx-auto mt-4 text-sm font-semibold text-theme-1">
                        {{ useRecovery ? 'Use authentication code' : 'Use recovery code' }}
                    </button>
                </div>
            </div>
        </section>
    </ThemeLayout>
</template>

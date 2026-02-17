<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import { MailCheck } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    status?: string;
    theme: { name: string; slug: string };
}

defineProps<Props>();
const form = useForm({});
const submit = () => { form.post('/email/verification-notification'); };
</script>

<template>
    <ThemeLayout>
        <Head title="Verify Email" />
        <section class="py-12 lg:py-20">
            <div class="max-w-md mx-auto px-4">
                <div class="bg-white rounded-2xl border p-8 text-center">
                    <MailCheck class="w-16 h-16 mx-auto mb-4 text-theme-1" />
                    <h1 class="text-2xl font-extrabold mb-3 text-title font-title">Verify Your Email</h1>
                    <p class="text-gray-500 text-sm mb-6">We've sent a verification link to your email. Please check your inbox and click the link to verify your account.</p>

                    <div v-if="status === 'verification-link-sent'" class="mb-4 text-sm text-green-600 bg-green-50 rounded-xl p-3">
                        A new verification link has been sent to your email.
                    </div>

                    <form @submit.prevent="submit">
                        <button type="submit" :disabled="form.processing" class="dmart-btn dmart-btn-primary w-full py-3.5 text-base justify-center">
                            {{ form.processing ? 'Sending...' : 'Resend Verification Email' }}
                        </button>
                    </form>

                    <Link href="/logout" method="post" as="button" class="block mx-auto mt-4 text-sm text-gray-500 hover:text-gray-700">
                        Log Out
                    </Link>
                </div>
            </div>
        </section>
    </ThemeLayout>
</template>

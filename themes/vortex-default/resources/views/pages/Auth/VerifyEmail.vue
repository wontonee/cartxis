<script setup lang="ts">
import EmailVerificationNotificationController from '@/actions/App/Http/Controllers/Auth/EmailVerificationNotificationController';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { LoaderCircle, Mail, CheckCircle } from 'lucide-vue-next';
import { computed } from 'vue';
import ThemeLayout from '../../layouts/ThemeLayout.vue';

const page = usePage();
const theme = computed(() => page.props.theme as any);
const siteConfig = computed(() => page.props.siteConfig as any);
const primaryColor = computed(() => theme.value?.settings?.primary_color || '#3b82f6');

defineProps<{
    status?: string;
}>();
</script>

<template>
    <ThemeLayout>
        <Head :title="`Verify Email - ${siteConfig?.name || 'Shop'}`" />

        <div class="min-h-screen flex items-center justify-center px-4 py-12 bg-gray-50">
            <div class="w-full max-w-md">
                <!-- Card -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <!-- Icon -->
                    <div class="flex justify-center mb-6">
                        <div 
                            class="w-16 h-16 rounded-full flex items-center justify-center"
                            :style="{ backgroundColor: primaryColor + '20' }"
                        >
                            <Mail 
                                class="w-8 h-8"
                                :style="{ color: primaryColor }"
                            />
                        </div>
                    </div>

                    <!-- Header -->
                    <div class="text-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">
                            Verify Your Email
                        </h1>
                        <p class="text-gray-600">
                            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?
                        </p>
                    </div>

                    <!-- Success Message -->
                    <div
                        v-if="status === 'verification-link-sent'"
                        class="mb-6 p-3 text-center text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-md flex items-center justify-center"
                    >
                        <CheckCircle class="w-4 h-4 mr-2" />
                        A new verification link has been sent to your email address.
                    </div>

                    <!-- Resend Form -->
                    <Form
                        v-bind="EmailVerificationNotificationController.store.form()"
                        v-slot="{ processing }"
                        class="space-y-4"
                    >
                        <Button
                            type="submit"
                            class="w-full text-white font-medium py-2.5 rounded-md hover:opacity-90 transition-opacity"
                            :style="{ backgroundColor: primaryColor }"
                            :disabled="processing"
                        >
                            <LoaderCircle
                                v-if="processing"
                                class="h-5 w-5 animate-spin mr-2"
                            />
                            {{ processing ? 'Sending...' : 'Resend Verification Email' }}
                        </Button>
                    </Form>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">or</span>
                        </div>
                    </div>

                    <!-- Logout Link -->
                    <div class="text-center">
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="text-sm text-gray-600 hover:underline"
                        >
                            Log out
                        </Link>
                    </div>
                </div>

                <!-- Back to Shop -->
                <div class="text-center mt-6">
                    <TextLink
                        href="/"
                        class="text-sm text-gray-600 hover:underline inline-flex items-center"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to shop
                    </TextLink>
                </div>

                <!-- Email Tips -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-blue-900 mb-2">
                        Didn't receive the email?
                    </h3>
                    <ul class="text-xs text-blue-800 space-y-1">
                        <li>• Check your spam or junk folder</li>
                        <li>• Make sure you entered the correct email address</li>
                        <li>• Click the "Resend" button above if needed</li>
                    </ul>
                </div>
            </div>
        </div>
    </ThemeLayout>
</template>

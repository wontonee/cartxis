<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import Breadcrumb from '../../components/Breadcrumb.vue';
import { Trash2, ShieldCheck, AlertTriangle, Mail } from 'lucide-vue-next';

interface Props {
    appName: string;
    supportEmail: string;
    appUrl: string;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { label: 'Home', url: '/' },
    { label: 'Account Deletion' },
];
</script>

<template>
    <ThemeLayout>
        <Head title="Account & Data Deletion" />
        <Breadcrumb :items="breadcrumbs" />

        <section class="py-8 lg:py-12">
            <div class="dmart-container max-w-2xl">
                <h1 class="text-2xl font-extrabold mb-2 text-title font-title">
                    Account &amp; Data Deletion
                </h1>
                <p class="text-gray-500 text-sm mb-8">
                    How to delete your {{ props.appName }} account and what happens to your data.
                </p>

                <!-- How to delete -->
                <div class="border rounded-2xl p-6 mb-5">
                    <h2 class="font-bold mb-4 flex items-center gap-2 font-title">
                        <Trash2 class="w-5 h-5 text-theme-1" /> How to Delete Your Account
                    </h2>
                    <p class="text-sm text-gray-600 mb-4">
                        You can delete your account directly from the app or website:
                    </p>
                    <ol class="list-decimal list-inside space-y-2.5 text-sm text-gray-700">
                        <li>
                            Log in at
                            <a :href="props.appUrl + '/account/login'" class="text-theme-1 hover:underline">
                                {{ props.appUrl }}/account/login
                            </a>
                        </li>
                        <li>Open <strong>My Profile</strong> from the account menu</li>
                        <li>Scroll to the <strong>Danger Zone</strong> section</li>
                        <li>Click <strong>"Delete My Account"</strong></li>
                        <li>Enter your password and confirm by clicking <strong>"Yes, Delete My Account"</strong></li>
                    </ol>
                    <p class="text-xs text-gray-500 mt-4">
                        Deletion is immediate and permanent once confirmed.
                    </p>
                </div>

                <!-- What is deleted -->
                <div class="border rounded-2xl p-6 mb-5">
                    <h2 class="font-bold mb-4 flex items-center gap-2 font-title">
                        <ShieldCheck class="w-5 h-5 text-theme-1" /> What Gets Deleted
                    </h2>
                    <p class="text-sm text-gray-600 mb-3">
                        The following personal data is <strong>permanently deleted immediately</strong>:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-sm text-gray-700">
                        <li>Account profile — name, email address, phone number</li>
                        <li>Saved delivery addresses</li>
                        <li>Wishlist items</li>
                        <li>Shopping cart contents</li>
                        <li>All active login sessions and API tokens</li>
                    </ul>
                </div>

                <!-- What is retained -->
                <div class="border border-amber-200 bg-amber-50 rounded-2xl p-6 mb-5">
                    <h2 class="font-bold mb-4 flex items-center gap-2 font-title text-amber-800">
                        <AlertTriangle class="w-5 h-5" /> What Is Retained
                    </h2>
                    <p class="text-sm text-amber-800 mb-3">
                        <strong>Order history is kept but fully anonymized</strong> for legal and accounting purposes:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-sm text-amber-800 mb-3">
                        <li>Your name and email are removed from all orders</li>
                        <li>Order records (items, amounts, dates) are retained but not linked to you</li>
                    </ul>
                    <p class="text-xs text-amber-600">
                        Retention period: anonymized order records are kept for at least 7 years as required by accounting regulations.
                    </p>
                </div>

                <!-- Request by email -->
                <div class="border rounded-2xl p-6 mb-6">
                    <h2 class="font-bold mb-3 flex items-center gap-2 font-title">
                        <Mail class="w-5 h-5 text-theme-1" /> Can't Log In? Request by Email
                    </h2>
                    <p class="text-sm text-gray-600 mb-3">
                        If you cannot access your account, email our support team and we'll process the deletion within 7 business days.
                    </p>
                    <a :href="'mailto:' + props.supportEmail" class="dmart-btn dmart-btn-primary inline-flex items-center gap-2 py-2.5 text-sm">
                        <Mail class="w-4 h-4" /> {{ props.supportEmail }}
                    </a>
                </div>

                <p class="text-xs text-gray-400 text-center">
                    This page is provided by <strong>{{ props.appName }}</strong> in compliance with
                    Google Play and Apple App Store data-safety requirements.
                </p>
            </div>
        </section>
    </ThemeLayout>
</template>

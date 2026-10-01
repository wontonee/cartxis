<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

interface Props {
    appName: string;
    appVersion: string;
}

defineProps<Props>();

const startSetup = () => {
    router.visit('/setup/business-type');
};
</script>

<template>
    <Head title="Setup Wizard" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
        <div class="max-w-2xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-8 text-white">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center">
                        <img
                            src="/logos/logo_icon.png"
                            alt="Cartxis logo"
                            class="w-12 h-12 object-contain"
                        />
                    </div>
                </div>
                <h1 class="text-4xl font-bold text-center mb-2">{{ appName }}</h1>
                <p class="text-center text-blue-100">Version {{ appVersion }}</p>
            </div>

            <div class="p-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-3">
                        Set up your store
                    </h2>
                    <p class="text-lg text-gray-600">
                        Cartxis works for retail, digital downloads, and quote/RFQ catalogs — not only physical shops.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Choose how you sell</h3>
                            <p class="text-sm text-gray-600">Retail, digital downloads, RFQ, or start blank</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Store basics</h3>
                            <p class="text-sm text-gray-600">Name, country, currency, and timezone</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Optional samples</h3>
                            <p class="text-sm text-gray-600">Import demo products — or skip anytime</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Go to admin</h3>
                            <p class="text-sm text-gray-600">Finish and start adding your real catalog</p>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button
                        type="button"
                        @click="startSetup"
                        class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg shadow-lg hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200"
                    >
                        <span>Get Started</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-4 text-center text-sm text-gray-600">
                <p>Open-source eCommerce — customize everything after setup</p>
            </div>
        </div>
    </div>
</template>

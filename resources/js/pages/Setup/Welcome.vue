<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

interface Props {
    appName: string;
    appVersion: string;
}

const props = defineProps<Props>();

const startSetup = () => {
    router.visit('/setup/business-type');
};
</script>

<template>
    <Head title="Setup Wizard" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
        <div class="max-w-2xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-8 text-white">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center text-4xl font-bold text-blue-600">
                        V
                    </div>
                </div>
                <h1 class="text-4xl font-bold text-center mb-2">{{ appName }}</h1>
                <p class="text-center text-blue-100">Version {{ appVersion }}</p>
            </div>

            <!-- Content -->
            <div class="p-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">
                        Welcome to Setup Wizard
                    </h2>
                    <p class="text-lg text-gray-600">
                        Let's get your eCommerce store up and running in just a few steps!
                    </p>
                </div>

                <!-- Features -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Business Type Selection</h3>
                            <p class="text-sm text-gray-600">Choose from Retail, Kirana, Electronics, or Fashion</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Demo Data Import</h3>
                            <p class="text-sm text-gray-600">Pre-loaded products, categories, and content</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Ready-to-Use Store</h3>
                            <p class="text-sm text-gray-600">Start selling immediately after setup</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Quick Setup</h3>
                            <p class="text-sm text-gray-600">Complete setup in less than 5 minutes</p>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="text-center">
                    <button
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

            <!-- Footer -->
            <div class="bg-gray-50 px-8 py-4 text-center text-sm text-gray-600">
                <p>Open-source eCommerce platform built with ❤️</p>
            </div>
        </div>
    </div>
</template>

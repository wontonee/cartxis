<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface BusinessType {
    id: string;
    name: string;
    description: string;
}

interface Props {
    businessTypes: BusinessType[];
}

const props = defineProps<Props>();

const selectedType = ref<string>('');

const selectBusinessType = (typeId: string) => {
    selectedType.value = typeId;
};

const continueToNextStep = () => {
    if (selectedType.value) {
        router.visit(`/setup/business-settings?type=${selectedType.value}`);
    }
};

const goBack = () => {
    router.visit('/setup');
};
</script>

<template>
    <Head title="Select Business Type" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
        <div class="max-w-4xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white">
                <h1 class="text-3xl font-bold text-center">Select Your Business Type</h1>
                <p class="text-center text-blue-100 mt-2">Choose the type that best matches your store</p>
            </div>

            <!-- Progress Bar -->
            <div class="px-8 pt-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Step 1 of 3</span>
                    <span class="text-sm font-medium text-gray-700">33%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 33%"></div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        v-for="type in businessTypes"
                        :key="type.id"
                        @click="selectBusinessType(type.id)"
                        :class="[
                            'border-2 rounded-lg p-6 cursor-pointer transition-all duration-200 hover:shadow-lg',
                            selectedType === type.id
                                ? 'border-blue-600 bg-blue-50 ring-2 ring-blue-600'
                                : 'border-gray-200 hover:border-blue-300'
                        ]"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="text-xl font-bold text-gray-800">{{ type.name }}</h3>
                            <div
                                :class="[
                                    'w-6 h-6 rounded-full border-2 flex items-center justify-center',
                                    selectedType === type.id
                                        ? 'border-blue-600 bg-blue-600'
                                        : 'border-gray-300'
                                ]"
                            >
                                <svg
                                    v-if="selectedType === type.id"
                                    class="w-4 h-4 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-gray-600">{{ type.description }}</p>
                    </div>
                </div>

                <div class="mt-8 text-center text-sm text-gray-600">
                    <p>💡 Don't worry, you can customize everything later!</p>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="bg-gray-50 px-8 py-4 flex items-center justify-between">
                <button
                    @click="goBack"
                    class="inline-flex items-center px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    Back
                </button>
                <button
                    @click="continueToNextStep"
                    :disabled="!selectedType"
                    :class="[
                        'inline-flex items-center px-6 py-2 font-medium rounded-lg transition-all duration-200',
                        selectedType
                            ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105'
                            : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                    ]"
                >
                    Continue
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

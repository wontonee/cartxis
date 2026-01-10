<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

interface BusinessType {
    id: string;
    name: string;
    description: string;
}

interface Props {
    businessType: string;
    businessTypes: BusinessType[];
}

const props = defineProps<Props>();

const importProducts = ref(true);
const importing = ref(false);
const importSuccess = ref(false);
const importError = ref('');
const stats = ref<any>({});

const selectedBusinessType = props.businessTypes.find(t => t.id === props.businessType);

const startImport = async () => {
    importing.value = true;
    importError.value = '';
    
    try {
        const response = await axios.post('/setup/import-demo-data', {
            business_type: props.businessType,
            import_products: importProducts.value
        });

        if (response.data.success) {
            importSuccess.value = true;
            stats.value = response.data.stats;
            
            // Wait 2 seconds then continue to finish page
            setTimeout(() => {
                router.visit('/setup/finish');
            }, 2000);
        } else {
            importError.value = response.data.message;
            importing.value = false;
        }
    } catch (error: any) {
        importError.value = error.response?.data?.message || 'Failed to import demo data';
        importing.value = false;
    }
};

const skipImport = () => {
    router.visit('/setup/finish');
};

const goBack = () => {
    router.visit(`/setup/business-settings?type=${props.businessType}`);
};
</script>

<template>
    <Head title="Import Demo Data" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
        <div class="max-w-3xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white">
                <h1 class="text-3xl font-bold text-center">Import Demo Data</h1>
                <p class="text-center text-blue-100 mt-2">Pre-load your store with sample data</p>
            </div>

            <!-- Progress Bar -->
            <div class="px-8 pt-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Step 3 of 4</span>
                    <span class="text-sm font-medium text-gray-700">75%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 75%"></div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <!-- Selected Business Type -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm text-gray-600">Selected Business Type</p>
                            <p class="font-bold text-gray-800">{{ selectedBusinessType?.name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Import Options -->
                <div v-if="!importing && !importSuccess" class="space-y-6">
                    <div class="border-2 border-gray-200 rounded-lg p-6">
                        <label class="flex items-start cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="importProducts"
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-1"
                            />
                            <div class="ml-3">
                                <h3 class="font-semibold text-gray-800 mb-2">Import Sample Products</h3>
                                <p class="text-sm text-gray-600">
                                    Import pre-configured products, categories, and brands relevant to your business type.
                                    This will help you see how your store looks with real data.
                                </p>
                                <div class="mt-3 text-sm text-gray-500">
                                    Includes:
                                    <ul class="list-disc list-inside ml-2 mt-1">
                                        <li>Product categories</li>
                                        <li>Sample products with descriptions</li>
                                        <li>Brand information</li>
                                        <li>Sample pages and blocks</li>
                                    </ul>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="text-sm">
                                <p class="font-medium text-yellow-800 mb-1">Note:</p>
                                <p class="text-yellow-700">
                                    Demo data is for demonstration purposes only. You can delete or modify it later from the admin panel.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Importing State -->
                <div v-if="importing && !importSuccess" class="text-center py-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Importing Demo Data...</h3>
                    <p class="text-gray-600">This may take a few moments. Please don't close this window.</p>
                </div>

                <!-- Success State -->
                <div v-if="importSuccess" class="text-center py-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Import Successful!</h3>
                    <p class="text-gray-600 mb-4">Demo data has been imported successfully.</p>
                    
                    <div v-if="stats" class="inline-block bg-gray-50 rounded-lg p-4 text-left">
                        <p class="text-sm text-gray-600 mb-2">Imported:</p>
                        <ul class="text-sm space-y-1">
                            <li v-if="stats.categories">✓ {{ stats.categories }} Categories</li>
                            <li v-if="stats.products">✓ {{ stats.products }} Products</li>
                            <li v-if="stats.brands">✓ {{ stats.brands }} Brands</li>
                            <li v-if="stats.pages">✓ {{ stats.pages }} Pages</li>
                            <li v-if="stats.blocks">✓ {{ stats.blocks }} Blocks</li>
                        </ul>
                    </div>
                    
                    <p class="text-sm text-gray-500 mt-4">Redirecting to final step...</p>
                </div>

                <!-- Error State -->
                <div v-if="importError" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-medium text-red-800">Import Failed</p>
                            <p class="text-sm text-red-700 mt-1">{{ importError }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div v-if="!importing && !importSuccess" class="bg-gray-50 px-8 py-4 flex items-center justify-between">
                <button
                    @click="goBack"
                    class="inline-flex items-center px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    Back
                </button>
                <div class="flex space-x-3">
                    <button
                        @click="skipImport"
                        class="inline-flex items-center px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors"
                    >
                        Skip for Now
                    </button>
                    <button
                        @click="startImport"
                        class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Import Demo Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

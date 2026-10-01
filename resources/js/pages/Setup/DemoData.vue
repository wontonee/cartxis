<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import axios from 'axios';

interface BusinessType {
    id: string;
    name: string;
    description: string;
    group?: string;
    group_label?: string;
    has_demo?: boolean;
}

interface Props {
    businessType: string;
    businessTypes: BusinessType[];
    hasDemo: boolean;
}

const props = defineProps<Props>();

const importProducts = ref(props.hasDemo);
const importing = ref(false);
const importSuccess = ref(false);
const importError = ref('');
const stats = ref<Record<string, number>>({});

const selectedBusinessType = computed(() =>
    props.businessTypes.find((t) => t.id === props.businessType)
);

const demoHints = computed(() => {
    switch (props.businessType) {
        case 'digital':
            return [
                'Downloadable and virtual sample products',
                'Categories for ebooks, software, and courses',
                'No shipping required on these samples',
            ];
        case 'rfq':
            return [
                'Quote-only sample products (type: quote)',
                'Categories for manufacturing, B2B, and bulk',
                'Customers use Request Quote instead of checkout',
            ];
        case 'blank':
            return [
                'Empty catalog — add products yourself',
                'Supports simple, digital, and quote products later',
                'No sample data will be imported',
            ];
        default:
            return [
                'Product categories and brands',
                'Sample products with descriptions',
                'Sample pages and content blocks',
            ];
    }
});

const startImport = async () => {
    importing.value = true;
    importError.value = '';

    try {
        const response = await axios.post('/setup/import-demo-data', {
            business_type: props.businessType,
            import_products: props.hasDemo ? importProducts.value : false,
        });

        if (response.data.success) {
            importSuccess.value = true;
            stats.value = response.data.stats ?? {};

            setTimeout(() => {
                router.visit('/setup/finish');
            }, 1500);
        } else {
            importError.value = response.data.message;
            importing.value = false;
        }
    } catch (error: any) {
        importError.value = error.response?.data?.message || 'Failed to import demo data';
        importing.value = false;
    }
};

const continueWithoutSamples = async () => {
    importing.value = true;
    importError.value = '';

    try {
        await axios.post('/setup/import-demo-data', {
            business_type: props.businessType,
            import_products: false,
        });
        router.visit('/setup/finish');
    } catch (error: any) {
        importError.value = error.response?.data?.message || 'Could not continue setup';
        importing.value = false;
    }
};

const goBack = () => {
    router.visit(`/setup/business-settings?type=${props.businessType}`);
};
</script>

<template>
    <Head title="Sample catalog" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
        <div class="max-w-3xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white">
                <h1 class="text-3xl font-bold text-center">
                    {{ hasDemo ? 'Sample catalog' : 'Empty catalog' }}
                </h1>
                <p class="text-center text-blue-100 mt-2">
                    {{ hasDemo
                        ? 'Optionally load sample products for your chosen selling model'
                        : 'You chose a blank start — continue without sample products'
                    }}
                </p>
            </div>

            <div class="px-8 pt-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Step 3 of 4</span>
                    <span class="text-sm font-medium text-gray-700">75%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 75%"></div>
                </div>
            </div>

            <div class="p-8">
                <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-gray-600">Selected starter</p>
                    <p class="font-bold text-gray-800">{{ selectedBusinessType?.name }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ selectedBusinessType?.description }}</p>
                </div>

                <div v-if="!importing && !importSuccess" class="space-y-6">
                    <div v-if="hasDemo" class="border-2 border-gray-200 rounded-lg p-6">
                        <label class="flex items-start cursor-pointer gap-3">
                            <input
                                type="checkbox"
                                v-model="importProducts"
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5"
                            />
                            <div>
                                <h3 class="font-semibold text-gray-800 mb-1">Import sample products</h3>
                                <p class="text-sm text-gray-600 mb-3">
                                    Recommended if you want to explore the admin and storefront quickly. You can delete samples later.
                                </p>
                                <ul class="text-sm text-gray-600 list-disc list-inside space-y-1">
                                    <li v-for="hint in demoHints" :key="hint">{{ hint }}</li>
                                </ul>
                            </div>
                        </label>
                    </div>

                    <div v-else class="border-2 border-gray-200 rounded-lg p-6">
                        <h3 class="font-semibold text-gray-800 mb-2">Ready to continue</h3>
                        <ul class="text-sm text-gray-600 list-disc list-inside space-y-1">
                            <li v-for="hint in demoHints" :key="hint">{{ hint }}</li>
                        </ul>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-sm text-yellow-800">
                            Skipping is always fine — add real products from Catalog after setup.
                        </p>
                    </div>
                </div>

                <div v-if="importing && !importSuccess" class="text-center py-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Working…</h3>
                    <p class="text-gray-600">Please keep this window open.</p>
                </div>

                <div v-if="importSuccess" class="text-center py-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">All set</h3>
                    <div v-if="stats && Object.keys(stats).length" class="inline-block bg-gray-50 rounded-lg p-4 text-left mt-2">
                        <ul class="text-sm space-y-1 text-gray-700">
                            <li v-if="stats.categories">✓ {{ stats.categories }} categories</li>
                            <li v-if="stats.products">✓ {{ stats.products }} products</li>
                            <li v-if="stats.brands">✓ {{ stats.brands }} brands</li>
                        </ul>
                    </div>
                    <p class="text-sm text-gray-500 mt-4">Continuing…</p>
                </div>

                <div v-if="importError" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <p class="font-medium text-red-800">Something went wrong</p>
                    <p class="text-sm text-red-700 mt-1">{{ importError }}</p>
                </div>
            </div>

            <div v-if="!importing && !importSuccess" class="bg-gray-50 px-8 py-4 flex items-center justify-between">
                <button
                    type="button"
                    @click="goBack"
                    class="inline-flex items-center px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    Back
                </button>
                <div class="flex gap-3">
                    <button
                        v-if="hasDemo"
                        type="button"
                        @click="continueWithoutSamples"
                        class="inline-flex items-center px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors"
                    >
                        Skip samples
                    </button>
                    <button
                        type="button"
                        @click="hasDemo && importProducts ? startImport() : continueWithoutSamples()"
                        class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200"
                    >
                        {{ hasDemo && importProducts ? 'Import samples' : 'Continue' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

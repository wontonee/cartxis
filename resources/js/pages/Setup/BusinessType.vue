<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface BusinessType {
    id: string;
    name: string;
    description: string;
    group: string;
    group_label: string;
    has_demo: boolean;
}

interface Props {
    businessTypes: BusinessType[];
}

const props = defineProps<Props>();

const selectedType = ref<string>('');

const groupOrder = ['physical', 'digital', 'services', 'blank'];

const groupedTypes = computed(() => {
    const map = new Map<string, { key: string; label: string; types: BusinessType[] }>();

    for (const type of props.businessTypes) {
        if (!map.has(type.group)) {
            map.set(type.group, {
                key: type.group,
                label: type.group_label,
                types: [],
            });
        }
        map.get(type.group)!.types.push(type);
    }

    return groupOrder
        .filter((key) => map.has(key))
        .map((key) => map.get(key)!);
});

const selectBusinessType = (typeId: string) => {
    selectedType.value = typeId;
};

const continueToNextStep = () => {
    if (selectedType.value) {
        router.visit(`/setup/business-settings?type=${selectedType.value}`);
    }
};

const startBlank = () => {
    selectedType.value = 'blank';
    router.visit('/setup/business-settings?type=blank');
};

const goBack = () => {
    router.visit('/setup');
};
</script>

<template>
    <Head title="How will you sell?" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
        <div class="max-w-4xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white">
                <h1 class="text-3xl font-bold text-center">How will you sell?</h1>
                <p class="text-center text-blue-100 mt-2 max-w-2xl mx-auto">
                    Choose a starting catalog. Physical retail, digital downloads, quote/RFQ, or a blank store — you can change everything later.
                </p>
            </div>

            <div class="px-8 pt-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Step 1 of 4</span>
                    <span class="text-sm font-medium text-gray-700">25%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 25%"></div>
                </div>
            </div>

            <div class="p-8 space-y-8">
                <section
                    v-for="group in groupedTypes"
                    :key="group.key"
                    class="space-y-3"
                >
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        {{ group.label }}
                    </h2>

                    <div
                        :class="[
                            'grid gap-4',
                            group.types.length === 1 ? 'grid-cols-1' : 'grid-cols-1 md:grid-cols-2',
                        ]"
                    >
                        <button
                            v-for="type in group.types"
                            :key="type.id"
                            type="button"
                            @click="selectBusinessType(type.id)"
                            :class="[
                                'text-left border-2 rounded-xl p-5 transition-all duration-200 hover:shadow-lg',
                                selectedType === type.id
                                    ? 'border-blue-600 bg-blue-50 ring-2 ring-blue-600'
                                    : 'border-gray-200 hover:border-blue-300',
                            ]"
                        >
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-gray-800">{{ type.name }}</h3>
                                <span
                                    :class="[
                                        'mt-0.5 w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0',
                                        selectedType === type.id
                                            ? 'border-blue-600 bg-blue-600'
                                            : 'border-gray-300',
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
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ type.description }}</p>
                            <p
                                v-if="!type.has_demo"
                                class="mt-3 text-xs font-medium text-gray-500"
                            >
                                No sample products — empty catalog
                            </p>
                            <p
                                v-else
                                class="mt-3 text-xs font-medium text-blue-700"
                            >
                                Optional sample products available next
                            </p>
                        </button>
                    </div>
                </section>

                <div class="rounded-xl bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-gray-600 text-center">
                    Not sure yet?
                    <button
                        type="button"
                        class="font-semibold text-blue-700 underline underline-offset-2 hover:no-underline ml-1"
                        @click="startBlank"
                    >
                        Start with a blank catalog
                    </button>
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-4 flex items-center justify-between">
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
                <button
                    type="button"
                    @click="continueToNextStep"
                    :disabled="!selectedType"
                    :class="[
                        'inline-flex items-center px-6 py-2 font-medium rounded-lg transition-all duration-200',
                        selectedType
                            ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105'
                            : 'bg-gray-300 text-gray-500 cursor-not-allowed',
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

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight, Home } from 'lucide-vue-next';

interface BreadcrumbItem {
    label: string;
    href?: string;
    url?: string;
}

defineProps<{
    items: BreadcrumbItem[];
    title?: string;
}>();
</script>

<template>
    <div class="dmart-breadcrumb">
        <div class="dmart-container py-4">
            <h1 v-if="title" class="text-2xl font-bold mb-2 text-title font-title">
                {{ title }}
            </h1>
            <nav class="flex items-center gap-1.5 text-sm">
                <Link href="/" class="flex items-center gap-1 text-gray-500 hover:text-theme-1 transition-colors">
                    <Home class="w-3.5 h-3.5" /> Home
                </Link>
                <template v-for="(item, index) in items" :key="index">
                    <ChevronRight class="w-3.5 h-3.5 text-gray-400" />
                    <Link v-if="item.href || item.url" :href="item.href || item.url || '#'" class="text-gray-500 hover:text-theme-1 transition-colors">
                        {{ item.label }}
                    </Link>
                    <span v-else class="font-medium text-theme-1">{{ item.label }}</span>
                </template>
            </nav>
        </div>
    </div>
</template>

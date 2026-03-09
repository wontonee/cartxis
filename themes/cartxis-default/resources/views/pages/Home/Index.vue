<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import UIBlockRenderer from '@/components/UIEditor/UIBlockRenderer.vue';

interface Props {
    theme: {
        name: string;
        slug: string;
        settings: Record<string, unknown>;
    };
    siteConfig: {
        name: string;
        url: string;
        description: string;
    };
    layoutData?: Record<string, unknown> | null;
}

const props = defineProps<Props>();
</script>

<template>
    <ThemeLayout>
        <Head :title="`${siteConfig.name} - Premium E-Commerce Store`" />
        <UIBlockRenderer :layout="layoutData ?? null" :editor-mode="false" />
    </ThemeLayout>
</template>
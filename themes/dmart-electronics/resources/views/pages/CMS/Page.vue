<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';
import Breadcrumb from '../../components/Breadcrumb.vue';
import UIBlockRenderer from '@/components/UIEditor/UIBlockRenderer.vue';

interface CMSPage {
    id: number; title: string; slug: string; content: string;
    meta_title?: string; meta_description?: string;
    featured_image?: string;
}

interface Props {
    page: CMSPage;
    theme: { name: string; slug: string };
    siteConfig: { name: string };
    layoutData?: Record<string, unknown> | null;
}

const props = defineProps<Props>();
const breadcrumbs = [{ label: props.page.title }];
const hasLayout = computed(() => !!(props.layoutData?.sections && (props.layoutData.sections as unknown[]).length));
</script>

<template>
    <ThemeLayout>
        <Head :title="page.meta_title || page.title" />

        <Breadcrumb :items="breadcrumbs" />

        <!-- Hero -->
        <section v-if="page.featured_image" class="relative h-[250px] lg:h-[350px] overflow-hidden">
            <img :src="page.featured_image" :alt="page.title" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                <h1 class="text-3xl lg:text-4xl font-extrabold text-white text-center font-title">
                    {{ page.title }}
                </h1>
            </div>
        </section>

        <section class="py-10 lg:py-16">
            <div class="dmart-container max-w-4xl">
                <h1
                    v-if="!page.featured_image"
                    class="text-2xl lg:text-3xl font-extrabold mb-8 text-title font-title"
                >
                    {{ page.title }}
                </h1>

                <template v-if="hasLayout">
                    <UIBlockRenderer :layout="layoutData" :editor-mode="false" />
                </template>
                <template v-else>
                    <div class="prose prose-lg max-w-none text-gray-600 prose-headings:text-title prose-headings:font-title prose-a:text-theme-1 prose-img:rounded-xl" v-html="page.content" />
                </template>
            </div>
        </section>
    </ThemeLayout>
</template>

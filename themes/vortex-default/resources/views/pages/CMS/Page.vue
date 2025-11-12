<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';

interface Props {
    page: {
        title: string;
        content: string;
        meta_title?: string;
        meta_description?: string;
        meta_keywords?: string;
        url_key: string;
        created_at?: string;
        updated_at?: string;
    };
    theme: {
        name: string;
        slug: string;
        settings: {
            primary_color: string;
            secondary_color: string;
            features: Record<string, boolean>;
        };
    };
    siteConfig: {
        name: string;
        url: string;
        description: string;
    };
}

const props = defineProps<Props>();
</script>

<template>
    <ThemeLayout>
        <Head>
            <title>{{ page.meta_title || page.title }}</title>
            <meta v-if="page.meta_description" name="description" :content="page.meta_description" />
            <meta v-if="page.meta_keywords" name="keywords" :content="page.meta_keywords" />
        </Head>

        <div class="bg-gray-50 min-h-screen">
            <!-- Page Header -->
            <div class="bg-white border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <h1 class="text-4xl font-bold text-gray-900">{{ page.title }}</h1>
                    <p v-if="page.updated_at" class="text-sm text-gray-500 mt-2">
                        Last updated: {{ page.updated_at }}
                    </p>
                </div>
            </div>

            <!-- Page Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="bg-white rounded-lg shadow-sm p-8">
                    <div 
                        v-html="page.content" 
                        class="prose prose-lg max-w-none
                               prose-headings:text-gray-900 
                               prose-h1:text-3xl prose-h1:font-bold prose-h1:mb-4
                               prose-h2:text-2xl prose-h2:font-bold prose-h2:mt-8 prose-h2:mb-4
                               prose-h3:text-xl prose-h3:font-semibold prose-h3:mt-6 prose-h3:mb-3
                               prose-p:text-gray-700 prose-p:leading-relaxed prose-p:mb-4
                               prose-a:text-indigo-600 prose-a:no-underline hover:prose-a:underline
                               prose-ul:list-disc prose-ul:pl-6 prose-ul:mb-4
                               prose-ol:list-decimal prose-ol:pl-6 prose-ol:mb-4
                               prose-li:text-gray-700 prose-li:mb-2
                               prose-strong:text-gray-900 prose-strong:font-semibold
                               prose-blockquote:border-l-4 prose-blockquote:border-indigo-500 
                               prose-blockquote:pl-4 prose-blockquote:italic prose-blockquote:text-gray-600"
                    ></div>
                </div>
            </div>
        </div>
    </ThemeLayout>
</template>

<style scoped>
/* Additional custom styles for CMS content if needed */
</style>

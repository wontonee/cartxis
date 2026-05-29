<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';

interface Category { id: number; name: string; slug: string }
interface Post {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    content: string;
    featured_image: string | null;
    published_at: string;
    view_count: number;
    meta_title: string | null;
    meta_description: string | null;
    meta_keywords: string | null;
    category: Category | null;
    creator: { name: string } | null;
}

defineProps<{
    post: Post;
    related: Post[];
}>();

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
</script>

<template>
    <ThemeLayout>
        <Head>
            <title>{{ post.meta_title || post.title }}</title>
            <meta v-if="post.meta_description" name="description" :content="post.meta_description" />
            <meta v-if="post.meta_keywords" name="keywords" :content="post.meta_keywords" />
        </Head>

        <div class="bg-gray-50 min-h-screen">
            <!-- Hero / Featured Image -->
            <div v-if="post.featured_image" class="w-full max-h-96 overflow-hidden">
                <img :src="post.featured_image" :alt="post.title" class="w-full object-cover max-h-96" />
            </div>

            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Breadcrumb -->
                <nav class="text-sm text-gray-500 mb-6 flex gap-2">
                    <Link href="/" class="hover:text-blue-600">Home</Link>
                    <span>/</span>
                    <Link href="/blog" class="hover:text-blue-600">Blog</Link>
                    <span>/</span>
                    <span class="text-gray-900">{{ post.title }}</span>
                </nav>

                <article class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-8 sm:p-12">
                        <!-- Category -->
                        <div v-if="post.category" class="mb-4">
                            <Link :href="`/blog?category_id=${post.category.id}`" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                                {{ post.category.name }}
                            </Link>
                        </div>

                        <!-- Title -->
                        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4 leading-tight">{{ post.title }}</h1>

                        <!-- Meta -->
                        <div class="flex flex-wrap gap-4 text-sm text-gray-500 mb-8 pb-8 border-b border-gray-100">
                            <span v-if="post.creator" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ post.creator.name }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ formatDate(post.published_at) }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ post.view_count }} views
                            </span>
                        </div>

                        <!-- Content -->
                        <div class="prose prose-gray max-w-none" v-html="post.content"></div>
                    </div>
                </article>

                <!-- Related Posts -->
                <div v-if="related.length" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Posts</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        <article
                            v-for="relPost in related"
                            :key="relPost.id"
                            class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group"
                        >
                            <Link :href="`/blog/${relPost.slug}`">
                                <div class="aspect-video bg-gray-100 overflow-hidden">
                                    <img
                                        v-if="relPost.featured_image"
                                        :src="relPost.featured_image"
                                        :alt="relPost.title"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-200">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1" />
                                        </svg>
                                    </div>
                                </div>
                            </Link>
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900 mb-1 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                    <Link :href="`/blog/${relPost.slug}`">{{ relPost.title }}</Link>
                                </h3>
                                <p class="text-xs text-gray-500">{{ formatDate(relPost.published_at) }}</p>
                            </div>
                        </article>
                    </div>
                </div>

                <!-- Back Link -->
                <div class="mt-8">
                    <Link href="/blog" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Blog
                    </Link>
                </div>
            </div>
        </div>
    </ThemeLayout>
</template>

<style>
/* Allow blog HTML content to be styled naturally */
.prose h1, .prose h2, .prose h3, .prose h4 { font-weight: bold; margin: 1rem 0 0.5rem; }
.prose h1 { font-size: 2em; }
.prose h2 { font-size: 1.5em; }
.prose h3 { font-size: 1.17em; }
.prose p { margin: 0.75rem 0; line-height: 1.75; }
.prose a { color: #2563eb; text-decoration: underline; }
.prose ul, .prose ol { padding-left: 1.5rem; margin: 0.5rem 0; }
.prose ul { list-style-type: disc; }
.prose ol { list-style-type: decimal; }
.prose li { margin: 0.25rem 0; }
.prose blockquote { border-left: 4px solid #e5e7eb; padding-left: 1rem; margin: 1rem 0; color: #6b7280; font-style: italic; }
.prose img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 1rem 0; }
.prose code { background: #f3f4f6; padding: 0.125rem 0.375rem; border-radius: 0.25rem; font-size: 0.875em; }
.prose pre { background: #1f2937; color: #f9fafb; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; margin: 1rem 0; }
.prose pre code { background: none; padding: 0; }
</style>

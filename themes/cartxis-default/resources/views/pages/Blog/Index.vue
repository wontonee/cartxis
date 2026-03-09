<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ThemeLayout from '../../layouts/ThemeLayout.vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    posts_count: number;
}

interface Post {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    featured_image: string | null;
    published_at: string;
    category: Category | null;
    creator: { name: string } | null;
}

interface PaginatedPosts {
    data: Post[];
    links: { url: string | null; label: string; active: boolean }[];
    meta: { current_page: number; last_page: number; total: number };
}

defineProps<{
    posts: PaginatedPosts;
    categories: Category[];
}>();

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
</script>

<template>
    <ThemeLayout>
        <Head>
            <title>Blog</title>
            <meta name="description" content="Read our latest articles and news" />
        </Head>

        <div class="bg-gray-50 min-h-screen">
            <!-- Blog Header -->
            <div class="bg-white border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <h1 class="text-4xl font-bold text-gray-900">Blog</h1>
                    <p class="mt-2 text-gray-600">Latest articles and news from our team</p>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <!-- Posts Grid -->
                    <div class="lg:col-span-3">
                        <div v-if="posts.data.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <article
                                v-for="post in posts.data"
                                :key="post.id"
                                class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group"
                            >
                                <!-- Featured Image -->
                                <Link :href="`/blog/${post.slug}`">
                                    <div class="aspect-video bg-gray-100 overflow-hidden">
                                        <img
                                            v-if="post.featured_image"
                                            :src="post.featured_image"
                                            :alt="post.title"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1M19 20a2 2 0 002-2V8a2 2 0 00-2-2h-5M8 12h.01M12 12h.01M16 12h.01" />
                                            </svg>
                                        </div>
                                    </div>
                                </Link>

                                <div class="p-5">
                                    <!-- Category Badge -->
                                    <div v-if="post.category" class="mb-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">
                                            {{ post.category.name }}
                                        </span>
                                    </div>

                                    <!-- Title -->
                                    <h2 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                        <Link :href="`/blog/${post.slug}`">{{ post.title }}</Link>
                                    </h2>

                                    <!-- Excerpt -->
                                    <p v-if="post.excerpt" class="text-sm text-gray-600 mb-4 line-clamp-2">{{ post.excerpt }}</p>

                                    <!-- Meta -->
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <span>{{ formatDate(post.published_at) }}</span>
                                        <Link :href="`/blog/${post.slug}`" class="text-blue-600 hover:text-blue-800 font-medium">
                                            Read more →
                                        </Link>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <!-- Empty state -->
                        <div v-else class="text-center py-16">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1M19 20a2 2 0 002-2V8a2 2 0 00-2-2h-5" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">No posts yet</h3>
                            <p class="text-gray-500">Check back soon for new articles.</p>
                        </div>

                        <!-- Pagination -->
                        <div v-if="posts.meta.last_page > 1" class="mt-8 flex justify-center gap-1">
                            <template v-for="link in posts.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="px-3 py-1.5 rounded text-sm font-medium border transition-colors"
                                    :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="px-3 py-1.5 rounded text-sm text-gray-400"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>

                    <!-- Sidebar: Categories -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 sticky top-4">
                            <h3 class="text-base font-semibold text-gray-900 mb-4">Categories</h3>
                            <ul class="space-y-2">
                                <li>
                                    <Link href="/blog" class="flex items-center justify-between text-sm text-gray-700 hover:text-blue-600 transition-colors py-1">
                                        <span>All Posts</span>
                                        <span class="text-gray-400 text-xs">{{ posts.meta.total }}</span>
                                    </Link>
                                </li>
                                <li v-for="cat in categories" :key="cat.id">
                                    <Link :href="`/blog?category_id=${cat.id}`" class="flex items-center justify-between text-sm text-gray-700 hover:text-blue-600 transition-colors py-1">
                                        <span>{{ cat.name }}</span>
                                        <span class="text-gray-400 text-xs">{{ cat.posts_count }}</span>
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ThemeLayout>
</template>

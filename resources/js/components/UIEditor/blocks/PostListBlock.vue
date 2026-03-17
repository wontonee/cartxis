<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

interface BlogPost {
    id: number
    title: string
    slug: string
    excerpt: string | null
    featured_image: string | null
    published_at: string
    category: { id: number; name: string } | null
    creator: { name: string } | null
}

const posts = ref<BlogPost[]>([])
const loading = ref(false)

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })

onMounted(async () => {
    if (props.editorMode) return
    loading.value = true
    try {
        const params: Record<string, unknown> = { limit: props.settings.count ?? 5 }
        if (props.settings.category_id) params.category_id = props.settings.category_id
        const { data } = await axios.get('/api/blog/posts', { params })
        posts.value = data.data ?? data
    } catch {
        // silently fail
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <div class="w-full">
        <h2 v-if="settings.title" class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
            {{ settings.title }}
        </h2>

        <!-- Loading skeletons -->
        <div v-if="loading" class="space-y-4">
            <div
                v-for="n in (settings.count ?? 5)"
                :key="n"
                class="flex gap-4 p-4 rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse"
            >
                <div class="w-24 h-20 flex-shrink-0 rounded-lg bg-gray-200 dark:bg-gray-700" />
                <div class="flex-1 space-y-2 py-1">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4" />
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2" />
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-full" />
                </div>
            </div>
        </div>

        <!-- Post list -->
        <div v-else-if="posts.length" class="space-y-4">
            <article
                v-for="post in posts"
                :key="post.id"
                class="flex gap-4 p-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow group"
            >
                <a
                    v-if="settings.show_thumbnail"
                    :href="`/blog/${post.slug}`"
                    class="flex-shrink-0 w-24 h-20 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700"
                >
                    <img
                        v-if="post.featured_image"
                        :src="post.featured_image"
                        :alt="post.title"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 12h6m-6-4h6" />
                        </svg>
                    </div>
                </a>
                <div class="flex-1 min-w-0">
                    <div v-if="settings.show_category && post.category" class="mb-1">
                        <a
                            :href="`/blog?category_id=${post.category.id}`"
                            class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide hover:underline"
                        >
                            {{ post.category.name }}
                        </a>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1 line-clamp-2 group-hover:text-blue-600 transition-colors">
                        <a :href="`/blog/${post.slug}`">{{ post.title }}</a>
                    </h3>
                    <p
                        v-if="settings.show_excerpt && post.excerpt"
                        class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-2"
                    >
                        {{ post.excerpt }}
                    </p>
                    <span v-if="settings.show_date" class="text-xs text-gray-400">
                        {{ formatDate(post.published_at) }}
                    </span>
                </div>
            </article>
        </div>

        <!-- Editor placeholder -->
        <div v-else-if="editorMode" class="space-y-3">
            <div
                v-for="n in (settings.count ?? 5)"
                :key="n"
                class="flex gap-4 p-4 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-400 text-sm items-center"
            >
                <div class="w-24 h-16 flex-shrink-0 rounded-lg bg-gray-200 dark:bg-gray-700" />
                <span>Post item {{ n }}</span>
            </div>
        </div>

        <!-- No posts -->
        <div v-else class="text-center py-12 text-gray-400">
            <p>No posts found.</p>
        </div>
    </div>
</template>

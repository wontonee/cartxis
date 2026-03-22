<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

interface BlogPost {
    id: number
    title: string
    slug: string
    excerpt: string | null
    featured_image: string | null
    published_at: string
    view_count: number
    category: { id: number; name: string } | null
    creator: { name: string } | null
}

const posts = ref<BlogPost[]>([])
const loading = ref(false)

const colsClass = computed(() => {
    const cols = (props.settings.columns as number) ?? 3
    const map: Record<number, string> = { 1: 'grid-cols-1', 2: 'grid-cols-1 sm:grid-cols-2', 3: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3', 4: 'grid-cols-2 lg:grid-cols-4' }
    return map[cols] ?? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3'
})

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })

onMounted(async () => {
    if (props.editorMode) return
    loading.value = true
    try {
        const params: Record<string, unknown> = {
            limit: props.settings.count ?? 6,
        }
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
        <!-- Block Title -->
        <h2 v-if="settings.title" class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
            {{ settings.title }}
        </h2>

        <!-- Loading -->
        <div v-if="loading" class="grid gap-6" :class="colsClass">
            <div v-for="n in (settings.count ?? 6)" :key="n" class="h-64 bg-gray-100 dark:bg-gray-800 rounded-xl animate-pulse" />
        </div>

        <!-- Posts Grid -->
        <div v-else-if="posts.length" class="grid gap-6" :class="colsClass">
            <article
                v-for="post in posts"
                :key="post.id"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow group"
            >
                <a :href="`/blog/${post.slug}`">
                    <div v-if="settings.show_image" class="aspect-video bg-gray-100 dark:bg-gray-700 overflow-hidden">
                        <img
                            v-if="post.featured_image"
                            :src="post.featured_image"
                            :alt="post.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 12h6m-6-4h6" />
                            </svg>
                        </div>
                    </div>
                </a>
                <div class="p-5">
                    <div v-if="settings.show_category && post.category" class="mb-2">
                        <a :href="`/blog?category_id=${post.category.id}`" class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide hover:underline">
                            {{ post.category.name }}
                        </a>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                        <a :href="`/blog/${post.slug}`">{{ post.title }}</a>
                    </h3>
                    <p v-if="settings.show_excerpt && post.excerpt" class="text-sm text-gray-500 dark:text-gray-400 line-clamp-3 mb-3">
                        {{ post.excerpt }}
                    </p>
                    <div class="flex items-center gap-3 text-xs text-gray-400 mt-auto">
                        <span v-if="settings.show_date">{{ formatDate(post.published_at) }}</span>
                        <span v-if="settings.show_author && post.creator">by {{ post.creator.name }}</span>
                    </div>
                </div>
            </article>
        </div>

        <!-- Editor placeholder -->
        <div v-else-if="editorMode" class="grid gap-6" :class="colsClass">
            <div
                v-for="n in (settings.count ?? 6)"
                :key="n"
                class="h-64 bg-gray-50 dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-400 text-sm"
            >
                Blog Post {{ n }}
            </div>
        </div>

        <!-- No posts -->
        <div v-else class="text-center py-12 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 12h6m-6-4h6" />
            </svg>
            <p>No blog posts found.</p>
        </div>
    </div>
</template>

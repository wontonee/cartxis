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

const post = ref<BlogPost | null>(null)
const loading = ref(false)

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })

onMounted(async () => {
    if (props.editorMode) return
    loading.value = true
    try {
        const params: Record<string, unknown> = { limit: 1 }
        if (props.settings.category_id) params.category_id = props.settings.category_id
        const { data } = await axios.get('/api/blog/posts', { params })
        const list = data.data ?? data
        post.value = list[0] ?? null
    } catch {
        // silently fail
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <div class="w-full">
        <h2 v-if="settings.section_title" class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
            {{ settings.section_title }}
        </h2>

        <!-- Loading skeleton -->
        <div
            v-if="loading"
            class="rounded-2xl bg-gray-100 dark:bg-gray-800 animate-pulse"
            :style="`min-height: ${settings.min_height ?? 400}px`"
        />

        <!-- Featured post card -->
        <article
            v-else-if="post"
            class="relative rounded-2xl overflow-hidden shadow-lg group"
            :style="`min-height: ${settings.min_height ?? 400}px`"
        >
            <a :href="`/blog/${post.slug}`" class="absolute inset-0">
                <img
                    v-if="post.featured_image"
                    :src="post.featured_image"
                    :alt="post.title"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                />
                <div v-else class="w-full h-full bg-gradient-to-br from-blue-600 to-indigo-700" />
            </a>
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />
            <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                <div v-if="post.category" class="mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider bg-blue-600 px-3 py-1 rounded-full">
                        {{ post.category.name }}
                    </span>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold mb-3 group-hover:text-blue-300 transition-colors">
                    <a :href="`/blog/${post.slug}`">{{ post.title }}</a>
                </h3>
                <p
                    v-if="settings.show_excerpt && post.excerpt"
                    class="text-gray-200 mb-4 line-clamp-2 text-base max-w-2xl"
                >
                    {{ post.excerpt }}
                </p>
                <div class="text-sm text-gray-300">
                    <span v-if="post.creator">By {{ post.creator.name }}</span>
                    <span v-if="post.creator && post.published_at"> · </span>
                    <span>{{ formatDate(post.published_at) }}</span>
                </div>
            </div>
        </article>

        <!-- Editor placeholder -->
        <div
            v-else-if="editorMode"
            class="rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center text-gray-400 bg-gray-50 dark:bg-gray-800"
            :style="`min-height: ${settings.min_height ?? 400}px`"
        >
            <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
            <p class="text-sm font-medium">Featured Post</p>
            <p class="text-xs mt-1 opacity-70">Will display the latest featured blog post</p>
        </div>

        <!-- No post fallback -->
        <div v-else class="text-center py-16 text-gray-400">
            <p>No featured post available.</p>
        </div>
    </div>
</template>

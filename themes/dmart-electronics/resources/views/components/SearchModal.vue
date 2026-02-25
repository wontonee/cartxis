<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { X, Search } from 'lucide-vue-next';
import axios from 'axios';

interface Props { open: boolean; }
defineProps<Props>();
const emit = defineEmits<{ close: [] }>();

const query = ref('');
const results = ref<any[]>([]);
const isSearching = ref(false);
let searchDebounce: ReturnType<typeof setTimeout> | null = null;

const doSearch = () => {
    if (searchDebounce) clearTimeout(searchDebounce);
    if (query.value.length < 2) { results.value = []; return; }
    isSearching.value = true;
    searchDebounce = setTimeout(async () => {
        try {
            const { data } = await axios.get('/search/suggestions', { params: { q: query.value } });
            results.value = data.suggestions || [];
        } catch { results.value = []; }
        isSearching.value = false;
    }, 300);
};

const submitSearch = () => {
    if (query.value.trim()) {
        window.location.href = `/search?q=${encodeURIComponent(query.value)}`;
        emit('close');
    }
};
</script>

<template>
    <Transition name="fade">
        <div v-if="open" class="fixed inset-0 bg-black/60 z-[80] flex items-start justify-center pt-[15vh]" @click.self="$emit('close')">
            <div class="bg-white rounded-2xl w-[90vw] max-w-2xl overflow-hidden shadow-2xl">
                <div class="flex items-center gap-3 px-5 py-4 border-b">
                    <Search class="w-5 h-5 text-gray-400" />
                    <input
                        v-model="query"
                        type="text"
                        placeholder="Search for products..."
                        class="flex-1 text-lg outline-none font-body"
                        @input="doSearch"
                        @keydown.enter="submitSearch"
                        @keydown.escape="$emit('close')"
                        autofocus
                    />
                    <button @click="$emit('close')" class="p-1 text-gray-400 hover:text-gray-700">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <div v-if="results.length > 0" class="max-h-80 overflow-y-auto">
                    <Link
                        v-for="item in results"
                        :key="item.id"
                        :href="`/product/${item.slug}`"
                        class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 transition-colors"
                        @click="$emit('close')"
                    >
                        <img :src="item.image" :alt="item.name" class="w-12 h-12 object-contain rounded" />
                        <div>
                            <div class="font-medium text-gray-900">{{ item.name }}</div>
                            <div class="text-sm text-theme-1">{{ item.price }}</div>
                        </div>
                    </Link>
                </div>
                <div v-else-if="query.length >= 2 && !isSearching" class="px-5 py-8 text-center text-gray-400">
                    No results found for "{{ query }}"
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

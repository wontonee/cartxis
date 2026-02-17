<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { ArrowUp } from 'lucide-vue-next';

const show = ref(false);
const handleScroll = () => { show.value = window.scrollY > 400; };
const scrollToTop = () => { window.scrollTo({ top: 0, behavior: 'smooth' }); };

onMounted(() => window.addEventListener('scroll', handleScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));
</script>

<template>
    <Transition name="fade">
        <button
            v-if="show"
            @click="scrollToTop"
            class="fixed bottom-6 right-6 z-50 w-12 h-12 rounded-full text-white shadow-lg flex items-center justify-center hover:scale-110 transition-transform bg-theme-1"
            aria-label="Back to top"
        >
            <ArrowUp class="w-5 h-5" />
        </button>
    </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }
</style>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import ThemeHeader from '../components/ThemeHeader.vue';
import ThemeFooter from '../components/ThemeFooter.vue';
import UIBlockRenderer from '@/components/UIEditor/UIBlockRenderer.vue';
import { useThemeSettings } from '@/composables/useThemeSettings';
import { ChevronUp } from 'lucide-vue-next';

const page = usePage();
const { cssVars, backToTop, containerWidth } = useThemeSettings();

const theme = computed(() => page.props.theme);
const siteConfig = computed(() => page.props.siteConfig);
const categories = computed(() => page.props.categories || []);
const footerRaw = computed(() => (page.props as Record<string, unknown>).footerRegion ?? null);

function parseRegion(v: unknown): Record<string, unknown> | null {
    if (!v) return null;
    if (typeof v === 'string') {
        try { return JSON.parse(v); } catch { return null; }
    }
    if (typeof v === 'object') return v as Record<string, unknown>;
    return null;
}

const footerRegion = computed(() => parseRegion(footerRaw.value));
const showBackToTop = ref(false);

const containerStyle = computed(() => ({
    maxWidth: containerWidth.value,
}));

const onScroll = () => {
    showBackToTop.value = window.scrollY > 400;
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
});
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 flex flex-col theme-root"
        :style="cssVars"
    >
        <ThemeHeader
            :theme="theme"
            :site-config="siteConfig"
            :categories="categories"
            :container-style="containerStyle"
        />

        <main class="flex-1">
            <slot />
        </main>

        <UIBlockRenderer
            v-if="footerRegion"
            :layout="footerRegion"
            :editor-mode="false"
        />
        <ThemeFooter
            v-else
            :theme="theme"
            :site-config="siteConfig"
            :container-style="containerStyle"
        />

        <button
            v-if="backToTop && showBackToTop"
            type="button"
            class="fixed bottom-6 right-6 z-50 flex h-11 w-11 items-center justify-center rounded-full text-white shadow-lg transition-all hover:scale-105 hover:shadow-xl"
            style="background-color: var(--theme-primary)"
            aria-label="Back to top"
            @click="scrollToTop"
        >
            <ChevronUp class="h-5 w-5" />
        </button>
    </div>
</template>

<style scoped>
.theme-root {
    font-family: var(--theme-font, Inter, sans-serif);
}
</style>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ThemeHeader from '../components/ThemeHeader.vue';
import ThemeFooter from '../components/ThemeFooter.vue';
import UIBlockRenderer from '@/components/UIEditor/UIBlockRenderer.vue';

const page = usePage();

const theme = computed(() => page.props.theme);
const siteConfig = computed(() => page.props.siteConfig);
const categories = computed(() => page.props.categories || []);
const footerRaw = computed(() => (page.props as Record<string, unknown>).footerRegion ?? null);
const headerRaw = computed(() => (page.props as Record<string, unknown>).headerRegion ?? null);

// Normalise: may arrive as a JSON string (double-encoded DB row) or already as an object
function parseRegion(v: unknown): Record<string, unknown> | null {
  if (!v) return null
  if (typeof v === 'string') { try { return JSON.parse(v) } catch { return null } }
  if (typeof v === 'object') return v as Record<string, unknown>
  return null
}
const footerRegion = computed(() => parseRegion(footerRaw.value))
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <ThemeHeader :theme="theme" :site-config="siteConfig" :categories="categories" />
        
        <main class="flex-1">
            <slot />
        </main>

        <!-- Use UIEditor-managed footer when a published region exists, else fall back to theme default -->
        <UIBlockRenderer
            v-if="footerRegion"
            :layout="footerRegion"
            :editor-mode="false"
        />
        <ThemeFooter
            v-else
            :theme="theme"
            :site-config="siteConfig"
        />
    </div>
</template>

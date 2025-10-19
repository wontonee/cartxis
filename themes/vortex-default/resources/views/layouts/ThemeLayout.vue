<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ThemeHeader from '../components/ThemeHeader.vue';
import ThemeFooter from '../components/ThemeFooter.vue';

const page = usePage();

const theme = computed(() => page.props.theme);
const siteConfig = computed(() => page.props.siteConfig);
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <ThemeHeader :theme="theme" :site-config="siteConfig" />
        
        <main class="flex-1">
            <slot />
        </main>
        
        <ThemeFooter :theme="theme" :site-config="siteConfig" />
    </div>
</template>

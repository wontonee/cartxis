<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import UIBlockRenderer from '@/components/UIEditor/UIBlockRenderer.vue'

const props = defineProps<{
  page: { id: number | null; title: string } | null
  pageType?: 'cms_page' | 'homepage'
  layoutData: Record<string, unknown> | null
}>()
</script>

<template>
  <Head :title="`Preview — ${page?.title ?? 'Page'}`" />
  <div class="uie-preview-root">
    <UIBlockRenderer :layout="layoutData" :editor-mode="false" />
  </div>
</template>

<style>
html, body { margin: 0; padding: 0; }
.uie-preview-root { min-height: 100vh; }
</style>

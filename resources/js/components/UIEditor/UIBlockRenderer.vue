<script setup lang="ts">
import { defineAsyncComponent, computed, markRaw, onErrorCaptured, type Component } from 'vue'
import { usePage } from '@inertiajs/vue3'

interface Block   { id: string; type: string; settings: Record<string, unknown> }
interface Column  { id: string; width: number; settings: Record<string, unknown>; blocks: Block[] }
interface Section { id: string; type: string; settings: Record<string, unknown>; columns: Column[] }
interface Layout  { sections?: Section[] }

const props = defineProps<{
  layout: Layout | null | Record<string, unknown>
  editorMode?: boolean
}>()

const page = usePage()

// Active theme slug from Inertia shared props (set by ShareFrontendData middleware)
const themeSlug = computed(() => (page.props as any).theme?.slug as string | null ?? null)

function toPascal(str: string): string {
  if (!str) return 'Text'
  return str.split(/[-_]/).map(w => w.charAt(0).toUpperCase() + w.slice(1)).join('')
}

// Cache component definitions keyed by theme+type.
// Re-creating defineAsyncComponent on every render causes Vue to unmount+remount
// every block, losing state and triggering infinite update loops.
const blockComponentCache = new Map<string, Component>()

function blockComponent(type: string) {
  if (!type) return null
  const cacheKey = `${themeSlug.value ?? ''}:${type}`
  if (!blockComponentCache.has(cacheKey)) {
    const slug   = themeSlug.value
    const pascal = toPascal(type)
    const silent = { template: '<div />' }   // errorComponent — hides broken blocks silently
    blockComponentCache.set(cacheKey, markRaw(defineAsyncComponent({
      loader: () => {
        // 1. Try the active theme's override first
        // 2. Fall back to the shared block library
        // 3. Final fallback: TextBlock (never a blank crash)
        const shared = () => import(`./blocks/${pascal}Block.vue`).catch(() => import('./blocks/TextBlock.vue'))
        return slug ? import(`@themes/${slug}/blocks/${pascal}Block.vue`).catch(shared) : shared()
      },
      errorComponent: silent,
    })))
  }
  return blockComponentCache.get(cacheKey)!
}

// Catch runtime errors thrown by individual block components so one broken block
// cannot take down the entire renderer (all blocks disappearing).
onErrorCaptured((err) => {
  if (import.meta.env.DEV) console.warn('[UIBlockRenderer] block render error:', err)
  return false  // stop propagation but log it
})

function sectionStyle(section: Section): Record<string, string> {
  const s = section.settings ?? {}
  const out: Record<string, string> = {}
  if (s.background_color) out.backgroundColor = s.background_color as string
  if (s.background_image) {
    out.backgroundImage = `url('${s.background_image}')`
    out.backgroundSize  = 'cover'
    out.backgroundPosition = 'center'
  }
  if (s.padding_top !== undefined)    out.paddingTop    = `${s.padding_top}px`
  if (s.padding_bottom !== undefined) out.paddingBottom = `${s.padding_bottom}px`
  return out
}

function gridStyle(columns: Column[]): Record<string, string> {
  const fr = columns.map(c => `${c.width ?? 6}fr`).join(' ')
  return { display: 'grid', gridTemplateColumns: fr, gap: '24px' }
}

const sections = () => ((props.layout as Layout)?.sections ?? [])
</script>

<template>
  <div class="uie-renderer">
    <section
      v-for="section in sections()"
      :key="section.id"
      class="uie-section"
      :style="sectionStyle(section)"
    >
      <div
        :class="['uie-section-inner', section.settings?.full_width ? 'w-full' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8']"
        :style="gridStyle(section.columns)"
      >
        <div v-for="col in section.columns" :key="col.id" class="uie-col">
          <div
            v-for="block in col.blocks"
            :key="block.id"
            class="uie-block"
          >
            <component
              :is="blockComponent(block.type)"
              :settings="block.settings"
              :editor-mode="editorMode ?? false"
            />
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.uie-renderer  { width: 100%; }
.uie-section   { width: 100%; }
.uie-section-inner { width: 100%; }
.uie-col       { min-width: 0; }
.uie-block     { margin-bottom: 0; }
</style>

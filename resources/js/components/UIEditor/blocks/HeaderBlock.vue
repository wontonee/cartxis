<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

interface MenuItem {
  id: number
  title: string
  url: string | null
  route: string | null
  children: MenuItem[]
}

const props = defineProps<{ settings: Record<string, unknown>; editorMode?: boolean }>()

const menuItems = ref<MenuItem[]>([])
const loading   = ref(false)
const openDrop  = ref<number | null>(null)

// ── Computed settings ─────────────────────────────────────────────
const logoType   = computed(() => (props.settings.logo_type  as string) || 'text')
const logoText   = computed(() => (props.settings.logo_text  as string) || 'My Store')
const logoImage  = computed(() => props.settings.logo_image  as string | null)
const logoUrl    = computed(() => (props.settings.logo_url   as string) || '/')
const bgColor    = computed(() => (props.settings.background_color as string) || '#ffffff')
const textColor  = computed(() => (props.settings.text_color as string) || '#111827')
const accent     = computed(() => (props.settings.accent_color as string) || '#2563eb')
const showSearch = computed(() => props.settings.show_search  !== false)
const showCart   = computed(() => props.settings.show_cart    !== false)
const showAuth   = computed(() => props.settings.show_auth_buttons !== false)
const isSticky   = computed(() => props.settings.sticky !== false)
const menuSource = computed(() => (props.settings.menu_source as string) || 'header')

function getUrl(item: MenuItem): string {
  return item.url || item.route || '#'
}

// ── Fetch menu ────────────────────────────────────────────────────
async function fetchMenu() {
  if (menuSource.value === 'none') { menuItems.value = []; return }
  loading.value = true
  try {
    const { data } = await axios.get(`/api/menus/${menuSource.value}`)
    menuItems.value = data.items || []
  } catch {
    menuItems.value = []
  } finally {
    loading.value = false
  }
}

onMounted(fetchMenu)
watch(menuSource, fetchMenu)
</script>

<template>
  <header
    :style="{
      backgroundColor: bgColor,
      color: textColor,
      borderBottom: '1px solid #e5e7eb',
      position: editorMode ? 'relative' : (isSticky ? 'sticky' : 'relative'),
      top: (!editorMode && isSticky) ? '0' : undefined,
      zIndex: editorMode ? 'auto' : '50',
      boxShadow: '0 1px 3px rgba(0,0,0,.06)',
      pointerEvents: editorMode ? 'none' : undefined,
    }"
  >
    <div style="max-width:1280px;margin:0 auto;padding:0 24px;display:flex;align-items:center;height:64px;gap:20px;">

      <!-- Logo -->
      <a :href="logoUrl" style="text-decoration:none;flex-shrink:0;display:flex;align-items:center;">
        <img
          v-if="logoType === 'image' && logoImage"
          :src="logoImage"
          alt="Logo"
          style="height:36px;width:auto;object-fit:contain;"
        />
        <span
          v-else
          :style="{ color: accent, fontWeight: '700', fontSize: '21px', letterSpacing: '-0.3px', lineHeight: '1' }"
        >{{ logoText }}</span>
      </a>

      <!-- Navigation -->
      <nav style="display:flex;align-items:center;gap:2px;flex:1;min-width:0;">
        <!-- Live menu items -->
        <template v-if="menuItems.length">
          <div
            v-for="item in menuItems"
            :key="item.id"
            style="position:relative;"
            @mouseenter="openDrop = item.id"
            @mouseleave="openDrop = null"
          >
            <a
              :href="editorMode ? undefined : getUrl(item)"
              :style="{
                color: textColor,
                textDecoration: 'none',
                padding: '8px 11px',
                borderRadius: '7px',
                display: 'flex',
                alignItems: 'center',
                gap: '4px',
                fontSize: '14.5px',
                fontWeight: '500',
                whiteSpace: 'nowrap',
              }"
            >
              {{ item.title }}
              <svg
                v-if="item.children && item.children.length"
                width="12" height="12" viewBox="0 0 24 24" fill="none"
                :stroke="textColor" stroke-width="2.5"
                style="margin-top:1px;"
              >
                <polyline points="6 9 12 15 18 9"/>
              </svg>
            </a>

            <!-- Dropdown -->
            <div
              v-if="item.children && item.children.length && openDrop === item.id"
              style="position:absolute;top:100%;left:0;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);padding:6px;min-width:160px;z-index:100;"
            >
              <a
                v-for="child in item.children"
                :key="child.id"
                :href="editorMode ? undefined : getUrl(child)"
                style="display:block;padding:7px 12px;border-radius:6px;text-decoration:none;font-size:14px;color:#374151;"
              >{{ child.title }}</a>
            </div>
          </div>
        </template>

        <!-- Loading -->
        <span v-else-if="loading" style="font-size:13px;color:#9ca3af;padding:0 8px;">Loading menu…</span>

        <!-- Empty / None -->
        <span
          v-else-if="menuSource === 'none'"
          style="font-size:12px;color:#9ca3af;font-style:italic;padding:0 8px;"
        >No navigation menu</span>

        <span
          v-else
          style="font-size:12px;color:#9ca3af;font-style:italic;padding:0 8px;"
        >No menu items — add them in Content → Navigation Menus</span>
      </nav>

      <!-- Right-side Actions -->
      <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">

        <!-- Search -->
        <div
          v-if="showSearch"
          style="display:flex;align-items:center;border:1px solid #d1d5db;border-radius:8px;padding:5px 10px;gap:6px;background:#f9fafb;cursor:text;"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <span style="font-size:13px;color:#9ca3af;white-space:nowrap;">Search…</span>
        </div>

        <!-- Cart -->
        <a
          v-if="showCart"
          :href="editorMode ? undefined : '/cart'"
          :style="{ color: textColor, textDecoration: 'none', display: 'flex', position: 'relative' }"
        >
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="textColor" stroke-width="1.8">
            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
          </svg>
        </a>

        <!-- Auth Buttons -->
        <template v-if="showAuth">
          <a
            :href="editorMode ? undefined : '/login'"
            :style="{
              backgroundColor: accent,
              color: '#fff',
              padding: '7px 16px',
              borderRadius: '8px',
              textDecoration: 'none',
              fontSize: '13.5px',
              fontWeight: '500',
              whiteSpace: 'nowrap',
            }"
          >Login</a>
          <a
            :href="editorMode ? undefined : '/register'"
            :style="{
              border: `1.5px solid ${accent}`,
              color: accent,
              padding: '6px 15px',
              borderRadius: '8px',
              textDecoration: 'none',
              fontSize: '13.5px',
              fontWeight: '500',
              background: 'transparent',
              whiteSpace: 'nowrap',
            }"
          >Register</a>
        </template>
      </div>

      <!-- Editor badge -->
      <div
        v-if="editorMode"
        style="position:absolute;top:4px;right:6px;font-size:10px;font-weight:700;background:#dbeafe;color:#1d4ed8;border-radius:4px;padding:2px 6px;pointer-events:none;letter-spacing:.5px;"
      >HEADER</div>
    </div>
  </header>
</template>

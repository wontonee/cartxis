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

// ── Computed settings ─────────────────────────────────────────────
const bgColor       = computed(() => (props.settings.background_color as string) || '#111827')
const textColor     = computed(() => (props.settings.text_color        as string) || '#9ca3af')
const headingColor  = computed(() => (props.settings.heading_color     as string) || '#ffffff')
const accentColor   = computed(() => (props.settings.accent_color      as string) || '#3b82f6')
const borderColor   = computed(() => (props.settings.border_color      as string) || '#1f2937')

const logoType    = computed(() => (props.settings.logo_type   as string) || 'text')
const logoText    = computed(() => (props.settings.logo_text   as string) || 'My Store')
const logoImage   = computed(() => props.settings.logo_image   as string | null)
const logoUrl     = computed(() => (props.settings.logo_url    as string) || '/')
const tagline     = computed(() => (props.settings.tagline     as string) || '')
const copyright   = computed(() => (props.settings.copyright   as string) || '')
const menuSource  = computed(() => (props.settings.menu_source as string) || 'footer')
const showSocial  = computed(() => props.settings.show_social  !== false)
const showPayment = computed(() => props.settings.show_payment_icons !== false)

const socialLinks = computed(() => (props.settings.social_links as Record<string, string>) || {
  facebook: '', twitter: '', instagram: '', youtube: '', linkedin: ''
})

const currentYear = new Date().getFullYear()
const copyrightText = computed(() => {
  const c = copyright.value || `© ${currentYear} ${logoText.value}. All rights reserved.`
  return c.replace('{year}', String(currentYear)).replace('{store}', logoText.value)
})

// ── Sections from footer menu ──────────────────────────────────────
const footerSections = computed(() =>
  menuItems.value.filter(item => item.children && item.children.length > 0)
)

// Columns: brand col + up to 3 menu section cols
const totalCols = computed(() => 1 + Math.min(footerSections.value.length, 3))
const gridTpl   = computed(() => {
  if (totalCols.value === 4) return '2fr 1fr 1fr 1fr'
  if (totalCols.value === 3) return '2fr 1fr 1fr'
  if (totalCols.value === 2) return '2fr 1fr'
  return '1fr'
})

function getUrl(item: MenuItem): string {
  return item.url || item.route || '#'
}

// ── Fetch menu ─────────────────────────────────────────────────────
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

// Social icons SVG paths
const socialIcons: Record<string, string> = {
  facebook:  'M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z',
  twitter:   'M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z',
  instagram: 'M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z M17.5 6.5h.01 M7.55 3.55A7 7 0 0 0 3 9.5v5a7 7 0 0 0 4.55 5.95A7 7 0 0 0 12 21a7 7 0 0 0 4.45-1.55A7 7 0 0 0 21 14.5v-5A7 7 0 0 0 17.5 3.55 7 7 0 0 0 12 3a7 7 0 0 0-4.45 1.55z',
  youtube:   'M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58A2.78 2.78 0 0 0 3.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.96A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z M9.75 15.02l5.75-3.02-5.75-3.02v6.04z',
  linkedin:  'M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z M2 9h4v12H2z M4 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4z',
}

const activeSocials = computed(() =>
  Object.entries(socialLinks.value).filter(([, url]) => url && url.trim())
)
</script>

<template>
  <footer :style="{ backgroundColor: bgColor, color: textColor, position: 'relative', pointerEvents: editorMode ? 'none' : undefined }">

    <!-- EDITOR BADGE -->
    <div
      v-if="editorMode"
      style="position:absolute;top:6px;right:8px;font-size:10px;font-weight:700;background:#fef3c7;color:#d97706;border-radius:4px;padding:2px 7px;pointer-events:none;letter-spacing:.5px;z-index:10;"
    >FOOTER</div>

    <!-- MAIN GRID -->
    <div
      style="max-width:1280px;margin:0 auto;padding:56px 24px 40px;"
      :style="{ display: 'grid', gridTemplateColumns: gridTpl, gap: '40px', alignItems: 'start' }"
    >
      <!-- Brand column -->
      <div>
        <!-- Logo -->
        <a :href="editorMode ? undefined : logoUrl" style="text-decoration:none;display:inline-block;margin-bottom:12px;">
          <img
            v-if="logoType === 'image' && logoImage"
            :src="logoImage"
            alt="Logo"
            style="height:36px;width:auto;object-fit:contain;"
          />
          <span
            v-else
            :style="{ color: headingColor, fontWeight: '700', fontSize: '22px', letterSpacing: '-0.3px' }"
          >{{ logoText }}</span>
        </a>

        <!-- Tagline -->
        <p
          v-if="tagline"
          style="font-size:14px;line-height:1.6;margin-bottom:20px;max-width:260px;"
        >{{ tagline }}</p>

        <!-- Social icons -->
        <div v-if="showSocial && activeSocials.length" style="display:flex;gap:10px;flex-wrap:wrap;">
          <a
            v-for="[platform, url] in activeSocials"
            :key="platform"
            :href="editorMode ? undefined : url"
            :title="platform"
            :style="{
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              width: '34px', height: '34px', borderRadius: '8px',
              background: '#1f2937', color: textColor, textDecoration: 'none',
              transition: 'background .2s',
            }"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" :stroke="textColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path :d="socialIcons[platform] || ''"/>
            </svg>
          </a>
        </div>

        <!-- Social placeholder (editor only, no links configured) -->
        <div
          v-else-if="showSocial && editorMode"
          style="display:flex;gap:8px;"
        >
          <div
            v-for="p in ['facebook','twitter','instagram']"
            :key="p"
            style="width:34px;height:34px;border-radius:8px;background:#1f2937;display:flex;align-items:center;justify-content:center;opacity:.4;"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Footer menu sections (up to 3 groups) -->
      <template v-if="!loading && footerSections.length">
        <div
          v-for="section in footerSections.slice(0, 3)"
          :key="section.id"
        >
          <h4 :style="{ color: headingColor, fontWeight: '700', fontSize: '13px', textTransform: 'uppercase', letterSpacing: '1px', marginBottom: '16px' }">
            {{ section.title }}
          </h4>
          <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px;">
            <li v-for="child in section.children" :key="child.id">
              <a
                :href="editorMode ? undefined : getUrl(child)"
                :style="{ color: textColor, textDecoration: 'none', fontSize: '14px' }"
              >{{ child.title }}</a>
            </li>
          </ul>
        </div>
      </template>

      <!-- Loading placeholder -->
      <template v-else-if="loading">
        <div v-for="i in 3" :key="i">
          <div style="height:12px;width:80px;background:#1f2937;border-radius:4px;margin-bottom:16px;"/>
          <div v-for="j in 4" :key="j" style="height:10px;width:120px;background:#1f2937;border-radius:3px;margin-bottom:10px;opacity:.6;"/>
        </div>
      </template>

      <!-- Empty: editor hint -->
      <template v-else-if="editorMode && menuSource !== 'none'">
        <div style="grid-column:2/-1;padding:20px;border:1px dashed #374151;border-radius:8px;text-align:center;">
          <p style="font-size:13px;color:#6b7280;">No footer menu sections found. Add parent items with children in
            <strong style="color:#9ca3af;">Content → Navigation Menus → Footer Menu</strong>
          </p>
        </div>
      </template>
    </div>

    <!-- BOTTOM BAR -->
    <div :style="{ borderTop: `1px solid ${borderColor}`, padding: '18px 24px' }">
      <div style="max-width:1280px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">

        <!-- Copyright -->
        <p style="font-size:13px;margin:0;">{{ copyrightText }}</p>

        <!-- Payment icons -->
        <div v-if="showPayment" style="display:flex;align-items:center;gap:6px;">
          <!-- Simple card payment icon badges -->
          <span
            v-for="card in ['VISA', 'MC', 'AMEX', 'UPI']"
            :key="card"
            :style="{
              display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
              padding: '3px 8px', border: `1px solid ${borderColor}`, borderRadius: '5px',
              fontSize: '10px', fontWeight: '700', color: headingColor,
              letterSpacing: '.5px', background: '#1f2937',
            }"
          >{{ card }}</span>
        </div>

        <p v-else-if="editorMode" style="font-size:12px;color:#4b5563;font-style:italic;margin:0;">
          Powered by Cartxis
        </p>
      </div>
    </div>
  </footer>
</template>

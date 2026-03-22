<script setup lang="ts">
const props = defineProps<{ settings: Record<string, unknown> }>()
const emit  = defineEmits<{ 'update:settings': [v: Record<string, unknown>] }>()

function update(key: string, value: unknown) {
  emit('update:settings', { ...props.settings, [key]: value })
}

function get(key: string, fallback: unknown): unknown {
  return key in props.settings ? props.settings[key] : fallback
}
function str(key: string, fallback: string): string {
  return String(key in props.settings ? props.settings[key] : fallback)
}
function bool(key: string, fallback: boolean): boolean {
  const v = props.settings[key]
  return v === undefined ? fallback : Boolean(v)
}
</script>

<template>
  <div class="p-4 space-y-5 text-sm">

    <!-- ── Navigation Menu ─────────────────────────────────────── -->
    <div>
      <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
        Navigation Menu
      </label>
      <select
        :value="str('menu_source', 'header')"
        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
        @change="update('menu_source', ($event.target as HTMLSelectElement).value)"
      >
        <option value="header">Header Menu (Storefront Navigation)</option>
        <option value="footer">Footer Menu</option>
        <option value="none">No menu</option>
      </select>
      <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1.5 leading-relaxed">
        Edit items in <strong class="font-medium text-gray-500 dark:text-gray-400">Content → Navigation Menus → Header Menu</strong>
      </p>
    </div>

    <!-- ── Logo ───────────────────────────────────────────────── -->
    <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
      <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Logo</p>

      <!-- Type toggle -->
      <div class="flex gap-2 mb-3">
        <button
          :class="[
            'flex-1 py-1.5 text-xs rounded-lg border transition-colors',
            str('logo_type', 'text') === 'text'
              ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-400'
          ]"
          @click="update('logo_type', 'text')"
        >Text</button>
        <button
          :class="[
            'flex-1 py-1.5 text-xs rounded-lg border transition-colors',
            str('logo_type', 'text') === 'image'
              ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
              : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-400'
          ]"
          @click="update('logo_type', 'image')"
        >Image URL</button>
      </div>

      <!-- Logo text -->
      <div v-if="str('logo_type', 'text') === 'text'" class="mb-3">
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Store Name</label>
        <input
          :value="str('logo_text', 'My Store')"
          type="text"
          class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
          @input="update('logo_text', ($event.target as HTMLInputElement).value)"
        />
      </div>

      <!-- Logo image -->
      <div v-else class="mb-3">
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Image URL</label>
        <input
          :value="str('logo_image', '')"
          type="text"
          placeholder="/images/logo.png"
          class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none font-mono"
          @input="update('logo_image', ($event.target as HTMLInputElement).value)"
        />
      </div>

      <!-- Logo link -->
      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Link URL</label>
        <input
          :value="str('logo_url', '/')"
          type="text"
          class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none font-mono"
          @input="update('logo_url', ($event.target as HTMLInputElement).value)"
        />
      </div>
    </div>

    <!-- ── Features ────────────────────────────────────────────── -->
    <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
      <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Features</p>
      <div class="space-y-2.5">

        <label class="flex items-center gap-2.5 cursor-pointer group">
          <input
            type="checkbox"
            :checked="bool('show_search', true)"
            class="w-3.5 h-3.5 rounded border-gray-300 dark:border-gray-600 text-blue-600"
            @change="update('show_search', ($event.target as HTMLInputElement).checked)"
          />
          <span class="text-xs text-gray-700 dark:text-gray-300">Search bar</span>
        </label>

        <label class="flex items-center gap-2.5 cursor-pointer">
          <input
            type="checkbox"
            :checked="bool('show_cart', true)"
            class="w-3.5 h-3.5 rounded border-gray-300 dark:border-gray-600 text-blue-600"
            @change="update('show_cart', ($event.target as HTMLInputElement).checked)"
          />
          <span class="text-xs text-gray-700 dark:text-gray-300">Cart icon</span>
        </label>

        <label class="flex items-center gap-2.5 cursor-pointer">
          <input
            type="checkbox"
            :checked="bool('show_auth_buttons', true)"
            class="w-3.5 h-3.5 rounded border-gray-300 dark:border-gray-600 text-blue-600"
            @change="update('show_auth_buttons', ($event.target as HTMLInputElement).checked)"
          />
          <span class="text-xs text-gray-700 dark:text-gray-300">Login / Register buttons</span>
        </label>

        <label class="flex items-center gap-2.5 cursor-pointer">
          <input
            type="checkbox"
            :checked="bool('sticky', true)"
            class="w-3.5 h-3.5 rounded border-gray-300 dark:border-gray-600 text-blue-600"
            @change="update('sticky', ($event.target as HTMLInputElement).checked)"
          />
          <span class="text-xs text-gray-700 dark:text-gray-300">Sticky header (fixed to top)</span>
        </label>
      </div>
    </div>

    <!-- ── Colors ──────────────────────────────────────────────── -->
    <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
      <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Colors</p>
      <div class="space-y-2.5">

        <div class="flex items-center gap-3">
          <input
            type="color"
            :value="str('background_color', '#ffffff')"
            class="w-7 h-7 rounded-md cursor-pointer border border-gray-300 dark:border-gray-600 p-0.5"
            @input="update('background_color', ($event.target as HTMLInputElement).value)"
          />
          <span class="text-xs text-gray-700 dark:text-gray-300 flex-1">Background</span>
          <code class="text-[11px] text-gray-400 dark:text-gray-500 font-mono">{{ str('background_color', '#ffffff') }}</code>
        </div>

        <div class="flex items-center gap-3">
          <input
            type="color"
            :value="str('text_color', '#111827')"
            class="w-7 h-7 rounded-md cursor-pointer border border-gray-300 dark:border-gray-600 p-0.5"
            @input="update('text_color', ($event.target as HTMLInputElement).value)"
          />
          <span class="text-xs text-gray-700 dark:text-gray-300 flex-1">Text &amp; icons</span>
          <code class="text-[11px] text-gray-400 dark:text-gray-500 font-mono">{{ str('text_color', '#111827') }}</code>
        </div>

        <div class="flex items-center gap-3">
          <input
            type="color"
            :value="str('accent_color', '#2563eb')"
            class="w-7 h-7 rounded-md cursor-pointer border border-gray-300 dark:border-gray-600 p-0.5"
            @input="update('accent_color', ($event.target as HTMLInputElement).value)"
          />
          <span class="text-xs text-gray-700 dark:text-gray-300 flex-1">Accent (logo text, buttons)</span>
          <code class="text-[11px] text-gray-400 dark:text-gray-500 font-mono">{{ str('accent_color', '#2563eb') }}</code>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import {
  Palette, Phone, Paintbrush, Layout, ToggleLeft, Save,
  CheckCircle, Info, Download, AlertTriangle, X, Camera,
  ShoppingCart, ExternalLink, LayoutTemplate, Wand2, Monitor,
  ChevronRight, Eye, Settings2, Layers,
} from 'lucide-vue-next'

interface Theme {
  id: number
  name: string
  slug: string
  description: string
  version: string
  author: string
  screenshot: string | null
  is_active: boolean
}

interface Field {
  id: string
  type: 'color' | 'select' | 'number' | 'boolean' | 'text'
  label: string
  description?: string
  default: any
  options?: Record<string, string>
  min?: number
  max?: number
  step?: number
}

interface Section {
  id: string
  title: string
  description: string
  fields: Field[]
}

interface Schema {
  sections: Section[]
}

interface Props {
  theme: Theme
  schema: Schema
  settings: Record<string, any>
  hasThemeData: boolean
  hasProductData: boolean
  hasDemoLayout: boolean
  hasPublishedLayout: boolean
}

const props = defineProps<Props>()
const page = usePage()
const processing = ref(false)
const importing = ref(false)
const importingLayout = ref(false)
const showImportDialog = ref(false)
const importFresh = ref(false)
const importProducts = ref(false)
const uploadingScreenshot = ref(false)
const screenshotInput = ref<HTMLInputElement | null>(null)

const flash = computed(() => (page.props.flash as any) ?? {})

const screenshotUrl = computed(() => {
  if (props.theme.screenshot) {
    const url = props.theme.screenshot
    const sep = url.includes('?') ? '&' : '?'
    return `${url}${sep}t=${Date.now()}`
  }
  return null
})

const triggerScreenshotUpload = () => screenshotInput.value?.click()

const uploadScreenshot = (event: Event) => {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return
  uploadingScreenshot.value = true
  const formData = new FormData()
  formData.append('screenshot', file)
  router.post(`/admin/appearance/themes/${props.theme.slug}/screenshot`, formData, {
    preserveScroll: true,
    forceFormData: true,
    onFinish: () => { uploadingScreenshot.value = false; input.value = '' },
  })
}

const importLayout = () => {
  if (importingLayout.value) return
  importingLayout.value = true
  router.post(`/admin/appearance/themes/${props.theme.slug}/import-layout`, {}, {
    preserveScroll: true,
    onFinish: () => { importingLayout.value = false },
  })
}

const importThemeData = () => {
  importing.value = true
  router.post(
    `/admin/appearance/themes/${props.theme.slug}/import-data`,
    { fresh: importFresh.value, include_products: importProducts.value },
    {
      preserveScroll: true,
      onFinish: () => {
        importing.value = false
        showImportDialog.value = false
        importFresh.value = false
        importProducts.value = false
      },
    },
  )
}

const tabDefs: Record<string, { icon: any; label: string; sections: string[] }> = {
  contact: { icon: Phone, label: 'Contact & Social', sections: ['contact', 'social'] },
  colors: { icon: Paintbrush, label: 'Colors & Typography', sections: ['colors', 'typography'] },
  layout: { icon: Layout, label: 'Layout', sections: ['layout'] },
  features: { icon: ToggleLeft, label: 'Features', sections: ['features', 'homepage'] },
}

const tabs = computed(() =>
  Object.entries(tabDefs)
    .filter(([_, def]) => def.sections.some(sid => props.schema.sections.find(s => s.id === sid)))
    .map(([id, def]) => ({ id, ...def }))
)

const activeTab = ref(tabs.value[0]?.id ?? 'contact')

const sectionsForTab = computed(() => {
  const def = tabDefs[activeTab.value]
  if (!def) return []
  return def.sections
    .map(sid => props.schema.sections.find(s => s.id === sid))
    .filter(Boolean) as Section[]
})

const form = reactive<Record<string, any>>({})
props.schema.sections.forEach(section => {
  section.fields.forEach(field => {
    const key = `${section.id}.${field.id}`
    form[key] = props.settings[key] ?? field.default
  })
})

const saveSettings = () => {
  processing.value = true
  router.put(`/admin/appearance/themes/${props.theme.slug}/settings`, { settings: form }, {
    preserveScroll: true,
    onFinish: () => { processing.value = false },
  })
}
</script>

<template>
  <AdminLayout title="Appearance">
    <Head title="Appearance" />

    <div class="space-y-5">

      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Appearance</h1>
          <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
            Customize your store's look, feel and homepage layout
          </p>
        </div>
        <div class="flex items-center gap-2.5">
          <a
            href="/"
            target="_blank"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            <Eye class="w-4 h-4" />
            Preview
          </a>
          <button
            @click="saveSettings"
            :disabled="processing"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 rounded-xl text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <Save v-if="!processing" class="w-4 h-4" />
            <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            {{ processing ? 'Saving\u2026' : 'Save Changes' }}
          </button>
        </div>
      </div>

      <!-- Flash Messages -->
      <transition name="slide-down">
        <div v-if="flash.success" class="flex items-center gap-3 px-4 py-3 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/30">
          <CheckCircle class="w-4 h-4 text-green-500 flex-shrink-0" />
          <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ flash.success }}</p>
        </div>
      </transition>
      <transition name="slide-down">
        <div v-if="flash.error" class="flex items-center gap-3 px-4 py-3 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/30">
          <Info class="w-4 h-4 text-red-500 flex-shrink-0" />
          <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ flash.error }}</p>
        </div>
      </transition>

      <!-- Active Theme Card -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="flex flex-col sm:flex-row">
          <!-- Screenshot -->
          <div
            class="relative sm:w-52 lg:w-60 flex-shrink-0 cursor-pointer group overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900/60 dark:to-gray-800/60"
            style="min-height: 168px"
            @click="triggerScreenshotUpload"
            title="Click to upload a screenshot"
          >
            <img
              v-if="screenshotUrl"
              :src="screenshotUrl"
              :alt="theme.name"
              class="absolute inset-0 w-full h-full object-cover object-top transition-transform duration-700 group-hover:scale-105"
            />
            <div v-else class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-center bg-gradient-to-br from-blue-600 to-indigo-700">
              <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                <Monitor class="w-6 h-6 text-white/80" />
              </div>
              <p class="text-xs text-white/80 font-medium">{{ theme.name }}</p>
            </div>
            <div class="absolute inset-0 bg-black/50 flex flex-col items-center justify-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
              <Camera v-if="!uploadingScreenshot" class="w-5 h-5 text-white" />
              <svg v-else class="w-5 h-5 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              <span class="text-[11px] text-white font-medium">Upload screenshot</span>
            </div>
          </div>
          <input ref="screenshotInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="uploadScreenshot" />

          <!-- Details -->
          <div class="flex-1 p-5 sm:p-6 flex flex-col gap-4">
            <div>
              <div class="flex flex-wrap items-center gap-2 mb-1.5">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-snug">{{ theme.name }}</h3>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400 ring-1 ring-inset ring-green-600/20">
                  <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                  Active
                </span>
              </div>
              <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ theme.description }}</p>
              <div class="flex items-center gap-3 mt-2 text-xs text-gray-400 dark:text-gray-500">
                <span>v{{ theme.version }}</span>
                <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                <span>by {{ theme.author }}</span>
              </div>
            </div>

            <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
              <a
                href="/admin/appearance/themes"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              >
                <Settings2 class="w-3.5 h-3.5" />
                Manage Themes
              </a>
              <a
                href="/"
                target="_blank"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              >
                <ExternalLink class="w-3.5 h-3.5" />
                Preview Storefront
              </a>
              <button
                v-if="hasThemeData"
                @click="showImportDialog = true"
                type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              >
                <Download class="w-3.5 h-3.5" />
                Import Demo Data
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Homepage Layout Status -->
      <div v-if="hasDemoLayout">
        <!-- Published -->
        <div
          v-if="hasPublishedLayout"
          class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-5 py-4 rounded-2xl border border-green-200 dark:border-green-800/60 bg-green-50 dark:bg-green-900/20"
        >
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-green-100 dark:bg-green-900/50 flex items-center justify-center flex-shrink-0">
              <CheckCircle class="w-5 h-5 text-green-600 dark:text-green-400" />
            </div>
            <div>
              <p class="text-sm font-semibold text-green-900 dark:text-green-200">Homepage Layout is Live</p>
              <p class="text-xs text-green-700 dark:text-green-400 mt-0.5">
                Your homepage is rendering a published layout. Open the Page Builder to edit it.
              </p>
            </div>
          </div>
          <a
            href="/admin/content/pages"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-green-700 dark:text-green-300 bg-white dark:bg-green-950/40 border border-green-300 dark:border-green-700 rounded-xl hover:bg-green-50 dark:hover:bg-green-900/40 transition-colors flex-shrink-0"
          >
            <Wand2 class="w-4 h-4" />
            Open Page Builder
            <ChevronRight class="w-3.5 h-3.5 opacity-60" />
          </a>
        </div>

        <!-- Not published -->
        <div
          v-else
          class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-5 py-4 rounded-2xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-900/20"
        >
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center flex-shrink-0">
              <LayoutTemplate class="w-5 h-5 text-amber-600 dark:text-amber-400" />
            </div>
            <div>
              <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">No Homepage Layout</p>
              <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                Your homepage has no published layout. Import the demo layout to get started instantly.
              </p>
            </div>
          </div>
          <button
            @click="importLayout"
            :disabled="importingLayout"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 dark:focus:ring-offset-gray-900 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0 shadow-sm"
          >
            <svg v-if="importingLayout" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            <Layers v-else class="w-4 h-4" />
            {{ importingLayout ? 'Importing\u2026' : 'Import Demo Layout' }}
          </button>
        </div>
      </div>

      <!-- Settings Tabs -->
      <form @submit.prevent="saveSettings" novalidate>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
          <!-- Tab nav -->
          <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex overflow-x-auto scrollbar-none px-4 sm:px-6" aria-label="Settings tabs">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                type="button"
                @click="activeTab = tab.id"
                :class="[
                  activeTab === tab.id
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                  'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors whitespace-nowrap mr-7 last:mr-0'
                ]"
              >
                <component
                  :is="tab.icon"
                  class="w-4 h-4 mr-2 flex-shrink-0 transition-colors"
                  :class="activeTab === tab.id ? 'text-blue-500 dark:text-blue-400' : 'text-gray-400 group-hover:text-gray-500'"
                />
                {{ tab.label }}
              </button>
            </nav>
          </div>

          <!-- Tab panels -->
          <div class="p-6 sm:p-8">
            <div v-if="tabs.length === 0 || sectionsForTab.length === 0" class="py-16 text-center">
              <Palette class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
              <p class="text-sm text-gray-400 dark:text-gray-500">No settings available for this tab.</p>
            </div>
            <div v-else class="space-y-10">
              <template v-for="(section, si) in sectionsForTab" :key="section.id">
                <div>
                  <div class="mb-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">{{ section.title }}</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ section.description }}</p>
                  </div>
                  <div class="space-y-5">
                    <div
                      v-for="field in section.fields"
                      :key="field.id"
                      class="grid grid-cols-1 sm:grid-cols-12 gap-x-8 gap-y-1.5 items-start"
                    >
                      <div class="sm:col-span-4 sm:pt-2.5">
                        <label :for="`${section.id}-${field.id}`" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                          {{ field.label }}
                        </label>
                        <p v-if="field.description" class="mt-0.5 text-xs text-gray-400 dark:text-gray-500 leading-relaxed">
                          {{ field.description }}
                        </p>
                      </div>
                      <div class="sm:col-span-8">
                        <!-- Color -->
                        <div v-if="field.type === 'color'" class="flex items-center gap-2.5">
                          <input
                            :id="`${section.id}-${field.id}`"
                            v-model="form[`${section.id}.${field.id}`]"
                            type="color"
                            class="w-11 h-10 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer bg-white dark:bg-gray-700 p-0.5 shadow-sm"
                          />
                          <input
                            v-model="form[`${section.id}.${field.id}`]"
                            type="text"
                            class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono uppercase focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="#000000"
                          />
                        </div>
                        <!-- Select -->
                        <select
                          v-else-if="field.type === 'select'"
                          :id="`${section.id}-${field.id}`"
                          v-model="form[`${section.id}.${field.id}`]"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                          <option v-for="(label, value) in field.options" :key="value" :value="value">{{ label }}</option>
                        </select>
                        <!-- Number -->
                        <div v-else-if="field.type === 'number'" class="flex items-center gap-2.5">
                          <input
                            :id="`${section.id}-${field.id}`"
                            v-model.number="form[`${section.id}.${field.id}`]"
                            type="number"
                            :min="field.min"
                            :max="field.max"
                            :step="field.step ?? 1"
                            class="w-28 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          />
                          <span v-if="field.min !== undefined || field.max !== undefined" class="text-xs text-gray-400">
                            {{ field.min ?? '\u2013' }} \u2013 {{ field.max ?? '\u221e' }}
                          </span>
                        </div>
                        <!-- Boolean toggle -->
                        <div v-else-if="field.type === 'boolean'" class="flex items-center gap-3 pt-1.5">
                          <button
                            type="button"
                            @click="form[`${section.id}.${field.id}`] = !form[`${section.id}.${field.id}`]"
                            :class="[
                              form[`${section.id}.${field.id}`] ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-600',
                              'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800'
                            ]"
                            role="switch"
                            :aria-checked="form[`${section.id}.${field.id}`]"
                          >
                            <span
                              :class="[
                                form[`${section.id}.${field.id}`] ? 'translate-x-5' : 'translate-x-0',
                                'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200'
                              ]"
                            />
                          </button>
                          <span class="text-sm text-gray-500 dark:text-gray-400 select-none">
                            {{ form[`${section.id}.${field.id}`] ? 'Enabled' : 'Disabled' }}
                          </span>
                        </div>
                        <!-- Text -->
                        <input
                          v-else
                          :id="`${section.id}-${field.id}`"
                          v-model="form[`${section.id}.${field.id}`]"
                          type="text"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                      </div>
                    </div>
                  </div>
                </div>
                <div v-if="si < sectionsForTab.length - 1" class="border-t border-gray-100 dark:border-gray-700/60" />
              </template>
            </div>
          </div>

          <!-- Save bar -->
          <div class="px-6 py-4 bg-gray-50/80 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-4">
            <p class="text-xs text-gray-400 dark:text-gray-500 hidden sm:block">
              Settings are saved per-theme and won't affect other themes.
            </p>
            <button
              type="submit"
              :disabled="processing"
              class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 rounded-lg text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <Save v-if="!processing" class="w-4 h-4" />
              <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              {{ processing ? 'Saving\u2026' : 'Save Changes' }}
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Import Demo Data Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showImportDialog" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showImportDialog = false" />
          <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                  <Download class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Import Theme Data</h3>
              </div>
              <button @click="showImportDialog = false" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg transition-colors">
                <X class="w-5 h-5" />
              </button>
            </div>
            <div class="px-6 py-5 space-y-3.5">
              <p class="text-sm text-gray-600 dark:text-gray-400">Import demo content from this theme's data file:</p>
              <ul class="space-y-1.5 text-sm text-gray-500 dark:text-gray-400">
                <li v-for="item in ['CMS Blocks (banners, sliders, hero sections)', 'Navigation Menus (header & footer)', 'Theme Settings (colors, layout, features)']" :key="item" class="flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-blue-400 flex-shrink-0" />
                  {{ item }}
                </li>
              </ul>
              <label class="flex items-start gap-3 p-3.5 rounded-xl cursor-pointer border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100/70 dark:hover:bg-amber-900/30 transition-colors">
                <input v-model="importFresh" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                <div>
                  <p class="text-sm font-medium text-amber-800 dark:text-amber-300">Fresh Import</p>
                  <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">Remove existing blocks, menus &amp; settings before importing.</p>
                </div>
              </label>
              <label
                v-if="hasProductData"
                class="flex items-start gap-3 p-3.5 rounded-xl cursor-pointer border transition-colors"
                :class="importProducts
                  ? 'border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-900/20'
                  : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20 hover:bg-gray-100/60 dark:hover:bg-gray-900/40'"
              >
                <input v-model="importProducts" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500" />
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium" :class="importProducts ? 'text-red-800 dark:text-red-300' : 'text-gray-700 dark:text-gray-300'">
                    <ShoppingCart class="w-3.5 h-3.5 inline -mt-0.5 mr-1" />
                    Include Products &amp; Categories
                  </p>
                  <p class="text-xs mt-0.5" :class="importProducts ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400'">
                    Import demo products from the theme data.
                  </p>
                  <div v-if="importProducts" class="mt-2.5 flex items-start gap-2 p-2.5 rounded-lg bg-red-100 dark:bg-red-900/40 border border-red-200 dark:border-red-700/50">
                    <AlertTriangle class="w-3.5 h-3.5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                    <p class="text-xs font-medium text-red-700 dark:text-red-300">
                      This will <strong>delete all existing products and categories</strong>. Cannot be undone.
                    </p>
                  </div>
                </div>
              </label>
            </div>
            <div class="flex items-center justify-end gap-2.5 px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
              <button @click="showImportDialog = false" :disabled="importing" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50">
                Cancel
              </button>
              <button
                @click="importThemeData"
                :disabled="importing"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors disabled:opacity-50"
                :class="importProducts ? 'bg-red-600 hover:bg-red-700' : importFresh ? 'bg-amber-600 hover:bg-amber-700' : 'bg-blue-600 hover:bg-blue-700'"
              >
                <svg v-if="importing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <Download v-else class="w-4 h-4" />
                {{ importing ? 'Importing\u2026' : (importProducts ? 'Import with Products' : importFresh ? 'Fresh Import' : 'Import Data') }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </AdminLayout>
</template>

<style scoped>
.scrollbar-none { scrollbar-width: none; }
.scrollbar-none::-webkit-scrollbar { display: none; }
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.2s ease; }
.slide-down-enter-from { opacity: 0; transform: translateY(-6px); }
.slide-down-leave-to { opacity: 0; transform: translateY(-4px); }
.modal-enter-active { transition: opacity 0.2s ease; }
.modal-enter-active > div:last-child { transition: transform 0.2s cubic-bezier(0.34,1.56,0.64,1), opacity 0.2s ease; }
.modal-leave-active { transition: opacity 0.15s ease; }
.modal-leave-active > div:last-child { transition: transform 0.15s ease, opacity 0.15s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from > div:last-child, .modal-leave-to > div:last-child { transform: scale(0.95); opacity: 0; }
</style>

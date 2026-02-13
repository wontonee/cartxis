<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import {
  Palette,
  Phone,
  Share2,
  Paintbrush,
  Layout,
  ToggleLeft,
  Home,
  Type,
  Save,
  CheckCircle,
  Info,
  Download,
  AlertTriangle,
  X,
  Upload,
  Camera,
  ShoppingCart,
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
}

const props = defineProps<Props>()
const page = usePage()
const processing = ref(false)
const importing = ref(false)
const showImportDialog = ref(false)
const importFresh = ref(false)
const importProducts = ref(false)
const uploadingScreenshot = ref(false)
const screenshotInput = ref<HTMLInputElement | null>(null)

const screenshotUrl = computed(() => {
  if (props.theme.screenshot) {
    // Controller already passes full asset URL via $theme->asset()
    const url = props.theme.screenshot
    const separator = url.includes('?') ? '&' : '?'
    return `${url}${separator}t=${Date.now()}`
  }
  return null
})

const triggerScreenshotUpload = () => {
  screenshotInput.value?.click()
}

const uploadScreenshot = (event: Event) => {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return
  uploadingScreenshot.value = true
  const formData = new FormData()
  formData.append('screenshot', file)
  router.post(
    `/admin/appearance/themes/${props.theme.slug}/screenshot`,
    formData,
    {
      preserveScroll: true,
      forceFormData: true,
      onFinish: () => {
        uploadingScreenshot.value = false
        input.value = ''
      },
    },
  )
}

// Map schema sections to tab definitions
const tabMap: Record<string, { icon: any; label: string; sections: string[] }> = {
  contact: { icon: Phone, label: 'Contact & Social', sections: ['contact', 'social'] },
  colors: { icon: Paintbrush, label: 'Colors & Typography', sections: ['colors', 'typography'] },
  layout: { icon: Layout, label: 'Layout', sections: ['layout'] },
  features: { icon: ToggleLeft, label: 'Features', sections: ['features', 'homepage'] },
}

const tabs = computed(() => {
  return Object.entries(tabMap)
    .filter(([_, def]) => def.sections.some(sid => props.schema.sections.find(s => s.id === sid)))
    .map(([id, def]) => ({ id, ...def }))
})

const activeTab = ref(tabs.value.length > 0 ? tabs.value[0].id : 'contact')

const sectionsForTab = computed(() => {
  const def = tabMap[activeTab.value]
  if (!def) return []
  return def.sections
    .map(sid => props.schema.sections.find(s => s.id === sid))
    .filter(Boolean) as Section[]
})

// Initialize form with current settings or defaults
const form = reactive<Record<string, any>>({})
props.schema.sections.forEach((section) => {
  section.fields.forEach((field) => {
    const key = `${section.id}.${field.id}`
    form[key] = props.settings[key] ?? field.default
  })
})

const saveSettings = () => {
  processing.value = true
  router.put(
    `/admin/appearance/themes/${props.theme.slug}/settings`,
    { settings: form },
    {
      preserveScroll: true,
      onFinish: () => { processing.value = false },
    },
  )
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
</script>

<template>
  <AdminLayout title="Appearance">
    <Head title="Appearance" />

    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Appearance</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Customize the look and feel of your storefront
          </p>
        </div>
        <button
          @click="saveSettings"
          :disabled="processing"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-xl text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-900 shadow-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <Save v-if="!processing" class="w-4 h-4" />
          <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
          </svg>
          {{ processing ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>

      <!-- Flash Messages -->
      <div v-if="(page.props.flash as any)?.success" class="flex items-center gap-3 p-4 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/30">
        <CheckCircle class="w-5 h-5 text-green-500 flex-shrink-0" />
        <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ (page.props.flash as any).success }}</p>
      </div>
      <div v-if="(page.props.flash as any)?.error" class="flex items-center gap-3 p-4 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/30">
        <Info class="w-5 h-5 text-red-500 flex-shrink-0" />
        <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ (page.props.flash as any).error }}</p>
      </div>

      <!-- Active Theme Card -->
      <div class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <!-- Screenshot Upload Thumbnail -->
        <div
          class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 relative group cursor-pointer"
          @click="triggerScreenshotUpload"
          title="Click to upload theme screenshot"
        >
          <img
            v-if="screenshotUrl"
            :src="screenshotUrl"
            :alt="theme.name"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <Palette class="w-7 h-7 text-gray-400 dark:text-gray-500" />
          </div>
          <!-- Upload Overlay -->
          <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
            <Camera v-if="!uploadingScreenshot" class="w-5 h-5 text-white" />
            <svg v-else class="w-5 h-5 text-white animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
          </div>
        </div>
        <input
          ref="screenshotInput"
          type="file"
          accept="image/jpeg,image/png,image/jpg,image/webp"
          class="hidden"
          @change="uploadScreenshot"
        />
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ theme.name }}</h3>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400">
              Active
            </span>
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
            v{{ theme.version }} · by {{ theme.author }}
          </p>
        </div>
        <button
          v-if="hasThemeData"
          @click="showImportDialog = true"
          type="button"
          class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-colors duration-200"
        >
          <Download class="w-4 h-4" />
          Import Demo Data
        </button>
      </div>

      <!-- Import Theme Data Modal -->
      <Teleport to="body">
        <Transition name="modal">
          <div v-if="showImportDialog" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 dark:bg-black/70" @click="showImportDialog = false" />
            <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700">
              <!-- Modal Header -->
              <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                  <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30">
                    <AlertTriangle class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                  </div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Import Theme Data</h3>
                </div>
                <button @click="showImportDialog = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                  <X class="w-5 h-5" />
                </button>
              </div>

              <!-- Modal Body -->
              <div class="p-5 space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  This will import demo content from the theme's data file, including:
                </p>
                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1.5 ml-1">
                  <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 flex-shrink-0" />
                    CMS Blocks (banners, sliders, content sections)
                  </li>
                  <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 flex-shrink-0" />
                    Navigation Menus (header &amp; footer)
                  </li>
                  <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 flex-shrink-0" />
                    Theme Settings (colors, layout, features)
                  </li>
                </ul>

                <!-- Fresh Import Toggle -->
                <div class="flex items-start gap-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                  <div class="flex items-center h-5 mt-0.5">
                    <input
                      id="import-fresh"
                      v-model="importFresh"
                      type="checkbox"
                      class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-amber-600 focus:ring-amber-500 dark:bg-gray-700"
                    />
                  </div>
                  <div>
                    <label for="import-fresh" class="text-sm font-medium text-amber-800 dark:text-amber-300 cursor-pointer">Fresh Import</label>
                    <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                      Remove existing blocks, menus &amp; settings before importing. Use this to reset to the theme's default state.
                    </p>
                  </div>
                </div>

                <!-- Products & Categories Import Toggle -->
                <div v-if="hasProductData" class="flex items-start gap-3 p-3 rounded-lg" :class="importProducts ? 'bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-800' : 'bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700'">
                  <div class="flex items-center h-5 mt-0.5">
                    <input
                      id="import-products"
                      v-model="importProducts"
                      type="checkbox"
                      class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500 dark:bg-gray-700"
                    />
                  </div>
                  <div>
                    <label for="import-products" class="text-sm font-medium cursor-pointer" :class="importProducts ? 'text-red-800 dark:text-red-300' : 'text-gray-700 dark:text-gray-300'">
                      <ShoppingCart class="w-3.5 h-3.5 inline -mt-0.5 mr-1" />
                      Import Products &amp; Categories
                    </label>
                    <p class="text-xs mt-0.5" :class="importProducts ? 'text-red-700 dark:text-red-400' : 'text-gray-500 dark:text-gray-400'">
                      Import demo products and categories from the theme data.
                    </p>
                    <div v-if="importProducts" class="mt-2 flex items-start gap-2 p-2 rounded-md bg-red-100 dark:bg-red-900/40 border border-red-200 dark:border-red-700">
                      <AlertTriangle class="w-4 h-4 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                      <p class="text-xs font-medium text-red-700 dark:text-red-300">
                        This will <strong>DELETE all existing products and categories</strong> and replace them with theme defaults. This action cannot be undone.
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Modal Footer -->
              <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-b-xl">
                <button
                  @click="showImportDialog = false"
                  type="button"
                  :disabled="importing"
                  class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:focus:ring-offset-gray-800 transition-colors duration-200 disabled:opacity-50"
                >
                  Cancel
                </button>
                <button
                  @click="importThemeData"
                  type="button"
                  :disabled="importing"
                  class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                  :class="importProducts ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : (importFresh ? 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500' : 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500')"
                >
                  <svg v-if="importing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                  </svg>
                  <Download v-else class="w-4 h-4" />
                  {{ importing ? 'Importing...' : (importProducts ? 'Import with Products' : (importFresh ? 'Fresh Import' : 'Import Data')) }}
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>

      <!-- Settings Card with Tabs -->
      <form @submit.prevent="saveSettings" novalidate>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
          <!-- Tabs Navigation -->
          <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
            <nav class="flex space-x-6 px-6 overflow-x-auto" aria-label="Tabs">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                type="button"
                @click="activeTab = tab.id"
                :class="[
                  activeTab === tab.id
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                  'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap'
                ]"
              >
                <component
                  :is="tab.icon"
                  class="w-4 h-4 mr-2"
                  :class="activeTab === tab.id ? 'text-blue-500 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400'"
                />
                {{ tab.label }}
              </button>
            </nav>
          </div>

          <!-- Tab Panels -->
          <div class="p-6 sm:p-8">
            <TransitionGroup name="fade" tag="div">
              <div
                v-for="section in sectionsForTab"
                :key="section.id"
                class="mb-8 last:mb-0"
              >
                <div class="mb-5">
                  <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ section.title }}</h3>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ section.description }}</p>
                </div>

                <div class="space-y-5">
                  <div
                    v-for="field in section.fields"
                    :key="field.id"
                    class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 items-start"
                  >
                    <!-- Label -->
                    <div class="sm:pt-2">
                      <label
                        :for="`${section.id}-${field.id}`"
                        class="text-sm font-medium text-gray-700 dark:text-gray-300"
                      >
                        {{ field.label }}
                      </label>
                      <p v-if="field.description" class="mt-0.5 text-xs text-gray-500 dark:text-gray-500">
                        {{ field.description }}
                      </p>
                    </div>

                    <!-- Input -->
                    <div class="sm:col-span-2">
                      <!-- Color -->
                      <div v-if="field.type === 'color'" class="flex items-center gap-3">
                        <input
                          :id="`${section.id}-${field.id}`"
                          v-model="form[`${section.id}.${field.id}`]"
                          type="color"
                          class="h-10 w-14 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer bg-white dark:bg-gray-700 p-0.5"
                        />
                        <input
                          v-model="form[`${section.id}.${field.id}`]"
                          type="text"
                          class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="#000000"
                        />
                      </div>

                      <!-- Select -->
                      <select
                        v-else-if="field.type === 'select'"
                        :id="`${section.id}-${field.id}`"
                        v-model="form[`${section.id}.${field.id}`]"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      >
                        <option
                          v-for="(label, value) in field.options"
                          :key="value"
                          :value="value"
                        >
                          {{ label }}
                        </option>
                      </select>

                      <!-- Number -->
                      <input
                        v-else-if="field.type === 'number'"
                        :id="`${section.id}-${field.id}`"
                        v-model.number="form[`${section.id}.${field.id}`]"
                        type="number"
                        :min="field.min"
                        :max="field.max"
                        :step="field.step"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      />

                      <!-- Boolean (Toggle) -->
                      <div v-else-if="field.type === 'boolean'" class="flex items-center gap-3">
                        <button
                          type="button"
                          @click="form[`${section.id}.${field.id}`] = !form[`${section.id}.${field.id}`]"
                          :class="[
                            form[`${section.id}.${field.id}`]
                              ? 'bg-blue-600'
                              : 'bg-gray-200 dark:bg-gray-600',
                            'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800'
                          ]"
                          role="switch"
                          :aria-checked="form[`${section.id}.${field.id}`]"
                        >
                          <span
                            :class="[
                              form[`${section.id}.${field.id}`] ? 'translate-x-5' : 'translate-x-0',
                              'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                            ]"
                          />
                        </button>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                          {{ form[`${section.id}.${field.id}`] ? 'Enabled' : 'Disabled' }}
                        </span>
                      </div>

                      <!-- Text -->
                      <input
                        v-else
                        :id="`${section.id}-${field.id}`"
                        v-model="form[`${section.id}.${field.id}`]"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                  </div>
                </div>

                <!-- Divider between sections in same tab -->
                <hr v-if="sectionsForTab.indexOf(section) < sectionsForTab.length - 1" class="mt-8 border-gray-200 dark:border-gray-700" />
              </div>
            </TransitionGroup>
          </div>

          <!-- Save Bar -->
          <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3">
            <button
              type="submit"
              :disabled="processing"
              class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 shadow-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <Save v-if="!processing" class="w-4 h-4" />
              <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
              {{ processing ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.modal-enter-active {
  transition: opacity 0.2s ease;
}
.modal-enter-active > div:last-child {
  transition: transform 0.2s ease, opacity 0.2s ease;
}
.modal-leave-active {
  transition: opacity 0.15s ease;
}
.modal-leave-active > div:last-child {
  transition: transform 0.15s ease, opacity 0.15s ease;
}
.modal-enter-from {
  opacity: 0;
}
.modal-enter-from > div:last-child {
  transform: scale(0.95);
  opacity: 0;
}
.modal-leave-to {
  opacity: 0;
}
.modal-leave-to > div:last-child {
  transform: scale(0.95);
  opacity: 0;
}
</style>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import {
  ChevronLeft, Save, CheckCircle, Info, Phone, Paintbrush,
  Layout, ToggleLeft, Palette, Settings2,
} from 'lucide-vue-next'

interface Theme {
  id: number
  name: string
  slug: string
  description: string
  version: string
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
}

const props = defineProps<Props>()
const page = usePage()
const processing = ref(false)
const flash = computed(() => (page.props.flash as any) ?? {})

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

const activeTab = ref(tabs.value[0]?.id ?? '')

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
  <AdminLayout :title="`${theme.name} \u2014 Settings`">
    <Head :title="`${theme.name} Settings`" />

    <div class="space-y-5">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <a
            href="/admin/appearance/themes"
            class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
          >
            <ChevronLeft class="w-5 h-5" />
          </a>
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white leading-tight">{{ theme.name }} Settings</h1>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
              v{{ theme.version }}
              <span v-if="theme.is_active" class="ml-2 inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse" />
                Active
              </span>
            </p>
          </div>
        </div>
        <button
          @click="saveSettings"
          :disabled="processing"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 rounded-xl text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <Save v-if="!processing" class="w-4 h-4" />
          <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
          {{ processing ? 'Saving\u2026' : 'Save Changes' }}
        </button>
      </div>

      <!-- Flash -->
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

      <!-- Settings Card -->
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
                  class="w-4 h-4 mr-2 flex-shrink-0"
                  :class="activeTab === tab.id ? 'text-blue-500 dark:text-blue-400' : 'text-gray-400 group-hover:text-gray-500'"
                />
                {{ tab.label }}
              </button>
            </nav>
          </div>

          <!-- Panels -->
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
                        <!-- Boolean -->
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

          <!-- Footer save bar -->
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
  </AdminLayout>
</template>

<style scoped>
.scrollbar-none { scrollbar-width: none; }
.scrollbar-none::-webkit-scrollbar { display: none; }
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.2s ease; }
.slide-down-enter-from { opacity: 0; transform: translateY(-6px); }
.slide-down-leave-to { opacity: 0; transform: translateY(-4px); }
</style>

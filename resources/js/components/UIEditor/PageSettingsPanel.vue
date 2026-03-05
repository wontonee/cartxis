<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { useUiEditorStore } from '@/stores/uiEditorStore'
import type { PageSettingsData } from '@/stores/uiEditorStore'
import axios from 'axios'

const store = useUiEditorStore()

// ── Local form state (clone of store.pageSettings) ──────────────────────────
const form = ref<PageSettingsData>({
  id: 0,
  title: '',
  url_key: '',
  status: 'draft',
  is_homepage: false,
  meta_title: null,
  meta_description: null,
  meta_keywords: null,
})
const saveSuccess = ref(false)
const fieldErrors = ref<Record<string, string>>({})

// Sync form whenever store.pageSettings changes (e.g. after save)
watch(
  () => store.pageSettings,
  (val) => {
    if (val) form.value = { ...val }
  },
  { immediate: true, deep: true },
)

// ── Slug availability check ──────────────────────────────────────────────────
const slugAvailable = ref<boolean | null>(null)
const slugChecking = ref(false)
let slugTimer: ReturnType<typeof setTimeout> | null = null

function onUrlKeyInput() {
  slugAvailable.value = null
  if (slugTimer) clearTimeout(slugTimer)
  slugTimer = setTimeout(checkSlug, 450)
}

async function checkSlug() {
  if (!form.value.url_key || form.value.is_homepage) return
  slugChecking.value = true
  try {
    const res = await axios.post('/admin/content/pages/check-slug', {
      slug: form.value.url_key,
      exclude_id: form.value.id,
    })
    slugAvailable.value = res.data.available
  } catch {
    slugAvailable.value = null
  } finally {
    slugChecking.value = false
  }
}

// ── Save ─────────────────────────────────────────────────────────────────────
async function save() {
  fieldErrors.value = {}
  saveSuccess.value = false
  try {
    await store.savePageSettings({ ...form.value })
    saveSuccess.value = true
    setTimeout(() => (saveSuccess.value = false), 3000)
  } catch {
    // settingsError is set inside the store action
  }
}

// ── ESC to close ─────────────────────────────────────────────────────────────
function onKeyDown(e: KeyboardEvent) {
  if (e.key === 'Escape') store.isPageSettingsOpen = false
}

onMounted(() => window.addEventListener('keydown', onKeyDown))
onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeyDown)
  if (slugTimer) clearTimeout(slugTimer)
})
</script>

<template>
  <Teleport to="body">
    <!-- Backdrop -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="store.isPageSettingsOpen"
        class="fixed inset-0 bg-black/30 z-[9980]"
        @click="store.isPageSettingsOpen = false"
      />
    </Transition>

    <!-- Slide-over panel -->
    <Transition
      enter-active-class="transition-transform duration-250 ease-out"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-transform duration-200 ease-in"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <div
        v-if="store.isPageSettingsOpen"
        class="fixed top-0 right-0 h-full w-96 bg-white dark:bg-gray-900 shadow-2xl z-[9990] flex flex-col overflow-hidden"
      >
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0 bg-gray-50 dark:bg-gray-800">
          <div class="flex items-center gap-2">
            <!-- Settings gear icon -->
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <h2 class="text-sm font-semibold text-gray-800 dark:text-white">Page Settings</h2>
          </div>
          <button
            type="button"
            class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
            @click="store.isPageSettingsOpen = false"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Scrollable body -->
        <div class="flex-1 overflow-y-auto p-5 space-y-5">

          <!-- Success flash -->
          <Transition
            enter-active-class="transition-all duration-200"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <div v-if="saveSuccess" class="flex items-center gap-2 rounded-lg bg-green-50 border border-green-200 px-3 py-2 text-sm text-green-700">
              <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
              Settings saved successfully.
            </div>
          </Transition>

          <!-- Error flash -->
          <div v-if="store.settingsError" class="flex items-center gap-2 rounded-lg bg-red-50 border border-red-200 px-3 py-2 text-sm text-red-700">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ store.settingsError }}
          </div>

          <!-- ── Page Info ──────────────────────────────────────────────── -->
          <div>
            <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Page Info</h3>

            <!-- Title -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Title <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.title"
                type="text"
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Page title"
              />
            </div>

            <!-- URL Key -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL Key</label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">/</span>
                <input
                  v-model="form.url_key"
                  type="text"
                  :disabled="form.is_homepage"
                  class="w-full pl-6 pr-8 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono disabled:opacity-50 disabled:cursor-not-allowed"
                  placeholder="page-url-key"
                  @input="onUrlKeyInput"
                />
                <!-- Availability indicator -->
                <div class="absolute right-2.5 top-1/2 -translate-y-1/2">
                  <svg v-if="slugChecking" class="animate-spin w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  <svg v-else-if="slugAvailable === true" class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  <svg v-else-if="slugAvailable === false" class="w-3.5 h-3.5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                  </svg>
                </div>
              </div>
              <p v-if="slugAvailable === false" class="mt-1 text-xs text-red-600">This URL key is already taken.</p>
              <p v-else-if="form.is_homepage" class="mt-1 text-xs text-gray-400">Homepage URL is fixed.</p>
            </div>

            <!-- Status -->
            <div v-if="!form.is_homepage">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
              <select
                v-model="form.status"
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="disabled">Disabled</option>
              </select>
              <p class="mt-1 text-xs text-gray-500">Draft pages are not visible on storefront.</p>
            </div>
            <div v-else>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
              <div class="flex items-center gap-2 px-3 py-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>
                <span class="text-sm text-green-700 dark:text-green-400 font-medium">Always Published</span>
              </div>
            </div>
          </div>

          <!-- ── SEO ────────────────────────────────────────────────────── -->
          <div class="border-t border-gray-100 dark:border-gray-700 pt-5">
            <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">SEO</h3>

            <!-- Meta Title -->
            <div class="mb-4">
              <div class="flex items-center justify-between mb-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Meta Title</label>
                <span
                  class="text-xs"
                  :class="(form.meta_title?.length ?? 0) > 60 ? 'text-amber-500' : 'text-gray-400'"
                >
                  {{ form.meta_title?.length ?? 0 }}/60
                </span>
              </div>
              <input
                v-model="form.meta_title"
                type="text"
                maxlength="255"
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Leave empty to use page title"
              />
            </div>

            <!-- Meta Description -->
            <div class="mb-4">
              <div class="flex items-center justify-between mb-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Meta Description</label>
                <span
                  class="text-xs"
                  :class="(form.meta_description?.length ?? 0) > 160 ? 'text-amber-500' : 'text-gray-400'"
                >
                  {{ form.meta_description?.length ?? 0 }}/160
                </span>
              </div>
              <textarea
                v-model="form.meta_description"
                rows="3"
                maxlength="500"
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                placeholder="Brief description for search results (150–160 chars recommended)"
              />
            </div>

            <!-- Meta Keywords -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Keywords</label>
              <input
                v-model="form.meta_keywords"
                type="text"
                maxlength="500"
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="keyword1, keyword2, keyword3"
              />
              <p class="mt-1 text-xs text-gray-400">Comma-separated</p>
            </div>
          </div>
        </div>

        <!-- Footer / Save button -->
        <div class="flex-shrink-0 border-t border-gray-200 dark:border-gray-700 px-5 py-4 bg-gray-50 dark:bg-gray-800">
          <button
            type="button"
            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors"
            :disabled="store.isSettingsSaving || slugAvailable === false"
            @click="save"
          >
            <svg v-if="store.isSettingsSaving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span>{{ store.isSettingsSaving ? 'Saving...' : 'Save Settings' }}</span>
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import {
  Plus,
  Pencil,
  Trash2,
  LayoutTemplate,
  Globe,
  ChevronDown,
  CheckCircle,
  Clock,
  X,
  AlignLeft,
} from 'lucide-vue-next'
import axios from 'axios'

type RegionType = 'header' | 'footer' | 'section' | 'banner' | 'sidebar'

interface GlobalRegion {
  id: number
  name: string
  slug: string
  description: string | null
  region_type: RegionType
  status: 'draft' | 'published'
  published_at: string | null
  updated_at: string | null
}

const props = defineProps<{ regions: GlobalRegion[] }>()

// ── Create modal ─────────────────────────────────────────────────────────────
const showCreate = ref(false)
const form = ref({ name: '', description: '', region_type: 'section' as RegionType })
const creating = ref(false)
const createError = ref('')

function openCreate() {
  form.value = { name: '', description: '', region_type: 'section' }
  createError.value = ''
  showCreate.value = true
}

async function submitCreate() {
  if (!form.value.name.trim()) { createError.value = 'Name is required.'; return }
  creating.value = true
  createError.value = ''
  try {
    // Use Inertia post so we follow the redirect to the editor
    router.post('/admin/uieditor/regions', form.value)
  } catch {
    createError.value = 'Failed to create region.'
    creating.value = false
  }
}

// ── Delete ───────────────────────────────────────────────────────────────────
const confirmDelete = ref<GlobalRegion | null>(null)
const deleting = ref(false)

async function doDelete() {
  if (!confirmDelete.value) return
  deleting.value = true
  try {
    await axios.delete(`/admin/uieditor/regions/${confirmDelete.value.id}`)
    router.reload({ only: ['regions'] })
  } finally {
    deleting.value = false
    confirmDelete.value = null
  }
}

// ── Helpers ──────────────────────────────────────────────────────────────────
const typeColors: Record<RegionType, string> = {
  header:  'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
  footer:  'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
  section: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
  banner:  'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
  sidebar: 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300',
}

const typeLabels: Record<RegionType, string> = {
  header:  'Header',
  footer:  'Footer',
  section: 'Section',
  banner:  'Banner',
  sidebar: 'Sidebar',
}

function formatDate(iso: string | null): string {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
}

const regionTypes: { value: RegionType; label: string }[] = [
  { value: 'header',  label: 'Header'  },
  { value: 'footer',  label: 'Footer'  },
  { value: 'section', label: 'Section' },
  { value: 'banner',  label: 'Banner'  },
  { value: 'sidebar', label: 'Sidebar' },
]
</script>

<template>
  <Head title="Reusable Sections" />

  <AdminLayout title="Reusable Sections">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reusable Sections</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Build once, reuse anywhere — edit a section once and all pages update automatically.
          </p>
        </div>
        <button
          @click="openCreate"
          class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm"
        >
          <Plus class="w-4 h-4" />
          New Section
        </button>
      </div>

      <!-- Empty state -->
      <div
        v-if="!regions.length"
        class="flex flex-col items-center justify-center py-20 text-center"
      >
        <LayoutTemplate class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" />
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-1">No sections yet</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 max-w-xs">
          Create your first Reusable Section — like a promo header or CTA strip — then insert it into any landing page.
        </p>
        <button
          @click="openCreate"
          class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm"
        >
          <Plus class="w-4 h-4" />
          Create First Section
        </button>
      </div>

      <!-- Regions grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="region in regions"
          :key="region.id"
          class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-md transition-shadow flex flex-col"
        >
          <!-- Card header -->
          <div class="flex items-start justify-between p-4 pb-3">
            <div class="flex items-center gap-2 min-w-0">
              <span
                :class="['text-xs font-semibold px-2 py-0.5 rounded-full capitalize flex-shrink-0', typeColors[region.region_type]]"
              >
                {{ typeLabels[region.region_type] }}
              </span>
              <span
                v-if="region.status === 'published'"
                class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400"
              >
                <CheckCircle class="w-3 h-3" />
                Live
              </span>
              <span v-else class="inline-flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400">
                <Clock class="w-3 h-3" />
                Draft
              </span>
            </div>
            <button
              @click="confirmDelete = region"
              class="flex-shrink-0 p-1 text-gray-300 hover:text-red-500 transition-colors"
              title="Delete region"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>

          <!-- Card body -->
          <div class="px-4 pb-2 flex-1">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ region.name }}</h3>
            <p v-if="region.description" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ region.description }}</p>
            <p v-else class="mt-0.5 text-xs text-gray-400 dark:text-gray-500 italic">No description</p>
          </div>

          <!-- Card footer -->
          <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <span class="text-xs text-gray-400">Updated {{ formatDate(region.updated_at) }}</span>
            <a
              :href="`/admin/uieditor/regions/${region.id}/editor`"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-white bg-blue-50 hover:bg-blue-600 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white rounded-xl transition-colors"
            >
              <Pencil class="w-3.5 h-3.5" />
              Edit Layout
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Create modal ──────────────────────────────────────────────────── -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showCreate"
          class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4"
          @click.self="showCreate = false"
        >
          <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md border border-gray-200 dark:border-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
              <h2 class="text-base font-semibold text-gray-900 dark:text-white">New Reusable Section</h2>
              <button @click="showCreate = false" class="text-gray-400 hover:text-gray-600 transition">
                <X class="w-5 h-5" />
              </button>
            </div>

            <!-- Modal body -->
            <div class="px-6 py-5 space-y-4">
              <!-- Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Section Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="e.g. Summer Promo Header"
                  class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  @keydown.enter="submitCreate"
                />
              </div>

              <!-- Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                <select
                  v-model="form.region_type"
                  class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option v-for="t in regionTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                </select>
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description <span class="text-gray-400">(optional)</span></label>
                <textarea
                  v-model="form.description"
                  rows="2"
                  placeholder="What is this region used for?"
                  class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                />
              </div>

              <p v-if="createError" class="text-sm text-red-600">{{ createError }}</p>
            </div>

            <!-- Modal footer -->
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end gap-3">
              <button
                @click="showCreate = false"
                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition"
              >
                Cancel
              </button>
              <button
                @click="submitCreate"
                :disabled="creating || !form.name.trim()"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm"
              >
                <span v-if="creating">Creating…</span>
                <span v-else>Create &amp; Edit Layout</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Delete confirm modal -->
      <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="confirmDelete"
          class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4"
          @click.self="confirmDelete = null"
        >
          <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Delete Section?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
              "<strong class="text-gray-700 dark:text-gray-200">{{ confirmDelete?.name }}</strong>" will be permanently deleted. Any pages that reference this section will show a placeholder.
            </p>
            <div class="flex justify-end gap-3">
              <button @click="confirmDelete = null" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition">Cancel</button>
              <button
                @click="doDelete"
                :disabled="deleting"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition disabled:opacity-50"
              >
                <span v-if="deleting">Deleting…</span>
                <span v-else>Yes, Delete</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </AdminLayout>
</template>

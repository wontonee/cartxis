<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import ConfirmModal from '@/components/Admin/ConfirmModal.vue'
import {
  RefreshCcw, Download, LayoutTemplate, Search, CheckCircle2, ArrowUpCircle, Monitor,
  Wand2, Settings2, Power, ChevronRight,
} from 'lucide-vue-next'

interface Category {
  slug: string
  label: string
  sort?: number
}

interface TemplateType {
  slug: string
  label: string
  enabled?: boolean
}

interface TemplateItem {
  slug: string
  type: string
  category: string
  category_label?: string
  tags?: string[]
  name: string
  description: string
  version: string
  author?: string
  author_url?: string | null
  screenshot_url?: string | null
  source?: string
  includes?: string[]
  demo_business_type?: string | null
  has_demo_layout?: boolean
  native_homepage?: boolean
  installed?: boolean
  is_active?: boolean
  installed_version?: string | null
  update_available?: boolean
  remote_categories?: Array<{ slug: string; name: string }>
  updated_at?: string | null
}

interface Props {
  templates: TemplateItem[]
  categories: Category[]
  types: TemplateType[]
  filters: {
    type?: string | null
    category?: string | null
    search?: string | null
  }
  remoteBrowseEnabled?: boolean
  remoteInstallEnabled?: boolean
  directoryUrl?: string
  remoteThemeCount?: number
  remoteProbe?: {
    ok: boolean
    theme_count: number
    category_count: number
    status: number | null
    error: string | null
  }
}

const props = defineProps<Props>()

const isSyncing = ref(false)
const installing = ref<Record<string, boolean>>({})
const activating = ref<Record<string, boolean>>({})
const searchQuery = ref(props.filters.search || '')
const selectedCategory = ref(props.filters.category || '')
const selectedType = ref(props.filters.type || 'storefront')

const confirmModal = ref({
  show: false,
  title: '',
  message: '',
  confirmText: 'Confirm',
  variant: 'primary' as 'primary' | 'warning' | 'danger',
  onConfirm: () => {},
})

const openConfirm = (options: {
  title: string
  message: string
  confirmText?: string
  variant?: 'primary' | 'warning' | 'danger'
  onConfirm: () => void
}) => {
  confirmModal.value = {
    show: true,
    title: options.title,
    message: options.message,
    confirmText: options.confirmText ?? 'Confirm',
    variant: options.variant ?? 'primary',
    onConfirm: options.onConfirm,
  }
}

const handleConfirm = () => {
  confirmModal.value.onConfirm()
  confirmModal.value.show = false
}

const categoryLabel = (template: TemplateItem) =>
  template.category_label
  || props.categories.find((item) => item.slug === template.category)?.label
  || template.category

const filteredTemplates = computed(() => props.templates)

const builderUrl = (template: TemplateItem) =>
  template.native_homepage ? '/admin/appearance?tab=features' : '/admin/content/pages'

const applyFilters = () => {
  router.get('/admin/appearance/template-zone', {
    type: selectedType.value || undefined,
    category: selectedCategory.value || undefined,
    search: searchQuery.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const syncCatalog = () => {
  isSyncing.value = true
  router.post('/admin/appearance/template-zone/sync', {}, {
    preserveScroll: true,
    onFinish: () => { isSyncing.value = false },
  })
}

const isRemoteTheme = (template: TemplateItem) => template.source === 'remote'

const canInstallRemote = (template: TemplateItem) =>
  !isRemoteTheme(template) || props.remoteInstallEnabled !== false

const runInstall = (template: TemplateItem) => {
  const remote = isRemoteTheme(template)

  installing.value[template.slug] = true
  router.post(`/admin/appearance/template-zone/${encodeURIComponent(template.slug)}/install`, {
    import_demo_products: false,
    import_layout: !remote && !!template.has_demo_layout,
    activate: true,
  }, {
    preserveScroll: true,
    onFinish: () => { installing.value[template.slug] = false },
  })
}

const runActivate = (template: TemplateItem) => {
  activating.value[template.slug] = true
  router.post(`/admin/appearance/themes/${encodeURIComponent(template.slug)}/activate`, {}, {
    preserveScroll: true,
    onFinish: () => { activating.value[template.slug] = false },
  })
}

const installTemplate = (template: TemplateItem) => {
  if (installing.value[template.slug]) return

  if (!canInstallRemote(template)) {
    openConfirm({
      title: 'API key required',
      message: 'Set CARTXIS_THEME_API_KEY in your .env file to install themes from the Cartxis directory. It is auto-generated when you run php artisan cartxis:install.',
      confirmText: 'Got it',
      variant: 'warning',
      onConfirm: () => {},
    })
    return
  }

  const remote = isRemoteTheme(template)

  openConfirm({
    title: remote ? 'Install from Cartxis Directory' : 'Install theme',
    message: remote
      ? `Install "${template.name}" from the Cartxis theme directory? The package will be downloaded securely and activated on your store.`
      : `Install "${template.name}" into your store and activate it as the storefront theme?`,
    confirmText: 'Install & Activate',
    variant: 'primary',
    onConfirm: () => runInstall(template),
  })
}

const activateTemplate = (template: TemplateItem) => {
  if (activating.value[template.slug]) return

  openConfirm({
    title: 'Activate theme',
    message: `Activate "${template.name}" as your storefront theme? Your live store appearance will switch to this theme.`,
    confirmText: 'Activate',
    variant: 'primary',
    onConfirm: () => runActivate(template),
  })
}

const downloadTemplate = (slug: string) => {
  window.location.href = `/admin/appearance/template-zone/${encodeURIComponent(slug)}/download?source=catalog`
}
</script>

<template>
  <Head title="Browse Themes" />

  <AdminLayout title="Browse Themes">
    <div class="space-y-6">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <LayoutTemplate class="w-7 h-7 text-blue-600" />
            Browse Themes
          </h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Search the Cartxis theme directory, preview storefront themes, and install with one click.
          </p>
        </div>

        <div class="flex flex-wrap gap-2">
          <Link
            href="/admin/appearance/themes"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            Installed Themes
          </Link>
          <button
            @click="syncCatalog"
            :disabled="isSyncing"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 rounded-xl text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            <RefreshCcw class="w-4 h-4" :class="isSyncing ? 'animate-spin' : ''" />
            Sync Catalog
          </button>
        </div>
      </div>

      <div
        v-if="remoteBrowseEnabled === false"
        class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4"
      >
        <p class="text-sm text-amber-900 dark:text-amber-100">
          Theme directory URL is not configured. Set
          <code class="font-mono text-xs">CARTXIS_THEME_DIRECTORY_URL</code> in your
          <code class="font-mono text-xs">.env</code> or run
          <code class="font-mono text-xs">php artisan cartxis:install</code>
          to browse themes from cartxis.com.
        </p>
      </div>

      <div
        v-else-if="remoteProbe && !remoteProbe.ok"
        class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4"
      >
        <p class="text-sm text-red-900 dark:text-red-100">
          Could not reach the theme directory at
          <code class="font-mono text-xs">{{ directoryUrl }}</code>.
          {{ remoteProbe.error || 'Check that cartxis-home is running and the URL includes /api.' }}
        </p>
      </div>

      <div
        v-else-if="remoteProbe?.ok && remoteProbe.theme_count > 0 && filteredTemplates.length === 0"
        class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4"
      >
        <p class="text-sm text-amber-900 dark:text-amber-100">
          The directory reports {{ remoteProbe.theme_count }} theme(s) but none matched the current filters.
          Try <strong>All Templates</strong> or click <strong>Sync Catalog</strong>.
        </p>
      </div>

      <div
        v-else-if="remoteProbe?.ok && remoteProbe.theme_count === 0"
        class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4"
      >
        <p class="text-sm text-amber-900 dark:text-amber-100">
          Connected to <code class="font-mono text-xs">{{ directoryUrl }}</code> but no themes are published there yet.
        </p>
      </div>

      <div v-else-if="remoteInstallEnabled === false" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
        <p class="text-sm text-amber-900 dark:text-amber-100">
          You can browse the Cartxis theme directory, but one-click installs require
          <code class="font-mono text-xs">CARTXIS_THEME_API_KEY</code> in your
          <code class="font-mono text-xs">.env</code> (auto-generated during
          <code class="font-mono text-xs">cartxis:install</code>).
        </p>
      </div>

      <div v-else class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
        <p class="text-sm text-blue-900 dark:text-blue-100">
          Themes from the official Cartxis directory install securely — packages are downloaded server-side and activated in one step.
        </p>
      </div>

      <div class="flex flex-col xl:flex-row gap-6">
        <aside class="xl:w-64 shrink-0 space-y-4">
          <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Categories</h2>
            <div class="space-y-1">
              <button
                @click="selectedCategory = ''; applyFilters()"
                class="w-full text-left px-3 py-2 rounded-lg text-sm transition-colors"
                :class="!selectedCategory ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50'"
              >
                All Templates
              </button>
              <button
                v-for="category in categories"
                :key="category.slug"
                @click="selectedCategory = category.slug; applyFilters()"
                class="w-full text-left px-3 py-2 rounded-lg text-sm transition-colors"
                :class="selectedCategory === category.slug ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50'"
              >
                {{ category.label }}
              </button>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Template Type</h2>
            <div class="space-y-1">
              <button
                v-for="type in types"
                :key="type.slug"
                @click="selectedType = type.slug; applyFilters()"
                :disabled="!type.enabled"
                class="w-full text-left px-3 py-2 rounded-lg text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                :class="selectedType === type.slug ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50'"
              >
                {{ type.label }}
                <span v-if="!type.enabled" class="text-xs text-gray-400"> (soon)</span>
              </button>
            </div>
          </div>
        </aside>

        <div class="flex-1 space-y-4">
          <div class="relative">
            <Search class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              v-model="searchQuery"
              @keyup.enter="applyFilters"
              type="search"
              placeholder="Search themes..."
              class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm"
            />
          </div>

          <div v-if="filteredTemplates.length === 0" class="text-center py-16 bg-white dark:bg-gray-900 rounded-xl border border-dashed border-gray-300">
            <Monitor class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">No themes found</h3>
            <p class="text-sm text-gray-500 mt-1">Try another category or clear your search filters.</p>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <article
              v-for="template in filteredTemplates"
              :key="template.slug"
              class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm hover:shadow-md transition-shadow flex flex-col"
            >
              <div class="aspect-video bg-gray-100 relative">
                <img
                  v-if="template.screenshot_url"
                  :src="template.screenshot_url"
                  :alt="template.name"
                  class="w-full h-full object-cover object-top"
                />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <Monitor class="w-12 h-12 text-gray-300" />
                </div>

                <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-2 p-3">
                  <span class="inline-flex max-w-[55%] px-2.5 py-1 rounded-full text-xs font-semibold bg-white/95 text-gray-700 shadow-sm ring-1 ring-black/5 truncate">
                    {{ categoryLabel(template) }}
                  </span>

                  <div class="flex flex-col items-end gap-1.5 shrink-0">
                    <span
                      v-if="isRemoteTheme(template)"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 ring-1 ring-purple-600/10"
                    >
                      Cartxis Directory
                    </span>
                    <span
                      v-if="template.is_active"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-600 text-white shadow-sm"
                    >
                      Active
                    </span>
                    <span
                      v-else-if="template.installed"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 ring-1 ring-green-600/10"
                    >
                      <CheckCircle2 class="w-3 h-3" />
                      Installed
                    </span>
                    <span
                      v-if="template.update_available"
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 ring-1 ring-amber-600/10"
                    >
                      <ArrowUpCircle class="w-3 h-3" />
                      Update
                    </span>
                  </div>
                </div>
              </div>

              <div class="p-5 space-y-3 flex-1 flex flex-col">
                <div>
                  <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ template.name }}</h3>
                  <p class="text-xs text-gray-500 mt-1">
                    v{{ template.version }} · {{ template.author }}
                    <span v-if="template.installed && template.installed_version" class="text-green-600">
                      · installed v{{ template.installed_version }}
                    </span>
                  </p>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3 flex-1">{{ template.description }}</p>

                <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                  <template v-if="template.installed && !template.update_available">
                    <button
                      v-if="!template.is_active"
                      @click="activateTemplate(template)"
                      :disabled="activating[template.slug]"
                      class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
                    >
                      <Power class="w-4 h-4" />
                      {{ activating[template.slug] ? 'Activating…' : 'Activate' }}
                    </button>
                    <Link
                      v-if="template.is_active"
                      href="/admin/appearance"
                      class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                    >
                      <Settings2 class="w-4 h-4" />
                      Customize
                    </Link>
                    <Link
                      v-if="template.is_active"
                      :href="builderUrl(template)"
                      class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-green-700 bg-green-50 border border-green-200 hover:bg-green-100"
                    >
                      <Wand2 class="w-4 h-4" />
                      Open Page Builder
                      <ChevronRight class="w-3.5 h-3.5 opacity-60" />
                    </Link>
                  </template>

                  <button
                    v-else
                    @click="installTemplate(template)"
                    :disabled="installing[template.slug] || (isRemoteTheme(template) && !canInstallRemote(template))"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
                  >
                    {{ template.installed && template.update_available ? 'Update' : 'Install Now' }}
                  </button>

                  <button
                    v-if="!isRemoteTheme(template)"
                    @click="downloadTemplate(template.slug)"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50"
                  >
                    <Download class="w-4 h-4" />
                    Download
                  </button>
                  <Link
                    :href="`/admin/appearance/template-zone/${encodeURIComponent(template.slug)}`"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-blue-700 hover:bg-blue-50"
                  >
                    Details
                  </Link>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </div>

    <ConfirmModal
      v-model:show="confirmModal.show"
      :title="confirmModal.title"
      :message="confirmModal.message"
      :confirm-text="confirmModal.confirmText"
      :variant="confirmModal.variant"
      @confirm="handleConfirm"
    />
  </AdminLayout>
</template>

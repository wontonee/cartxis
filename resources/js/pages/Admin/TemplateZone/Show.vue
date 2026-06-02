<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import ConfirmModal from '@/components/Admin/ConfirmModal.vue'
import { Download, LayoutTemplate, CheckCircle2, ArrowLeft, Wand2, Settings2, Power } from 'lucide-vue-next'

interface Category {
  slug: string
  label: string
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
  template: TemplateItem
  categories: Category[]
  remoteBrowseEnabled?: boolean
  remoteInstallEnabled?: boolean
}

const props = defineProps<Props>()
const installing = ref(false)
const activating = ref(false)

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

const categoryLabel = () =>
  props.template.category_label
  || props.categories.find((item) => item.slug === props.template.category)?.label
  || props.template.category

const builderUrl = () =>
  props.template.native_homepage ? '/admin/appearance?tab=features' : '/admin/content/pages'

const isRemoteTheme = () => props.template.source === 'remote'

const runInstall = () => {
  installing.value = true
  router.post(`/admin/appearance/template-zone/${encodeURIComponent(props.template.slug)}/install`, {
    import_demo_products: false,
    import_layout: !isRemoteTheme() && !!props.template.has_demo_layout,
    activate: true,
  }, {
    onFinish: () => { installing.value = false },
  })
}

const runActivate = () => {
  activating.value = true
  router.post(`/admin/appearance/themes/${encodeURIComponent(props.template.slug)}/activate`, {}, {
    onFinish: () => { activating.value = false },
  })
}

const installTemplate = () => {
  if (installing.value) return

  if (isRemoteTheme() && props.remoteInstallEnabled === false) {
    openConfirm({
      title: 'API key required',
      message: 'Set CARTXIS_THEME_API_KEY in your .env file to install themes from the Cartxis directory. It is auto-generated when you run php artisan cartxis:install.',
      confirmText: 'Got it',
      variant: 'warning',
      onConfirm: () => {},
    })
    return
  }

  openConfirm({
    title: isRemoteTheme() ? 'Install from Cartxis Directory' : 'Install theme',
    message: isRemoteTheme()
      ? `Install "${props.template.name}" from the Cartxis theme directory? The package will be downloaded securely and activated on your store.`
      : `Install "${props.template.name}" and activate it as the storefront theme?`,
    confirmText: 'Install & Activate',
    variant: 'primary',
    onConfirm: runInstall,
  })
}

const downloadTemplate = () => {
  window.location.href = `/admin/appearance/template-zone/${encodeURIComponent(props.template.slug)}/download?source=catalog`
}

const activateTemplate = () => {
  if (activating.value) return

  openConfirm({
    title: 'Activate theme',
    message: `Activate "${props.template.name}" as your storefront theme? Your live store appearance will switch to this theme.`,
    confirmText: 'Activate',
    variant: 'primary',
    onConfirm: runActivate,
  })
}
</script>

<template>
  <Head :title="template.name" />

  <AdminLayout :title="template.name">
    <div class="max-w-5xl mx-auto space-y-6">
      <Link
        href="/admin/appearance/template-zone"
        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900"
      >
        <ArrowLeft class="w-4 h-4" />
        Back to Browse Themes
      </Link>

      <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="aspect-[21/9] bg-gray-100">
          <img
            v-if="template.screenshot_url"
            :src="template.screenshot_url"
            :alt="template.name"
            class="w-full h-full object-cover"
          />
        </div>

        <div class="p-6 space-y-4">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
              <div class="flex items-center gap-2 text-sm text-gray-500">
                <LayoutTemplate class="w-4 h-4" />
                {{ categoryLabel() }}
              </div>
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ template.name }}</h1>
              <p class="text-sm text-gray-500 mt-1">
                v{{ template.version }} · {{ template.author }}
                <span v-if="template.updated_at"> · Updated {{ new Date(template.updated_at).toLocaleDateString() }}</span>
              </p>
            </div>
            <div class="flex flex-wrap gap-2">
              <span
                v-if="isRemoteTheme()"
                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-700"
              >
                Cartxis Directory
              </span>
              <span
                v-if="template.is_active"
                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700"
              >
                Active
              </span>
              <span
                v-else-if="template.installed"
                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700"
              >
                <CheckCircle2 class="w-4 h-4" />
                Installed<span v-if="template.installed_version"> (v{{ template.installed_version }})</span>
              </span>
            </div>
          </div>

          <p class="text-gray-600 dark:text-gray-300">{{ template.description }}</p>

          <div v-if="template.remote_categories?.length" class="flex flex-wrap gap-2">
            <span
              v-for="category in template.remote_categories"
              :key="category.slug"
              class="px-2 py-1 rounded-md bg-blue-50 text-xs text-blue-700"
            >
              {{ category.name }}
            </span>
          </div>

          <div v-if="template.tags?.length" class="flex flex-wrap gap-2">
            <span
              v-for="tag in template.tags"
              :key="tag"
              class="px-2 py-1 rounded-md bg-gray-100 text-xs text-gray-600"
            >
              {{ tag }}
            </span>
          </div>

          <div class="flex flex-wrap gap-3 pt-2">
            <template v-if="template.installed && !template.update_available">
              <button
                v-if="!template.is_active"
                @click="activateTemplate"
                :disabled="activating"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
              >
                <Power class="w-4 h-4" />
                {{ activating ? 'Activating…' : 'Activate Theme' }}
              </button>
              <Link
                v-if="template.is_active"
                href="/admin/appearance"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
              >
                <Settings2 class="w-4 h-4" />
                Customize
              </Link>
              <Link
                v-if="template.is_active"
                :href="builderUrl()"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium text-green-700 bg-green-50 border border-green-200 hover:bg-green-100"
              >
                <Wand2 class="w-4 h-4" />
                Open Page Builder
              </Link>
            </template>

            <button
              v-else
              @click="installTemplate"
              :disabled="installing || (isRemoteTheme() && remoteInstallEnabled === false)"
              class="inline-flex items-center px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
            >
              {{ template.installed && template.update_available ? 'Update Theme' : 'Install Now' }}
            </button>
            <button
              v-if="!isRemoteTheme()"
              @click="downloadTemplate"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50"
            >
              <Download class="w-4 h-4" />
              Download Zip
            </button>
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

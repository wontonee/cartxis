<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import ImageUploader from '@/components/Admin/ImageUploader.vue'

interface Props {
  settings?: Record<string, any>
}

const props = withDefaults(defineProps<Props>(), {
  settings: () => ({})
})

// Active tab
const activeTab = ref<'site' | 'seo'>('site')

const existingSiteLogo = ref(props.settings['site_logo'] || '')
const existingAdminLogo = ref(props.settings['admin_logo'] || '')
const existingSiteFavicon = ref(props.settings['site_favicon'] || '')
const siteLogoFiles = ref<File[]>([])
const adminLogoFiles = ref<File[]>([])
const siteFaviconFiles = ref<File[]>([])

const form = useForm({
  // Site Information
  site_name: props.settings['site_name'] || 'Cartxis Shop',
  site_tagline: props.settings['site_tagline'] || '',
  admin_email: props.settings['admin_email'] || '',
  contact_phone: props.settings['contact_phone'] || '',
  contact_address: props.settings['contact_address'] || '',
  
  // SEO Settings
  meta_title: props.settings['meta_title'] || '',
  meta_description: props.settings['meta_description'] || '',
  meta_keywords: props.settings['meta_keywords'] || '',
  google_analytics_id: props.settings['google_analytics_id'] || '',
  google_tag_manager_id: props.settings['google_tag_manager_id'] || '',
  facebook_pixel_id: props.settings['facebook_pixel_id'] || '',
})

const save = () => {
  form.transform((data) => {
    const payload: Record<string, any> = { ...data }

    if (siteLogoFiles.value.length > 0) {
      payload.site_logo = siteLogoFiles.value[0]
    } else {
      delete payload.site_logo
    }

    if (adminLogoFiles.value.length > 0) {
      payload.admin_logo = adminLogoFiles.value[0]
    } else {
      delete payload.admin_logo
    }

    if (siteFaviconFiles.value.length > 0) {
      payload.site_favicon = siteFaviconFiles.value[0]
    } else {
      delete payload.site_favicon
    }

    return payload
  })

  form.post('/admin/settings/general', {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      if (siteLogoFiles.value.length > 0) {
        existingSiteLogo.value = ''
        siteLogoFiles.value = []
      }
      if (adminLogoFiles.value.length > 0) {
        existingAdminLogo.value = ''
        adminLogoFiles.value = []
      }
      if (siteFaviconFiles.value.length > 0) {
        existingSiteFavicon.value = ''
        siteFaviconFiles.value = []
      }
    },
  })
}
</script>

<template>
  <AdminLayout title="General Settings">
    <Head title="General Settings" />

    <div class="p-6">
      <!-- Page Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">General Settings</h1>
        <p class="mt-1 text-sm text-gray-500">Configure basic site information and SEO settings</p>
      </div>

      <form @submit.prevent="save">
        <!-- Main Content Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <!-- Tabs -->
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6">
              <button type="button" @click="activeTab = 'site'" :class="[activeTab === 'site' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                Site Information
              </button>
              <button type="button" @click="activeTab = 'seo'" :class="[activeTab === 'seo' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                SEO
              </button>
            </nav>
          </div>

          <!-- Site Information Tab -->
          <div v-show="activeTab === 'site'" class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Site Information</h3>

            <div class="space-y-6">
              <!-- Site Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Site Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.site_name"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': form.errors.site_name }"
                  placeholder="Your Store Name"
                />
                <p v-if="form.errors.site_name" class="mt-1 text-sm text-red-600">{{ form.errors.site_name }}</p>
              </div>

              <!-- Site Tagline -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Site Tagline</label>
                <input
                  v-model="form.site_tagline"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': form.errors.site_tagline }"
                  placeholder="Your store's tagline or motto"
                />
                <p v-if="form.errors.site_tagline" class="mt-1 text-sm text-red-600">{{ form.errors.site_tagline }}</p>
              </div>

              <!-- Admin Email -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Admin Email <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.admin_email"
                  type="email"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': form.errors.admin_email }"
                  placeholder="admin@example.com"
                />
                <p v-if="form.errors.admin_email" class="mt-1 text-sm text-red-600">{{ form.errors.admin_email }}</p>
              </div>

              <!-- Contact Phone -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                <input
                  v-model="form.contact_phone"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': form.errors.contact_phone }"
                  placeholder="+1 (555) 123-4567"
                />
                <p v-if="form.errors.contact_phone" class="mt-1 text-sm text-red-600">{{ form.errors.contact_phone }}</p>
              </div>

              <!-- Contact Address -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Address</label>
                <textarea
                  v-model="form.contact_address"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': form.errors.contact_address }"
                  placeholder="Your business address"
                ></textarea>
                <p v-if="form.errors.contact_address" class="mt-1 text-sm text-red-600">{{ form.errors.contact_address }}</p>
              </div>

              <!-- Site Logo -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Site Logo (Frontend)</label>
                <div v-if="existingSiteLogo && siteLogoFiles.length === 0" class="mb-4">
                  <img :src="`/storage/${existingSiteLogo}`" alt="Site logo" class="h-16 object-contain border border-gray-200 rounded" />
                  <p class="text-xs text-gray-500 mt-1">Current logo</p>
                </div>
                <ImageUploader v-model="siteLogoFiles" :maxFiles="1" :maxSize="2" accept="image/*" />
                <p class="mt-1 text-xs text-gray-500">Recommended size: 200x60 pixels. Max 2MB. Displayed on storefront header.</p>
                <p v-if="form.errors.site_logo" class="mt-1 text-sm text-red-600">{{ form.errors.site_logo }}</p>
              </div>

              <!-- Admin Logo -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Admin Logo (Dashboard)</label>
                <div v-if="existingAdminLogo && adminLogoFiles.length === 0" class="mb-4">
                  <img :src="`/storage/${existingAdminLogo}`" alt="Admin logo" class="h-12 object-contain border border-gray-200 rounded" />
                  <p class="text-xs text-gray-500 mt-1">Current admin logo</p>
                </div>
                <ImageUploader v-model="adminLogoFiles" :maxFiles="1" :maxSize="2" accept="image/*" />
                <p class="mt-1 text-xs text-gray-500">Recommended size: 150x40 pixels. Max 2MB. Displayed in admin sidebar.</p>
                <p v-if="form.errors.admin_logo" class="mt-1 text-sm text-red-600">{{ form.errors.admin_logo }}</p>
              </div>

              <!-- Site Favicon -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Site Favicon</label>
                <div v-if="existingSiteFavicon && siteFaviconFiles.length === 0" class="mb-4">
                  <img :src="`/storage/${existingSiteFavicon}`" alt="Site favicon" class="h-10 w-10 object-contain border border-gray-200 rounded" />
                  <p class="text-xs text-gray-500 mt-1">Current favicon</p>
                </div>
                <ImageUploader v-model="siteFaviconFiles" :maxFiles="1" :maxSize="1" accept="image/*" />
                <p class="mt-1 text-xs text-gray-500">Recommended size: 32x32 pixels, ICO or PNG format</p>
                <p v-if="form.errors.site_favicon" class="mt-1 text-sm text-red-600">{{ form.errors.site_favicon }}</p>
              </div>
            </div>
          </div>

          <!-- SEO Settings Tab -->
          <div v-show="activeTab === 'seo'" class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Search Engine Optimization</h3>

            <div class="space-y-6">
              <!-- Meta Title -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                <input
                  v-model="form.meta_title"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': form.errors.meta_title }"
                  placeholder="Your Store - Best Products Online"
                />
                <p class="mt-1 text-xs text-gray-500">Recommended: 50-60 characters</p>
                <p v-if="form.errors.meta_title" class="mt-1 text-sm text-red-600">{{ form.errors.meta_title }}</p>
              </div>

              <!-- Meta Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                <textarea
                  v-model="form.meta_description"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': form.errors.meta_description }"
                  placeholder="Brief description of your store for search engines"
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">Recommended: 150-160 characters</p>
                <p v-if="form.errors.meta_description" class="mt-1 text-sm text-red-600">{{ form.errors.meta_description }}</p>
              </div>

              <!-- Meta Keywords -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Meta Keywords</label>
                <input
                  v-model="form.meta_keywords"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': form.errors.meta_keywords }"
                  placeholder="ecommerce, online store, products"
                />
                <p class="mt-1 text-xs text-gray-500">Comma-separated keywords</p>
                <p v-if="form.errors.meta_keywords" class="mt-1 text-sm text-red-600">{{ form.errors.meta_keywords }}</p>
              </div>

              <!-- Google Analytics ID -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Google Analytics ID</label>
                <input
                  v-model="form.google_analytics_id"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': form.errors.google_analytics_id }"
                  placeholder="G-XXXXXXXXXX or UA-XXXXXXXXX-X"
                />
                <p class="mt-1 text-xs text-gray-500">Your Google Analytics tracking ID</p>
                <p v-if="form.errors.google_analytics_id" class="mt-1 text-sm text-red-600">{{ form.errors.google_analytics_id }}</p>
              </div>

              <!-- Google Tag Manager ID -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Google Tag Manager ID</label>
                <input
                  v-model="form.google_tag_manager_id"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': form.errors.google_tag_manager_id }"
                  placeholder="GTM-XXXXXXX"
                />
                <p class="mt-1 text-xs text-gray-500">Your Google Tag Manager container ID</p>
                <p v-if="form.errors.google_tag_manager_id" class="mt-1 text-sm text-red-600">{{ form.errors.google_tag_manager_id }}</p>
              </div>

              <!-- Facebook Pixel ID -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Facebook Pixel ID</label>
                <input
                  v-model="form.facebook_pixel_id"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': form.errors.facebook_pixel_id }"
                  placeholder="XXXXXXXXXXXXXXX"
                />
                <p class="mt-1 text-xs text-gray-500">Your Facebook Pixel ID for tracking</p>
                <p v-if="form.errors.facebook_pixel_id" class="mt-1 text-sm text-red-600">{{ form.errors.facebook_pixel_id }}</p>
              </div>
            </div>
          </div>

          <!-- Save Button Section -->
          <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
            <div class="flex items-center justify-end">
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg v-if="!form.processing" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ form.processing ? 'Saving...' : 'Save Settings' }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

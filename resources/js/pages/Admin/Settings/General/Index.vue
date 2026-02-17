<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import ImageUploader from '@/components/Admin/ImageUploader.vue'
import { 
  Save, 
  Globe, 
  Search, 
  Image as ImageIcon, 
  Mail, 
  Phone, 
  MapPin, 
  Flag,
  BarChart, 
  Facebook,
  Tag
} from 'lucide-vue-next'

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
  store_country: props.settings['store_country'] || '',
  
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

    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">General Settings</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure basic site information and SEO settings</p>
        </div>
        <div>
            <button
                @click="save"
                :disabled="form.processing"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-xl text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <Save v-if="!form.processing" class="w-4 h-4" />
                <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ form.processing ? 'Saving...' : 'Save Changes' }}
            </button>
        </div>
      </div>

      <form @submit.prevent="save">
        <!-- Main Content Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
          <!-- Tabs -->
          <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
              <button 
                type="button" 
                @click="activeTab = 'site'" 
                :class="[
                    activeTab === 'site' 
                        ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600', 
                    'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200'
                ]"
              >
                <Globe class="w-4 h-4 mr-2" :class="activeTab === 'site' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
                Site Information
              </button>
              <button 
                type="button" 
                @click="activeTab = 'seo'" 
                :class="[
                    activeTab === 'seo' 
                        ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600', 
                    'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200'
                ]"
              >
                <Search class="w-4 h-4 mr-2" :class="activeTab === 'seo' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
                SEO
              </button>
            </nav>
          </div>

          <!-- Site Information Tab -->
          <div v-show="activeTab === 'site'" class="p-6 sm:p-8">
            <div class="space-y-8">
              <!-- Basic Info Section -->
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <Globe class="w-5 h-5 text-gray-400" />
                    Basic Information
                </h3>
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                    <!-- Site Name -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Site Name <span class="text-red-500">*</span>
                        </label>
                        <input
                        v-model="form.site_name"
                        type="text"
                        class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                        :class="{ 'border-red-500': form.errors.site_name }"
                        placeholder="Your Store Name"
                        />
                        <p v-if="form.errors.site_name" class="mt-1 text-sm text-red-600">{{ form.errors.site_name }}</p>
                    </div>

                    <!-- Site Tagline -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Site Tagline</label>
                        <input
                        v-model="form.site_tagline"
                        type="text"
                        class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                        :class="{ 'border-red-500': form.errors.site_tagline }"
                        placeholder="Your store's tagline or motto"
                        />
                        <p v-if="form.errors.site_tagline" class="mt-1 text-sm text-red-600">{{ form.errors.site_tagline }}</p>
                    </div>
                </div>
              </div>

              <!-- Contact Info Section -->
              <div class="pt-6 border-t border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <Phone class="w-5 h-5 text-gray-400" />
                    Contact Information
                </h3>
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                     <!-- Admin Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Admin Email <span class="text-red-500">*</span>
                        </label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 padding-l-3 flex items-center pl-3 pointer-events-none">
                                <Mail class="h-4 w-4 text-gray-400" />
                            </div>
                            <input
                            v-model="form.admin_email"
                            type="email"
                            class="block w-full pl-10 pr-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-shadow duration-200"
                            :class="{ 'border-red-500': form.errors.admin_email }"
                            placeholder="admin@example.com"
                            />
                        </div>
                        <p v-if="form.errors.admin_email" class="mt-1 text-sm text-red-600">{{ form.errors.admin_email }}</p>
                    </div>

                    <!-- Contact Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Contact Phone</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 padding-l-3 flex items-center pl-3 pointer-events-none">
                                <Phone class="h-4 w-4 text-gray-400" />
                            </div>
                            <input
                            v-model="form.contact_phone"
                            type="text"
                            class="block w-full pl-10 pr-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                            :class="{ 'border-red-500': form.errors.contact_phone }"
                            placeholder="+1 (555) 123-4567"
                            />
                        </div>
                        <p v-if="form.errors.contact_phone" class="mt-1 text-sm text-red-600">{{ form.errors.contact_phone }}</p>
                    </div>

                    <!-- Contact Address -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Contact Address</label>
                        <div class="relative rounded-lg shadow-sm">
                             <div class="absolute top-3 left-3 pointer-events-none">
                                <MapPin class="h-4 w-4 text-gray-400" />
                            </div>
                            <textarea
                            v-model="form.contact_address"
                            rows="3"
                            class="block w-full pl-10 pr-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                            :class="{ 'border-red-500': form.errors.contact_address }"
                            placeholder="Your business address"
                            ></textarea>
                        </div>
                        <p v-if="form.errors.contact_address" class="mt-1 text-sm text-red-600">{{ form.errors.contact_address }}</p>
                    </div>

                      <!-- Store Country -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                          Store Country <span class="text-red-500">*</span>
                        </label>
                        <div class="relative rounded-lg shadow-sm">
                          <div class="absolute inset-y-0 left-0 padding-l-3 flex items-center pl-3 pointer-events-none">
                            <Flag class="h-4 w-4 text-gray-400" />
                          </div>
                          <select
                            v-model="form.store_country"
                            class="block w-full pl-10 pr-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                            :class="{ 'border-red-500': form.errors.store_country }"
                          >
                            <option value="">Select country...</option>
                            <option value="India">India</option>
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="United States">United States</option>
                            <option value="Canada">Canada</option>
                            <option value="Australia">Australia</option>
                            <option value="Germany">Germany</option>
                            <option value="France">France</option>
                            <option value="United Arab Emirates">United Arab Emirates</option>
                          </select>
                        </div>
                        <p v-if="form.errors.store_country" class="mt-1 text-sm text-red-600">{{ form.errors.store_country }}</p>
                      </div>
                </div>
              </div>

              <!-- Branding Section -->
               <div class="pt-6 border-t border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <ImageIcon class="w-5 h-5 text-gray-400" />
                    Branding
                </h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <!-- Site Logo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Site Logo</label>
                        <div v-if="existingSiteLogo && siteLogoFiles.length === 0" class="mb-3 p-2 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-100 dark:border-gray-600 inline-block">
                        <img :src="`/storage/${existingSiteLogo}`" alt="Site logo" class="h-12 object-contain" />
                        </div>
                        <ImageUploader v-model="siteLogoFiles" :maxFiles="1" :maxSize="2" accept="image/*" />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Frontend header logo. Recommended: 200x60px.
                        </p>
                        <p v-if="form.errors.site_logo" class="mt-1 text-sm text-red-600">{{ form.errors.site_logo }}</p>
                    </div>

                    <!-- Admin Logo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Admin Logo</label>
                        <div v-if="existingAdminLogo && adminLogoFiles.length === 0" class="mb-3 p-2 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-100 dark:border-gray-600 inline-block">
                        <img :src="`/storage/${existingAdminLogo}`" alt="Admin logo" class="h-12 object-contain" />
                        </div>
                        <ImageUploader v-model="adminLogoFiles" :maxFiles="1" :maxSize="2" accept="image/*" />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Dashboard sidebar logo. Recommended: 150x40px.
                        </p>
                        <p v-if="form.errors.admin_logo" class="mt-1 text-sm text-red-600">{{ form.errors.admin_logo }}</p>
                    </div>

                    <!-- Site Favicon -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Site Favicon</label>
                        <div v-if="existingSiteFavicon && siteFaviconFiles.length === 0" class="mb-3 p-2 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-100 dark:border-gray-600 inline-block">
                        <img :src="`/storage/${existingSiteFavicon}`" alt="Site favicon" class="h-8 w-8 object-contain" />
                        </div>
                        <ImageUploader v-model="siteFaviconFiles" :maxFiles="1" :maxSize="1" accept="image/*" />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Browser tab icon. Recommended: 32x32px ICO/PNG.
                        </p>
                        <p v-if="form.errors.site_favicon" class="mt-1 text-sm text-red-600">{{ form.errors.site_favicon }}</p>
                    </div>
                </div>
              </div>
            </div>
          </div>

          <!-- SEO Settings Tab -->
          <div v-show="activeTab === 'seo'" class="p-6 sm:p-8">
            <div class="space-y-8">
               <!-- Basic SEO -->
               <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <Search class="w-5 h-5 text-gray-400" />
                    Search Engine Optimization
                  </h3>
                  <div class="space-y-6">
                      <!-- Meta Title -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Meta Title</label>
                        <input
                          v-model="form.meta_title"
                          type="text"
                          class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                          :class="{ 'border-red-500': form.errors.meta_title }"
                          placeholder="Your Store - Best Products Online"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommended length: 50-60 characters</p>
                        <p v-if="form.errors.meta_title" class="mt-1 text-sm text-red-600">{{ form.errors.meta_title }}</p>
                      </div>

                      <!-- Meta Description -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Meta Description</label>
                        <textarea
                          v-model="form.meta_description"
                          rows="3"
                          class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                          :class="{ 'border-red-500': form.errors.meta_description }"
                          placeholder="Brief description of your store for search engines"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommended length: 150-160 characters</p>
                        <p v-if="form.errors.meta_description" class="mt-1 text-sm text-red-600">{{ form.errors.meta_description }}</p>
                      </div>

                      <!-- Meta Keywords -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Meta Keywords</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 padding-l-3 flex items-center pl-3 pointer-events-none">
                                <Tag class="h-4 w-4 text-gray-400" />
                            </div>
                            <input
                            v-model="form.meta_keywords"
                            type="text"
                            class="block w-full pl-10 pr-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                            :class="{ 'border-red-500': form.errors.meta_keywords }"
                            placeholder="ecommerce, online store, products"
                            />
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Comma-separated keywords</p>
                        <p v-if="form.errors.meta_keywords" class="mt-1 text-sm text-red-600">{{ form.errors.meta_keywords }}</p>
                      </div>
                  </div>
               </div>

               <!-- Analytics Section -->
               <div class="pt-6 border-t border-gray-100 dark:border-gray-700">
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <BarChart class="w-5 h-5 text-gray-400" />
                    Analytics & Tracking
                  </h3>
                  <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                       <!-- Google Analytics ID -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Google Analytics ID</label>
                        <input
                          v-model="form.google_analytics_id"
                          type="text"
                          class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                          :class="{ 'border-red-500': form.errors.google_analytics_id }"
                          placeholder="G-XXXXXXXXXX"
                        />
                      </div>

                      <!-- Google Tag Manager ID -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Google Tag Manager ID</label>
                        <input
                          v-model="form.google_tag_manager_id"
                          type="text"
                          class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                          :class="{ 'border-red-500': form.errors.google_tag_manager_id }"
                          placeholder="GTM-XXXXXXX"
                        />
                      </div>

                      <!-- Facebook Pixel ID -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Facebook Pixel ID</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 padding-l-3 flex items-center pl-3 pointer-events-none">
                                <Facebook class="h-4 w-4 text-gray-400" />
                            </div>
                            <input
                            v-model="form.facebook_pixel_id"
                            type="text"
                            class="block w-full pl-10 pr-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                            :class="{ 'border-red-500': form.errors.facebook_pixel_id }"
                            placeholder="Pixel ID"
                            />
                        </div>
                      </div>
                  </div>
               </div>
            </div>
          </div>

          <!-- Bottom Actions -->
          <div class="bg-gray-50 dark:bg-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end">
             <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 border border-transparent rounded-xl text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <Save v-if="!form.processing" class="w-4 h-4" />
                <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ form.processing ? 'Saving...' : 'Save Settings' }}
              </button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

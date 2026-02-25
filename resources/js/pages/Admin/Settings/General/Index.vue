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
  Tag,
  Smartphone,
  X
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
const existingMobileAuthLogo = ref(props.settings['mobile_auth_logo'] || '')
const siteLogoFiles = ref<File[]>([])
const adminLogoFiles = ref<File[]>([])
const siteFaviconFiles = ref<File[]>([])
const mobileAuthLogoFiles = ref<File[]>([])

const removeSiteLogo = ref(false)
const removeAdminLogo = ref(false)
const removeSiteFavicon = ref(false)
const removeMobileAuthLogo = ref(false)

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
    }

    if (adminLogoFiles.value.length > 0) {
      payload.admin_logo = adminLogoFiles.value[0]
    }

    if (siteFaviconFiles.value.length > 0) {
      payload.site_favicon = siteFaviconFiles.value[0]
    }

    if (mobileAuthLogoFiles.value.length > 0) {
      payload.mobile_auth_logo = mobileAuthLogoFiles.value[0]
    }

    if (removeSiteLogo.value)       payload.remove_site_logo = '1'
    if (removeAdminLogo.value)      payload.remove_admin_logo = '1'
    if (removeSiteFavicon.value)    payload.remove_site_favicon = '1'
    if (removeMobileAuthLogo.value) payload.remove_mobile_auth_logo = '1'

    return payload
  })

  form.post('/admin/settings/general', {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      // Refresh existing-logo refs from the updated page props so the
      // newly-uploaded image is shown immediately without a page reload.
      existingSiteLogo.value = (props.settings['site_logo'] as string) || ''
      existingAdminLogo.value = (props.settings['admin_logo'] as string) || ''
      existingSiteFavicon.value = (props.settings['site_favicon'] as string) || ''
      existingMobileAuthLogo.value = (props.settings['mobile_auth_logo'] as string) || ''
      // Clear file pickers and remove flags
      siteLogoFiles.value = []
      adminLogoFiles.value = []
      siteFaviconFiles.value = []
      mobileAuthLogoFiles.value = []
      removeSiteLogo.value = false
      removeAdminLogo.value = false
      removeSiteFavicon.value = false
      removeMobileAuthLogo.value = false
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
                            <option value="Afghanistan">Afghanistan</option>
                            <option value="Albania">Albania</option>
                            <option value="Algeria">Algeria</option>
                            <option value="Andorra">Andorra</option>
                            <option value="Angola">Angola</option>
                            <option value="Argentina">Argentina</option>
                            <option value="Armenia">Armenia</option>
                            <option value="Australia">Australia</option>
                            <option value="Austria">Austria</option>
                            <option value="Azerbaijan">Azerbaijan</option>
                            <option value="Bahamas">Bahamas</option>
                            <option value="Bahrain">Bahrain</option>
                            <option value="Bangladesh">Bangladesh</option>
                            <option value="Belarus">Belarus</option>
                            <option value="Belgium">Belgium</option>
                            <option value="Belize">Belize</option>
                            <option value="Benin">Benin</option>
                            <option value="Bhutan">Bhutan</option>
                            <option value="Bolivia">Bolivia</option>
                            <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                            <option value="Botswana">Botswana</option>
                            <option value="Brazil">Brazil</option>
                            <option value="Brunei">Brunei</option>
                            <option value="Bulgaria">Bulgaria</option>
                            <option value="Burkina Faso">Burkina Faso</option>
                            <option value="Burundi">Burundi</option>
                            <option value="Cambodia">Cambodia</option>
                            <option value="Cameroon">Cameroon</option>
                            <option value="Canada">Canada</option>
                            <option value="Cape Verde">Cape Verde</option>
                            <option value="Central African Republic">Central African Republic</option>
                            <option value="Chad">Chad</option>
                            <option value="Chile">Chile</option>
                            <option value="China">China</option>
                            <option value="Colombia">Colombia</option>
                            <option value="Comoros">Comoros</option>
                            <option value="Congo">Congo</option>
                            <option value="Costa Rica">Costa Rica</option>
                            <option value="Croatia">Croatia</option>
                            <option value="Cuba">Cuba</option>
                            <option value="Cyprus">Cyprus</option>
                            <option value="Czech Republic">Czech Republic</option>
                            <option value="Denmark">Denmark</option>
                            <option value="Djibouti">Djibouti</option>
                            <option value="Dominican Republic">Dominican Republic</option>
                            <option value="Ecuador">Ecuador</option>
                            <option value="Egypt">Egypt</option>
                            <option value="El Salvador">El Salvador</option>
                            <option value="Equatorial Guinea">Equatorial Guinea</option>
                            <option value="Eritrea">Eritrea</option>
                            <option value="Estonia">Estonia</option>
                            <option value="Eswatini">Eswatini</option>
                            <option value="Ethiopia">Ethiopia</option>
                            <option value="Fiji">Fiji</option>
                            <option value="Finland">Finland</option>
                            <option value="France">France</option>
                            <option value="Gabon">Gabon</option>
                            <option value="Gambia">Gambia</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Germany">Germany</option>
                            <option value="Ghana">Ghana</option>
                            <option value="Greece">Greece</option>
                            <option value="Guatemala">Guatemala</option>
                            <option value="Guinea">Guinea</option>
                            <option value="Guinea-Bissau">Guinea-Bissau</option>
                            <option value="Guyana">Guyana</option>
                            <option value="Haiti">Haiti</option>
                            <option value="Honduras">Honduras</option>
                            <option value="Hungary">Hungary</option>
                            <option value="Iceland">Iceland</option>
                            <option value="India">India</option>
                            <option value="Indonesia">Indonesia</option>
                            <option value="Iran">Iran</option>
                            <option value="Iraq">Iraq</option>
                            <option value="Ireland">Ireland</option>
                            <option value="Israel">Israel</option>
                            <option value="Italy">Italy</option>
                            <option value="Jamaica">Jamaica</option>
                            <option value="Japan">Japan</option>
                            <option value="Jordan">Jordan</option>
                            <option value="Kazakhstan">Kazakhstan</option>
                            <option value="Kenya">Kenya</option>
                            <option value="Kuwait">Kuwait</option>
                            <option value="Kyrgyzstan">Kyrgyzstan</option>
                            <option value="Laos">Laos</option>
                            <option value="Latvia">Latvia</option>
                            <option value="Lebanon">Lebanon</option>
                            <option value="Lesotho">Lesotho</option>
                            <option value="Liberia">Liberia</option>
                            <option value="Libya">Libya</option>
                            <option value="Liechtenstein">Liechtenstein</option>
                            <option value="Lithuania">Lithuania</option>
                            <option value="Luxembourg">Luxembourg</option>
                            <option value="Madagascar">Madagascar</option>
                            <option value="Malawi">Malawi</option>
                            <option value="Malaysia">Malaysia</option>
                            <option value="Maldives">Maldives</option>
                            <option value="Mali">Mali</option>
                            <option value="Malta">Malta</option>
                            <option value="Mauritania">Mauritania</option>
                            <option value="Mauritius">Mauritius</option>
                            <option value="Mexico">Mexico</option>
                            <option value="Moldova">Moldova</option>
                            <option value="Monaco">Monaco</option>
                            <option value="Mongolia">Mongolia</option>
                            <option value="Montenegro">Montenegro</option>
                            <option value="Morocco">Morocco</option>
                            <option value="Mozambique">Mozambique</option>
                            <option value="Myanmar">Myanmar</option>
                            <option value="Namibia">Namibia</option>
                            <option value="Nepal">Nepal</option>
                            <option value="Netherlands">Netherlands</option>
                            <option value="New Zealand">New Zealand</option>
                            <option value="Nicaragua">Nicaragua</option>
                            <option value="Niger">Niger</option>
                            <option value="Nigeria">Nigeria</option>
                            <option value="North Macedonia">North Macedonia</option>
                            <option value="Norway">Norway</option>
                            <option value="Oman">Oman</option>
                            <option value="Pakistan">Pakistan</option>
                            <option value="Panama">Panama</option>
                            <option value="Papua New Guinea">Papua New Guinea</option>
                            <option value="Paraguay">Paraguay</option>
                            <option value="Peru">Peru</option>
                            <option value="Philippines">Philippines</option>
                            <option value="Poland">Poland</option>
                            <option value="Portugal">Portugal</option>
                            <option value="Qatar">Qatar</option>
                            <option value="Romania">Romania</option>
                            <option value="Russia">Russia</option>
                            <option value="Rwanda">Rwanda</option>
                            <option value="Saudi Arabia">Saudi Arabia</option>
                            <option value="Senegal">Senegal</option>
                            <option value="Serbia">Serbia</option>
                            <option value="Sierra Leone">Sierra Leone</option>
                            <option value="Singapore">Singapore</option>
                            <option value="Slovakia">Slovakia</option>
                            <option value="Slovenia">Slovenia</option>
                            <option value="Somalia">Somalia</option>
                            <option value="South Africa">South Africa</option>
                            <option value="South Korea">South Korea</option>
                            <option value="South Sudan">South Sudan</option>
                            <option value="Spain">Spain</option>
                            <option value="Sri Lanka">Sri Lanka</option>
                            <option value="Sudan">Sudan</option>
                            <option value="Suriname">Suriname</option>
                            <option value="Sweden">Sweden</option>
                            <option value="Switzerland">Switzerland</option>
                            <option value="Syria">Syria</option>
                            <option value="Taiwan">Taiwan</option>
                            <option value="Tajikistan">Tajikistan</option>
                            <option value="Tanzania">Tanzania</option>
                            <option value="Thailand">Thailand</option>
                            <option value="Togo">Togo</option>
                            <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                            <option value="Tunisia">Tunisia</option>
                            <option value="Turkey">Turkey</option>
                            <option value="Turkmenistan">Turkmenistan</option>
                            <option value="Uganda">Uganda</option>
                            <option value="Ukraine">Ukraine</option>
                            <option value="United Arab Emirates">United Arab Emirates</option>
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="United States">United States</option>
                            <option value="Uruguay">Uruguay</option>
                            <option value="Uzbekistan">Uzbekistan</option>
                            <option value="Venezuela">Venezuela</option>
                            <option value="Vietnam">Vietnam</option>
                            <option value="Yemen">Yemen</option>
                            <option value="Zambia">Zambia</option>
                            <option value="Zimbabwe">Zimbabwe</option>
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
                        <div v-if="existingSiteLogo && siteLogoFiles.length === 0 && !removeSiteLogo" class="mb-3 p-2 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-100 dark:border-gray-600 flex items-center gap-2">
                            <img :src="`/storage/${existingSiteLogo}`" alt="Site logo" class="h-12 object-contain" />
                            <button type="button" @click="removeSiteLogo = true" class="ml-1 p-1 text-gray-400 hover:text-red-500 rounded transition-colors" title="Remove logo"><X class="w-4 h-4" /></button>
                        </div>
                        <div v-else-if="removeSiteLogo && siteLogoFiles.length === 0" class="mb-3 px-3 py-2 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800 flex items-center gap-2 text-sm text-red-600 dark:text-red-400">
                            <span>Logo will be removed on save.</span>
                            <button type="button" @click="removeSiteLogo = false" class="underline text-xs">Undo</button>
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
                        <div v-if="existingAdminLogo && adminLogoFiles.length === 0 && !removeAdminLogo" class="mb-3 p-2 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-100 dark:border-gray-600 flex items-center gap-2">
                            <img :src="`/storage/${existingAdminLogo}`" alt="Admin logo" class="h-12 object-contain" />
                            <button type="button" @click="removeAdminLogo = true" class="ml-1 p-1 text-gray-400 hover:text-red-500 rounded transition-colors" title="Remove logo"><X class="w-4 h-4" /></button>
                        </div>
                        <div v-else-if="removeAdminLogo && adminLogoFiles.length === 0" class="mb-3 px-3 py-2 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800 flex items-center gap-2 text-sm text-red-600 dark:text-red-400">
                            <span>Logo will be removed on save.</span>
                            <button type="button" @click="removeAdminLogo = false" class="underline text-xs">Undo</button>
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
                        <div v-if="existingSiteFavicon && siteFaviconFiles.length === 0 && !removeSiteFavicon" class="mb-3 p-2 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-100 dark:border-gray-600 flex items-center gap-2">
                            <img :src="`/storage/${existingSiteFavicon}`" alt="Site favicon" class="h-8 w-8 object-contain" />
                            <button type="button" @click="removeSiteFavicon = true" class="ml-1 p-1 text-gray-400 hover:text-red-500 rounded transition-colors" title="Remove favicon"><X class="w-4 h-4" /></button>
                        </div>
                        <div v-else-if="removeSiteFavicon && siteFaviconFiles.length === 0" class="mb-3 px-3 py-2 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800 flex items-center gap-2 text-sm text-red-600 dark:text-red-400">
                            <span>Favicon will be removed on save.</span>
                            <button type="button" @click="removeSiteFavicon = false" class="underline text-xs">Undo</button>
                        </div>
                        <ImageUploader v-model="siteFaviconFiles" :maxFiles="1" :maxSize="1" accept="image/*" />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Browser tab icon. Recommended: 32x32px ICO/PNG.
                        </p>
                        <p v-if="form.errors.site_favicon" class="mt-1 text-sm text-red-600">{{ form.errors.site_favicon }}</p>
                    </div>
                </div>
              </div>

              <!-- Mobile App Branding Section -->
              <div class="pt-6 border-t border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1 flex items-center gap-2">
                    <Smartphone class="w-5 h-5 text-gray-400" />
                    Mobile App Branding
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Assets displayed in the mobile application.</p>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <!-- Mobile Auth Logo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Auth Screen Logo</label>
                        <div v-if="existingMobileAuthLogo && mobileAuthLogoFiles.length === 0 && !removeMobileAuthLogo" class="mb-3 p-2 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-100 dark:border-gray-600 flex items-center gap-2">
                            <img :src="`/storage/${existingMobileAuthLogo}`" alt="Mobile auth logo" class="h-12 object-contain" />
                            <button type="button" @click="removeMobileAuthLogo = true" class="ml-1 p-1 text-gray-400 hover:text-red-500 rounded transition-colors" title="Remove logo"><X class="w-4 h-4" /></button>
                        </div>
                        <div v-else-if="removeMobileAuthLogo && mobileAuthLogoFiles.length === 0" class="mb-3 px-3 py-2 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800 flex items-center gap-2 text-sm text-red-600 dark:text-red-400">
                            <span>Logo will be removed on save.</span>
                            <button type="button" @click="removeMobileAuthLogo = false" class="underline text-xs">Undo</button>
                        </div>
                        <ImageUploader v-model="mobileAuthLogoFiles" :maxFiles="1" :maxSize="2" accept="image/*" />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Shown on login &amp; register screens. Recommended: 300x100px PNG/SVG.
                        </p>
                        <p v-if="form.errors.mobile_auth_logo" class="mt-1 text-sm text-red-600">{{ form.errors.mobile_auth_logo }}</p>
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

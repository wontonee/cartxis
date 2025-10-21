<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import TiptapEditor from '@/components/Admin/TiptapEditor.vue'

interface Props {
  settings?: {
    store_name: string
    store_description: string
    business_registration: string
    vat_number: string
    store_email: string
    support_email: string
    store_phone: string
    store_phone_alt: string
    store_whatsapp: string
    store_address_1: string
    store_address_2: string
    store_city: string
    store_state: string
    store_postal_code: string
    store_country: string
    store_timezone: string
    social_facebook: string
    social_instagram: string
    social_twitter: string
    social_linkedin: string
    social_youtube: string
    social_tiktok: string
    social_pinterest: string
    policy_privacy: string
    policy_terms: string
    policy_return: string
    policy_shipping: string
  }
}

const props = withDefaults(defineProps<Props>(), {
  settings: () => ({
    store_name: '',
    store_description: '',
    business_registration: '',
    vat_number: '',
    store_email: '',
    support_email: '',
    store_phone: '',
    store_phone_alt: '',
    store_whatsapp: '',
    store_address_1: '',
    store_address_2: '',
    store_city: '',
    store_state: '',
    store_postal_code: '',
    store_country: '',
    store_timezone: 'UTC',
    social_facebook: '',
    social_instagram: '',
    social_twitter: '',
    social_linkedin: '',
    social_youtube: '',
    social_tiktok: '',
    social_pinterest: '',
    policy_privacy: '',
    policy_terms: '',
    policy_return: '',
    policy_shipping: '',
  }),
})

const activeTab = ref('details')

const form = useForm({
  store_name: props.settings.store_name || '',
  store_description: props.settings.store_description || '',
  business_registration: props.settings.business_registration || '',
  vat_number: props.settings.vat_number || '',
  store_email: props.settings.store_email || '',
  support_email: props.settings.support_email || '',
  store_phone: props.settings.store_phone || '',
  store_phone_alt: props.settings.store_phone_alt || '',
  store_whatsapp: props.settings.store_whatsapp || '',
  store_address_1: props.settings.store_address_1 || '',
  store_address_2: props.settings.store_address_2 || '',
  store_city: props.settings.store_city || '',
  store_state: props.settings.store_state || '',
  store_postal_code: props.settings.store_postal_code || '',
  store_country: props.settings.store_country || '',
  store_timezone: props.settings.store_timezone || 'UTC',
  social_facebook: props.settings.social_facebook || '',
  social_instagram: props.settings.social_instagram || '',
  social_twitter: props.settings.social_twitter || '',
  social_linkedin: props.settings.social_linkedin || '',
  social_youtube: props.settings.social_youtube || '',
  social_tiktok: props.settings.social_tiktok || '',
  social_pinterest: props.settings.social_pinterest || '',
  policy_privacy: props.settings.policy_privacy || '',
  policy_terms: props.settings.policy_terms || '',
  policy_return: props.settings.policy_return || '',
  policy_shipping: props.settings.policy_shipping || '',
})

const save = () => {
  form.post('/admin/settings/store', {
    preserveScroll: true,
  })
}
</script>

<template>
  <AdminLayout title="Store Configuration">
    <Head title="Store Configuration" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Store Configuration</h1>
        <p class="mt-1 text-sm text-gray-600">
          Manage store details, contact information, social media links, and policies
        </p>
      </div>

      <form @submit.prevent="save" novalidate>
        <div class="bg-white rounded-lg shadow">
          <!-- Tabs Navigation -->
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
              <button
                type="button"
                @click="activeTab = 'details'"
                :class="[
                  activeTab === 'details'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Store Details
              </button>
              <button
                type="button"
                @click="activeTab = 'contact'"
                :class="[
                  activeTab === 'contact'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Contact & Address
              </button>
              <button
                type="button"
                @click="activeTab = 'social'"
                :class="[
                  activeTab === 'social'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Social Media
              </button>
              <button
                type="button"
                @click="activeTab = 'policies'"
                :class="[
                  activeTab === 'policies'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Policies
              </button>
            </nav>
          </div>

          <!-- Store Details Tab -->
          <div v-show="activeTab === 'details'" class="p-6">
            <div class="space-y-6">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                  <label for="store_name" class="block text-sm font-medium text-gray-700">
                    Business Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="store_name"
                    v-model="form.store_name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                  />
                  <p v-if="form.errors.store_name" class="mt-1 text-sm text-red-600">{{ form.errors.store_name }}</p>
                </div>

                <div>
                  <label for="business_registration" class="block text-sm font-medium text-gray-700">
                    Business Registration Number
                  </label>
                  <input
                    id="business_registration"
                    v-model="form.business_registration"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                  <p v-if="form.errors.business_registration" class="mt-1 text-sm text-red-600">{{ form.errors.business_registration }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                  <label for="vat_number" class="block text-sm font-medium text-gray-700">
                    VAT Number
                  </label>
                  <input
                    id="vat_number"
                    v-model="form.vat_number"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                  <p v-if="form.errors.vat_number" class="mt-1 text-sm text-red-600">{{ form.errors.vat_number }}</p>
                </div>

                <div>
                  <label for="store_timezone" class="block text-sm font-medium text-gray-700">
                    Store Timezone <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="store_timezone"
                    v-model="form.store_timezone"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                  >
                    <option value="UTC">UTC</option>
                    <option value="America/New_York">America/New York</option>
                    <option value="America/Chicago">America/Chicago</option>
                    <option value="America/Denver">America/Denver</option>
                    <option value="America/Los_Angeles">America/Los Angeles</option>
                    <option value="Europe/London">Europe/London</option>
                    <option value="Europe/Paris">Europe/Paris</option>
                    <option value="Asia/Dubai">Asia/Dubai</option>
                    <option value="Asia/Kolkata">Asia/Kolkata</option>
                    <option value="Asia/Singapore">Asia/Singapore</option>
                    <option value="Asia/Tokyo">Asia/Tokyo</option>
                    <option value="Australia/Sydney">Australia/Sydney</option>
                  </select>
                  <p v-if="form.errors.store_timezone" class="mt-1 text-sm text-red-600">{{ form.errors.store_timezone }}</p>
                </div>
              </div>

              <div>
                <label for="store_description" class="block text-sm font-medium text-gray-700">
                  Business Description
                </label>
                <textarea
                  id="store_description"
                  v-model="form.store_description"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Brief description of your business for SEO purposes..."
                ></textarea>
                <p v-if="form.errors.store_description" class="mt-1 text-sm text-red-600">{{ form.errors.store_description }}</p>
              </div>
            </div>
          </div>

          <!-- Contact & Address Tab -->
          <div v-show="activeTab === 'contact'" class="p-6">
            <div class="space-y-6">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                  <label for="store_email" class="block text-sm font-medium text-gray-700">
                    Primary Email <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="store_email"
                    v-model="form.store_email"
                    type="email"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                  />
                  <p v-if="form.errors.store_email" class="mt-1 text-sm text-red-600">{{ form.errors.store_email }}</p>
                </div>

                <div>
                  <label for="support_email" class="block text-sm font-medium text-gray-700">
                    Support Email
                  </label>
                  <input
                    id="support_email"
                    v-model="form.support_email"
                    type="email"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                  <p v-if="form.errors.support_email" class="mt-1 text-sm text-red-600">{{ form.errors.support_email }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                  <label for="store_phone" class="block text-sm font-medium text-gray-700">
                    Phone Number <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="store_phone"
                    v-model="form.store_phone"
                    type="tel"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                  />
                  <p v-if="form.errors.store_phone" class="mt-1 text-sm text-red-600">{{ form.errors.store_phone }}</p>
                </div>

                <div>
                  <label for="store_phone_alt" class="block text-sm font-medium text-gray-700">
                    Alternate Phone
                  </label>
                  <input
                    id="store_phone_alt"
                    v-model="form.store_phone_alt"
                    type="tel"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                  <p v-if="form.errors.store_phone_alt" class="mt-1 text-sm text-red-600">{{ form.errors.store_phone_alt }}</p>
                </div>

                <div>
                  <label for="store_whatsapp" class="block text-sm font-medium text-gray-700">
                    WhatsApp Number
                  </label>
                  <input
                    id="store_whatsapp"
                    v-model="form.store_whatsapp"
                    type="tel"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                  <p v-if="form.errors.store_whatsapp" class="mt-1 text-sm text-red-600">{{ form.errors.store_whatsapp }}</p>
                </div>
              </div>

              <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Store Address</h3>
                
                <div class="space-y-6">
                  <div>
                    <label for="store_address_1" class="block text-sm font-medium text-gray-700">
                      Street Address <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="store_address_1"
                      v-model="form.store_address_1"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      required
                    />
                    <p v-if="form.errors.store_address_1" class="mt-1 text-sm text-red-600">{{ form.errors.store_address_1 }}</p>
                  </div>

                  <div>
                    <label for="store_address_2" class="block text-sm font-medium text-gray-700">
                      Address Line 2
                    </label>
                    <input
                      id="store_address_2"
                      v-model="form.store_address_2"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <p v-if="form.errors.store_address_2" class="mt-1 text-sm text-red-600">{{ form.errors.store_address_2 }}</p>
                  </div>

                  <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div>
                      <label for="store_city" class="block text-sm font-medium text-gray-700">
                        City <span class="text-red-500">*</span>
                      </label>
                      <input
                        id="store_city"
                        v-model="form.store_city"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                      />
                      <p v-if="form.errors.store_city" class="mt-1 text-sm text-red-600">{{ form.errors.store_city }}</p>
                    </div>

                    <div>
                      <label for="store_state" class="block text-sm font-medium text-gray-700">
                        State/Province <span class="text-red-500">*</span>
                      </label>
                      <input
                        id="store_state"
                        v-model="form.store_state"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                      />
                      <p v-if="form.errors.store_state" class="mt-1 text-sm text-red-600">{{ form.errors.store_state }}</p>
                    </div>

                    <div>
                      <label for="store_postal_code" class="block text-sm font-medium text-gray-700">
                        Postal Code <span class="text-red-500">*</span>
                      </label>
                      <input
                        id="store_postal_code"
                        v-model="form.store_postal_code"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                      />
                      <p v-if="form.errors.store_postal_code" class="mt-1 text-sm text-red-600">{{ form.errors.store_postal_code }}</p>
                    </div>
                  </div>

                  <div>
                    <label for="store_country" class="block text-sm font-medium text-gray-700">
                      Country <span class="text-red-500">*</span>
                    </label>
                    <select
                      id="store_country"
                      v-model="form.store_country"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      required
                    >
                      <option value="">Select a country...</option>
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
                      <option value="Barbados">Barbados</option>
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
                      <option value="Dominica">Dominica</option>
                      <option value="Dominican Republic">Dominican Republic</option>
                      <option value="East Timor">East Timor</option>
                      <option value="Ecuador">Ecuador</option>
                      <option value="Egypt">Egypt</option>
                      <option value="El Salvador">El Salvador</option>
                      <option value="Equatorial Guinea">Equatorial Guinea</option>
                      <option value="Eritrea">Eritrea</option>
                      <option value="Estonia">Estonia</option>
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
                      <option value="Grenada">Grenada</option>
                      <option value="Guatemala">Guatemala</option>
                      <option value="Guinea">Guinea</option>
                      <option value="Guinea-Bissau">Guinea-Bissau</option>
                      <option value="Guyana">Guyana</option>
                      <option value="Haiti">Haiti</option>
                      <option value="Honduras">Honduras</option>
                      <option value="Hong Kong">Hong Kong</option>
                      <option value="Hungary">Hungary</option>
                      <option value="Iceland">Iceland</option>
                      <option value="India">India</option>
                      <option value="Indonesia">Indonesia</option>
                      <option value="Iran">Iran</option>
                      <option value="Iraq">Iraq</option>
                      <option value="Ireland">Ireland</option>
                      <option value="Israel">Israel</option>
                      <option value="Italy">Italy</option>
                      <option value="Ivory Coast">Ivory Coast</option>
                      <option value="Jamaica">Jamaica</option>
                      <option value="Japan">Japan</option>
                      <option value="Jordan">Jordan</option>
                      <option value="Kazakhstan">Kazakhstan</option>
                      <option value="Kenya">Kenya</option>
                      <option value="Kiribati">Kiribati</option>
                      <option value="Korea, North">Korea, North</option>
                      <option value="Korea, South">Korea, South</option>
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
                      <option value="Macau">Macau</option>
                      <option value="Macedonia">Macedonia</option>
                      <option value="Madagascar">Madagascar</option>
                      <option value="Malawi">Malawi</option>
                      <option value="Malaysia">Malaysia</option>
                      <option value="Maldives">Maldives</option>
                      <option value="Mali">Mali</option>
                      <option value="Malta">Malta</option>
                      <option value="Marshall Islands">Marshall Islands</option>
                      <option value="Mauritania">Mauritania</option>
                      <option value="Mauritius">Mauritius</option>
                      <option value="Mexico">Mexico</option>
                      <option value="Micronesia">Micronesia</option>
                      <option value="Moldova">Moldova</option>
                      <option value="Monaco">Monaco</option>
                      <option value="Mongolia">Mongolia</option>
                      <option value="Montenegro">Montenegro</option>
                      <option value="Morocco">Morocco</option>
                      <option value="Mozambique">Mozambique</option>
                      <option value="Myanmar">Myanmar</option>
                      <option value="Namibia">Namibia</option>
                      <option value="Nauru">Nauru</option>
                      <option value="Nepal">Nepal</option>
                      <option value="Netherlands">Netherlands</option>
                      <option value="New Zealand">New Zealand</option>
                      <option value="Nicaragua">Nicaragua</option>
                      <option value="Niger">Niger</option>
                      <option value="Nigeria">Nigeria</option>
                      <option value="Norway">Norway</option>
                      <option value="Oman">Oman</option>
                      <option value="Pakistan">Pakistan</option>
                      <option value="Palau">Palau</option>
                      <option value="Palestine">Palestine</option>
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
                      <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                      <option value="Saint Lucia">Saint Lucia</option>
                      <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
                      <option value="Samoa">Samoa</option>
                      <option value="San Marino">San Marino</option>
                      <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                      <option value="Saudi Arabia">Saudi Arabia</option>
                      <option value="Senegal">Senegal</option>
                      <option value="Serbia">Serbia</option>
                      <option value="Seychelles">Seychelles</option>
                      <option value="Sierra Leone">Sierra Leone</option>
                      <option value="Singapore">Singapore</option>
                      <option value="Slovakia">Slovakia</option>
                      <option value="Slovenia">Slovenia</option>
                      <option value="Solomon Islands">Solomon Islands</option>
                      <option value="Somalia">Somalia</option>
                      <option value="South Africa">South Africa</option>
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
                      <option value="Tonga">Tonga</option>
                      <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                      <option value="Tunisia">Tunisia</option>
                      <option value="Turkey">Turkey</option>
                      <option value="Turkmenistan">Turkmenistan</option>
                      <option value="Tuvalu">Tuvalu</option>
                      <option value="Uganda">Uganda</option>
                      <option value="Ukraine">Ukraine</option>
                      <option value="United Arab Emirates">United Arab Emirates</option>
                      <option value="United Kingdom">United Kingdom</option>
                      <option value="United States">United States</option>
                      <option value="Uruguay">Uruguay</option>
                      <option value="Uzbekistan">Uzbekistan</option>
                      <option value="Vanuatu">Vanuatu</option>
                      <option value="Vatican City">Vatican City</option>
                      <option value="Venezuela">Venezuela</option>
                      <option value="Vietnam">Vietnam</option>
                      <option value="Yemen">Yemen</option>
                      <option value="Zambia">Zambia</option>
                      <option value="Zimbabwe">Zimbabwe</option>
                    </select>
                    <p v-if="form.errors.store_country" class="mt-1 text-sm text-red-600">{{ form.errors.store_country }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Social Media Tab -->
          <div v-show="activeTab === 'social'" class="p-6">
            <div class="space-y-6">
              <div>
                <label for="social_facebook" class="block text-sm font-medium text-gray-700">
                  Facebook
                </label>
                <input
                  id="social_facebook"
                  v-model="form.social_facebook"
                  type="url"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="https://facebook.com/yourstore"
                />
                <p v-if="form.errors.social_facebook" class="mt-1 text-sm text-red-600">{{ form.errors.social_facebook }}</p>
              </div>

              <div>
                <label for="social_instagram" class="block text-sm font-medium text-gray-700">
                  Instagram
                </label>
                <input
                  id="social_instagram"
                  v-model="form.social_instagram"
                  type="url"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="https://instagram.com/yourstore"
                />
                <p v-if="form.errors.social_instagram" class="mt-1 text-sm text-red-600">{{ form.errors.social_instagram }}</p>
              </div>

              <div>
                <label for="social_twitter" class="block text-sm font-medium text-gray-700">
                  Twitter/X
                </label>
                <input
                  id="social_twitter"
                  v-model="form.social_twitter"
                  type="url"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="https://twitter.com/yourstore"
                />
                <p v-if="form.errors.social_twitter" class="mt-1 text-sm text-red-600">{{ form.errors.social_twitter }}</p>
              </div>

              <div>
                <label for="social_linkedin" class="block text-sm font-medium text-gray-700">
                  LinkedIn
                </label>
                <input
                  id="social_linkedin"
                  v-model="form.social_linkedin"
                  type="url"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="https://linkedin.com/company/yourstore"
                />
                <p v-if="form.errors.social_linkedin" class="mt-1 text-sm text-red-600">{{ form.errors.social_linkedin }}</p>
              </div>

              <div>
                <label for="social_youtube" class="block text-sm font-medium text-gray-700">
                  YouTube
                </label>
                <input
                  id="social_youtube"
                  v-model="form.social_youtube"
                  type="url"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="https://youtube.com/@yourstore"
                />
                <p v-if="form.errors.social_youtube" class="mt-1 text-sm text-red-600">{{ form.errors.social_youtube }}</p>
              </div>

              <div>
                <label for="social_tiktok" class="block text-sm font-medium text-gray-700">
                  TikTok
                </label>
                <input
                  id="social_tiktok"
                  v-model="form.social_tiktok"
                  type="url"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="https://tiktok.com/@yourstore"
                />
                <p v-if="form.errors.social_tiktok" class="mt-1 text-sm text-red-600">{{ form.errors.social_tiktok }}</p>
              </div>

              <div>
                <label for="social_pinterest" class="block text-sm font-medium text-gray-700">
                  Pinterest
                </label>
                <input
                  id="social_pinterest"
                  v-model="form.social_pinterest"
                  type="url"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="https://pinterest.com/yourstore"
                />
                <p v-if="form.errors.social_pinterest" class="mt-1 text-sm text-red-600">{{ form.errors.social_pinterest }}</p>
              </div>
            </div>
          </div>

          <!-- Policies Tab -->
          <div v-show="activeTab === 'policies'" class="p-6">
            <div class="space-y-8">
              <div>
                <label for="policy_privacy" class="block text-sm font-medium text-gray-700 mb-2">
                  Privacy Policy
                </label>
                <TiptapEditor
                  v-model="form.policy_privacy"
                  placeholder="Enter your privacy policy content..."
                />
                <p v-if="form.errors.policy_privacy" class="mt-1 text-sm text-red-600">{{ form.errors.policy_privacy }}</p>
              </div>

              <div>
                <label for="policy_terms" class="block text-sm font-medium text-gray-700 mb-2">
                  Terms & Conditions
                </label>
                <TiptapEditor
                  v-model="form.policy_terms"
                  placeholder="Enter your terms and conditions..."
                />
                <p v-if="form.errors.policy_terms" class="mt-1 text-sm text-red-600">{{ form.errors.policy_terms }}</p>
              </div>

              <div>
                <label for="policy_return" class="block text-sm font-medium text-gray-700 mb-2">
                  Return Policy
                </label>
                <TiptapEditor
                  v-model="form.policy_return"
                  placeholder="Enter your return and refund policy..."
                />
                <p v-if="form.errors.policy_return" class="mt-1 text-sm text-red-600">{{ form.errors.policy_return }}</p>
              </div>

              <div>
                <label for="policy_shipping" class="block text-sm font-medium text-gray-700 mb-2">
                  Shipping Policy
                </label>
                <TiptapEditor
                  v-model="form.policy_shipping"
                  placeholder="Enter your shipping terms and conditions..."
                />
                <p v-if="form.errors.policy_shipping" class="mt-1 text-sm text-red-600">{{ form.errors.policy_shipping }}</p>
              </div>
            </div>
          </div>

          <!-- Save Button -->
          <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end rounded-b-lg">
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
              {{ form.processing ? 'Saving...' : 'Save Configuration' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>


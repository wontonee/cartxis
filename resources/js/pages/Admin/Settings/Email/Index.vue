<template>
  <AdminLayout title="Email Settings">
    <template #default>
      <Head title="Email Settings" />
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Email Settings</h1>
        <p class="mt-2 text-sm text-gray-600">Configure email delivery and manage email templates</p>
      </div>

      <!-- Tabs -->
      <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Configuration Tab -->
          <div v-show="activeTab === 'configuration'">
            <form @submit.prevent="saveConfiguration">
              <!-- General Settings -->
              <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">General Settings</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mail Driver</label>
                    <select
                      v-model="form.mail_driver"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option v-for="(label, value) in drivers" :key="value" :value="value">{{ label }}</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Email</label>
                    <input
                      v-model="form.mail_from_address"
                      type="email"
                      required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Name</label>
                    <input
                      v-model="form.mail_from_name"
                      type="text"
                      required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reply-To Email (Optional)</label>
                    <input
                      v-model="form.reply_to_email"
                      type="email"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                </div>
              </div>

              <!-- SMTP Configuration -->
              <div v-if="form.mail_driver === 'smtp'" class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">SMTP Configuration</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                    <input
                      v-model="form.smtp_host"
                      type="text"
                      placeholder="smtp.gmail.com"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                    <input
                      v-model="form.smtp_port"
                      type="number"
                      placeholder="587"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Username</label>
                    <input
                      v-model="form.smtp_username"
                      type="text"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Password</label>
                    <input
                      v-model="form.smtp_password"
                      :type="showPassword ? 'text' : 'password'"
                      placeholder="••••••••"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <label class="mt-2 flex items-center text-sm text-gray-600">
                      <input v-model="showPassword" type="checkbox" class="mr-2" />
                      Show password
                    </label>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Encryption</label>
                    <select
                      v-model="form.smtp_encryption"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="tls">TLS</option>
                      <option value="ssl">SSL</option>
                      <option value="none">None</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Amazon SES Configuration -->
              <div v-if="form.mail_driver === 'ses'" class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Amazon SES Configuration</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Access Key ID</label>
                    <input
                      v-model="form.ses_key"
                      type="text"
                      :placeholder="configuration.ses_key_masked || 'AKIA...'"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Secret Access Key</label>
                    <input
                      v-model="form.ses_secret"
                      :type="showSesSecret ? 'text' : 'password'"
                      :placeholder="configuration.ses_secret_masked || '••••••••'"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <label class="mt-2 flex items-center text-sm text-gray-600">
                      <input v-model="showSesSecret" type="checkbox" class="mr-2" />
                      Show secret
                    </label>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                    <select
                      v-model="form.ses_region"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="us-east-1">US East (N. Virginia)</option>
                      <option value="us-west-2">US West (Oregon)</option>
                      <option value="eu-west-1">EU (Ireland)</option>
                      <option value="ap-southeast-1">Asia Pacific (Singapore)</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Postmark Configuration -->
              <div v-if="form.mail_driver === 'postmark'" class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Postmark Configuration</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Server Token</label>
                    <input
                      v-model="form.postmark_token"
                      :type="showPostmarkToken ? 'text' : 'password'"
                      :placeholder="configuration.postmark_token_masked || '••••••••'"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <label class="mt-2 flex items-center text-sm text-gray-600">
                      <input v-model="showPostmarkToken" type="checkbox" class="mr-2" />
                      Show token
                    </label>
                  </div>
                </div>
              </div>

              <!-- Test Connection Status -->
              <div v-if="configuration.last_test_at" class="mb-6 p-4 rounded-lg" :class="configuration.last_test_status === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                <div class="flex items-center">
                  <svg v-if="configuration.last_test_status === 'success'" class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                  <div>
                    <p :class="configuration.last_test_status === 'success' ? 'text-green-800' : 'text-red-800'" class="text-sm font-medium">
                      {{ configuration.last_test_message }}
                    </p>
                    <p class="text-xs text-gray-600 mt-1">Last tested: {{ formatDate(configuration.last_test_at) }}</p>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex space-x-4">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                >
                  {{ form.processing ? 'Saving...' : 'Save Configuration' }}
                </button>
                <button
                  type="button"
                  @click="testConnection"
                  :disabled="testingConnection"
                  class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
                >
                  {{ testingConnection ? 'Testing...' : 'Test Connection' }}
                </button>
                <button
                  type="button"
                  @click="showSendTestDialog = true"
                  class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
                >
                  Send Test Email
                </button>
              </div>
            </form>
          </div>

          <!-- Email Templates Tab -->
          <div v-show="activeTab === 'templates'">
            <div v-for="(categoryTemplates, category) in templates" :key="category" class="mb-8">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ categories[category] }}</h3>
              
              <div class="space-y-3">
                <div
                  v-for="template in categoryTemplates"
                  :key="template.id"
                  class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  <div class="flex items-center space-x-4">
                    <div :class="template.is_active ? 'bg-green-500' : 'bg-gray-400'" class="w-2 h-2 rounded-full"></div>
                    <div>
                      <h4 class="font-medium text-gray-900">{{ template.name }}</h4>
                      <p class="text-sm text-gray-600">{{ template.description }}</p>
                      <p class="text-xs text-gray-500 mt-1">Subject: {{ template.subject }}</p>
                    </div>
                  </div>
                  
                  <div class="flex items-center space-x-3">
                    <span class="text-xs text-gray-500">
                      {{ template.times_sent }} sent
                    </span>
                    <button
                      @click="editTemplate(template)"
                      class="inline-flex items-center text-blue-600 hover:text-blue-800"
                      title="Edit Template"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      @click="toggleTemplateStatus(template.id)"
                      :class="template.is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800'"
                      class="inline-flex items-center"
                      :title="template.is_active ? 'Disable Template' : 'Enable Template'"
                    >
                      <svg v-if="template.is_active" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                      </svg>
                      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </button>
                    <button
                      @click="sendTestTemplate(template)"
                      class="inline-flex items-center text-purple-600 hover:text-purple-800"
                      title="Send Test Email"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Template Modal -->
      <div v-if="editingTemplate" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto m-4">
          <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Edit Email Template</h3>
            <button
              type="button"
              @click="editingTemplate = null"
              class="text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="saveTemplate">
            <div class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                <input
                  v-model="templateForm.subject"
                  type="text"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">HTML Content</label>
                <TiptapEditor v-model="templateForm.html_content" placeholder="Email HTML content with formatting..." />
                <p class="text-xs text-gray-500 mt-1">Available variables: {customer_name}, {order_number}, {store_name}, etc.</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Plain Text Content (Optional)</label>
                <textarea
                  v-model="templateForm.plain_text_content"
                  rows="8"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                ></textarea>
              </div>
            </div>
            
            <div class="p-6 bg-gray-50 flex justify-end space-x-3 border-t border-gray-200">
              <button
                type="button"
                @click="editingTemplate = null"
                class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="templateForm.processing"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                {{ templateForm.processing ? 'Saving...' : 'Save Template' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Send Test Email Dialog -->
      <div v-if="showSendTestDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full m-4">
          <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Send Test Email</h3>
            <button
              type="button"
              @click="showSendTestDialog = false"
              class="text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="sendTest">
            <div class="p-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Test Email Address</label>
              <input
                v-model="testEmail"
                type="email"
                required
                placeholder="your@email.com"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div class="p-6 bg-gray-50 flex justify-end space-x-3 border-t border-gray-200">
              <button
                type="button"
                @click="showSendTestDialog = false"
                class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="sendingTest"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50"
              >
                {{ sendingTest ? 'Sending...' : 'Send Test' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </template>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import TiptapEditor from '@/components/Admin/TiptapEditor.vue'

const props = defineProps<{
  configuration: any
  templates: any
  drivers: any
  categories: any
}>()

const tabs = [
  { id: 'configuration', name: 'Configuration' },
  { id: 'templates', name: 'Email Templates' },
]

const activeTab = ref('configuration')
const showPassword = ref(false)
const showSesSecret = ref(false)
const showPostmarkToken = ref(false)
const testingConnection = ref(false)
const showSendTestDialog = ref(false)
const testEmail = ref('')
const sendingTest = ref(false)
const editingTemplate = ref(null)

const form = reactive({
  mail_driver: props.configuration.mail_driver,
  mail_from_address: props.configuration.mail_from_address,
  mail_from_name: props.configuration.mail_from_name,
  reply_to_email: props.configuration.reply_to_email,
  bcc_email: props.configuration.bcc_email,
  smtp_host: props.configuration.smtp_host,
  smtp_port: props.configuration.smtp_port,
  smtp_username: props.configuration.smtp_username,
  smtp_password: props.configuration.smtp_password_masked || '',
  smtp_encryption: props.configuration.smtp_encryption,
  ses_key: props.configuration.ses_key_masked || '',
  ses_secret: props.configuration.ses_secret_masked || '',
  ses_region: props.configuration.ses_region,
  postmark_token: props.configuration.postmark_token_masked || '',
  processing: false,
})

const templateForm = reactive({
  id: null,
  subject: '',
  html_content: '',
  plain_text_content: '',
  processing: false,
})

const saveConfiguration = () => {
  form.processing = true
  router.post('/admin/settings/email/configuration', form, {
    preserveScroll: true,
    onFinish: () => {
      form.processing = false
    },
  })
}

const testConnection = () => {
  testingConnection.value = true
  router.post('/admin/settings/email/test-connection', {}, {
    preserveScroll: true,
    onFinish: () => {
      testingConnection.value = false
    },
  })
}

const sendTest = () => {
  sendingTest.value = true
  router.post('/admin/settings/email/send-test', { email: testEmail.value }, {
    preserveScroll: true,
    onFinish: () => {
      sendingTest.value = false
      showSendTestDialog.value = false
    },
  })
}

const editTemplate = (template: any) => {
  editingTemplate.value = template
  templateForm.id = template.id
  templateForm.subject = template.subject
  templateForm.html_content = template.html_content
  templateForm.plain_text_content = template.plain_text_content || ''
}

const saveTemplate = () => {
  templateForm.processing = true
  router.put(`/admin/settings/email/templates/${templateForm.id}`, templateForm, {
    preserveScroll: true,
    onFinish: () => {
      templateForm.processing = false
      editingTemplate.value = null
    },
  })
}

const toggleTemplateStatus = (id: number) => {
  router.post(`/admin/settings/email/templates/${id}/toggle`, {}, {
    preserveScroll: true,
  })
}

const sendTestTemplate = (template: any) => {
  const email = prompt('Enter email address to send test:')
  if (email) {
    router.post(`/admin/settings/email/templates/${template.id}/send-test`, { email }, {
      preserveScroll: true,
    })
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleString()
}
</script>

<template>
  <AdminLayout title="Email Settings">
    <template #default>
      <Head title="Email Settings" />
      <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Email Settings</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure email delivery and manage email templates</p>
            </div>
            <div v-if="activeTab === 'configuration'">
                <button
                    @click="saveConfiguration"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-xl text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <Save v-if="!form.processing" class="w-4 h-4" />
                    <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ form.processing ? 'Saving...' : 'Save Configuration' }}
                </button>
            </div>
        </div>

      <!-- Tabs -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800">
          <nav class="flex space-x-8 px-6" aria-label="Tabs">
            <button
              @click="activeTab = 'configuration'"
              :class="[
                activeTab === 'configuration'
                  ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                  : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap'
              ]"
            >
              <Settings class="w-4 h-4 mr-2" :class="activeTab === 'configuration' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
              Configuration
            </button>
            <button
               @click="activeTab = 'templates'"
               :class="[
                 activeTab === 'templates'
                   ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                   : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                 'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap'
               ]"
             >
               <FileText class="w-4 h-4 mr-2" :class="activeTab === 'templates' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'" />
               Email Templates
             </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div>
          <!-- Configuration Tab -->
          <div v-show="activeTab === 'configuration'" class="p-6 sm:p-8">
            <form @submit.prevent="saveConfiguration">
              <!-- General Settings -->
              <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <Settings class="w-5 h-5 text-gray-400" />
                    General Configuration
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Mail Driver</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 padding-l-3 flex items-center pl-3 pointer-events-none">
                            <Server class="h-4 w-4 text-gray-400" />
                        </div>
                        <select
                        v-model="form.mail_driver"
                        class="block w-full pl-10 pr-10 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                        >
                        <option v-for="(label, value) in drivers" :key="value" :value="value">{{ label }}</option>
                        </select>
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">From Email</label>
                    <input
                      v-model="form.mail_from_address"
                      type="email"
                      required
                      class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">From Name</label>
                    <input
                      v-model="form.mail_from_name"
                      type="text"
                      required
                      class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Reply-To Email (Optional)</label>
                    <input
                      v-model="form.reply_to_email"
                      type="email"
                      class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                    />
                  </div>
                </div>
              </div>

              <!-- SMTP Configuration -->
              <div v-if="form.mail_driver === 'smtp'" class="mb-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <Mail class="w-5 h-5 text-gray-400" />
                    SMTP Server Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SMTP Host</label>
                    <input
                      v-model="form.smtp_host"
                      type="text"
                      placeholder="smtp.gmail.com"
                      class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SMTP Port</label>
                    <input
                      v-model="form.smtp_port"
                      type="number"
                      placeholder="587"
                      class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SMTP Username</label>
                    <input
                      v-model="form.smtp_username"
                      type="text"
                      class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SMTP Password</label>
                    <div class="relative">
                        <input
                        v-model="form.smtp_password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="••••••••"
                        class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200 pr-10"
                        />
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none"
                        >
                            <Eye v-if="!showPassword" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Encryption</label>
                    <div class="relative">
                         <div class="absolute inset-y-0 left-0 padding-l-3 flex items-center pl-3 pointer-events-none">
                            <Shield class="h-4 w-4 text-gray-400" />
                        </div>
                        <select
                        v-model="form.smtp_encryption"
                        class="block w-full pl-10 pr-10 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                        >
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="none">None</option>
                        </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Amazon SES Configuration -->
              <div v-if="form.mail_driver === 'ses'" class="mb-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <Server class="w-5 h-5 text-gray-400" />
                    Amazon SES Configuration
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Access Key ID</label>
                    <input
                      v-model="form.ses_key"
                      type="text"
                      :placeholder="configuration.ses_key_masked || 'AKIA...'"
                      class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Secret Access Key</label>
                    <div class="relative">
                        <input
                        v-model="form.ses_secret"
                        :type="showSesSecret ? 'text' : 'password'"
                        :placeholder="configuration.ses_secret_masked || '••••••••'"
                        class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200 pr-10"
                        />
                         <button 
                            type="button" 
                            @click="showSesSecret = !showSesSecret" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none"
                        >
                            <Eye v-if="!showSesSecret" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Region</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 padding-l-3 flex items-center pl-3 pointer-events-none">
                            <Globe class="h-4 w-4 text-gray-400" />
                        </div>
                        <select
                        v-model="form.ses_region"
                        class="block w-full pl-10 pr-10 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200"
                        >
                        <option value="us-east-1">US East (N. Virginia)</option>
                        <option value="us-west-2">US West (Oregon)</option>
                        <option value="eu-west-1">EU (Ireland)</option>
                        <option value="ap-southeast-1">Asia Pacific (Singapore)</option>
                        </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Postmark Configuration -->
              <div v-if="form.mail_driver === 'postmark'" class="mb-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <Mail class="w-5 h-5 text-gray-400" />
                    Postmark Configuration
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Server Token</label>
                    <div class="relative">
                        <input
                        v-model="form.postmark_token"
                        :type="showPostmarkToken ? 'text' : 'password'"
                        :placeholder="configuration.postmark_token_masked || '••••••••'"
                        class="block w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow duration-200 pr-10"
                        />
                         <button 
                            type="button" 
                            @click="showPostmarkToken = !showPostmarkToken" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none"
                        >
                            <Eye v-if="!showPostmarkToken" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Test Connection Status -->
              <div v-if="configuration.last_test_at" class="mb-6 p-4 rounded-xl border" :class="configuration.last_test_status === 'success' ? 'bg-green-50 border-green-200 dark:bg-green-900/10 dark:border-green-800' : 'bg-red-50 border-red-200 dark:bg-red-900/10 dark:border-red-800'">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <Check v-if="configuration.last_test_status === 'success'" class="w-5 h-5 text-green-600 dark:text-green-400" />
                        <AlertCircle v-else class="w-5 h-5 text-red-600 dark:text-red-400" />
                    </div>
                  <div>
                    <h4 :class="configuration.last_test_status === 'success' ? 'text-green-800 dark:text-green-300' : 'text-red-800 dark:text-red-300'" class="text-sm font-semibold">
                      {{ configuration.last_test_message }}
                    </h4>
                    <p :class="configuration.last_test_status === 'success' ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'" class="text-xs mt-1 opacity-80">Last tested: {{ formatDate(configuration.last_test_at) }}</p>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex flex-wrap items-center gap-4">
                <button
                  type="button"
                  @click="testConnection"
                  :disabled="testingConnection"
                  class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-sm transition-all duration-200 disabled:opacity-50"
                >
                  <Play v-if="!testingConnection" class="w-4 h-4 mr-2 text-green-500" />
                  <svg v-else class="w-4 h-4 mr-2 animate-spin text-green-500" fill="none" viewBox="0 0 24 24">
                       <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                       <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ testingConnection ? 'Testing...' : 'Test Connection' }}
                </button>

                <button
                  type="button"
                  @click="showSendTestDialog = true"
                  class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 shadow-sm transition-all duration-200"
                >
                  <Mail class="w-4 h-4 mr-2 text-purple-500" />
                  Send Test Email
                </button>

                <div class="flex-1"></div>

                <button
                  type="submit"
                  :disabled="form.processing"
                  class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-xl text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <Save v-if="!form.processing" class="w-4 h-4 mr-2" />
                  <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ form.processing ? 'Saving...' : 'Save Configuration' }}
                </button>
              </div>
            </form>
          </div>

          <!-- Email Templates Tab -->
          <div v-show="activeTab === 'templates'" class="p-6 sm:p-8">
            <div v-for="(categoryTemplates, category) in templates" :key="category" class="mb-8 last:mb-0">
              <div class="flex items-center gap-2 mb-4">
                <FileText class="w-5 h-5 text-gray-400" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ categories[category] }}</h3>
              </div>
              
              <div class="space-y-3">
                <div
                  v-for="template in categoryTemplates"
                  :key="template.id"
                  class="flex items-center justify-between p-5 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700 rounded-xl hover:shadow-sm hover:border-gray-300 dark:hover:border-gray-600 transition-all duration-200"
                >
                  <div class="flex items-start gap-4 flex-1">
                    <div class="flex-shrink-0 mt-1">
                      <span 
                        :class="template.is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800' : 'bg-gray-100 text-gray-500 dark:bg-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-600'"
                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium"
                      >
                        <CheckCircle2 v-if="template.is_active" class="w-3 h-3 mr-1.5" />
                        <X v-else class="w-3 h-3 mr-1.5" />
                        {{ template.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="font-semibold text-gray-900 dark:text-white mb-1">{{ template.name }}</h4>
                      <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ template.description }}</p>
                      <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
                        <span class="flex items-center gap-1.5 bg-white dark:bg-gray-800 px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700">
                          <Mail class="w-3 h-3 text-gray-400" />
                          <span class="truncate max-w-[200px]">Subject: {{ template.subject }}</span>
                        </span>
                        <span class="flex items-center gap-1.5 bg-white dark:bg-gray-800 px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700">
                          <CheckCircle2 class="w-3 h-3 text-gray-400" />
                          {{ template.times_sent }} sent
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="flex items-center gap-2 ml-4">
                    <button
                      @click="editTemplate(template)"
                      class="inline-flex items-center gap-1.5 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-200 dark:hover:border-blue-800 rounded-lg text-sm font-medium transition-all shadow-sm"
                      title="Edit Template"
                    >
                      <Edit2 class="w-3.5 h-3.5" />
                      Edit
                    </button>
                    <button
                      @click="toggleTemplateStatus(template.id)"
                      :class="template.is_active 
                        ? 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 hover:border-red-200 dark:hover:border-red-800' 
                        : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400 hover:border-green-200 dark:hover:border-green-800'"
                      class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all shadow-sm w-[100px] justify-center"
                      :title="template.is_active ? 'Disable Template' : 'Enable Template'"
                    >
                      <Power class="w-3.5 h-3.5" />
                      {{ template.is_active ? 'Disable' : 'Enable' }}
                    </button>
                    <button
                      @click="sendTestTemplate(template)"
                      class="inline-flex items-center gap-1.5 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:text-purple-600 dark:hover:text-purple-400 hover:border-purple-200 dark:hover:border-purple-800 rounded-lg text-sm font-medium transition-all shadow-sm"
                      title="Send Test Email"
                    >
                      <Mail class="w-3.5 h-3.5" />
                      Test
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Template Modal -->
      <div v-if="editingTemplate" class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                <Edit2 class="w-5 h-5 text-blue-600 dark:text-blue-400" />
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Email Template</h3>
            </div>
            <button
              type="button"
              @click="editingTemplate = null"
              class="p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            >
              <X class="w-5 h-5" />
            </button>
          </div>
          
          <form @submit.prevent="saveTemplate" class="flex-1 overflow-y-auto">
            <div class="p-6 space-y-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subject Line</label>
                <input
                  v-model="templateForm.subject"
                  type="text"
                  required
                  placeholder="Enter email subject..."
                  class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">HTML Content</label>
                <TiptapEditor v-model="templateForm.html_content" placeholder="Email HTML content with formatting..." />
                <div class="mt-2 p-3 bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-lg">
                  <p class="text-xs text-blue-700 dark:text-blue-300 font-medium mb-1">Available Variables:</p>
                  <p class="text-xs text-blue-600 dark:text-blue-400">{customer_name}, {order_number}, {store_name}, {order_total}, {tracking_number}</p>
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Plain Text Content (Optional)</label>
                <textarea
                  v-model="templateForm.plain_text_content"
                  rows="8"
                  placeholder="Fallback plain text version..."
                  class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all"
                ></textarea>
              </div>
            </div>
            
            <div class="p-6 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
              <button
                type="button"
                @click="editingTemplate = null"
                class="px-5 py-2.5 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-colors"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="templateForm.processing"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium shadow-sm transition-all"
              >
                <Save v-if="!templateForm.processing" class="w-4 h-4" />
                <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ templateForm.processing ? 'Saving...' : 'Save Template' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Send Test Email Dialog -->
      <div v-if="showSendTestDialog" class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                <Mail class="w-5 h-5 text-purple-600 dark:text-purple-400" />
              </div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Send Test Email</h3>
            </div>
            <button
              type="button"
              @click="showSendTestDialog = false"
              class="p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            >
              <X class="w-5 h-5" />
            </button>
          </div>
          
          <form @submit.prevent="sendTest">
            <div class="p-6">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Test Email Address</label>
              <input
                v-model="testEmail"
                type="email"
                required
                placeholder="your@email.com"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all"
              />
              <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">A test email will be sent to this address using your current configuration.</p>
            </div>
            
            <div class="p-6 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700">
              <button
                type="button"
                @click="showSendTestDialog = false"
                class="px-5 py-2.5 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-colors"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="sendingTest"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-purple-600 text-white rounded-xl hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium shadow-sm transition-all"
              >
                <Mail v-if="!sendingTest" class="w-4 h-4" />
                <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ sendingTest ? 'Sending...' : 'Send Test' }}
              </button>
            </div>
          </form>
        </div>
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
import { 
  Save, 
  Settings, 
  Mail, 
  Server, 
  Shield, 
  Trash2, 
  Edit2, 
  Play, 
  Check, 
  X,
  FileText,
  AlertCircle,
  Eye,
  EyeOff,
  Power,
  CheckCircle2
} from 'lucide-vue-next'

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

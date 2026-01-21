<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

interface ProviderConfig {
  name: string
  type: string
  base_url?: string
  api_key?: string
  org_id?: string
  project_id?: string
  headers?: Record<string, string>
  is_default?: boolean
}

interface ModelConfig {
  name: string
  provider: string
  mode?: string
  max_tokens?: number | null
  is_default?: boolean
}

interface AgentConfig {
  name: string
  provider: string
  model: string
  temperature?: number | null
  max_tokens?: number | null
  system_prompt?: string
  is_default?: boolean
}

interface Props {
  settings?: {
    ai_enabled?: boolean
    default_provider?: string
    default_agent?: string
    providers?: ProviderConfig[]
    models?: ModelConfig[]
    agents?: AgentConfig[]
  }
}

const props = withDefaults(defineProps<Props>(), {
  settings: () => ({})
})

const page = usePage()
const errors = computed(() => page.props.errors as Record<string, string>)

const form = useForm({
  ai_enabled: props.settings.ai_enabled ?? false,
  default_provider: props.settings.default_provider ?? '',
  default_agent: props.settings.default_agent ?? '',
    product_description_agent: props.settings.product_description_agent ?? '',
  providers: (props.settings.providers ?? []) as ProviderConfig[],
  models: (props.settings.models ?? []) as ModelConfig[],
  agents: (props.settings.agents ?? []) as AgentConfig[],
})

const showApiKeys = ref(false)
const activeTab = ref<'access' | 'providers' | 'models' | 'agents'>('access')

const providerOptions = computed(() => form.providers.map((p) => p.name).filter(Boolean))
const agentOptions = computed(() => form.agents.map((a) => a.name).filter(Boolean))

const getModelsForProvider = (provider: string) => {
  return form.models
    .filter((model) => !provider || model.provider === provider)
    .map((model) => model.name)
    .filter(Boolean)
}

const addProvider = () => {
  form.providers.push({
    name: '',
    type: 'openai',
    base_url: '',
    api_key: '',
    org_id: '',
    project_id: '',
    headers: {},
    is_default: false,
  })
}

const removeProvider = (index: number) => {
  form.providers.splice(index, 1)
}

const addModel = () => {
  form.models.push({
    name: '',
    provider: form.default_provider || (providerOptions.value[0] ?? ''),
    mode: 'text',
    max_tokens: null,
    is_default: false,
  })
}

const removeModel = (index: number) => {
  form.models.splice(index, 1)
}

const addAgent = () => {
  form.agents.push({
    name: '',
    provider: form.default_provider || (providerOptions.value[0] ?? ''),
    model: form.models[0]?.name || '',
    temperature: 0.7,
    max_tokens: 1024,
    system_prompt: '',
    is_default: false,
  })
}

const removeAgent = (index: number) => {
  form.agents.splice(index, 1)
}

const save = () => {
  form.post('/admin/settings/ai', {
    preserveScroll: true,
  })
}
</script>

<template>
  <AdminLayout title="AI Settings">
    <Head title="AI Settings" />

    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">AI Settings</h1>
        <p class="mt-1 text-sm text-gray-500">Manage AI providers, models, and agents for the platform.</p>
      </div>

      <form @submit.prevent="save">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6">
              <button type="button" @click="activeTab = 'access'" :class="[activeTab === 'access' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                Access
              </button>
              <button type="button" @click="activeTab = 'providers'" :class="[activeTab === 'providers' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                Providers
              </button>
              <button type="button" @click="activeTab = 'models'" :class="[activeTab === 'models' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                Models
              </button>
              <button type="button" @click="activeTab = 'agents'" :class="[activeTab === 'agents' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                Agents
              </button>
            </nav>
          </div>

          <div class="space-y-8">
            <div v-show="activeTab === 'access'" class="p-6 space-y-6">
              <h3 class="text-lg font-semibold text-gray-900">AI Access</h3>
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500">Enable or disable AI features globally.</p>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                  <input v-model="form.ai_enabled" type="checkbox" class="rounded border-gray-300" />
                  Enable AI
                </label>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Default Provider</label>
                  <select v-model="form.default_provider" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Select provider</option>
                    <option v-for="provider in providerOptions" :key="provider" :value="provider">
                      {{ provider }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Default AI Agent</label>
                  <select v-model="form.default_agent" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Select agent</option>
                    <option v-for="agent in agentOptions" :key="agent" :value="agent">
                      {{ agent }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Product Description Agent</label>
                  <select v-model="form.product_description_agent" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Select agent</option>
                    <option v-for="agent in agentOptions" :key="agent" :value="agent">
                      {{ agent }}
                    </option>
                  </select>
                  <p class="mt-1 text-xs text-gray-500">Used for product description generation.</p>
                </div>
              </div>
            </div>

            <div v-show="activeTab === 'providers'" class="p-6 space-y-6">
              <h3 class="text-lg font-semibold text-gray-900">Providers</h3>
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500">Configure Gemini, OpenAI, or custom providers.</p>
                </div>
                <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg" @click="addProvider">Add Provider</button>
              </div>

              <div v-if="form.providers.length === 0" class="text-sm text-gray-500">No providers configured.</div>

              <div v-for="(provider, index) in form.providers" :key="index" class="border border-gray-200 rounded-lg p-4 space-y-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-sm font-semibold text-gray-900">Provider {{ index + 1 }}</h3>
                  <button type="button" class="text-sm text-red-600" @click="removeProvider(index)">Remove</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Provider Name</label>
                    <input v-model="provider.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="OpenAI" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Provider Type</label>
                    <select v-model="provider.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                      <option value="openai">OpenAI</option>
                      <option value="gemini">Gemini</option>
                      <option value="custom">Custom</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Base URL</label>
                    <input v-model="provider.base_url" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="https://api.openai.com/v1" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                    <div class="relative">
                      <input v-model="provider.api_key" :type="showApiKeys ? 'text' : 'password'" class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg" placeholder="sk-..." />
                      <button type="button" class="absolute inset-y-0 right-2 flex items-center text-gray-400 hover:text-gray-600" @click="showApiKeys = !showApiKeys" :aria-label="showApiKeys ? 'Hide API key' : 'Show API key'">
                        <svg v-if="!showApiKeys" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.042-3.368M6.223 6.223A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.118 5.012M3 3l18 18" />
                        </svg>
                      </button>
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Organization ID</label>
                    <input v-model="provider.org_id" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Optional" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project ID</label>
                    <input v-model="provider.project_id" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Optional" />
                  </div>
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                  <input v-model="provider.is_default" type="checkbox" class="rounded border-gray-300" />
                  Set as default provider
                </label>
              </div>

              
            </div>

            <div v-show="activeTab === 'models'" class="p-6 space-y-6">
              <h3 class="text-lg font-semibold text-gray-900">Models</h3>
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500">Add available models for each provider.</p>
                </div>
                <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg" @click="addModel">Add Model</button>
              </div>

              <div v-if="form.models.length === 0" class="text-sm text-gray-500">No models configured.</div>

              <div v-for="(model, index) in form.models" :key="index" class="border border-gray-200 rounded-lg p-4 space-y-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-sm font-semibold text-gray-900">Model {{ index + 1 }}</h3>
                  <button type="button" class="text-sm text-red-600" @click="removeModel(index)">Remove</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Model Name</label>
                    <input v-model="model.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="gpt-4o-mini" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                    <select v-model="model.provider" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                      <option value="">Select provider</option>
                      <option v-for="provider in providerOptions" :key="provider" :value="provider">
                        {{ provider }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
                    <select v-model="model.mode" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                      <option value="text">Text</option>
                      <option value="vision">Vision</option>
                      <option value="embedding">Embedding</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Tokens</label>
                    <input v-model.number="model.max_tokens" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="4096" />
                  </div>
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                  <input v-model="model.is_default" type="checkbox" class="rounded border-gray-300" />
                  Set as default model for provider
                </label>
              </div>
            </div>

            <div v-show="activeTab === 'agents'" class="p-6 space-y-6">
              <h3 class="text-lg font-semibold text-gray-900">AI Agents</h3>
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500">Create named agents for different AI tasks.</p>
                </div>
                <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg" @click="addAgent">Add Agent</button>
              </div>

              <div v-if="form.agents.length === 0" class="text-sm text-gray-500">No agents configured.</div>

              <div v-for="(agent, index) in form.agents" :key="index" class="border border-gray-200 rounded-lg p-4 space-y-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-sm font-semibold text-gray-900">Agent {{ index + 1 }}</h3>
                  <button type="button" class="text-sm text-red-600" @click="removeAgent(index)">Remove</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agent Name</label>
                    <input v-model="agent.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Product Description Agent" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                    <select v-model="agent.provider" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                      <option value="">Select provider</option>
                      <option v-for="provider in providerOptions" :key="provider" :value="provider">
                        {{ provider }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                    <select v-model="agent.model" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                      <option value="">Select model</option>
                      <option v-for="modelName in getModelsForProvider(agent.provider)" :key="modelName" :value="modelName">
                        {{ modelName }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Temperature</label>
                    <input v-model.number="agent.temperature" type="number" step="0.1" min="0" max="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Tokens</label>
                    <input v-model.number="agent.max_tokens" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">System Prompt</label>
                  <textarea v-model="agent.system_prompt" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="You are a helpful product description assistant..."></textarea>
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                  <input v-model="agent.is_default" type="checkbox" class="rounded border-gray-300" />
                  Set as default agent
                </label>
              </div>
            </div>
          </div>

          <div class="border-t border-gray-200 px-6 py-4 flex items-center justify-between">
            <div class="text-sm text-red-600" v-if="errors.general">{{ errors.general }}</div>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg" :disabled="form.processing">
              Save Settings
            </button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

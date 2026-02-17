<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { 
  Brain, 
  Server, 
  Cpu, 
  Bot, 
  ChevronDown,
  ChevronRight,
  Save, 
  Plus, 
  Trash2, 
  Eye, 
  EyeOff, 
  Key,
  Database,
  Terminal,
  Settings
} from 'lucide-vue-next'

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
  is_system?: boolean
}

interface Props {
  settings?: {
    ai_enabled?: boolean
    default_provider?: string
    default_agent?: string
    product_description_agent?: string
    price_comparison_agent?: string
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
  price_comparison_agent: props.settings.price_comparison_agent ?? '',
  providers: (props.settings.providers ?? []) as ProviderConfig[],
  models: (props.settings.models ?? []) as ModelConfig[],
  agents: (props.settings.agents ?? []) as AgentConfig[],
})

const showApiKeys = ref<Record<number, boolean>>({})
const activeTab = ref<'access' | 'providers' | 'models' | 'agents'>('access')
const expandedAgentIndex = ref<number | null>(0)

const providerOptions = computed(() => form.providers.map((p) => p.name).filter(Boolean))
const agentOptions = computed(() => form.agents.map((a) => a.name).filter(Boolean))

const tabs = [
  { id: 'providers', name: 'Providers', icon: Server },
  { id: 'models', name: 'Models', icon: Cpu },
  { id: 'agents', name: 'AI Agents', icon: Bot },
  { id: 'access', name: 'Access & Defaults', icon: Key },
]

const toggleApiKeyVisibility = (index: number) => {
  showApiKeys.value[index] = !showApiKeys.value[index]
}

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
  const provider = form.default_provider || (providerOptions.value[0] ?? '')
  const model = provider ? (getModelsForProvider(provider)[0] || '') : ''

  form.agents.push({
    name: '',
    provider,
    model,
    temperature: 0.7,
    max_tokens: 1024,
    system_prompt: '',
  })

  expandedAgentIndex.value = form.agents.length - 1
}

const removeAgent = (index: number) => {
  if (form.agents[index]?.is_system) {
    showToast('System agents cannot be deleted.', 'warning')
    return
  }

  form.agents.splice(index, 1)

  if (expandedAgentIndex.value === index) {
    expandedAgentIndex.value = null
  } else if (expandedAgentIndex.value !== null && expandedAgentIndex.value > index) {
    expandedAgentIndex.value -= 1
  }
}

const toggleAgentAccordion = (index: number) => {
  expandedAgentIndex.value = expandedAgentIndex.value === index ? null : index
}

watch(
  () => form.agents.map((agent) => ({ provider: agent.provider, model: agent.model })),
  (agents) => {
    agents.forEach((agentState, index) => {
      const availableModels = getModelsForProvider(agentState.provider)

      if (availableModels.length === 0) {
        if (form.agents[index].model) {
          form.agents[index].model = ''
        }
        return
      }

      if (!availableModels.includes(agentState.model)) {
        form.agents[index].model = availableModels[0]
      }
    })
  },
  { deep: true }
)

const showToast = (message: string, type: 'success' | 'error' | 'warning' | 'info' = 'success') => {
  window.dispatchEvent(new CustomEvent('show-toast', {
    detail: { message, type },
  }))
}

const save = () => {
  const section = activeTab.value
  if (section === 'agents') {
    form.agents = form.agents.map((agent) => {
      const fallbackProvider = form.default_provider || providerOptions.value[0] || ''
      const provider = agent.provider || fallbackProvider
      const model = agent.model || (provider ? getModelsForProvider(provider)[0] || '' : '')
      return {
        ...agent,
        provider,
        model,
      }
    })
  }

  const payload = (() => {
    switch (section) {
      case 'providers':
        return { providers: form.providers }
      case 'models':
        return { models: form.models }
      case 'agents':
        return { agents: form.agents }
      case 'access':
        return {
          ai_enabled: form.ai_enabled,
          default_provider: form.default_provider,
          default_agent: form.default_agent,
          product_description_agent: form.product_description_agent,
          price_comparison_agent: form.price_comparison_agent,
        }
      default:
        return {}
    }
  })()

  form.transform(() => payload).post('/admin/settings/ai', {
    preserveScroll: true,
    onSuccess: () => {
      showToast('AI settings saved successfully.', 'success')
    },
    onError: () => {
      const firstError = Object.values(form.errors || {})[0]
      showToast(firstError || 'Failed to save AI settings. Please try again.', 'error')
    },
  })
}
</script>

<template>
  <Head title="AI Settings" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300">
            AI Configuration
          </h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <Brain class="w-4 h-4" />
            Manage AI providers, models, and agents
          </p>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <form @submit.prevent="save">
          <!-- Tabs -->
          <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex overflow-x-auto" aria-label="Tabs">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                type="button"
                @click="activeTab = tab.id as any"
                :class="[
                  'flex items-center gap-2 whitespace-nowrap px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                  activeTab === tab.id
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/10'
                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'
                ]"
              >
                <component :is="tab.icon" class="w-4 h-4" />
                {{ tab.name }}
              </button>
            </nav>
          </div>

          <!-- Content -->
          <div class="p-6">
            <!-- Access & Defaults Tab -->
            <div v-show="activeTab === 'access'" class="space-y-8">
              <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-100 dark:border-gray-700">
                <div>
                  <h3 class="text-base font-semibold text-gray-900 dark:text-white">AI Features</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Enable or disable AI capabilities globally across the platform.</p>
                </div>
                <div class="flex items-center">
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="form.ai_enabled" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">{{ form.ai_enabled ? 'Enabled' : 'Disabled' }}</span>
                  </label>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Default Provider</label>
                  <div class="relative">
                    <select v-model="form.default_provider" class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white appearance-none">
                      <option value="">Select provider</option>
                      <option v-for="provider in providerOptions" :key="provider" :value="provider">{{ provider }}</option>
                    </select>
                    <Server class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Default Generic Agent</label>
                  <div class="relative">
                    <select v-model="form.default_agent" class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white appearance-none">
                      <option value="">Select agent</option>
                      <option v-for="agent in agentOptions" :key="agent" :value="agent">{{ agent }}</option>
                    </select>
                    <Bot class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Product Description Agent</label>
                  <div class="relative">
                    <select v-model="form.product_description_agent" class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white appearance-none">
                      <option value="">Select agent</option>
                      <option v-for="agent in agentOptions" :key="agent" :value="agent">{{ agent }}</option>
                    </select>
                    <Terminal class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                  </div>
                  <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Helper agent for generating product descriptions.</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Price Comparison Agent</label>
                  <div class="relative">
                    <select v-model="form.price_comparison_agent" class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white appearance-none">
                      <option value="">Select agent</option>
                      <option v-for="agent in agentOptions" :key="agent" :value="agent">{{ agent }}</option>
                    </select>
                    <Database class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                  </div>
                  <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Agent used by product pricing comparison modal.</p>
                </div>
              </div>
            </div>

            <!-- Providers Tab -->
            <div v-show="activeTab === 'providers'" class="space-y-6">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">AI Providers</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Configure connections to LLM services.</p>
                </div>
                <button type="button" @click="addProvider" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors shadow-sm">
                  <Plus class="w-4 h-4 mr-2" />
                  Add Provider
                </button>
              </div>

              <div v-if="form.providers.length === 0" class="text-center py-12 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
                <Server class="w-8 h-8 text-gray-400 mx-auto mb-3" />
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">No providers configured</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add a provider to get started with AI features.</p>
              </div>

              <div v-else class="space-y-4">
                <div v-for="(provider, index) in form.providers" :key="index" class="bg-gray-50 dark:bg-gray-700/20 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                  <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-100/50 dark:bg-gray-700/40">
                    <div class="flex items-center gap-2">
                       <span class="bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 text-xs font-bold px-2 py-1 rounded-md uppercase">{{ provider.type || 'Custom' }}</span>
                       <h4 class="font-medium text-gray-900 dark:text-white">{{ provider.name || 'New Provider' }}</h4>
                    </div>
                    <button type="button" @click="removeProvider(index)" class="text-red-500 hover:text-red-700 dark:hover:text-red-400 p-1 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                  
                  <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Provider Name <span class="text-red-500">*</span></label>
                      <input v-model="provider.name" type="text" class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white" placeholder="e.g. OpenAI Main" />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type <span class="text-red-500">*</span></label>
                      <select v-model="provider.type" class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <option value="openai">OpenAI</option>
                        <option value="gemini">Gemini</option>
                        <option value="anthropic">Anthropic</option>
                        <option value="custom">Custom (OpenAI Compatible)</option>
                      </select>
                    </div>
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Base URL</label>
                      <input v-model="provider.base_url" type="text" class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white" placeholder="https://api.openai.com/v1" />
                    </div>
                    <div class="md:col-span-2">
                       <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API Key <span class="text-red-500">*</span></label>
                       <div class="relative">
                        <input 
                          v-model="provider.api_key" 
                          :type="showApiKeys[index] ? 'text' : 'password'" 
                          class="w-full px-4 py-2.5 pr-12 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white font-mono text-sm" 
                          placeholder="sk-..." 
                        />
                        <button 
                          type="button" 
                          class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" 
                          @click="toggleApiKeyVisibility(index)"
                        >
                          <component :is="showApiKeys[index] ? EyeOff : Eye" class="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Organization ID</label>
                      <input v-model="provider.org_id" type="text" class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white" placeholder="Optional" />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project ID</label>
                      <input v-model="provider.project_id" type="text" class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white" placeholder="Optional" />
                    </div>
                    <div class="md:col-span-2 flex items-center pt-2">
                       <label class="flex items-center gap-3 cursor-pointer">
                          <input v-model="provider.is_default" type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:bg-gray-700">
                          <span class="text-sm font-medium text-gray-900 dark:text-gray-200">Use as primary provider for this type</span>
                       </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Models Tab -->
            <div v-show="activeTab === 'models'" class="space-y-6">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">AI Models</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Map available models to providers.</p>
                </div>
                <button type="button" @click="addModel" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors shadow-sm">
                  <Plus class="w-4 h-4 mr-2" />
                  Add Model
                </button>
              </div>

              <div v-if="form.models.length === 0" class="text-center py-12 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
                <Cpu class="w-8 h-8 text-gray-400 mx-auto mb-3" />
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">No models configured</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Define which models are available for use.</p>
              </div>

              <div v-else class="space-y-4">
                <div v-for="(model, index) in form.models" :key="index" class="bg-gray-50 dark:bg-gray-700/20 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                   <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-100/50 dark:bg-gray-700/40">
                    <div class="flex items-center gap-2">
                       <span class="bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 text-xs font-bold px-2 py-1 rounded-md uppercase">{{ model.mode || 'Text' }}</span>
                       <h4 class="font-medium text-gray-900 dark:text-white">{{ model.name || 'New Model' }}</h4>
                       <span v-if="model.provider" class="text-xs text-gray-500 dark:text-gray-400 ml-2">via {{ model.provider }}</span>
                    </div>
                    <button type="button" @click="removeModel(index)" class="text-red-500 hover:text-red-700 dark:hover:text-red-400 p-1 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>

                  <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Model ID <span class="text-red-500">*</span></label>
                      <input v-model="model.name" type="text" class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white" placeholder="gpt-4-turbo" />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Provider <span class="text-red-500">*</span></label>
                      <select v-model="model.provider" class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <option value="">Select provider</option>
                        <option v-for="provider in providerOptions" :key="provider" :value="provider">{{ provider }}</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mode</label>
                      <select v-model="model.mode" class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <option value="text">Text / Chat</option>
                        <option value="vision">Vision (Image Analysis)</option>
                        <option value="embedding">Embedding</option>
                        <option value="image_generation">Image Generation</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Context Tokens</label>
                      <input v-model.number="model.max_tokens" type="number" min="1" class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white" placeholder="128000" />
                    </div>
                     <div class="md:col-span-2 flex items-center pt-2">
                       <label class="flex items-center gap-3 cursor-pointer">
                          <input v-model="model.is_default" type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:bg-gray-700">
                          <span class="text-sm font-medium text-gray-900 dark:text-gray-200">Set as default model for this provider</span>
                       </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Agents Tab -->
            <div v-show="activeTab === 'agents'" class="space-y-6">
              <div class="flex items-center justify-between">
                 <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">AI Agents</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Configure specialize agents for specific tasks.</p>
                </div>
                <button type="button" @click="addAgent" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors shadow-sm">
                  <Plus class="w-4 h-4 mr-2" />
                  Add Agent
                </button>
              </div>

              <div v-if="form.agents.length === 0" class="text-center py-12 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
                <Bot class="w-8 h-8 text-gray-400 mx-auto mb-3" />
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">No agents configured</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create agents to handle specific tasks.</p>
              </div>

               <div v-else class="space-y-4">
                <div v-for="(agent, index) in form.agents" :key="index" class="bg-gray-50 dark:bg-gray-700/20 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                  <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-100/50 dark:bg-gray-700/40">
                    <button type="button" class="flex items-center gap-2 text-left" @click="toggleAgentAccordion(index)">
                      <component :is="expandedAgentIndex === index ? ChevronDown : ChevronRight" class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                       <Bot class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                       <h4 class="font-medium text-gray-900 dark:text-white">{{ agent.name || 'New Agent' }}</h4>
                    </button>
                    <div class="flex items-center gap-2">
                      <span v-if="agent.is_system" class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900/30 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-300">
                        System
                      </span>
                      <button type="button" @click="toggleAgentAccordion(index)" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 p-1 hover:bg-gray-200/60 dark:hover:bg-gray-600/40 rounded-lg transition-colors">
                        <component :is="expandedAgentIndex === index ? ChevronDown : ChevronRight" class="w-4 h-4" />
                      </button>
                      <button v-if="!agent.is_system" type="button" @click="removeAgent(index)" class="text-red-500 hover:text-red-700 dark:hover:text-red-400 p-1 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </div>

                  <div v-show="expandedAgentIndex === index" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Agent Name <span class="text-red-500">*</span></label>
                      <input v-model="agent.name" type="text" class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white" placeholder="Product Describer" />
                    </div>
                     <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Provider</label>
                      <select v-model="agent.provider" class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <option value="">Select provider</option>
                        <option v-for="provider in providerOptions" :key="provider" :value="provider">{{ provider }}</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Model</label>
                      <select v-model="agent.model" class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <option value="">Select model</option>
                        <option v-for="modelName in getModelsForProvider(agent.provider)" :key="modelName" :value="modelName">{{ modelName }}</option>
                      </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Temperature</label>
                        <input v-model.number="agent.temperature" type="number" step="0.1" min="0" max="2" class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white" />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Output</label>
                        <input v-model.number="agent.max_tokens" type="number" min="1" class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white" />
                      </div>
                    </div>
                    <div class="md:col-span-2">
                       <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">System Prompt</label>
                       <textarea v-model="agent.system_prompt" rows="4" class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white font-mono text-sm" placeholder="You are a helpful assistant..."></textarea>
                    </div>
                  </div>
                </div>
               </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-between p-6 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
             <div class="text-sm text-red-600 dark:text-red-400" v-if="errors.general">{{ errors.general }}</div>
             <div v-else></div>
             <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium text-sm transition-all shadow-sm disabled:opacity-70 disabled:cursor-not-allowed"
            >
              <Save v-if="!form.processing" class="w-4 h-4 mr-2" />
              <div v-else class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent mr-2"></div>
              <span>{{ form.processing ? 'Saving Changes...' : 'Save Settings' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, useForm, usePage, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import {
  Cable,
  Copy,
  Check,
  Key,
  Plus,
  Trash2,
  Save,
  Shield,
  Terminal,
  Wrench,
} from 'lucide-vue-next'

interface McpToken {
  id: number
  name: string
  abilities: string[]
  last_used_at: string | null
  expires_at: string | null
  created_at: string | null
}

interface McpTool {
  name: string
  description: string
}

interface Props {
  endpointUrl: string
  localCaCertPath?: string | null
  settings?: {
    mcp_enabled?: boolean
  }
  tokens?: McpToken[]
  tools?: McpTool[]
  plainTextToken?: string | null
}

const props = withDefaults(defineProps<Props>(), {
  localCaCertPath: null,
  settings: () => ({ mcp_enabled: false }),
  tokens: () => [],
  tools: () => [],
  plainTextToken: null,
})

const page = usePage()
const flash = computed(() => (page.props.flash as Record<string, string | null>) || {})

const settingsForm = useForm({
  mcp_enabled: props.settings.mcp_enabled ?? false,
})

const tokenForm = useForm({
  name: 'Cursor MCP',
  expires_at: '',
})

const copiedKey = ref<string | null>(null)
const activeTab = ref<'access' | 'tokens' | 'connect' | 'tools'>('access')

const tabs = [
  { id: 'access', name: 'Access', icon: Shield },
  { id: 'tokens', name: 'Tokens', icon: Key },
  { id: 'connect', name: 'Connect', icon: Cable },
  { id: 'tools', name: 'Tools', icon: Wrench },
]

const showToast = (message: string, type: 'success' | 'error' | 'warning' | 'info' = 'success') => {
  window.dispatchEvent(new CustomEvent('show-toast', {
    detail: { message, type },
  }))
}

const saveSettings = () => {
  settingsForm.post('/admin/settings/mcp', {
    preserveScroll: true,
    onSuccess: () => showToast('MCP settings saved.', 'success'),
    onError: () => showToast('Failed to save MCP settings.', 'error'),
  })
}

const createToken = () => {
  tokenForm.post('/admin/settings/mcp/tokens', {
    preserveScroll: true,
    onSuccess: () => {
      tokenForm.reset('expires_at')
      tokenForm.name = 'Cursor MCP'
      showToast('Token created. Copy it now.', 'success')
      activeTab.value = 'tokens'
    },
    onError: () => {
      const firstError = Object.values(tokenForm.errors || {})[0]
      showToast(firstError || 'Failed to create token.', 'error')
    },
  })
}

const revokeToken = (id: number) => {
  if (!confirm('Revoke this MCP token? Connected clients will stop working.')) {
    return
  }

  router.delete(`/admin/settings/mcp/tokens/${id}`, {
    preserveScroll: true,
    onSuccess: () => showToast('Token revoked.', 'success'),
    onError: () => showToast('Failed to revoke token.', 'error'),
  })
}

const copyText = async (text: string, key: string) => {
  try {
    await navigator.clipboard.writeText(text)
    copiedKey.value = key
    showToast('Copied to clipboard.', 'success')
    setTimeout(() => {
      if (copiedKey.value === key) copiedKey.value = null
    }, 2000)
  } catch {
    showToast('Could not copy to clipboard.', 'error')
  }
}

const selectedPlatform = ref('cursor')

const isLocalHttpsEndpoint = computed(() => {
  try {
    const u = new URL(props.endpointUrl)
    const host = u.hostname.toLowerCase()
    const localHost =
      host === 'localhost'
      || host === '127.0.0.1'
      || host.endsWith('.test')
      || host.endsWith('.local')
      || host.endsWith('.localhost')
    return u.protocol === 'https:' && localHost
  } catch {
    return false
  }
})

const herdCaPath = props.localCaCertPath
  || '/Users/YOU/Library/Application Support/Herd/config/valet/CA/LaravelValetCASelfSigned.pem'

const remoteEnv = computed(() => {
  // Local Herd/Valet HTTPS certs are not trusted by Node's default CA store.
  if (!isLocalHttpsEndpoint.value) {
    return undefined
  }

  return {
    NODE_EXTRA_CA_CERTS: herdCaPath,
    NODE_TLS_REJECT_UNAUTHORIZED: '0',
  }
})

const buildRemoteSnippet = (url: string) => {
  const server: Record<string, unknown> = {
    command: 'npx',
    args: ['-y', 'mcp-remote', url, '--header', 'Authorization: Bearer YOUR_MCP_TOKEN'],
  }
  if (remoteEnv.value) {
    server.env = remoteEnv.value
  }
  return JSON.stringify({ mcpServers: { cartxis: server } }, null, 2)
}

const platforms = computed(() => [
  {
    id: 'cursor',
    label: 'Cursor (direct URL)',
    description: isLocalHttpsEndpoint.value
      ? 'May fail on local .test HTTPS (Node cannot verify Herd/Valet certs). Prefer “Cursor (mcp-remote)” below.'
      : 'Add to ~/.cursor/mcp.json or project .cursor/mcp.json',
    fileHint: '.cursor/mcp.json',
    snippet: JSON.stringify({
      mcpServers: {
        cartxis: {
          url: props.endpointUrl,
          headers: {
            Authorization: 'Bearer YOUR_MCP_TOKEN',
          },
        },
      },
    }, null, 2),
  },
  {
    id: 'cursor-remote',
    label: 'Cursor (mcp-remote / stdio)',
    description: isLocalHttpsEndpoint.value
      ? 'Recommended for local Herd/Valet HTTPS — includes TLS env so Node trusts the local CA'
      : 'Use when your Cursor build needs an npx stdio bridge',
    fileHint: '.cursor/mcp.json',
    snippet: buildRemoteSnippet(props.endpointUrl),
  },
  {
    id: 'claude',
    label: 'Claude Code / Claude Desktop',
    description: 'Claude Desktop MCP config or Claude Code MCP settings',
    fileHint: 'claude_desktop_config.json',
    snippet: buildRemoteSnippet(props.endpointUrl),
  },
  {
    id: 'vscode',
    label: 'VS Code (GitHub Copilot MCP)',
    description: 'Add under MCP servers in VS Code settings / mcp.json',
    fileHint: '.vscode/mcp.json',
    snippet: JSON.stringify({
      servers: {
        cartxis: {
          type: 'http',
          url: props.endpointUrl,
          headers: {
            Authorization: 'Bearer YOUR_MCP_TOKEN',
          },
        },
      },
    }, null, 2),
  },
  {
    id: 'chatgpt',
    label: 'ChatGPT / custom GPT',
    description: 'Point a custom action or MCP-compatible connector at the endpoint with a Bearer token. OAuth is not supported in this MVP.',
    fileHint: null,
    snippet: JSON.stringify({
      endpoint: props.endpointUrl,
      headers: {
        Authorization: 'Bearer YOUR_MCP_TOKEN',
      },
      note: 'OAuth is not supported — Bearer tokens only',
    }, null, 2),
  },
  {
    id: 'grok',
    label: 'Grok / other HTTP MCP clients',
    description: 'Generic Streamable HTTP MCP config for Grok and similar clients',
    fileHint: 'mcp.json',
    snippet: JSON.stringify({
      mcpServers: {
        cartxis: {
          url: props.endpointUrl,
          headers: {
            Authorization: 'Bearer YOUR_MCP_TOKEN',
          },
        },
      },
    }, null, 2),
  },
])

const activePlatform = computed(() =>
  platforms.value.find((p) => p.id === selectedPlatform.value) ?? platforms.value[0]
)

const displayedToken = computed(() => props.plainTextToken || null)

if (isLocalHttpsEndpoint.value) {
  selectedPlatform.value = 'cursor-remote'
}
</script>

<template>
  <Head title="MCP Settings" />

  <AdminLayout title="MCP Settings">
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300">
            Model Context Protocol
          </h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <Cable class="w-4 h-4" />
            Connect Claude Code, Cursor, VS Code, ChatGPT, and other MCP clients to manage this store
          </p>
        </div>
      </div>

      <div
        v-if="flash.success && !displayedToken"
        class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200"
      >
        {{ flash.success }}
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
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

        <div class="p-6">
          <!-- Access -->
          <div v-show="activeTab === 'access'" class="space-y-8">
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-100 dark:border-gray-700">
              <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Enable MCP</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  When disabled, <code class="text-xs">/mcp</code> returns HTTP 503.
                </p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input v-model="settingsForm.mcp_enabled" type="checkbox" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600" />
              </label>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">MCP endpoint</label>
              <div class="flex gap-2">
                <input
                  :value="endpointUrl"
                  readonly
                  class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm font-mono text-gray-900 dark:text-gray-100"
                />
                <button
                  type="button"
                  class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                  @click="copyText(endpointUrl, 'endpoint')"
                >
                  <Check v-if="copiedKey === 'endpoint'" class="w-4 h-4 text-green-600" />
                  <Copy v-else class="w-4 h-4" />
                  Copy
                </button>
              </div>
              <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Clients must send <code>Authorization: Bearer &lt;token&gt;</code> on every request.
              </p>
            </div>

            <div class="flex justify-end">
              <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                :disabled="settingsForm.processing"
                @click="saveSettings"
              >
                <Save class="w-4 h-4" />
                Save
              </button>
            </div>
          </div>

          <!-- Tokens -->
          <div v-show="activeTab === 'tokens'" class="space-y-8">
            <div
              v-if="displayedToken"
              class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20"
            >
              <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-100">Copy your token now</h3>
              <p class="mt-1 text-xs text-amber-800 dark:text-amber-200">
                This plaintext token is shown only once. Store it in your MCP client config.
              </p>
              <div class="mt-3 flex gap-2">
                <input
                  :value="displayedToken"
                  readonly
                  class="flex-1 rounded-lg border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-900 px-3 py-2 text-xs font-mono"
                />
                <button
                  type="button"
                  class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-3 py-2 text-sm text-white hover:bg-amber-700"
                  @click="copyText(displayedToken!, 'new-token')"
                >
                  <Check v-if="copiedKey === 'new-token'" class="w-4 h-4" />
                  <Copy v-else class="w-4 h-4" />
                  Copy
                </button>
              </div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 space-y-4">
              <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <Plus class="w-4 h-4" />
                Create token
              </h3>
              <div class="grid gap-4 sm:grid-cols-2">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                  <input
                    v-model="tokenForm.name"
                    type="text"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm"
                    placeholder="Cursor MCP"
                  />
                  <p v-if="tokenForm.errors.name" class="mt-1 text-xs text-red-600">{{ tokenForm.errors.name }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expires (optional)</label>
                  <input
                    v-model="tokenForm.expires_at"
                    type="datetime-local"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm"
                  />
                  <p v-if="tokenForm.errors.expires_at" class="mt-1 text-xs text-red-600">{{ tokenForm.errors.expires_at }}</p>
                </div>
              </div>
              <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                :disabled="tokenForm.processing"
                @click="createToken"
              >
                <Key class="w-4 h-4" />
                Create MCP token
              </button>
            </div>

            <div>
              <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Your MCP tokens</h3>
              <div v-if="tokens.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
                No MCP tokens yet.
              </div>
              <div v-else class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                  <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                      <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Name</th>
                      <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Created</th>
                      <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Last used</th>
                      <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Expires</th>
                      <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="token in tokens" :key="token.id">
                      <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ token.name }}</td>
                      <td class="px-4 py-3 text-gray-500">{{ token.created_at || '—' }}</td>
                      <td class="px-4 py-3 text-gray-500">{{ token.last_used_at || 'Never' }}</td>
                      <td class="px-4 py-3 text-gray-500">{{ token.expires_at || 'Never' }}</td>
                      <td class="px-4 py-3 text-right">
                        <button
                          type="button"
                          class="inline-flex items-center gap-1 text-red-600 hover:text-red-700"
                          @click="revokeToken(token.id)"
                        >
                          <Trash2 class="w-4 h-4" />
                          Revoke
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Connect -->
          <div v-show="activeTab === 'connect'" class="space-y-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Choose your MCP client, then copy the config. Replace
              <code>YOUR_MCP_TOKEN</code> with a token from the Tokens tab.
            </p>

            <div
              v-if="isLocalHttpsEndpoint"
              class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-100"
            >
              <p class="font-semibold">Local HTTPS certificate tip</p>
              <p class="mt-1 text-xs leading-relaxed text-amber-800 dark:text-amber-200">
                Cursor’s Node process does not trust Herd/Valet <code>.test</code> certificates by default
                (<code>UNABLE_TO_VERIFY_LEAF_SIGNATURE</code>). Use
                <strong>Cursor (mcp-remote)</strong> — the snippet includes
                <code>NODE_EXTRA_CA_CERTS</code> / <code>NODE_TLS_REJECT_UNAUTHORIZED</code> for local only.
                Do not use that insecure flag in production.
              </p>
            </div>

            <div class="max-w-md">
              <label for="mcp-platform" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Platform
              </label>
              <select
                id="mcp-platform"
                v-model="selectedPlatform"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option v-for="platform in platforms" :key="platform.id" :value="platform.id">
                  {{ platform.label }}
                </option>
              </select>
            </div>

            <div class="space-y-3 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
              <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div>
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <Terminal class="w-4 h-4" />
                    {{ activePlatform.label }}
                  </h3>
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ activePlatform.description }}
                    <template v-if="activePlatform.fileHint">
                      · Config file: <code class="text-[11px]">{{ activePlatform.fileHint }}</code>
                    </template>
                  </p>
                </div>
                <button
                  type="button"
                  class="inline-flex items-center gap-2 shrink-0 rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                  @click="copyText(activePlatform.snippet, activePlatform.id)"
                >
                  <Check v-if="copiedKey === activePlatform.id" class="w-4 h-4 text-green-600" />
                  <Copy v-else class="w-4 h-4" />
                  Copy JSON
                </button>
              </div>

              <pre class="overflow-x-auto rounded-xl bg-gray-900 text-gray-100 p-4 text-xs leading-relaxed">{{ activePlatform.snippet }}</pre>
            </div>
          </div>

          <!-- Tools -->
          <div v-show="activeTab === 'tools'" class="space-y-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Tools registered on this MCP server (read/write catalog, orders, customers, and safe settings).
            </p>
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                  <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Tool</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Description</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="tool in tools" :key="tool.name">
                    <td class="px-4 py-3 font-mono text-xs text-gray-900 dark:text-gray-100">{{ tool.name }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ tool.description }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { 
  Trash2, Settings, Power, CheckCircle, Eye, 
  AlertTriangle, Monitor, Plus, Package 
} from 'lucide-vue-next';

interface Theme {
  id: number;
  name: string;
  slug: string;
  description: string;
  version: string;
  author: string;
  screenshot: string | null;
  is_active: boolean;
  is_default: boolean;
  exists: boolean;
  supports: string[];
}

interface Props {
  themes: Theme[];
}

const props = defineProps<Props>();
const page = usePage();
const flash = computed(() => (page.props as any).flash || {});
const processing = ref<Record<string, boolean>>({});
const uploadingTheme = ref(false);
const themeFileInput = ref<HTMLInputElement | null>(null);

const activeTheme = computed(() => props.themes.find(t => t.is_active));
const otherThemes = computed(() => props.themes.filter(t => !t.is_active));

const activateTheme = (slug: string) => {
  if (processing.value[slug]) return;
  
  processing.value[slug] = true;
  
  router.post(
    `/admin/appearance/themes/${slug}/activate`,
    {},
    {
      preserveScroll: true,
      onFinish: () => {
        processing.value[slug] = false;
      },
    }
  );
};

const deleteTheme = (slug: string, name: string) => {
  if (processing.value[slug]) return;
  
  if (!confirm(`Are you sure you want to delete the theme "${name}"?`)) {
    return;
  }
  
  processing.value[slug] = true;
  
  router.delete(`/admin/appearance/themes/${slug}`, {
    preserveScroll: true,
    onFinish: () => {
      processing.value[slug] = false;
    },
  });
};

const goToSettings = (slug: string) => {
  router.get('/admin/appearance');
};

const triggerThemeUpload = () => {
  if (uploadingTheme.value) return;
  themeFileInput.value?.click();
};

const uploadTheme = (event: Event) => {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];

  if (!file) return;

  uploadingTheme.value = true;

  const formData = new FormData();
  formData.append('theme', file);

  router.post('/admin/appearance/themes/upload', formData, {
    preserveScroll: true,
    forceFormData: true,
    onFinish: () => {
      uploadingTheme.value = false;
      input.value = '';
    },
  });
};
</script>

<template>
  <AdminLayout title="Themes">
    <Head title="Themes" />

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Themes</h1>
            <p class="mt-1 text-sm text-gray-500">
              Manage your store's appearance and layout
            </p>
          </div>
          <input
            ref="themeFileInput"
            type="file"
            accept=".zip,application/zip"
            class="hidden"
            @change="uploadTheme"
          />
        </div>

        <!-- Alerts -->
        <div v-if="flash.success" class="mb-6 rounded-lg bg-green-50 p-4 border border-green-200">
          <div class="flex">
            <CheckCircle class="h-5 w-5 text-green-500" />
            <p class="ml-3 text-sm font-medium text-green-800">
              {{ flash.success }}
            </p>
          </div>
        </div>

        <div v-if="flash.error" class="mb-6 rounded-lg bg-red-50 p-4 border border-red-200">
          <div class="flex">
            <AlertTriangle class="h-5 w-5 text-red-500" />
            <p class="ml-3 text-sm font-medium text-red-800">
              {{ flash.error }}
            </p>
          </div>
        </div>

        <!-- Active Theme -->
        <div v-if="activeTheme" class="mb-12">
          <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Current Theme</h2>
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col md:flex-row">
            <!-- Screenshot -->
            <div class="md:w-1/2 lg:w-2/5 aspect-video bg-gray-100 relative group overflow-hidden">
                <img
                  v-if="activeTheme.screenshot"
                  :src="activeTheme.screenshot"
                  :alt="activeTheme.name"
                  class="w-full h-full object-cover object-top transition-transform duration-700 group-hover:scale-105"
                />
                <div v-else class="w-full h-full flex items-center justify-center bg-gray-50">
                  <Monitor class="w-16 h-16 text-gray-300" />
                </div>
                <div class="absolute top-4 left-4">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-500 text-white shadow-sm ring-2 ring-white">
                    <CheckCircle class="w-3.5 h-3.5 mr-1" />
                    Active
                  </span>
                </div>
            </div>
            
            <!-- Details -->
            <div class="p-6 md:p-8 md:w-1/2 lg:w-3/5 flex flex-col justify-center">
              <div class="flex items-start justify-between">
                <div>
                  <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ activeTheme.name }}</h3>
                  <p class="text-gray-500 mb-4 text-base leading-relaxed">{{ activeTheme.description }}</p>
                  <div class="flex items-center gap-4 text-sm text-gray-500">
                    <div class="flex items-center">
                      <span class="font-medium text-gray-900 mr-1">Version:</span> {{ activeTheme.version }}
                    </div>
                    <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                    <div class="flex items-center">
                      <span class="font-medium text-gray-900 mr-1">Author:</span> {{ activeTheme.author }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-8 flex flex-wrap gap-3">
                <a
                  href="/"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center px-5 py-2.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors"
                  title="Preview active theme"
                >
                  <Eye class="w-4 h-4 mr-2" />
                  Preview
                </a>
                <button
                  @click="goToSettings(activeTheme.slug)"
                  class="inline-flex items-center px-5 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                  <Settings class="w-4 h-4 mr-2" />
                  Customize
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Other Themes -->
        <div v-if="otherThemes.length > 0">
          <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Theme Library</h2>
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div
              v-for="theme in otherThemes"
              :key="theme.id"
              class="group relative bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-300 flex flex-col h-full"
            >
              <!-- Screenshot -->
              <div class="aspect-video bg-gray-100 relative overflow-hidden">
                <img
                  v-if="theme.screenshot"
                  :src="theme.screenshot"
                  :alt="theme.name"
                  class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div v-else class="w-full h-full flex items-center justify-center bg-gray-50">
                  <Monitor class="w-12 h-12 text-gray-300" />
                </div>
                
                <!-- Overlay Actions -->
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-3">
                    <button
                      v-if="theme.exists"
                      @click="activateTheme(theme.slug)"
                      :disabled="processing[theme.slug]"
                      class="inline-flex items-center px-4 py-2 bg-white rounded-lg text-sm font-semibold text-gray-900 shadow-lg hover:bg-gray-50 focus:outline-none transform translate-y-2 group-hover:translate-y-0 transition-all duration-300"
                    >
                        <svg v-if="processing[theme.slug]" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-900" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <Power v-else class="w-4 h-4 mr-2" />
                        Activate
                    </button>
                </div>
              </div>

              <!-- Content -->
              <div class="p-5 flex-1 flex flex-col">
                <div class="flex items-start justify-between mb-2">
                  <div>
                    <h3 class="text-lg font-bold text-gray-900 leading-tight">{{ theme.name }}</h3>
                    <div class="text-xs text-gray-500 mt-1">v{{ theme.version }} by {{ theme.author }}</div>
                  </div>
                  <span v-if="theme.is_default" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/10">Default</span>
                </div>
                
                <p class="text-sm text-gray-500 mb-4 line-clamp-2">{{ theme.description }}</p>

                <div class="mt-auto border-t border-gray-100 pt-4 flex items-center justify-between">
                   <div v-if="!theme.exists" class="flex items-center text-xs text-amber-600 font-medium">
                       <AlertTriangle class="w-3.5 h-3.5 mr-1" />
                       Files missing
                   </div>
                   <div v-else class="text-xs text-gray-400 font-medium">
                        {{ theme.exists ? 'Ready to use' : 'Not installed' }}
                   </div>

                   <button
                    v-if="!theme.is_active && !theme.is_default"
                    @click="deleteTheme(theme.slug, theme.name)"
                    class="text-gray-400 hover:text-red-600 transition-colors p-1"
                    title="Delete Theme"
                   >
                       <Trash2 class="w-4 h-4" />
                   </button>
                </div>
              </div>
            </div>
            
            <!-- Upload Placeholder Card -->
             <div 
                @click="triggerThemeUpload"
                class="group relative rounded-xl border-2 border-dashed border-gray-300 hover:border-gray-400 bg-gray-50 hover:bg-gray-100 transition-all cursor-pointer min-h-[300px] flex flex-col items-center justify-center text-center p-6"
              >
                 <div class="w-16 h-16 rounded-full bg-white shadow-sm flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                     <Plus class="w-8 h-8 text-gray-400 group-hover:text-gray-600" />
                 </div>
                 <h3 class="text-base font-semibold text-gray-900">Add New Theme</h3>
                 <p class="text-sm text-gray-500 mt-1 max-w-[200px]">Upload a .zip file containing your theme files</p>
             </div>
          </div>
        </div>

        <!-- Empty State (Only if no active theme AND no other themes) -->
        <div
          v-if="props.themes.length === 0"
          class="text-center py-20 bg-white rounded-xl border border-dashed border-gray-300 cursor-pointer hover:bg-gray-50 transition-colors"
          @click="triggerThemeUpload"
        >
          <Package class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-4 text-lg font-medium text-gray-900">No themes installed</h3>
          <p class="mt-2 text-sm text-gray-500">Click here to upload your first theme.</p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

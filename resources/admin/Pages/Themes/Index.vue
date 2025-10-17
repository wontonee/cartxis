<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

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
const processing = ref<Record<string, boolean>>({});

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
  router.get(`/admin/appearance/themes/${slug}/settings`);
};
</script>

<template>
  <AdminLayout>
    <Head title="Themes" />

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Themes</h1>
            <p class="mt-2 text-sm text-gray-600">
              Customize the look and feel of your store
            </p>
          </div>
          
          <button
            type="button"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Upload Theme
          </button>
        </div>

        <!-- Success/Error Messages -->
        <div v-if="page.props.flash.success" class="mb-6 rounded-md bg-green-50 p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <p class="ml-3 text-sm font-medium text-green-800">
              {{ page.props.flash.success }}
            </p>
          </div>
        </div>

        <div v-if="page.props.flash.error" class="mb-6 rounded-md bg-red-50 p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <p class="ml-3 text-sm font-medium text-red-800">
              {{ page.props.flash.error }}
            </p>
          </div>
        </div>

        <!-- Themes Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="theme in props.themes"
            :key="theme.id"
            class="relative rounded-lg border border-gray-300 bg-white overflow-hidden hover:shadow-lg transition-shadow duration-200"
          >
            <!-- Screenshot -->
            <div class="aspect-w-16 aspect-h-9 bg-gray-100">
              <img
                v-if="theme.screenshot"
                :src="theme.screenshot"
                :alt="theme.name"
                class="w-full h-48 object-cover"
              />
              <div v-else class="w-full h-48 flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>

            <!-- Active Badge -->
            <div v-if="theme.is_active" class="absolute top-3 right-3">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500 text-white shadow-sm">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Active
              </span>
            </div>

            <!-- Theme Info -->
            <div class="p-5">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <h3 class="text-lg font-semibold text-gray-900">
                    {{ theme.name }}
                    <span v-if="theme.is_default" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                      Default
                    </span>
                  </h3>
                  <p class="mt-1 text-sm text-gray-500">{{ theme.description }}</p>
                  <div class="mt-2 flex items-center text-xs text-gray-500">
                    <span>By {{ theme.author }}</span>
                    <span class="mx-2">•</span>
                    <span>v{{ theme.version }}</span>
                  </div>
                </div>
              </div>

              <!-- Warning if theme files not exist -->
              <div v-if="!theme.exists" class="mt-3 rounded-md bg-yellow-50 p-3">
                <p class="text-xs text-yellow-800">
                  ⚠️ Theme files are missing. Please reinstall this theme.
                </p>
              </div>

              <!-- Actions -->
              <div class="mt-5 flex space-x-2">
                <button
                  v-if="!theme.is_active && theme.exists"
                  @click="activateTheme(theme.slug)"
                  :disabled="processing[theme.slug]"
                  class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="processing[theme.slug]" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Activate
                </button>
                
                <button
                  v-if="theme.exists"
                  @click="goToSettings(theme.slug)"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </button>

                <button
                  v-if="!theme.is_active && !theme.is_default"
                  @click="deleteTheme(theme.slug, theme.name)"
                  :disabled="processing[theme.slug]"
                  class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="props.themes.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No themes</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by uploading a theme.</p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

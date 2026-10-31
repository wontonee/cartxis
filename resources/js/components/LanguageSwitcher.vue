<script setup lang="ts">
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { ChevronDown, Languages } from 'lucide-vue-next';

interface LocaleOption {
  code: string;
  name: string;
  native_name: string | null;
  direction: string;
  is_default: boolean;
}

withDefaults(
  defineProps<{
    compact?: boolean;
  }>(),
  { compact: false },
);

const page = usePage();
const open = ref(false);
const switching = ref(false);

const i18n = computed(() => (page.props.i18n as Record<string, unknown>) || {});
const locales = computed<LocaleOption[]>(() => (i18n.value.availableLocales as LocaleOption[]) || []);
const current = computed(() => (i18n.value.locale as string) || 'en');

const currentLabel = computed(() => {
  const match = locales.value.find((l) => l.code === current.value);
  return match?.native_name || match?.name || current.value.toUpperCase();
});

/**
 * Full document navigation (Bagisto-style). Soft Inertia/SPA updates leave
 * server-rendered props, menus, and CMS blocks on the old locale until a
 * hard reload — so the switch itself must remount the page.
 */
const switchTo = (code: string) => {
  open.value = false;
  if (code === current.value || switching.value) return;

  switching.value = true;
  // Relative path avoids APP_URL scheme/host mismatches rejecting the redirect.
  const redirect = `${window.location.pathname}${window.location.search}${window.location.hash}`;
  window.location.assign(`/locale/${code}?redirect=${encodeURIComponent(redirect)}`);
};
</script>

<template>
  <div v-if="locales.length > 1" class="relative">
    <button
      type="button"
      class="inline-flex items-center gap-1.5 rounded-md px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-100 disabled:opacity-60 dark:text-slate-200 dark:hover:bg-slate-800"
      :aria-label="$t('common.language')"
      :aria-expanded="open"
      :disabled="switching"
      @click="open = !open"
    >
      <Languages class="h-4 w-4 shrink-0" />
      <span v-if="!compact">{{ currentLabel }}</span>
      <span v-else class="uppercase">{{ current }}</span>
      <ChevronDown class="h-3.5 w-3.5 opacity-60" />
    </button>

    <div
      v-if="open"
      class="absolute end-0 z-50 mt-1 min-w-[10rem] overflow-hidden rounded-md border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-900"
    >
      <button
        v-for="locale in locales"
        :key="locale.code"
        type="button"
        class="flex w-full items-center justify-between gap-3 px-3 py-2 text-start text-sm hover:bg-slate-50 dark:hover:bg-slate-800"
        :class="locale.code === current ? 'bg-slate-50 font-medium text-slate-900 dark:bg-slate-800 dark:text-white' : 'text-slate-700 dark:text-slate-200'"
        :disabled="switching"
        @click="switchTo(locale.code)"
      >
        <span>{{ locale.native_name || locale.name }}</span>
        <span class="text-xs uppercase text-slate-400">{{ locale.code }}</span>
      </button>
    </div>

    <button
      v-if="open"
      type="button"
      class="fixed inset-0 z-40 cursor-default"
      aria-hidden="true"
      @click="open = false"
    />
  </div>
</template>

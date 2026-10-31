<script setup lang="ts">
import { computed, reactive, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface LocaleOption {
  code: string;
  name: string;
  native_name?: string | null;
  is_default?: boolean;
}

const props = defineProps<{
  locales: LocaleOption[];
  modelValue: Record<string, Record<string, string | null | undefined>>;
  fields: Array<{ key: string; label: string; type?: 'text' | 'textarea' }>;
  excludeDefault?: boolean;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: Record<string, Record<string, string | null | undefined>>];
}>();

const editableLocales = computed(() =>
  props.locales.filter((l) => !(props.excludeDefault && l.is_default)),
);

const draft = reactive<Record<string, Record<string, string>>>({});

const ensureLocale = (code: string) => {
  if (!draft[code]) {
    draft[code] = {};
  }
  for (const field of props.fields) {
    if (draft[code][field.key] === undefined) {
      const existing = props.modelValue?.[code]?.[field.key];
      draft[code][field.key] = existing == null ? '' : String(existing);
    }
  }
};

watch(
  () => [props.locales, props.modelValue],
  () => {
    for (const locale of editableLocales.value) {
      ensureLocale(locale.code);
    }
  },
  { immediate: true, deep: true },
);

const onInput = (locale: string, key: string, value: string) => {
  ensureLocale(locale);
  draft[locale][key] = value;
  emit('update:modelValue', { ...props.modelValue, [locale]: { ...draft[locale] } });
};
</script>

<template>
  <div class="space-y-6 rounded-lg border border-slate-200 bg-white p-4">
    <div>
      <h3 class="text-sm font-semibold text-slate-900">Translations</h3>
      <p class="mt-1 text-xs text-slate-500">
        Default locale content is edited in the main form. Add optional translations for other active languages.
      </p>
    </div>

    <div
      v-for="locale in editableLocales"
      :key="locale.code"
      class="space-y-3 rounded-md border border-slate-100 p-3"
    >
      <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-slate-800">
          {{ locale.native_name || locale.name }}
          <span class="ms-2 text-xs uppercase text-slate-400">{{ locale.code }}</span>
        </p>
      </div>

      <div
        v-for="field in fields"
        :key="`${locale.code}-${field.key}`"
        class="space-y-1.5"
      >
        <Label :for="`${locale.code}-${field.key}`">{{ field.label }}</Label>
        <Textarea
          v-if="field.type === 'textarea'"
          :id="`${locale.code}-${field.key}`"
          :model-value="draft[locale.code]?.[field.key] || ''"
          rows="3"
          @update:model-value="onInput(locale.code, field.key, String($event))"
        />
        <Input
          v-else
          :id="`${locale.code}-${field.key}`"
          :model-value="draft[locale.code]?.[field.key] || ''"
          @update:model-value="onInput(locale.code, field.key, String($event))"
        />
      </div>
    </div>

    <p v-if="editableLocales.length === 0" class="text-sm text-slate-500">
      Activate additional languages under Settings → Locales to manage translations.
    </p>
  </div>
</template>

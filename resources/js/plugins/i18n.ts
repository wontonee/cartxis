import { createI18n, type I18n } from 'vue-i18n';
import en from '../locales/en.json';

export type CartxisI18nMessages = typeof en;

let i18nInstance: I18n | null = null;

export function createCartxisI18n(options?: {
    locale?: string;
    fallbackLocale?: string;
    messages?: Record<string, CartxisI18nMessages | Record<string, unknown>>;
}): I18n {
    const locale = options?.locale || 'en';
    const fallbackLocale = options?.fallbackLocale || 'en';
    const messages = {
        en,
        ...(options?.messages || {}),
    };

    i18nInstance = createI18n({
        legacy: false,
        globalInjection: true,
        locale,
        fallbackLocale,
        messages: messages as Record<string, CartxisI18nMessages>,
        missingWarn: false,
        fallbackWarn: false,
    });

    return i18nInstance;
}

export function syncI18nFromPageProps(props: Record<string, unknown>): void {
    if (!i18nInstance) {
        return;
    }

    const i18n = props.i18n as
        | {
              locale?: string;
              fallbackLocale?: string;
              messages?: Record<string, unknown>;
              direction?: string;
          }
        | undefined;

    if (!i18n) {
        return;
    }

    const global = i18nInstance.global as {
        locale: { value: string } | string;
        fallbackLocale: { value: string | string[] } | string | string[];
        setLocaleMessage: (locale: string, messages: CartxisI18nMessages) => void;
    };

    // Register messages before flipping locale so $t() resolves immediately.
    if (i18n.messages && i18n.locale) {
        global.setLocaleMessage(i18n.locale, i18n.messages as CartxisI18nMessages);
    }

    if (i18n.fallbackLocale) {
        if (typeof global.fallbackLocale === 'object' && global.fallbackLocale !== null && 'value' in global.fallbackLocale) {
            global.fallbackLocale.value = i18n.fallbackLocale;
        } else {
            (global as { fallbackLocale: string }).fallbackLocale = i18n.fallbackLocale;
        }
    }

    if (i18n.locale) {
        if (typeof global.locale === 'object' && global.locale !== null && 'value' in global.locale) {
            global.locale.value = i18n.locale;
        } else {
            (global as { locale: string }).locale = i18n.locale;
        }
    }

    if (typeof document !== 'undefined') {
        document.documentElement.lang = (i18n.locale || 'en').replace('_', '-');
        document.documentElement.dir = i18n.direction === 'rtl' ? 'rtl' : 'ltr';
    }
}

export function getI18n(): I18n | null {
    return i18nInstance;
}

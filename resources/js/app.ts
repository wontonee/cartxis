import '../css/app.css';

import axios from 'axios';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { initializeTheme } from './composables/useAppearance';
import { resolveTemplatePage } from './lib/resolveTemplatePage';
import { createCartxisI18n, syncI18nFromPageProps } from './plugins/i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pinia = createPinia();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        // Legacy Theme:: / themes/ component names → templates/{slug}/pages/...
        if (name.startsWith('themes/')) {
            name = name
                .replace(/^themes\//, 'templates/')
                .replace(/^templates\/([^/]+)\/resources\/views\//, 'templates/$1/');
        }

        // Extension pages (e.g. "Extensions/SalesChat/Settings") — resolved from
        // extension/*/src/Resources/js/pages/**/*.vue so that premium extensions
        // stay completely self-contained and never pollute the main app source.
        if (name.startsWith('Extensions/')) {
            const componentPath = name.slice('Extensions/'.length) // e.g. "SalesChat/Settings"
            const pages = import.meta.glob<DefineComponent>('../../extension/*/src/Resources/js/pages/**/*.vue')
            const entry = Object.entries(pages).find(([key]) => key.endsWith(`/pages/${componentPath}.vue`))
            if (entry) return entry[1]()
            throw new Error(`Extension page not found: ${name}`)
        }

        // Storefront template pages — glob first, then Vite manifest fallback for
        // themes installed after the main app bundle was last built.
        if (name.startsWith('templates/')) {
            return resolveTemplatePage(name)
        }

        // Default — main app pages directory
        return resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        )
    },
    setup({ el, App, props, plugin }) {
        const pageProps = (props.initialPage?.props || {}) as Record<string, unknown>;
        const i18nProps = (pageProps.i18n || {}) as {
            locale?: string;
            fallbackLocale?: string;
            messages?: Record<string, unknown>;
        };

        const i18n = createCartxisI18n({
            locale: i18nProps.locale || 'en',
            fallbackLocale: i18nProps.fallbackLocale || 'en',
            messages: i18nProps.locale && i18nProps.messages
                ? { [i18nProps.locale]: i18nProps.messages }
                : undefined,
        });

        syncI18nFromPageProps(pageProps);

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(i18n)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
const forceLightStorefront = typeof document !== 'undefined'
    && document.body?.dataset?.forceLight === '1';

if (forceLightStorefront) {
    document.documentElement.classList.remove('dark');
} else {
    initializeTheme();
}

// Sync locale + CSRF after every Inertia navigation.
router.on('navigate', (event) => {
    const pageProps = event.detail.page.props as Record<string, unknown>;
    syncI18nFromPageProps(pageProps);

    const token = pageProps.csrf_token as string | undefined;
    if (token) {
        const meta = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
        if (meta) meta.content = token;
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    }
});
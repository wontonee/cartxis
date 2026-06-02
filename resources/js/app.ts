import '../css/app.css';

import axios from 'axios';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { initializeTheme } from './composables/useAppearance';
import { resolveTemplatePage } from './lib/resolveTemplatePage';

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
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
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

// Permanently fix 419 PAGE EXPIRED errors caused by stale CSRF tokens in the
// Inertia SPA. Inertia v2 uses Axios internally but does NOT inject CSRF headers
// itself — it relies on Axios's XSRF-TOKEN cookie auto-read, which can silently
// fail when using full absolute URLs (Axios same-origin check).  
//
// The robust solution: after every Inertia navigation (including the first), pull
// the fresh token from the server's shared props and inject it DIRECTLY into
// Axios's default headers. Because Inertia's own getHeaders() is merged with
// axios.defaults, this X-CSRF-TOKEN arrives on every subsequent POST/PUT/DELETE.
router.on('navigate', (event) => {
    const token = (event.detail.page.props as Record<string, unknown>).csrf_token as string | undefined;
    if (token) {
        // 1. Keep <meta name="csrf-token"> in sync (used by non-Inertia forms)
        const meta = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
        if (meta) meta.content = token;
        // 2. Inject into Axios defaults so every Inertia XHR carries the header
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    }
});
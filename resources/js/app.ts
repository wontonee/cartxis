import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pinia = createPinia();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        // Check if it's a theme component (e.g., "themes/cartxis-default/pages/Home")
        if (name.startsWith('themes/')) {
            // Extract theme slug and component path
            const parts = name.split('/');
            const themeSlug = parts[1]; // e.g., "cartxis-default"
            const componentPath = parts.slice(2).join('/'); // e.g., "pages/Home"
            
            return resolvePageComponent(
                `../../themes/${themeSlug}/resources/views/${componentPath}.vue`,
                import.meta.glob<DefineComponent>('../../themes/**/resources/views/**/*.vue'),
            );
        }
        
        // Default to pages directory
        return resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        );
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
initializeTheme();
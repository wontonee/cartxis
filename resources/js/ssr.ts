import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createSSRApp, DefineComponent, h } from 'vue';
import { renderToString } from 'vue/server-renderer';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer(
    (page) =>
        createInertiaApp({
            page,
            render: renderToString,
            title: (title) => (title ? `${title} - ${appName}` : appName),
            resolve: (name) => {
                if (name.startsWith('themes/')) {
                    name = name
                        .replace(/^themes\//, 'templates/')
                        .replace(/^templates\/([^/]+)\/resources\/views\//, 'templates/$1/');
                }

                if (name.startsWith('templates/')) {
                    const parts = name.split('/');
                    const themeSlug = parts[1];
                    const componentPath = parts.slice(2).join('/');

                    const pages = import.meta.glob<DefineComponent>(
                        '../../templates/storefront/**/resources/views/**/*.vue',
                    );
                    const suffix = `/resources/views/${componentPath}.vue`;
                    const entry = Object.entries(pages).find(
                        ([key]) => key.includes(`/${themeSlug}/`) && key.endsWith(suffix),
                    );

                    if (entry) {
                        return entry[1]();
                    }

                    throw new Error(`Template page not found: ${name}`);
                }

                return resolvePageComponent(
                    `./pages/${name}.vue`,
                    import.meta.glob<DefineComponent>('./pages/**/*.vue'),
                );
            },
            setup: ({ App, props, plugin }) =>
                createSSRApp({ render: () => h(App, props) }).use(plugin),
        }),
    { cluster: true },
);

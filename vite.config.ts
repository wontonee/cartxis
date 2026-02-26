import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.ts',
                'resources/admin/css/styles.css',
            ],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: [
            { find: /^@\/Layouts/, replacement: '/resources/js/layouts' },
            { find: '@admin', replacement: '/resources/admin' },
            { find: '@themes', replacement: '/themes' },
            { find: '@', replacement: '/resources/js' },
        ],
    },
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    // Vite normalises paths to forward-slashes even on Windows,
                    // but we normalise anyway to be safe.
                    const nid = id.replace(/\\/g, '/');

                    // ── Core Vue ecosystem ──────────────────────────────────
                    // lucide-vue-next is placed here to break the circular dep:
                    //   vendor-vue(@inertiajs→axios) → vendor(lucide→vue) → vendor-vue
                    if (nid.includes('node_modules/vue/') ||
                        nid.includes('node_modules/vue-demi/') ||
                        nid.includes('node_modules/@vue/') ||
                        nid.includes('node_modules/@inertiajs/') ||
                        nid.includes('node_modules/@vueuse/') ||
                        nid.includes('node_modules/pinia/') ||
                        nid.includes('node_modules/lucide-vue-next/')) {
                        return 'vendor-vue';
                    }

                    // ── Rich-text editor (TipTap + ProseMirror) ─────────────
                    if (nid.includes('node_modules/@tiptap/') ||
                        nid.includes('node_modules/prosemirror-') ||
                        nid.includes('node_modules/@prosemirror/')) {
                        return 'vendor-editor';
                    }

                    // ── Charts ──────────────────────────────────────────────
                    if (nid.includes('node_modules/chart.js/') ||
                        nid.includes('node_modules/vue-chartjs/')) {
                        return 'vendor-charts';
                    }

                    // ── Reka UI + its runtime dependencies ──────────────────
                    // reka-ui depends on @floating-ui, @internationalized,
                    // @tanstack/vue-virtual, aria-hidden, defu, ohash.
                    // Grouping them avoids inflating the catch-all vendor chunk.
                    if (nid.includes('node_modules/reka-ui/') ||
                        nid.includes('node_modules/@floating-ui/') ||
                        nid.includes('node_modules/@internationalized/') ||
                        nid.includes('node_modules/@tanstack/') ||
                        nid.includes('node_modules/aria-hidden/') ||
                        nid.includes('node_modules/defu/') ||
                        nid.includes('node_modules/ohash/')) {
                        return 'vendor-reka';
                    }

                    // ── All other node_modules ──────────────────────────────
                    if (nid.includes('node_modules/')) {
                        return 'vendor';
                    }
                    // Application code — let Rollup auto-split
                },
            },
        },
    },
});

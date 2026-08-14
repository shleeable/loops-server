import { defineConfig } from 'vite'
import { fileURLToPath, URL } from 'node:url'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'
import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/fonts.css',
                'resources/sass/next.css',
                'resources/js/embed.js',
                'resources/css/embed.css'
            ],
            refresh: true
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false
                }
            }
        }),
        tailwindcss(),
        VueI18nPlugin({
            include: [
                fileURLToPath(new URL('./resources/js/i18n/locales/*.json', import.meta.url))
            ],
            strictMessage: false,
            dropMessageCompiler: true
        }),
        AutoImport({
            imports: [
                'vue',
                'vue-router',
                'pinia',
                '@vueuse/core',
                {
                    '@tanstack/vue-query': [
                        'useQuery',
                        'useMutation',
                        'useQueryClient',
                        'useInfiniteQuery'
                    ],
                    '@unhead/vue': ['useHead', 'useSeoMeta']
                }
            ],
            dirs: ['resources/js/composables', 'resources/js/stores'],
            vueTemplate: true,
            dts: 'resources/js/auto-imports.d.ts',
            eslintrc: {
                enabled: true,
                filepath: './.eslintrc-auto-import.json'
            }
        }),
        Components({
            dirs: ['resources/js/components'],
            extensions: ['vue'],
            deep: true,
            dts: 'resources/js/components.d.ts',
            directoryAsNamespace: false,
            collapseSamePrefixes: false
        })
    ],
    resolve: {
        alias: {
            '~': '/resources/js',
            '@': '/resources/js',
            vue: 'vue/dist/vue.runtime.esm-bundler.js'
        }
    },
    optimizeDeps: {
        exclude: ['mediabunny']
    },
    build: {
        minify: 'esbuild',
        cssCodeSplit: true,
        chunkSizeWarningLimit: 1024,
        sourcemap: false,
        commonjsOptions: {
            transformMixedEsModules: true,
            strictRequires: 'auto'
        },
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (!id.includes('node_modules')) return
                    if (
                        /[\\/](vue|@vue|vue-router|vue-i18n|@intlify|pinia|@vueuse|@unhead)[\\/]/.test(
                            id
                        )
                    ) {
                        return 'vendor-core'
                    }
                }
            }
        }
    },
    define: {
        __VUE_PROD_DEVTOOLS__: false,
        __VUE_OPTIONS_API__: true,
        __VUE_I18N_LEGACY_API__: false,
        __VUE_I18N_FULL_INSTALL__: false,
        __INTLIFY_PROD_DEVTOOLS__: false
    }
})

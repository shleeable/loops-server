import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js', 'resources/css/fonts.css', 'resources/sass/next.css'],
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

                    if (/[\\/](vue|vue-router|pinia|@vueuse)[\\/]/.test(id)) {
                        return 'vendor-vue'
                    }

                    if (id.includes('@unhead') || id.includes('vue-i18n')) {
                        return 'vendor-meta'
                    }

                    if (id.includes('@headlessui') || id.includes('@heroicons')) {
                        return 'vendor-ui'
                    }

                    if (id.includes('@tanstack') || id.includes('axios')) {
                        return 'vendor-data'
                    }

                    if (id.includes('echarts') || id.includes('zrender')) {
                        return 'vendor-charts'
                    }

                    if (
                        id.includes('video.js') ||
                        id.includes('@videojs') ||
                        id.includes('videojs-') ||
                        id.includes('mpd-parser') ||
                        id.includes('m3u8-parser') ||
                        id.includes('mux.js') ||
                        id.includes('aes-decrypter') ||
                        id.includes('@babel/runtime') ||
                        id.includes('global/window') ||
                        id.includes('global/document') ||
                        id.includes('mediabunny')
                    ) {
                        return 'vendor-video'
                    }

                    if (
                        id.includes('@tiptap') ||
                        id.includes('prosemirror') ||
                        id.includes('md-editor-v3') ||
                        id.includes('markdown-it') ||
                        id.includes('highlight.js')
                    ) {
                        return 'vendor-editor'
                    }

                    if (
                        id.includes('vue-advanced-cropper') ||
                        id.includes('emoji-mart-vue-fast') ||
                        id.includes('vue-draggable-next')
                    ) {
                        return 'vendor-media-tools'
                    }

                    if (id.includes('date-fns')) {
                        return 'vendor-utils'
                    }

                    return 'vendor'
                }
            }
        }
    },
    define: {
        __VUE_PROD_DEVTOOLS__: false,
        __VUE_OPTIONS_API__: true
    }
})

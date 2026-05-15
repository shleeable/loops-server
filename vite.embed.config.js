import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [vue()],
    build: {
        outDir: 'public',
        emptyOutDir: false,
        lib: {
            entry: 'resources/js/loops-embed.js',
            name: 'LoopsEmbed',
            formats: ['iife'],
            fileName: () => 'embed.js'
        },
        rollupOptions: {
            output: {
                assetFileNames: 'embed.[ext]'
            }
        }
    }
})

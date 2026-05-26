import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        outDir: 'publishable/assets/build',
        manifest: true,
        rollupOptions: {
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: '[name].[ext]',
            },
        },
    },
})

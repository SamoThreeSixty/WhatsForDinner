import {defineConfig} from 'vite';
import tailwindcss from "@tailwindcss/vite";
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';

export default defineConfig({
    resolve: {
        alias: {
            '@src': path.resolve('src')
        }
    },

    plugins: [
        laravel({
            input: ['src/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        vue(),
    ],
});

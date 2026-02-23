import {defineConfig} from 'vite';
import tailwindcss from "@tailwindcss/vite";
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import {fileURLToPath, URL} from "node:url";

export default defineConfig({
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url)),
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

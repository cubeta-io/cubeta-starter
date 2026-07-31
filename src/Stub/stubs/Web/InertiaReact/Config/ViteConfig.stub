import inertia from '@inertiajs/vite'
import laravel from 'laravel-vite-plugin'
import { defineConfig } from 'vite'
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/cubeta-starter.tsx'],
            refresh: true,
        }),
        inertia(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/sass/cubeta-starter.scss', 'resources/js/cubeta-starter.js'],
            refresh: true,
        }),
    ],

    assets: {
        path: 'public/assets',
    },

});

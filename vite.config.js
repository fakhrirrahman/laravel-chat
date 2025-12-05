import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/chat-init.js', 'resources/js/video-call.js'],
            refresh: true,
        }),
    ],
    define: {
        global: 'globalThis',
    },
});

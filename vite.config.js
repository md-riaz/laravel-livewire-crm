import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js', 
                'resources/js/sip-agent.js',
                'resources/js/click-to-call.js'
            ],
            refresh: true,
        }),
    ],
});

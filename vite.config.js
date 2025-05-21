import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // Generate manifest.json in outDir
        manifest: true,
        rollupOptions: {
            // Ensure proper output format
            output: {
                manualChunks: undefined
            }
        }
    }
});

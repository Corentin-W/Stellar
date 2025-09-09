import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],

    // Optimisations pour l'application télescope
    resolve: {
        alias: {
            '@': '/resources',
            '@js': '/resources/js',
            '@css': '/resources/css',
        },
    },

    // Configuration du build pour la production
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    // Séparer Alpine.js dans son propre chunk
                    alpine: ['alpinejs'],
                },
            },
        },
        // Optimisation pour les assets
        assetsInlineLimit: 4096,
        cssCodeSplit: true,
    },

    // Configuration du serveur de développement
    server: {
        hmr: {
            host: 'localhost',
        },
        // Proxy pour l'API si nécessaire
        proxy: {
            // Exemple : rediriger les appels API vers un serveur externe
            // '/api/telescope': {
            //     target: 'http://your-telescope-api.local',
            //     changeOrigin: true,
            // }
        }
    },

    // Optimisations des dépendances
    optimizeDeps: {
        include: ['alpinejs'],
    },

    // Configuration CSS
    css: {
        devSourcemap: true,
    },
});

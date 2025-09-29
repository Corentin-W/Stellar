// ====================================
// resources/js/bootstrap.js
// ====================================

/**
 * Configuration Bootstrap pour AstroSphere
 * Compatible avec Vite (Laravel 11+)
 */

// Import d'Axios (compatible Vite)
import axios from 'axios';
window.axios = axios;

// Configuration par défaut d'Axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Token CSRF pour Laravel
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}

/**
 * Configuration des requêtes API
 */
window.api = {
    /**
     * Wrapper pour les appels API avec gestion d'erreurs
     */
    async call(method, url, data = {}) {
        try {
            const config = {
                method,
                url,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            };

            if (method.toLowerCase() !== 'get') {
                config.data = data;
            } else if (Object.keys(data).length > 0) {
                config.params = data;
            }

            const response = await window.axios(config);
            return response.data;
        } catch (error) {
            console.error('API Error:', error);

            if (error.response) {
                const message = error.response.data.message || 'Server error occurred';
                throw new Error(message);
            } else if (error.request) {
                throw new Error('Network error - please check your connection');
            } else {
                throw new Error('Request failed - please try again');
            }
        }
    },

    // Méthodes helper
    get(url, params = {}) {
        return this.call('GET', url, params);
    },

    post(url, data = {}) {
        return this.call('POST', url, data);
    },

    put(url, data = {}) {
        return this.call('PUT', url, data);
    },

    delete(url, data = {}) {
        return this.call('DELETE', url, data);
    }
};

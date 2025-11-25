/**
 * Application Configuration for JavaScript
 * Dynamic path detection and URL construction
 */

(function() {
    'use strict';
    
    // Detect base URL from the current script location
    function detectBaseUrl() {
        // Get all script tags
        const scripts = document.getElementsByTagName('script');
        
        // Look for this config script or any script in public/assets/js
        for (let script of scripts) {
            const src = script.src;
            if (src && src.includes('/public/assets/js/')) {
                // Extract base URL from script src
                const match = src.match(/^(.*?)\/public\/assets\/js\//);
                if (match) {
                    return match[1];
                }
            }
        }
        
        // Fallback: try to detect from current page URL
        const path = window.location.pathname;
        const match = path.match(/^(.*?\/[^\/]+)\//);
        if (match) {
            // Try to find the root by looking for common folder names
            const segments = path.split('/').filter(s => s);
            for (let i = segments.length - 1; i >= 0; i--) {
                if (segments[i] === 'modules' || segments[i] === 'public') {
                    return window.location.origin + '/' + segments.slice(0, i).join('/');
                }
            }
        }
        
        // Last resort: use origin
        return window.location.origin;
    }
    
    // Initialize base URL
    const BASE_URL = detectBaseUrl();
    
    // Create global App configuration object
    window.App = window.App || {};
    
    // Configuration constants
    window.App.config = {
        baseUrl: BASE_URL,
        
        // URL paths
        urls: {
            auth: BASE_URL + '/modules/authentication',
            dashboard: BASE_URL + '/modules/dashboard',
            documents: BASE_URL + '/modules/document-management',
            users: BASE_URL + '/modules/user-management',
            reports: BASE_URL + '/modules/reports-analytics',
            search: BASE_URL + '/modules/search',
            audit: BASE_URL + '/modules/audit',
            help: BASE_URL + '/modules/help'
        },
        
        // API endpoints
        api: {
            auth: BASE_URL + '/modules/authentication/api',
            dashboard: BASE_URL + '/modules/dashboard/api',
            documents: BASE_URL + '/modules/document-management/api',
            users: BASE_URL + '/modules/user-management/api',
            reports: BASE_URL + '/modules/reports-analytics/api',
            search: BASE_URL + '/modules/search/api',
            audit: BASE_URL + '/modules/audit/api'
        },
        
        // Assets
        assets: {
            base: BASE_URL + '/public/assets',
            css: BASE_URL + '/public/assets/css',
            js: BASE_URL + '/public/assets/js',
            images: BASE_URL + '/public/assets/images'
        },
        
        // Common endpoints
        endpoints: {
            login: BASE_URL + '/modules/authentication/controllers/LoginController.php',
            logout: BASE_URL + '/modules/authentication/controllers/LogoutController.php',
            register: BASE_URL + '/modules/authentication/controllers/RegisterController.php'
        }
    };
    
    /**
     * Helper function to build URLs
     * @param {string} path - Relative path from base URL
     * @returns {string} Full URL
     */
    window.App.url = function(path) {
        path = path.replace(/^\/+/, ''); // Remove leading slashes
        return BASE_URL + (path ? '/' + path : '');
    };
    
    /**
     * Helper function to build asset URLs
     * @param {string} path - Relative path from assets folder
     * @returns {string} Full asset URL
     */
    window.App.asset = function(path) {
        path = path.replace(/^\/+/, ''); // Remove leading slashes
        return window.App.config.assets.base + '/' + path;
    };
    
    /**
     * Helper function to build API URLs
     * @param {string} module - Module name (e.g., 'documents', 'users')
     * @param {string} endpoint - API endpoint path
     * @returns {string} Full API URL
     */
    window.App.apiUrl = function(module, endpoint) {
        endpoint = endpoint.replace(/^\/+/, ''); // Remove leading slashes
        const baseApi = window.App.config.api[module] || (BASE_URL + '/modules/' + module + '/api');
        return baseApi + '/' + endpoint;
    };
    
    /**
     * Redirect helper
     * @param {string} url - URL to redirect to
     */
    window.App.redirect = function(url) {
        window.location.href = url;
    };
    
    /**
     * Redirect to login page
     */
    window.App.redirectToLogin = function() {
        window.location.href = window.App.config.urls.auth + '/views/login.php';
    };
    
    /**
     * Redirect to dashboard
     */
    window.App.redirectToDashboard = function() {
        window.location.href = window.App.config.urls.dashboard + '/views/index.php';
    };
    
    // Log configuration in development mode
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        console.log('App Configuration Loaded:', window.App.config);
    }
})();

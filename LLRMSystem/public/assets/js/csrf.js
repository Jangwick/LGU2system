/**
 * CSRF Protection — Global Fetch Interceptor
 *
 * Wraps window.fetch so that every state-changing request (POST, PUT,
 * PATCH, DELETE) automatically carries the X-CSRF-Token header.
 * The token value is read from the <meta name="csrf-token"> tag that
 * header.php injects into every authenticated page.
 *
 * No existing JavaScript files need to be modified — this script is
 * loaded early in <head> and transparently protects all subsequent
 * fetch() calls, whether they send JSON bodies or FormData payloads.
 */
(function () {
    'use strict';

    /** Read the token from the page's meta tag. */
    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : null;
    }

    var SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS', 'TRACE'];

    var originalFetch = window.fetch;

    window.fetch = function (input, init) {
        init = init || {};

        var method = (init.method || (typeof input === 'object' && input.method) || 'GET').toUpperCase();

        if (SAFE_METHODS.indexOf(method) === -1) {
            var token = getCsrfToken();
            if (token) {
                // Support both plain objects and Headers instances
                if (!init.headers) {
                    init.headers = {};
                }

                if (typeof init.headers.set === 'function') {
                    // Headers instance
                    init.headers.set('X-CSRF-Token', token);
                } else {
                    // Plain object
                    init.headers['X-CSRF-Token'] = token;
                }
            }
        }

        return originalFetch.call(this, input, init);
    };
}());

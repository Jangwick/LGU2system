/**
 * ThemeManager
 * Shared dark/light theme logic for landing, guest, and authenticated pages.
 */
(function () {
    'use strict';

    if (window.__themeInitialized) return;
    window.__themeInitialized = true;

    const THEME_KEY = 'theme';
    const DARK_CLASS = 'dark';
    const DEFAULT_THEME = 'light';

    const html = document.documentElement;

    function getStoredTheme() {
        try {
            return localStorage.getItem(THEME_KEY);
        } catch (e) {
            return null;
        }
    }

    function setStoredTheme(theme) {
        try {
            localStorage.setItem(THEME_KEY, theme);
        } catch (e) {
            // ignore storage errors (e.g. private mode)
        }
    }

    function getPreferredTheme() {
        const stored = getStoredTheme();
        if (stored === 'dark' || stored === 'light') {
            return stored;
        }
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : DEFAULT_THEME;
    }

    function applyThemeClass(theme) {
        const isDark = theme === 'dark';
        if (isDark) {
            html.classList.add(DARK_CLASS);
            html.style.colorScheme = 'dark';
        } else {
            html.classList.remove(DARK_CLASS);
            html.style.colorScheme = 'light';
        }
        return isDark;
    }

    function updateThemeMeta(isDark) {
        const meta = document.querySelector('meta[name="theme-color"]');
        if (meta) {
            meta.content = isDark ? '#0a0a0a' : '#dc2626';
        }
    }

    function updateIcons(isDark) {
        document.querySelectorAll('.dark-mode-icon').forEach(function (el) {
            el.classList.toggle('hidden', isDark);
        });
        document.querySelectorAll('.light-mode-icon').forEach(function (el) {
            el.classList.toggle('hidden', !isDark);
        });
    }

    function applyTheme(theme) {
        const isDark = applyThemeClass(theme);
        updateThemeMeta(isDark);
        updateIcons(isDark);
    }

    function initTheme() {
        const theme = getPreferredTheme();
        applyThemeClass(theme);
        updateThemeMeta(theme === 'dark');

        // Update icons once the DOM is ready.
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                applyTheme(getPreferredTheme());
            });
        } else {
            applyTheme(theme);
        }
    }

    function toggleTheme() {
        const isDark = html.classList.contains(DARK_CLASS);
        const newTheme = isDark ? 'light' : 'dark';
        setStoredTheme(newTheme);
        applyTheme(newTheme);
    }

    window.ThemeManager = {
        initTheme: initTheme,
        toggleTheme: toggleTheme,
        applyTheme: applyTheme,
        updateIcons: updateIcons,
        getPreferredTheme: getPreferredTheme
    };

    initTheme();
})();

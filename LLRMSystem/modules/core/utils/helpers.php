<?php
/**
 * Global View Helper Functions
 *
 * This file provides utility functions used across all view/template files.
 * Always include this file before any output is produced.
 */

if (!function_exists('e')) {
    /**
     * HTML-encode a value for safe output in an HTML context.
     *
     * Wraps htmlspecialchars() with sensible defaults:
     *   - ENT_QUOTES  — escapes both single and double quotes
     *   - ENT_SUBSTITUTE — replaces invalid code-unit sequences instead of returning ""
     *   - UTF-8 encoding
     *
     * Usage:
     *   <?= e($variable) ?>
     *   <?php echo e($variable) ?>
     *
     * @param  mixed  $value   The value to encode. Non-strings are cast to string.
     * @return string          HTML-safe string.
     */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

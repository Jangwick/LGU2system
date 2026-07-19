<?php
/**
 * Temporary opcache reset for deployment
 */
if (function_exists('opcache_reset')) {
    $ok = opcache_reset();
    echo json_encode(['opcache_reset' => $ok ? 'cleared' : 'failed']);
} else {
    echo json_encode(['opcache_reset' => 'not_installed']);
}

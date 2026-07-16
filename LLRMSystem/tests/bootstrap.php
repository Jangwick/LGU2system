<?php
/**
 * Test Bootstrap
 * Loads config and autoloading for test environment
 */

// Suppress notice/warning output while loading config in test mode
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
@ini_set('display_errors', '1');

// Define base path only if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Prevent config from defining session settings multiple times
if (!defined('APP_CONFIG_LOADED')) {
    define('APP_CONFIG_LOADED', true);
}

// Load config (it will define remaining constants)
require_once BASE_PATH . '/modules/core/config/config.php';

// Restore normal error reporting for tests
error_reporting(E_ALL);
@ini_set('display_errors', '1');

// Load Sanitizer and Request utilities
require_once BASE_PATH . '/modules/core/utils/Sanitizer.php';
require_once BASE_PATH . '/modules/core/utils/Request.php';

// Load middleware
require_once BASE_PATH . '/modules/core/middleware/SessionTimeoutMiddleware.php';

// Load services for integration tests
require_once BASE_PATH . '/modules/document-management/services/FileStorageService.php';
require_once BASE_PATH . '/modules/document-management/services/OcrService.php';
require_once BASE_PATH . '/modules/document-management/services/SummarizationService.php';
require_once BASE_PATH . '/modules/search/services/SearchService.php';
require_once BASE_PATH . '/modules/search/services/EmbeddingService.php';
require_once BASE_PATH . '/modules/search/controllers/SearchController.php';

// Suppress session output during tests
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

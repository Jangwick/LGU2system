<?php
// Test helper for session timeout modal / confirmation
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'administrator';
$_SESSION['full_name'] = 'Test Admin';
$_SESSION['last_activity'] = time();
$_SESSION['email'] = 'admin@lgu.gov.ph';

// Use a short JS timeout so the modal appears quickly during automated testing
$sessionTimeout = 5;
$pageTitle = 'Session Timeout Test';

require_once __DIR__ . '/../../../modules/core/layouts/header.php';
require_once __DIR__ . '/../../../modules/core/layouts/sidebar.php';
require_once __DIR__ . '/../../../modules/core/layouts/navbar.php';
?>
<div class="flex-1 flex flex-col">
    <main class="flex-1 bg-gray-100 dark:bg-gray-900 p-6">
        <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-md p-8">
            <h1 class="text-2xl font-black text-gray-800 dark:text-white mb-2">Session Timeout Test Page</h1>
            <p class="text-gray-600 dark:text-gray-400">The modal should appear after 5 seconds of inactivity.</p>
        </div>
    </main>
</div>
<?php
require_once __DIR__ . '/../../../modules/core/layouts/footer.php';

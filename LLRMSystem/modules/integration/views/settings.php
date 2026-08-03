<?php
/**
 * Integration Webhook Settings (legacy path)
 *
 * Redirects to the full UI in the user-management module.
 */

session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: /modules/user-management/views/login.php');
    exit;
}

header('Location: /modules/user-management/views/integration-settings.php');
exit;


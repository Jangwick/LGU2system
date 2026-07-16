<?php
session_start();
$currentModule = 'hearings';
$pageTitle = 'Integrated Public Hearings';
$currentPage = 'hearings';

require_once __DIR__ . '/../controllers/IntegrationController.php';
require_once __DIR__ . '/../../core/layouts/header.php';
require_once __DIR__ . '/../../core/layouts/sidebar.php';

include __DIR__ . '/shared_layout.php';
?>

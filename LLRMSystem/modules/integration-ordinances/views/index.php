<?php
session_start();
$currentModule = 'ordinances';
$pageTitle = 'Integrated Ordinances';
$currentPage = 'ordinances';

require_once __DIR__ . '/../../integration/controllers/IntegrationController.php';
require_once __DIR__ . '/../../core/layouts/header.php';
require_once __DIR__ . '/../../core/layouts/sidebar.php';

include __DIR__ . '/../../integration/views/shared_layout.php';
?>

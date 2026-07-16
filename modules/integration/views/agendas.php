<?php
session_start();
$currentModule = 'agendas';
$pageTitle = 'Integrated Agendas';
$currentPage = 'agendas';

require_once __DIR__ . '/../controllers/IntegrationController.php';
require_once __DIR__ . '/../../core/layouts/header.php';
require_once __DIR__ . '/../../core/layouts/sidebar.php';

include __DIR__ . '/shared_layout.php';
?>

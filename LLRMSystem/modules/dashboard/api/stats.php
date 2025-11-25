<?php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../controllers/DashboardController.php';

try {
    $controller = new DashboardController();
    
    // Get requested data
    $action = $_GET['action'] ?? 'all';
    
    switch ($action) {
        case 'statistics':
            $data = $controller->getStatistics();
            break;
            
        case 'upload_trend':
            $data = $controller->getUploadTrend();
            break;
            
        case 'document_types':
            $data = $controller->getDocumentTypesDistribution();
            break;
            
        case 'recent_documents':
            $limit = $_GET['limit'] ?? 5;
            $data = $controller->getRecentDocuments($limit);
            break;
            
        case 'system_status':
            $data = $controller->getSystemStatus();
            break;
            
        case 'all':
        default:
            $data = [
                'statistics' => $controller->getStatistics(),
                'upload_trend' => $controller->getUploadTrend(),
                'document_types' => $controller->getDocumentTypesDistribution(),
                'recent_documents' => $controller->getRecentDocuments(5),
                'system_status' => $controller->getSystemStatus()
            ];
            break;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch dashboard data',
        'message' => $e->getMessage()
    ]);
}

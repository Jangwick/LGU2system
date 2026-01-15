<?php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/config/database.php';

try {
    // Get refresh parameters
    $startDate = $_GET['start_date'] ?? null;
    $endDate = $_GET['end_date'] ?? null;
    
    $db = getDatabase();
    
    // Fetch fresh statistics
    $stats = [];
    
    // Total documents
    $stmt = $db->query("SELECT COUNT(*) FROM legislative_documents WHERE deleted_at IS NULL");
    $stats['total_documents'] = $stmt->fetchColumn();
    
    // Approved documents
    $stmt = $db->query("SELECT COUNT(*) FROM legislative_documents WHERE status = 'approved' AND deleted_at IS NULL");
    $stats['approved_documents'] = $stmt->fetchColumn();
    
    // Active users
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
    $stats['active_users'] = $stmt->fetchColumn();
    
    // Total storage
    $stmt = $db->query("SELECT SUM(file_size) FROM legislative_documents WHERE deleted_at IS NULL");
    $stats['total_storage'] = $stmt->fetchColumn() ?? 0;
    
    // Recent activities (last 24h)
    $stmt = $db->query("SELECT COUNT(*) FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stats['activities_24h'] = $stmt->fetchColumn();
    
    // Pending documents
    $stmt = $db->query("SELECT COUNT(*) FROM legislative_documents WHERE status = 'pending' AND deleted_at IS NULL");
    $stats['pending_documents'] = $stmt->fetchColumn();
    
    // Get chart data
    $charts = [];
    
    // Documents by type
    $stmt = $db->query("
        SELECT document_type, COUNT(*) as count 
        FROM legislative_documents 
        WHERE deleted_at IS NULL
        GROUP BY document_type
    ");
    $docsByType = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $charts['documentsByType'] = [
        'labels' => array_column($docsByType, 'document_type'),
        'data' => array_column($docsByType, 'count')
    ];
    
    // Documents by status
    $stmt = $db->query("
        SELECT status, COUNT(*) as count 
        FROM legislative_documents 
        WHERE deleted_at IS NULL
        GROUP BY status
    ");
    $docsByStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $charts['documentsByStatus'] = [
        'labels' => array_column($docsByStatus, 'status'),
        'data' => array_column($docsByStatus, 'count')
    ];
    
    // Monthly timeline
    $stmt = $db->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as count
        FROM legislative_documents
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        AND deleted_at IS NULL
        GROUP BY month
        ORDER BY month ASC
    ");
    $timeline = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $charts['timeline'] = [
        'labels' => array_column($timeline, 'month'),
        'data' => array_column($timeline, 'count')
    ];
    
    // Get recent activities
    $stmt = $db->prepare("
        SELECT 
            al.*,
            u.full_name,
            u.username
        FROM activity_logs al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get top uploaders
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.full_name,
            u.department,
            COUNT(ld.id) as document_count
        FROM users u
        INNER JOIN legislative_documents ld ON u.id = ld.uploaded_by
        WHERE ld.deleted_at IS NULL
        GROUP BY u.id
        ORDER BY document_count DESC
        LIMIT 5
    ");
    $stmt->execute();
    $topUploaders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'charts' => $charts,
        'tables' => [
            'recentActivities' => $recentActivities,
            'topUploaders' => $topUploaders
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

<?php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../controllers/ReportController.php';

try {
    $controller = new ReportController();
    
    // Get request parameters
    $reportType = Sanitizer::enum($_GET['type'] ?? '', ['user_activity', 'document_access', 'top_uploaders', 'storage_usage', 'monthly_growth', 'department_summary', 'document_status', 'document_type'], '');
    $startDate = Sanitizer::date($_GET['start_date'] ?? '') ?: null;
    $endDate = Sanitizer::date($_GET['end_date'] ?? '') ?: null;
    $format = Sanitizer::enum($_GET['format'] ?? 'json', ['json', 'csv', 'excel'], 'json');
    
    if (empty($reportType)) {
        throw new Exception('Report type is required');
    }
    
    // Generate report based on type
    $reportData = [];
    $reportTitle = '';
    
    switch ($reportType) {
        case 'user_activity':
            $reportData = $controller->getUserActivityReport($startDate, $endDate);
            $reportTitle = 'User Activity Report';
            break;
            
        case 'document_access':
            $reportData = $controller->getDocumentAccessReport($startDate, $endDate);
            $reportTitle = 'Document Access Report';
            break;
            
        case 'top_uploaders':
            $reportData = $controller->getTopUploaders(50);
            $reportTitle = 'Top Uploaders Report';
            break;
            
        case 'storage_usage':
            $reportData = $controller->getStorageByType();
            $reportTitle = 'Storage Usage by Type';
            break;
            
        case 'monthly_growth':
            $reportData = $controller->getMonthlyGrowth();
            $reportTitle = 'Monthly Growth Statistics';
            break;
            
        case 'department_summary':
            $reportData = $controller->getDocumentsByDepartment();
            $reportTitle = 'Documents by Department';
            break;
            
        case 'document_status':
            $reportData = $controller->getDocumentsByStatus();
            $reportTitle = 'Documents by Status';
            break;
            
        case 'document_type':
            $reportData = $controller->getDocumentsByType();
            $reportTitle = 'Documents by Type';
            break;
            
        default:
            throw new Exception('Invalid report type');
    }
    
    // Return based on format
    if ($format === 'csv') {
        $controller->exportToCSV($reportType, $reportData);
        exit;
    } elseif ($format === 'excel') {
        exportToExcel($reportType, $reportData, $reportTitle);
        exit;
    } else {
        // JSON response
        echo json_encode([
            'success' => true,
            'report' => [
                'title' => $reportTitle,
                'type' => $reportType,
                'generated_at' => date('Y-m-d H:i:s'),
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate
                ],
                'headers' => !empty($reportData) ? array_keys($reportData[0]) : [],
                'data' => $reportData,
                'record_count' => count($reportData)
            ]
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Export to Excel format (simple HTML table format)
 */
function exportToExcel($reportType, $data, $title) {
    $filename = $reportType . '_report_' . date('Y-m-d_His') . '.xls';
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo "<html>";
    echo "<head><meta charset='UTF-8'></head>";
    echo "<body>";
    echo "<h2>{$title}</h2>";
    echo "<p>Generated: " . date('F d, Y H:i:s') . "</p>";
    echo "<table border='1'>";
    
    if (!empty($data)) {
        // Headers
        echo "<thead><tr>";
        foreach (array_keys($data[0]) as $header) {
            echo "<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "</tr></thead>";
        
        // Data
        echo "<tbody>";
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }
        echo "</tbody>";
    }
    
    echo "</table>";
    echo "</body>";
    echo "</html>";
    exit;
}

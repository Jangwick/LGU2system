<?php
session_start();
require_once __DIR__ . '/../controllers/ReportController.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/modules/authentication/views/login.php');
    exit;
}

$userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
if (!in_array($userRole, ['officer', 'administrator', 'admin'])) {
    die('Access denied. Insufficient permissions.');
}

$controller = new ReportController();

// Initialize logger
$db = getDatabase();
$logger = new Logger($db);

// Get parameters
$reportType = $_GET['report_type'] ?? '';
$format = $_GET['format'] ?? 'csv';
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

// Get report data
switch ($reportType) {
    case 'user_activity':
        $data = $controller->getUserActivityReport($startDate, $endDate);
        $reportTitle = 'User Activity Report';
        break;
    case 'document_access':
        $data = $controller->getDocumentAccessReport($startDate, $endDate);
        $reportTitle = 'Document Access Report';
        break;
    case 'top_uploaders':
        $data = $controller->getTopUploaders(50);
        $reportTitle = 'Top Uploaders Report';
        break;
    default:
        die('Invalid report type');
}

// Check if data is empty
if (empty($data)) {
    die('No data available for the selected report and date range.');
}

// Log report export activity
$logger->logActivity(Logger::ACTION_REPORT_EXPORT, 'reports', null,
    "Exported $reportTitle in $format format", [
        'report_type' => $reportType,
        'format' => $format,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'record_count' => count($data)
    ]);

// Export based on format
switch ($format) {
    case 'pdf':
        exportToPDF($data, $reportTitle, $reportType, $startDate, $endDate);
        break;
    case 'excel':
        exportToExcel($data, $reportTitle, $reportType);
        break;
    case 'word':
        exportToWord($data, $reportTitle, $reportType, $startDate, $endDate);
        break;
    case 'csv':
    default:
        exportToCSV($data, $reportTitle, $reportType);
        break;
}

/**
 * Export to CSV
 */
function exportToCSV($data, $reportTitle, $reportType) {
    $filename = str_replace(' ', '_', strtolower($reportType)) . '_' . date('Y-m-d_His') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write title
    fputcsv($output, [$reportTitle]);
    fputcsv($output, ['Generated: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    
    // Write headers
    fputcsv($output, array_keys($data[0]));
    
    // Write data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

/**
 * Export to Excel (using simple HTML table method)
 */
function exportToExcel($data, $reportTitle, $reportType) {
    $filename = str_replace(' ', '_', strtolower($reportType)) . '_' . date('Y-m-d_His') . '.xls';
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
    echo '<style>';
    echo 'table { border-collapse: collapse; width: 100%; }';
    echo 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
    echo 'th { background-color: #c41e3a; color: white; font-weight: bold; }';
    echo '.title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }';
    echo '.info { font-size: 12px; color: #666; margin-bottom: 20px; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    
    echo '<div class="title">' . htmlspecialchars($reportTitle) . '</div>';
    echo '<div class="info">Generated: ' . date('Y-m-d H:i:s') . '</div>';
    echo '<div class="info">City Government of Valenzuela - LRMS</div>';
    
    echo '<table>';
    echo '<thead><tr>';
    foreach (array_keys($data[0]) as $header) {
        echo '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $header))) . '</th>';
    }
    echo '</tr></thead>';
    
    echo '<tbody>';
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . htmlspecialchars($cell ?? '') . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    
    echo '</body></html>';
    exit;
}

/**
 * Export to Word
 */
function exportToWord($data, $reportTitle, $reportType, $startDate, $endDate) {
    $filename = str_replace(' ', '_', strtolower($reportType)) . '_' . date('Y-m-d_His') . '.doc';
    
    header('Content-Type: application/vnd.ms-word');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<html xmlns:w="urn:schemas-microsoft-com:office:word">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
    echo '<style>';
    echo 'body { font-family: Arial, sans-serif; }';
    echo 'table { border-collapse: collapse; width: 100%; margin-top: 20px; }';
    echo 'th, td { border: 1px solid #000; padding: 8px; text-align: left; }';
    echo 'th { background-color: #c41e3a; color: white; font-weight: bold; }';
    echo '.header { text-align: center; margin-bottom: 30px; }';
    echo '.title { font-size: 24px; font-weight: bold; color: #c41e3a; }';
    echo '.subtitle { font-size: 16px; margin-top: 10px; }';
    echo '.info { font-size: 12px; color: #666; margin-top: 5px; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    
    echo '<div class="header">';
    echo '<div class="title">City Government of Valenzuela</div>';
    echo '<div class="subtitle">Legislative Records Management System</div>';
    echo '<div style="margin-top: 20px; font-size: 18px; font-weight: bold;">' . htmlspecialchars($reportTitle) . '</div>';
    echo '<div class="info">Generated: ' . date('F d, Y h:i A') . '</div>';
    if ($startDate || $endDate) {
        echo '<div class="info">Period: ';
        echo $startDate ? date('F d, Y', strtotime($startDate)) : 'Beginning';
        echo ' to ';
        echo $endDate ? date('F d, Y', strtotime($endDate)) : 'Present';
        echo '</div>';
    }
    echo '</div>';
    
    echo '<table>';
    echo '<thead><tr>';
    foreach (array_keys($data[0]) as $header) {
        echo '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $header))) . '</th>';
    }
    echo '</tr></thead>';
    
    echo '<tbody>';
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . htmlspecialchars($cell ?? '') . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    
    echo '<div style="margin-top: 40px; font-size: 10px; color: #999;">';
    echo '<p>This is a computer-generated report from the Legislative Records Management System.</p>';
    echo '<p>City Government of Valenzuela, Metropolitan Manila</p>';
    echo '</div>';
    
    echo '</body></html>';
    exit;
}

/**
 * Export to PDF (using simple PDF generation)
 */
function exportToPDF($data, $reportTitle, $reportType, $startDate, $endDate) {
    $filename = str_replace(' ', '_', strtolower($reportType)) . '_' . date('Y-m-d_His') . '.pdf';
    
    // Create a simple PDF using basic PDF structure
    // This creates a valid PDF file that can be downloaded
    
    ob_start();
    
    // PDF Header
    $pdf = "%PDF-1.4\n";
    $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    
    // Build content string
    $content = "City Government of Valenzuela\n";
    $content .= "Legislative Records Management System\n\n";
    $content .= $reportTitle . "\n";
    $content .= "Generated: " . date('F d, Y h:i A') . "\n";
    
    if ($startDate || $endDate) {
        $content .= "Period: ";
        $content .= $startDate ? date('F d, Y', strtotime($startDate)) : 'Beginning';
        $content .= ' to ';
        $content .= $endDate ? date('F d, Y', strtotime($endDate)) : 'Present';
        $content .= "\n";
    }
    
    $content .= "\n";
    
    // Add headers
    $headers = array_keys($data[0]);
    $content .= implode(' | ', array_map(function($h) {
        return ucwords(str_replace('_', ' ', $h));
    }, $headers)) . "\n";
    $content .= str_repeat('-', 120) . "\n";
    
    // Add data rows
    foreach ($data as $row) {
        $rowData = array_map(function($cell) {
            return $cell ?? 'N/A';
        }, array_values($row));
        $content .= implode(' | ', $rowData) . "\n";
    }
    
    $content .= "\n\nThis is a computer-generated report from the Legislative Records Management System.\n";
    $content .= "City Government of Valenzuela, Metropolitan Manila\n";
    
    // For a proper solution, we'll use HTML to PDF conversion via wkhtmltopdf or similar
    // Since we don't have external libraries, we'll create a better HTML-to-PDF workaround
    
    // Alternative: Use TCPDF-like approach with basic PDF generation
    // For now, let's use a PHP-based PDF generation that creates actual PDF bytes
    
    // Include a minimal PDF library or create one
    $pdfContent = generateSimplePDF($reportTitle, $data, $startDate, $endDate);
    
    ob_end_clean();
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdfContent));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    echo $pdfContent;
    exit;
}

/**
 * Generate a simple PDF using basic PDF structure
 */
function generateSimplePDF($reportTitle, $data, $startDate, $endDate) {
    require_once __DIR__ . '/../../core/utils/SimplePDF.php';
    
    $pdf = new SimplePDF();
    $pdf->SetTitle($reportTitle);
    $pdf->SetAuthor('City Government of Valenzuela - LRMS');
    
    // Add header
    $pdf->AddText('City Government of Valenzuela', 18, 'center', 'bold');
    $pdf->AddText('Legislative Records Management System', 12, 'center');
    $pdf->AddText('', 10); // spacing
    $pdf->AddText($reportTitle, 14, 'center', 'bold');
    $pdf->AddText('Generated: ' . date('F d, Y h:i A'), 9, 'center');
    
    if ($startDate || $endDate) {
        $period = 'Period: ';
        $period .= $startDate ? date('F d, Y', strtotime($startDate)) : 'Beginning';
        $period .= ' to ';
        $period .= $endDate ? date('F d, Y', strtotime($endDate)) : 'Present';
        $pdf->AddText($period, 9, 'center');
    }
    
    $pdf->AddText('', 10); // spacing
    $pdf->AddText(str_repeat('=', 90), 8);
    $pdf->AddText('', 8); // spacing
    
    // Get column headers
    $headers = array_keys($data[0]);
    $numCols = count($headers);
    
    // Calculate column width (fixed width approach)
    $pageWidth = 80; // characters
    $colWidth = floor($pageWidth / $numCols);
    
    // Format and add header row
    $headerLine = '';
    foreach ($headers as $header) {
        $headerText = strtoupper(str_replace('_', ' ', $header));
        $headerText = substr($headerText, 0, $colWidth - 1);
        $headerLine .= str_pad($headerText, $colWidth);
    }
    $pdf->AddText($headerLine, 8, 'left', 'bold');
    $pdf->AddText(str_repeat('-', 90), 7);
    
    // Add data rows (limit to prevent overflow)
    $rowCount = 0;
    $maxRows = 30; // Limit rows per page
    
    foreach ($data as $row) {
        if ($rowCount >= $maxRows) {
            $pdf->AddText('... (more rows available in CSV/Excel export)', 7, 'center');
            break;
        }
        
        $rowLine = '';
        foreach ($row as $cell) {
            $cellText = ($cell !== null && $cell !== '') ? (string)$cell : 'N/A';
            $cellText = substr($cellText, 0, $colWidth - 1);
            $rowLine .= str_pad($cellText, $colWidth);
        }
        $pdf->AddText($rowLine, 7);
        $rowCount++;
    }
    
    $pdf->AddText('', 8); // spacing
    $pdf->AddText(str_repeat('=', 90), 8);
    $pdf->AddText('', 8); // spacing
    
    if ($rowCount >= $maxRows) {
        $pdf->AddText('Note: This PDF shows first ' . $maxRows . ' rows. For complete data, use Excel or CSV export.', 7, 'center');
        $pdf->AddText('', 8);
    }
    
    $pdf->AddText('This is a computer-generated report from the Legislative Records Management System.', 7, 'center');
    $pdf->AddText('City Government of Valenzuela, Metropolitan Manila', 7, 'center');
    
    return $pdf->Output();
}

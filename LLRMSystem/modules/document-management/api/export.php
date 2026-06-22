<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../../core/config/config.php';
    redirectToLogin();
}

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

$db = getDatabase();
$documentModel = new Document($db);
$logger = new Logger($db);

// Get export type (using 'export_type' to avoid conflict with document type filter) — sanitized
$exportType = Sanitizer::enum($_GET['export_type'] ?? 'list', ['list', 'files'], 'list');
$format = Sanitizer::enum($_GET['format'] ?? 'csv', ['csv', 'pdf'], 'csv');

// Validate format - only CSV and PDF allowed for list exports
if ($exportType === 'list' && !in_array(strtolower($format), ['csv', 'pdf'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid export format. Only CSV and PDF exports are supported.']);
    exit;
}

// Get document IDs for bulk export (comma-separated) — sanitized
$documentIds = isset($_GET['ids']) ? explode(',', Sanitizer::string($_GET['ids'])) : [];

// Get filters — sanitized
$filters = [
    'search' => Sanitizer::plainText($_GET['search'] ?? ''),
    'type' => Sanitizer::plainText($_GET['doc_type'] ?? $_GET['type'] ?? ''),
    'status' => Sanitizer::enum($_GET['status'] ?? '', ['draft', 'pending', 'approved', 'rejected'], ''),
    'date_from' => Sanitizer::date($_GET['date_from'] ?? ''),
    'date_to' => Sanitizer::date($_GET['date_to'] ?? ''),
    'user_role' => strtolower(trim($_SESSION['user_role'] ?? 'viewer')),
    'limit' => 10000,
    'offset' => 0
];

// Get documents
if (!empty($documentIds)) {
    // Export specific selected documents
    $documents = [];
    foreach ($documentIds as $id) {
        $doc = $documentModel->getById(Sanitizer::int(trim($id), 0));
        if ($doc) {
            $documents[] = $doc;
        }
    }
} else {
    // Export all filtered documents
    $documents = $documentModel->getAll($filters);
}

// Log export activity
$exportDetails = [
    'export_type' => $exportType,
    'format' => $format,
    'document_count' => count($documents),
    'filters' => $filters
];

// Get document titles for specific exports
if (!empty($documentIds)) {
    $exportedTitles = array_map(function($doc) {
        return $doc['title'] ?? $doc['reference_number'] ?? 'Unknown';
    }, $documents);
    $exportDetails['exported_documents'] = implode(', ', array_slice($exportedTitles, 0, 5));
    if (count($exportedTitles) > 5) {
        $exportDetails['exported_documents'] .= '... and ' . (count($exportedTitles) - 5) . ' more';
    }
}

$logger->logActivity(
    Logger::ACTION_DOCUMENT_EXPORT,
    'documents',
    null,
    "Exported " . count($documents) . " document(s) as " . strtoupper($format) . ($exportType === 'files' ? ' (ZIP files)' : ' (list)'),
    $exportDetails
);

// Check export type
if ($exportType === 'files') {
    // Export actual document files as ZIP
    exportDocumentFiles($documents);
} else {
    // Export document list
    if ($format === 'csv') {
        exportCSV($documents);
    } else {
        // PDF export requires TCPDF or DOMPDF library
        // For now, fallback to CSV with a note
        header('Content-Type: application/json');
        echo json_encode(['error' => 'PDF export requires TCPDF or DOMPDF library. Please install the library first. Use CSV format instead.']);
    }
}

function exportDocumentFiles($documents) {
    if (empty($documents)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No documents selected for export']);
        exit;
    }
    
    // Check if ZipArchive is available
    if (!class_exists('ZipArchive')) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'ZIP extension not enabled. Please enable php_zip extension in php.ini',
            'solution' => 'Edit php.ini and uncomment: extension=zip'
        ]);
        exit;
    }
    
    // Create temporary ZIP file
    $zipFilename = 'documents_' . date('Y-m-d_His') . '.zip';
    $zipPath = sys_get_temp_dir() . '/' . $zipFilename;
    
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Could not create ZIP file']);
        exit;
    }
    
    // Try multiple possible storage paths
    $possiblePaths = [
        __DIR__ . '/../../../storage/documents/',
        __DIR__ . '/../../storage/documents/',
        dirname(dirname(dirname(__DIR__))) . '/storage/documents/'
    ];
    
    $baseStoragePath = null;
    foreach ($possiblePaths as $path) {
        if (is_dir($path)) {
            $baseStoragePath = $path;
            break;
        }
    }
    
    if (!$baseStoragePath) {
        $zip->close();
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Storage directory not found',
            'debug' => [
                'checked_paths' => $possiblePaths,
                'current_dir' => __DIR__
            ]
        ]);
        exit;
    }
    
    $filesAdded = 0;
    $errors = [];
    
    foreach ($documents as $doc) {
        // Handle both absolute and relative file paths
        if (isset($doc['file_path']) && !empty($doc['file_path'])) {
            // Try different path combinations
            $filePaths = [
                $baseStoragePath . $doc['file_path'],
                $baseStoragePath . ltrim($doc['file_path'], '/'),
                $doc['file_path'] // In case it's already absolute
            ];
            
            $fileFound = false;
            foreach ($filePaths as $filePath) {
                if (file_exists($filePath) && is_file($filePath)) {
                    // Create safe filename
                    $safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $doc['file_name'] ?? 'document.pdf');
                    
                    // Add file to ZIP with reference number prefix for organization
                    $zipFilename = ($doc['reference_number'] ?? 'DOC') . '_' . $safeFilename;
                    
                    if ($zip->addFile($filePath, $zipFilename)) {
                        $filesAdded++;
                        $fileFound = true;
                        break;
                    }
                }
            }
            
            if (!$fileFound) {
                $errors[] = [
                    'title' => $doc['title'] ?? 'Unknown',
                    'reference' => $doc['reference_number'] ?? 'N/A',
                    'expected_path' => $doc['file_path'] ?? 'No path'
                ];
            }
        }
    }
    
    $zip->close();
    
    if ($filesAdded === 0) {
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'No valid document files found',
            'debug' => [
                'total_documents' => count($documents),
                'storage_path' => $baseStoragePath,
                'missing_files' => $errors
            ]
        ]);
        exit;
    }
    
    // Send ZIP file to browser
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zipPath) . '"');
    header('Content-Length: ' . filesize($zipPath));
    header('Pragma: no-cache');
    header('Expires: 0');
    
    readfile($zipPath);
    
    // Delete temporary file
    if (file_exists($zipPath)) {
        unlink($zipPath);
    }
    exit;
}

function exportCSV($documents) {
    // Display CSV data as HTML table instead of downloading
    header('Content-Type: text/html; charset=utf-8');
    
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>Document Export - CSV View</title>';
    echo '<script src="https://cdn.tailwindcss.com"></script>';
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">';
    echo '</head>';
    echo '<body class="bg-gray-50">';
    
    echo '<div class="container mx-auto px-4 py-8">';
    echo '<div class="bg-white rounded-lg shadow-lg p-6">';
    
    // Header with download button
    echo '<div class="flex justify-between items-center mb-6">';
    echo '<div>';
    echo '<h1 class="text-2xl font-bold text-gray-800">Document Export</h1>';
    echo '<p class="text-gray-600 mt-1">Total Documents: ' . count($documents) . '</p>';
    echo '</div>';
    echo '<button type="button" onclick="downloadCSV()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">';
    echo '<i class="bi bi-download"></i> Download CSV';
    echo '</button>';
    echo '</div>';
    
    // Table
    echo '<div class="overflow-x-auto">';
    echo '<table id="exportTable" class="min-w-full divide-y divide-gray-200">';
    echo '<thead class="bg-gray-100">';
    echo '<tr>';
    echo '<th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Reference Number</th>';
    echo '<th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Title</th>';
    echo '<th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Document Type</th>';
    echo '<th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>';
    echo '<th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Document Date</th>';
    echo '<th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">File Name</th>';
    echo '<th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">File Size</th>';
    echo '<th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Uploaded By</th>';
    echo '<th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Created At</th>';
    echo '<th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Updated At</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody class="bg-white divide-y divide-gray-200">';
    
    foreach ($documents as $doc) {
        echo '<tr class="hover:bg-gray-50">';
        echo '<td class="px-4 py-3 text-sm text-gray-900">' . htmlspecialchars($doc['reference_number'] ?? '') . '</td>';
        echo '<td class="px-4 py-3 text-sm text-gray-900">' . htmlspecialchars($doc['title'] ?? '') . '</td>';
        echo '<td class="px-4 py-3 text-sm text-gray-900">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $doc['document_type'] ?? ''))) . '</td>';
        echo '<td class="px-4 py-3 text-sm"><span class="px-2 py-1 rounded-full text-xs font-medium bg-' . getStatusColor($doc['status'] ?? '') . '-100 text-' . getStatusColor($doc['status'] ?? '') . '-800">' . htmlspecialchars(ucfirst($doc['status'] ?? '')) . '</span></td>';
        echo '<td class="px-4 py-3 text-sm text-gray-900">' . htmlspecialchars($doc['document_date'] ?? '') . '</td>';
        echo '<td class="px-4 py-3 text-sm text-gray-900">' . htmlspecialchars($doc['file_name'] ?? '') . '</td>';
        echo '<td class="px-4 py-3 text-sm text-gray-900">' . htmlspecialchars(formatFileSize($doc['file_size'] ?? 0)) . '</td>';
        echo '<td class="px-4 py-3 text-sm text-gray-900">' . htmlspecialchars($doc['uploaded_by_name'] ?? '') . '</td>';
        echo '<td class="px-4 py-3 text-sm text-gray-900">' . htmlspecialchars($doc['created_at'] ?? '') . '</td>';
        echo '<td class="px-4 py-3 text-sm text-gray-900">' . htmlspecialchars($doc['updated_at'] ?? '') . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
    
    // JavaScript for CSV download
    echo '<script>';
    echo 'function downloadCSV() {';
    echo '  const table = document.getElementById("exportTable");';
    echo '  let csv = [];';
    echo '  const rows = table.querySelectorAll("tr");';
    echo '  ';
    echo '  for (let i = 0; i < rows.length; i++) {';
    echo '    const row = [], cols = rows[i].querySelectorAll("td, th");';
    echo '    for (let j = 0; j < cols.length; j++) {';
    echo '      let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/"/g, \'""\');';
    echo '      row.push(\'"\' + data + \'"\');';
    echo '    }';
    echo '    csv.push(row.join(","));';
    echo '  }';
    echo '  ';
    echo '  const csvContent = "\\uFEFF" + csv.join("\\n");';
    echo '  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });';
    echo '  const link = document.createElement("a");';
    echo '  const url = URL.createObjectURL(blob);';
    echo '  link.setAttribute("href", url);';
    echo '  link.setAttribute("download", "documents_export_' . date('Y-m-d_His') . '.csv");';
    echo '  link.style.visibility = "hidden";';
    echo '  document.body.appendChild(link);';
    echo '  link.click();';
    echo '  document.body.removeChild(link);';
    echo '}';
    echo '</script>';
    
    echo '</body>';
    echo '</html>';
    exit;
}

function getStatusColor($status) {
    switch (strtolower($status)) {
        case 'approved': return 'green';
        case 'pending': return 'yellow';
        case 'rejected': return 'red';
        case 'archived': return 'gray';
        case 'draft': return 'blue';
        default: return 'gray';
    }
}

function exportExcel($documents) {
    $filename = 'documents_export_' . date('Y-m-d_His') . '.xls';
    
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    
    // Start HTML table
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
    echo '<style>
        table { border-collapse: collapse; }
        th { background-color: #4472C4; color: white; font-weight: bold; padding: 8px; border: 1px solid #ccc; }
        td { padding: 6px; border: 1px solid #ccc; }
        tr:nth-child(even) { background-color: #f2f2f2; }
    </style>';
    echo '</head>';
    echo '<body>';
    echo '<table border="1">';
    
    // Table Headers
    echo '<thead><tr>';
    echo '<th>Reference Number</th>';
    echo '<th>Title</th>';
    echo '<th>Document Type</th>';
    echo '<th>Status</th>';
    echo '<th>Document Date</th>';
    echo '<th>File Name</th>';
    echo '<th>File Size</th>';
    echo '<th>Uploaded By</th>';
    echo '<th>Created At</th>';
    echo '<th>Updated At</th>';
    echo '</tr></thead>';
    
    // Table Body
    echo '<tbody>';
    foreach ($documents as $doc) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($doc['reference_number'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($doc['title'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $doc['document_type'] ?? ''))) . '</td>';
        echo '<td>' . htmlspecialchars(ucfirst($doc['status'] ?? '')) . '</td>';
        echo '<td>' . htmlspecialchars($doc['document_date'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($doc['file_name'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars(formatFileSize($doc['file_size'] ?? 0)) . '</td>';
        echo '<td>' . htmlspecialchars($doc['uploaded_by_name'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($doc['created_at'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($doc['updated_at'] ?? '') . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</body>';
    echo '</html>';
    
    exit;
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

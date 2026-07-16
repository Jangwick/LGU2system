<?php
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/Logger.php';
require_once __DIR__ . '/../../notifications/models/Notification.php';

class IntegrationController {
    private $db;
    private $logger;
    private $notification;

    public function __construct() {
        $this->db = getDatabase();
        $this->logger = new Logger($this->db);
        $this->notification = new Notification();
    }

    /**
     * Get integrated records by module type
     */
    public function getRecords($type = null) {
        $sql = "SELECT * FROM integrated_records";
        $params = [];
        
        if ($type) {
            $sql .= " WHERE module_type = :type";
            $params[':type'] = $type;
        }
        
        $sql .= " ORDER BY received_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Receive data from external system
     */
    public function receive($data, $fileData = null) {
        $moduleType = $data['module_type'] ?? $data['type'] ?? null;
        $title = $data['title'] ?? null;

        if (empty($moduleType) || empty($title)) {
            return ['success' => false, 'error' => 'Missing required fields (module_type/type, title)'];
        }

        // Include file info in payload if uploaded
        $payload = $data['payload'] ?? [];
        if ($fileData) {
            $payload['file_path'] = $fileData['file_path'];
            $payload['file_name'] = $fileData['file_name'];
            $payload['file_size'] = $fileData['file_size'];
            $payload['file_type'] = $fileData['file_type'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO integrated_records (module_type, external_id, title, summary, data_payload, source_system, status)
            VALUES (:type, :ext_id, :title, :summary, :payload, :source, 'pending')
        ");

        $result = $stmt->execute([
            ':type' => $moduleType,
            ':ext_id' => $data['external_id'] ?? null,
            ':title' => $title,
            ':summary' => $data['summary'] ?? null,
            ':payload' => json_encode($payload),
            ':source' => $data['source_system'] ?? 'External Integration'
        ]);

        if ($result) {
            $id = $this->db->lastInsertId();
            $this->logger->logActivity('INTEGRATION_RECEIVED', 'integrated_records', $id, "Received {$moduleType} from " . ($data['source_system'] ?? 'External Integration'));
            
            // Notify staff about incoming external data
            $this->notification->broadcast([
                'type' => 'integration',
                'title' => 'Incoming External Data',
                'message' => "New " . ($moduleType ?: 'record') . " received: " . $title,
                'source_module' => 'integration',
                'source_id' => $id,
                'data' => ['link' => "modules/integration/views/{$moduleType}.php"]
            ]);

            return ['success' => true, 'id' => $id];
        }

        return ['success' => false, 'error' => 'Failed to save record'];
    }

    /**
     * Import an integrated record into the main LRMS Document system
     */
    public function importToLRMS($id) {
        $stmt = $this->db->prepare("SELECT * FROM integrated_records WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            return ['success' => false, 'error' => 'Record not found'];
        }

        if ($record['status'] === 'synced') {
            return ['success' => false, 'error' => 'Record already synced to LRMS'];
        }

        // Parse extra payload data
        $payload = json_decode($record['data_payload'], true) ?: [];
        $docDate = $payload['document_date'] ?? date('Y-m-d');
        $tags = $payload['tags'] ?? '';
        
        // Get file info from payload (uploaded during receive)
        $filePath = $payload['file_path'] ?? 'external_sync';
        $fileName = $payload['file_name'] ?? 'Synced Data';
        $fileSize = $payload['file_size'] ?? 0;
        $fileType = $payload['file_type'] ?? 'application/json';

        // Map module_type to document_type labels used in LRMS (Matches ENUM in documents table)
        $typeMap = [
            'ordinances' => 'ordinance',
            'sessions' => 'session',
            'agendas' => 'agenda',
            'committees' => 'committee',
            'research' => 'research',
            'resolutions' => 'resolution',
            'voting' => 'voting',
            'hearings' => 'hearing',
            'archives' => 'archive',
            'consultations' => 'consultation'
        ];
        $docType = $typeMap[$record['module_type']] ?? 'ordinance'; // Default to ordinance if unknown to satisfy ENUM

        // Generate a reference number
        $refPrefix = strtoupper(substr($docType, 0, 3));
        $refNum = $refPrefix . '-' . date('Y') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);

        // Insert into legislative_documents
        $stmt = $this->db->prepare("
            INSERT INTO legislative_documents (
                reference_number, title, document_type, document_date,
                status, description, tags, source_module, source_id,
                uploaded_by, created_at, file_path, file_name, file_size, file_type
            ) VALUES (
                :ref, :title, :type, :doc_date,
                'draft', :desc, :tags, 'integration', :source_id,
                :user_id, NOW(), :file_path, :file_name, :file_size, :file_type
            )
        ");

        $result = $stmt->execute([
            ':ref' => $refNum,
            ':title' => $record['title'],
            ':type' => $docType,
            ':doc_date' => $docDate,
            ':desc' => $record['summary'],
            ':tags' => $tags,
            ':source_id' => $id,
            ':user_id' => $_SESSION['user_id'] ?? 1,
            ':file_path' => $filePath,
            ':file_name' => $fileName,
            ':file_size' => $fileSize,
            ':file_type' => $fileType
        ]);

        if ($result) {
            $docId = $this->db->lastInsertId();
            
            // Update the integration record status
            $this->updateStatus($id, 'synced');
            
            $this->logger->logActivity('INTEGRATION_IMPORTED', 'legislative_documents', $docId, "Imported {$record['module_type']} Record #{$id} as Document #{$docId}");
            
            // Notify about successful sync
            $this->notification->broadcast([
                'type' => 'file',
                'title' => 'Record Imported',
                'message' => "External record '{$record['title']}' has been successfully imported as Document #$refNum",
                'source_module' => 'document-management',
                'source_id' => $docId,
                'data' => ['link' => "modules/document-management/views/view.php?id={$docId}"]
            ]);

            return ['success' => true, 'document_id' => $docId];
        }

        return ['success' => false, 'error' => 'Failed to create document'];
    }

    /**
     * Update status of a record
     */
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE integrated_records SET status = :status, processed_at = NOW() WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    /**
     * Receive a document from an external system via API
     * Creates both integrated_records AND legislative_documents entries
     * Runs OCR on the file and generates key points
     */
    public function receiveDocument($data, $fileData) {
        if (empty($data['title']) || empty($data['document_type'])) {
            return ['success' => false, 'error' => 'Missing required fields (title, document_type)'];
        }

        if (!$fileData || !isset($fileData['tmp_name'])) {
            return ['success' => false, 'error' => 'No file provided'];
        }

        // Start transaction
        $this->db->beginTransaction();

        try {
            // 1. Save file to storage
            require_once __DIR__ . '/../../document-management/services/FileStorageService.php';
            $storage = new FileStorageService();

            $ext = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
            $safeFilename = 'INT_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $uploadDir = dirname(dirname(dirname(__DIR__))) . '/storage/documents/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $destPath = $uploadDir . $safeFilename;

            if (!move_uploaded_file($fileData['tmp_name'], $destPath)) {
                // Fallback: copy if move_uploaded_file fails (e.g., for API testing)
                if (!copy($fileData['tmp_name'], $destPath)) {
                    $this->db->rollBack();
                    return ['success' => false, 'error' => 'Failed to save uploaded file.'];
                }
            }

            $storedFilePath = 'storage/documents/' . $safeFilename;
            $absoluteFilePath = dirname(dirname(dirname(__DIR__))) . '/' . $storedFilePath;

            // 2. Run OCR on the file (before encryption, while file is plaintext)
            require_once __DIR__ . '/../../document-management/services/OcrService.php';
            require_once __DIR__ . '/../../document-management/services/SummarizationService.php';

            $ocrService = new OcrService();
            $summarizationService = new SummarizationService();

            $ocrStatus = 'pending';
            $extractedText = null;
            $keyPoints = null;

            $asyncThreshold = defined('OCR_ASYNC_THRESHOLD') ? OCR_ASYNC_THRESHOLD : 5242880;

            if (defined('OCR_ENABLED') && OCR_ENABLED && $ocrService->isOcrCapable($fileData['type'], $fileData['name'])) {
                if ($fileData['size'] < $asyncThreshold) {
                    // Small file: run OCR synchronously
                    try {
                        $ocrResult = $ocrService->extractText($absoluteFilePath, $fileData['type']);
                        $extractedText = $ocrResult['text'];
                        $ocrStatus = $ocrResult['status'];

                        if ($ocrResult['status'] === 'completed' && !empty($ocrResult['text'])) {
                            $keyPoints = $summarizationService->generateKeyPointsString($ocrResult['text'], 7);
                        }
                    } catch (Exception $e) {
                        error_log("OCR failed during integration receive: " . $e->getMessage());
                        $ocrStatus = 'failed';
                    }
                }
                // Large files: ocr_status stays 'pending' for async worker
            } else {
                $ocrStatus = 'skipped';
            }

            // 3. Encrypt the file
            require_once __DIR__ . '/../../document-management/services/EncryptionService.php';
            $encryptionService = new EncryptionService();

            $fileKey = $encryptionService->generateFileKey();
            $encryptionResult = $encryptionService->encryptFile($destPath, $fileKey);

            if (!$encryptionResult['success']) {
                throw new Exception("File encryption failed: " . $encryptionResult['error']);
            }

            $keyEncryptionResult = $encryptionService->encryptFileKey($fileKey);
            if (!$keyEncryptionResult['success']) {
                throw new Exception("File key encryption failed: " . $keyEncryptionResult['error']);
            }
            $encryptedFileKey = $keyEncryptionResult['encrypted_key'];

            // 4. Generate reference number
            $docType = $data['document_type'];
            $refPrefix = strtoupper(substr($docType, 0, 3));
            $refNum = $refPrefix . '-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

            // Ensure uniqueness
            $checkStmt = $this->db->prepare("SELECT id FROM legislative_documents WHERE reference_number = ?");
            $checkStmt->execute([$refNum]);
            while ($checkStmt->fetch()) {
                $refNum = $refPrefix . '-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                $checkStmt->execute([$refNum]);
            }

            // 5. Create integrated_records entry
            $payload = [
                'document_date' => $data['document_date'],
                'tags' => $data['tags'],
                'description' => $data['description'],
                'api_key_id' => $data['api_key_id'] ?? null,
            ];

            $stmt = $this->db->prepare("
                INSERT INTO integrated_records (module_type, external_id, title, summary, data_payload, source_system, status)
                VALUES (:type, :ext_id, :title, :summary, :payload, :source, 'synced')
            ");

            $stmt->execute([
                ':type' => $docType . 's',
                ':ext_id' => $data['external_id'] ?? null,
                ':title' => $data['title'],
                ':summary' => $data['description'],
                ':payload' => json_encode($payload),
                ':source' => $data['source_system'] ?? 'External ERP'
            ]);

            $integrationId = $this->db->lastInsertId();

            // 6. Create legislative_documents entry
            $timestamp = date('Y-m-d H:i:s');
            $stmt = $this->db->prepare("
                INSERT INTO legislative_documents (
                    reference_number, title, document_type, document_date,
                    status, description, tags, source_module, source_id,
                    uploaded_by, created_at, file_path, file_name, file_size, file_type,
                    is_encrypted, encryption_key,
                    extracted_text, ocr_status, ocr_processed_at,
                    key_points, key_points_generated_at
                ) VALUES (
                    :ref, :title, :type, :doc_date,
                    'pending', :desc, :tags, 'integration', :source_id,
                    :user_id, NOW(), :file_path, :file_name, :file_size, :file_type,
                    1, :enc_key,
                    :extracted_text, :ocr_status, :ocr_processed_at,
                    :key_points, :key_points_generated_at
                )
            ");

            $stmt->execute([
                ':ref' => $refNum,
                ':title' => $data['title'],
                ':type' => $docType,
                ':doc_date' => $data['document_date'],
                ':desc' => $data['description'],
                ':tags' => $data['tags'],
                ':source_id' => $integrationId,
                ':user_id' => $_SESSION['user_id'] ?? 1,
                ':file_path' => $storedFilePath,
                ':file_name' => $fileData['name'],
                ':file_size' => $fileData['size'],
                ':file_type' => $fileData['type'],
                ':enc_key' => $encryptedFileKey,
                ':extracted_text' => $extractedText,
                ':ocr_status' => $ocrStatus,
                ':ocr_processed_at' => $ocrStatus === 'completed' ? $timestamp : null,
                ':key_points' => $keyPoints,
                ':key_points_generated_at' => $keyPoints ? $timestamp : null
            ]);

            $documentId = $this->db->lastInsertId();

            // 7. Link integrated_records to legislative_documents via external_id
            $this->db->prepare("UPDATE integrated_records SET external_id = :doc_id WHERE id = :int_id")
                ->execute([':doc_id' => $documentId, ':int_id' => $integrationId]);

            // Commit transaction
            $this->db->commit();

            // Log activity
            $this->logger->logActivity('INTEGRATION_DOCUMENT_RECEIVED', 'legislative_documents', $documentId,
                "Received document '{$data['title']}' from {$data['source_system']} - OCR: {$ocrStatus}");

            // Notify about new integrated document
            $this->notification->broadcast([
                'type' => 'file',
                'title' => 'New Document from Integration',
                'message' => "'{$data['title']}' received from {$data['source_system']} — OCR: {$ocrStatus}",
                'source_module' => 'document-management',
                'source_id' => $documentId,
                'data' => ['link' => "modules/document-management/views/view.php?id={$documentId}"]
            ]);

            return [
                'success' => true,
                'integration_id' => $integrationId,
                'document_id' => $documentId,
                'reference_number' => $refNum,
                'ocr_status' => $ocrStatus,
                'message' => 'Document received and saved successfully.'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();

            // Clean up file if it was saved
            if (isset($destPath) && file_exists($destPath)) {
                @unlink($destPath);
            }

            error_log("receiveDocument error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to process document: ' . $e->getMessage()];
        }
    }
}

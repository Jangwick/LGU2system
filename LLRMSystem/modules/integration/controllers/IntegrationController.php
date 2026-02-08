<?php
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

class IntegrationController {
    private $db;
    private $logger;

    public function __construct() {
        $this->db = getDatabase();
        $this->logger = new Logger($this->db);
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
    public function receive($data) {
        $moduleType = $data['module_type'] ?? $data['type'] ?? null;
        $title = $data['title'] ?? null;

        if (empty($moduleType) || empty($title)) {
            return ['success' => false, 'error' => 'Missing required fields (module_type/type, title)'];
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
            ':payload' => json_encode($data['payload'] ?? []),
            ':source' => $data['source_system'] ?? 'External Integration'
        ]);

        if ($result) {
            $id = $this->db->lastInsertId();
            $this->logger->logActivity('INTEGRATION_RECEIVED', 'integrated_records', $id, "Received {$data['module_type']} from {$data['source_system']}");
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
                :user_id, NOW(), 'external_sync', 'Synced Data', 0, 'application/json'
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
            ':user_id' => $_SESSION['user_id'] ?? 1
        ]);

        if ($result) {
            $docId = $this->db->lastInsertId();
            
            // Update the integration record status
            $this->updateStatus($id, 'synced');
            
            $this->logger->logActivity('INTEGRATION_IMPORTED', 'legislative_documents', $docId, "Imported {$record['module_type']} Record #{$id} as Document #{$docId}");
            
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
}

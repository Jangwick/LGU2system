<?php
/**
 * Document Tracking Service
 *
 * Manages the provenance/progression timeline for a document.
 * Merges external tracking entries with internal status history and activity logs.
 */
require_once __DIR__ . '/../../core/config/database.php';

class DocumentTrackingService
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: getDatabase();
    }

    /**
     * Get all tracking events for a document, merged and sorted by occurred_at.
     */
    public function getTrackingEvents($documentId)
    {
        $events = [];

        // External tracking events
        $stmt = $this->db->prepare("
            SELECT
                dt.id,
                dt.source_system,
                dt.external_reference,
                dt.event_action,
                dt.description,
                dt.occurred_at,
                dt.created_at,
                u.full_name AS recorded_by_name,
                'external' AS source
            FROM document_tracking dt
            LEFT JOIN users u ON dt.recorded_by = u.id
            WHERE dt.document_id = :document_id
            ORDER BY dt.occurred_at ASC
        ");
        $stmt->execute([':document_id' => $documentId]);
        $events = array_merge($events, $stmt->fetchAll(PDO::FETCH_ASSOC));

        // Internal status changes
        $stmt = $this->db->prepare("
            SELECT
                dsh.id,
                'LLRM System' AS source_system,
                NULL AS external_reference,
                CONCAT('Status: ', dsh.new_status) AS event_action,
                dsh.notes AS description,
                dsh.changed_at AS occurred_at,
                dsh.changed_at AS created_at,
                u.full_name AS recorded_by_name,
                'internal' AS source
            FROM document_status_history dsh
            LEFT JOIN users u ON dsh.changed_by = u.id
            WHERE dsh.document_id = :document_id
            ORDER BY dsh.changed_at ASC
        ");
        $stmt->execute([':document_id' => $documentId]);
        $events = array_merge($events, $stmt->fetchAll(PDO::FETCH_ASSOC));

        // Internal upload activity
        $stmt = $this->db->prepare("
            SELECT
                al.id,
                'LLRM System' AS source_system,
                NULL AS external_reference,
                al.action AS event_action,
                al.description,
                al.created_at AS occurred_at,
                al.created_at,
                u.full_name AS recorded_by_name,
                'internal' AS source
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.table_name = 'documents' AND al.record_id = :document_id
            ORDER BY al.created_at ASC
        ");
        $stmt->execute([':document_id' => $documentId]);
        $events = array_merge($events, $stmt->fetchAll(PDO::FETCH_ASSOC));

        // Sort by occurred_at
        usort($events, function ($a, $b) {
            return strtotime($a['occurred_at']) <=> strtotime($b['occurred_at']);
        });

        return $events;
    }

    /**
     * Add an external tracking event.
     */
    public function addTrackingEvent($documentId, $data, $userId)
    {
        $required = ['source_system', 'event_action', 'occurred_at'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO document_tracking (
                document_id, source_system, external_reference, event_action,
                description, occurred_at, recorded_by
            ) VALUES (
                :document_id, :source_system, :external_reference, :event_action,
                :description, :occurred_at, :recorded_by
            )
        ");

        $stmt->execute([
            ':document_id' => $documentId,
            ':source_system' => $data['source_system'],
            ':external_reference' => $data['external_reference'] ?? null,
            ':event_action' => $data['event_action'],
            ':description' => $data['description'] ?? null,
            ':occurred_at' => $data['occurred_at'],
            ':recorded_by' => $userId,
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Check if a user has an admin-like role.
     */
    public function isAdmin($userRole)
    {
        $role = strtolower(trim($userRole));
        return in_array($role, ['admin', 'super_admin', 'superadmin', 'administrator'], true);
    }
}

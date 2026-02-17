<?php
/**
 * VDMsystem - Committee Management Controller
 * Handles CRUD operations for legislative committees
 */
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/audit.php';

class CommitteeController {
    private $db;
    
    public function __construct() {
        $this->db = getDatabase();
    }
    
    /**
     * Get all committees with optional filters
     */
    public function index() {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        $query = "SELECT c.*, 
                    (SELECT COUNT(*) FROM documents WHERE committee_id = c.id) as document_count,
                    (SELECT COUNT(*) FROM voting_sessions WHERE committee_id = c.id) as session_count
                  FROM committees c WHERE 1=1";
        $params = [];
        
        if ($filters['status'] !== null && $filters['status'] !== '') {
            $query .= " AND c.is_active = :status";
            $params[':status'] = (int)$filters['status'];
        }
        
        if ($filters['search']) {
            $query .= " AND c.name LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $query .= " ORDER BY c.name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $committees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'committees' => $committees,
            'total' => count($committees),
            'filters' => $filters
        ];
    }
    
    /**
     * Get a single committee
     */
    public function show($id) {
        $stmt = $this->db->prepare("
            SELECT c.*, 
                (SELECT COUNT(*) FROM documents WHERE committee_id = c.id) as document_count,
                (SELECT COUNT(*) FROM voting_sessions WHERE committee_id = c.id) as session_count
            FROM committees c WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create a new committee
     */
    public function create($data) {
        if (empty($data['name'])) {
            return ['success' => false, 'error' => 'Committee name is required'];
        }
        
        // Check uniqueness
        $stmt = $this->db->prepare("SELECT id FROM committees WHERE name = ?");
        $stmt->execute([$data['name']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'A committee with this name already exists'];
        }
        
        try {
            $id = dbInsert('committees', [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1
            ]);
            
            logAudit(
                'committee_created',
                $_SESSION['user_id'] ?? null,
                'committees',
                'committees',
                $id,
                'Created committee: ' . $data['name'],
                ['name' => $data['name']]
            );
            
            return ['success' => true, 'message' => 'Committee created successfully', 'id' => $id];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to create committee: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update a committee
     */
    public function update($id, $data) {
        $current = $this->show($id);
        if (!$current) {
            return ['success' => false, 'error' => 'Committee not found'];
        }
        
        if (empty($data['name'])) {
            return ['success' => false, 'error' => 'Committee name is required'];
        }
        
        // Check uniqueness (exclude current)
        $stmt = $this->db->prepare("SELECT id FROM committees WHERE name = ? AND id != ?");
        $stmt->execute([$data['name'], $id]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Another committee with this name already exists'];
        }
        
        try {
            $updateData = [
                'name' => $data['name'],
                'description' => $data['description'] ?? $current['description'] ?? null,
                'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : $current['is_active']
            ];
            
            dbUpdate('committees', $updateData, 'id = ?', [$id]);
            
            logAudit(
                'committee_updated',
                $_SESSION['user_id'] ?? null,
                'committees',
                'committees',
                $id,
                'Updated committee: ' . $data['name'],
                ['old_name' => $current['name'], 'new_name' => $data['name']]
            );
            
            return ['success' => true, 'message' => 'Committee updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to update committee: ' . $e->getMessage()];
        }
    }
    
    /**
     * Delete a committee
     */
    public function delete($id) {
        $committee = $this->show($id);
        if (!$committee) {
            return ['success' => false, 'error' => 'Committee not found'];
        }
        
        // Check if committee has associated documents or sessions
        if (($committee['document_count'] ?? 0) > 0 || ($committee['session_count'] ?? 0) > 0) {
            return ['success' => false, 'error' => 'Cannot delete committee with associated documents or sessions. Deactivate it instead.'];
        }
        
        try {
            dbDelete('committees', 'id = ?', [$id]);
            
            logAudit(
                'committee_deleted',
                $_SESSION['user_id'] ?? null,
                'committees',
                'committees',
                $id,
                'Deleted committee: ' . $committee['name'],
                ['name' => $committee['name']]
            );
            
            return ['success' => true, 'message' => 'Committee deleted successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to delete committee: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get committee statistics
     */
    public function getStatistics() {
        $stats = [];
        $stats['total'] = dbCount('committees');
        $stats['active'] = dbCount('committees', 'is_active = 1');
        $stats['inactive'] = $stats['total'] - $stats['active'];
        
        // Total documents across all committees
        $stmt = $this->db->query("SELECT COUNT(*) FROM documents WHERE committee_id IS NOT NULL");
        $stats['total_documents'] = $stmt->fetchColumn();
        
        // Total sessions across all committees
        $stmt = $this->db->query("SELECT COUNT(*) FROM voting_sessions WHERE committee_id IS NOT NULL");
        $stats['total_sessions'] = $stmt->fetchColumn();
        
        return $stats;
    }
}

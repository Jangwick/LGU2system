<?php
require_once __DIR__ . '/../../core/config/config.php';

class IntegrationController
{
    private $lrmsDb = null;

    /**
     * Connect to the LLRM system database
     */
    private function getLrmsDatabase()
    {
        if ($this->lrmsDb === null) {
            try {
                $this->lrmsDb = new PDO(
                    "mysql:host=127.0.0.1;port=3306;dbname=lrms_db;charset=utf8mb4",
                    'root',
                    '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                return null;
            }
        }
        return $this->lrmsDb;
    }

    /**
     * Check if LRMS database is accessible
     */
    public function isConnected()
    {
        return $this->getLrmsDatabase() !== null;
    }

    /**
     * Get legislative documents with filters
     */
    public function getDocuments($type = null, $filters = [])
    {
        $db = $this->getLrmsDatabase();
        if (!$db) {
            return ['connected' => false, 'documents' => [], 'total' => 0, 'page' => 1, 'perPage' => 20, 'totalPages' => 0, 'filters' => $filters];
        }

        $page = max(1, intval($filters['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = ["ld.deleted_at IS NULL"];
        $params = [];

        if ($type) {
            $where[] = "ld.document_type = ?";
            $params[] = $type;
        }

        if (!empty($filters['status'])) {
            $where[] = "ld.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(ld.title LIKE ? OR ld.reference_number LIKE ? OR ld.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['year'])) {
            $where[] = "YEAR(ld.document_date) = ?";
            $params[] = $filters['year'];
        }

        if (!empty($filters['document_type']) && !$type) {
            $where[] = "ld.document_type = ?";
            $params[] = $filters['document_type'];
        }

        $whereClause = implode(' AND ', $where);

        // Count
        $countSql = "SELECT COUNT(*) FROM legislative_documents ld WHERE $whereClause";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        $totalPages = ceil($total / $perPage);

        // Fetch
        $sql = "SELECT ld.*, u.full_name AS uploaded_by_name 
                FROM legislative_documents ld 
                LEFT JOIN users u ON ld.uploaded_by = u.id 
                WHERE $whereClause 
                ORDER BY ld.document_date DESC, ld.created_at DESC 
                LIMIT $perPage OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $documents = $stmt->fetchAll();

        return [
            'connected' => true,
            'documents' => $documents,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'filters' => $filters
        ];
    }

    /**
     * Get LRMS statistics
     */
    public function getStatistics($type = null)
    {
        $db = $this->getLrmsDatabase();
        if (!$db) {
            return ['connected' => false, 'total' => 0, 'approved' => 0, 'pending' => 0, 'draft' => 0];
        }

        $typeFilter = '';
        $params = [];
        if ($type) {
            $typeFilter = "AND document_type = ?";
            $params[] = $type;
        }

        $total = $db->prepare("SELECT COUNT(*) FROM legislative_documents WHERE deleted_at IS NULL $typeFilter");
        $total->execute($params);

        $approved = $db->prepare("SELECT COUNT(*) FROM legislative_documents WHERE deleted_at IS NULL AND status = 'approved' $typeFilter");
        $approved->execute($params);

        $pending = $db->prepare("SELECT COUNT(*) FROM legislative_documents WHERE deleted_at IS NULL AND status = 'pending' $typeFilter");
        $pending->execute($params);

        $draft = $db->prepare("SELECT COUNT(*) FROM legislative_documents WHERE deleted_at IS NULL AND status = 'draft' $typeFilter");
        $draft->execute($params);

        return [
            'connected' => true,
            'total' => $total->fetchColumn(),
            'approved' => $approved->fetchColumn(),
            'pending' => $pending->fetchColumn(),
            'draft' => $draft->fetchColumn(),
        ];
    }

    /**
     * Get available years from documents
     */
    public function getAvailableYears($type = null)
    {
        $db = $this->getLrmsDatabase();
        if (!$db) return [];

        $typeFilter = '';
        $params = [];
        if ($type) {
            $typeFilter = "AND document_type = ?";
            $params[] = $type;
        }

        $stmt = $db->prepare("SELECT DISTINCT YEAR(document_date) as yr FROM legislative_documents WHERE deleted_at IS NULL AND document_date IS NOT NULL $typeFilter ORDER BY yr DESC");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get available document types (for LRMS records)
     */
    public function getDocumentTypes()
    {
        $db = $this->getLrmsDatabase();
        if (!$db) return [];

        $stmt = $db->query("SELECT DISTINCT document_type FROM legislative_documents WHERE deleted_at IS NULL ORDER BY document_type");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

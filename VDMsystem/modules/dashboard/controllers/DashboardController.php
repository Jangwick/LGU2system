<?php
/**
 * VDMsystem - Dashboard Controller
 */
require_once __DIR__ . '/../../core/config/database.php';

class DashboardController {
    
    /**
     * Get dashboard statistics
     */
    public function getStatistics() {
        try {
            $stats = [
                'total_documents' => $this->getTotalDocuments(),
                'pending_vote' => $this->getPendingVoteCount(),
                'approved' => $this->getApprovedCount(),
                'rejected' => $this->getRejectedCount(),
                'active_sessions' => $this->getActiveSessionsCount(),
                'total_votes' => $this->getTotalVotesCount(),
            ];
            return $stats;
        } catch (Exception $e) {
            error_log('Dashboard stats error: ' . $e->getMessage());
            return [
                'total_documents' => 0,
                'pending_vote' => 0,
                'approved' => 0,
                'rejected' => 0,
                'active_sessions' => 0,
                'total_votes' => 0,
            ];
        }
    }
    
    /**
     * Get total documents count
     */
    private function getTotalDocuments() {
        return dbCount('documents');
    }
    
    /**
     * Get pending vote count
     */
    private function getPendingVoteCount() {
        return dbCount('documents', "status = 'pending_vote'");
    }
    
    /**
     * Get approved count (this month)
     */
    private function getApprovedCount() {
        return dbCount('documents', "status = 'approved' AND MONTH(approved_at) = MONTH(CURRENT_DATE()) AND YEAR(approved_at) = YEAR(CURRENT_DATE())");
    }
    
    /**
     * Get rejected count
     */
    private function getRejectedCount() {
        return dbCount('documents', "status = 'rejected'");
    }
    
    /**
     * Get active sessions count
     */
    private function getActiveSessionsCount() {
        return dbCount('voting_sessions', "status = 'in_progress'");
    }
    
    /**
     * Get total votes count
     */
    private function getTotalVotesCount() {
        return dbCount('votes');
    }
    
    /**
     * Get recent documents
     */
    public function getRecentDocuments($limit = 5) {
        try {
            return dbFetchAll(
                "SELECT d.id, d.doc_number, d.title, d.type, d.status, d.created_at, u.full_name as author_name 
                 FROM documents d 
                 LEFT JOIN users u ON d.author_id = u.id 
                 ORDER BY d.created_at DESC 
                 LIMIT ?",
                [$limit]
            );
        } catch (Exception $e) {
            error_log('Recent documents error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get upcoming voting sessions
     */
    public function getUpcomingSessions($limit = 5) {
        try {
            return dbFetchAll(
                "SELECT id, session_number, title, session_date, start_time, status, vote_type 
                 FROM voting_sessions 
                 WHERE status IN ('scheduled', 'in_progress') 
                 AND session_date >= CURRENT_DATE()
                 ORDER BY session_date ASC, start_time ASC 
                 LIMIT ?",
                [$limit]
            );
        } catch (Exception $e) {
            error_log('Upcoming sessions error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent votes (for current user if councilor)
     */
    public function getRecentVotes($limit = 5) {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $userRole = strtolower($_SESSION['user_role'] ?? 'viewer');
            
            // Councilors see only their votes, admins see all
            if (in_array($userRole, ['admin', 'administrator', 'secretary'])) {
                return dbFetchAll(
                    "SELECT v.id, v.vote, v.cast_at, v.councilor_id, d.title as document_title, u.full_name as voter_name
                     FROM votes v
                     JOIN documents d ON v.document_id = d.id
                     JOIN users u ON v.councilor_id = u.id
                     ORDER BY v.cast_at DESC
                     LIMIT ?",
                    [$limit]
                );
            } else {
                return dbFetchAll(
                    "SELECT v.id, v.vote, v.cast_at, d.title as document_title
                     FROM votes v
                     JOIN documents d ON v.document_id = d.id
                     WHERE v.councilor_id = ?
                     ORDER BY v.cast_at DESC
                     LIMIT ?",
                    [$userId, $limit]
                );
            }
        } catch (Exception $e) {
            error_log('Recent votes error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get voting trend data for charts
     */
    public function getVotingTrend($days = 7) {
        try {
            $data = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $approved = dbCount('votes', "vote = 'approve' AND DATE(cast_at) = ?", [$date]);
                $rejected = dbCount('votes', "vote = 'reject' AND DATE(cast_at) = ?", [$date]);
                $abstained = dbCount('votes', "vote = 'abstain' AND DATE(cast_at) = ?", [$date]);
                
                $data[] = [
                    'date' => $date,
                    'label' => date('D', strtotime($date)),
                    'approved' => $approved,
                    'rejected' => $rejected,
                    'abstained' => $abstained
                ];
            }
            return $data;
        } catch (Exception $e) {
            error_log('Voting trend error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get document status distribution
     */
    public function getDocumentStatusDistribution() {
        try {
            $statuses = ['draft', 'under_review', 'committee_review', 'pending_vote', 'approved', 'rejected', 'archived'];
            $distribution = [];
            
            foreach ($statuses as $status) {
                $count = dbCount('documents', "status = ?", [$status]);
                $distribution[$status] = $count;
            }
            
            return $distribution;
        } catch (Exception $e) {
            error_log('Document distribution error: ' . $e->getMessage());
            return [];
        }
    }
}

<?php
/**
 * VDMsystem - Dashboard Controller
 * Comprehensive dashboard data provider
 */
require_once __DIR__ . '/../../core/config/database.php';

class DashboardController {

    private $db;

    public function __construct() {
        $this->db = getDatabase();
    }

    /* =========================================================
     *  STATISTICS CARDS
     * ========================================================= */

    public function getStatistics() {
        try {
            return [
                'total_sessions'      => $this->count('voting_sessions'),
                'active_sessions'     => $this->count('voting_sessions', "status = 'in_progress'"),
                'scheduled_sessions'  => $this->count('voting_sessions', "status = 'scheduled'"),
                'completed_sessions'  => $this->count('voting_sessions', "status = 'completed'"),
                'total_votes'         => $this->count('votes'),
                'pending_vote'        => $this->count('documents', "status = 'pending_vote'"),
                'approved'            => $this->count('documents', "status = 'approved' AND MONTH(updated_at) = MONTH(CURRENT_DATE()) AND YEAR(updated_at) = YEAR(CURRENT_DATE())"),
                'rejected'            => $this->count('documents', "status = 'rejected'"),
                'total_documents'     => $this->count('documents'),
                'total_users'         => $this->count('users', "is_active = 1"),
                'total_committees'    => $this->count('committees', "is_active = 1"),
                'today_actions'       => $this->count('audit_logs', "DATE(created_at) = CURRENT_DATE()"),
            ];
        } catch (Exception $e) {
            error_log('Dashboard stats error: ' . $e->getMessage());
            return array_fill_keys([
                'total_sessions','active_sessions','scheduled_sessions','completed_sessions',
                'total_votes','pending_vote','approved','rejected','total_documents',
                'total_users','total_committees','today_actions'
            ], 0);
        }
    }

    /* =========================================================
     *  VOTING TREND (last N days)
     * ========================================================= */

    public function getVotingTrend($days = 7) {
        try {
            $data = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $data[] = [
                    'date'     => $date,
                    'label'    => date('D', strtotime($date)),
                    'full'     => date('M d', strtotime($date)),
                    'approved' => (int) $this->count('votes', "vote = 'approve' AND DATE(cast_at) = ?", [$date]),
                    'rejected' => (int) $this->count('votes', "vote = 'reject'  AND DATE(cast_at) = ?", [$date]),
                    'abstained'=> (int) $this->count('votes', "vote = 'abstain' AND DATE(cast_at) = ?", [$date]),
                ];
            }
            return $data;
        } catch (Exception $e) {
            error_log('Voting trend error: ' . $e->getMessage());
            return [];
        }
    }

    /* =========================================================
     *  SESSION STATUS DISTRIBUTION (for doughnut chart)
     * ========================================================= */

    public function getSessionStatusDistribution() {
        try {
            $statuses = ['scheduled','in_progress','completed','cancelled'];
            $dist = [];
            foreach ($statuses as $s) {
                $dist[$s] = (int) $this->count('voting_sessions', "status = ?", [$s]);
            }
            return $dist;
        } catch (Exception $e) {
            return [];
        }
    }

    /* =========================================================
     *  DOCUMENT STATUS DISTRIBUTION
     * ========================================================= */

    public function getDocumentStatusDistribution() {
        try {
            $statuses = ['draft','under_review','committee_review','pending_vote','approved','rejected','archived'];
            $dist = [];
            foreach ($statuses as $s) {
                $dist[$s] = (int) $this->count('documents', "status = ?", [$s]);
            }
            return $dist;
        } catch (Exception $e) {
            return [];
        }
    }

    /* =========================================================
     *  MONTHLY VOTING SUMMARY (last 6 months)
     * ========================================================= */

    public function getMonthlyVotingSummary($months = 6) {
        try {
            $data = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $monthStart = date('Y-m-01', strtotime("-$i months"));
                $monthEnd   = date('Y-m-t', strtotime("-$i months"));
                $label      = date('M Y', strtotime($monthStart));

                $data[] = [
                    'label'    => $label,
                    'short'    => date('M', strtotime($monthStart)),
                    'sessions' => (int) $this->count('voting_sessions', "session_date BETWEEN ? AND ?", [$monthStart, $monthEnd]),
                    'approved' => (int) $this->count('votes', "vote = 'approve' AND DATE(cast_at) BETWEEN ? AND ?", [$monthStart, $monthEnd]),
                    'rejected' => (int) $this->count('votes', "vote = 'reject'  AND DATE(cast_at) BETWEEN ? AND ?", [$monthStart, $monthEnd]),
                    'abstained'=> (int) $this->count('votes', "vote = 'abstain' AND DATE(cast_at) BETWEEN ? AND ?", [$monthStart, $monthEnd]),
                ];
            }
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }

    /* =========================================================
     *  UPCOMING / ACTIVE SESSIONS
     * ========================================================= */

    public function getUpcomingSessions($limit = 5) {
        try {
            return $this->fetchAll(
                "SELECT vs.id, vs.session_number, vs.title, vs.session_date, vs.start_time, vs.end_time,
                        vs.status, vs.vote_type, vs.location,
                        u.full_name AS presider_name,
                        (SELECT COUNT(*) FROM session_documents sd WHERE sd.session_id = vs.id) AS doc_count,
                        (SELECT COUNT(*) FROM session_attendees sa WHERE sa.session_id = vs.id) AS attendee_count
                 FROM voting_sessions vs
                 LEFT JOIN users u ON vs.presider_id = u.id
                 WHERE vs.status IN ('scheduled','in_progress')
                 ORDER BY vs.session_date ASC, vs.start_time ASC
                 LIMIT ?", [$limit]
            );
        } catch (Exception $e) {
            return [];
        }
    }

    /* =========================================================
     *  RECENT VOTES
     * ========================================================= */

    public function getRecentVotes($limit = 8) {
        try {
            $userId  = $_SESSION['user_id'] ?? null;
            $role    = strtolower($_SESSION['user_role'] ?? 'viewer');
            $isAdmin = in_array($role, ['admin','administrator','secretary']);

            if ($isAdmin) {
                return $this->fetchAll(
                    "SELECT v.id, v.vote, v.cast_at, v.remarks,
                            d.title AS document_title, d.doc_number,
                            u.full_name AS voter_name,
                            vs.title AS session_title
                     FROM votes v
                     JOIN documents d ON v.document_id = d.id
                     JOIN users u ON v.councilor_id = u.id
                     LEFT JOIN voting_sessions vs ON v.session_id = vs.id
                     ORDER BY v.cast_at DESC LIMIT ?", [$limit]
                );
            }
            return $this->fetchAll(
                "SELECT v.id, v.vote, v.cast_at, v.remarks,
                        d.title AS document_title, d.doc_number,
                        vs.title AS session_title
                 FROM votes v
                 JOIN documents d ON v.document_id = d.id
                 LEFT JOIN voting_sessions vs ON v.session_id = vs.id
                 WHERE v.councilor_id = ?
                 ORDER BY v.cast_at DESC LIMIT ?", [$userId, $limit]
            );
        } catch (Exception $e) {
            return [];
        }
    }

    /* =========================================================
     *  RECENT DOCUMENTS
     * ========================================================= */

    public function getRecentDocuments($limit = 5) {
        try {
            return $this->fetchAll(
                "SELECT d.id, d.doc_number, d.title, d.type, d.status, d.priority, d.created_at,
                        u.full_name AS author_name
                 FROM documents d
                 LEFT JOIN users u ON d.author_id = u.id
                 ORDER BY d.created_at DESC LIMIT ?", [$limit]
            );
        } catch (Exception $e) {
            return [];
        }
    }

    /* =========================================================
     *  RECENT AUDIT ACTIVITY
     * ========================================================= */

    public function getRecentAuditActivity($limit = 10) {
        try {
            return $this->fetchAll(
                "SELECT a.id, a.event_type, u.full_name AS user_name, a.module, a.action, a.details, a.created_at
                 FROM audit_logs a
                 LEFT JOIN users u ON a.user_id = u.id
                 ORDER BY a.created_at DESC LIMIT ?", [$limit]
            );
        } catch (Exception $e) {
            return [];
        }
    }

    /* =========================================================
     *  USER NOTIFICATIONS (unread)
     * ========================================================= */

    public function getUnreadNotifications($limit = 5) {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) return [];
            return $this->fetchAll(
                "SELECT id, type, title, message, link, created_at
                 FROM notifications
                 WHERE user_id = ? AND is_read = 0
                 ORDER BY created_at DESC LIMIT ?", [$userId, $limit]
            );
        } catch (Exception $e) {
            return [];
        }
    }

    public function getUnreadNotificationCount() {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) return 0;
            return (int) $this->count('notifications', "user_id = ? AND is_read = 0", [$userId]);
        } catch (Exception $e) {
            return 0;
        }
    }

    /* =========================================================
     *  TOP VOTERS (councilors with most votes)
     * ========================================================= */

    public function getTopVoters($limit = 5) {
        try {
            return $this->fetchAll(
                "SELECT u.id, u.full_name, u.position, u.department, u.profile_picture,
                        COUNT(v.id) AS vote_count,
                        SUM(CASE WHEN v.vote = 'approve' THEN 1 ELSE 0 END) AS approved,
                        SUM(CASE WHEN v.vote = 'reject'  THEN 1 ELSE 0 END) AS rejected,
                        SUM(CASE WHEN v.vote = 'abstain' THEN 1 ELSE 0 END) AS abstained
                 FROM users u
                 JOIN votes v ON v.councilor_id = u.id
                 WHERE u.is_active = 1
                 GROUP BY u.id
                 ORDER BY vote_count DESC LIMIT ?", [$limit]
            );
        } catch (Exception $e) {
            return [];
        }
    }

    /* =========================================================
     *  APPROVAL RATE
     * ========================================================= */

    public function getApprovalRate() {
        try {
            $total    = (int) $this->count('votes');
            $approved = (int) $this->count('votes', "vote = 'approve'");
            return $total > 0 ? round(($approved / $total) * 100, 1) : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /* =========================================================
     *  MY PENDING VOTES  (for councilor)
     * ========================================================= */

    public function getMyPendingVotes() {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) return [];
            return $this->fetchAll(
                "SELECT sd.id, sd.document_id, sd.session_id, d.title AS document_title, d.doc_number,
                        vs.title AS session_title, vs.session_date
                 FROM session_documents sd
                 JOIN documents d ON sd.document_id = d.id
                 JOIN voting_sessions vs ON sd.session_id = vs.id
                 WHERE vs.status = 'in_progress'
                   AND sd.status IN ('pending','in_progress')
                   AND sd.document_id NOT IN (
                       SELECT v.document_id FROM votes v WHERE v.councilor_id = ? AND v.session_id = sd.session_id
                   )
                 ORDER BY vs.session_date ASC", [$userId]
            );
        } catch (Exception $e) {
            return [];
        }
    }

    /* =========================================================
     *  SYSTEM PERFORMANCE (landing page)
     * ========================================================= */

    public function getSystemPerformanceStats() {
        try {
            return [
                'total_records'  => number_format($this->count('documents') + $this->count('votes')),
                'latency'        => '120ms',
                'reliability'    => '99.9%',
                'daily_consults' => number_format($this->count('audit_logs', "DATE(created_at) = CURRENT_DATE()")) ?: '0'
            ];
        } catch (Exception $e) {
            return ['total_records' => '0','latency' => '0ms','reliability' => '100%','daily_consults' => '0'];
        }
    }

    /* =========================================================
     *  HELPERS
     * ========================================================= */

    private function count($table, $where = null, $params = []) {
        $sql = "SELECT COUNT(*) FROM $table";
        if ($where) $sql .= " WHERE $where";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function fetchAll($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

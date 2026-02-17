<?php
/**
 * VDMsystem - Reports & Analytics Controller
 * Comprehensive reporting engine for legislative performance,
 * voting trends, attendance metrics, and document analytics.
 */
require_once __DIR__ . '/../../core/config/database.php';

class ReportsController {

    private $db;

    public function __construct() {
        $this->db = getDatabase();
    }

    /* =========================================================
     *  KPI STATISTICS (date-filtered)
     * ========================================================= */

    public function getKPIStats($startDate, $endDate) {
        try {
            $totalSessions  = $this->count('voting_sessions', "session_date BETWEEN ? AND ?", [$startDate, $endDate]);
            $completedSess  = $this->count('voting_sessions', "status = 'completed' AND session_date BETWEEN ? AND ?", [$startDate, $endDate]);
            $totalVotes     = $this->count('votes', "DATE(cast_at) BETWEEN ? AND ?", [$startDate, $endDate]);
            $totalDocuments = $this->count('documents', "DATE(created_at) BETWEEN ? AND ?", [$startDate, $endDate]);
            $docsApproved   = $this->count('documents', "status = 'approved' AND DATE(updated_at) BETWEEN ? AND ?", [$startDate, $endDate]);
            $docsRejected   = $this->count('documents', "status = 'rejected' AND DATE(updated_at) BETWEEN ? AND ?", [$startDate, $endDate]);
            $uniqueVoters   = $this->fetchScalar(
                "SELECT COUNT(DISTINCT councilor_id) FROM votes WHERE DATE(cast_at) BETWEEN ? AND ?",
                [$startDate, $endDate]
            );
            $avgVotesPerSession = $totalSessions > 0 ? round($totalVotes / $totalSessions, 1) : 0;
            $approvalRate = ($docsApproved + $docsRejected) > 0
                ? round(($docsApproved / ($docsApproved + $docsRejected)) * 100, 1) : 0;

            // Previous period comparison
            $periodDays = max(1, (strtotime($endDate) - strtotime($startDate)) / 86400);
            $prevStart  = date('Y-m-d', strtotime($startDate . " - {$periodDays} days"));
            $prevEnd    = date('Y-m-d', strtotime($startDate . " - 1 day"));
            $prevSessions = $this->count('voting_sessions', "session_date BETWEEN ? AND ?", [$prevStart, $prevEnd]);
            $prevVotes    = $this->count('votes', "DATE(cast_at) BETWEEN ? AND ?", [$prevStart, $prevEnd]);
            $prevDocsApproved = $this->count('documents', "status = 'approved' AND DATE(updated_at) BETWEEN ? AND ?", [$prevStart, $prevEnd]);

            return [
                'total_sessions'       => $totalSessions,
                'completed_sessions'   => $completedSess,
                'total_votes'          => $totalVotes,
                'total_documents'      => $totalDocuments,
                'documents_approved'   => $docsApproved,
                'documents_rejected'   => $docsRejected,
                'unique_voters'        => $uniqueVoters,
                'avg_votes_per_session'=> $avgVotesPerSession,
                'approval_rate'        => $approvalRate,
                'session_change'       => $this->calcChange($totalSessions, $prevSessions),
                'vote_change'          => $this->calcChange($totalVotes, $prevVotes),
                'approval_change'      => $this->calcChange($docsApproved, $prevDocsApproved),
            ];
        } catch (Exception $e) {
            error_log('ReportsController::getKPIStats error: ' . $e->getMessage());
            return array_fill_keys([
                'total_sessions','completed_sessions','total_votes','total_documents',
                'documents_approved','documents_rejected','unique_voters',
                'avg_votes_per_session','approval_rate','session_change','vote_change','approval_change'
            ], 0);
        }
    }

    /* =========================================================
     *  VOTING TREND (daily aggregates)
     * ========================================================= */

    public function getVotingTrend($startDate, $endDate) {
        try {
            return $this->fetchAll(
                "SELECT DATE(cast_at) as date,
                        SUM(CASE WHEN vote = 'approve' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN vote = 'reject' THEN 1 ELSE 0 END) as rejected,
                        SUM(CASE WHEN vote = 'abstain' THEN 1 ELSE 0 END) as abstained,
                        COUNT(*) as total
                 FROM votes
                 WHERE DATE(cast_at) BETWEEN ? AND ?
                 GROUP BY DATE(cast_at)
                 ORDER BY date",
                [$startDate, $endDate]
            );
        } catch (Exception $e) {
            error_log('ReportsController::getVotingTrend error: ' . $e->getMessage());
            return [];
        }
    }

    /* =========================================================
     *  MONTHLY COMPARISON (last 12 months)
     * ========================================================= */

    public function getMonthlyComparison($months = 12) {
        try {
            $data = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $monthStart = date('Y-m-01', strtotime("-$i months"));
                $monthEnd   = date('Y-m-t', strtotime("-$i months"));
                $data[] = [
                    'label'    => date('M Y', strtotime($monthStart)),
                    'short'    => date('M', strtotime($monthStart)),
                    'sessions' => (int) $this->count('voting_sessions', "session_date BETWEEN ? AND ?", [$monthStart, $monthEnd]),
                    'votes'    => (int) $this->count('votes', "DATE(cast_at) BETWEEN ? AND ?", [$monthStart, $monthEnd]),
                    'approved' => (int) $this->count('votes', "vote = 'approve' AND DATE(cast_at) BETWEEN ? AND ?", [$monthStart, $monthEnd]),
                    'rejected' => (int) $this->count('votes', "vote = 'reject'  AND DATE(cast_at) BETWEEN ? AND ?", [$monthStart, $monthEnd]),
                    'abstained'=> (int) $this->count('votes', "vote = 'abstain' AND DATE(cast_at) BETWEEN ? AND ?", [$monthStart, $monthEnd]),
                    'documents'=> (int) $this->count('documents', "DATE(created_at) BETWEEN ? AND ?", [$monthStart, $monthEnd]),
                ];
            }
            return $data;
        } catch (Exception $e) {
            error_log('ReportsController::getMonthlyComparison error: ' . $e->getMessage());
            return [];
        }
    }

    /* =========================================================
     *  DOCUMENT TYPE DISTRIBUTION
     * ========================================================= */

    public function getDocumentTypeDistribution($startDate, $endDate) {
        try {
            return $this->fetchAll(
                "SELECT type, COUNT(*) as count,
                        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                        SUM(CASE WHEN status = 'pending_vote' THEN 1 ELSE 0 END) as pending
                 FROM documents
                 WHERE DATE(created_at) BETWEEN ? AND ?
                 GROUP BY type
                 ORDER BY count DESC",
                [$startDate, $endDate]
            );
        } catch (Exception $e) {
            return [];
        }
    }

    /* =========================================================
     *  DOCUMENT STATUS DISTRIBUTION
     * ========================================================= */

    public function getDocumentStatusDistribution($startDate, $endDate) {
        try {
            $statuses = ['draft','under_review','committee_review','pending_vote','approved','rejected','archived'];
            $result = [];
            foreach ($statuses as $s) {
                $result[$s] = (int) $this->count('documents', "status = ? AND DATE(created_at) BETWEEN ? AND ?", [$s, $startDate, $endDate]);
            }
            return $result;
        } catch (Exception $e) {
            return [];
        }
    }

    /* =========================================================
     *  SESSION STATUS BREAKDOWN
     * ========================================================= */

    public function getSessionStatusBreakdown($startDate, $endDate) {
        try {
            $statuses = ['scheduled','in_progress','completed','cancelled'];
            $result = [];
            foreach ($statuses as $s) {
                $result[$s] = (int) $this->count('voting_sessions', "status = ? AND session_date BETWEEN ? AND ?", [$s, $startDate, $endDate]);
            }
            return $result;
        } catch (Exception $e) {
            return [];
        }
    }

    /* =========================================================
     *  LEGISLATOR PARTICIPATION MATRIX
     * ========================================================= */

    public function getLegislatorParticipation($startDate, $endDate, $limit = 20) {
        try {
            return $this->fetchAll(
                "SELECT u.id, u.full_name, u.position, u.department, u.profile_picture,
                        COUNT(v.id) as total_votes,
                        SUM(CASE WHEN v.vote = 'approve' THEN 1 ELSE 0 END) as approve_count,
                        SUM(CASE WHEN v.vote = 'reject' THEN 1 ELSE 0 END) as reject_count,
                        SUM(CASE WHEN v.vote = 'abstain' THEN 1 ELSE 0 END) as abstain_count,
                        COUNT(DISTINCT v.session_id) as sessions_participated
                 FROM users u
                 LEFT JOIN votes v ON u.id = v.councilor_id AND DATE(v.cast_at) BETWEEN ? AND ?
                 WHERE u.role IN ('councilor', 'admin') AND u.is_active = 1
                 GROUP BY u.id, u.full_name, u.position, u.department, u.profile_picture
                 ORDER BY total_votes DESC
                 LIMIT ?",
                [$startDate, $endDate, $limit]
            );
        } catch (Exception $e) {
            error_log('ReportsController::getLegislatorParticipation error: ' . $e->getMessage());
            return [];
        }
    }

    /* =========================================================
     *  ATTENDANCE ANALYTICS
     * ========================================================= */

    public function getAttendanceAnalytics($startDate, $endDate) {
        try {
            $totalSessions = $this->count('voting_sessions', "session_date BETWEEN ? AND ?", [$startDate, $endDate]);
            $totalAttendees = $this->fetchScalar(
                "SELECT COUNT(*) FROM session_attendees sa
                 JOIN voting_sessions vs ON sa.session_id = vs.id
                 WHERE vs.session_date BETWEEN ? AND ? AND sa.status = 'present'",
                [$startDate, $endDate]
            );
            $totalExpected = $this->fetchScalar(
                "SELECT COUNT(*) FROM session_attendees sa
                 JOIN voting_sessions vs ON sa.session_id = vs.id
                 WHERE vs.session_date BETWEEN ? AND ?",
                [$startDate, $endDate]
            );
            $attendanceRate = $totalExpected > 0 ? round(($totalAttendees / $totalExpected) * 100, 1) : 0;

            // Per-user attendance
            $perUser = $this->fetchAll(
                "SELECT u.full_name, u.position,
                        COUNT(sa.id) as total_assigned,
                        SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) as times_present,
                        SUM(CASE WHEN sa.status = 'absent' THEN 1 ELSE 0 END) as times_absent
                 FROM users u
                 JOIN session_attendees sa ON u.id = sa.user_id
                 JOIN voting_sessions vs ON sa.session_id = vs.id
                 WHERE vs.session_date BETWEEN ? AND ? AND u.is_active = 1
                 GROUP BY u.id, u.full_name, u.position
                 ORDER BY times_present DESC",
                [$startDate, $endDate]
            );

            return [
                'total_sessions'  => $totalSessions,
                'total_present'   => (int) $totalAttendees,
                'total_expected'  => (int) $totalExpected,
                'attendance_rate' => $attendanceRate,
                'per_user'        => $perUser,
            ];
        } catch (Exception $e) {
            error_log('ReportsController::getAttendanceAnalytics error: ' . $e->getMessage());
            return [
                'total_sessions' => 0, 'total_present' => 0, 'total_expected' => 0,
                'attendance_rate' => 0, 'per_user' => []
            ];
        }
    }

    /* =========================================================
     *  VOTE DISTRIBUTION (overall breakdown)
     * ========================================================= */

    public function getVoteDistribution($startDate, $endDate) {
        try {
            $approves  = $this->count('votes', "vote = 'approve' AND DATE(cast_at) BETWEEN ? AND ?", [$startDate, $endDate]);
            $rejects   = $this->count('votes', "vote = 'reject'  AND DATE(cast_at) BETWEEN ? AND ?", [$startDate, $endDate]);
            $abstains  = $this->count('votes', "vote = 'abstain' AND DATE(cast_at) BETWEEN ? AND ?", [$startDate, $endDate]);
            $total     = $approves + $rejects + $abstains;
            return [
                'approve'  => $approves,
                'reject'   => $rejects,
                'abstain'  => $abstains,
                'total'    => $total,
                'approve_pct' => $total > 0 ? round(($approves / $total) * 100, 1) : 0,
                'reject_pct'  => $total > 0 ? round(($rejects / $total) * 100, 1) : 0,
                'abstain_pct' => $total > 0 ? round(($abstains / $total) * 100, 1) : 0,
            ];
        } catch (Exception $e) {
            return ['approve' => 0, 'reject' => 0, 'abstain' => 0, 'total' => 0,
                    'approve_pct' => 0, 'reject_pct' => 0, 'abstain_pct' => 0];
        }
    }

    /* =========================================================
     *  SESSION DETAILS LIST (recent or filtered)
     * ========================================================= */

    public function getSessionsList($startDate, $endDate) {
        try {
            return $this->fetchAll(
                "SELECT vs.id, vs.session_number, vs.title, vs.status, vs.session_date,
                        vs.start_time, vs.actual_start_time, vs.actual_end_time,
                        c.name as committee_name,
                        u.full_name as created_by_name,
                        (SELECT COUNT(*) FROM session_documents WHERE session_id = vs.id) as doc_count,
                        (SELECT COUNT(*) FROM session_attendees WHERE session_id = vs.id AND status = 'present') as present_count,
                        (SELECT COUNT(*) FROM votes WHERE session_id = vs.id) as vote_count
                 FROM voting_sessions vs
                 LEFT JOIN users u ON vs.created_by = u.id
                 LEFT JOIN committees c ON vs.committee_id = c.id
                 WHERE vs.session_date BETWEEN ? AND ?
                 ORDER BY vs.session_date DESC, vs.start_time DESC",
                [$startDate, $endDate]
            );
        } catch (Exception $e) {
            return [];
        }
    }

    /* =========================================================
     *  COMMITTEE ANALYTICS
     * ========================================================= */

    public function getCommitteeAnalytics($startDate, $endDate) {
        try {
            return $this->fetchAll(
                "SELECT c.id, c.name,
                        COUNT(DISTINCT vs.id) as session_count,
                        COUNT(DISTINCT d.id) as document_count,
                        (SELECT COUNT(*) FROM votes v
                         JOIN voting_sessions vs2 ON v.session_id = vs2.id
                         WHERE vs2.committee_id = c.id AND DATE(v.cast_at) BETWEEN ? AND ?) as vote_count
                 FROM committees c
                 LEFT JOIN voting_sessions vs ON c.id = vs.committee_id AND vs.session_date BETWEEN ? AND ?
                 LEFT JOIN documents d ON c.id = d.committee_id AND DATE(d.created_at) BETWEEN ? AND ?
                 WHERE c.is_active = 1
                 GROUP BY c.id, c.name
                 HAVING session_count > 0 OR document_count > 0
                 ORDER BY session_count DESC",
                [$startDate, $endDate, $startDate, $endDate, $startDate, $endDate]
            );
        } catch (Exception $e) {
            error_log('ReportsController::getCommitteeAnalytics error: ' . $e->getMessage());
            return [];
        }
    }

    /* =========================================================
     *  RECENT AUDIT ACTIVITY (for reporting)
     * ========================================================= */

    public function getRecentActivity($startDate, $endDate, $limit = 15) {
        try {
            return $this->fetchAll(
                "SELECT a.id, a.event_type, a.module, a.action, a.created_at,
                        u.full_name as user_name
                 FROM audit_logs a
                 LEFT JOIN users u ON a.user_id = u.id
                 WHERE DATE(a.created_at) BETWEEN ? AND ?
                 ORDER BY a.created_at DESC
                 LIMIT ?",
                [$startDate, $endDate, $limit]
            );
        } catch (Exception $e) {
            return [];
        }
    }

    /* =========================================================
     *  DOCUMENT RESOLUTION TIMELINE
     * ========================================================= */

    public function getDocumentTimeline($startDate, $endDate, $limit = 10) {
        try {
            return $this->fetchAll(
                "SELECT d.id, d.doc_number, d.title, d.type, d.status, d.created_at, d.updated_at,
                        u.full_name as author_name,
                        c.name as committee_name,
                        DATEDIFF(COALESCE(d.approved_at, d.updated_at), d.created_at) as resolution_days
                 FROM documents d
                 LEFT JOIN users u ON d.author_id = u.id
                 LEFT JOIN committees c ON d.committee_id = c.id
                 WHERE DATE(d.created_at) BETWEEN ? AND ?
                 ORDER BY d.created_at DESC
                 LIMIT ?",
                [$startDate, $endDate, $limit]
            );
        } catch (Exception $e) {
            return [];
        }
    }

    /* =========================================================
     *  PEAK VOTING HOURS
     * ========================================================= */

    public function getVotingByHour($startDate, $endDate) {
        try {
            return $this->fetchAll(
                "SELECT HOUR(cast_at) as hour, COUNT(*) as count
                 FROM votes
                 WHERE DATE(cast_at) BETWEEN ? AND ?
                 GROUP BY HOUR(cast_at)
                 ORDER BY hour",
                [$startDate, $endDate]
            );
        } catch (Exception $e) {
            return [];
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

    private function fetchScalar($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    private function calcChange($current, $previous) {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }
}

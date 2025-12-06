<?php
/**
 * VDMsystem - Voting Controller
 * Handles voting session management and vote processing
 */
require_once __DIR__ . '/../../core/config/database.php';

class VotingController {
    
    /**
     * Get all voting sessions with optional filters
     */
    public function getSessions($filters = []) {
        try {
            $where = "1=1";
            $params = [];
            
            if (!empty($filters['status'])) {
                $where .= " AND vs.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['search'])) {
                $where .= " AND (vs.title LIKE ? OR vs.session_number LIKE ?)";
                $params[] = "%{$filters['search']}%";
                $params[] = "%{$filters['search']}%";
            }
            
            if (!empty($filters['date_from'])) {
                $where .= " AND vs.session_date >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $where .= " AND vs.session_date <= ?";
                $params[] = $filters['date_to'];
            }
            
            return dbFetchAll(
                "SELECT vs.*, u.full_name as created_by_name,
                        (SELECT COUNT(*) FROM session_documents WHERE session_id = vs.id) as document_count,
                        (SELECT COUNT(*) FROM session_attendees WHERE session_id = vs.id AND status = 'present') as attendee_count,
                        (SELECT COUNT(*) FROM votes WHERE session_id = vs.id) as vote_count
                 FROM voting_sessions vs
                 LEFT JOIN users u ON vs.created_by = u.id
                 WHERE $where
                 ORDER BY vs.session_date DESC, vs.start_time DESC",
                $params
            );
        } catch (Exception $e) {
            error_log('VotingController::getSessions error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get a single session by ID
     */
    public function getSession($sessionId) {
        try {
            return dbFetchOne(
                "SELECT vs.*, u.full_name as created_by_name, c.name as committee_name
                 FROM voting_sessions vs
                 LEFT JOIN users u ON vs.created_by = u.id
                 LEFT JOIN committees c ON vs.committee_id = c.id
                 WHERE vs.id = ?",
                [$sessionId]
            );
        } catch (Exception $e) {
            error_log('VotingController::getSession error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a new voting session
     */
    public function createSession($data) {
        try {
            // Generate session number
            $year = date('Y');
            $count = dbCount('voting_sessions', "YEAR(created_at) = ?", [$year]);
            $sessionNumber = sprintf("VS-%s-%04d", $year, $count + 1);
            
            $sessionId = dbInsert('voting_sessions', [
                'session_number' => $sessionNumber,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'session_date' => $data['session_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'] ?? null,
                'location' => $data['location'] ?? 'Session Hall',
                'vote_type' => $data['vote_type'] ?? 'roll_call',
                'quorum_required' => $data['quorum_required'] ?? 5,
                'committee_id' => $data['committee_id'] ?? null,
                'status' => 'scheduled',
                'created_by' => $data['created_by']
            ]);
            
            // Add documents to session
            if (!empty($data['documents']) && is_array($data['documents'])) {
                $order = 1;
                foreach ($data['documents'] as $docId) {
                    dbInsert('session_documents', [
                        'session_id' => $sessionId,
                        'document_id' => $docId,
                        'voting_order' => $order++,
                        'voting_status' => 'pending'
                    ]);
                }
            }
            
            // Add attendees
            if (!empty($data['attendees']) && is_array($data['attendees'])) {
                foreach ($data['attendees'] as $userId) {
                    dbInsert('session_attendees', [
                        'session_id' => $sessionId,
                        'user_id' => $userId,
                        'status' => 'absent'
                    ]);
                }
            }
            
            return $sessionId;
        } catch (Exception $e) {
            error_log('VotingController::createSession error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Start a voting session
     */
    public function startSession($sessionId, $userId) {
        try {
            $session = $this->getSession($sessionId);
            if (!$session) {
                throw new Exception("Session not found");
            }
            
            if ($session['status'] !== 'scheduled') {
                throw new Exception("Session cannot be started. Current status: " . $session['status']);
            }
            
            dbUpdate('voting_sessions', $sessionId, [
                'status' => 'in_progress',
                'actual_start_time' => date('Y-m-d H:i:s')
            ]);
            
            logAudit($userId, 'session_start', 'voting_sessions', $sessionId, 
                     ['status' => 'scheduled'], ['status' => 'in_progress']);
            
            return true;
        } catch (Exception $e) {
            error_log('VotingController::startSession error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * End a voting session
     */
    public function endSession($sessionId, $userId) {
        try {
            $session = $this->getSession($sessionId);
            if (!$session) {
                throw new Exception("Session not found");
            }
            
            if ($session['status'] !== 'in_progress') {
                throw new Exception("Session cannot be ended. Current status: " . $session['status']);
            }
            
            // Calculate final results for all documents
            $this->calculateSessionResults($sessionId);
            
            dbUpdate('voting_sessions', $sessionId, [
                'status' => 'completed',
                'actual_end_time' => date('Y-m-d H:i:s')
            ]);
            
            logAudit($userId, 'session_end', 'voting_sessions', $sessionId,
                     ['status' => 'in_progress'], ['status' => 'completed']);
            
            return true;
        } catch (Exception $e) {
            error_log('VotingController::endSession error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cast a vote
     */
    public function castVote($data) {
        try {
            // Validate vote value
            if (!in_array($data['vote'], ['approve', 'reject', 'abstain'])) {
                throw new Exception("Invalid vote value");
            }
            
            // Check if already voted
            $existingVote = dbFetchOne(
                "SELECT id FROM votes WHERE document_id = ? AND session_id = ? AND councilor_id = ?",
                [$data['document_id'], $data['session_id'], $data['councilor_id']]
            );
            
            if ($existingVote) {
                throw new Exception("Vote already cast for this document");
            }
            
            // Check if session is active
            $session = $this->getSession($data['session_id']);
            if (!$session || $session['status'] !== 'in_progress') {
                throw new Exception("Voting session is not active");
            }
            
            // Record vote
            $voteId = dbInsert('votes', [
                'session_id' => $data['session_id'],
                'document_id' => $data['document_id'],
                'councilor_id' => $data['councilor_id'],
                'vote' => $data['vote'],
                'remarks' => $data['remarks'] ?? null,
                'cast_at' => date('Y-m-d H:i:s'),
                'ip_address' => $data['ip_address'] ?? null,
                'user_agent' => $data['user_agent'] ?? null
            ]);
            
            logAudit($data['councilor_id'], 'vote_cast', 'votes', $voteId, null, [
                'session_id' => $data['session_id'],
                'document_id' => $data['document_id'],
                'vote' => $data['vote']
            ]);
            
            return $voteId;
        } catch (Exception $e) {
            error_log('VotingController::castVote error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Calculate results for all documents in a session
     */
    public function calculateSessionResults($sessionId) {
        try {
            $documents = dbFetchAll(
                "SELECT sd.*, d.id as doc_id FROM session_documents sd 
                 JOIN documents d ON sd.document_id = d.id 
                 WHERE sd.session_id = ?",
                [$sessionId]
            );
            
            $session = $this->getSession($sessionId);
            $quorum = $session['quorum_required'] ?? 5;
            
            foreach ($documents as $doc) {
                $votes = $this->getDocumentVotes($doc['doc_id'], $sessionId);
                
                $approveCount = count(array_filter($votes, fn($v) => $v['vote'] === 'approve'));
                $rejectCount = count(array_filter($votes, fn($v) => $v['vote'] === 'reject'));
                $totalVotes = count($votes);
                
                // Determine result
                $result = 'pending';
                if ($totalVotes >= $quorum) {
                    if ($approveCount > $rejectCount) {
                        $result = 'passed';
                        // Update document status
                        dbUpdate('documents', $doc['doc_id'], [
                            'status' => 'approved',
                            'approved_at' => date('Y-m-d H:i:s')
                        ]);
                    } else {
                        $result = 'failed';
                        dbUpdate('documents', $doc['doc_id'], [
                            'status' => 'rejected'
                        ]);
                    }
                }
                
                // Update session document record
                dbUpdate('session_documents', $doc['id'], [
                    'voting_status' => $result,
                    'votes_for' => $approveCount,
                    'votes_against' => $rejectCount
                ]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('VotingController::calculateSessionResults error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get votes for a document in a session
     */
    public function getDocumentVotes($documentId, $sessionId) {
        try {
            return dbFetchAll(
                "SELECT v.*, u.full_name as voter_name 
                 FROM votes v
                 JOIN users u ON v.councilor_id = u.id
                 WHERE v.document_id = ? AND v.session_id = ?
                 ORDER BY v.cast_at",
                [$documentId, $sessionId]
            );
        } catch (Exception $e) {
            error_log('VotingController::getDocumentVotes error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mark attendee as present
     */
    public function markAttendance($sessionId, $userId, $isPresent = true) {
        try {
            $attendee = dbFetchOne(
                "SELECT id FROM session_attendees WHERE session_id = ? AND user_id = ?",
                [$sessionId, $userId]
            );
            
            if ($attendee) {
                dbUpdate('session_attendees', $attendee['id'], [
                    'status' => $isPresent ? 'present' : 'absent',
                    'check_in_time' => $isPresent ? date('Y-m-d H:i:s') : null
                ]);
            } else {
                dbInsert('session_attendees', [
                    'session_id' => $sessionId,
                    'user_id' => $userId,
                    'status' => $isPresent ? 'present' : 'absent',
                    'check_in_time' => $isPresent ? date('Y-m-d H:i:s') : null
                ]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('VotingController::markAttendance error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get session attendees
     */
    public function getSessionAttendees($sessionId) {
        try {
            return dbFetchAll(
                "SELECT sa.*, u.full_name, u.position, u.email
                 FROM session_attendees sa
                 JOIN users u ON sa.user_id = u.id
                 WHERE sa.session_id = ?
                 ORDER BY u.full_name",
                [$sessionId]
            );
        } catch (Exception $e) {
            error_log('VotingController::getSessionAttendees error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get voting statistics
     */
    public function getStatistics($filters = []) {
        try {
            return [
                'total_sessions' => dbCount('voting_sessions'),
                'active_sessions' => dbCount('voting_sessions', "status = 'in_progress'"),
                'completed_sessions' => dbCount('voting_sessions', "status = 'completed'"),
                'total_votes' => dbCount('votes'),
                'approved_documents' => dbCount('documents', "status = 'approved'"),
                'rejected_documents' => dbCount('documents', "status = 'rejected'"),
            ];
        } catch (Exception $e) {
            error_log('VotingController::getStatistics error: ' . $e->getMessage());
            return [];
        }
    }
}

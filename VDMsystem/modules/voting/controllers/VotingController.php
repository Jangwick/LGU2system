<?php
/**
 * VDMsystem - Voting Controller
 * Handles voting session management and vote processing
 */
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/audit.php';

class VotingController {
    
    /**
     * Get all voting sessions with optional filters
     */
    public function getSessions($filters = []) {
        try {
            $where = "1=1";
            $params = [];
            
            if (!empty($filters['status'])) {
                if (is_array($filters['status'])) {
                    $placeholders = implode(',', array_fill(0, count($filters['status']), '?'));
                    $where .= " AND vs.status IN ($placeholders)";
                    foreach ($filters['status'] as $status) {
                        $params[] = $status;
                    }
                } else {
                    $where .= " AND vs.status = ?";
                    $params[] = $filters['status'];
                }
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
                "SELECT vs.*, u.full_name as created_by_name, c.name as committee_name,
                        (SELECT COUNT(*) FROM session_documents WHERE session_id = vs.id) as document_count,
                        (SELECT COUNT(*) FROM session_attendees WHERE session_id = vs.id AND status = 'present') as attendee_count,
                        (SELECT COUNT(*) FROM votes WHERE session_id = vs.id) as vote_count
                 FROM voting_sessions vs
                 LEFT JOIN users u ON vs.created_by = u.id
                 LEFT JOIN committees c ON vs.committee_id = c.id
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
                "SELECT vs.*, u.full_name as created_by_name, c.name as committee_name,
                        (SELECT COUNT(*) FROM session_documents WHERE session_id = vs.id) as document_count,
                        (SELECT COUNT(*) FROM session_attendees WHERE session_id = vs.id AND status = 'present') as attendee_count,
                        (SELECT COUNT(*) FROM votes WHERE session_id = vs.id) as vote_count
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
                        'vote_order' => $order,
                        'order_number' => $order++,
                        'status' => 'pending'
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
     * Update a voting session
     */
    public function updateSession($sessionId, $data) {
        try {
            $session = $this->getSession($sessionId);
            if (!$session) {
                throw new Exception("Session not found");
            }

            if (!in_array($session['status'], ['scheduled'])) {
                throw new Exception("Only scheduled sessions can be edited.");
            }

            $updateData = [];
            $allowedFields = ['title', 'description', 'session_date', 'start_time', 'end_time', 'location', 'vote_type', 'quorum_required', 'committee_id'];
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field] ?: null;
                }
            }

            if (!empty($updateData)) {
                dbUpdate('voting_sessions', $updateData, 'id = ?', [$sessionId]);
            }

            // Update documents if provided
            if (isset($data['documents'])) {
                dbDelete('session_documents', 'session_id = ?', [$sessionId]);
                $order = 1;
                foreach ($data['documents'] as $docId) {
                    dbInsert('session_documents', [
                        'session_id' => $sessionId,
                        'document_id' => $docId,
                        'vote_order' => $order,
                        'order_number' => $order++,
                        'status' => 'pending'
                    ]);
                }
            }

            // Update attendees if provided
            if (isset($data['attendees'])) {
                dbDelete('session_attendees', 'session_id = ?', [$sessionId]);
                foreach ($data['attendees'] as $userId) {
                    dbInsert('session_attendees', [
                        'session_id' => $sessionId,
                        'user_id' => $userId,
                        'status' => 'absent'
                    ]);
                }
            }

            return true;
        } catch (Exception $e) {
            error_log('VotingController::updateSession error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete/cancel a voting session
     */
    public function cancelSession($sessionId, $userId) {
        try {
            $session = $this->getSession($sessionId);
            if (!$session) {
                throw new Exception("Session not found");
            }

            if ($session['status'] === 'completed') {
                throw new Exception("Completed sessions cannot be cancelled.");
            }

            dbUpdate('voting_sessions', ['status' => 'cancelled'], 'id = ?', [$sessionId]);

            logAudit('session_cancel', $userId, 'voting', 'voting_sessions', $sessionId, 'Voting session cancelled', [
                'session_number' => $session['session_number'],
                'previous_status' => $session['status']
            ]);

            return true;
        } catch (Exception $e) {
            error_log('VotingController::cancelSession error: ' . $e->getMessage());
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
            
            dbUpdate('voting_sessions', [
                'status' => 'in_progress',
                'actual_start_time' => date('Y-m-d H:i:s')
            ], 'id = ?', [$sessionId]);
            
            logAudit('session_start', $userId, 'voting', 'voting_sessions', $sessionId, 'Voting session started', [
                'session_number' => $session['session_number']
            ]);
            
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
            
            dbUpdate('voting_sessions', [
                'status' => 'completed',
                'actual_end_time' => date('Y-m-d H:i:s')
            ], 'id = ?', [$sessionId]);
            
            logAudit('session_end', $userId, 'voting', 'voting_sessions', $sessionId, 'Voting session completed', [
                'session_number' => $session['session_number']
            ]);
            
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
            
            logAudit('vote_cast', $data['councilor_id'], 'voting', 'votes', $voteId, 'Vote cast', [
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
                        $result = 'approved';
                        // Update document status
                        dbUpdate('documents', [
                            'status' => 'approved',
                            'approved_at' => date('Y-m-d H:i:s')
                        ], 'id = ?', [$doc['doc_id']]);
                    } else {
                        $result = 'rejected';
                        dbUpdate('documents', [
                            'status' => 'rejected'
                        ], 'id = ?', [$doc['doc_id']]);
                    }
                }
                
                // Update session document record
                dbUpdate('session_documents', [
                    'status' => $result,
                    'approve_count' => $approveCount,
                    'reject_count' => $rejectCount
                ], 'id = ?', [$doc['id']]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('VotingController::calculateSessionResults error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get summary of results for a session
     */
    public function getSessionResultsSummary($sessionId) {
        try {
            $docs = $this->getSessionDocuments($sessionId);
            $totalApprove = 0;
            $totalReject = 0;
            $totalAbstain = 0;
            $passed = 0;
            $failed = 0;

            foreach ($docs as $doc) {
                $totalApprove += $doc['approve_count'];
                $totalReject += $doc['reject_count'];
                $totalAbstain += $doc['abstain_count'];
                // Check both alias and direct column for safety
                $status = $doc['voting_status'] ?? $doc['status'] ?? 'pending';
                if ($status === 'passed' || $status === 'approved') $passed++;
                if ($status === 'failed' || $status === 'rejected') $failed++;
            }

            return [
                'total_docs' => count($docs),
                'passed_docs' => $passed,
                'failed_docs' => $failed,
                'total_approve' => $totalApprove,
                'total_reject' => $totalReject,
                'total_abstain' => $totalAbstain
            ];
        } catch (Exception $e) {
            return null;
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
                dbUpdate('session_attendees', [
                    'status' => $isPresent ? 'present' : 'absent',
                    'check_in_time' => $isPresent ? date('Y-m-d H:i:s') : null
                ], 'id = ?', [$attendee['id']]);
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
     * Get session documents
     */
    public function getSessionDocuments($sessionId) {
        try {
            return dbFetchAll(
                "SELECT sd.*, 
                        CASE 
                            WHEN sd.status = 'approved' THEN 'passed' 
                            WHEN sd.status = 'rejected' THEN 'failed' 
                            ELSE sd.status 
                        END as voting_status, 
                        sd.vote_order as voting_order, 
                        d.doc_number, d.title, d.type, d.description as summary, d.status as doc_status,
                        (SELECT COUNT(*) FROM votes WHERE document_id = d.id AND session_id = sd.session_id) as vote_count,
                        (SELECT COUNT(*) FROM votes WHERE document_id = d.id AND session_id = sd.session_id AND vote = 'approve') as approve_count,
                        (SELECT COUNT(*) FROM votes WHERE document_id = d.id AND session_id = sd.session_id AND vote = 'reject') as reject_count,
                        (SELECT COUNT(*) FROM votes WHERE document_id = d.id AND session_id = sd.session_id AND vote = 'abstain') as abstain_count
                 FROM session_documents sd
                 JOIN documents d ON sd.document_id = d.id
                 WHERE sd.session_id = ?
                 ORDER BY sd.vote_order, d.created_at",
                [$sessionId]
            );
        } catch (Exception $e) {
            error_log('VotingController::getSessionDocuments error: ' . $e->getMessage());
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

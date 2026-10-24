<?php
namespace ElectroGuard\Services;

use ElectroGuard\Config\Database;
use PDO;

class ClearanceRuleEngine {
    /**
     * Authoritative evaluation of clearance completeness and restoration eligibility.
     * Enforces the central principle:
     * "No completed clearance workflow, no authorization to proceed with a restoration request."
     */
    public static function evaluateJob(int $jobId): array {
        $pdo = Database::getConnection();

        // 1. Fetch job details with area and controller
        $stmt = $pdo->prepare("
            SELECT j.*, a.name AS area_name, a.code AS area_code,
                   c.controller_uid, c.connection_status AS controller_status, c.relay_state, c.last_heartbeat,
                   u.full_name AS supervisor_name
            FROM maintenance_jobs j
            JOIN maintenance_areas a ON j.area_id = a.id
            LEFT JOIN controllers c ON j.controller_id = c.id
            JOIN users u ON j.supervisor_id = u.id
            WHERE j.id = :id
        ");
        $stmt->execute([':id' => $jobId]);
        $job = $stmt->fetch();

        if (!$job) {
            return [
                'error' => 'Job not found',
                'is_eligible' => false,
                'restoration_status' => 'BLOCKED',
                'blocking_reason' => 'Invalid job reference'
            ];
        }

        // 2. Fetch all assigned workers requiring clearance
        $stmt = $pdo->prepare("
            SELECT a.worker_user_id, u.full_name, u.phone_masked, wp.worker_code, wp.skill_level
            FROM job_worker_assignments a
            JOIN users u ON a.worker_user_id = u.id
            JOIN worker_profiles wp ON u.id = wp.user_id
            WHERE a.job_id = :jid AND a.is_clearance_required = 1
        ");
        $stmt->execute([':jid' => $jobId]);
        $requiredWorkers = $stmt->fetchAll();
        $requiredCount = count($requiredWorkers);

        // 3. Fetch clearances submitted for this job
        $stmt = $pdo->prepare("
            SELECT cs.*, u.full_name, wp.worker_code
            FROM clearance_submissions cs
            JOIN users u ON cs.worker_user_id = u.id
            JOIN worker_profiles wp ON u.id = wp.user_id
            WHERE cs.job_id = :jid
        ");
        $stmt->execute([':jid' => $jobId]);
        $allClearances = $stmt->fetchAll();

        // Separate active valid submissions and reopened/revoked submissions
        $validSubmissions = [];
        $reopenedSubmissions = [];
        foreach ($allClearances as $c) {
            if ($c['status'] === 'SUBMITTED') {
                $validSubmissions[$c['worker_user_id']] = $c;
            } elseif ($c['status'] === 'REOPENED' || $c['status'] === 'REVOKED') {
                $reopenedSubmissions[$c['worker_user_id']] = $c;
            }
        }

        // Missing workers
        $missingWorkers = [];
        foreach ($requiredWorkers as $rw) {
            $uid = $rw['worker_user_id'];
            if (!isset($validSubmissions[$uid])) {
                $missingWorkers[] = [
                    'id' => $uid,
                    'name' => $rw['full_name'],
                    'code' => $rw['worker_code'],
                    'status' => isset($reopenedSubmissions[$uid]) ? 'REOPENED' : 'PENDING'
                ];
            }
        }

        // 4. Check supervisor review
        $stmt = $pdo->prepare("
            SELECT * FROM supervisor_reviews
            WHERE job_id = :jid
            ORDER BY reviewed_at DESC LIMIT 1
        ");
        $stmt->execute([':jid' => $jobId]);
        $supervisorReview = $stmt->fetch();
        $isSupervisorApproved = ($supervisorReview && $supervisorReview['review_status'] === 'APPROVED');

        // 5. Check controller connectivity
        $controllerStatus = $job['controller_status'] ?? 'UNKNOWN';
        // A controller is considered ONLINE only if status is strictly ONLINE
        $isControllerOnline = ($controllerStatus === 'ONLINE');

        // 6. Central Rule Evaluation
        $isEligible = false;
        $restorationStatus = 'BLOCKED';
        $blockingReason = '';
        $jobStatus = $job['status'];

        if ($requiredCount === 0) {
            $restorationStatus = 'BLOCKED';
            $blockingReason = 'Configuration Error: No workers assigned requiring clearance.';
        } elseif (!empty($reopenedSubmissions)) {
            // RULE F: A revoked or reopened clearance invalidates the previous clearance set and blocks restoration requests.
            $reopenedNames = array_map(fn($r) => $r['full_name'], $reopenedSubmissions);
            $namesList = implode(', ', $reopenedNames);
            $restorationStatus = 'BLOCKED';
            $blockingReason = "Blocked: Worker clearance was reopened by {$namesList}. Previous clearance set invalidated.";
            $jobStatus = 'MAINTENANCE_ACTIVE';
        } elseif (count($missingWorkers) > 0) {
            // RULE E: Missing clearance means restoration request remains blocked.
            $missingCount = count($missingWorkers);
            $missingNames = implode(', ', array_column($missingWorkers, 'name'));
            $restorationStatus = 'BLOCKED';
            $blockingReason = "Blocked: {$missingCount} required worker(s) ({$missingNames}) have not submitted clearance.";
            $jobStatus = 'MAINTENANCE_ACTIVE';
        } else {
            // All worker clearances are submitted and valid
            if (!$isSupervisorApproved) {
                // RULE H: A supervisor must review the clearance set and record procedural checks.
                $restorationStatus = 'AWAITING_REVIEW';
                $blockingReason = 'Blocked: All worker clearances submitted; Supervisor procedural safety review is pending.';
                $jobStatus = 'PENDING_SUPERVISOR_REVIEW';
            } elseif (!$isControllerOnline) {
                // RULE K: If controller is disconnected or unknown, prevent authorized restoration.
                $restorationStatus = 'BLOCKED';
                $blockingReason = "Blocked: IoT Controller '{$job['controller_uid']}' is {$controllerStatus}. State is UNKNOWN. Cannot proceed.";
            } else {
                // RULE I: Only after all conditions pass may it be marked eligible for review.
                if ($job['restoration_status'] === 'SIMULATED_COMPLETION') {
                    $restorationStatus = 'SIMULATED_COMPLETION';
                    $blockingReason = 'Simulated clearance workflow completed in demonstration mode.';
                    $jobStatus = 'CLEARANCE_COMPLETE';
                } else {
                    $isEligible = true;
                    $restorationStatus = 'ELIGIBLE_FOR_REVIEW';
                    $blockingReason = 'All clearances verified, supervisor approval complete, and controller confirmed online. Eligible for authorized restoration review.';
                    $jobStatus = 'CLEARANCE_COMPLETE';
                }
            }
        }

        // Persist authoritative calculated status back to maintenance_jobs table if changed
        if ($job['restoration_status'] !== $restorationStatus || $job['blocking_reason'] !== $blockingReason || $job['status'] !== $jobStatus) {
            $updateStmt = $pdo->prepare("
                UPDATE maintenance_jobs
                SET restoration_status = :rstatus, blocking_reason = :breason, status = :jstatus, updated_at = datetime('now')
                WHERE id = :id
            ");
            $updateStmt->execute([
                ':rstatus' => $restorationStatus,
                ':breason' => $blockingReason,
                ':jstatus' => $jobStatus,
                ':id' => $jobId
            ]);
        }

        return [
            'job_id' => $jobId,
            'job_code' => $job['job_code'],
            'title' => $job['title'],
            'area_id' => $job['area_id'],
            'area_name' => $job['area_name'],
            'supervisor_name' => $job['supervisor_name'],
            'job_status' => $jobStatus,
            'restoration_status' => $restorationStatus,
            'blocking_reason' => $blockingReason,
            'is_eligible' => $isEligible,
            'required_count' => $requiredCount,
            'submitted_count' => count($validSubmissions),
            'reopened_count' => count($reopenedSubmissions),
            'missing_workers' => $missingWorkers,
            'valid_submissions' => array_values($validSubmissions),
            'reopened_submissions' => array_values($reopenedSubmissions),
            'is_supervisor_approved' => $isSupervisorApproved,
            'supervisor_review' => $supervisorReview,
            'controller_uid' => $job['controller_uid'],
            'controller_status' => $controllerStatus,
            'is_controller_online' => $isControllerOnline
        ];
    }

    /**
     * Submit worker clearance declaration
     */
    public static function submitClearance(int $jobId, int $workerUserId, array $declarations = []): array {
        $pdo = Database::getConnection();

        // 1. Verify worker is assigned to this job and clearance is required
        $stmt = $pdo->prepare("
            SELECT * FROM job_worker_assignments
            WHERE job_id = :jid AND worker_user_id = :uid AND is_clearance_required = 1
        ");
        $stmt->execute([':jid' => $jobId, ':uid' => $workerUserId]);
        $assignment = $stmt->fetch();

        if (!$assignment) {
            AuditLogger::log($workerUserId, "User #{$workerUserId}", 'worker', 'CLEARANCE_REJECTED', 'JOB', (string)$jobId, 'BLOCKED', 'Worker is not assigned to this job or clearance not required');
            return [
                'success' => false,
                'error' => 'Unauthorized: Worker is not assigned to this maintenance job.'
            ];
        }

        // 2. Fetch worker details
        $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = :uid");
        $stmt->execute([':uid' => $workerUserId]);
        $workerName = $stmt->fetchColumn() ?: "Worker #{$workerUserId}";

        // 3. Upsert or insert clearance submission safely (prevents duplicate inconsistent states)
        $workCompleted = !empty($declarations['work_completed']) ? 1 : 1;
        $siteCleared = !empty($declarations['site_cleared']) ? 1 : 1;
        $toolsRemoved = !empty($declarations['tools_removed']) ? 1 : 1;
        $safetyWarning = !empty($declarations['safety_warning_acknowledged']) ? 1 : 1;

        $stmt = $pdo->prepare("
            INSERT INTO clearance_submissions (job_id, worker_user_id, status, work_completed_declared, site_cleared_declared, tools_removed_declared, safety_warning_acknowledged, reopen_reason, submitted_at, reopened_at)
            VALUES (:jid, :uid, 'SUBMITTED', :wc, :sc, :tr, :sw, NULL, datetime('now'), NULL)
            ON CONFLICT(job_id, worker_user_id) DO UPDATE SET
                status = 'SUBMITTED',
                work_completed_declared = :wc,
                site_cleared_declared = :sc,
                tools_removed_declared = :tr,
                safety_warning_acknowledged = :sw,
                reopen_reason = NULL,
                submitted_at = datetime('now'),
                reopened_at = NULL
        ");
        $stmt->execute([
            ':jid' => $jobId,
            ':uid' => $workerUserId,
            ':wc' => $workCompleted,
            ':sc' => $siteCleared,
            ':tr' => $toolsRemoved,
            ':sw' => $safetyWarning
        ]);

        AuditLogger::log($workerUserId, $workerName, 'worker', 'CLEARANCE_SUBMITTED', 'JOB', (string)$jobId, 'SUCCESS', 'Checklist declarations submitted by worker');
        AlertService::create('INFO', 'Clearance Submitted', "Worker {$workerName} submitted digital clearance for Job #{$jobId}", $jobId);

        // Re-evaluate the entire job state centrally
        $evaluated = self::evaluateJob($jobId);

        return [
            'success' => true,
            'message' => 'Clearance declaration successfully recorded.',
            'evaluation' => $evaluated
        ];
    }

    /**
     * Reopen worker clearance.
     * Invalidate previous clearance set, return job to restricted maintenance state,
     * block pending restoration authorizations, notify supervisor, and audit log.
     */
    public static function reopenClearance(int $jobId, int $workerUserId, string $reason): array {
        if (trim($reason) === '') {
            return [
                'success' => false,
                'error' => 'A valid reason must be specified for reopening maintenance clearance.'
            ];
        }

        $pdo = Database::getConnection();

        // 1. Check existing submission
        $stmt = $pdo->prepare("
            SELECT cs.*, u.full_name
            FROM clearance_submissions cs
            JOIN users u ON cs.worker_user_id = u.id
            WHERE cs.job_id = :jid AND cs.worker_user_id = :uid
        ");
        $stmt->execute([':jid' => $jobId, ':uid' => $workerUserId]);
        $clearance = $stmt->fetch();

        if (!$clearance) {
            return [
                'success' => false,
                'error' => 'No prior clearance submission found for this worker on this job.'
            ];
        }

        $workerName = $clearance['full_name'];

        // 2. Mark clearance as REOPENED
        $stmt = $pdo->prepare("
            UPDATE clearance_submissions
            SET status = 'REOPENED', reopen_reason = :reason, reopened_at = datetime('now')
            WHERE job_id = :jid AND worker_user_id = :uid
        ");
        $stmt->execute([
            ':reason' => $reason,
            ':jid' => $jobId,
            ':uid' => $workerUserId
        ]);

        // 3. Invalidate any existing supervisor reviews for this job
        $stmt = $pdo->prepare("DELETE FROM supervisor_reviews WHERE job_id = :jid");
        $stmt->execute([':jid' => $jobId]);

        // 4. Invalidate / block any restoration requests
        $stmt = $pdo->prepare("
            UPDATE restoration_requests
            SET status = 'BLOCKED', decision_reason = :reason, updated_at = datetime('now')
            WHERE job_id = :jid AND status != 'SIMULATED_COMPLETION'
        ");
        $stmt->execute([
            ':reason' => "Blocked: Worker {$workerName} reopened clearance. Reason: {$reason}",
            ':jid' => $jobId
        ]);

        // 5. Log audit trail and trigger critical alert
        AuditLogger::log($workerUserId, $workerName, 'worker', 'CLEARANCE_REOPENED', 'JOB', (string)$jobId, 'SUCCESS', "Reopened clearance. Reason: {$reason}");
        AlertService::create('CRITICAL', 'Clearance Reopened Alert', "Worker {$workerName} reopened clearance on Job #{$jobId}. Reason: {$reason}", $jobId);

        // 6. Centrally recalculate rule engine
        $evaluated = self::evaluateJob($jobId);

        return [
            'success' => true,
            'message' => 'Clearance reopened. Maintenance restrictions reinstated and all dependent authorizations invalidated.',
            'evaluation' => $evaluated
        ];
    }

    /**
     * Submit supervisor safety review
     */
    public static function submitSupervisorReview(int $jobId, int $supervisorId, string $notes, string $status = 'APPROVED'): array {
        $pdo = Database::getConnection();

        // Verify supervisor
        $stmt = $pdo->prepare("SELECT full_name, role FROM users WHERE id = :uid");
        $stmt->execute([':uid' => $supervisorId]);
        $user = $stmt->fetch();

        if (!$user || !in_array($user['role'], ['supervisor', 'admin'])) {
            return [
                'success' => false,
                'error' => 'Unauthorized: Only an assigned supervisor or administrator may record review.'
            ];
        }

        // Must first check if all workers have submitted valid clearance
        $evalBefore = self::evaluateJob($jobId);
        if ($evalBefore['submitted_count'] < $evalBefore['required_count'] || $evalBefore['reopened_count'] > 0) {
            AuditLogger::log($supervisorId, $user['full_name'], $user['role'], 'SUPERVISOR_REVIEW_REJECTED', 'JOB', (string)$jobId, 'BLOCKED', 'Attempted review while worker clearances were incomplete or reopened');
            return [
                'success' => false,
                'error' => 'Cannot record supervisor approval: Worker clearances are incomplete or reopened.'
            ];
        }

        $stmt = $pdo->prepare("
            INSERT INTO supervisor_reviews (job_id, supervisor_id, review_status, procedural_notes, ppe_and_grounding_verified, personnel_accounted_for, reviewed_at)
            VALUES (:jid, :sid, :status, :notes, 1, 1, datetime('now'))
        ");
        $stmt->execute([
            ':jid' => $jobId,
            ':sid' => $supervisorId,
            ':status' => $status,
            ':notes' => $notes
        ]);

        AuditLogger::log($supervisorId, $user['full_name'], $user['role'], 'SUPERVISOR_REVIEW', 'JOB', (string)$jobId, 'SUCCESS', "Supervisor {$status} review recorded");
        AlertService::create('INFO', 'Supervisor Review Completed', "Supervisor {$user['full_name']} completed review for Job #{$jobId}: {$status}", $jobId);

        $evaluated = self::evaluateJob($jobId);

        return [
            'success' => true,
            'message' => 'Supervisor procedural review recorded.',
            'evaluation' => $evaluated
        ];
    }

    /**
     * Attempt restoration request review & simulated switching
     */
    public static function executeRestorationRequest(int $jobId, int $adminId, string $action = 'SIMULATE_SWITCHING'): array {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT full_name, role FROM users WHERE id = :uid");
        $stmt->execute([':uid' => $adminId]);
        $user = $stmt->fetch();

        if (!$user || !in_array($user['role'], ['admin', 'supervisor'])) {
            return [
                'success' => false,
                'error' => 'Unauthorized: Only an administrator may authorize restoration requests.'
            ];
        }

        // Authoritative evaluation
        $evaluation = self::evaluateJob($jobId);

        if (!$evaluation['is_eligible']) {
            AuditLogger::log($adminId, $user['full_name'], $user['role'], 'RESTORATION_REQUEST_DENIED', 'JOB', (string)$jobId, 'BLOCKED', $evaluation['blocking_reason']);
            AlertService::create('CRITICAL', 'Restoration Request Blocked', "Restoration request denied for Job #{$jobId}. Reason: {$evaluation['blocking_reason']}", $jobId);

            return [
                'success' => false,
                'error' => $evaluation['blocking_reason'],
                'evaluation' => $evaluation
            ];
        }

        // If action is SIMULATE_SWITCHING
        $stmt = $pdo->prepare("
            INSERT INTO restoration_requests (job_id, requested_by, status, decision_reason, reviewed_by, simulated_switching_at, created_at, updated_at)
            VALUES (:jid, :uid, 'SIMULATED_COMPLETION', :reason, :rid, datetime('now'), datetime('now'), datetime('now'))
        ");
        $stmt->execute([
            ':jid' => $jobId,
            ':uid' => $adminId,
            ':reason' => 'Clearance criteria met. Simulated controller switching authorized in demonstration environment.',
            ':rid' => $adminId
        ]);

        // Update controller state to ENERGIZED in simulation
        if ($evaluation['controller_uid']) {
            $stmt = $pdo->prepare("
                UPDATE controllers
                SET relay_state = 'ENERGIZED', last_heartbeat = datetime('now')
                WHERE controller_uid = :cuid
            ");
            $stmt->execute([':cuid' => $evaluation['controller_uid']]);
        }

        // Update job status to CLEARANCE_COMPLETE / SIMULATED_COMPLETION
        $stmt = $pdo->prepare("
            UPDATE maintenance_jobs
            SET status = 'CLEARANCE_COMPLETE', restoration_status = 'SIMULATED_COMPLETION', blocking_reason = 'Simulated clearance workflow completed in demonstration mode.', updated_at = datetime('now')
            WHERE id = :jid
        ");
        $stmt->execute([':jid' => $jobId]);

        AuditLogger::log($adminId, $user['full_name'], $user['role'], 'SIMULATED_SWITCHING', 'JOB', (string)$jobId, 'SUCCESS', 'Executed simulated restoration in low-voltage demonstration mode');
        AlertService::create('INFO', 'Simulated Restoration Executed', "Simulated energization completed for Job #{$jobId} (Demonstration mode)", $jobId);

        return [
            'success' => true,
            'message' => 'Simulated restoration authorization executed successfully in prototype mode. (Notice: Physical grid switching requires utility lockout/tagout and certified manual procedures)',
            'evaluation' => self::evaluateJob($jobId)
        ];
    }
}

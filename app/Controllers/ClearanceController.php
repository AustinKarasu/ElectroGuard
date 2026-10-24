<?php
namespace ElectroGuard\Controllers;

use ElectroGuard\Config\Database;
use ElectroGuard\Middleware\AuthMiddleware;
use ElectroGuard\Services\ClearanceRuleEngine;
use ElectroGuard\Services\OtpService;
use ElectroGuard\Services\AuditLogger;
use PDO;

class ClearanceController {
    public static function verifyWorkerId(): void {
        $user = AuthMiddleware::requireAuth();
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $workerCode = trim($input['worker_code'] ?? '');
        $jobId = (int)($input['job_id'] ?? 0);

        if (empty($workerCode) || empty($jobId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Worker Code and Job ID are required.']);
            return;
        }

        $pdo = Database::getConnection();

        // 1. Check worker exists and profile matches
        $stmt = $pdo->prepare("
            SELECT u.id, u.full_name, u.role, wp.worker_code, wp.department, wp.skill_level
            FROM users u
            JOIN worker_profiles wp ON u.id = wp.user_id
            WHERE wp.worker_code = :code
        ");
        $stmt->execute([':code' => $workerCode]);
        $worker = $stmt->fetch();

        if (!$worker) {
            AuditLogger::log($user['id'], $user['full_name'], $user['role'], 'WORKER_ID_SCAN_FAILED', 'WORKER', $workerCode, 'FAILED', 'Worker Code not found in registry');
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => "Worker ID '{$workerCode}' not recognized in active utility directory."]);
            return;
        }

        // 2. Check worker is assigned to this job
        $stmt = $pdo->prepare("
            SELECT * FROM job_worker_assignments
            WHERE job_id = :jid AND worker_user_id = :uid
        ");
        $stmt->execute([':jid' => $jobId, ':uid' => $worker['id']]);
        $assignment = $stmt->fetch();

        if (!$assignment) {
            AuditLogger::log($worker['id'], $worker['full_name'], 'worker', 'WORKER_ID_JOB_MISMATCH', 'JOB', (string)$jobId, 'BLOCKED', "Worker {$workerCode} is not assigned to Job #{$jobId}");
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => "Worker {$worker['full_name']} ({$workerCode}) is not assigned to this maintenance job."]);
            return;
        }

        AuditLogger::log($worker['id'], $worker['full_name'], 'worker', 'WORKER_ID_VERIFIED', 'JOB', (string)$jobId, 'SUCCESS', "Worker ID {$workerCode} confirmed assigned to Job #{$jobId}");

        echo json_encode([
            'success' => true,
            'message' => 'Worker identity and job assignment successfully verified.',
            'worker' => $worker
        ]);
    }

    public static function requestOtp(int $jobId): void {
        $user = AuthMiddleware::requireAuth();
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $purpose = $input['purpose'] ?? 'CLEARANCE';

        $challenge = OtpService::createChallenge($jobId, (int)$user['id'], $purpose);

        echo json_encode([
            'success' => true,
            'data' => $challenge
        ]);
    }

    public static function verifyOtp(int $jobId): void {
        $user = AuthMiddleware::requireAuth();
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $code = trim($input['code'] ?? '');

        if (empty($code)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Please enter the 6-digit verification code.']);
            return;
        }

        $result = OtpService::verifyChallenge($jobId, (int)$user['id'], $code);
        if (!$result['success']) {
            http_response_code(422);
            echo json_encode($result);
            return;
        }

        echo json_encode($result);
    }

    public static function submit(int $jobId): void {
        $user = AuthMiddleware::requireAuth();
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        // Check if an OTP was verified within the last 10 minutes
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT id FROM otp_challenges
            WHERE job_id = :jid AND user_id = :uid AND is_verified = 1
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([':jid' => $jobId, ':uid' => $user['id']]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Two-factor OTP verification is required before submitting maintenance clearance.']);
            return;
        }

        $result = ClearanceRuleEngine::submitClearance($jobId, (int)$user['id'], $input);
        if (!$result['success']) {
            http_response_code(400);
            echo json_encode($result);
            return;
        }

        echo json_encode($result);
    }

    public static function reopen(int $jobId): void {
        $user = AuthMiddleware::requireAuth();
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $reason = trim($input['reason'] ?? '');

        if (empty($reason)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'A detailed reason must be provided to reopen clearance.']);
            return;
        }

        $result = ClearanceRuleEngine::reopenClearance($jobId, (int)$user['id'], $reason);
        if (!$result['success']) {
            http_response_code(400);
            echo json_encode($result);
            return;
        }

        echo json_encode($result);
    }

    public static function supervisorReview(int $jobId): void {
        $user = AuthMiddleware::requireRole(['supervisor', 'admin']);
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $notes = trim($input['notes'] ?? 'Procedural checks verified on site.');
        $status = $input['status'] ?? 'APPROVED';

        $result = ClearanceRuleEngine::submitSupervisorReview($jobId, (int)$user['id'], $notes, $status);
        if (!$result['success']) {
            http_response_code(400);
            echo json_encode($result);
            return;
        }

        echo json_encode($result);
    }
}

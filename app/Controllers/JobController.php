<?php
namespace ElectroGuard\Controllers;

use ElectroGuard\Config\Database;
use ElectroGuard\Middleware\AuthMiddleware;
use ElectroGuard\Services\ClearanceRuleEngine;
use ElectroGuard\Services\AuditLogger;
use ElectroGuard\Services\AlertService;
use PDO;

class JobController {
    public static function index(): void {
        AuthMiddleware::requireAuth();
        $pdo = Database::getConnection();

        $stmt = $pdo->query("
            SELECT j.*, a.name AS area_name, a.code AS area_code, a.substation, a.latitude, a.longitude,
                   c.controller_uid, c.connection_status AS controller_status, c.relay_state,
                   u.full_name AS supervisor_name
            FROM maintenance_jobs j
            JOIN maintenance_areas a ON j.area_id = a.id
            LEFT JOIN controllers c ON j.controller_id = c.id
            JOIN users u ON j.supervisor_id = u.id
            ORDER BY j.id ASC
        ");
        $rawJobs = $stmt->fetchAll();

        $jobs = [];
        foreach ($rawJobs as $job) {
            $eval = ClearanceRuleEngine::evaluateJob((int)$job['id']);
            $job['evaluation'] = $eval;
            $jobs[] = $job;
        }

        echo json_encode(['success' => true, 'data' => $jobs]);
    }

    public static function get(int $id): void {
        AuthMiddleware::requireAuth();
        $eval = ClearanceRuleEngine::evaluateJob($id);
        if (isset($eval['error'])) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Job not found']);
            return;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT j.*, a.name AS area_name, a.code AS area_code, a.substation, a.latitude, a.longitude,
                   c.controller_uid, c.connection_status AS controller_status, c.relay_state,
                   u.full_name AS supervisor_name
            FROM maintenance_jobs j
            JOIN maintenance_areas a ON j.area_id = a.id
            LEFT JOIN controllers c ON j.controller_id = c.id
            JOIN users u ON j.supervisor_id = u.id
            WHERE j.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $job = $stmt->fetch();
        $job['evaluation'] = $eval;

        echo json_encode(['success' => true, 'data' => $job]);
    }

    public static function workerJobs(): void {
        $user = AuthMiddleware::requireAuth();
        $workerUserId = (int)$user['id'];
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT j.*, a.name AS area_name, a.code AS area_code, a.substation,
                   c.controller_uid, c.connection_status AS controller_status,
                   u.full_name AS supervisor_name,
                   cs.status AS my_clearance_status, cs.submitted_at AS my_submitted_at, cs.reopen_reason AS my_reopen_reason
            FROM job_worker_assignments a_w
            JOIN maintenance_jobs j ON a_w.job_id = j.id
            JOIN maintenance_areas a ON j.area_id = a.id
            LEFT JOIN controllers c ON j.controller_id = c.id
            JOIN users u ON j.supervisor_id = u.id
            LEFT JOIN clearance_submissions cs ON cs.job_id = j.id AND cs.worker_user_id = :uid
            WHERE a_w.worker_user_id = :uid
            ORDER BY j.id ASC
        ");
        $stmt->execute([':uid' => $workerUserId]);
        $rawJobs = $stmt->fetchAll();

        $jobs = [];
        foreach ($rawJobs as $job) {
            $eval = ClearanceRuleEngine::evaluateJob((int)$job['id']);
            $job['evaluation'] = $eval;
            $jobs[] = $job;
        }

        echo json_encode(['success' => true, 'data' => $jobs]);
    }

    public static function create(): void {
        $user = AuthMiddleware::requireRole(['admin', 'supervisor']);
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $jobCode = trim($input['job_code'] ?? '');
        $areaId = (int)($input['area_id'] ?? 0);
        $supervisorId = (int)($input['supervisor_id'] ?? $user['id']);
        $title = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $safetyNotes = trim($input['safety_notes'] ?? '');
        $workerIds = (array)($input['worker_ids'] ?? []);
        $controllerId = !empty($input['controller_id']) ? (int)$input['controller_id'] : null;

        if (empty($jobCode) || empty($areaId) || empty($title) || empty($workerIds)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Job Code, Area, Title, and at least one Worker assignment are required.']);
            return;
        }

        $pdo = Database::getConnection();

        // Check unique code
        $stmt = $pdo->prepare("SELECT id FROM maintenance_jobs WHERE job_code = :code");
        $stmt->execute([':code' => $jobCode]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => "Job code '{$jobCode}' already exists."]);
            return;
        }

        // If controller not selected, try to match by area
        if (!$controllerId) {
            $cStmt = $pdo->prepare("SELECT id FROM controllers WHERE area_id = :aid LIMIT 1");
            $cStmt->execute([':aid' => $areaId]);
            $controllerId = $cStmt->fetchColumn() ?: null;
        }

        $stmt = $pdo->prepare("
            INSERT INTO maintenance_jobs (job_code, area_id, controller_id, supervisor_id, title, description, status, restoration_status, blocking_reason, safety_notes, planned_start, planned_end, created_at, updated_at)
            VALUES (:code, :aid, :cid, :sid, :title, :desc, 'MAINTENANCE_ACTIVE', 'BLOCKED', 'Awaiting worker clearances', :safety, datetime('now'), datetime('now', '+4 hours'), datetime('now'), datetime('now'))
        ");
        $stmt->execute([
            ':code' => $jobCode,
            ':aid' => $areaId,
            ':cid' => $controllerId,
            ':sid' => $supervisorId,
            ':title' => $title,
            ':desc' => $description,
            ':safety' => $safetyNotes
        ]);

        $jobId = (int)$pdo->lastInsertId();

        // Assign workers safely
        $assignStmt = $pdo->prepare("
            INSERT OR IGNORE INTO job_worker_assignments (job_id, worker_user_id, is_clearance_required, assigned_at)
            VALUES (:jid, :uid, 1, datetime('now'))
        ");
        foreach ($workerIds as $wId) {
            $assignStmt->execute([':jid' => $jobId, ':uid' => (int)$wId]);
        }

        // Audit log
        AuditLogger::log($user['id'], $user['full_name'], $user['role'], 'JOB_CREATED', 'MAINTENANCE_JOB', $jobCode, 'SUCCESS', "Created maintenance job {$jobCode} with " . count($workerIds) . " required workers");
        AlertService::create('INFO', 'New Maintenance Job Created', "Job {$jobCode} created for Area ID {$areaId}", $jobId);

        // Authoritatively evaluate
        $eval = ClearanceRuleEngine::evaluateJob($jobId);

        echo json_encode([
            'success' => true,
            'message' => "Job {$jobCode} created successfully.",
            'job_id' => $jobId,
            'evaluation' => $eval
        ]);
    }

    public static function getAreas(): void {
        AuthMiddleware::requireAuth();
        $pdo = Database::getConnection();
        $areas = $pdo->query("SELECT * FROM maintenance_areas ORDER BY id ASC")->fetchAll();
        echo json_encode(['success' => true, 'data' => $areas]);
    }

    public static function getWorkers(): void {
        AuthMiddleware::requireAuth();
        $pdo = Database::getConnection();
        $workers = $pdo->query("
            SELECT u.id, u.full_name, u.email, u.phone_masked, wp.worker_code, wp.department, wp.skill_level
            FROM users u
            JOIN worker_profiles wp ON u.id = wp.user_id
            WHERE u.role = 'worker'
            ORDER BY u.id ASC
        ")->fetchAll();
        echo json_encode(['success' => true, 'data' => $workers]);
    }
}

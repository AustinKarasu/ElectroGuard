<?php
namespace ElectroGuard\Controllers;

use ElectroGuard\Config\Database;
use ElectroGuard\Middleware\AuthMiddleware;
use ElectroGuard\Services\ClearanceRuleEngine;
use PDO;

class RestorationController {
    public static function index(): void {
        AuthMiddleware::requireAuth();
        $pdo = Database::getConnection();

        $stmt = $pdo->query("
            SELECT r.*, j.job_code, j.title AS job_title, j.status AS job_status,
                   a.name AS area_name, c.controller_uid, c.connection_status AS controller_status,
                   u.full_name AS requester_name
            FROM restoration_requests r
            JOIN maintenance_jobs j ON r.job_id = j.id
            JOIN maintenance_areas a ON j.area_id = a.id
            LEFT JOIN controllers c ON j.controller_id = c.id
            JOIN users u ON r.requested_by = u.id
            ORDER BY r.created_at DESC
        ");
        $requests = $stmt->fetchAll();

        echo json_encode(['success' => true, 'data' => $requests]);
    }

    public static function execute(int $jobId): void {
        $user = AuthMiddleware::requireRole(['admin', 'supervisor']);
        $result = ClearanceRuleEngine::executeRestorationRequest($jobId, (int)$user['id'], 'SIMULATE_SWITCHING');

        if (!$result['success']) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'CLEARANCE_INCOMPLETE',
                    'message' => $result['error'],
                    'details' => $result['evaluation'] ?? null
                ]
            ]);
            return;
        }

        echo json_encode($result);
    }
}

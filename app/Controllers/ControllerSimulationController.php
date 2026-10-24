<?php
namespace ElectroGuard\Controllers;

use ElectroGuard\Config\Database;
use ElectroGuard\Middleware\AuthMiddleware;
use ElectroGuard\Services\AuditLogger;
use ElectroGuard\Services\AlertService;
use PDO;

class ControllerSimulationController {
    public static function index(): void {
        AuthMiddleware::requireAuth();
        $pdo = Database::getConnection();

        $controllers = $pdo->query("
            SELECT c.*, a.name AS area_name, a.code AS area_code
            FROM controllers c
            LEFT JOIN maintenance_areas a ON c.area_id = a.id
            ORDER BY c.id ASC
        ")->fetchAll();

        echo json_encode(['success' => true, 'data' => $controllers]);
    }

    public static function updateStatus(int $id): void {
        $user = AuthMiddleware::requireRole(['admin', 'supervisor']);
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $status = strtoupper(trim($input['status'] ?? 'ONLINE'));
        $delayMs = (int)($input['response_delay_ms'] ?? 0);
        $failNext = !empty($input['fail_next_command']) ? 1 : 0;

        if (!in_array($status, ['ONLINE', 'OFFLINE', 'STALE', 'UNKNOWN'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid status option.']);
            return;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM controllers WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $controller = $stmt->fetch();

        if (!$controller) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Controller not found.']);
            return;
        }

        $newRelay = $status === 'ONLINE' ? $controller['relay_state'] : 'UNKNOWN';

        $stmt = $pdo->prepare("
            UPDATE controllers
            SET connection_status = :status, relay_state = :relay, response_delay_ms = :delay, fail_next_command = :fail, last_heartbeat = datetime('now')
            WHERE id = :id
        ");
        $stmt->execute([
            ':status' => $status,
            ':relay' => $newRelay,
            ':delay' => $delayMs,
            ':fail' => $failNext,
            ':id' => $id
        ]);

        AuditLogger::log($user['id'], $user['full_name'], $user['role'], 'CONTROLLER_STATE_CHANGED', 'CONTROLLER', $controller['controller_uid'], 'SUCCESS', "Simulation status changed from {$controller['connection_status']} to {$status}");

        if ($status !== 'ONLINE') {
            AlertService::create('WARNING', 'Controller Connectivity Warning', "ESP32 Controller {$controller['controller_uid']} reported {$status}. State is UNKNOWN.");
        }

        echo json_encode([
            'success' => true,
            'message' => "Controller {$controller['controller_uid']} status updated to {$status}."
        ]);
    }

    public static function triggerHeartbeat(int $id): void {
        AuthMiddleware::requireAuth();
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            UPDATE controllers
            SET connection_status = 'ONLINE', last_heartbeat = datetime('now')
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);

        echo json_encode(['success' => true, 'message' => 'Heartbeat received.']);
    }

    public static function simulateCommand(int $id): void {
        $user = AuthMiddleware::requireRole(['admin', 'supervisor']);
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $command = $input['command'] ?? 'TEST_PULSE';

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM controllers WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $c = $stmt->fetch();

        if (!$c) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Controller not found.']);
            return;
        }

        if ($c['connection_status'] !== 'ONLINE') {
            AuditLogger::log($user['id'], $user['full_name'], $user['role'], 'CONTROLLER_COMMAND_FAILED', 'CONTROLLER', $c['controller_uid'], 'FAILED', "Command rejected: Controller is {$c['connection_status']}");
            http_response_code(503);
            echo json_encode([
                'success' => false,
                'error' => "Command failed: Controller {$c['controller_uid']} is currently {$c['connection_status']}. Cannot execute software command on disconnected hardware."
            ]);
            return;
        }

        if ($c['fail_next_command']) {
            $pdo->prepare("UPDATE controllers SET fail_next_command = 0 WHERE id = :id")->execute([':id' => $id]);
            AuditLogger::log($user['id'], $user['full_name'], $user['role'], 'CONTROLLER_COMMAND_ERROR', 'CONTROLLER', $c['controller_uid'], 'FAILED', 'Simulated hardware communication timeout');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => "Simulated Hardware Error: ESP32 communication bus timeout. Command execution unconfirmed."
            ]);
            return;
        }

        AuditLogger::log($user['id'], $user['full_name'], $user['role'], 'CONTROLLER_COMMAND_EXECUTED', 'CONTROLLER', $c['controller_uid'], 'SUCCESS', "Executed low-voltage demonstration command: {$command}");

        echo json_encode([
            'success' => true,
            'message' => "Low-voltage demonstration signal {$command} acknowledged by ESP32 simulator."
        ]);
    }
}
